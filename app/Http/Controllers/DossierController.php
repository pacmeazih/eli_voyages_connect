<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use App\Models\Client;
use App\Models\User;
use App\Services\DossierService;
use App\Notifications\DossierCreatedNotification;
use App\Notifications\DossierStatusChangedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DossierController extends Controller
{
    use AuthorizesRequests;

    protected DossierService $dossierService;

    public function __construct(DossierService $dossierService)
    {
        $this->dossierService = $dossierService;
    }
    /**
     * Display a listing of dossiers.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Dossier::class);

        $query = Dossier::with(['client'])
            ->withCount('documents')
            ->latest();

        // Filter by user role
        if (auth()->user()->hasRole('Client')) {
            $query->where('client_id', auth()->id());
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $dossiers = $query->paginate(15);

        return inertia('Dossiers/Index', [
            'dossiers' => $dossiers,
            'filters' => $request->only('search', 'status'),
            'canCreate' => auth()->user()->can('create', Dossier::class),
        ]);
    }

    /**
     * Show the form for creating a new dossier.
     */
    public function create()
    {
        $this->authorize('create', Dossier::class);

        $clients = Client::select('id', 'name', 'email')->get();

        return inertia('Dossiers/Create', [
            'clients' => $clients,
        ]);
    }

    /**
     * Store a newly created dossier.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Dossier::class);

        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $dossier = DB::transaction(function () use ($validated) {
            $dossier = Dossier::create($validated);

            // Log activity
            activity()
                ->performedOn($dossier)
                ->causedBy(auth()->user())
                ->withProperties(['reference' => $dossier->reference])
                ->log('Dossier created');

            return $dossier;
        });

        // Send notification to client
        $client = $dossier->client;
        if ($client && $client->email) {
            // Find user account linked to this client
            $user = User::where('email', $client->email)->first();
            if ($user) {
                $user->notify(new DossierCreatedNotification($dossier, app()->getLocale()));
            }
        }

        return redirect()->route('dossiers.show', $dossier)
            ->with('success', "Dossier {$dossier->reference} created successfully!");
    }

    /**
     * Display the specified dossier.
     */
    public function show(Dossier $dossier)
    {
        $this->authorize('view', $dossier);

        // Use service to get dossier with all relations
        $dossier = $this->dossierService->getDossierWithRelations($dossier->id);

        if (!$dossier) {
            abort(404);
        }

        // Get progress using service
        $progress = $this->dossierService->getProgress($dossier->id);

        return inertia('Dossiers/Show', [
            'dossier' => $dossier,
            'documents' => $dossier->documents,
            'activities' => $dossier->activities->take(20),
            'progress' => $progress,
            'canEdit' => auth()->user()->can('update', $dossier),
            'canDelete' => auth()->user()->can('delete', $dossier),
            'canValidate' => auth()->user()->can('validate', $dossier),
            'canApprove' => auth()->user()->can('approve', $dossier),
            'canChangeStatus' => auth()->user()->can('update', $dossier),
            'canUploadDocuments' => auth()->user()->hasPermissionTo('upload documents'),
            'canDeleteDocuments' => auth()->user()->hasPermissionTo('delete documents'),
            'canGenerateContract' => auth()->user()->hasRole(['super_admin', 'admin', 'agent', 'consultant']),
        ]);
    }

    /**
     * Show the form for editing the dossier.
     */
    public function edit(Dossier $dossier)
    {
        $this->authorize('update', $dossier);

        $clients = Client::select('id', 'name', 'email')->get();

        return inertia('Dossiers/Edit', [
            'dossier' => $dossier->load('client'),
            'clients' => $clients,
            'canDelete' => auth()->user()->can('delete', $dossier),
        ]);
    }

    /**
     * Update the specified dossier.
     */
    public function update(Request $request, Dossier $dossier)
    {
        $this->authorize('update', $dossier);

        $validated = $request->validate([
            'client_id' => ['sometimes', 'exists:clients,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $dossier->update($validated);

        activity()
            ->performedOn($dossier)
            ->causedBy(auth()->user())
            ->log('Dossier updated');

        return redirect()->route('dossiers.show', $dossier)
            ->with('success', 'Dossier updated successfully!');
    }

    /**
     * Remove the specified dossier.
     */
    public function destroy(Dossier $dossier)
    {
        $this->authorize('delete', $dossier);

        $reference = $dossier->reference;

        activity()
            ->performedOn($dossier)
            ->causedBy(auth()->user())
            ->log('Dossier deleted');

        $dossier->delete();

        return redirect()->route('dossiers.index')
            ->with('success', "Dossier {$reference} deleted successfully!");
    }

    /**
     * Validate a dossier (Consultant action).
     */
    public function validate(Dossier $dossier)
    {
        $this->authorize('validate', $dossier);

        // TODO: Implement status management
        // $dossier->update(['status' => 'validated']);

        activity()
            ->performedOn($dossier)
            ->causedBy(auth()->user())
            ->log('Dossier validated');

        return back()->with('success', 'Dossier validated successfully!');
    }

    /**
     * Approve a dossier (Consultant action).
     */
    public function approve(Dossier $dossier)
    {
        $this->authorize('approve', $dossier);

        // Use service to update status
        $this->dossierService->updateStatus($dossier->id, 'approuve', 'Dossier approuvé');

        return back()->with('success', 'Dossier approved successfully!');
    }

    /**
     * Change dossier status with notifications
     */
    public function changeStatus(Request $request, Dossier $dossier)
    {
        $this->authorize('update', $dossier);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:draft,pending,in_progress,approved,rejected,completed'],
        ]);

        $oldStatus = $dossier->status;
        $newStatus = $validated['status'];

        // Update status
        $dossier->update(['status' => $newStatus]);

        // Log activity
        activity()
            ->performedOn($dossier)
            ->causedBy(auth()->user())
            ->withProperties([
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ])
            ->log("Dossier status changed from {$oldStatus} to {$newStatus}");

        // Send notification to client
        $client = $dossier->client;
        if ($client && $client->email) {
            $user = User::where('email', $client->email)->first();
            if ($user) {
                $user->notify(new DossierStatusChangedNotification(
                    $dossier,
                    $oldStatus,
                    $newStatus,
                    app()->getLocale()
                ));
            }
        }

        return back()->with('success', 'Dossier status updated successfully!');
    }
}
