<?php

namespace App\Http\Controllers;

use App\Mail\VerifyAccountMail;
use App\Models\Category;
use App\Models\Language;
use App\Models\PageContent;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\Signature;
use App\Models\User;
use App\Models\UserLevel;
use App\Rules\SafeExternalImageUrl;
use App\Support\AppLog;
use App\Support\CacheKeys;
use App\Support\ImageVariants;
use App\Support\PetitionCache;
use App\Support\Settings;
use App\Support\Spam;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Laravel\Facades\Image;

class PetitionController extends Controller
{
    private function requireOwner(Petition $petition): void
    {
        abort_unless(auth()->check() && (int) $petition->user_id === (int) auth()->id(), 403);
    }

    private function requireEditable(Petition $petition): void
    {
        $this->requireOwner($petition);

        abort_if(
            $petition->signatures()->where('user_id', '!=', $petition->user_id)->exists(),
            403,
            'Petition can no longer be edited after another person has signed.'
        );
    }

    private function resolveTrOrRedirect(string $routeName, string $locale, string $slug, Petition $petition, array $extra = [])
    {
        $tr = PetitionTranslation::query()
            ->where('petition_id', $petition->id)
            ->where('locale', $locale)
            ->first() ?? PetitionTranslation::query()
            ->where('petition_id', $petition->id)
            ->orderBy('id')
            ->first();

        abort_if(! $tr, 404);

        if ($tr->slug !== $slug || $tr->locale !== $locale) {
            return redirect()->route($routeName, array_merge([
                'locale' => $tr->locale,
                'slug' => $tr->slug,
                'id' => $petition->id,
            ], $extra));
        }

        return $tr;
    }

    public function index(string $locale)
    {
        $locale = normalize_locale($locale);
        $defaultLocale = default_locale();

        $search = trim((string) request('q', ''));
        $page = max(1, min((int) request('page', 1), 500));

        $query = Petition::query()
            ->select([
                'petitions.id',
                'petitions.signature_count',
                'petitions.goal_signatures',
                'petitions.category_id',
                'petitions.cover_image',
            ])
            ->withTranslation([
                'base' => 'petitions',
                'base_key' => 'id',
                'translation_table' => 'petition_translations',
                'translation_key' => 'petition_id',
                'locale_alias' => 'pt_locale',
                'default_alias' => 'pt_default',
                'locale' => $locale,
                'default_locale' => $defaultLocale,
                'columns' => ['title' => 'tr_title', 'slug' => 'tr_slug'],
            ])
            ->where(function ($q) {
                $q->whereNotNull('pt_locale.title')
                    ->orWhereNotNull('pt_default.title');
            })
            ->where('petitions.status', 'published')
            ->where('petitions.is_active', 1);

        if ($search !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';
            $query->where(function ($q) use ($like) {
                $q->where('pt_locale.title', 'LIKE', $like)
                    ->orWhere('pt_default.title', 'LIKE', $like);
            });
            $query->orderByDesc('petitions.signature_count');
            $petitions = $query->paginate(15, ['*'], 'page', $page)->withQueryString();
        } else {
            $cacheKey = CacheKeys::petitionsIndex($locale, $page);
            $petitions = cache()->remember($cacheKey, now()->addSeconds(300), function () use ($query, $page) {
                return $query->orderByDesc('petitions.id')->paginate(15, ['*'], 'page', $page);
            });

            $petitions = $petitions->withQueryString();
        }

        return view('pages.petitions-list', [
            'pageTitle' => $search ? __('Search results for ":q"', ['q' => $search]) : 'All the petitions',
            'heading' => $search ? __('Search: ":q"', ['q' => $search]) : 'Petitions',
            'search' => $search,
            'petitions' => $petitions,
            'petitionTitle' => fn ($row) => $row->tr_title,
            'petitionUrl' => fn ($row) => lroute('petition.show', [
                'slug' => $row->tr_slug,
                'id' => $row->id,
            ]),
        ]);
    }

    public function show(Request $request, string $locale, string $slug, int $id)
    {
        $locale = normalize_locale($locale);
        $defaultLocale = default_locale();

        $petition = Petition::query()
            ->with(['category', 'user'])
            ->where('id', $id)
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('status', 'published');
                })
                    ->orWhere(function ($q2) {
                        $q2->where('status', 'draft')
                            ->where('user_id', auth()->id() ?? 0);
                    });
            })
            ->firstOrFail();

        if ($petition->status === 'published' && $petition->is_active) {
            $this->recordView($request, $petition);
        }

        $tr = PetitionTranslation::query()
            ->where('petition_id', $petition->id)
            ->where('locale', $locale)
            ->first()
            ?? PetitionTranslation::query()
                ->where('petition_id', $petition->id)
                ->where('locale', $defaultLocale)
                ->first()
            ?? PetitionTranslation::query()
                ->where('petition_id', $petition->id)
                ->orderBy('id')
                ->first();

        abort_if(! $tr, 404);

        if ($tr->slug !== $slug || $tr->locale !== $locale) {
            return redirect()->route('petition.show', [
                'locale' => $tr->locale,
                'slug' => $tr->slug,
                'id' => $petition->id,
            ]);
        }

        if ($petition->status === 'published' && ! $petition->is_active) {
            return response()->view('petition.offline', compact('petition', 'tr', 'locale'));
        }

        $hasSigned = auth()->check()
            ? Signature::where('petition_id', $petition->id)
                ->where('email', auth()->user()->email)
                ->exists()
            : false;

        $goalTotal = (int) ($petition->goal_signatures ?? 100);
        $goalCurrent = (int) ($petition->signature_count ?? 0);
        $pct = $goalTotal > 0 ? min(100, round(($goalCurrent / $goalTotal) * 100)) : 0;

        $latest = cache()->remember(
            "petition:latest_sigs:{$petition->id}",
            now()->addMinutes(5),
            fn () => Signature::where('petition_id', $petition->id)
                ->where('confirmed', true)
                ->where('is_spam', false)
                ->latest('created_at')
                ->limit(27)
                ->get()
        );

        $directLink = lroute('petition.show', [
            'slug' => $tr->slug,
            'id' => $petition->id,
        ]);

        $content = PageContent::where('page', 'petition_show')
            ->where('locale', $tr->locale)
            ->pluck('value', 'key');

        $formContent = PageContent::where('page', 'petition_sign_form')
            ->where('locale', $tr->locale)
            ->pluck('value', 'key');

        $defaultComments = Language::where('is_active', true)
            ->pluck('code')
            ->push($locale, $defaultLocale)
            ->unique()
            ->map(fn ($loc) => __('sign.default_comment', [], $loc))
            ->filter()
            ->values()
            ->all();

        $shoutboxPage = max(1, min((int) request('shoutbox', 1), 500));

        $shoutbox = Signature::with('user')
            ->where('petition_id', $petition->id)
            ->where('is_spam', false)
            ->where('confirmed', true)
            ->whereNotNull('text')
            ->whereRaw("TRIM(text) <> ''")
            ->when(! empty($defaultComments), fn ($q) => $q->whereNotIn('text', $defaultComments))
            ->orderBy('created_at')
            ->paginate(10, ['*'], 'shoutbox', $shoutboxPage)
            ->withQueryString();

        return view('petition.show', compact(
            'petition',
            'tr',
            'locale',
            'latest',
            'goalTotal',
            'goalCurrent',
            'pct',
            'directLink',
            'hasSigned',
            'content',
            'formContent',
            'shoutbox'
        ));
    }

    private function recordView(Request $request, Petition $petition): void
    {
        $sessionKey = "petition_viewed.{$petition->id}";

        if ($request->session()->has($sessionKey)) {
            return;
        }

        DB::transaction(function () use ($petition) {
            $today = now()->toDateString();
            $now = now();

            DB::table('petition_views')->insertOrIgnore([
                'petition_id' => $petition->id,
                'viewed_on' => $today,
                'views' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('petition_views')
                ->where('petition_id', $petition->id)
                ->where('viewed_on', $today)
                ->increment('views', 1, ['updated_at' => $now]);

            DB::table('petitions')->where('id', $petition->id)->increment('views');
        });

        $request->session()->put($sessionKey, true);
    }

    public function sign(Request $request, string $locale, string $slug, int $id)
    {
        $petition = Petition::query()
            ->where('id', $id)
            ->where('status', 'published')
            ->where('is_active', 1)
            ->firstOrFail();

        // Guest confirm-form fields are scoped per petition; only merge when
        // the stored petition matches, otherwise ignore the stale data.
        $signSessionKey = "sign.{$petition->id}";

        if ($request->session()->has($signSessionKey)) {
            $request->merge($request->session()->get($signSessionKey));
        }

        $defaultLocale = default_locale();

        $tr = PetitionTranslation::query()
            ->where('petition_id', $petition->id)
            ->where('locale', $locale)
            ->first()
            ?? PetitionTranslation::query()
                ->where('petition_id', $petition->id)
                ->where('locale', $defaultLocale)
                ->first()
            ?? PetitionTranslation::query()
                ->where('petition_id', $petition->id)
                ->orderBy('id')
                ->first();

        abort_if(! $tr, 404);

        if (auth()->check()) {
            if (! auth()->user()->verified) {
                toast('Please verify your email before signing petitions.', 'warning');

                return back()->withInput();
            }

            return $this->signAsAuthed($request, $locale, $petition);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email', 'max:190'],
            'comment' => ['nullable', 'string', 'max:500'],

            'agree1' => ['required', 'in:on,1'],
            'agree2' => ['required', 'in:on,1'],
            'agree3' => ['required', 'in:on,1'],
        ]);

        $spamText = implode(' ', [
            $data['name'] ?? '',
            $data['comment'] ?? '',
        ]);

        if (Spam::isSpam($spamText)) {
            Spam::log('signature', json_encode($data));

            toast('Suspicious activity detected.', 'error');

            return back()->withInput();
        }

        if (Spam::rateLimit('petition')) {
            Spam::log('petition', 'Rate limit exceeded');
            Spam::banCurrentIp('Too many petition attempts');

            toast('Too many petition attempts.', 'error');

            return back()->withInput();
        }

        $email = strtolower(trim($data['email']));

        $existingUser = User::where('email', $email)->first();

        $alreadySigned = Signature::where('petition_id', $petition->id)
            ->where(function ($q) use ($email, $existingUser) {
                $q->where('email', $email);

                if ($existingUser) {
                    $q->orWhere('user_id', $existingUser->id);
                }
            })
            ->exists();

        if ($alreadySigned) {
            return back()
                ->withErrors(['email' => 'You already signed this petition.'])
                ->withInput($request->except('password'));
        }

        try {
            return DB::transaction(function () use ($data, $email, $locale, $petition, $tr, $existingUser) {
                $userLevel = UserLevel::where('name', 'user')->first();
                $needsVerification = (bool) Settings::get('require_email_verification', false, 'global');

                $user = $existingUser ?? User::create([
                    'name' => $data['name'],
                    'first_name' => $data['name'],
                    'last_name' => null,
                    'email' => $email,
                    'locale' => $locale,
                    'ip' => request()->ip(),
                    'level_id' => $userLevel?->id,
                    'verified' => ! $needsVerification,
                    'verification_token' => $needsVerification ? Str::random(64) : null,
                    'verification_token_sent_at' => $needsVerification ? now() : null,
                ]);

                if (! $existingUser && $needsVerification && Settings::get('smtp_enabled', false)) {
                    try {
                        Mail::to($user->email)->queue(new VerifyAccountMail($user, $locale));
                    } catch (\Throwable $e) {
                        AppLog::error('Verification email failed', $e->getMessage(), 'petition.sign');
                    }
                }

                $sig = Signature::firstOrCreate(
                    ['petition_id' => $petition->id, 'email' => $email],
                    [
                        'user_id' => $user->id,
                        'name' => $data['name'],
                        'locale' => $locale,
                        'text' => $data['comment'] ?? 'I support this petition',
                        'confirmed' => $user->verified,
                        'ip_address' => request()->ip(),
                    ]
                );

                if ($sig->wasRecentlyCreated && $sig->confirmed) {
                    DB::table('petitions')->where('id', $petition->id)->increment('signature_count');
                    PetitionCache::touch($petition);
                }

                AppLog::info(
                    'Petition signed',
                    'Petition ID: '.$petition->id.' | User: '.$email.' | IP: '.request()->ip(),
                    'petition.sign'
                );

                session()->forget("sign.{$petition->id}");
                session()->flash('sign_claim', [
                    'email' => $email,
                    'is_new' => ! $existingUser,
                    'name' => $data['name'],
                ]);

                return redirect()->route('petition.thanks', [
                    'locale' => $locale,
                    'slug' => $tr->slug,
                    'id' => $petition->id,
                    'status' => $user->verified ? 1 : 0,
                ]);
            });
        } catch (QueryException $e) {
            if (($e->errorInfo[0] ?? null) === '23000') {
                return back()
                    ->withErrors(['email' => 'You already signed this petition.'])
                    ->withInput($request->except('password'));
            }

            throw $e;
        }
    }

    public function create(string $locale)
    {
        $defaultLocale = default_locale();

        $categories = Category::query()
            ->select(['categories.id'])
            ->withTranslation([
                'base' => 'categories',
                'base_key' => 'id',
                'translation_table' => 'category_translations',
                'translation_key' => 'category_id',
                'locale_alias' => 'ct_locale',
                'default_alias' => 'ct_default',
                'locale' => $locale,
                'default_locale' => $defaultLocale,
                'columns' => ['name' => 'name', 'slug' => 'slug'],
            ])
            ->where('categories.is_active', true)
            ->orderBy('name')
            ->get();

        return view('petition.create', compact('locale', 'categories'));
    }

    public function store(Request $request, string $locale)
    {
        $uploaded = $request->file('image');
        if ($uploaded !== null && ! $uploaded->isValid()) {
            $reason = $uploaded->getError();
            $message = in_array($reason, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                ? 'The image may not be larger than 4 MB.'
                : 'The image failed to upload. Please try again.';

            return back()
                ->withErrors(['image' => $message])
                ->withInput();
        }

        $imageUrlRules = ['nullable', 'url', 'max:500', 'regex:/^https?:\/\//i'];
        if (! $request->hasFile('image') && filled($request->input('image_url'))) {
            $imageUrlRules[] = new SafeExternalImageUrl(4 * 1024 * 1024);
        }

        $data = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                function ($attr, $value, $fail) {
                    $words = preg_split('/\s+/', trim((string) $value));
                    $words = array_values(array_filter($words, fn ($w) => $w !== ''));
                    if (count($words) < 3) {
                        $fail('Title must contain at least 3 words.');
                    }
                },
            ],

            'description' => [
                'required',
                'string',
                function ($attr, $value, $fail) {
                    $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $value)));
                    if (mb_strlen($text) < 30) {
                        $fail('Text must contain at least 30 characters of meaningful content.');
                    }
                },
            ],

            'goal_signatures' => ['required', 'integer', 'in:50,100,1000,5000,10000,50000,100000,500000,1000000,10000000'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('is_active', true)],

            'tags' => [
                'nullable',
                'string',
                'max:255',
                function ($attr, $value, $fail) {
                    $tags = collect(explode(',', (string) $value))
                        ->map(fn ($t) => trim($t))
                        ->filter()
                        ->values();

                    if ($tags->count() > 10) {
                        $fail('Tags: maximum 10 keywords.');
                    }

                    if ($tags->contains(fn ($t) => mb_strlen($t) > 30)) {
                        $fail('Tags: each keyword must be 30 characters or less.');
                    }
                },
            ],

            'image' => ['nullable', 'file', 'image', 'max:4096', 'mimes:jpg,jpeg,png,gif,webp'],
            'image_url' => $imageUrlRules,

            'youtube' => ['nullable', 'string', 'max:200', 'regex:/^(?:https?:\/\/(?:www\.)?(?:youtube\.com\/watch\?v=|youtu\.be\/)[\w_-]+|[\w_-]+)$/i'],

            'target' => ['nullable', 'string', 'max:255'],
            'community' => ['nullable', 'string', 'max:255'],
            'community_url' => ['nullable', 'url', 'max:500', 'regex:/^https?:\/\//i'],
            'city' => ['nullable', 'string', 'max:120'],
        ], [
            'image.prohibited_with' => 'Please choose either upload an image OR use an external image link (not both).',
            'image_url.prohibited_with' => 'Please choose either upload an image OR use an external image link (not both).',
        ]);

        if ($request->hasFile('image') && filled($data['image_url'] ?? null)) {
            $conflict = 'Please choose either upload an image OR use an external image link (not both).';

            return back()
                ->withErrors([
                    'image' => $conflict,
                    'image_url' => $conflict,
                ])
                ->withInput();
        }

        $newCoverImage = null;

        try {
            return DB::transaction(function () use ($request, $data, $locale, &$newCoverImage) {

                $tags = collect(explode(',', $data['tags'] ?? ''))
                    ->map(fn ($t) => trim($t))
                    ->filter()
                    ->take(10)
                    ->implode(',');

                $petition = new Petition;
                $petition->user_id = auth()->id();
                $petition->status = 'draft';
                $petition->goal_signatures = $data['goal_signatures'];
                $petition->category_id = $data['category_id'];
                $petition->target = $data['target'] ?? null;
                $petition->tags = $tags ?: null;
                $petition->city = $data['city'] ?? null;
                $petition->community = $data['community'] ?? null;
                $petition->community_url = $data['community_url'] ?? null;
                $petition->youtube_url = $this->youtubeId($data['youtube'] ?? null);
                $petition->image_url = $request->hasFile('image') ? null : ($data['image_url'] ?? null);

                if ($request->hasFile('image')) {
                    $path = $request->file('image')->store('petitions', 'public');
                    $newCoverImage = $path;
                    $abs = Storage::disk('public')->path($path);

                    try {
                        Image::read($abs)->scaleDown(width: 800, height: 800)->save($abs);
                        ImageVariants::ensure($abs);
                    } catch (\Throwable $e) {
                        throw ValidationException::withMessages([
                            'image' => 'The image could not be processed. Please upload a valid JPG, PNG, GIF, or WebP file.',
                        ]);
                    }

                    if (! Storage::disk('public')->exists($path)) {
                        throw ValidationException::withMessages([
                            'image' => 'The image failed to save. Please try again.',
                        ]);
                    }

                    $petition->cover_image = $path;
                }

                $petition->save();

                $slug = $this->makeUniqueSlug($data['title'], $locale);

                $petition->translations()->create([
                    'locale' => $locale,
                    'title' => $data['title'],
                    'slug' => $slug,
                    'description' => sanitizePetitionHtml($data['description']),
                ]);

                $user = auth()->user();

                $alreadySigned = Signature::query()
                    ->where('petition_id', $petition->id)
                    ->where(function ($q) use ($user) {
                        $q->where('user_id', $user->id)
                            ->orWhere('email', $user->email);
                    })
                    ->exists();

                if (! $alreadySigned) {
                    Signature::create([
                        'petition_id' => $petition->id,
                        'user_id' => $user->id,
                        'name' => $user->name ?? 'Anonymous',
                        'email' => $user->email,
                        'locale' => $locale,
                    ]);

                    DB::table('petitions')->where('id', $petition->id)->increment('signature_count');
                    PetitionCache::touch($petition);
                }

                toast('Petition created successfully.', 'success');

                return redirect()->route('petition.thanks', [
                    'locale' => $locale,
                    'slug' => $slug,
                    'id' => $petition->id,
                    'status' => 'created',
                ]);
            });
        } catch (\Throwable $e) {
            if ($newCoverImage) {
                Storage::disk('public')->delete($newCoverImage);
                ImageVariants::purge($newCoverImage);
            }

            throw $e;
        }
    }

    private function signAsAuthed(Request $request, string $locale, Petition $petition)
    {
        $data = $request->validate([
            'comment' => ['nullable', 'string', 'max:500'],
            'agree1' => ['required', 'in:agree'],
            'agree2' => ['required', 'in:agree'],
            'agree3' => ['required', 'in:agree'],
        ]);

        $u = auth()->user();

        try {
            return DB::transaction(function () use ($data, $locale, $petition, $u) {
                $sig = Signature::firstOrCreate(
                    ['petition_id' => $petition->id, 'email' => $u->email],
                    [
                        'user_id' => $u->id,
                        'name' => $u->name,
                        'locale' => $locale,
                        'text' => $data['comment'] ?? 'I support this petition',
                        'ip_address' => request()->ip(),
                    ]
                );

                if (! $sig->wasRecentlyCreated) {
                    toast('You already signed this petition.', 'info');
                }

                if ($sig->wasRecentlyCreated) {
                    DB::table('petitions')->where('id', $petition->id)->increment('signature_count');
                    PetitionCache::touch($petition);
                }

                session()->forget("sign.{$petition->id}");

                $tr = PetitionTranslation::query()
                    ->where('petition_id', $petition->id)
                    ->where('locale', $locale)
                    ->first()
                    ?? PetitionTranslation::query()->where('petition_id', $petition->id)->orderBy('id')->first();

                abort_if(! $tr, 404);

                AppLog::info(
                    'Petition signed',
                    'Petition ID: '.$petition->id.' | User: '.$u->email.' | IP: '.request()->ip(),
                    'petition.sign'
                );

                if ($sig->wasRecentlyCreated) {
                    toast('Thank you for signing!', 'success');
                }

                return redirect()->route('petition.thanks', [
                    'locale' => $tr->locale,
                    'slug' => $tr->slug,
                    'id' => $petition->id,
                    'status' => 1,
                ]);
            });
        } catch (QueryException $e) {
            // Concurrent duplicate sign (unique key race): treat as
            // already signed instead of an uncaught 500.
            if (($e->errorInfo[0] ?? null) !== '23000' && ! str_contains($e->getMessage(), 'UNIQUE')) {
                throw $e;
            }

            $sig = Signature::where('petition_id', $petition->id)
                ->where(function ($q) use ($u) {
                    $q->where('email', $u->email)->orWhere('user_id', $u->id);
                })
                ->first();

            if ($sig) {
                toast('You already signed this petition.', 'info');
            } else {
                throw $e;
            }

            $tr = PetitionTranslation::query()
                ->where('petition_id', $petition->id)
                ->where('locale', $locale)
                ->first()
                ?? PetitionTranslation::query()->where('petition_id', $petition->id)->orderBy('id')->first();

            abort_if(! $tr, 404);

            return redirect()->route('petition.thanks', [
                'locale' => $tr->locale,
                'slug' => $tr->slug,
                'id' => $petition->id,
                'status' => 1,
            ]);
        }
    }

    public function thanks(Request $request, string $locale, string $slug, int $id, $status = 0)
    {
        $locale = normalize_locale($locale);
        $defaultLocale = default_locale();

        $petition = Petition::with('category')->findOrFail($id);

        $tr = PetitionTranslation::where('petition_id', $petition->id)
            ->where('locale', $locale)
            ->first()
            ?? PetitionTranslation::where('petition_id', $petition->id)
                ->where('locale', $defaultLocale)
                ->first()
            ?? PetitionTranslation::where('petition_id', $petition->id)
                ->orderBy('id')
                ->first();

        abort_if(! $tr, 404);

        if ($tr->slug !== $slug || $tr->locale !== $locale) {
            return redirect()->route('petition.thanks', [
                'locale' => $tr->locale,
                'slug' => $tr->slug,
                'id' => $petition->id,
                'status' => $status,
            ]);
        }

        $mode = ((string) $status === 'created') ? 'created' : 'signed';

        $suggestions = Petition::query()
            ->select([
                'petitions.id',
                'petitions.signature_count',
            ])
            ->withTranslation([
                'base' => 'petitions',
                'base_key' => 'id',
                'translation_table' => 'petition_translations',
                'translation_key' => 'petition_id',
                'locale_alias' => 'pt_locale',
                'default_alias' => 'pt_default',
                'locale' => $locale,
                'default_locale' => $defaultLocale,
                'columns' => ['title' => 'tr_title', 'slug' => 'tr_slug'],
            ])
            ->where(function ($q) {
                $q->whereNotNull('pt_locale.title')
                    ->orWhereNotNull('pt_default.title');
            })
            ->where('petitions.status', 'published')
            ->where('petitions.is_active', 1)
            ->where('petitions.id', '!=', $petition->id)
            ->orderByDesc('petitions.signature_count')
            ->limit(5)
            ->get();

        $content = PageContent::where('page', 'petition_thanks')
            ->where('locale', $tr->locale)
            ->pluck('value', 'key');

        $claim = $request->session()->get('sign_claim');
        $canClaim = false;

        if ($claim) {
            $request->session()->keep(['sign_claim']);
            $claimUser = User::where('email', $claim['email'])->first();
            $canClaim = $claimUser && ! filled($claimUser->password);
        }

        return view('petition.thanks', compact(
            'petition',
            'suggestions',
            'locale',
            'mode',
            'status',
            'tr',
            'content',
            'claim',
            'canClaim'
        ));
    }

    public function claimAccount(Request $request, string $locale, string $slug, int $id)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'max:72'],
        ]);

        $claim = $request->session()->get('sign_claim');
        abort_unless($claim, 404);

        $email = $claim['email'];

        $user = User::lockForUpdate()->where('email', $email)->first();
        abort_unless($user, 404);

        if (filled($user->password)) {
            return back()->withErrors([
                'password' => 'This account already has a password — please log in.',
            ]);
        }

        $user->forceFill(['password' => Hash::make($request->password)])->save();
        $request->session()->forget('sign_claim');

        toast('Your password has been set. You can now log in.', 'success');

        return redirect()->route('login', ['locale' => $locale]);
    }

    public function signPage(Request $request, string $locale, string $slug, int $id)
    {
        $petition = Petition::query()
            ->whereKey($id)
            ->where('status', 'published')
            ->where('is_active', true)
            ->firstOrFail();

        $tr = PetitionTranslation::where('petition_id', $petition->id)
            ->where('locale', $locale)
            ->first()
            ?? PetitionTranslation::where('petition_id', $petition->id)
                ->orderBy('id')
                ->first();

        abort_if(! $tr, 404);

        if ($tr->slug !== $slug || $tr->locale !== $locale) {
            return redirect()->route('petition.sign.page', [
                'locale' => $tr->locale,
                'slug' => $tr->slug,
                'id' => $petition->id,
            ]);
        }

        $alreadySigned = auth()->check() && Signature::where('petition_id', $petition->id)
            ->where('email', auth()->user()->email)
            ->exists();

        if ($alreadySigned) {
            return redirect()->route('petition.show', [
                'locale' => $tr->locale,
                'slug' => $tr->slug,
                'id' => $petition->id,
            ]);
        }

        // GET renders the confirm form for already-authenticated users (the
        // OAuth petition flow returns here after auth); validation and the
        // session snapshot only apply to the actual POST. signAsAuthed()
        // re-validates the agreed flags on the sign POST itself.
        if ($request->isMethod('get')) {
            if (auth()->check()) {
                $content = PageContent::where('page', 'petition_sign')
                    ->where('locale', $locale)
                    ->pluck('value', 'key');

                return view('petition.sign_page', compact('petition', 'tr', 'locale', 'content'));
            }

            return redirect()->route('petition.show', compact('locale', 'slug', 'id'));
        }

        $rules = auth()->check()
            ? [
                'comment' => ['nullable', 'string', 'max:500'],
                'agree1' => ['required', 'in:agree'],
                'agree2' => ['required', 'in:agree'],
                'agree3' => ['required', 'in:agree'],
            ]
            : [
                'name' => ['required', 'string', 'max:60'],
                'email' => ['required', 'email', 'max:190'],
                'comment' => ['nullable', 'string', 'max:500'],
                'agree1' => ['required', 'in:on,1'],
                'agree2' => ['required', 'in:on,1'],
                'agree3' => ['required', 'in:on,1'],
            ];

        session(["sign.{$petition->id}" => $request->validate($rules)]);

        $content = PageContent::where('page', 'petition_sign')
            ->where('locale', $locale)
            ->pluck('value', 'key');

        return view('petition.sign_page', compact('petition', 'tr', 'locale', 'content'));
    }

    public function myPetitions(Request $request, string $locale)
    {
        $locale = normalize_locale($locale);
        $defaultLocale = default_locale();

        $u = auth()->user();
        $tab = $request->query('tab');

        $withTr = function ($q) use ($locale, $defaultLocale) {
            $q->withTranslation([
                'base' => 'petitions',
                'base_key' => 'id',
                'translation_table' => 'petition_translations',
                'translation_key' => 'petition_id',
                'locale_alias' => 'pt_locale',
                'default_alias' => 'pt_default',
                'locale' => $locale,
                'default_locale' => $defaultLocale,
                'columns' => ['title' => 'tr_title', 'slug' => 'tr_slug'],
            ])
                ->where(function ($q) {
                    $q->whereNotNull('pt_locale.title')
                        ->orWhereNotNull('pt_default.title');
                });
        };

        $signedBase = Petition::query()
            ->select([
                'petitions.*',
                'signatures.created_at as signed_at',
            ])
            ->join('signatures', 'signatures.petition_id', '=', 'petitions.id')
            ->where('signatures.email', $u->email)
            ->tap($withTr)
            ->orderByDesc('signatures.created_at');

        $createdBase = Petition::query()
            ->select(['petitions.*'])
            ->where('petitions.user_id', $u->id)
            ->tap($withTr)
            ->latest('petitions.created_at');

        if ($tab === 'signed') {
            $signed = $signedBase->paginate(10)->withQueryString();
            $created = $createdBase->limit(5)->get();
        } elseif ($tab === 'created') {
            $signed = $signedBase->limit(5)->get();
            $created = $createdBase->paginate(10)->withQueryString();
        } else {
            $signed = $signedBase->limit(5)->get();
            $created = $createdBase->limit(5)->get();
        }

        return view('petition.my-petitions', compact('locale', 'signed', 'created', 'tab'));
    }

    public function edit(Request $request, string $locale, string $slug, int $id)
    {
        $petition = Petition::query()->with('category')->findOrFail($id);
        $this->requireEditable($petition);

        $trOrRedirect = $this->resolveTrOrRedirect('petition.edit', $locale, $slug, $petition);
        if ($trOrRedirect instanceof RedirectResponse) {
            return $trOrRedirect;
        }
        $tr = $trOrRedirect;

        $defaultLocale = default_locale();

        $categories = Category::query()
            ->select(['categories.id'])
            ->withTranslation([
                'base' => 'categories',
                'base_key' => 'id',
                'translation_table' => 'category_translations',
                'translation_key' => 'category_id',
                'locale_alias' => 'ct_locale',
                'default_alias' => 'ct_default',
                'locale' => $locale,
                'default_locale' => $defaultLocale,
                'columns' => ['name' => 'name', 'slug' => 'slug'],
            ])
            ->where('categories.is_active', true)
            ->orderBy('name')
            ->get();

        $mode = 'edit';

        return view('petition.create', compact('petition', 'tr', 'locale', 'categories', 'mode'));
    }

    public function update(Request $request, string $locale, string $slug, int $id)
    {
        $petition = Petition::query()->findOrFail($id);
        $this->requireEditable($petition);

        $trOrRedirect = $this->resolveTrOrRedirect('petition.edit', $locale, $slug, $petition);
        if ($trOrRedirect instanceof RedirectResponse) {
            return $trOrRedirect;
        }
        $tr = $trOrRedirect;

        $imageUrlRules = ['nullable', 'url', 'max:500', 'regex:/^https?:\/\//i'];
        $submittedUrl = $request->input('image_url');
        if (! $request->hasFile('image')
            && filled($submittedUrl)
            && $submittedUrl !== $petition->image_url) {
            $imageUrlRules[] = new SafeExternalImageUrl(4 * 1024 * 1024);
        }

        $data = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                function ($attr, $value, $fail) {
                    $words = preg_split('/\s+/', trim((string) $value));
                    $words = array_values(array_filter($words, fn ($w) => $w !== ''));
                    if (count($words) < 3) {
                        $fail('Title must contain at least 3 words.');
                    }
                },
            ],

            'description' => [
                'required',
                'string',
                function ($attr, $value, $fail) {
                    $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $value)));
                    if (mb_strlen($text) < 30) {
                        $fail('Text must contain at least 30 characters of meaningful content.');
                    }
                },
            ],

            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('is_active', true)],

            'tags' => [
                'nullable',
                'string',
                'max:255',
                function ($attr, $value, $fail) {
                    $tags = collect(explode(',', (string) $value))
                        ->map(fn ($t) => trim($t))
                        ->filter()
                        ->values();

                    if ($tags->count() > 10) {
                        $fail('Tags: maximum 10 keywords.');
                    }
                    if ($tags->contains(fn ($t) => mb_strlen($t) > 30)) {
                        $fail('Tags: each keyword must be 30 characters or less.');
                    }
                },
            ],

            'image' => ['nullable', 'image', 'max:4096', 'mimes:jpg,jpeg,png,gif,webp'],
            'image_url' => $imageUrlRules,

            'youtube' => ['nullable', 'string', 'max:200', 'regex:/^(?:https?:\/\/(?:www\.)?(?:youtube\.com\/watch\?v=|youtu\.be\/)[\w_-]+|[\w_-]+)$/i'],

            'remove_image' => ['nullable', 'boolean'],

            'target' => ['nullable', 'string', 'max:255'],
            'community' => ['nullable', 'string', 'max:255'],
            'community_url' => ['nullable', 'url', 'max:500', 'regex:/^https?:\/\//i'],
            'city' => ['nullable', 'string', 'max:120'],
        ]);

        if ($request->hasFile('image') && filled($data['image_url'] ?? null)) {
            toast('Please choose either upload or external image (not both).', 'error');

            return back()
                // ->withErrors([
                //     'image' => 'Please choose either upload an image OR use an external image link (not both).',
                //     'image_url' => 'Please choose either upload an image OR use an external image link (not both).',
                // ])
                ->withInput();
        }

        $oldCoverImage = $petition->cover_image;
        $newCoverImage = null;

        try {
            $response = DB::transaction(function () use ($request, $data, $petition, $tr, &$newCoverImage) {

                $tags = collect(explode(',', $data['tags'] ?? ''))
                    ->map(fn ($t) => trim($t))
                    ->filter()
                    ->take(10)
                    ->implode(',');

                $petition->category_id = $data['category_id'];
                $petition->target = $data['target'] ?? null;
                $petition->tags = $tags ?: null;
                $petition->city = $data['city'] ?? null;
                $petition->community = $data['community'] ?? null;
                $petition->community_url = $data['community_url'] ?? null;

                $petition->youtube_url = $this->youtubeId($data['youtube'] ?? null);

                $petition->image_url = $data['image_url'] ?? null;

                if ($request->hasFile('image')) {
                    $path = $request->file('image')->store('petitions', 'public');
                    $newCoverImage = $path;
                    $abs = Storage::disk('public')->path($path);

                    Image::read($abs)->scaleDown(width: 800, height: 800)->save($abs);
                    ImageVariants::ensure($abs);

                    $petition->cover_image = $newCoverImage;

                    $petition->image_url = null;
                } elseif ($request->boolean('remove_image') || filled($petition->image_url)) {
                    $petition->cover_image = null;
                }

                $petition->save();

                $tr->title = $data['title'];
                $tr->slug = $this->makeUniqueSlug($data['title'], $tr->locale, $tr->id);
                $tr->description = sanitizePetitionHtml($data['description']);
                $tr->save();

                toast('Petition updated successfully.', 'success');

                return redirect()->route('petition.show', [
                    'locale' => $tr->locale,
                    'slug' => $tr->slug,
                    'id' => $petition->id,
                ]);
            });
        } catch (\Throwable $e) {
            if ($newCoverImage) {
                Storage::disk('public')->delete($newCoverImage);
                ImageVariants::purge($newCoverImage);
            }

            throw $e;
        }

        if ($oldCoverImage && $oldCoverImage !== $petition->cover_image && ! Str::startsWith($oldCoverImage, ['http://', 'https://'])) {
            Storage::disk('public')->delete($oldCoverImage);
            ImageVariants::purge($oldCoverImage);
        }

        return $response;
    }

    public function downloadTxt(Request $request, string $locale, string $slug, int $id)
    {
        $petition = Petition::query()->findOrFail($id);
        $this->requireOwner($petition);

        $trOrRedirect = $this->resolveTrOrRedirect('petition.show', $locale, $slug, $petition);
        if ($trOrRedirect instanceof RedirectResponse) {
            return $trOrRedirect;
        }
        $tr = $trOrRedirect;

        $signatures = Signature::query()
            ->where('petition_id', $petition->id)
            ->orderBy('created_at')
            ->limit(100)
            ->get();

        $lines = [];

        $lines[] = strtoupper($tr->title ?? 'Petition');
        $lines[] = 'URL: '.url("/{$locale}/petition/{$tr->slug}/{$petition->id}");
        $lines[] = '';
        $lines[] = '--- DESCRIPTION ---';
        $lines[] = trim(strip_tags($tr->description ?? ''));
        $lines[] = '';
        $lines[] = 'Total signatures: '.(int) ($petition->signature_count ?? 0);
        $lines[] = '';

        $lines[] = '--- SIGNATURES (latest '.$signatures->count().') ---';

        if ($signatures->isEmpty()) {
            $lines[] = 'No signatures yet.';
        } else {
            foreach ($signatures as $i => $sig) {
                $num = $i + 1;
                $name = $sig->name ?: 'Anonymous';
                $date = optional($sig->created_at)->format('Y-m-d');
                $comment = trim($sig->text ?? 'I support this petition');

                $lines[] = "{$num}. {$name} ({$date})";
                $lines[] = "   {$comment}";
            }
        }

        $txt = implode("\n", $lines);
        $filename = 'petition-'.$petition->id.'.txt';

        return response($txt)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    public function downloadPdf(Request $request, string $locale, string $slug, int $id)
    {
        $petition = Petition::query()->findOrFail($id);
        $this->requireOwner($petition);

        $trOrRedirect = $this->resolveTrOrRedirect('petition.show', $locale, $slug, $petition);
        if ($trOrRedirect instanceof RedirectResponse) {
            return $trOrRedirect;
        }
        $tr = $trOrRedirect;

        $signatures = Signature::query()
            ->where('petition_id', $petition->id)
            ->orderBy('created_at')
            ->limit(100)
            ->get();

        try {
            return Pdf::loadView('petition.pdf', compact(
                'petition',
                'tr',
                'locale',
                'signatures'
            ))
                ->download("petition-{$petition->id}.pdf");
        } catch (\Exception $e) {
            AppLog::error('PDF generation failed', $e->getMessage(), 'petition.pdf');
            toast('PDF generation failed. Downloading as text instead.', 'warning');

            return $this->downloadTxt($request, $locale, $slug, $id);
        }
    }

    private function makeUniqueSlug(string $title, string $locale, ?int $ignoreTranslationId = null): string
    {
        $base = Str::slug($title);
        $base = $base ?: 'petition';

        if (Str::length($base) > 255) {
            $base = rtrim(Str::substr($base, 0, 255), '-');
        }

        $slug = $base;
        $i = 1;

        while (PetitionTranslation::where('locale', $locale)
            ->where('slug', $slug)
            ->when($ignoreTranslationId, fn ($query) => $query->whereKeyNot($ignoreTranslationId))
            ->exists()) {
            $suffix = '-'.$i++;
            $trimmed = Str::length($base) + Str::length($suffix) > 255
                ? rtrim(Str::substr($base, 0, 255 - Str::length($suffix)), '-')
                : $base;
            $slug = $trimmed.$suffix;
        }

        return $slug;
    }

    private function youtubeId(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (preg_match('/(?:v=|youtu\.be\/)([\w_-]+)/i', $value, $matches)) {
            return $matches[1];
        }

        return $value;
    }

    public function signatures(Request $request, string $locale, string $slug, int $id)
    {
        $locale = normalize_locale($locale);
        $defaultLocale = default_locale();

        $petition = Petition::with('category', 'user')
            ->where('id', $id)
            ->where('status', 'published')
            ->where('is_active', 1)
            ->firstOrFail();

        $tr = PetitionTranslation::where('petition_id', $petition->id)
            ->where('locale', $locale)
            ->first()
            ?? PetitionTranslation::where('petition_id', $petition->id)
                ->where('locale', $defaultLocale)
                ->first()
            ?? PetitionTranslation::where('petition_id', $petition->id)
                ->orderBy('id')
                ->first();

        abort_if(! $tr, 404);

        if ($tr->slug !== $slug || $tr->locale !== $locale) {
            return redirect()->route('petition.signatures', [
                'locale' => $tr->locale,
                'slug' => $tr->slug,
                'id' => $petition->id,
            ]);
        }

        $page = max(1, min((int) request('page', 1), 500));

        $signatures = Signature::with('user')
            ->where('petition_id', $petition->id)
            ->where('confirmed', true)
            ->where('is_spam', false)
            ->orderByDesc('created_at')
            ->paginate(25, ['*'], 'page', $page)
            ->withQueryString();

        $goalTotal = (int) ($petition->goal_signatures ?? 100);
        $goalCurrent = (int) ($petition->signature_count ?? 0);
        $pct = $goalTotal > 0 ? min(100, round(($goalCurrent / $goalTotal) * 100)) : 0;

        return view('petition.signatures', compact(
            'petition',
            'tr',
            'locale',
            'signatures',
            'goalTotal',
            'goalCurrent',
            'pct'
        ));
    }
}
