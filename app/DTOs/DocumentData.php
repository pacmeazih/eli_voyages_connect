<?php

namespace App\DTOs;

use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;

class DocumentData
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $dossier_id,
        public readonly string $type,
        public readonly ?string $nom,
        public readonly ?string $fichier,
        public readonly ?int $taille,
        public readonly ?string $mime_type,
        public readonly string $statut = 'en_attente',
        public readonly ?string $commentaire = null,
        public readonly ?UploadedFile $file = null,
    ) {}

    /**
     * Create from request with uploaded file
     */
    public static function fromRequest(Request $request, int $dossierId): self
    {
        $file = $request->file('file');

        return new self(
            id: $request->input('id'),
            dossier_id: $dossierId,
            type: $request->input('type', 'general'),
            nom: $file ? $file->getClientOriginalName() : $request->input('nom'),
            fichier: null, // Will be set after upload
            taille: $file ? $file->getSize() : null,
            mime_type: $file ? $file->getMimeType() : null,
            statut: $request->input('statut', 'en_attente'),
            commentaire: $request->input('commentaire'),
            file: $file,
        );
    }

    /**
     * Create from uploaded file
     */
    public static function fromUploadedFile(UploadedFile $file, int $dossierId, string $type = 'general'): self
    {
        return new self(
            id: null,
            dossier_id: $dossierId,
            type: $type,
            nom: $file->getClientOriginalName(),
            fichier: null, // Will be set after upload
            taille: $file->getSize(),
            mime_type: $file->getMimeType(),
            statut: 'en_attente',
            commentaire: null,
            file: $file,
        );
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            dossier_id: (int) $data['dossier_id'],
            type: $data['type'],
            nom: $data['nom'] ?? null,
            fichier: $data['fichier'] ?? null,
            taille: $data['taille'] ?? null,
            mime_type: $data['mime_type'] ?? null,
            statut: $data['statut'] ?? 'en_attente',
            commentaire: $data['commentaire'] ?? null,
            file: null,
        );
    }

    /**
     * Convert to array for database storage
     */
    public function toArray(): array
    {
        return [
            'dossier_id' => $this->dossier_id,
            'type' => $this->type,
            'nom' => $this->nom,
            'fichier' => $this->fichier,
            'taille' => $this->taille,
            'mime_type' => $this->mime_type,
            'statut' => $this->statut,
            'commentaire' => $this->commentaire,
            'date_upload' => now(),
        ];
    }

    /**
     * Check if document has a file
     */
    public function hasFile(): bool
    {
        return $this->file !== null;
    }

    /**
     * Check if document is approved
     */
    public function isApproved(): bool
    {
        return $this->statut === 'approuve';
    }

    /**
     * Check if document is rejected
     */
    public function isRejected(): bool
    {
        return $this->statut === 'rejete';
    }

    /**
     * Check if document is PDF
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Check if document is image
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    /**
     * Get file size in MB
     */
    public function getSizeInMB(): ?float
    {
        if (!$this->taille) {
            return null;
        }

        return round($this->taille / 1024 / 1024, 2);
    }

    /**
     * With updated file path
     */
    public function withFichier(string $fichier): self
    {
        return new self(
            id: $this->id,
            dossier_id: $this->dossier_id,
            type: $this->type,
            nom: $this->nom,
            fichier: $fichier,
            taille: $this->taille,
            mime_type: $this->mime_type,
            statut: $this->statut,
            commentaire: $this->commentaire,
            file: $this->file,
        );
    }
}
