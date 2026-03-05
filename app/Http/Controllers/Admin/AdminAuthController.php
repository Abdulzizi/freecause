<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function show()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.options.global');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => ['required'],
            'password' => ['required'],
        ]);

        $login    = $request->login;
        $password = $request->password;

        $emergencyEmail    = env('ADMIN_EMERGENCY_EMAIL');
        $emergencyPassword = env('ADMIN_EMERGENCY_PASSWORD');

        if (
            $emergencyEmail &&
            $emergencyPassword &&
            $login === $emergencyEmail &&
            $password === $emergencyPassword
        ) {
            try {
                $user = User::where('email', $emergencyEmail)->first();
                if ($user) {
                    Auth::guard('admin')->login($user);
                    $request->session()->regenerate();
                    return redirect()->route('admin.options.global');
                }
            } catch (\Throwable $e) {
                return back()->withErrors(['login' => 'Emergency login: DB unavailable.'])->withInput();
            }
        }

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        if (!Auth::guard('admin')->attempt([
            $field    => $login,
            'password' => $password,
        ])) {
            return back()->withErrors(['login' => 'invalid credentials'])->withInput();
        }

        $user = Auth::guard('admin')->user();
        $user->load('level');

        if (!$user->hasLevel('admin')) {
            Auth::guard('admin')->logout();
            return back()->withErrors(['login' => 'not authorized'])->withInput();
        }

        return redirect()->route('admin.options.global');
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }
}