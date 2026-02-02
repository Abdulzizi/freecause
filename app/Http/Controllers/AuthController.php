<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister(string $locale)
    {
        return view('auth.register', compact('locale'));
    }

    public function profile()
    {
        return view('auth.profile');
    }

    public function register(Request $request, string $locale)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'surname' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'max:72'],
            'nickname' => ['nullable', 'string', 'max:80'],
            'city' => ['nullable', 'string', 'max:80'],
            'agree_terms' => ['accepted'], // checkbox must be checked
        ]);

        $fullName = trim($data['name'] . ' ' . $data['surname']);

        $user = User::create([
            'name' => $fullName,
            'email' => strtolower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            'locale' => $locale,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->to("/{$locale}");
    }

    public function login(Request $request, string $locale)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $redirect = $request->input('redirect');
            if ($redirect) {
                return redirect()->to($redirect);
            }

            return redirect()->intended("/{$locale}");
        }

        return back()
            ->withErrors(['email' => 'invalid credentials'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $locale = $request->input('locale', 'en');
        return redirect()->to("/{$locale}");
    }

    public function updateProfile(Request $request, string $locale)
    {
        $u = Auth::user();

        $data = $request->validate([
            'first_name' => ['nullable', 'string', 'max:60'],
            'last_name' => ['nullable', 'string', 'max:60'],
            'nickname' => ['nullable', 'string', 'max:80'],
            'city' => ['nullable', 'string', 'max:80'],
            'identify_mode' => ['nullable', Rule::in(['full', 'name', 'nick'])],

            'new_email' => ['nullable', 'email', 'max:190'],
            'new_email_confirmation' => ['nullable', 'same:new_email'],

            'new_password' => ['nullable', 'string', 'min:6', 'max:72'],
            'new_password_confirmation' => ['nullable', 'same:new_password'],
        ]);


        $first = trim($data['first_name'] ?? '');
        $last  = trim($data['last_name'] ?? '');

        if ($first !== '' || $last !== '') {
            $u->name = trim($first . ' ' . $last);
        }

        try {
            if (\Schema::hasColumn('users', 'nickname')) {
                $u->nickname = $data['nickname'] ?? null;
            }
            if (\Schema::hasColumn('users', 'city')) {
                $u->city = $data['city'] ?? null;
            }
            if (\Schema::hasColumn('users', 'identify_mode')) {
                $u->identify_mode = $data['identify_mode'] ?? ($u->identify_mode ?? 'full');
            }
            if (\Schema::hasColumn('users', 'first_name')) {
                $u->first_name = $first ?: null;
            }
            if (\Schema::hasColumn('users', 'last_name')) {
                $u->last_name = $last ?: null;
            }
        } catch (\Throwable $e) {
            // ignore schema errors
        }

        $newEmail = strtolower(trim($data['new_email'] ?? ''));

        if ($newEmail !== '') {
            $exists = User::where('email', $newEmail)
                ->where('id', '!=', $u->id)
                ->exists();

            if ($exists) {
                return back()
                    ->withInput()
                    ->withErrors(['new_email' => 'this email is already taken.']);
            }

            $u->email = $newEmail;
        }

        $newPass = $data['new_password'] ?? null;
        if ($newPass) {
            $u->password = Hash::make($newPass);
        }

        $u->save();

        return back()->with('success', 'profile updated');
    }

    public function delete(Request $request, string $locale)
    {
        $data = $request->validate([
            'confirm_delete' => ['required', 'in:1'],
        ]);

        $u = Auth::user();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $u->delete();

        return redirect()->to("/{$locale}")->with('success', 'account deleted');
    }
}
