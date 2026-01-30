<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Petition;
use App\Models\Signature;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::firstOrCreate(
            ['email' => 'demo@freecause.test'],
            [
                'name' => 'Demo User',
                'password' => bcrypt('password'),
                'locale' => 'en',
            ]
        );

        foreach (['en', 'fr', 'it'] as $loc) {
            $petitions = Petition::factory()
                ->count(30)
                ->for($owner)
                ->locale($loc)
                ->create();

            foreach ($petitions as $petition) {
                $count = rand(0, 200);

                if ($count > 0) {
                    Signature::factory()
                        ->count($count)
                        ->locale($loc)
                        ->create([
                            'petition_id' => $petition->id,
                            'user_id' => null,
                        ]);

                    $petition->update(['signature_count' => $count]);
                }
            }
        }
    }
}
