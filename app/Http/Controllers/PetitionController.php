<?php

namespace App\Http\Controllers;

use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\Signature;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class PetitionController extends Controller
{
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
            'oauthLoggedIn'
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

        return redirect()->route('petition.thanks', [
            'locale' => $locale,
            'slug' => $petition->slug,
            'id' => $petition->id,
            'status' => 0,
        ]);
    }

    public function thanks(Request $request, string $locale, string $slug, int $id, $status = 0)
    {
        $petition = Petition::query()->findOrFail($id);

        $tr = PetitionTranslation::query()
            ->where('petition_id', $petition->id)
            ->where('locale', $locale)
            ->firstOrFail();

        if ($petition->slug !== $slug) {
            return redirect()->route('petition.thanks', [
                'locale' => $locale,
                'slug'   => $tr->slug,
                'id'     => $petition->id,
                'status' => $status,
            ]);
        }

        $mode = ((string) $status === 'created') ? 'created' : 'signed';

        $suggestions = Petition::query()
            ->where('locale', $locale)
            ->where('id', '!=', $petition->id)
            ->when($petition->category_id, fn($q) => $q->where('category_id', $petition->category_id))
            ->orderByDesc('signature_count')
            ->limit(5)
            ->get(['id', 'slug', 'title', 'locale']);

        return view('petition.thanks', compact('petition', 'suggestions', 'locale', 'status', 'mode'));
    }

    public function signPage(Request $request, string $locale, string $slug, int $id)
    {
        if (!auth()->check()) {
            return redirect()->route('petition.show', compact('locale', 'slug', 'id'));
        }

        $petition = Petition::query()
            ->where('id', $id)
            ->where('locale', $locale)
            ->firstOrFail();

        if ($petition->slug !== $slug) {
            return redirect()->route('petition.sign.page', [
                'locale' => $locale,
                'slug' => $petition->slug,
                'id' => $petition->id,
            ]);
        }

        $hasSigned = Signature::query()
            ->where('petition_id', $petition->id)
            ->where('email', auth()->user()->email)
            ->exists();

        if ($hasSigned) {
            return redirect()->route('petition.show', [
                'locale' => $locale,
                'slug' => $petition->slug,
                'id' => $petition->id,
            ]);
        }

        return view('petition.sign_page', compact('petition', 'locale'));
    }

    public function myPetitions(Request $request, string $locale)
    {
        $u = auth()->user();

        $tab = $request->query('tab');

        $signedBase = Petition::query()
            ->select('petitions.*', 'signatures.created_at as signed_at')
            ->join('signatures', 'signatures.petition_id', '=', 'petitions.id')
            ->where('petitions.locale', $locale)
            ->where('signatures.email', $u->email)
            ->orderByDesc('signatures.created_at');

        $createdBase = Petition::query()
            ->where('locale', $locale)
            ->where('user_id', $u->id)
            ->latest('created_at');

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
}
