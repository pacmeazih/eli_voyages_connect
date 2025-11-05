<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Http\Resources\ClientResource;
use App\Http\Resources\ClientCollection;
use Illuminate\Http\JsonResponse;

class ClientController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $clients = Client::query()
            ->when($request->search, fn($q, $s) => $q->where('first_name', 'like', "%{$s}%")->orWhere('last_name', 'like', "%{$s}%"))
            ->paginate($request->per_page ?? 15);

        return $this->sendResponse(new ClientCollection($clients), 'Clients retrieved successfully');
    }

    public function show(Client $client): JsonResponse
    {
        return $this->sendResponse(new ClientResource($client), 'Client retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
        ]);

        $client = Client::create($data);

        return $this->sendResponse(new ClientResource($client), 'Client created', 201);
    }

    public function update(Request $request, Client $client): JsonResponse
    {
        $data = $request->validate([
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|nullable|string|max:255',
            'email' => ['sometimes', 'required', 'email', 'max:255'],
            'phone' => 'sometimes|nullable|string',
            'address' => 'sometimes|nullable|string',
            'city' => 'sometimes|nullable|string',
            'country' => 'sometimes|nullable|string',
        ]);

        if (isset($data['email']) && $data['email'] !== $client->email) {
            $request->validate(['email' => 'unique:clients,email']);
        }

        $client->update($data);

        return $this->sendResponse(new ClientResource($client), 'Client updated');
    }

    public function destroy(Client $client): JsonResponse
    {
        $client->delete();

        return $this->sendResponse(null, 'Client deleted');
    }
}
