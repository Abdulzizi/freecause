<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function show()
    {
        if (session()->has('admin_user_id')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $admin = AdminUser::where('email', $request->email)->first();

        if (!$admin || !$admin->is_active || !Hash::check($request->password, $admin->password)) {
            return back()->withErrors(['email' => 'invalid credentials'])->withInput();
        }

        session()->put('admin_user_id', $admin->id);
        session()->put('admin_username', $admin->username ?? $admin->email);

        return redirect()->route('admin.dashboard');
    }

    public function logout()
    {
        session()->forget(['admin_user_id', 'admin_username']);
        return redirect()->route('admin.login');
    }
}
