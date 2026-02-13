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
        if (Auth::check()) {
            if (Auth::user()->level === 'admin') {
                return redirect()->route('admin.options.global');
            }

            Auth::logout();
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

        if (!Auth::attempt([$field => $login, 'password' => $password])) {
            return back()->withErrors(['login' => 'invalid credentials'])->withInput();
        }

        if (Auth::user()->level !== 'admin') {
            Auth::logout();
            return back()->withErrors(['login' => 'not authorized'])->withInput();
        }

        return redirect()->route('admin.options.global');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('admin.login');
    }
}
