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
        
        // Store file to configured disk (local for cPanel, s3 for cloud)
        $storedPath = Storage::put($path, file_get_contents($file->getRealPath()));
        
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
        // Check if file exists
        if (!Storage::exists($document->path)) {
            abort(404, 'Le fichier n\'existe pas ou a été supprimé.');
        }

        // Log activity
        activity()
            ->performedOn($document)
            ->causedBy(auth()->user())
            ->log('Document downloaded: ' . $document->name);
        
        return Storage::download($document->path, $document->name);
    }

    /**
     * Delete a document
     */
    public function deleteDocument(Document $document): bool
    {
        // Delete from storage
        Storage::delete($document->path);
        
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
        // For local storage, return a route-based URL
        if (config('filesystems.default') === 'local') {
            return route('documents.download', $document->id);
        }
        
        // For S3, use temporary signed URLs
        return Storage::temporaryUrl($document->path, now()->addMinutes($minutes));
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
     * Validate file before upload - ENHANCED SECURITY
     */
    public function validateFile(UploadedFile $file): array
    {
        $errors = [];
        
        // 1. Check if file is actually uploaded (not tampered)
        if (!$file->isValid()) {
            $errors[] = 'Invalid file upload.';
            return $errors;
        }

        // 2. Max size by type
        $maxSizes = [
            'application/pdf' => 50 * 1024 * 1024, // 50MB for PDFs
            'image/jpeg' => 10 * 1024 * 1024,      // 10MB for images
            'image/png' => 10 * 1024 * 1024,       // 10MB for images
            'application/msword' => 20 * 1024 * 1024, // 20MB for DOC
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 20 * 1024 * 1024, // 20MB for DOCX
            'application/vnd.ms-excel' => 15 * 1024 * 1024, // 15MB for Excel
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 15 * 1024 * 1024, // 15MB for XLSX
        ];

        $detectedMime = $file->getMimeType();
        $maxSize = $maxSizes[$detectedMime] ?? 10 * 1024 * 1024; // Default 10MB
        
        if ($file->getSize() > $maxSize) {
            $errors[] = 'File size exceeds ' . ($maxSize / (1024 * 1024)) . 'MB limit for this file type.';
        }
        
        // 3. Whitelist of allowed MIME types (detected, not from extension)
        $allowedMimes = [
            'application/pdf',
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'text/csv',
        ];
        
        if (!in_array($detectedMime, $allowedMimes)) {
            $errors[] = 'File type not allowed. Detected type: ' . $detectedMime . '. Allowed: PDF, JPG, PNG, DOC, DOCX, XLS, XLSX, TXT, CSV.';
        }

        // 4. Verify extension matches MIME type (prevent spoofing)
        $extension = strtolower($file->getClientOriginalExtension());
        $expectedExtensions = [
            'application/pdf' => ['pdf'],
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/jpg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/gif' => ['gif'],
            'application/msword' => ['doc'],
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
            'application/vnd.ms-excel' => ['xls'],
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
            'text/plain' => ['txt'],
            'text/csv' => ['csv'],
        ];

        if (isset($expectedExtensions[$detectedMime]) && !in_array($extension, $expectedExtensions[$detectedMime])) {
            $errors[] = 'File extension does not match file content. Possible file tampering detected.';
        }

        // 5. Check for executable extensions (blacklist)
        $dangerousExtensions = ['php', 'exe', 'sh', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar', 'zip', 'rar'];
        if (in_array($extension, $dangerousExtensions)) {
            $errors[] = 'Executable and archive files are not allowed for security reasons.';
        }

        // 6. Check for double extensions (e.g., file.pdf.exe)
        $filename = $file->getClientOriginalName();
        $parts = explode('.', $filename);
        if (count($parts) > 2) {
            // Check if any part before the last is a dangerous extension
            array_pop($parts); // Remove last extension
            foreach ($parts as $part) {
                if (in_array(strtolower($part), $dangerousExtensions)) {
                    $errors[] = 'Double extensions detected. File rejected for security.';
                    break;
                }
            }
        }

        // 7. Filename sanitation check
        if (preg_match('/[<>:\"\/\\\\|?*\x00-\x1F]/', $filename)) {
            $errors[] = 'Filename contains invalid characters.';
        }

        // 8. Check filename length
        if (strlen($filename) > 255) {
            $errors[] = 'Filename is too long (max 255 characters).';
        }

        // 9. PHP file content detection (for images that might contain PHP code)
        if (in_array($detectedMime, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'])) {
            $fileContent = file_get_contents($file->getRealPath());
            if (preg_match('/<\?php|<\?=|<script|eval\(|base64_decode/i', $fileContent)) {
                $errors[] = 'Image file contains suspicious code. Upload rejected.';
            }
        }

        // 10. Verify actual file content for common types
        if ($detectedMime === 'application/pdf') {
            $fileContent = file_get_contents($file->getRealPath());
            // PDF files must start with %PDF
            if (substr($fileContent, 0, 4) !== '%PDF') {
                $errors[] = 'Invalid PDF file structure.';
            }
        }

        // 11. Check for null bytes in filename (directory traversal attempt)
        if (strpos($filename, "\0") !== false) {
            $errors[] = 'Invalid filename: null byte detected.';
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
