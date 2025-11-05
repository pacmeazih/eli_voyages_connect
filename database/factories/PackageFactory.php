<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Package::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->paragraph(),
            'destination' => $this->faker->city() . ', ' . $this->faker->country(),
            'duration' => $this->faker->numberBetween(3, 15),
            'price' => $this->faker->randomFloat(2, 500, 5000),
            'includes' => $this->faker->sentences(4, true),
            'excludes' => $this->faker->sentences(3, true),
            'max_travelers' => $this->faker->numberBetween(10, 30),
            'start_date' => $this->faker->dateTimeBetween('now', '+1 year'),
            'end_date' => function (array $attributes) {
                return $this->faker->dateTimeBetween(
                    $attributes['start_date'], 
                    (clone $attributes['start_date'])->modify('+' . $attributes['duration'] . ' days')
                );
            },
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Indicate that the package is published.
     */
    public function published(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'published',
            ];
        });
    }

    /**
     * Indicate that the package is a draft.
     */
    public function draft(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'draft',
            ];
        });
    }
}