<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Dossier;
use App\Services\DocumentService;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(
        protected DocumentService $documentService
    ) {}

    /**
     * Display a listing of documents for a dossier.
     */
    public function index(Dossier $dossier)
    {
        $this->authorize('view', $dossier);

        $documents = $dossier->documents()
            ->with(['uploader'])
            ->latest()
            ->get();

        return inertia('Documents/Index', [
            'dossier' => $dossier,
            'documents' => $documents,
        ]);
    }

    /**
     * Store a newly uploaded document.
     */
    public function store(Request $request, Dossier $dossier)
    {
        $this->authorize('view', $dossier);
        $this->authorize('upload documents');

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:51200'], // 50MB
            'type' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        // Validate file
        $errors = $this->documentService->validateFile($validated['file']);
        if (!empty($errors)) {
            return back()->withErrors(['file' => implode(' ', $errors)]);
        }

        // Upload document
        $document = $this->documentService->uploadDocument(
            $dossier,
            $validated['file'],
            $validated['type'],
            $validated['description'] ?? null
        );

        return back()->with('success', 'Document uploaded successfully!');
    }

    /**
     * Display the specified document.
     */
    public function show(Document $document)
    {
        $this->authorize('view', $document->dossier);

        return inertia('Documents/Show', [
            'document' => $document->load(['dossier', 'uploader', 'previousVersion']),
            'temporaryUrl' => $this->documentService->getTemporaryUrl($document),
        ]);
    }

    /**
     * Download the specified document.
     */
    public function download(Document $document)
    {
        $this->authorize('view', $document->dossier);
        $this->authorize('download documents');

        return $this->documentService->downloadDocument($document);
    }

    /**
     * Update the specified document.
     */
    public function update(Request $request, Document $document)
    {
        $this->authorize('view', $document->dossier);
        $this->authorize('edit documents');

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $document->update($validated);

        activity()
            ->performedOn($document)
            ->causedBy(auth()->user())
            ->log('Document updated: ' . $document->name);

        return back()->with('success', 'Document updated successfully!');
    }

    /**
     * Remove the specified document.
     */
    public function destroy(Document $document)
    {
        $this->authorize('view', $document->dossier);
        $this->authorize('delete documents');

        $this->documentService->deleteDocument($document);

        return back()->with('success', 'Document deleted successfully!');
    }

    /**
     * Upload a new version of a document.
     */
    public function version(Request $request, Document $document)
    {
        $this->authorize('view', $document->dossier);
        $this->authorize('edit documents');

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:51200'], // 50MB
        ]);

        // Validate file
        $errors = $this->documentService->validateFile($validated['file']);
        if (!empty($errors)) {
            return back()->withErrors(['file' => implode(' ', $errors)]);
        }

        // Create new version
        $newDocument = $this->documentService->createVersion($document, $validated['file']);

        return back()->with('success', 'New version uploaded successfully!');
    }
}
