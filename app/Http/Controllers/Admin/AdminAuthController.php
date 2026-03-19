<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLevel;
use Illuminate\Http\Request;
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

        // Admin username shortcut (defined in .env)
        $adminUsername = config('services.admin_username');
        $adminPassword = config('services.admin_username_password');
        if ($adminUsername && $adminPassword && $login === $adminUsername && $password === $adminPassword) {
            $user = User::whereHas('level', fn($q) => $q->where('name', 'admin'))->first();
            if (!$user) {
                // No admin user in DB yet — create one via the emergency path
                try {
                    $adminLevel = UserLevel::where('name', 'admin')->first();
                    $user = User::firstOrCreate(
                        ['email' => 'sadmin@freecause.local'],
                        [
                            'name'       => 'sadmin',
                            'first_name' => 'Super',
                            'last_name'  => 'Admin',
                            'password'   => Hash::make($adminPassword),
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
                } catch (\Throwable $e) {
                    Log::error('Sadmin bootstrap failed: ' . $e->getMessage());
                    return back()->withErrors(['login' => 'Login failed: ' . $e->getMessage()])->withInput();
                }
            }
            session(['admin_user_id' => $user->id]);
            $request->session()->regenerateToken();
            return redirect()->route('admin.options.global');
        }

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        $user = User::where($field, $login)->first();

        if (!$user) {
            return back()->withErrors(['login' => 'invalid credentials'])->withInput();
        }

        $passwordValid = Hash::check($password, $user->password);

        if (!$passwordValid && str_contains($user->password, ':')) {
            [$hash, $salt] = explode(':', $user->password, 2);
            if (md5(md5($password) . $salt) === $hash) {
                $user->password = Hash::make($password);
                $user->save();
                $passwordValid = true;
            }
        }

        if (!$passwordValid) {
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
