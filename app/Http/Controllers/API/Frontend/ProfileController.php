<?php

namespace App\Http\Controllers\API\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    public function getProfile(Request $request)
    {
        try {
            $user = $request->user();

            $profile = [
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->profile && $user->profile->phone_number ? $user->profile->phone_number : null,
                'profile_image' => $user->profile && $user->profile->profile_image ? url($user->profile->profile_image) : null,
                'gender' => $user->profile && $user->profile->gender ? $user->profile->gender : null,
            ];

            return response()->json([
                'profile' => $profile,
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error('API Profile failed', ['error' => $th->getMessage()]);
            return response()->json([
                'message' => 'Something went wrong!'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateProfile(Request $request)
    {
        // Validation rules
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max_size'],
            'gender' => ['nullable', 'in:male,female,other'],
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

            $user->name = $request->name;
            $user->save();

            $profile = $user->profile;
            $profile->first_name = $request->name;
            $profile->phone_number = $request->phone;
            $profile->gender = $request->gender;

            if ($request->hasFile('profile_image')) {
                if (isset($profile->profile_image) && File::exists(public_path($profile->profile_image))) {
                    File::delete(public_path($profile->profile_image));
                }

                $profileImage = $request->file('profile_image');
                $profileImage_ext = $profileImage->getClientOriginalExtension();
                $profileImage_name = time() . '_profileImage.' . $profileImage_ext;

                $profileImage_path = 'uploads/profile-images';
                $profileImage->move(public_path($profileImage_path), $profileImage_name);
                $profile->profile_image = $profileImage_path . "/" . $profileImage_name;
            }
            $profile->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error('Profile update failed', ['error' => $th->getMessage()]);
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

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully.',
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
