<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\Dossier;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class DocumentController extends ApiController
{
    public function store(Request $request, Dossier $dossier): JsonResponse
    {
        $this->authorize('upload', [Document::class, $dossier]);

        $data = $request->validate([
            'file' => 'required|file|max:10240' // up to 10MB
        ]);

        $file = $request->file('file');
        $path = $file->store('documents', ['disk' => config('filesystems.default')]);

        $doc = Document::create([
            'dossier_id' => $dossier->id,
            'uploaded_by' => $request->user()?->id,
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'version' => 1,
        ]);

        return $this->sendResponse($doc, 'Document uploaded', 201);
    }

    public function download(Dossier $dossier, Document $document)
    {
        // ensure the document belongs to dossier
        if ($document->dossier_id !== $dossier->id) {
            abort(404);
        }

        $this->authorize('view', $document);

        // Use default disk
        $disk = config('filesystems.default');

        if (!\Illuminate\Support\Facades\Storage::disk($disk)->exists($document->path)) {
            abort(404);
        }

        return \Illuminate\Support\Facades\Storage::disk($disk)->download($document->path, $document->filename);
    }
}
