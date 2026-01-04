<?php

namespace App\Exceptions;

use App\Http\Requests\Auth\RegisterRequest;
use Exception;
use Illuminate\Http\JsonResponse;

class RegisterException extends Exception
{
    public function render($request)
    {

        return [

            'name.required' => 'نام الزامی است.',
            'name.string' => 'نام باید متنی باشد.',
            'name.min' => 'نام نمی‌تواند خالی باشد.',
            'name.max' => 'نام نباید بیشتر از ۱۲۰ کاراکتر باشد.',
            'name.regex' => 'نام فقط شامل حروف فارسی یا انگلیسی و فاصله باشد.',

            'email.required' => 'ایمیل الزامی است.',
            'email.email' => 'فرمت ایمیل صحیح نیست.',
            'email.string' => 'ایمیل باید متنی باشد.',
            'email.unique' => 'این ایمیل قبلاً ثبت شده است.',

            'password.required' => 'رمز عبور الزامی است.',
            'password.min' => 'رمز عبور حداقل ۸ کاراکتر باشد.',
            'password.confirmed' => 'تکرار رمز عبور با رمز عبور مطابقت ندارد.',
        ];
    }
}
