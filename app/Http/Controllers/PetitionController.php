<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PageContent;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\Signature;
use App\Models\User;
use App\Models\UserLevel;
use App\Support\AppLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use App\Support\Spam;
use Illuminate\Support\Str;

class PetitionController extends Controller
{

    private function requireOwner(Petition $petition): void
    {
        abort_unless(auth()->check() && (int)$petition->user_id === (int)auth()->id(), 403);
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
                'slug'   => $tr->slug,
                'id'     => $petition->id,
            ], $extra));
        }

        return $tr;
    }

    public function index(string $locale)
    {
        $locale = normalize_locale($locale);
        $defaultLocale = default_locale();

        $page = (int) request('page', 1);
        $cacheKey = "petitions:index:{$locale}:page:{$page}";

        $petitions = cache()->remember($cacheKey, now()->addSeconds(60), function () use ($locale, $defaultLocale) {

            return Petition::query()
                ->select([
                    'petitions.id',
                    'petitions.signature_count',
                    'petitions.goal_signatures',
                    'petitions.category_id',
                    'petitions.cover_image',
                    DB::raw("COALESCE(pt_locale.title, pt_default.title) as tr_title"),
                    DB::raw("COALESCE(pt_locale.slug, pt_default.slug) as tr_slug"),
                ])
                ->leftJoin('petition_translations as pt_locale', function ($join) use ($locale) {
                    $join->on('pt_locale.petition_id', '=', 'petitions.id')
                        ->where('pt_locale.locale', '=', $locale);
                })
                ->leftJoin('petition_translations as pt_default', function ($join) use ($defaultLocale) {
                    $join->on('pt_default.petition_id', '=', 'petitions.id')
                        ->where('pt_default.locale', '=', $defaultLocale);
                })
                ->where(function ($q) {
                    $q->whereNotNull('pt_locale.title')
                        ->orWhereNotNull('pt_default.title');
                })
                ->where('petitions.status', 'published')
                ->where('petitions.is_active', 1)
                ->orderByDesc('petitions.id')
                ->paginate(15);
        });

        $petitions->withQueryString();

        return view('pages.petitions-list', [
            'pageTitle' => 'All the petitions',
            'heading' => 'Petitions',
            'petitions' => $petitions,
            'petitionTitle' => fn($row) => $row->tr_title,
            'petitionUrl' => fn($row) => lroute('petition.show', [
                'slug' => $row->tr_slug,
                'id'   => $row->id,
            ]),
        ]);
    }

    public function show(Request $request, string $locale, string $slug, int $id)
    {
        $locale = normalize_locale($locale);
        $defaultLocale = default_locale();

        $petition = Petition::query()
            ->with('category')
            ->where('id', $id)
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('status', 'published')->where('is_active', 1);
                })
                    ->orWhere(function ($q2) {
                        $q2->where('status', 'draft')
                            ->where('user_id', auth()->id() ?? 0);
                    });
            })
            ->firstOrFail();

        $tr = PetitionTranslation::query()
            ->where('petition_id', $petition->id)
            ->where('locale', $locale)
            ->first()
            ?? PetitionTranslation::query()
            ->where('petition_id', $petition->id)
            ->where('locale', $defaultLocale)
            ->first();

        abort_if(! $tr, 404);

        if ($tr->slug !== $slug || $tr->locale !== $locale) {
            return redirect()->route('petition.show', [
                'locale' => $tr->locale,
                'slug'   => $tr->slug,
                'id'     => $petition->id,
            ]);
        }

        $hasSigned = auth()->check()
            ? Signature::where('petition_id', $petition->id)
            ->where('email', auth()->user()->email)
            ->exists()
            : false;

        $goalTotal = (int) ($petition->goal_signatures ?? 100);
        $goalCurrent = (int) ($petition->signature_count ?? 0);
        $pct = $goalTotal > 0 ? min(100, round(($goalCurrent / $goalTotal) * 100)) : 0;

        $latest = Signature::where('petition_id', $petition->id)
            ->latest('created_at')
            ->limit(25)
            ->get();

        $directLink = lroute('petition.show', [
            'slug' => $tr->slug,
            'id'   => $petition->id,
        ]);

        $content = PageContent::where('page', 'petition_show')
            ->where('locale', $tr->locale)
            ->pluck('value', 'key');

        $formContent = PageContent::where('page', 'petition_sign_form')
            ->where('locale', $tr->locale)
            ->pluck('value', 'key');

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
            'formContent'
        ));
    }

    public function sign(Request $request, string $locale, string $slug, int $id)
    {
        $petition = Petition::query()->findOrFail($id);

        $tr = PetitionTranslation::query()
            ->where('petition_id', $petition->id)
            ->where('locale', $locale)
            ->firstOrFail();

        if (auth()->check()) {
            return $this->signAsAuthed($request, $locale, $petition);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'surname' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'string', 'min:8', 'max:72'],
            'comment' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:80'],
            'nickname' => ['nullable', 'string', 'max:80'],

            'agree1' => ['required', 'in:agree'],
            'agree2' => ['required', 'in:agree'],
            'agree3' => ['required', 'in:agree'],
        ]);

        $spamText = implode(' ', [
            $data['name'] ?? '',
            $data['surname'] ?? '',
            $data['comment'] ?? '',
            $data['city'] ?? '',
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

        if (User::where('email', $email)->exists()) {
            $loginUrl = url("/{$locale}/login?email=" . urlencode($email) . "&redirect=" . urlencode(url()->previous()));

            toast('This email is already registered. Please sign in first.', 'warning');

            return back()
                ->withInput($request->except('password'))
                ->with('login_url', $loginUrl);
        }

        return DB::transaction(function () use ($data, $email, $locale, $petition, $tr) {
            $userLevel = UserLevel::where('name', 'user')->first();

            $user = User::create([
                'name'       => trim($data['name'] . ' ' . $data['surname']),
                'first_name' => $data['name'],
                'last_name'  => $data['surname'],
                'email'      => $email,
                'password'   => Hash::make($data['password']),
                'locale'     => $locale,
                'ip'         => request()->ip(),
                'level_id'   => $userLevel?->id,
                'verified'   => true,
                'nickname'   => $data['nickname'] ?? null,
                'city'       => $data['city'] ?? null,
            ]);

            $sig = Signature::firstOrCreate(
                ['petition_id' => $petition->id, 'email' => $email],
                [
                    'user_id' => $user->id,
                    'name'    => $data['nickname'] ?: $user->name,
                    'locale'  => $locale,
                    // 'city'    => $data['city'] ?? null,
                    'text'    => $data['comment'] ?? 'I support this petition',
                    'ip_address' => request()->ip(),
                ]
            );

            if ($sig->wasRecentlyCreated) {
                $petition->increment('signature_count');
            }

            AppLog::info(
                'Petition signed',
                'Petition ID: ' . $petition->id . ' | User: ' . $email . ' | IP: ' . request()->ip(),
                'petition.sign'
            );

            session()->forget('sign');

            return redirect()->route('petition.thanks', [
                'locale' => $locale,
                'slug'   => $tr->slug,
                'id'     => $petition->id,
                'status' => 0,
            ]);
        });
    }

    public function create(string $locale)
    {
        $defaultLocale = default_locale();

        $categories = Category::query()
            ->select(['categories.id'])
            ->selectRaw("COALESCE(ct_locale.name, ct_default.name) as name")
            ->selectRaw("COALESCE(ct_locale.slug, ct_default.slug) as slug")
            ->leftJoin('category_translations as ct_locale', function ($join) use ($locale) {
                $join->on('ct_locale.category_id', '=', 'categories.id')
                    ->where('ct_locale.locale', '=', $locale);
            })
            ->leftJoin('category_translations as ct_default', function ($join) use ($defaultLocale) {
                $join->on('ct_default.category_id', '=', 'categories.id')
                    ->where('ct_default.locale', '=', $defaultLocale);
            })
            ->where('categories.is_active', true)
            ->orderBy('name')
            ->get();

        return view('petition.create', compact('locale', 'categories'));
    }

    public function store(Request $request, string $locale)
    {
        $data = $request->validate([
            'title' => [
                'required',
                'string',
                'max:190',
                function ($attr, $value, $fail) {
                    $words = preg_split('/\s+/', trim((string) $value));
                    $words = array_values(array_filter($words, fn($w) => $w !== ''));
                    if (count($words) < 3) {
                        $fail('Title must contain at least 3 words.');
                    }
                },
            ],

            'description' => [
                'required',
                'string',
                function ($attr, $value, $fail) {
                    $text = trim(preg_replace('/\s+/', ' ', strip_tags((string)$value)));
                    if (mb_strlen($text) < 30) {
                        $fail('Text must contain at least 30 characters of meaningful content.');
                    }
                },
            ],

            'goal_signatures' => ['required', 'integer', 'in:50,100,1000,5000,10000,50000,100000,500000,1000000,10000000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],

            'tags' => [
                'nullable',
                'string',
                'max:255',
                function ($attr, $value, $fail) {
                    $tags = collect(explode(',', (string) $value))
                        ->map(fn($t) => trim($t))
                        ->filter()
                        ->values();

                    if ($tags->count() > 10) {
                        $fail('Tags: maximum 10 keywords.');
                    }

                    if ($tags->contains(fn($t) => mb_strlen($t) > 30)) {
                        $fail('Tags: each keyword must be 30 characters or less.');
                    }
                },
            ],

            'image' => ['nullable', 'image', 'max:4096'],
            'image_url' => [
                'nullable',
                'url',
                'max:500',
                function ($attr, $value, $fail) {
                    $path = strtolower(parse_url((string) $value, PHP_URL_PATH) ?? '');
                    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];
                    $ext = pathinfo($path, PATHINFO_EXTENSION);
                    if (!$ext || !in_array($ext, $allowed, true)) {
                        $fail('Image URL must point to a valid image file (jpg, jpeg, png, gif, webp, avif).');
                    }
                },
            ],

            'youtube' => ['nullable', 'url', 'max:200'],

            'target' => ['nullable', 'string', 'max:190'],
            'community' => ['nullable', 'string', 'max:190'],
            'community_url' => ['nullable', 'url', 'max:500'],
            'city' => ['nullable', 'string', 'max:120'],
        ], [
            'image.prohibited_with' => 'Please choose either upload an image OR use an external image link (not both).',
            'image_url.prohibited_with' => 'Please choose either upload an image OR use an external image link (not both).',
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

        return DB::transaction(function () use ($request, $data, $locale) {

            $tags = collect(explode(',', $data['tags'] ?? ''))
                ->map(fn($t) => trim($t))
                ->filter()
                ->take(10)
                ->implode(',');

            $petition = new Petition();
            $petition->user_id = auth()->id();
            $petition->status = 'draft';
            $petition->goal_signatures = $data['goal_signatures'];
            $petition->category_id = $data['category_id'];
            $petition->target = $data['target'] ?? null;
            $petition->tags = $tags ?: null;
            $petition->city = $data['city'] ?? null;
            $petition->community = $data['community'] ?? null;
            $petition->community_url = $data['community_url'] ?? null;
            $petition->youtube_url = $data['youtube'] ?? null;
            $petition->image_url = $data['image_url'] ?? null;

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('petitions', 'public');
                $abs  = Storage::disk('public')->path($path);

                Image::read($abs)
                    ->cover(1200, 630)
                    ->toJpeg(82)
                    ->save($abs);

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

                $petition->increment('signature_count');
            }

            toast('Petition created successfully.', 'success');

            return redirect()->route('petition.thanks', [
                'locale' => $locale,
                'slug'   => $slug,
                'id'     => $petition->id,
                'status'   => 'created',
            ]);
        });
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
            $petition->increment('signature_count');
        }

        session()->forget('sign');

        $tr = PetitionTranslation::query()
            ->where('petition_id', $petition->id)
            ->where('locale', $locale)
            ->first()
            ?? PetitionTranslation::query()->where('petition_id', $petition->id)->orderBy('id')->first();

        abort_if(! $tr, 404);

        AppLog::info(
            'Petition signed',
            'Petition ID: ' . $petition->id . ' | User: ' . $u->email . ' | IP: ' . request()->ip(),
            'petition.sign'
        );

        toast('Thank you for signing!', 'success');

        return redirect()->route('petition.thanks', [
            'locale' => $tr->locale,
            'slug' => $tr->slug,
            'id' => $petition->id,
            'status' => 0,
        ]);
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
            ->first();

        abort_if(! $tr, 404);

        if ($tr->slug !== $slug || $tr->locale !== $locale) {
            return redirect()->route('petition.thanks', [
                'locale' => $tr->locale,
                'slug'   => $tr->slug,
                'id'     => $petition->id,
                'status' => $status,
            ]);
        }

        $mode = ((string) $status === 'created') ? 'created' : 'signed';

        $suggestions = Petition::query()
            ->select([
                'petitions.id',
                'petitions.signature_count',
                DB::raw("COALESCE(pt_locale.title, pt_default.title) as tr_title"),
                DB::raw("COALESCE(pt_locale.slug, pt_default.slug) as tr_slug"),
            ])
            ->leftJoin('petition_translations as pt_locale', function ($join) use ($locale) {
                $join->on('pt_locale.petition_id', '=', 'petitions.id')
                    ->where('pt_locale.locale', '=', $locale);
            })
            ->leftJoin('petition_translations as pt_default', function ($join) use ($defaultLocale) {
                $join->on('pt_default.petition_id', '=', 'petitions.id')
                    ->where('pt_default.locale', '=', $defaultLocale);
            })
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

        return view('petition.thanks', compact(
            'petition',
            'suggestions',
            'locale',
            'mode',
            'tr',
            'content'
        ));
    }

    public function signPage(Request $request, string $locale, string $slug, int $id)
    {
        if (! auth()->check()) {
            return redirect()->route('petition.show', compact('locale', 'slug', 'id'));
        }

        $petition = Petition::findOrFail($id);

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
                'slug'   => $tr->slug,
                'id'     => $petition->id,
            ]);
        }

        $alreadySigned = Signature::where('petition_id', $petition->id)
            ->where('email', auth()->user()->email)
            ->exists();

        if ($alreadySigned) {
            return redirect()->route('petition.show', [
                'locale' => $tr->locale,
                'slug'   => $tr->slug,
                'id'     => $petition->id,
            ]);
        }

        session([
            'sign.comment' => $request->input('comment'),
            'sign.agree1'  => $request->input('agree1', 'agree'),
            'sign.agree2'  => $request->input('agree2', 'agree'),
            'sign.agree3'  => $request->input('agree3', 'agree'),
        ]);

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
            $q->leftJoin('petition_translations as pt_locale', function ($join) use ($locale) {
                $join->on('pt_locale.petition_id', '=', 'petitions.id')
                    ->where('pt_locale.locale', '=', $locale);
            })
                ->leftJoin('petition_translations as pt_default', function ($join) use ($defaultLocale) {
                    $join->on('pt_default.petition_id', '=', 'petitions.id')
                        ->where('pt_default.locale', '=', $defaultLocale);
                })
                ->addSelect([
                    DB::raw("COALESCE(pt_locale.title, pt_default.title) as tr_title"),
                    DB::raw("COALESCE(pt_locale.slug, pt_default.slug) as tr_slug"),
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
        $this->requireOwner($petition);

        $trOrRedirect = $this->resolveTrOrRedirect('petition.edit', $locale, $slug, $petition);
        if ($trOrRedirect instanceof \Illuminate\Http\RedirectResponse) return $trOrRedirect;
        $tr = $trOrRedirect;

        $defaultLocale = default_locale();

        $categories = Category::query()
            ->select(['categories.id'])
            ->selectRaw("COALESCE(ct_locale.name, ct_default.name) as name")
            ->selectRaw("COALESCE(ct_locale.slug, ct_default.slug) as slug")
            ->leftJoin('category_translations as ct_locale', function ($join) use ($locale) {
                $join->on('ct_locale.category_id', '=', 'categories.id')
                    ->where('ct_locale.locale', '=', $locale);
            })
            ->leftJoin('category_translations as ct_default', function ($join) use ($defaultLocale) {
                $join->on('ct_default.category_id', '=', 'categories.id')
                    ->where('ct_default.locale', '=', $defaultLocale);
            })
            ->where('categories.is_active', true)
            ->orderBy('name')
            ->get();

        $mode = 'edit';

        return view('petition.create', compact('petition', 'tr', 'locale', 'categories', 'mode'));
    }

    public function update(Request $request, string $locale, string $slug, int $id)
    {
        $petition = Petition::query()->findOrFail($id);
        $this->requireOwner($petition);

        $trOrRedirect = $this->resolveTrOrRedirect('petition.edit', $locale, $slug, $petition);
        if ($trOrRedirect instanceof \Illuminate\Http\RedirectResponse) return $trOrRedirect;
        $tr = $trOrRedirect;

        $data = $request->validate([
            'title' => [
                'required',
                'string',
                'max:190',
                function ($attr, $value, $fail) {
                    $words = preg_split('/\s+/', trim((string) $value));
                    $words = array_values(array_filter($words, fn($w) => $w !== ''));
                    if (count($words) < 3) $fail('Title must contain at least 3 words.');
                },
            ],

            'description' => [
                'required',
                'string',
                function ($attr, $value, $fail) {
                    $text = trim(preg_replace('/\s+/', ' ', strip_tags((string)$value)));
                    if (mb_strlen($text) < 30) $fail('Text must contain at least 30 characters of meaningful content.');
                },
            ],

            'goal_signatures' => ['required', 'integer', 'in:50,100,1000,5000,10000,50000,100000,500000,1000000,10000000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],

            'tags' => [
                'nullable',
                'string',
                'max:255',
                function ($attr, $value, $fail) {
                    $tags = collect(explode(',', (string) $value))
                        ->map(fn($t) => trim($t))
                        ->filter()
                        ->values();

                    if ($tags->count() > 10) $fail('Tags: maximum 10 keywords.');
                    if ($tags->contains(fn($t) => mb_strlen($t) > 30)) $fail('Tags: each keyword must be 30 characters or less.');
                },
            ],

            'image' => ['nullable', 'image', 'max:4096'],
            'image_url' => ['nullable', 'url', 'max:500'],

            'youtube' => ['nullable', 'url', 'max:200'],

            'target' => ['nullable', 'string', 'max:190'],
            'community' => ['nullable', 'string', 'max:190'],
            'community_url' => ['nullable', 'url', 'max:500'],
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

        return DB::transaction(function () use ($request, $data, $locale, $petition, $tr) {

            $tags = collect(explode(',', $data['tags'] ?? ''))
                ->map(fn($t) => trim($t))
                ->filter()
                ->take(10)
                ->implode(',');

            $petition->goal_signatures = $data['goal_signatures'];
            $petition->category_id = $data['category_id'];
            $petition->target = $data['target'] ?? null;
            $petition->tags = $tags ?: null;
            $petition->city = $data['city'] ?? null;
            $petition->community = $data['community'] ?? null;
            $petition->community_url = $data['community_url'] ?? null;

            $petition->youtube_url = $data['youtube'] ?? null;

            $petition->image_url = $data['image_url'] ?? null;

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('petitions', 'public');
                $abs  = Storage::disk('public')->path($path);

                Image::read($abs)
                    ->cover(1200, 630)
                    ->toJpeg(82)
                    ->save($abs);

                $petition->cover_image = $path;

                $petition->image_url = null;
            }

            $petition->save();

            $tr->title = $data['title'];
            $tr->description = sanitizePetitionHtml($data['description']);
            $tr->save();

            toast('Petition updated successfully.', 'success');

            return redirect()->route('petition.show', [
                'locale' => $tr->locale,
                'slug'   => $tr->slug,
                'id'     => $petition->id,
            ]);
        });
    }

    public function downloadTxt(Request $request, string $locale, string $slug, int $id)
    {
        $petition = Petition::query()->findOrFail($id);
        $this->requireOwner($petition);

        $trOrRedirect = $this->resolveTrOrRedirect('petition.show', $locale, $slug, $petition);
        if ($trOrRedirect instanceof \Illuminate\Http\RedirectResponse) return $trOrRedirect;
        $tr = $trOrRedirect;

        $signatures = Signature::query()
            ->where('petition_id', $petition->id)
            ->orderBy('created_at')
            ->limit(100)
            ->get();

        $lines = [];

        $lines[] = strtoupper($tr->title ?? 'Petition');
        $lines[] = 'URL: ' . url("/{$locale}/petition/{$tr->slug}/{$petition->id}");
        $lines[] = '';
        $lines[] = '--- DESCRIPTION ---';
        $lines[] = trim(strip_tags($tr->description ?? ''));
        $lines[] = '';
        $lines[] = 'Total signatures: ' . (int) ($petition->signature_count ?? 0);
        $lines[] = '';

        $lines[] = '--- SIGNATURES (latest ' . $signatures->count() . ') ---';

        if ($signatures->isEmpty()) {
            $lines[] = 'No signatures yet.';
        } else {
            foreach ($signatures as $i => $sig) {
                $num  = $i + 1;
                $name = $sig->name ?: 'Anonymous';
                $date = optional($sig->created_at)->format('Y-m-d');
                $comment = trim($sig->comment ?? 'I support this petition');

                $lines[] = "{$num}. {$name} ({$date})";
                $lines[] = "   {$comment}";
            }
        }

        $txt = implode("\n", $lines);
        $filename = 'petition-' . $petition->id . '.txt';

        return response($txt)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function downloadPdf(Request $request, string $locale, string $slug, int $id)
    {
        $petition = Petition::query()->findOrFail($id);
        $this->requireOwner($petition);

        $trOrRedirect = $this->resolveTrOrRedirect('petition.show', $locale, $slug, $petition);
        if ($trOrRedirect instanceof \Illuminate\Http\RedirectResponse) return $trOrRedirect;
        $tr = $trOrRedirect;

        $signatures = Signature::query()
            ->where('petition_id', $petition->id)
            ->orderBy('created_at')
            ->limit(100)
            ->get();

        return Pdf::loadView('petition.pdf', compact(
            'petition',
            'tr',
            'locale',
            'signatures'
        ))
            ->download("petition-{$petition->id}.pdf");
    }

    private function makeUniqueSlug(string $title, string $locale): string
    {
        $base = Str::slug($title);
        $base = $base ?: 'petition';

        $slug = $base;
        $i = 1;

        while (PetitionTranslation::where('locale', $locale)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    public function signatures(Request $request, string $locale, string $slug, int $id)
    {
        $locale        = normalize_locale($locale);
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
            ->first();

        abort_if(! $tr, 404);

        if ($tr->slug !== $slug || $tr->locale !== $locale) {
            return redirect()->route('petition.signatures', [
                'locale' => $tr->locale,
                'slug'   => $tr->slug,
                'id'     => $petition->id,
            ]);
        }

        $signatures = Signature::with('user')
            ->where('petition_id', $petition->id)
            ->where('is_spam', false)
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $goalTotal   = (int) ($petition->goal_signatures ?? 100);
        $goalCurrent = (int) ($petition->signature_count ?? 0);
        $pct         = $goalTotal > 0 ? min(100, round(($goalCurrent / $goalTotal) * 100)) : 0;

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
