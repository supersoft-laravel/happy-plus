<?php

namespace App\Http\Controllers\API\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Mail\AdminUserRegistered;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class FacebookController extends Controller
{
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->stateless()->redirect();
    }

    public function handleFacebookCallback()
    {
        try {
            DB::beginTransaction();

            $fbUser = Socialite::driver('facebook')->stateless()->user();

            Log::info('Facebook user data:', ['user' => $fbUser]);

            $user = User::where('email', $fbUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $fbUser->getName(),
                    'email' => $fbUser->getEmail(),
                    'password' => Hash::make($fbUser->getEmail()),
                    'provider' => 'facebook',
                    'provider_id' => $fbUser->getId(),
                    'email_verified_at' => now(),
                ]);
            }

            try {
                $adminEmail = 'laravel.supersoft@gmail.com';
                Mail::to($adminEmail)->send(new AdminUserRegistered($user));
            } catch (\Throwable $th) {
                //throw $th;
            }

            $token = $user->createToken($user->name, ['auth_token'])->plainTextToken;

            DB::commit();

            return response()->json([
                'message' => 'Login successful',
                'user' => $user->only(['id', 'name', 'email']),
                'token' => $token
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Facebook login failed:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Something went wrong! Please try again later.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function facebookLoginRegister(Request $request)
    {
        $request->validate([
            'access_token' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            // Facebook token verify + profile get
            $fbUser = Socialite::driver('facebook')
                ->stateless()
                ->userFromToken($request->access_token);

            Log::info('Facebook user', ['data' => $fbUser]);

            if (!$fbUser->getEmail()) {
                return response()->json([
                    'message' => 'Facebook email permission missing'
                ], 422);
            }

            $email  = $fbUser->getEmail();
            $name   = $fbUser->getName();
            $avatar = $fbUser->getAvatar();
            $facebookId = $fbUser->getId();

            Log::info('Facebook user payload:', [
                'email' => $email,
                'name' => $name,
                'avatar' => $avatar,
                'facebook_id' => $facebookId
            ]);

            // User find or create
            $user = User::where('email', $fbUser->getEmail())->first();

            if (!$user) {
                $user = new User();
                $user->name = $name;
                $user->email = $email;
                $user->password = Hash::make($email);
                $user->provider = 'facebook';
                $user->provider_id = $facebookId;
                $user->email_verified_at = now();

                $username = $this->generateUsername($name);
                while (User::where('username', $username)->exists()) {
                    $username = $this->generateUsername($name);
                }

                $user->username = $username;
                $user->save();

                // ✅ Admin Email
                try {
                    $adminEmail = 'laravel.supersoft@gmail.com';
                    Mail::to($adminEmail)->send(new AdminUserRegistered($user));
                } catch (\Throwable $th) {
                }

                // ✅ Notifications
                app('notificationService')->notifyUsers(
                    [$user],
                    'Welcome to ' . Helper::getCompanyName(),
                    'Start exploring today and enjoy your journey'
                );

                app('notificationService')->notifyUsers(
                    [$user],
                    'Update Your Password!',
                    'You have been registered from Facebook. Please update your password from profile settings.'
                );
            }

            // ✅ Profile
            $profile = Profile::updateOrCreate(
                ['user_id' => $user->id],
                ['first_name' => $name]
            );

            // ✅ Profile Image Save
            if ($avatar) {
                if (!empty($profile->profile_image) && File::exists(public_path($profile->profile_image))) {
                    File::delete(public_path($profile->profile_image));
                }

                $profileImage_ext = pathinfo($avatar, PATHINFO_EXTENSION) ?: 'jpg';
                $profileImage_name = time() . '_profileImage.' . $profileImage_ext;
                $profileImage_path = 'uploads/profile-images';
                $fullImagePath = public_path($profileImage_path . '/' . $profileImage_name);

                if (!File::exists(public_path($profileImage_path))) {
                    File::makeDirectory(public_path($profileImage_path), 0777, true);
                }

                file_put_contents($fullImagePath, file_get_contents($avatar));

                $profile->profile_image = $profileImage_path . "/" . $profileImage_name;
                $profile->save();
            }

            // ✅ API Token
            $token = $user->createToken($user->name, ['auth_token'])->plainTextToken;

            DB::commit();

            return response()->json([
                'message' => 'Facebook Login successful',
                'user' => $user->only(['id', 'name', 'email', 'username']),
                'token' => $token,
            ], Response::HTTP_OK);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Facebook Login Error', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Invalid Facebook token or login failed'
            ], 401);
        }
    }
}
