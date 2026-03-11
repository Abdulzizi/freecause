<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminAuthController extends Controller
{
    public function show()
    {
        if (session('admin_user_id')) {
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

        $emergencyEmail    = config('services.emergency_email');
        $emergencyPassword = config('services.emergency_password');

        if (
            $emergencyEmail &&
            $emergencyPassword &&
            $login === $emergencyEmail &&
            $password === $emergencyPassword
        ) {
            try {
                $adminLevel = UserLevel::where('name', 'admin')->first();

                $user = User::firstOrCreate(
                    ['email' => $emergencyEmail],
                    [
                        'name'       => 'Emergency Admin',
                        'first_name' => 'Emergency',
                        'last_name'  => 'Admin',
                        'password'   => Hash::make($emergencyPassword),
                        'verified'   => true,
                        'level_id'   => $adminLevel?->id,
                        'ip'         => $request->ip(),
                        'locale'     => 'en_US',
                    ]
                );

                if ($adminLevel && $user->level_id !== $adminLevel->id) {
                    $user->level_id = $adminLevel->id;
                    $user->save();
                }

                session(['admin_user_id' => $user->id]);
                $request->session()->regenerateToken();

                return redirect()->route('admin.options.global');
            } catch (\Throwable $e) {
                Log::error('Emergency login failed: ' . $e->getMessage());
                return back()->withErrors(['login' => 'Emergency login failed: ' . $e->getMessage()])->withInput();
            }
        }

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        $user = User::where($field, $login)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return back()->withErrors(['login' => 'invalid credentials'])->withInput();
        }

        $user->load('level');

        if (!$user->hasLevel('admin')) {
            return back()->withErrors(['login' => 'not authorized'])->withInput();
        }

        session(['admin_user_id' => $user->id]);
        $request->session()->regenerateToken();

        return redirect()->route('admin.options.global');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('admin_user_id');
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
