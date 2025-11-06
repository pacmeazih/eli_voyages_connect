<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Dossier;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    /**
     * Upload a document for a dossier
     */
    public function uploadDocument(
        Dossier $dossier,
        UploadedFile $file,
        string $type = 'general',
        ?string $description = null
    ): Document {
        // Generate unique filename
        $filename = $this->generateUniqueFilename($file);
        
        // Build storage path: dossiers/{reference}/{type}/{filename}
        $path = "dossiers/{$dossier->reference}/{$type}/{$filename}";
        
        // Store file to S3 (or configured disk)
        $storedPath = Storage::disk('s3')->put($path, file_get_contents($file->getRealPath()));
        
        // Create document record
        $document = Document::create([
            'dossier_id' => $dossier->id,
            'name' => $file->getClientOriginalName(),
            'type' => $type,
            'path' => $storedPath ?: $path,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'description' => $description,
            'uploaded_by' => auth()->id(),
        ]);
        
        // Log activity
        activity()
            ->performedOn($document)
            ->causedBy(auth()->user())
            ->withProperties(['dossier_id' => $dossier->id])
            ->log('Document uploaded: ' . $file->getClientOriginalName());
        
        return $document;
    }

    /**
     * Download a document
     */
    public function downloadDocument(Document $document): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        // Check authorization (should be done in controller/policy)
        
        // Log activity
        activity()
            ->performedOn($document)
            ->causedBy(auth()->user())
            ->log('Document downloaded: ' . $document->name);
        
        return Storage::disk('s3')->download($document->path, $document->name);
    }

    /**
     * Delete a document
     */
    public function deleteDocument(Document $document): bool
    {
        // Delete from storage
        Storage::disk('s3')->delete($document->path);
        
        // Log activity
        activity()
            ->performedOn($document)
            ->causedBy(auth()->user())
            ->log('Document deleted: ' . $document->name);
        
        // Delete database record
        return $document->delete();
    }

    /**
     * Get temporary URL for document preview
     */
    public function getTemporaryUrl(Document $document, int $minutes = 60): string
    {
        return Storage::disk('s3')->temporaryUrl($document->path, now()->addMinutes($minutes));
    }

    /**
     * Generate unique filename
     */
    private function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $basename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $sanitized = Str::slug($basename);
        
        return $sanitized . '_' . time() . '_' . Str::random(8) . '.' . $extension;
    }

    /**
     * Validate file before upload
     */
    public function validateFile(UploadedFile $file): array
    {
        $errors = [];
        
        // Max size: 50MB
        if ($file->getSize() > 50 * 1024 * 1024) {
            $errors[] = 'File size exceeds 50MB limit.';
        }
        
        // Allowed types
        $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 
                         'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            $errors[] = 'File type not allowed. Allowed types: PDF, JPG, PNG, DOC, DOCX.';
        }
        
        return $errors;
    }

    /**
     * Create a new version of a document
     */
    public function createVersion(Document $document, UploadedFile $file): Document
    {
        // Upload new file
        $newDocument = $this->uploadDocument(
            $document->dossier,
            $file,
            $document->type,
            $document->description
        );
        
        // Link to previous version
        $newDocument->update(['previous_version_id' => $document->id]);
        
        return $newDocument;
    }
}
