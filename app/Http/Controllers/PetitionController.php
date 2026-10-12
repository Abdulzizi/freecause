<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClaimAccountRequest;
use App\Http\Requests\PetitionRequest;
use App\Models\Category;
use App\Models\Language;
use App\Models\PageContent;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\Signature;
use App\Models\User;
use App\Services\PetitionSigning;
use App\Services\PetitionStats;
use App\Support\AppLog;
use App\Support\CacheKeys;
use App\Support\ImageVariants;
use App\Support\Slug;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
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
        $tr = $petition->translationOrFallback($locale);

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

    private function normalizeTags(?string $tags): ?string
    {
        $normalized = collect(explode(',', (string) $tags))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->take(10)
            ->implode(',');

        return $normalized ?: null;
    }

    private function storeCoverImage(UploadedFile $file): string
    {
        $path = $file->store('petitions', 'public');
        $absolute = Storage::disk('public')->path($path);

        try {
            Image::read($absolute)->scaleDown(width: 800, height: 800)->save($absolute);
            ImageVariants::ensure($absolute);

            if (! Storage::disk('public')->exists($path)) {
                throw ValidationException::withMessages([
                    'image' => 'The image failed to save. Please try again.',
                ]);
            }
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($path);
            ImageVariants::purge($path);

            if ($e instanceof ValidationException) {
                throw $e;
            }

            throw ValidationException::withMessages([
                'image' => 'The image could not be processed. Please upload a valid JPG, PNG, GIF, or WebP file.',
            ]);
        }

        return $path;
    }

    private function imageConflict(Request $request, array $data): bool
    {
        return $request->hasFile('image') && filled($data['image_url'] ?? null);
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
            ->translated($locale, 'pt_locale', 'pt_default', ['title' => 'tr_title', 'slug' => 'tr_slug'], $defaultLocale)
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
            $sort = in_array(request('sort'), ['signed', 'newest'], true) ? request('sort') : 'newest';

            if ($sort === 'signed') {
                $petitions = $query
                    ->orderByDesc('petitions.signature_count')
                    ->orderByDesc('petitions.id')
                    ->paginate(15, ['*'], 'page', $page)
                    ->withQueryString();
            } else {
                $cacheKey = CacheKeys::petitionsIndex($locale, $page);
                $petitions = cache()->remember($cacheKey, now()->addSeconds(300), function () use ($query, $page) {
                    return $query->orderByDesc('petitions.id')->paginate(15, ['*'], 'page', $page);
                });

                $petitions = $petitions->withQueryString();
            }
        }

        return view('pages.petitions-list', [
            'pageTitle' => $search ? __('list.search_results', ['q' => $search]) : __('list.all_petitions'),
            'heading' => $search ? __('list.search_results', ['q' => $search]) : __('list.petitions'),
            'search' => $search,
            'sort' => $search ? null : (in_array(request('sort'), ['signed', 'newest'], true) ? request('sort') : 'newest'),
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

        $tr = $petition->translationOrFallback($locale, $defaultLocale);

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

        $recentSignatureCount = cache()->remember(
            "petition:recent24h:{$petition->id}",
            now()->addMinutes(10),
            fn () => Signature::where('petition_id', $petition->id)
                ->where('confirmed', true)
                ->where('is_spam', false)
                ->where('created_at', '>=', now()->subDay())
                ->count()
        );

        $related = cache()->remember(
            "petition:related:{$petition->id}:{$locale}",
            now()->addMinutes(10),
            function () use ($petition, $locale, $defaultLocale) {
                if (! $petition->category_id) {
                    return collect();
                }

                return Petition::query()
                    ->select(['petitions.id', 'petitions.signature_count', 'petitions.cover_image'])
                    ->translated($locale, 'pt_locale', 'pt_default', ['title' => 'tr_title', 'slug' => 'tr_slug'], $defaultLocale)
                    ->where('petitions.category_id', $petition->category_id)
                    ->where('petitions.id', '!=', $petition->id)
                    ->where('petitions.status', 'published')
                    ->where('petitions.is_active', 1)
                    ->where(fn ($q) => $q->whereNotNull('pt_locale.title')->orWhereNotNull('pt_default.title'))
                    ->orderByDesc('petitions.signature_count')
                    ->limit(3)
                    ->get();
            }
        );

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
            'shoutbox',
            'recentSignatureCount',
            'related'
        ));
    }

    private function recordView(Request $request, Petition $petition): void
    {
        $sessionKey = "petition_viewed.{$petition->id}";

        if ($request->session()->has($sessionKey)) {
            return;
        }

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

        $request->session()->put($sessionKey, true);
    }

    public function sign(Request $request, string $locale, string $slug, int $id)
    {
        $petition = Petition::query()
            ->where('id', $id)
            ->where('status', 'published')
            ->where('is_active', 1)
            ->firstOrFail();

        $signSessionKey = "sign.{$petition->id}";

        if ($request->session()->has($signSessionKey)) {
            $request->merge($request->session()->get($signSessionKey));
        }

        $defaultLocale = default_locale();

        $tr = $petition->translationOrFallback($locale, $defaultLocale);

        abort_if(! $tr, 404);

        if (auth()->check()) {
            if (! auth()->user()->verified) {
                toast('Please verify your email before signing petitions.', 'warning');

                return back()->withInput();
            }

            return PetitionSigning::signAsAuthed($request, $locale, $petition);
        }

        return PetitionSigning::signAsGuest($request, $petition, $locale);
    }

    public function create(string $locale)
    {
        $defaultLocale = default_locale();

        $categories = Category::query()
            ->select(['categories.id'])
            ->translated($locale, 'ct_locale', 'ct_default', ['name' => 'name', 'slug' => 'slug'], $defaultLocale)
            ->where('categories.is_active', true)
            ->orderBy('name')
            ->get();

        return view('petition.create', compact('locale', 'categories'));
    }

    public function store(PetitionRequest $request, string $locale)
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

        $data = $request->validated();

        if ($this->imageConflict($request, $data)) {
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

                $tags = $this->normalizeTags($data['tags'] ?? null);

                $petition = new Petition;
                $petition->user_id = auth()->id();
                $petition->status = 'draft';
                $petition->goal_signatures = $data['goal_signatures'];
                $petition->category_id = $data['category_id'];
                $petition->target = $data['target'] ?? null;
                $petition->tags = $tags;
                $petition->city = $data['city'] ?? null;
                $petition->community = $data['community'] ?? null;
                $petition->community_url = $data['community_url'] ?? null;
                $petition->youtube_url = $this->youtubeId($data['youtube'] ?? null);
                $petition->image_url = $request->hasFile('image') ? null : ($data['image_url'] ?? null);

                if ($request->hasFile('image')) {
                    $newCoverImage = $this->storeCoverImage($request->file('image'));
                    $petition->cover_image = $newCoverImage;
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

                    PetitionStats::increment($petition->id);
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

    public function thanks(Request $request, string $locale, string $slug, int $id, $status = 0)
    {
        $requestedLocale = normalize_locale($locale);
        $defaultLocale = default_locale();

        $petition = Petition::with('category')->findOrFail($id);

        $tr = $petition->translationOrFallback($requestedLocale, $defaultLocale);

        abort_if(! $tr, 404);

        if ($tr->slug !== $slug || $tr->locale !== $requestedLocale) {
            return redirect()->route('petition.thanks', [
                'locale' => $tr->locale,
                'slug' => $tr->slug,
                'id' => $petition->id,
                'status' => $status,
            ]);
        }

        $locale = $tr->locale;
        App::setLocale($locale);
        URL::defaults(['locale' => $locale]);
        $request->session()->put('locale', $locale);

        $mode = ((string) $status === 'created') ? 'created' : 'signed';

        $suggestions = Petition::query()
            ->select([
                'petitions.id',
                'petitions.signature_count',
                'pt_locale.title as tr_title',
                'pt_locale.slug as tr_slug',
            ])
            ->join('petition_translations as pt_locale', function ($join) use ($locale) {
                $join->on('pt_locale.petition_id', '=', 'petitions.id')
                    ->where('pt_locale.locale', '=', $locale);
            })

            ->whereNotNull('pt_locale.title')
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

    public function claimAccount(ClaimAccountRequest $request, string $locale, string $slug, int $id)
    {
        $data = $request->validated();

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

        $user->forceFill(['password' => Hash::make($data['password'])])->save();
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
            $q->translated($locale, 'pt_locale', 'pt_default', ['title' => 'tr_title', 'slug' => 'tr_slug'], $defaultLocale)
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
            ->translated($locale, 'ct_locale', 'ct_default', ['name' => 'name', 'slug' => 'slug'], $defaultLocale)
            ->where('categories.is_active', true)
            ->orderBy('name')
            ->get();

        $mode = 'edit';

        return view('petition.create', compact('petition', 'tr', 'locale', 'categories', 'mode'));
    }

    public function update(PetitionRequest $request, string $locale, string $slug, int $id)
    {
        $petition = Petition::query()->findOrFail($id);
        $this->requireEditable($petition);

        $trOrRedirect = $this->resolveTrOrRedirect('petition.edit', $locale, $slug, $petition);
        if ($trOrRedirect instanceof RedirectResponse) {
            return $trOrRedirect;
        }
        $tr = $trOrRedirect;

        $data = $request->validated();

        if ($this->imageConflict($request, $data)) {
            return back()
                ->withInput()
                ->withErrors([
                    'image' => __('create.err_image_conflict'),
                    'image_url' => __('create.err_image_conflict'),
                ]);
        }

        $oldCoverImage = $petition->cover_image;
        $newCoverImage = null;

        try {
            $response = DB::transaction(function () use ($request, $data, $petition, $tr, &$newCoverImage) {

                $tags = $this->normalizeTags($data['tags'] ?? null);

                $petition->category_id = $data['category_id'];
                $petition->target = $data['target'] ?? null;
                $petition->tags = $tags;
                $petition->city = $data['city'] ?? null;
                $petition->community = $data['community'] ?? null;
                $petition->community_url = $data['community_url'] ?? null;

                $petition->youtube_url = $this->youtubeId($data['youtube'] ?? null);

                $petition->image_url = $data['image_url'] ?? null;

                if ($request->hasFile('image')) {
                    $newCoverImage = $this->storeCoverImage($request->file('image'));

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
        return Slug::dedupe(
            $title,
            fn (string $slug) => PetitionTranslation::where('locale', $locale)
                ->where('slug', $slug)
                ->when($ignoreTranslationId, fn ($query) => $query->whereKeyNot($ignoreTranslationId))
                ->exists(),
            'petition',
            255
        );
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
