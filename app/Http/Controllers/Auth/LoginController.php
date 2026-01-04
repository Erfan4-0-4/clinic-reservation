<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoginResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class LoginController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="لاگین کاربر",
     *     description="ورود کاربر با ایمیل و رمز ورود",
     *     operationId="loginUser",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="ali@gmail.com"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 format="password",
     *                 example="12345678"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="ورود با موفقیت"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="علی رضایی"),
     *                  @OA\Property(property="email", type="string", example="ali@gmail.com"),
     *                  @OA\Property(property="role", type="string", example="customer"),
     *                 @OA\Property(property="token", type="string", example="1|eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9"),
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */

    public function login(Request $request)
    {
        // بررسی اعتبار مقادیر ورودی

        $data = Validator::make($request->all(), [
            'email' => 'required|string|email|exists:users,email',
            'password' => 'required|min:8',
        ]);

        if($data->fails()) return new LoginResource($data->errors());

        $user = User::where('email', $request->email)->first();

        // بررسی درست بودن رمز عبور با رمز هش شده در دیتابیس و وجود یوزر

        if(!$user || !Hash::check($request['password'], $user->password))
        {
            return response([
                'message' => 'ایمیل یا پسورد اشتباه است',
                'status' => 'error'
            ],401);
        }

        $user->tokens()->delete();

        // ساخت توکن

        if($user->role !== 'admin')
        {
            $tokenResult = $user->createToken(
                'auth_token',
                ['customer'],
                now()->addMinutes(20)
            );
            $plainTextToken = $tokenResult->plainTextToken;
        }
        else
        {
            $tokenResult = $user->createToken(
                'auth_token',
                ['admin'],
                now()->addMinutes(20)
            );
            $plainTextToken = $tokenResult->plainTextToken;
        }

        if($user){
        return response()->json([
            'message' => 'ورود با موفقیت',
            'user' => $user,
            'token' => $plainTextToken,
        ]);
        }
        else
        {
            return response()->json([
                'message' => 'ورود ناموفق',
                'status' => 'error'
            ]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="خروج کاربر",
     *     description="کاربر احراز هویت شده از سیستم خارج می شود و توکن دسترسی لغو می شود",
     *     operationId="logoutUser",
     *     tags={"Auth"},
     *      security={{"BearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="خروج با موفقیت انجام شد"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        $token->delete();

        return response()->json([
            'message' => 'خروج با موفقیت انجام شد',
            'status' => 'success'
        ], 200);
    }
}
