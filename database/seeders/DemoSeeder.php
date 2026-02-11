<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use App\Models\Petition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $userCount = (int) env('SEED_USERS', 1000);

        if (User::count() < $userCount) {
            User::factory()
                ->count($userCount)
                ->state(fn() => [
                    'level' => 'user',
                    'verified' => rand(0, 1),
                    'locale' => collect(['en_US', 'fr_FR', 'it_IT'])->random(),
                ])
                ->create();
        }
        $petitionsPerLocale   = (int) env('SEED_PETITIONS', 2000);
        $minSign              = (int) env('SEED_SIG_MIN', 5);
        $maxSign              = (int) env('SEED_SIG_MAX', 200);
        $signatureBatchSize   = (int) env('SEED_SIG_BATCH', 5000);

        $categories = Category::query()
            ->where('is_active', true)
            ->pluck('id');

        if ($categories->isEmpty()) {
            $categories = Category::query()->pluck('id');
        }

        if ($categories->isEmpty()) {
            throw new \RuntimeException('No categories found. Run CategorySeeder first.');
        }

        $owner = User::firstOrCreate(
            ['email' => 'demo@freecause.test'],
            [
                'name' => 'Demo User',
                'first_name' => 'Demo',
                'last_name' => 'User',
                'password' => bcrypt('password'),
                'locale' => 'en_US',
                'level' => 'admin',
                'verified' => true,
                'ip' => '127.0.0.1',
            ]
        );

        $locales = ['en', 'fr', 'it'];
        $now = now();

        $chunkSize = (int) env('SEED_PETITION_CHUNK', 250);

        $users = User::where('level', 'user')->get(['id', 'name', 'first_name', 'last_name', 'email']);

        foreach ($locales as $loc) {

            $remaining = $petitionsPerLocale;

            while ($remaining > 0) {
                $take = min($chunkSize, $remaining);
                $remaining -= $take;

                $createdPetitions = Petition::factory()
                    ->count($take)
                    ->state(fn() => [
                        'user_id' => $owner->id,
                        'category_id' => $categories->random(),
                    ])
                    ->create();

                foreach ($createdPetitions as $petition) {
                    foreach ($locales as $trLoc) {
                        $title = fake($trLoc)->sentence(6);

                        $petition->translations()->create([
                            'locale' => $trLoc,
                            'title' => $title,
                            'slug' => Str::slug($title) . '-' . Str::lower(Str::random(6)),
                            'description' => fake($trLoc)->paragraphs(4, true),
                        ]);
                    }
                }

                $signatureRows = [];

                foreach ($createdPetitions as $petition) {
                    $count = rand($minSign, $maxSign);

                    Petition::whereKey($petition->id)->update(['signature_count' => $count]);

                    $usedUserIds = [];

                    for ($i = 0; $i < $count; $i++) {
                        $email = $petition->id . '-' . $i . '@seed.test';

                        $useRealUser = rand(0, 1);

                        if ($useRealUser && $users->isNotEmpty()) {
                            $availableUsers = $users->whereNotIn('id', $usedUserIds);

                            if ($availableUsers->isEmpty()) {
                                $useRealUser = false;
                            } else {
                                $user = $availableUsers->random();
                                $usedUserIds[] = $user->id;

                                $name = $user->name
                                    ?: trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))
                                    ?: 'Anonymous';

                                $email = $user->email ?: ($petition->id . '-' . $i . '@seed.test');

                                $signatureRows[] = [
                                    'petition_id' => $petition->id,
                                    'user_id' => $user->id,
                                    'name' => $name,
                                    'email' => $email,
                                    'locale' => $loc,
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ];

                                continue;
                            }
                        } else {
                            $signatureRows[] = [
                                'petition_id' => $petition->id,
                                'user_id' => null,
                                'name' => "Seeder " . Str::title(Str::random(6)),
                                'email' => $email,
                                'locale' => $loc,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }

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
