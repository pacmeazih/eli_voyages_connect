<?php

namespace Database\Factories;

use App\Models\Dossier;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class DossierFactory extends Factory
{
    protected $model = Dossier::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            // 'reference' auto-generated in model boot, so omit here
            'title' => $this->faker->sentence(3),
            'notes' => $this->faker->paragraph(),
        ];
    }
}
