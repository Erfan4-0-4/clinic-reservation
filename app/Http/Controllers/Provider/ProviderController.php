<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\DateResource;
use App\Http\Resources\ProviderResource;
use App\Models\Provider;
use App\Models\ProviderSchedule;
use Illuminate\Http\Request;

class ProviderController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/provider",
     *     summary="دریافت همه ارائه دهندگان",
     *     operationId="getProvider",
     *     tags={"Provider"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="string", example="دکتر علی محمدی"),
     *                     @OA\Property(property="speciality", type="string", example="دکتر عمومی")
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    // all providers
    public function index()
    {
        $providers = Provider::all();
        if(count($providers) == 0){
            return  response([
                'message' => 'متاسفانه دکتری وجود ندارد',
                'status' => 'error'
            ]);
        }
        return ProviderResource::collection($providers);
    }

    /**
     * @OA\Get(
     *     path="/api/provider/{id}",
     *     summary="دریافت ارائه دهنده با شناسه",
     *     operationId="getProviderById",
     *     tags={"Provider"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Provider ID",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="دکتر هاشمی"),
     *                 @OA\Property(property="speciality", type="string", example="متخصص حلق و بینی")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Provider not found"
     *     )
     * )
     */


    //find provider
    public function find($id)
    {
        $findProvider = Provider::find($id)->first();
        return new ProviderResource($findProvider);
    }

    /**
     * @OA\Get(
     *     path="/api/provider/{id}/available-slots?date=",
     *     summary="دریافت تاریخ‌های در دسترس بودن ارائه‌دهنده بر اساس تاریخ دریافتی",
     *     operationId="getProviderDatesByDay",
     *     tags={"Provider"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Provider ID",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         required=true,
     *         description="Day of week ",
     *         @OA\Schema(
     *             type="string",
     *             example="1404-10-11"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=10),
     *                     @OA\Property(property="provider_id", type="string", example="دکتر حسین محمدی"),
     *                     @OA\Property(property="speciality", type="string", example="دکتر عمومی"),
     *                     @OA\Property(property="day_of_week", type="string", example="1404-10-11"),
     *                     @OA\Property(property="start_time", type="string", example="09:00 AM"),
     *                     @OA\Property(property="end_time", type="string", example="17:00 PM")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */


    // Get date Presentation
    public function date(Request $request ,$id)
    {
        $day = $request->query('date');
        $findDate = ProviderSchedule::where('day_of_week', $day)->get();
        if(count($findDate) == 0)
        {
            return response([
                'message' => 'آیدی یا زمان مورد نظر وجود ندارد',
                'status' => 'error'
            ]);
        }
        return DateResource::collection($findDate);
    }
}
