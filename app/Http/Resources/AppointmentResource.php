<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => 'رزرو شد',
            'id' => $this->id,
            'provider_id' => $this->provider->user->name,
            'service_id' => $this->service->name,
            'price' => $this->service->price,
            'customer_id' => $this->user->name,
            'appointment_date' => $this->appointment_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,

        ];
    }
}
