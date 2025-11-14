<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class ClientData
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $email,
        public readonly string $nom,
        public readonly string $prenom,
        public readonly ?string $telephone,
        public readonly ?string $adresse,
        public readonly ?string $city,
        public readonly ?string $country,
        public readonly ?string $date_naissance,
        public readonly ?string $lieu_naissance,
        public readonly ?string $nationalite,
        public readonly ?string $profession,
        public readonly ?string $passeport_numero,
        public readonly ?string $passeport_date_emission,
        public readonly ?string $passeport_date_expiration,
        public readonly ?string $civilite = null,
    ) {}

    /**
     * Create from request data
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            id: $request->input('id'),
            email: $request->input('email'),
            nom: $request->input('nom') ?? $request->input('last_name'),
            prenom: $request->input('prenom') ?? $request->input('first_name'),
            telephone: $request->input('telephone') ?? $request->input('phone'),
            adresse: $request->input('adresse') ?? $request->input('address'),
            city: $request->input('city'),
            country: $request->input('country'),
            date_naissance: $request->input('date_naissance'),
            lieu_naissance: $request->input('lieu_naissance'),
            nationalite: $request->input('nationalite'),
            profession: $request->input('profession'),
            passeport_numero: $request->input('passeport_numero') ?? $request->input('passport_number'),
            passeport_date_emission: $request->input('passeport_date_emission'),
            passeport_date_expiration: $request->input('passeport_date_expiration') ?? $request->input('passport_expiry'),
            civilite: $request->input('civilite'),
        );
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            email: $data['email'],
            nom: $data['nom'] ?? $data['last_name'] ?? '',
            prenom: $data['prenom'] ?? $data['first_name'] ?? '',
            telephone: $data['telephone'] ?? $data['phone'] ?? null,
            adresse: $data['adresse'] ?? $data['address'] ?? null,
            city: $data['city'] ?? null,
            country: $data['country'] ?? null,
            date_naissance: $data['date_naissance'] ?? null,
            lieu_naissance: $data['lieu_naissance'] ?? null,
            nationalite: $data['nationalite'] ?? null,
            profession: $data['profession'] ?? null,
            passeport_numero: $data['passeport_numero'] ?? $data['passport_number'] ?? null,
            passeport_date_emission: $data['passeport_date_emission'] ?? null,
            passeport_date_expiration: $data['passeport_date_expiration'] ?? $data['passport_expiry'] ?? null,
            civilite: $data['civilite'] ?? null,
        );
    }

    /**
     * Convert to array for database storage
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'telephone' => $this->telephone,
            'adresse' => $this->adresse,
            'city' => $this->city,
            'country' => $this->country,
            'date_naissance' => $this->date_naissance,
            'lieu_naissance' => $this->lieu_naissance,
            'nationalite' => $this->nationalite,
            'profession' => $this->profession,
            'passeport_numero' => $this->passeport_numero,
            'passeport_date_emission' => $this->passeport_date_emission,
            'passeport_date_expiration' => $this->passeport_date_expiration,
            'civilite' => $this->civilite,
        ];
    }

    /**
     * Get full name
     */
    public function fullName(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }

    /**
     * Check if passport is valid
     */
    public function hasValidPassport(): bool
    {
        if (!$this->passeport_date_expiration) {
            return false;
        }

        return now()->lessThan(new \DateTime($this->passeport_date_expiration));
    }

    /**
     * Check if all required fields are filled
     */
    public function isComplete(): bool
    {
        return !empty($this->email) &&
               !empty($this->nom) &&
               !empty($this->prenom) &&
               !empty($this->telephone) &&
               !empty($this->date_naissance) &&
               !empty($this->passeport_numero);
    }
}
