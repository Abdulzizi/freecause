<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        $first = fake()->firstName();
        $last  = fake()->lastName();

        return [
            'first_name' => $first,
            'last_name' => $last,
            'name' => trim($first . ' ' . $last),

            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),

            'locale' => fake()->randomElement(['en_US', 'fr_FR', 'it_IT']),
            'ip' => fake()->ipv4(),
            'level' => 'user',
            'verified' => fake()->boolean(20),

            'remember_token' => Str::random(10),
        ];
    }
}
