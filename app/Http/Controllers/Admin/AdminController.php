<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminReportResource;
use App\Http\Resources\AdminResource;
use App\Models\Appointment;
use App\Models\Provider;
use App\Models\ProviderSchedule;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/admin/appointments",
     *     summary="دریافت لیست تمام نوبت‌ها (فقط ادمین)",
     *     description="این API لیست تمام نوبت‌ها را فقط در صورتی که کاربر ادمین باشد برمی‌گرداند",
     *     operationId="getAllAppointments",
     *     tags={"Admin"},
     *     security={{"BearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="لیست نوبت‌ها با موفقیت دریافت شد",
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="id", type="integer", example="1"),
     *             @OA\Property(property="provider_id", type="integer", example="دکتر محمد صادقی"),
     *               @OA\Property(property="service_id", type="integer", example="ویزیت دکتر عمومی"),
     *               @OA\Property(property="customer_id", type="integer", example="حسین ملکی"),
     *               @OA\Property(property="appointment_date", type="string", format="date", example="2025-01-10"),
     *               @OA\Property(property="start_time", type="string", example="09:00"),
     *               @OA\Property(property="end_time", type="string", example="09:30"),
     *               @OA\Property(property="status", type="string", example="completed")
     *
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="عدم احراز هویت"
     *     )
     * )
     */
    public function index()
    {
        $allAppointment = Appointment::all();
        return AdminResource::collection($allAppointment);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/reports/daily",
     *     summary="گزارش نوبت‌ها (فقط ادمین)",
     *     description="دریافت گزارش نوبت‌ها به صورت مرتب‌شده بر اساس تاریخ نوبت",
     *     operationId="getAppointmentsReport",
     *     tags={"Admin"},
     *     security={{"BearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="گزارش نوبت‌ها با موفقیت دریافت شد",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(
     *                     property="appointment_date",
     *                     type="string",
     *                     format="date",
     *                     example="1404-1-15"
     *                 ),
     *                 @OA\Property(
     *                     property="data",
     *                     type="object",
     *                     @OA\Property(
     *                         property="provider_id",
     *                         type="string",
     *                         example="دکتر محمدی"
     *                     ),
     *                     @OA\Property(
     *                         property="service_id",
     *                         type="string",
     *                         example="ویزیت حلق و بینی"
     *                     ),
     *                     @OA\Property(
     *                         property="customer_id",
     *                         type="string",
     *                         example="علی احمدی"
     *                     ),
     *                     @OA\Property(
     *                         property="start_time",
     *                         type="string",
     *                         example="09:00"
     *                     ),
     *                     @OA\Property(
     *                         property="end_time",
     *                         type="string",
     *                         example="09:30"
     *                     ),
     *                     @OA\Property(
     *                         property="status",
     *                         type="string",
     *                         example="reserved"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="عدم دسترسی (کاربر ادمین نیست)",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="عدم احراز هویت"
     *     )
     * )
     */
    public function report()
    {
        $allAppointment = Appointment::all()->sortBy('appointment_date');
        if(count($allAppointment) == 0)
        {
            return response([
                'message' => 'در حال حاضر رزروی وجود ندارد',
                'status' => 'error'
            ]);
        }

        return AdminReportResource::collection($allAppointment);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/provider/create",
     *     operationId="CreateProvider",
     *     tags={"Admin"},
     *     summary="ایجاد پزشک جدید",
     *     description="ایجاد پزشک جدید بر اساس کاربر موجود و تعیین تخصص",
     *     security={{"BearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id","speciality"},
     *
     *             @OA\Property(
     *                 property="user_id",
     *                 type="integer",
     *                 example=2,
     *                 description="شناسه کاربر"
     *             ),
     *             @OA\Property(
     *                 property="speciality",
     *                 type="string",
     *                 example="متخصص قلب و عروق",
     *                 description="تخصص پزشک"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="پزشک با موفقیت ایجاد شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="message", type="string", example="موفقیت"),
     *                 @OA\Property(property="user_id", type="string", example="دکتر احمدی"),
     *                 @OA\Property(property="speciality", type="string", example="متخصص قلب و عروق"),
     *                 @OA\Property(property="is_active", type="integer", example=1)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="خطای اعتبارسنجی",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="array",
     *                 @OA\Items(type="string", example="کاربر مورد نظر وجود ندارد")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="پزشک قبلاً ثبت شده است",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="دکتر از قبل وجود دارد"),
     *             @OA\Property(property="status", type="string", example="error")
     *         )
     *     )
     * )
     */

    public function createProvider(Request $request)
    {

        $data = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'speciality' => 'required|string'
        ]);

        // نمایش خطای اعتبار سنجی

        if ($data->fails())
        {
            return response()->json($data->errors(), 422);
        }

        $provider = Provider::where('user_id', $request->user_id)->first();

        if($provider !== null)
        {
            return response([
                'message' => "دکتر از قبل وجود دارد",
                'status' => 'error'
            ]);
        }

        $newProvider = Provider::create([
            'user_id' => $request->user_id,
            'speciality' => $request->speciality,
            'is_active' => '1'
        ]);

        $user = User::where('id', $request->user_id);
        $user->update([
            'role' => 'provider'
        ]);

        return response([
            'data' => [
                'message' => 'موفقیت',
                'user_id' => $newProvider->user->name,
                'speciality' => $request->speciality,
                'is_active' => 1
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/provider/createSchedule",
     *     operationId="CreateProviderSchedule",
     *     tags={"Admin"},
     *     summary="ایجاد برنامه کاری پزشک",
     *     description="ثبت برنامه کاری پزشک در روز و ساعت مشخص",
     *     security={{"BearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"provider_id","day_of_week","start_time","end_time"},
     *
     *             @OA\Property(
     *                 property="provider_id",
     *                 type="integer",
     *                 example=3,
     *                 description="شناسه پزشک"
     *             ),
     *             @OA\Property(
     *                 property="day_of_week",
     *                 type="string",
     *                 example="1404-10-11",
     *                 description="روز هفته"
     *             ),
     *             @OA\Property(
     *                 property="start_time",
     *                 type="string",
     *                 example="08:00",
     *                 description="ساعت شروع"
     *             ),
     *             @OA\Property(
     *                 property="end_time",
     *                 type="string",
     *                 example="14:00",
     *                 description="ساعت پایان"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="برنامه کاری با موفقیت ایجاد شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=7),
     *                 @OA\Property(property="provider_id", type="integer", example="دکتر محمد صادقی"),
     *                 @OA\Property(property="day_of_week", type="string", example="1404-10-11"),
     *                 @OA\Property(property="start_time", type="string", example="08:00"),
     *                 @OA\Property(property="end_time", type="string", example="14:00")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=409,
     *         description="برنامه کاری از قبل وجود دارد",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="برنامه مورد نظر از قبل وجود دارد"),
     *             @OA\Property(property="status", type="string", example="error")
     *         )
     *     )
     * )
     */

    public function createSchedule(Request $request)
    {
        $data = Validator::make($request->all(), [
            'provider_id' => 'required|integer|exists:providers,id',
            'day_of_week' => 'required|string',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
        ]);

        // نمایش خطای اعتبار سنجی

        if ($data->fails())
        {
            return response()->json($data->errors(), 422);
        }

        $schedule = ProviderSchedule::where('provider_id', $request->provider_id)
            ->where('day_of_week', $request->day_of_week)
            ->where('start_time', $request->start_time)
            ->where('end_time', $request->end_time)->first();

        $provider = Provider::where('id', $request->provider_id)->first();

        if($schedule == null && $provider !== null)
        {
            $new = ProviderSchedule::create([
                'provider_id' => $request->provider_id,
                'day_of_week' => $request->day_of_week,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            return response([
                'data' => [
                    'id' => $new->id,
                    'provider_id' => $provider->user->name,
                    'day_of_week' => $request->day_of_week,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                ]
            ]);
        }
        else
        {
            return response([
                'message' => 'برنامه مورد نظر از قبل وجود دارد',
                'status' => 'error'
            ]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/admin/reservation/create",
     *     operationId="CreateReservation",
     *     tags={"Admin"},
     *     summary="ایجاد نوبت جدید",
     *     description="ایجاد نوبت برای پزشک در تاریخ و ساعت مشخص",
     *     security={{"BearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"provider_id","service_id","appointment_date","start_time","end_time"},
     *             @OA\Property(
     *                 property="provider_id",
     *                 type="integer",
     *                 example=1,
     *                 description="شناسه پزشک"
     *             ),
     *             @OA\Property(
     *                 property="service_id",
     *                 type="integer",
     *                 example=2,
     *                 description="شناسه سرویس"
     *             ),
     *             @OA\Property(
     *                 property="appointment_date",
     *                 type="string",
     *                 example="1404-10-13",
     *                 description="تاریخ نوبت"
     *             ),
     *             @OA\Property(
     *                 property="start_time",
     *                 type="string",
     *                 example="10:00",
     *                 description="ساعت شروع"
     *             ),
     *             @OA\Property(
     *                 property="end_time",
     *                 type="string",
     *                 example="10:30",
     *                 description="ساعت پایان"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="نوبت با موفقیت ایجاد شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=15),
     *             @OA\Property(property="provider_id", type="integer", example="دکتر حسین صادقی"),
     *             @OA\Property(property="service_id", type="integer", example="ویزیت دکتر عمومی"),
     *             @OA\Property(property="appointment_date", type="string", example="1404-10-13"),
     *             @OA\Property(property="start_time", type="string", example="10:00"),
     *             @OA\Property(property="end_time", type="string", example="10:30"),
     *             @OA\Property(property="status", type="string", example="completed")
     *         )
     *     ),
     *
     *
     *     @OA\Response(
     *         response=400,
     *         description="نوبت قبلاً ایجاد شده",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="message", type="string", example="این نوبت قبلا ایجاد شده است"),
     *                 @OA\Property(property="status", type="string", example="error")
     *             )
     *         )
     *     )
     * )
     */

    public function CreateReservation(Request $request)
    {

        $data = Validator::make($request->all(), [
            'provider_id' => 'required|integer|exists:providers,id',
            'service_id' => 'required|integer|exists:services,id',
            'appointment_date' => 'required|string',
            'start_time' => 'required|string',
            'end_time' => 'required|string'
        ]);

        // نمایش خطای اعتبار سنجی

        if ($data->fails())
        {
            return response()->json($data->errors(), 422);
        }

        $provider = Provider::where('id', $request->provider_id)->first();

        if($provider->is_active == 1) {

            $appointment = Appointment::where('provider_id', $request->provider_id)
                ->where('appointment_date', $request->appointment_date)
                ->where('start_time', $request->start_time)
                ->where('end_time', $request->end_time)->first();

            if ($appointment == null)
            {
                $create = Appointment::create([
                    'provider_id' => $request->provider_id,
                    'service_id' => $request->service_id,
                    'appointment_date' => $request->appointment_date,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'status' => 'completed'
                ]);

                return new AdminResource($create);
            }
            else
            {
                return response([
                    'data' => [
                        'message' => 'این نوبت قبلا ایجاد شده است',
                        'status' => 'error'
                    ],
                ]);
            }

        }
        else
        {
            return response([
                'data' => [
                    'message' => 'پزشک مورد نظر در حال حاظر در دسترس نمی باشد',
                    'status' => 'error'
                ],
            ]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/admin/service/create",
     *     operationId="ServiceReservation",
     *     tags={"Admin"},
     *     summary="ایجاد ویزیت جدید",
     *     description="ایجاد سرویس (ویزیت) جدید در صورت عدم وجود نام تکراری",
     *     security={{"BearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","duration_minutes","price"},
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="ویزیت دکتر عمومی",
     *                 description="نام سرویس"
     *             ),
     *             @OA\Property(
     *                 property="duration_minutes",
     *                 type="string",
     *                 example="30 دقیقه",
     *                 description="مدت زمان سرویس (دقیقه)"
     *             ),
     *             @OA\Property(
     *                 property="price",
     *                 type="string",
     *                 example="150,000",
     *                 description="هزینه سرویس"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="سرویس با موفقیت ایجاد شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=5),
     *                 @OA\Property(property="duration_minutes", type="string", example="30 دقیقه"),
     *                 @OA\Property(property="price", type="string", example="150000"),
     *                 @OA\Property(property="is_active", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="success")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="نام سرویس تکراری است",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="message", type="string", example="نام ویزیت وارد شده از قبل وجود دارد"),
     *                 @OA\Property(property="status", type="string", example="error")
     *             )
     *         )
     *     )
     * )
     */

    public function ServiceReservation(Request $request)
    {
        $data = Validator::make($request->all(), [
            'name' => 'required|string',
            'duration_minutes' => 'required|string',
            'price' => 'required|string'
        ]);

        // نمایش خطای اعتبار سنجی

        if ($data->fails())
        {
            return response()->json($data->errors(), 422);
        }

        $service = Service::where('name', $request->name)->first();

        if($service == null)
        {
            $create = Service::create([
                'name' => $request->name,
                'duration_minutes' => $request->duration_minutes,
                'price' => $request->price,
                'is_active' => '1'
            ]);

            return response([
                'data' => [
                    'id' => $create->id,
                    'duration_minutes' => $request-> duration_minutes,
                    'price' => $request->price,
                    'is_active' => 1,
                    'status' => 'success'
                ]
            ]);
        }
        else
        {
            return response([
                'data' => [
                    'message' => 'نام ویزیت وارد شده از قبل وجود دارد',
                    'status' => 'error'
                ],
            ]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/appointments/{id}",
     *     tags={"Admin"},
     *     summary="حذف نوبت",
     *     description="حذف نوبت با استفاده از شناسه نوبت",
     *     operationId="destroyReservation",
     *
     *      security={{"BearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="شناسه نوبت",
     *         @OA\Schema(type="integer", example=12)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="نوبت با موفقیت حذف شد",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="نوبت مورد نظر با موفقیت حذف شد"),
     *                 @OA\Property(property="status", type="string", example="success")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="نوبت پیدا نشد",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="نوبت مورد نظر پیدا نشد"),
     *                 @OA\Property(property="status", type="string", example="error")
     *             )
     *         )
     *     )
     * )
     */

    public function destroyReservation($id)
    {
        Appointment::where('id', $id)->delete();

        return response([
            'data' => [
                'message' => 'نوبت مورد نظر با موفقیت حذف شد',
                'status' => 'success'
            ],
        ]);
    }
}
