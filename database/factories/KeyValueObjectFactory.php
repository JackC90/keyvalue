<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KeyValueObject>
 */
class KeyValueObjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => $this->faker->word(),
            'value' => json_encode([
                'name' => $this->faker->word(),
                'description' => $this->faker->sentence(),
                'price' => $this->faker->numberBetween(5, 100) * 100,
                'address' => [
                    'street' => $this->faker->streetAddress(),
                    'city' => $this->faker->city(),
                    'country' => $this->faker->country(),
                ],
                'active' => $this->faker->boolean(),
            ]),
            'created_at' => $this->faker->dateTimeBetween('-1 year'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month'),
        ];
    }

    // Helper method to create with specific key
    public function withKey(string $key): self
    {
        return $this->state(fn (array $attributes) => [
            'key' => $key,
        ]);
    }

    // Helper method to create with specific value
    public function withValue(array $value): self
    {
        return $this->state(fn (array $attributes) => [
            'value' => json_encode($value),
        ]);
    }
}
