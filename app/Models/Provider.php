<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{

    protected $fillable = ['user_id', 'speciality' , 'is_active'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function schedule()
    {
        return $this->hasMany(ProviderSchedule::class);
    }

    public function appointment()
    {
        return $this->hasMany(Appointment::class);
    }



}
