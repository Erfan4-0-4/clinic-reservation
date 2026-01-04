<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\RegisterResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;


class RegisterController extends Controller
{

    /**
     *
     * @OA\Info(
     *       version="1.0.0",
     *       title="Clinic",
     *       description="API پروژه ",
     *  )
     *
     * @OA\Post(
     *     path="/api/auth/register",
     *     tags={"Auth"},
     *     summary="ثبت نام کاربر جدید",
     *     description="ثبت‌نام کاربر جدید و ایجاد توکن دسترسی",
     *
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="علی رضایی"),
     *             @OA\Property(property="email", type="string", format="email", example="ali@gmail.com"),
     *             @OA\Property(property="password", type="string", format="password", example="12345678"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="علی رضایی"),
     *                 @OA\Property(property="email", type="string", example="ali@gmail.com"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object",
     *                 example={
     *                     "name":{"تکمیل فیلد نام الزامی است"},
     *                     "email": {"تکمیل فیلد ایمیل الزامی است"},
     *                     "password": {"تکمیل فیلد گذرواژه الزامی است"}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many requests",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="لطفا دوباره تلاش کنید")
     *         )
     *     )
     * )
     */

    public function register(Request $request)
    {
        // بررسی اعتبار مقادیر ورودی

        $data = Validator::make($request->all(), [
            'name' => 'required|string|min:1|max:120|regex:/^[ا-یa-zA-Zء-ي ]+$/u',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'in:admin,provider,customer',
        ]);

        if($data->fails()) return new RegisterResource($data->errors());

        // لاک کردن برای جلوگیری از ورود دو ایمیل یک شکل دو کاربر مختلف

        $lock = Cache::lock("register:email:{$request->email}", 10);

        if (! $lock->get()) {
            return response()->json([
                'message' => 'لطفا دوباره تلاش کنید',
                'status' => 'error'
            ], 429);
        }

        try
        {
            $user = User::query()->create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            return new RegisterResource($user);
        }
        finally
        {
            $lock->release();
        }

    }


}
