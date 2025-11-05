<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\Dossier;
use Illuminate\Http\Request;
use App\Http\Resources\ClientResource;
use Illuminate\Http\JsonResponse;

class DossierController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $dossiers = Dossier::with('client')->paginate($request->per_page ?? 15);

        return $this->sendResponse($dossiers, 'Dossiers retrieved');
    }

    public function show(Dossier $dossier): JsonResponse
    {
        return $this->sendResponse($dossier->load('documents'), 'Dossier retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $dossier = Dossier::create($data);

        return $this->sendResponse($dossier, 'Dossier created', 201);
    }
}
