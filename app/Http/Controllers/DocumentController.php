<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Dossier;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected DocumentService $documentService
    ) {}

    /**
     * Display a listing of documents for a dossier.
     */
    public function index(Request $request, Dossier $dossier)
    {
        $this->authorize('view', $dossier);

        $query = $dossier->documents()->with(['uploader']);

        // Search filter (by name)
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Type filter
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        // Uploader filter
        if ($uploader = $request->input('uploader')) {
            $query->where('uploaded_by', $uploader);
        }

        // Sort
        $sortBy = $request->input('sort', 'created_at');
        $sortDir = $request->input('direction', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $documents = $query->paginate(15)->withQueryString();

        // Aggregate data for filter dropdowns
        $types = $dossier->documents()->distinct()->pluck('type');
        $uploaders = \App\Models\User::whereIn('id', $dossier->documents()->distinct()->pluck('uploaded_by'))->get(['id', 'name']);

        return inertia('Documents/Index', [
            'dossier' => $dossier,
            'documents' => $documents,
            'types' => $types,
            'uploaders' => $uploaders,
            'filters' => $request->only(['search', 'type', 'uploader', 'sort', 'direction']),
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
        $this->authorize('view', $document);
        $this->authorize('download', $document);

        return $this->documentService->downloadDocument($document);
    }

    /**
     * View document in browser (inline display for PDF and images).
     */
    public function view(Document $document)
    {
        $this->authorize('view', $document);

        // Check if file exists
        if (!Storage::exists($document->path)) {
            abort(404, 'Document file not found');
        }

        // Get file content
        $fileContent = Storage::get($document->path);
        $mimeType = $document->mime_type ?? 'application/octet-stream';

        // Return inline response for viewing in browser
        return response($fileContent, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $document->original_filename . '"');
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

    /**
     * Approve uploaded document (Team only)
     */
    public function approve(Document $document)
    {
        $this->authorize('view', $document->dossier);
        $this->authorize('validate documents'); // Staff permission

        $document->approve(auth()->user());

        activity()
            ->performedOn($document)
            ->causedBy(auth()->user())
            ->log('Document approved: ' . $document->name);

        return back()->with('success', 'Document approuvé avec succès!');
    }

    /**
     * Reject uploaded document with reason (Team only)
     */
    public function reject(Request $request, Document $document)
    {
        $this->authorize('view', $document->dossier);
        $this->authorize('validate documents'); // Staff permission

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $document->reject(auth()->user(), $validated['reason']);

        activity()
            ->performedOn($document)
            ->causedBy(auth()->user())
            ->log('Document rejected: ' . $document->name . ' - Reason: ' . $validated['reason']);

        return back()->with('success', 'Document rejeté. Le client sera notifié.');
    }
}
