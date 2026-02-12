<?php

namespace Database\Factories;

use App\Models\Signature;
use App\Models\Petition;
use Illuminate\Database\Eloquent\Factories\Factory;

class SignatureFactory extends Factory
{
    protected $model = Signature::class;

    public function definition(): array
    {
        return [
            'petition_id' => Petition::factory(),
            'user_id' => null,
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'locale' => 'en',
            'text' => $this->faker->optional()->sentence(),
            'confirmed' => $this->faker->boolean(90),
            'ip_address' => $this->faker->ipv4(),
            'is_spam' => false,
        ];
    }

    public function locale(string $locale): self
    {
        return $this->state(fn() => ['locale' => $locale]);
    }
}
