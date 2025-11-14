<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class DossierData
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $client_id,
        public readonly int $package_id,
        public readonly ?string $reference,
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly string $statut = 'documents_requis',
        public readonly ?int $assigned_to = null,
        public readonly ?string $notes = null,
    ) {}

    /**
     * Create from request data
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            id: $request->input('id'),
            client_id: (int) $request->input('client_id'),
            package_id: (int) $request->input('package_id'),
            reference: $request->input('reference'),
            title: $request->input('title'),
            description: $request->input('description'),
            statut: $request->input('statut', 'documents_requis'),
            assigned_to: $request->input('assigned_to') ? (int) $request->input('assigned_to') : null,
            notes: $request->input('notes'),
        );
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            client_id: (int) $data['client_id'],
            package_id: (int) $data['package_id'],
            reference: $data['reference'] ?? null,
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            statut: $data['statut'] ?? 'documents_requis',
            assigned_to: isset($data['assigned_to']) ? (int) $data['assigned_to'] : null,
            notes: $data['notes'] ?? null,
        );
    }

    /**
     * Convert to array for database storage
     */
    public function toArray(): array
    {
        $data = [
            'client_id' => $this->client_id,
            'package_id' => $this->package_id,
            'statut' => $this->statut,
            'title' => $this->title,
            'description' => $this->description,
            'notes' => $this->notes,
        ];

        if ($this->reference) {
            $data['reference'] = $this->reference;
        }

        if ($this->assigned_to) {
            $data['assigned_to'] = $this->assigned_to;
        }

        return $data;
    }

    /**
     * Create for new dossier
     */
    public static function forNewDossier(int $clientId, int $packageId, ?array $additionalData = []): self
    {
        return new self(
            id: null,
            client_id: $clientId,
            package_id: $packageId,
            reference: null, // Will be auto-generated
            title: $additionalData['title'] ?? null,
            description: $additionalData['description'] ?? null,
            statut: $additionalData['statut'] ?? 'documents_requis',
            assigned_to: $additionalData['assigned_to'] ?? null,
            notes: $additionalData['notes'] ?? null,
        );
    }

    /**
     * Check if dossier can be submitted
     */
    public function canBeSubmitted(): bool
    {
        return in_array($this->statut, ['documents_requis', 'en_cours']);
    }

    /**
     * Check if dossier is in final state
     */
    public function isFinal(): bool
    {
        return in_array($this->statut, ['termine', 'archive', 'rejete']);
    }
}
