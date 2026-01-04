<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'provider_id',
        'service_id',
        'customer_id',
        'appointment_date',
        'start_time',
        'end_time',
        'status',

    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
