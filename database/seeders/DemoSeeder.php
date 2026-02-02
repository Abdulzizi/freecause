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
            ['name' => 'Demo User', 'password' => bcrypt('password'), 'locale' => 'en']
        );

        $locales = ['en', 'fr', 'it'];
        $now = now();

        $chunkSize = (int) env('SEED_PETITION_CHUNK', 250);

        foreach ($locales as $loc) {

            $remaining = $petitionsPerLocale;

            while ($remaining > 0) {
                $take = min($chunkSize, $remaining);
                $remaining -= $take;

                $createdPetitions = Petition::factory()
                    ->count($take)
                    ->state(function () use ($owner, $loc, $categories) {
                        $seed = Str::random(12);

                        return [
                            'user_id' => $owner->id,
                            'locale' => $loc,
                            'status' => 'published',
                            'category_id' => $categories->random(),
                            'cover_image' => "https://picsum.photos/seed/{$seed}/1200/600",
                        ];
                    })
                    ->create();

                $signatureRows = [];

                foreach ($createdPetitions as $petition) {
                    $count = rand($minSign, $maxSign);

                    Petition::whereKey($petition->id)->update(['signature_count' => $count]);

                    for ($i = 0; $i < $count; $i++) {
                        $email = $petition->id . '-' . $i . '@seed.test';

                        $signatureRows[] = [
                            'petition_id' => $petition->id,
                            'user_id' => null,
                            'name' => "Seeder " . Str::title(Str::random(6)),
                            'email' => $email,
                            'locale' => $loc,
                            'created_at' => $now,
                            'updated_at' => $now,
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
