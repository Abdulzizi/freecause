<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
            // optional fields in the future if needed
            // 'nickname' => $data['nickname'] ?? null,
            // 'city' => $data['city'] ?? null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->to("/{$locale}");
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // keep locale if provided, fallback to /en
            $locale = app()->getLocale();
            return redirect()->to("/{$locale}");
        }

        return back()->withErrors(['email' => 'invalid credentials'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $locale = $request->input('locale', 'en');
        return redirect()->to("/{$locale}");
    }
}
