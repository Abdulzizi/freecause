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
        $title = $this->faker->sentence(6);

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::lower(Str::random(6)),
            'description' => $this->faker->paragraphs(4, true),
            'goal_signatures' => $this->faker->numberBetween(100, 50000),
            'signature_count' => 0,
            'status' => 'published',
            'locale' => 'en',
        ];
    }

    public function locale(string $locale): self
    {
        return $this->state(fn() => ['locale' => $locale]);
    }
}
