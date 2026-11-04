<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminSystemUserInfoRequest;
use App\Support\AppLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminSystemController extends Controller
{
    public function userInfo()
    {
        $admin = admin_user();

        return view('admin.system.user-info', compact('admin'));
    }

    public function updateUserInfo(AdminSystemUserInfoRequest $request)
    {
        $admin = admin_user();

        $data = $request->validated();

        if ((! empty($data['new_password']) || $request->emailChanged()) && ! Hash::check($data['current_password'], $admin->password)) {
            return back()->withErrors([
                'current_password' => 'Current password is incorrect.',
            ]);
        }

        if (! empty($data['new_password'])) {
            $admin->password = Hash::make($data['new_password']);
            $admin->password_changed_at = now();
        }

        $admin->email = strtolower(trim($data['email']));
        $admin->save();

        AppLog::info(
            'Admin account updated',
            'Admin ID: '.$admin->id.' | Email: '.$admin->email,
            'admin.system'
        );

        if (! empty($data['new_password'])) {
            Auth::guard('admin')->logout();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->with('success', 'Password changed. Please login again.');
        }

        return back()->with('success', 'Account updated successfully.');
    }
}
