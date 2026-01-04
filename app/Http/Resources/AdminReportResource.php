<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'appointment_date' => $this->appointment_date,
            'data' => [
                'provider_id' => $this->provider->user->name,
                'service_id' => $this->service->name,
                'customer_id' => $this->customer_id == null ? null : $this->user->name,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'status' => $this->status
            ],
        ];
    }
}
