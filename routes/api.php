<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Appointment\AppointmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Provider\ProviderController;
use App\Http\Controllers\Service\ServiceController;
use Illuminate\Support\Facades\Route;

// Auth
Route::prefix('auth')->group(function(){

    Route::Post('register', [RegisterController::class, 'register'])
        ->middleware('throttle:register');

    Route::Post('login', [LoginController::class, 'login'])
        ->middleware('throttle:register');

    Route::Post('logout', [LoginController::class, 'logout'])
        ->middleware('auth:sanctum');
});

// Provider
Route::get('provider', [ProviderController::class, 'index']);

Route::get('provider/{id}', [ProviderController::class, 'find']);

Route::get('provider/{id}/available-slots', [ProviderController::class, 'date']);

// Services
Route::get('services', [ServiceController::class, 'index']);

// Appointment
Route::middleware('auth:sanctum')->group(function(){

    Route::Post('appointments', [AppointmentController::class, 'reservation']);

    Route::Put('appointments/{id}', [AppointmentController::class, 'changeReservation']);

    Route::get('appointments/my', [AppointmentController::class, 'myReservation']);
});

// Admin
Route::prefix('admin')->middleware(['auth:sanctum', 'abilities:admin'])->group(function(){

    Route::get('reports/daily', [AdminController::class, 'report']);

    Route::Post('provider/create', [AdminController::class, 'createProvider']);

    Route::Post('provider/createSchedule', [AdminController::class, 'createSchedule']);

    Route::Post('reservation/create', [AdminController::class, 'CreateReservation']);

    Route::Post('service/create', [AdminController::class, 'ServiceReservation']);

    Route::Delete('appointments/{id}', [AdminController::class, 'destroyReservation']);

    Route::get('appointments', [AdminController::class, 'index']);

});


