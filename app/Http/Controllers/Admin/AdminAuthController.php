<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function show()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(AdminLoginRequest $request)
    {
        $login = $request->login;
        $password = $request->password;

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        $user = User::where($field, $login)->first();

        if (! $user) {
            return back()->withErrors(['login' => 'invalid credentials'])->withInput();
        }

        try {
            $passwordValid = Hash::check($password, $user->password);
        } catch (\RuntimeException $e) {
            $passwordValid = false;
        }

        if (! $passwordValid && filled($user->password) && str_contains($user->password, ':')) {
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

        if (! $user->canAccessAdmin()) {
            return back()->withErrors(['login' => 'not authorized'])->withInput();
        }

        Auth::guard('admin')->login($user);
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
