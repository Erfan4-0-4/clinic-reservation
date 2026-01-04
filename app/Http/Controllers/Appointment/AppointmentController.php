<?php

namespace App\Http\Controllers\Appointment;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Http\Resources\MyAppointmentResource;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/appointments",
     *     operationId="ReserveAppointment",
     *     tags={"Appointment"},
     *     summary="رزرو نوبت",
     *     description="رزرو یک نوبت موجود توسط کاربر لاگین‌شده",
     *
     *     security={{"BearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"appointment_id"},
     *             @OA\Property(
     *                 property="appointment_id",
     *                 type="integer",
     *                 example=12,
     *                 description="شناسه نوبت"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="رزرو با موفقیت انجام شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=12),
     *             @OA\Property(property="provider_id", type="integer", example="دکتر محمد صادقی"),
     *             @OA\Property(property="customer_id", type="integer", example="مهدی محمدی"),
     *             @OA\Property(property="appointment_date", type="string", example="1404-10-13"),
     *             @OA\Property(property="start_time", type="string", example="09:00"),
     *             @OA\Property(property="end_time", type="string", example="09:30"),
     *             @OA\Property(property="status", type="string", example="reserved")
     *         )
     *     ),
     *
     *
     *     @OA\Response(
     *         response=409,
     *         description="نوبت قبلاً رزرو شده یا در حال رزرو است",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="message", type="string", example="قبلا رزرو شده است"),
     *                 @OA\Property(property="status", type="string", example="error")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="کاربر احراز هویت نشده است"
     *     )
     * )
     */
    public function reservation(Request $request)
    {
        // اعتبار سنجی مقادیر ورودی

        $data = Validator::make($request->all(), [
            'appointment_id' => 'required|integer|exists:appointments,id',
        ]);

        // نمایش خطای اعتبار سنجی

        if ($data->fails())
        {
            return response()->json($data->errors(), 422);
        }

        $appointment = Appointment::where('id' , $request->appointment_id)
            ->where('status', 'reserved')
            ->first();

            if ($appointment !== null && $appointment->customer_id !== '')  // بررسی رزرو نبودن نوبت مورد نظر
            {
                return response([
                    'data' => [
                        'message' => 'قبلا رزرو شده است',
                        'status' => 'error'
                    ],
                ]);
            }
            else
            {
                // لاک کردن برای جلوگیری از ثبت یک نوبت برای دو مشتری

                $lockKey = "appointment_lock:provider_{$request->provider_id}:{$request->appointment_date}:{$request->start_time}";
                $lock = Cache::lock($lockKey, 10);
                if (! $lock->get())
                {
                    return response([
                        'data' => [
                            'message' => 'این تایم در حال رزرو شدن است، لطفا چند لحظه بعد تلاش کنید',
                            'status' => 'error'
                        ],
                    ]);
                }

                // تلاش برای ساخت نوبت

                try
                {
                    $reservation = Appointment::where('id', $request->appointment_id)->first();
                    $reservation->update([
                        'customer_id' => auth()->user()->id,
                        'status' => 'reserved',

                    ]);

                    return new AppointmentResource($reservation);
                }

                // آزاد شدن لاک

                finally
                {
                    optional($lock)->release();
                }
            }
        }

    /**
     * @OA\Put(
     *     path="/api/appointments/{id}",
     *     operationId="ChangeReservation",
     *     tags={"Appointment"},
     *     summary="تغییر نوبت رزرو شده",
     *     description="تغییر نوبت فعلی کاربر به نوبت جدید در صورت خالی بودن",
     *
     *    security={{"BearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="شناسه نوبت فعلی کاربر",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"appointment_id"},
     *             @OA\Property(
     *                 property="appointment_id",
     *                 type="integer",
     *                 example=25,
     *                 description="شناسه نوبت جدید برای تغییر"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="نوبت با موفقیت تغییر کرد",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=25),
     *             @OA\Property(property="provider_id", type="integer", example="دکتر محمد صادقی"),
     *             @OA\Property(property="customer_id", type="integer", example="علی رضایی"),
     *             @OA\Property(property="appointment_date", type="string", example="1404-10-13"),
     *             @OA\Property(property="start_time", type="string", example="11:00"),
     *             @OA\Property(property="end_time", type="string", example="11:30"),
     *             @OA\Property(property="status", type="string", example="reserved")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=409,
     *         description="نوبت جدید قبلاً رزرو شده است",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="message", type="string", example="نوبت مورد نظر قبلا رزرو شده است"),
     *                 @OA\Property(property="status", type="string", example="error")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="کاربر احراز هویت نشده است"
     *     )
     * )
     */

    public function changeReservation(Request $request , $id)
    {
        // اعتبار سنجی مقادیر ورودی

        $data = Validator::make($request->all(), [
            'appointment_id' => 'required|integer|exists:appointments,id',
        ]);

        // نمایش خطای اعتبار سنجی

        if ($data->fails())
        {
            return response()->json($data->errors(), 422);
        }

        // بررسی درست بودن ای دی نوبت وروردی

            $changeAppointment = Appointment::where('id', $request->appointment_id)->first();

            // بررسی خالی بودن ساعت مورد نظر برای تغییر ساعت قبلی

            if($changeAppointment->customer_id == '' && $changeAppointment->status !== 'reserved')
            {
                $appointment = Appointment::where('id', $id)->where('customer_id' , auth()->user()->id)->first();
                $appointment->update([
                    'customer_id' => null,
                    'status' => 'completed'
                ]);


                $changeAppointment->update([
                    'customer_id'=> auth()->user()->id,
                    'status' => 'reserved',
                ]);

                return new AppointmentResource($changeAppointment);

            }
            else
            {
                return response([
                    'data' => [
                        'message' => 'نوبت مورد نظر قبلا رزرو شده است',
                        'status' => 'error'
                    ],
                ]);
            }
    }

    /**
     * @OA\Get(
     *     path="/api/appointments/my",
     *     operationId="MyReservations",
     *     tags={"Appointment"},
     *     summary="لیست نوبت‌های من",
     *     description="دریافت لیست نوبت‌های رزرو شده توسط کاربر لاگین‌شده",
     *
     *    security={{"BearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="لیست نوبت‌ها با موفقیت دریافت شد",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=12),
     *                 @OA\Property(property="provider_name", type="string", example="دکتر محمد صادقی"),
     *                 @OA\Property(property="appointment_date", type="string", example="1404-10-14"),
     *                 @OA\Property(property="start_time", type="string", example="10:00"),
     *                 @OA\Property(property="end_time", type="string", example="10:30"),
     *                 @OA\Property(property="status", type="string", example="reserved")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="کاربر احراز هویت نشده است"
     *     )
     * )
     */

    public function myReservation()
    {
        $myReservation = Appointment::where('customer_id', auth()->user()->id)->get();
        if(!$myReservation)
        {
            return response([
                'data' => [
                    'message' => 'نوبتی برای شما وجود ندارد',
                    'status' => 'success'
                ],
            ]);
        }

        return MyAppointmentResource::collection($myReservation);
    }
}


