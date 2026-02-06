<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PageContent;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\Signature;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

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
        $rows = PetitionTranslation::query()
            ->where('locale', $locale)
            ->whereHas('petition', fn($q) => $q->where('status', 'published'))
            ->with(['petition' => fn($q) => $q->select('id', 'signature_count', 'goal_signatures', 'category_id', 'cover_image')])
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('pages.petitions-list', [
            'pageTitle' => 'All the petitions',
            'heading' => 'Petitions',
            'petitions' => $rows,
            'petitionTitle' => fn($tr) => $tr->title,
            'petitionUrl' => fn($tr) => url("/{$locale}/petition/{$tr->slug}/{$tr->petition_id}"),
        ]);
    }

    public function show(Request $request, string $locale, string $slug, int $id)
    {
        $hasSigned = false;
        $oauthLoggedIn = session('oauth_logged_in', false);

        $petition = Petition::query()
            ->with('category')
            ->findOrFail($id);

        $tr = PetitionTranslation::query()
            ->where('petition_id', $petition->id)
            ->where('locale', $locale)
            ->first();

        if (! $tr) {
            $tr = PetitionTranslation::query()
                ->where('petition_id', $petition->id)
                ->orderBy('id')
                ->first();

            abort_if(! $tr, 404);

            return redirect()->route('petition.show', [
                'locale' => $tr->locale,
                'slug'   => $tr->slug,
                'id'     => $petition->id,
            ]);
        }

        if (auth()->check()) {
            $hasSigned = Signature::query()
                ->where('petition_id', $petition->id)
                ->where('email', auth()->user()->email)
                ->exists();
        }

        if ($tr->slug !== $slug) {
            return redirect()->route('petition.show', [
                'locale' => $locale,
                'slug'   => $tr->slug,
                'id'     => $petition->id,
            ]);
        }

        $goalTotal = (int) ($petition->goal_signatures ?? 100);
        $goalCurrent = (int) ($petition->signature_count ?? 0);
        $pct = $goalTotal > 0 ? min(100, round(($goalCurrent / $goalTotal) * 100)) : 0;

        $latest = Signature::query()
            ->where('petition_id', $petition->id)
            ->latest('created_at')
            ->limit(25)
            ->get();

        $directLink = url("/{$locale}/petition/{$tr->slug}/{$petition->id}");

        $content = PageContent::where('page', 'petition_show')
            ->where('locale', $locale)
            ->pluck('value', 'key');

        $formContent = PageContent::where('page', 'petition_sign_form')
            ->where('locale', $locale)
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
            'oauthLoggedIn',
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
            'password' => ['required', 'string', 'min:6', 'max:72'],
            'comment' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:80'],
            'nickname' => ['nullable', 'string', 'max:80'],

            'agree1' => ['required', 'in:agree'],
            'agree2' => ['required', 'in:agree'],
            'agree3' => ['required', 'in:agree'],
        ]);

        $email = strtolower(trim($data['email']));

        if (User::where('email', $email)->exists()) {
            $loginUrl = url("/{$locale}/login?email=" . urlencode($email) . "&redirect=" . urlencode(url()->previous()));

            return back()
                ->withInput($request->except('password'))
                ->with('login_url', $loginUrl)
                ->withErrors(['email' => 'this email is already registered. please sign in first.']);
        }

        $user = User::create([
            'name' => trim($data['name'] . ' ' . $data['surname']),
            'email' => $email,
            'password' => Hash::make($data['password']),
            'locale' => $locale,
        ]);

        $sig = Signature::firstOrCreate(
            [
                'petition_id' => $petition->id,
                'email' => $email,
            ],
            [
                'user_id' => $user->id,
                'name' => $data['nickname'] ?: $user->name,
                'locale' => $locale,
                'city' => $data['city'] ?? null,
                'comment' => $data['comment'] ?? 'I support this petition',
            ]
        );

        if ($sig->wasRecentlyCreated) {
            $petition->increment('signature_count');
        }

        // later: send verification mail here (optional for phase 1)
        // $user->sendEmailVerificationNotification();

        return redirect()->route('petition.thanks', [
            'locale' => $locale,
            'slug' => $tr->slug,
            'id' => $petition->id,
            'status' => 0,
        ]);
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
                'comment' => $data['comment'] ?? 'I support this petition',
            ]
        );

        if ($sig->wasRecentlyCreated) {
            $petition->increment('signature_count');
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
            'status' => 0,
        ]);
    }

    public function thanks(Request $request, string $locale, string $slug, int $id, $status = 0)
    {
        $petition = Petition::query()
            ->with('category')
            ->findOrFail($id);

        $tr = PetitionTranslation::query()
            ->where('petition_id', $petition->id)
            ->where('locale', $locale)
            ->first()
            ?? PetitionTranslation::query()
            ->where('petition_id', $petition->id)
            ->orderBy('id')
            ->first();

        abort_if(! $tr, 404);

        if ($tr->slug !== $slug || $tr->locale !== $locale) {
            return redirect()->route('petition.thanks', [
                'locale'  => $tr->locale,
                'slug'    => $tr->slug,
                'id'      => $petition->id,
                'status'  => $status,
            ]);
        }

        $mode = ((string) $status === 'created') ? 'created' : 'signed';

        $suggestions = Petition::query()
            ->select([
                'petitions.id',
                'petitions.category_id',
                'petitions.signature_count',
                'pt.title as tr_title',
                'pt.slug as tr_slug',
            ])
            ->join('petition_translations as pt', function ($join) use ($locale) {
                $join->on('pt.petition_id', '=', 'petitions.id')
                    ->where('pt.locale', '=', $locale);
            })
            ->where('petitions.status', 'published')
            ->where('petitions.id', '!=', $petition->id)
            ->when($petition->category_id, fn($q) => $q->where('petitions.category_id', $petition->category_id))
            ->orderByDesc('petitions.signature_count')
            ->orderByDesc('petitions.id')
            ->limit(5)
            ->get();

        $content = PageContent::query()
            ->where('page', 'petition_thanks')
            ->where('locale', $locale)
            ->pluck('value', 'key');

        return view('petition.thanks', compact('petition', 'suggestions', 'locale', 'status', 'mode', 'tr', 'content'));
    }

    public function signPage(Request $request, string $locale, string $slug, int $id)
    {
        if (! auth()->check()) {
            return redirect()->route('petition.show', compact('locale', 'slug', 'id'));
        }

        $petition = Petition::query()->findOrFail($id);

        $tr = PetitionTranslation::query()
            ->where('petition_id', $petition->id)
            ->where('locale', $locale)
            ->first()
            ?? PetitionTranslation::query()->where('petition_id', $petition->id)->orderBy('id')->first();

        abort_if(! $tr, 404);

        if ($tr->slug !== $slug || $tr->locale !== $locale) {
            return redirect()->route('petition.sign.page', [
                'locale' => $tr->locale,
                'slug' => $tr->slug,
                'id' => $petition->id,
            ]);
        }

        $hasSigned = Signature::query()
            ->where('petition_id', $petition->id)
            ->where('email', auth()->user()->email)
            ->exists();

        if ($hasSigned) {
            return redirect()->route('petition.show', [
                'locale' => $tr->locale,
                'slug' => $tr->slug,
                'id' => $petition->id,
            ]);
        }

        $content = PageContent::query()
            ->where('page', 'petition_sign')
            ->where('locale', $locale)
            ->pluck('value', 'key');

        return view('petition.sign_page', compact('petition', 'locale', 'tr', 'content'));
    }

    public function myPetitions(Request $request, string $locale)
    {
        $u = auth()->user();
        $tab = $request->query('tab');

        $withTr = function ($q) use ($locale) {
            $q->join('petition_translations as pt', function ($join) use ($locale) {
                $join->on('pt.petition_id', '=', 'petitions.id')
                    ->where('pt.locale', '=', $locale);
            })->addSelect([
                'pt.title as tr_title',
                'pt.slug as tr_slug',
            ]);
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

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // reuse create form
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
            return back()
                ->withErrors([
                    'image' => 'Please choose either upload an image OR use an external image link (not both).',
                    'image_url' => 'Please choose either upload an image OR use an external image link (not both).',
                ])
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
            $tr->description = $this->sanitizePetitionHtml($data['description']);
            $tr->save();

            return redirect()->route('petition.show', [
                'locale' => $tr->locale,
                'slug'   => $tr->slug,
                'id'     => $petition->id,
            ])->with('success', 'Petition updated');
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

    private function sanitizePetitionHtml(string $html): string
    {
        $allowedTags = ['br', 'p', 'strong', 'em', 'u', 'ul', 'ol', 'li'];

        libxml_use_internal_errors(true);

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadHTML('<?xml encoding="utf-8" ?><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $container = $doc->getElementsByTagName('div')->item(0);

        $this->domSanitizeNode($container, $allowedTags, $doc);

        $out = '';
        foreach ($container->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        $out = str_ireplace(['<b>', '</b>', '<i>', '</i>'], ['<strong>', '</strong>', '<em>', '</em>'], $out);

        return trim($out);
    }

    private function domSanitizeNode(\DOMNode $node, array $allowedTags, \DOMDocument $doc): void
    {
        if (!$node->hasChildNodes()) return;

        for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
            $child = $node->childNodes->item($i);

            if ($child->nodeType === XML_ELEMENT_NODE) {
                $tag = strtolower($child->nodeName);

                if ($child->hasAttributes()) {
                    while ($child->attributes->length) {
                        $child->removeAttributeNode($child->attributes->item(0));
                    }
                }

                if (!in_array($tag, $allowedTags, true)) {
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);
                    continue;
                }

                $this->domSanitizeNode($child, $allowedTags, $doc);
            } elseif ($child->nodeType === XML_COMMENT_NODE) {
                $node->removeChild($child);
            }
        }
    }
}
