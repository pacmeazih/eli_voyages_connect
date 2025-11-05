<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\Package;
use Illuminate\Http\Request;
use App\Http\Resources\PackageResource;
use App\Http\Resources\PackageCollection;
use Illuminate\Http\JsonResponse;

class PackageController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $packages = Package::query()
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->paginate($request->per_page ?? 15);

        return $this->sendResponse(new PackageCollection($packages), 'Packages retrieved successfully');
    }

    public function show(Package $package): JsonResponse
    {
        return $this->sendResponse(new PackageResource($package), 'Package retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'destination' => 'nullable|string|max:255',
            'duration' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:draft,published,archived',
        ]);

        $package = Package::create($data);

        return $this->sendResponse(new PackageResource($package), 'Package created', 201);
    }

    public function update(Request $request, Package $package): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'destination' => 'sometimes|nullable|string|max:255',
            'duration' => 'sometimes|nullable|integer|min:1',
            'price' => 'sometimes|nullable|numeric|min:0',
            'start_date' => 'sometimes|nullable|date',
            'end_date' => 'sometimes|nullable|date|after_or_equal:start_date',
            'status' => 'sometimes|nullable|in:draft,published,archived',
        ]);

        $package->update($data);

        return $this->sendResponse(new PackageResource($package), 'Package updated');
    }

    public function destroy(Package $package): JsonResponse
    {
        $package->delete();

        return $this->sendResponse(null, 'Package deleted');
    }
}
