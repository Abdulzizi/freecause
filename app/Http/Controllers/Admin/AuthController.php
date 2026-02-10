<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function show()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        // TODO: replace with DB check
        if (
            $request->username === config('admin.username', 'sadmin') &&
            $request->password === config('admin.password', 'secret')
        ) {

            session()->put('admin_logged_in', true);
            return redirect()->route('admin.home');
        }

        return back()->withErrors(['username' => 'invalid credentials'])->withInput();
    }

    public function logout()
    {
        session()->forget('admin_logged_in');
        return redirect()->route('admin.login');
    }
}
