<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Display appointments calendar page
     */
    public function index()
    {
        $user = Auth::user();
        $isAgent = !$user->hasRole('Client');

        return Inertia::render('Appointments/Index', [
            'isAgent' => $isAgent,
        ]);
    }

    /**
     * Display appointment confirmation page
     */
    public function confirmation(Appointment $appointment)
    {
        // Load agent relation
        $appointment->load('agent');

        return Inertia::render('Appointments/Confirmation', [
            'appointment' => $appointment,
        ]);
    }

    /**
     * Get appointments for calendar
     */
    public function getAppointments(Request $request)
    {
        $user = Auth::user();
        $isAgent = !$user->hasRole('Client');

        $query = Appointment::with(['client', 'agent', 'dossier']);

        // Filter by user role
        if ($isAgent) {
            // Agents see their own appointments
            $query->where('agent_id', $user->id);
        } else {
            // Clients see their own appointments
            $query->where('client_id', $user->id);
        }

        // Filter by date range if provided
        if ($request->has('start') && $request->has('end')) {
            $query->whereBetween('scheduled_at', [
                Carbon::parse($request->start),
                Carbon::parse($request->end)
            ]);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderBy('scheduled_at')->get();

        return response()->json($appointments);
    }

    /**
     * Get available time slots for booking
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'agent_id' => 'required|exists:users,id',
            'date' => 'required|date',
        ]);

        $date = Carbon::parse($request->date);
        $agentId = $request->agent_id;

        // Business hours: 9:00 - 17:00
        $startTime = $date->copy()->setTime(9, 0);
        $endTime = $date->copy()->setTime(17, 0);
        $slotDuration = 30; // minutes

        // Get existing appointments for this agent on this date
        $existingAppointments = Appointment::where('agent_id', $agentId)
            ->whereDate('scheduled_at', $date)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->get();

        $availableSlots = [];
        $currentTime = $startTime->copy();

        while ($currentTime->lt($endTime)) {
            $slotEnd = $currentTime->copy()->addMinutes($slotDuration);
            
            // Check if slot is available
            $isAvailable = true;
            foreach ($existingAppointments as $appointment) {
                $appointmentStart = Carbon::parse($appointment->scheduled_at);
                $appointmentEnd = $appointmentStart->copy()->addMinutes($appointment->duration_minutes);

                // Check for overlap
                if ($currentTime->lt($appointmentEnd) && $slotEnd->gt($appointmentStart)) {
                    $isAvailable = false;
                    break;
                }
            }

            if ($isAvailable) {
                $availableSlots[] = [
                    'time' => $currentTime->format('H:i'),
                    'label' => $currentTime->format('H:i') . ' - ' . $slotEnd->format('H:i'),
                    'datetime' => $currentTime->toIso8601String(),
                ];
            }

            $currentTime->addMinutes($slotDuration);
        }

        return response()->json($availableSlots);
    }

    /**
     * Get list of available agents
     */
    public function getAgents()
    {
        $agents = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Agent', 'Admin']);
        })->select('id', 'name', 'email')->get();

        return response()->json($agents);
    }

    /**
     * Store a new appointment
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $isAgent = !$user->hasRole('Client');

        $validated = $request->validate([
            'agent_id' => 'required|exists:users,id',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:15|max:120',
            'type' => 'required|in:consultation,document_review,signing,follow_up',
            'client_notes' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'dossier_id' => 'nullable|exists:dossiers,id',
            'meeting_link' => 'nullable|url',
            'location' => 'nullable|string|max:255',
        ]);

        // Set client_id based on role
        $validated['client_id'] = $isAgent ? $request->client_id : $user->id;

        $appointment = Appointment::create($validated);

        // TODO: Send notification to agent and client

        return response()->json([
            'message' => 'Rendez-vous créé avec succès',
            'appointment' => $appointment->load(['client', 'agent', 'dossier'])
        ], 201);
    }

    /**
     * Update appointment
     */
    public function update(Request $request, Appointment $appointment)
    {
        $user = Auth::user();
        
        // Authorization check
        if ($appointment->client_id !== $user->id && $appointment->agent_id !== $user->id) {
            abort(403, 'Non autorisé');
        }

        $validated = $request->validate([
            'scheduled_at' => 'sometimes|date|after:now',
            'duration_minutes' => 'sometimes|integer|min:15|max:120',
            'type' => 'sometimes|in:consultation,document_review,signing,follow_up',
            'client_notes' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'meeting_link' => 'nullable|url',
            'location' => 'nullable|string|max:255',
        ]);

        $appointment->update($validated);

        return response()->json([
            'message' => 'Rendez-vous mis à jour',
            'appointment' => $appointment->load(['client', 'agent', 'dossier'])
        ]);
    }

    /**
     * Confirm appointment
     */
    public function confirm(Appointment $appointment)
    {
        $user = Auth::user();
        
        if ($appointment->agent_id !== $user->id) {
            abort(403, 'Seul l\'agent peut confirmer le rendez-vous');
        }

        $appointment->confirm();

        return response()->json([
            'message' => 'Rendez-vous confirmé',
            'appointment' => $appointment->load(['client', 'agent', 'dossier'])
        ]);
    }

    /**
     * Cancel appointment
     */
    public function cancel(Request $request, Appointment $appointment)
    {
        $user = Auth::user();
        
        // Both client and agent can cancel
        if ($appointment->client_id !== $user->id && $appointment->agent_id !== $user->id) {
            abort(403, 'Non autorisé');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $appointment->cancel($validated['reason'] ?? null);

        return response()->json([
            'message' => 'Rendez-vous annulé',
            'appointment' => $appointment->load(['client', 'agent', 'dossier'])
        ]);
    }

    /**
     * Delete appointment
     */
    public function destroy(Appointment $appointment)
    {
        $user = Auth::user();
        
        // Only agent can delete
        if ($appointment->agent_id !== $user->id && !$user->hasRole('Admin')) {
            abort(403, 'Non autorisé');
        }

        $appointment->delete();

        return response()->json([
            'message' => 'Rendez-vous supprimé'
        ]);
    }
}
