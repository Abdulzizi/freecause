<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AppLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminSystemController extends Controller
{
    public function userInfo()
    {
        $admin = admin_user();

        return view('admin.system.user-info', compact('admin'));
    }

    public function updateUserInfo(Request $request)
    {
        $admin = admin_user();

        $data = $request->validate([
            'email' => ['required', 'email', 'max:190'],

            'current_password' => [
                Rule::requiredIf($request->filled('new_password')),
                'nullable',
                'string'
            ],

            'new_password' => [
                'nullable',
                'confirmed',
                'min:8',
                'max:72'
            ],
        ]);

        if (!empty($data['new_password'])) {
            if (!Hash::check($data['current_password'], $admin->password)) {
                return back()->withErrors([
                    'current_password' => 'Current password is incorrect.',
                ]);
            }

            $admin->password = Hash::make($data['new_password']);
        }

        $admin->email = strtolower(trim($data['email']));
        $admin->save();

        AppLog::info(
            'Admin account updated',
            'Admin ID: ' . $admin->id . ' | Email: ' . $admin->email,
            'admin.system'
        );

        if (!empty($data['new_password'])) {
            $request->session()->forget('admin_user_id');
            $request->session()->regenerateToken();
            return redirect()->route('admin.login')->with('success', 'Password changed. Please login again.');
        }

        return back()->with('success', 'Account updated successfully.');
    }
}
