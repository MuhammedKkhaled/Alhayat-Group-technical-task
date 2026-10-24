<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->pivot?->service_name_snapshot ?? $this->name,
            'price' => $this->pivot?->price_snapshot ?? $this->price,
            'duration_minutes' => $this->pivot?->duration_minutes_snapshot ?? $this->duration_minutes,
        ];
    }
}
