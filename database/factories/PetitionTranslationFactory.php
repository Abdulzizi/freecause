<?php

namespace Database\Factories;

use App\Models\Petition;
use App\Models\PetitionTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class PetitionTranslationFactory extends Factory
{
    protected $model = PetitionTranslation::class;

    public function definition(): array
    {
        return [
            'petition_id' => Petition::factory(),
            'locale' => 'en',
            'title' => $this->faker->sentence(5),
            'slug' => $this->faker->unique()->slug(2),
            'description' => '<p>'.$this->faker->paragraphs(3, true).'</p>',
        ];
    }
}
