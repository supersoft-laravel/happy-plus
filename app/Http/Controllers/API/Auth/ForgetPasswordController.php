<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgetPassOTPMail;

class ForgetPasswordController extends Controller
{

    public function forgetPassEmail(Request $request)
    {
        $rules = [
            'email' => ['required', 'string', 'email', 'max:255'],
        ];

        if (config('captcha.version') !== 'no_captcha') {
            $rules['g-recaptcha-response'] = 'required|captcha';
        } else {
            $rules['g-recaptcha-response'] = 'nullable';
        }

        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return response()->json([
                'message' => 'Validation Error!',
                'errors' => $validate->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'No user found with this email address.',
                ], Response::HTTP_NOT_FOUND);
            }

            $token = $user->createToken($user->name, ['auth_token'])->plainTextToken;

            do {
                $otp = rand(1000, 9999);
            } while (User::where('email_otp', $otp)->exists());

            // Save OTP to user record
            $user->email_otp = $otp;
            $user->save();

            $subject = 'OTP to Reset Your Password';

            // ✅ Send OTP email
            Mail::to($user->email)->send(new ForgetPassOTPMail($user, $otp, $subject));

            DB::commit();

            return response()->json([
                'message' => 'An OTP has been sent to your email. Please use it to reset your password.',
                'user' => $user->only(['id', 'name', 'email', 'username']),
                'token' => $token,
            ], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error('User Forget Password failed', ['error' => $th->getMessage()]);

            return response()->json([
                'message' => 'Something went wrong! Please try again later',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function resetPassword(Request $request)
    {
        $rules = [
            'password' => ['required', 'string', 'min:6'],
            'confirm_password' => ['required', 'same:password'],
        ];

        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return response()->json([
                'message' => 'Validation Error!',
                'errors' => $validate->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            $user = $request->user();

            if (!$user) {
                return response()->json(['message' => 'Unauthorized user'], 401);
            }

            // Update user's password
            $user->password = Hash::make($request->password);
            $user->save();

            // ✅ Invalidate token after password reset
            $user->tokens()->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully. You can now login with your new password.',
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error('User Password Reset failed', ['error' => $th->getMessage()]);
            return response()->json([
                'message' => 'Something went wrong! Please try again later',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
