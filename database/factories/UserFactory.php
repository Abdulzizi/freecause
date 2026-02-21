<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserLevel;
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

        $languageCodes = \App\Models\Language::pluck('code')->toArray();

        $userLevel = UserLevel::where('name', 'user')->first();

        if (!$userLevel) {
            throw new \RuntimeException('User levels not seeded.');
        }

        return [
            'first_name' => $first,
            'last_name'  => $last,
            'name'       => trim($first . ' ' . $last),

            'email'    => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),

            'locale' => fake()->randomElement($languageCodes),
            'ip'     => fake()->ipv4(),

            'level_id' => $userLevel->id,

            'verified' => fake()->boolean(20),
            'remember_token' => Str::random(10),
        ];
    }
}
