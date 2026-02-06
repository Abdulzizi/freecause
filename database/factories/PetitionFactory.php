<?php

namespace Database\Factories;

use App\Models\Petition;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PetitionFactory extends Factory
{
    protected $model = Petition::class;

    public function definition(): array
    {
        $allowedGoals = [
            50,
            100,
            1000,
            5000,
            10000,
            50000,
            100000,
            500000,
            1000000,
            10000000,
        ];

        return [
            'user_id' => User::factory(),
            'goal_signatures' => $this->faker->randomElement($allowedGoals),
            'signature_count' => 0,
            'status' => 'published',

            'target' => $this->faker->optional(0.6)->name(),
            'tags' => $this->faker->optional(0.7)->words(rand(2, 6), true),
            'city' => $this->faker->optional(0.5)->city(),

            'community' => $this->faker->optional(0.25)->company(),
            'community_url' => $this->faker->optional(0.25)->url(),
            'youtube_url' => $this->faker->optional(0.2)->url(),
            'image_url' => null,
            'cover_image' =>  'https://picsum.photos/seed/' . $this->faker->uuid . '/1200/630'
        ];
    }
}
