<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class AdminResetPassword extends Command
{
    protected $signature = 'admin:reset-password {email} {password}';

    protected $description = 'Reset the password for an admin user by email';

    public function handle(): int
    {
        $email    = $this->argument('email');
        $password = $this->argument('password');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No user found with email: {$email}");
            return 1;
        }

        $user->load('level');

        if (! $user->hasLevel('admin')) {
            $this->error("User {$email} is not an admin.");
            return 1;
        }

        $user->password = Hash::make($password);
        $user->save();

        $this->info("Password updated for admin: {$email}");

        return 0;
    }
}
