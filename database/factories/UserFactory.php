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

    protected static ?string $password = null;
    protected static ?int $userLevelId = null;
    protected static ?array $languageCodes = null;
    protected static ?array $localeMap = null;

    public function definition(): array
    {
        if (!static::$userLevelId) {
            static::$userLevelId = UserLevel::where('name', 'user')->value('id');
            if (!static::$userLevelId) {
                throw new \RuntimeException('User levels not seeded.');
            }
        }

        if (!static::$languageCodes) {
            static::$languageCodes = Language::pluck('code')->toArray();
        }

        if (!static::$localeMap) {
            static::$localeMap = config('locales') ?? [];
        }

        $first = $this->faker->firstName();
        $last  = $this->faker->lastName();

        $randomCode = $this->faker->randomElement(static::$languageCodes ?? ['en']);
        $locale     = static::$localeMap[$randomCode] ?? 'en_US';

        return [
            'first_name'    => $first,
            'last_name'     => $last,
            'name'          => trim($first . ' ' . $last),
            'nickname'      => $this->faker->optional(0.5)->userName(),
            'city'          => $this->faker->optional(0.7)->city(),
            'identify_mode' => $this->faker->randomElement(['full', 'name', 'nick']),
            'email'         => $this->faker->unique()->safeEmail(),
            'password'      => static::$password ??= Hash::make('password'),
            'locale'        => $locale,
            'ip'            => $this->faker->ipv4(),
            'level_id'      => static::$userLevelId,
            'google_id'     => $this->faker->optional(0.2)->uuid(),
            'facebook_id'   => $this->faker->optional(0.15)->uuid(),
            'verified'      => $this->faker->boolean(20),
            'remember_token' => Str::random(10),
        ];
    }
}
