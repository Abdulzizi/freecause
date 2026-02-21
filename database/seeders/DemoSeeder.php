<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Language;
use App\Models\User;
use App\Models\Petition;
use App\Models\UserLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $userCount = (int) env('SEED_USERS', 1000);

        $adminLevel = UserLevel::where('name', 'admin')->first();
        $userLevel  = UserLevel::where('name', 'user')->first();

        if (!$adminLevel || !$userLevel) {
            throw new \RuntimeException('User levels not seeded. Run UserLevelSeeder first.');
        }

        //* Locales
        $languageCodes = Language::pluck('code')->toArray();
        $localeMap     = config('locales'); // ['en' => 'en_US']

        if (empty($languageCodes)) {
            throw new \RuntimeException('No languages found.');
        }

        if (User::count() < $userCount) {
            User::factory()
                ->count($userCount)
                ->state(fn() => [
                    'level_id' => $userLevel->id,
                    'verified' => rand(0, 1),
                    'locale'   => $localeMap[fake()->randomElement($languageCodes)] ?? 'en_US',
                ])
                ->create();
        }

        User::firstOrCreate(
            ['email' => 'sadmin@f.test'],
            [
                'name'       => 'sadmin',
                'first_name' => 'Super',
                'last_name'  => 'Admin',
                'password'   => bcrypt('a'),
                'locale'     => 'en_US',
                'level_id'   => $adminLevel->id,
                'verified'   => true,
                'ip'         => '127.0.0.1',
            ]
        );

        //* Categories
        $categories = Category::where('is_active', true)->pluck('id');

        if ($categories->isEmpty()) {
            $categories = Category::pluck('id');
        }

        if ($categories->isEmpty()) {
            throw new \RuntimeException('No categories found. Run CategorySeeder first.');
        }

        //* Petition Owners
        $petitionOwners = User::where('level_id', $userLevel->id)
            ->pluck('id')
            ->toArray();

        if (empty($petitionOwners)) {
            throw new \RuntimeException('No normal users found for petition ownership.');
        }

        //* Petition & Signature Config
        $locales = Language::where('is_active', 1)->pluck('code')->toArray();

        $petitionsPerLocale = (int) env('SEED_PETITIONS', 2000);
        $minSign            = (int) env('SEED_SIG_MIN', 5);
        $maxSign            = (int) env('SEED_SIG_MAX', 200);
        $signatureBatchSize = (int) env('SEED_SIG_BATCH', 5000);
        $chunkSize          = (int) env('SEED_PETITION_CHUNK', 250);

        $users = User::where('level_id', $userLevel->id)->get(['id', 'name', 'first_name', 'last_name', 'email']);
        $now = now();


        //* Generate petitions
        foreach ($locales as $loc) {
            $remaining = $petitionsPerLocale;
            while ($remaining > 0) {
                $take = min($chunkSize, $remaining);
                $remaining -= $take;

                $createdPetitions = Petition::factory()
                    ->count($take)
                    ->state(fn() => [
                        'user_id'     => fake()->randomElement($petitionOwners),
                        'category_id' => $categories->random(),
                    ])
                    ->create();

                //* Translations
                foreach ($createdPetitions as $petition) {
                    foreach ($locales as $trLoc) {
                        $title = fake()->sentence(6);
                        $petition->translations()->create([
                            'locale'      => $trLoc,
                            'title'       => $title,
                            'slug'        => Str::slug($title) . '-' . Str::lower(Str::random(6)),
                            'description' => fake()->paragraphs(4, true),
                        ]);
                    }
                }

                //* Signatures

                $signatureRows = [];

                foreach ($createdPetitions as $petition) {
                    $count = rand($minSign, $maxSign);
                    $petition->update(['signature_count' => $count]);
                    $usedUserIds = [];

                    for ($i = 0; $i < $count; $i++) {
                        $email = $petition->id . '-' . $i . '@seed.test';
                        $useRealUser = rand(0, 1);

                        if ($useRealUser && $users->isNotEmpty()) {
                            $availableUsers = $users->whereNotIn('id', $usedUserIds);
                            if ($availableUsers->isNotEmpty()) {

                                $user = $availableUsers->random();
                                $usedUserIds[] = $user->id;

                                $signatureRows[] = [
                                    'petition_id' => $petition->id,
                                    'user_id'     => $user->id,
                                    'name'        => $user->name
                                        ?: trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))
                                        ?: 'Anonymous',
                                    'email'       => $user->email ?: $email,
                                    'locale'      => $loc,
                                    'text'        => fake()->optional()->sentence(),
                                    'confirmed'   => rand(0, 1),
                                    'ip_address'  => fake()->ipv4(),
                                    'is_spam'     => rand(0, 50) === 1,
                                    'created_at'  => $now,
                                    'updated_at'  => $now,
                                ];

                                continue;
                            }
                        }

                        // anonyanus signature fallback
                        $signatureRows[] = [
                            'petition_id' => $petition->id,
                            'user_id'     => null,
                            'name'        => "Seeder " . Str::title(Str::random(6)),
                            'email'       => $email,
                            'locale'      => $loc,
                            'text'        => fake()->optional()->sentence(),
                            'confirmed'   => rand(0, 1),
                            'ip_address'  => fake()->ipv4(),
                            'is_spam'     => rand(0, 50) === 1,
                            'created_at'  => $now,
                            'updated_at'  => $now,
                        ];

                        if (count($signatureRows) >= $signatureBatchSize) {
                            DB::table('signatures')->insert($signatureRows);
                            $signatureRows = [];
                        }
                    }
                }

                if (!empty($signatureRows)) {
                    DB::table('signatures')->insert($signatureRows);
                }
            }
        }
    }
}
