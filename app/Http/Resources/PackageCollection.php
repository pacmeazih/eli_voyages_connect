<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PackageCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return [
            'data' => $this->collection->transform(fn($item) => new PackageResource($item)),
            'meta' => [
                'total' => $this->resource->total(),
                'count' => $this->resource->count(),
                'per_page' => $this->resource->perPage(),
                'current_page' => $this->resource->currentPage(),
                'last_page' => $this->resource->lastPage(),
            ],
        ];
    }
}
