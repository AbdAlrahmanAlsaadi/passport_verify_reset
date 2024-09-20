<?php

namespace App\Http\Controllers;

use App\Mail\SendCodeResetPassword;
use App\Mail\SendVerificationCode;
use App\Models\ResetCodePassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class passportController extends Controller
{
    public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8',
    ]);

    $verificationCode = random_int(100000, 999999); // توليد رمز تحقق عشوائي

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'verification_code' => $verificationCode,
    ]);


    // إرسال رمز التحقق إلى البريد الإلكتروني
    Mail::to($user->email)->send(new SendVerificationCode($user, $verificationCode));


    return response()->json(['message' => 'Verification code sent to your email.']);
}

    // استرجاع المستخدم بناءً على البريد الإلكتروني
    public function verifyCodeOnly(Request $request)
{
    $request->validate([
        'code' => 'required',
    ]);

    // البحث عن المستخدم الذي يطابق رمز التحقق
    $user = User::where('verification_code', $request->code)->first();

    if (!$user) {
        return response()->json(['message' => 'Invalid verification code.'], 400);
    }

    // تحديث حالة البريد الإلكتروني وإزالة رمز التحقق
    $user->email_verified_at = now();
    $user->verification_code = null;
    $user->save();

    // إنشاء access token


    $success =  $user;
    // إنشاء access token
    $success['token'] = $user->createToken('API Token')->plainTextToken; // استخدم accessToken للحصول على التوكن الفعلي

    return response()->json(['message' => 'Email verified successfully, user registered.', 'token' => $success], 200);
}

public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials.'], 401);
    }

    // التحقق من تأكيد البريد الإلكتروني
    if (!$user->email_verified_at) {
        return response()->json(['message' => 'Please verify your email first.'], 403);
    }

    // إنشاء توكن Passport
    $token = $user->createToken('API Token')->accessToken;

    return response()->json(['token' => $token], 200);
}

public function userforgetpassword(Request $request){
    $data=$request->validate([
    'email'=>'required|email|exists:users'
    ]);
    //delete all old code that was send
    ResetCodePassword::query()->where('email',$request['email'])->delete();
    //create new code
    $data['code']=mt_rand(100000,999999);

    $codeData=ResetCodePassword::query()->create($data);
    //send email to user
    Mail::to($request['email'])->send(new SendCodeResetPassword($codeData['code']));
    return response()->json(['message'=>('password.sent')]);
    }
    public function usercheckpassword(Request $request){
    $request->validate([
    'code'=>'required|string'
    ]);
    $passwordReset=ResetCodePassword::query()->firstWhere('code',$request['code']);

    if($passwordReset['created_ar']> now()->addHour()){
        $passwordReset->delete();
    return response()->json(['message'=>('code is expire')]);

    }
        return response()->json([
            'code'=>$passwordReset['code'],
            'message'=>('code valid')
        ]);
    }


    public function userResetpassword(Request $request){

    $input=$request->validate([
        'code'=>'required|string',
        'password'=>'required',
    ]);
    $passwordReset=ResetCodePassword::query()->firstWhere('code',$request['code']);
    if($passwordReset['created_ar']> now()->addHour()){
        $passwordReset->delete();
        return response()->json([

            'message'=>('code is expire')
        ]);}
        $user=User::query()->firstWhere('email',$passwordReset['email']);

    $input['password']=bcrypt($input['password']);

    $user->update([
        'password'=>$input['password']
    ]);

    $passwordReset->delete();

    return response()->json(['message'=>'password reset succesfully']);


    }



}
