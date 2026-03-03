<?php

namespace Database\Factories;

use App\Models\Language;
use App\Models\User;
use App\Models\UserLevel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use function fake;

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

        $first = fake()->firstName();
        $last  = fake()->lastName();

        $randomCode = fake()->randomElement(static::$languageCodes ?? ['en']);
        $locale     = static::$localeMap[$randomCode] ?? 'en_US';

        return [
            'first_name' => $first,
            'last_name'  => $last,
            'name'       => trim($first . ' ' . $last),

            'nickname' => fake()->optional(50)->userName(),
            'city'     => fake()->optional(70)->city(),
            'identify_mode' => fake()->randomElement(['full', 'name', 'nick']),

            'email'    => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),

            'locale' => $locale,
            'ip'     => fake()->ipv4(),

            'level_id' => static::$userLevelId,

            'google_id'   => fake()->optional(20)->uuid(),
            'facebook_id' => fake()->optional(15)->uuid(),

            'verified' => fake()->boolean(20),
            'remember_token' => Str::random(10),
        ];
    }
}
