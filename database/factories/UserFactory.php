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
        $first = $this->faker->firstName();
        $last  = $this->faker->lastName();

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

            'nickname' => $this->faker->optional(50)->userName(),
            'city'     => $this->faker->optional(70)->city(),
            'identify_mode' => $this->faker->randomElement(['full', 'name', 'nick']),

            'email'    => $this->faker->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),

            'locale' => $localeMap[$this->faker->randomElement($languageCodes)] ?? 'en_US',
            'ip'     => $this->faker->ipv4(),

            'level_id' => $userLevel->id,

            'google_id'   => $this->faker->optional(20)->uuid(),
            'facebook_id' => $this->faker->optional(15)->uuid(),

            'verified' => $this->faker->boolean(20),
            'remember_token' => Str::random(10),
        ];
    }
}
