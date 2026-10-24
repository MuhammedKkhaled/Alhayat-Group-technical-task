<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'status' => $this->status->value,
            'doctor' => [
                'id' => $this->doctor_id,
                'name' => $this->doctor_name_snapshot,
            ],
            'services' => ServiceResource::collection($this->services)->resolve($request),
            'total_price' => $this->total_price,
            'total_duration_minutes' => $this->total_duration_minutes,
            'starts_at' => $this->starts_at->toIso8601String(),
            'ends_at' => $this->ends_at->toIso8601String(),
        ];
    }
}
