<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\ProviderServices;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/services",
     *     summary="دریافت ویزیت های فعال",
     *     operationId="getActiveServices",
     *     tags={"Services"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Active services found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="ویزیت متخصص قلب و عروق"),
     *                     @OA\Property(property="duration_minutes", type="string", example="30 Min"),
     *                     @OA\Property(property="price", type="string", example="200,000"),
     *                     @OA\Property(property="is_active", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="No active services",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="اتمام ویزیت"
     *             )
     *         )
     *     )
     * )
     */

    public function index()
    {
        $getServices = Service::all()->where('is_active', 1);

        if($getServices->count() !== 0)
        {
            return new ServiceResource($getServices);
        }
        else
        {
            return response([
                'message' => 'اتمام ویزیت',
                'status' => 'error'
            ]);
        }
    }
}
