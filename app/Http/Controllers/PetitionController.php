<?php

namespace App\Http\Controllers;

use App\Models\Petition;
use App\Models\Signature;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class PetitionController extends Controller
{
    public function index(string $locale)
    {
        $petitions = Petition::query()
            ->where('locale', $locale)
            ->where('status', 'published')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.petitions-list', [
            'pageTitle' => 'All the petitions',
            'heading' => 'Petitions',
            'petitions' => $petitions,
            'petitionTitle' => fn($p) => $p->title,
            'petitionUrl' => fn($p) => url("/{$locale}/petition/{$p->slug}/{$p->id}"),
        ]);
    }

    public function show(Request $request, string $locale, string $slug, int $id)
    {
        $hasSigned = false;
        $oauthLoggedIn = session('oauth_logged_in', false);

        $petition = Petition::query()
            ->where('id', $id)
            ->where('locale', $locale)
            ->with('category')
            ->firstOrFail();

        if (auth()->check()) {
            $hasSigned = Signature::query()
                ->where('petition_id', $petition->id)
                ->where('email', auth()->user()->email)
                ->exists();
        }

        if ($petition->slug !== $slug) {
            return redirect()->route('petition.show', [
                'locale' => $locale,
                'slug' => $petition->slug,
                'id' => $petition->id,
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

        $directLink = url("/{$locale}/petition/{$petition->slug}/{$petition->id}");

        return view('petition.show', compact(
            'petition',
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
        $petition = Petition::query()
            ->where('id', $id)
            ->where('locale', $locale)
            ->firstOrFail();

        // if logged in: just sign with their account (optional but nice)
        if (auth()->check()) {
            return $this->signAsAuthed($request, $locale, $petition);
        }

        // signup + sign (guest)
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'surname' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'string', 'min:6', 'max:72'],
            'comment' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:80'],
            'nickname' => ['nullable', 'string', 'max:80'],

            // prod-like “must agree”
            'agree1' => ['required', 'in:agree'],
            'agree2' => ['required', 'in:agree'],
            'agree3' => ['required', 'in:agree'],
        ]);

        $email = strtolower(trim($data['email']));

        // email already exists => do NOT sign in here, push them to login
        if (User::where('email', $email)->exists()) {
            $loginUrl = url("/{$locale}/login?email=" . urlencode($email) . "&redirect=" . urlencode(url()->previous()));

            return back()
                ->withInput($request->except('password'))
                ->with('login_url', $loginUrl)
                ->withErrors(['email' => 'this email is already registered. please sign in first.']);
        }

        // create user but DO NOT login
        $user = User::create([
            'name' => trim($data['name'] . ' ' . $data['surname']),
            'email' => $email,
            'password' => Hash::make($data['password']),
            'locale' => $locale,
            // if you later use email verification, keep email_verified_at NULL
        ]);

        // create signature (use email uniqueness per petition)
        $sig = Signature::firstOrCreate(
            [
                'petition_id' => $petition->id,
                'email' => $email,
            ],
            [
                'user_id' => $user->id,
                'name' => $data['nickname'] ?: $user->name, // prod-ish: nickname can be shown
                'locale' => $locale,
                'city' => $data['city'] ?? null,
                'comment' => $data['comment'] ?? 'I support this petition',
            ]
        );

        // only increment if it was new
        if ($sig->wasRecentlyCreated) {
            $petition->increment('signature_count');
        }

        // later: send verification mail here (optional for phase 1)
        // $user->sendEmailVerificationNotification();

        return redirect()->route('petition.thanks', [
            'locale' => $locale,
            'slug' => $petition->slug,
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

    public function thanks(Request $request, string $locale, string $slug, int $id, int $status = 0)
    {
        $petition = Petition::query()
            ->where('id', $id)
            ->where('locale', $locale)
            ->firstOrFail();

        $suggestions = Petition::query()
            ->where('locale', $locale)
            ->where('id', '!=', $petition->id)
            ->when($petition->category_id, fn($q) => $q->where('category_id', $petition->category_id))
            ->orderByDesc('signature_count')
            ->limit(5)
            ->get(['id', 'slug', 'title', 'locale']);

        return view('petition.thanks', compact('petition', 'suggestions', 'locale', 'status'));
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

        // keep canonical slug
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

        // if already signed, go back to petition
        if ($hasSigned) {
            return redirect()->route('petition.show', [
                'locale' => $locale,
                'slug' => $petition->slug,
                'id' => $petition->id,
            ]);
        }

        return view('petition.sign_page', compact('petition', 'locale'));
    }
}
