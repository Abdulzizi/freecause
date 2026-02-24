<?php

namespace Database\Factories;

use App\Models\Language;
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

        $languageCodes = Language::pluck('code')->toArray();
        $localeMap = config('locales');

        $userLevel = UserLevel::where('name', 'user')->first();

        if (!$userLevel) {
            throw new \RuntimeException('User levels not seeded.');
        }

        return [
            'first_name' => $first,
            'last_name'  => $last,
            'name'       => trim($first . ' ' . $last),

            'nickname' => fake()->optional(50)->userName(),
            'city'     => fake()->optional(70)->city(),
            'identify_mode' => fake()->randomElement(['full', 'name', 'nick']),

            'email'    => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),

            'locale' => $localeMap[fake()->randomElement($languageCodes)] ?? 'en_US',
            'ip'     => fake()->ipv4(),

            'level_id' => $userLevel->id,

            'google_id'   => fake()->optional(20)->uuid(),
            'facebook_id' => fake()->optional(15)->uuid(),

            'verified' => fake()->boolean(20),
            'remember_token' => Str::random(10),
        ];
    }
}
