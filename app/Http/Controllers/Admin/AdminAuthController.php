<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
            'login' => ['required'],
            'password' => ['required'],
        ]);

        $login = $request->login;
        $password = $request->password;

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        if (!Auth::guard('admin')->attempt([
            $field => $login,
            'password' => $password
        ])) {
            return back()->withErrors(['login' => 'invalid credentials'])->withInput();
        }

        // if (Auth::guard('admin')->user()->level !== 'admin') {
        //     Auth::guard('admin')->logout();
        //     return back()->withErrors(['login' => 'not authorized'])->withInput();
        // }

        if (Auth::guard('admin')->user()->level()->name !== 'admin') {
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
