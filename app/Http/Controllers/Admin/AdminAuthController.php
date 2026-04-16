<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
            'login' => ['required'],
            'password' => ['required'],
        ]);

        $login = $request->login;
        $password = $request->password;

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        $user = User::where($field, $login)->first();

        if (! $user) {
            return back()->withErrors(['login' => 'invalid credentials'])->withInput();
        }

        $passwordValid = Hash::check($password, $user->password);

        if (! $passwordValid && str_contains($user->password, ':')) {
            [$hash, $salt] = explode(':', $user->password, 2);
            if (md5(md5($password).$salt) === $hash) {
                $user->password = Hash::make($password);
                $user->save();
                $passwordValid = true;
            }
        }

        if (! $passwordValid) {
            return back()->withErrors(['login' => 'invalid credentials'])->withInput();
        }

        $user->load('level');

        if (! $user->hasLevel('admin')) {
            return back()->withErrors(['login' => 'not authorized'])->withInput();
        }

        Auth::guard('admin')->login($user);
        $request->session()->regenerateToken();

        return redirect()->route('admin.options.global');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
