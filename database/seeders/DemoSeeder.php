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
        $petitionsPerLocale = (int) env('SEED_PETITIONS', 2000);
        $minSign = (int) env('SEED_SIG_MIN', 5);
        $maxSign = (int) env('SEED_SIG_MAX', 200);
        $signatureBatchSize = (int) env('SEED_SIG_BATCH', 5000);

        $categories = Category::query()->where('is_active', true)->get();
        if ($categories->isEmpty()) $categories = Category::query()->get();

        $owner = User::firstOrCreate(
            ['email' => 'demo@freecause.test'],
            ['name' => 'Demo User', 'password' => bcrypt('password'), 'locale' => 'en']
        );

        foreach (['en', 'fr', 'it'] as $loc) {

            // Create petitions
            $createdPetitions = Petition::factory()
                ->count($petitionsPerLocale)
                ->state(function () use ($owner, $loc, $categories) {
                    $seed = Str::random(12);

                    return [
                        'user_id' => $owner->id,
                        'locale' => $loc,
                        'category_id' => $categories->random()->id,
                        'cover_image' => "https://picsum.photos/seed/{$seed}/1200/600",
                    ];
                })
                ->create();

            // Build signatures in batches
            $signatureRows = [];
            $now = now();

            foreach ($createdPetitions as $petition) {
                $count = rand($minSign, $maxSign);

                // cached counter
                $petition->update(['signature_count' => $count]);

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
