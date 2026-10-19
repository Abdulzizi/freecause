<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ConditionalLogging;
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
use Illuminate\Pagination\LengthAwarePaginator;
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

    /**
     * Export authorization: the petition owner OR a logged-in admin.
     * Matches the legacy rule (owner_id == user || VALID_ADMIN) for
     * signature downloads. Kept separate from requireOwner(), which must
     * stay owner-only for edit/update.
     */
    private function requireOwnerOrAdmin(Petition $petition): void
    {
        $isOwner = auth()->check() && (int) $petition->user_id === (int) auth()->id();
        $isAdmin = admin_user()?->canAccessAdmin() ?? false;

        abort_unless($isOwner || $isAdmin, 403);
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
        $size = @getimagesize($file->getPathname());
        if ($size !== false && max($size[0], $size[1]) > 2000) {
            throw ValidationException::withMessages([
                'image' => 'The image must not exceed 2000 pixels on its longest side.',
            ]);
        }

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

        $categories = cache()->remember(CacheKeys::homeCategories($locale), 43200, function () use ($locale, $defaultLocale) {
            return Category::select(['categories.id', 'categories.sort_order'])
                ->translated($locale, 'ct_locale', 'ct_default', ['name' => 'tr_name', 'slug' => 'tr_slug'], $defaultLocale)
                ->where('categories.is_active', true)
                ->orderBy('categories.sort_order')
                ->get();
        });

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
            ->visible();

        if ($search !== '') {
            $driver = DB::connection()->getDriverName();
            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('MATCH(pt_locale.title) AGAINST(? IN BOOLEAN MODE)', [$search])
                        ->orWhereRaw('MATCH(pt_default.title) AGAINST(? IN BOOLEAN MODE)', [$search]);
                });
            } else {
                $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';
                $query->where(function ($q) use ($like) {
                    $q->where('pt_locale.title', 'LIKE', $like)
                        ->orWhere('pt_default.title', 'LIKE', $like);
                });
            }
            $query->orderByDesc('petitions.signature_count');
            $petitions = $query->paginate(15, ['*'], 'page', $page)->withQueryString();
        } else {
            $sort = in_array(request('sort'), ['signed', 'newest', 'trending'], true) ? request('sort') : 'newest';

            if ($sort === 'signed') {
                $petitions = $query
                    ->orderByDesc('petitions.signature_count')
                    ->orderByDesc('petitions.id')
                    ->paginate(15, ['*'], 'page', $page)
                    ->withQueryString();
            } elseif ($sort === 'trending') {
                $recent = Signature::query()
                    ->where('confirmed', true)
                    ->where('created_at', '>=', now()->subDays(7))
                    ->selectRaw('petition_id, count(*) as recent_count')
                    ->groupBy('petition_id');

                $cacheKey = CacheKeys::petitionsTrending($locale, $page);

                $cached = cache()->remember($cacheKey, now()->addMinutes(10), function () use ($query, $recent, $page) {
                    $p = (clone $query)
                        ->leftJoinSub($recent, 'trend', 'trend.petition_id', '=', 'petitions.id')
                        ->orderByDesc('trend.recent_count')
                        ->orderByDesc('petitions.signature_count')
                        ->orderByDesc('petitions.id')
                        ->paginate(15, ['petitions.id'], 'page', $page);

                    return ['ids' => $p->items(), 'total' => $p->total()];
                });
                $ids = collect($cached['ids'] ?? [])->pluck('id')->all();
                $rows = empty($ids) ? collect() : (clone $query)->whereIn('petitions.id', $ids)
                    ->with('translations')
                    ->orderByRaw('FIELD(petitions.id, '.implode(',', array_map('intval', $ids)).')')->get();
                $petitions = new LengthAwarePaginator($rows, $cached['total'] ?? 0, 15, $page);

                $petitions = $petitions->withQueryString();
            } else {
                $cacheKey = CacheKeys::petitionsIndex($locale, $page);
                $cached = cache()->remember($cacheKey, now()->addSeconds(300), function () use ($query, $page) {
                    $p = (clone $query)->orderByDesc('petitions.id')->paginate(15, ['petitions.id'], 'page', $page);

                    return ['ids' => $p->items(), 'total' => $p->total()];
                });
                $ids = collect($cached['ids'] ?? [])->pluck('id')->all();
                $rows = empty($ids) ? collect() : (clone $query)->whereIn('petitions.id', $ids)
                    ->with('translations')
                    ->orderByRaw('FIELD(petitions.id, '.implode(',', array_map('intval', $ids)).')')->get();
                $petitions = new LengthAwarePaginator($rows, $cached['total'] ?? 0, 15, $page);

                $petitions = $petitions->withQueryString();
            }
        }

        return view('pages.petitions-list', [
            'pageTitle' => $search ? __('list.search_results', ['q' => $search]) : __('list.all_petitions'),
            'heading' => $search ? __('list.search_results', ['q' => $search]) : __('list.petitions'),
            'search' => $search,
            'categories' => $categories,
            'sort' => $search ? null : (in_array(request('sort'), ['signed', 'newest', 'trending'], true) ? request('sort') : 'newest'),
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
            ->with(['category', 'user', 'translations'])
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

        $related = cache()->remember(
            "petition:related:{$petition->id}:{$locale}",
            now()->addMinutes(10),
            function () use ($petition, $locale, $defaultLocale) {
                if (! $petition->category_id) {
                    return collect();
                }

                return Petition::query()
                    ->with('translations')
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

        if ($petition->status === 'published' && ! $petition->is_active) {
            return response()->view('petition.offline', compact('petition', 'tr', 'locale', 'related'));
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
                ->with('user')
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
        if (ConditionalLogging::isPrefetchOnly($request)) {
            return;
        }

        $sessionKey = "petition_viewed.{$petition->id}";

        if ($request->hasSession() && $request->session()->has($sessionKey)) {
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

        if ($request->hasSession()) {
            $request->session()->put($sessionKey, true);
        }
    }

    public function sign(Request $request, string $locale, string $slug, int $id)
    {
        $petition = Petition::query()
            ->where('id', $id)
            ->visible()
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

    public function shoutboxStore(Request $request, string $locale, string $slug, int $id): RedirectResponse
    {
        $petition = Petition::query()->where('id', $id)->visible()->firstOrFail();
        $data = $request->validate(['comment' => ['required', 'string', 'max:500']]);
        $u = $request->user();
        $sig = Signature::query()->where('petition_id', $petition->id)->where('user_id', $u->id)->first();
        abort_unless($sig, 403);
        $sig->forceFill(['text' => $data['comment']])->save();
        toast('Comment posted!', 'success');
        $tr = $petition->translationOrFallback($locale);
        abort_if(! $tr, 404);

        return redirect()->to(route('petition.show', ['locale' => $tr->locale, 'slug' => $tr->slug, 'id' => $petition->id]).'#petition-comments');
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
                $petition->status = 'published';
                $petition->is_active = 1;
                $petition->goal_signatures = $data['goal_signatures'];
                $petition->category_id = $data['category_id'];
                $petition->target = $data['target'] ?? null;
                $petition->tags = $tags;
                $petition->city = $data['city'] ?? null;
                $petition->country = $data['country'] ?? null;
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

                    $previousCount = PetitionStats::incrementMany([$petition->id => 1])[$petition->id] ?? null;
                    PetitionStats::notifyMilestoneIfCrossed($petition->id, $previousCount);
                }

                toast('Petition created successfully.', 'success');

                return redirect()->route('petition.thanks', [
                    'locale' => $locale,
                    'slug' => $slug,
                    'id' => $petition->id,
                ])->with('petition_mode', 'created');
            });
        } catch (\Throwable $e) {
            if ($newCoverImage) {
                Storage::disk('public')->delete($newCoverImage);
                ImageVariants::purge($newCoverImage);
            }

            throw $e;
        }
    }

    public function thanks(Request $request, string $locale, string $slug, int $id)
    {
        $requestedLocale = normalize_locale($locale);
        $defaultLocale = default_locale();

        $petition = Petition::with('category')->findOrFail($id);

        $tr = $petition->translationOrFallback($requestedLocale, $defaultLocale);

        abort_if(! $tr, 404);

        $mode = $request->session()->get('petition_mode', 'signed');
        $verified = $request->session()->get('petition_verified', true);

        if ($tr->slug !== $slug || $tr->locale !== $requestedLocale) {
            return redirect()->route('petition.thanks', [
                'locale' => $tr->locale,
                'slug' => $tr->slug,
                'id' => $petition->id,
            ])->with('petition_mode', $mode)->with('petition_verified', $verified);
        }

        $locale = $tr->locale;
        App::setLocale($locale);
        URL::defaults(['locale' => $locale]);
        $request->session()->put('locale', $locale);

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
            $claimUser = User::where('email', $claim['email'])->first();
            $canClaim = $claimUser && ! filled($claimUser->password);
        }

        $count = (int) ($petition->signature_count ?? 0);
        $ladder = [50, 100, 1000, 5000, 10000, 50000, 100000, 500000, 1000000];
        $nextMilestone = null;
        foreach ($ladder as $m) {
            if ($m > $count) {
                $nextMilestone = $m;
                break;
            }
        }
        $nextMilestone ??= end($ladder);
        $shareUrl = lroute('petition.show', ['slug' => $tr->slug, 'id' => $petition->id]);

        return view('petition.thanks', compact(
            'petition',
            'suggestions',
            'locale',
            'mode',
            'verified',
            'tr',
            'content',
            'claim',
            'canClaim',
            'nextMilestone',
            'shareUrl'
        ));
    }

    public function claimAccount(ClaimAccountRequest $request, string $locale, string $slug, int $id)
    {
        $data = $request->validated();

        $claim = $request->session()->get('sign_claim');

        if (! $claim) {
            $request->session()->forget('sign_claim');
            toast(__('sign.claim_expired'), 'warning');

            return redirect()->route('login', ['locale' => $locale]);
        }

        $claimed = false;

        $user = DB::transaction(function () use ($claim, $data, &$claimed) {
            $locked = User::lockForUpdate()->where('email', $claim['email'])->first();
            abort_unless($locked && $locked->verified && is_null($locked->verification_token), 403);
            if (filled($locked->password)) {
                return $locked;
            }
            $locked->forceFill(['password' => Hash::make($data['password'])])->save();
            $claimed = true;

            return $locked;
        });

        if (! $claimed) {
            return back()->withErrors([
                'password' => 'This account already has a password — please log in.',
            ]);
        }

        $request->session()->forget('sign_claim');

        auth()->login($user);
        $request->session()->regenerate();

        toast(__('sign.claim_success'), 'success');

        $dest = lroute('petition.show', ['slug' => $slug, 'id' => $id]);

        if (! $user->hasCompleteProfile()) {
            $dest = self::profileCompletionTarget($locale, $dest);
        }

        return redirect()->to($dest);
    }

    public function signPage(Request $request, string $locale, string $slug, int $id)
    {
        $petition = Petition::query()
            ->with('translations')
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
            $content = PageContent::where('page', 'petition_sign')
                ->where('locale', $locale)
                ->pluck('value', 'key');

            return view('petition.sign_page', compact('petition', 'tr', 'locale', 'content'));
        }

        $rules = auth()->check()
            ? PetitionSigning::authedRules()
            : PetitionSigning::guestRules();

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
            ->where(fn ($q) => $q->where('signatures.email', $u->email)->orWhere('signatures.user_id', $u->id))
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
                $petition->country = $data['country'] ?? null;
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
        $this->requireOwnerOrAdmin($petition);

        $trOrRedirect = $this->resolveTrOrRedirect('petition.show', $locale, $slug, $petition);
        if ($trOrRedirect instanceof RedirectResponse) {
            return $trOrRedirect;
        }
        $tr = $trOrRedirect;

        $filename = 'petition-'.$petition->id.'.txt';
        $total = (int) ($petition->signature_count ?? 0);

        $header = [];
        $header[] = strtoupper($tr->title ?? 'Petition');
        $header[] = 'URL: '.url("/{$locale}/petition/{$tr->slug}/{$petition->id}");
        $header[] = '';
        $header[] = '--- DESCRIPTION ---';
        $header[] = trim(strip_tags($tr->description ?? ''));
        $header[] = '';
        $header[] = 'Total signatures: '.$total;
        $header[] = '';
        $header[] = '--- SIGNATURES ---';

        return response()->streamDownload(function () use ($petition, $header) {
            $out = fopen('php://output', 'w');

            fwrite($out, implode("\n", $header)."\n");

            $num = 0;
            Signature::query()
                ->where('petition_id', $petition->id)
                ->orderBy('created_at')->orderBy('id')
                ->chunkById(1000, function ($rows) use ($out, &$num) {
                    foreach ($rows as $sig) {
                        $num++;
                        $name = $sig->name ?: 'Anonymous';
                        $date = optional($sig->created_at)->format('Y-m-d');
                        $comment = trim($sig->text ?? 'I support this petition');

                        fwrite($out, "{$num}. {$name} ({$date})\n");
                        fwrite($out, "   {$comment}\n");
                    }
                });

            if ($num === 0) {
                fwrite($out, "No signatures yet.\n");
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function downloadPdf(Request $request, string $locale, string $slug, int $id)
    {
        $petition = Petition::query()->findOrFail($id);
        $this->requireOwnerOrAdmin($petition);

        $trOrRedirect = $this->resolveTrOrRedirect('petition.show', $locale, $slug, $petition);
        if ($trOrRedirect instanceof RedirectResponse) {
            return $trOrRedirect;
        }
        $tr = $trOrRedirect;

        // PDF renders in-memory, so cap the embedded list; the full,
        // uncapped export is available as TXT/CSV (streamed).
        $signatures = Signature::query()
            ->where('petition_id', $petition->id)
            ->orderBy('created_at')
            ->limit(1000)
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

    public function downloadCsv(Request $request, string $locale, string $slug, int $id)
    {
        $petition = Petition::query()->findOrFail($id);
        $this->requireOwnerOrAdmin($petition);

        $trOrRedirect = $this->resolveTrOrRedirect('petition.show', $locale, $slug, $petition);
        if ($trOrRedirect instanceof RedirectResponse) {
            return $trOrRedirect;
        }

        $filename = 'petition-'.$petition->id.'-signatures.csv';

        // Stream ALL signatures as CSV (chunked cursor) so the export is
        // memory-safe on large petitions.
        $showEmail = (bool) (admin_user()?->canAccessAdmin());
        $header = $showEmail ? ['#', 'name', 'email', 'comment', 'locale', 'confirmed', 'signed_at'] : ['#', 'name', 'comment', 'locale', 'confirmed', 'signed_at'];

        return response()->streamDownload(function () use ($petition, $showEmail, $header) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM so spreadsheet apps read accents correctly.
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, $header);

            $num = 0;
            Signature::query()
                ->where('petition_id', $petition->id)
                ->orderBy('created_at')->orderBy('id')
                ->chunkById(1000, function ($rows) use ($out, &$num, $showEmail) {
                    foreach ($rows as $sig) {
                        $num++;
                        $row = [$num, $sig->name ?: 'Anonymous'];
                        if ($showEmail) {
                            $row[] = $sig->email ?? '';
                        }
                        $row[] = trim($sig->text ?? '');
                        $row[] = $sig->locale ?? '';
                        $row[] = $sig->confirmed ? '1' : '0';
                        $row[] = optional($sig->created_at)->format('Y-m-d H:i:s');
                        fputcsv($out, $row);
                    }
                });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
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

        if (preg_match('/^[\w-]{11}$/', $value)) {
            return $value;
        }

        return null;
    }

    public function signatures(Request $request, string $locale, string $slug, int $id)
    {
        $locale = normalize_locale($locale);
        $defaultLocale = default_locale();

        $petition = Petition::with('category', 'user', 'translations')
            ->where('id', $id)
            ->visible()
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
