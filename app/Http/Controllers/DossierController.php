<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DossierController extends Controller
{
    /**
     * Display a listing of dossiers.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Dossier::class);

        $query = Dossier::with(['client'])
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

        $dossiers = $query->paginate(15);

        return inertia('Dossiers/Index', [
            'dossiers' => $dossiers,
            'filters' => $request->only('search'),
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

        return redirect()->route('dossiers.show', $dossier)
            ->with('success', "Dossier {$dossier->reference} created successfully!");
    }

    /**
     * Display the specified dossier.
     */
    public function show(Dossier $dossier)
    {
        $this->authorize('view', $dossier);

        $dossier->load([
            'client',
            'documents' => function($query) {
                $query->latest()->limit(10);
            }
        ]);

        // Get activity logs
        $activities = activity()
            ->forSubject($dossier)
            ->latest()
            ->take(20)
            ->get();

        return inertia('Dossiers/Show', [
            'dossier' => $dossier,
            'activities' => $activities,
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

        // TODO: Implement status management
        // $dossier->update(['status' => 'approved']);

        activity()
            ->performedOn($dossier)
            ->causedBy(auth()->user())
            ->log('Dossier approved');

        return back()->with('success', 'Dossier approved successfully!');
    }
}
