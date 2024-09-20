<?php

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Mail\SendVerificationCode;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // التحقق من المدخلات
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // إنشاء المستخدم
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // إنشاء رمز تحقق وإرساله بالبريد الإلكتروني
        $code = rand(100000, 999999);
        $user->verification_code = $code;
        $user->save();

        Mail::to($user->email)->send(new SendVerificationCode($code));

        return response()->json(['message' => 'User registered successfully. Verification code sent.'], 201);
    }
    public function verifyEmail(Request $request)
{
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'User not found.'], 404);
    }

    if ($user->verification_code == $request->code) {
        $user->email_verified_at = now();
        $user->verification_code = null; // حذف رمز التحقق بعد نجاح العملية
        $user->save();

        return response()->json(['message' => 'Email verified successfully.'], 200);
    }

    return response()->json(['message' => 'Invalid verification code.'], 400);
}

}
