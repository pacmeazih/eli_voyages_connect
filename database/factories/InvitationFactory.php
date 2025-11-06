<?php

namespace Database\Factories;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvitationFactory extends Factory
{
    protected $model = Invitation::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'token' => Str::random(64),
            'role' => $this->faker->randomElement(['Consultant', 'Agent', 'Client']),
            'invited_by' => User::factory(),
            'dossier_id' => null, // optional
            'expires_at' => now()->addDays(7),
            'accepted_at' => null,
        ];
    }

    public function expired(): self
    {
        return $this->state(fn() => [
            'expires_at' => now()->subHour(),
        ]);
    }

    public function withDossier(): self
    {
        return $this->state(fn() => [
            'dossier_id' => \App\Models\Dossier::factory(),
        ]);
    }
}
