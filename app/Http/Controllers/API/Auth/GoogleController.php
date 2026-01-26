<?php

namespace App\Http\Controllers\API\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Mail\AdminUserRegistered;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            DB::beginTransaction();
            $googleUser = Socialite::driver('google')->stateless()->user();

            Log::info('Google user data:', ['user' => $googleUser]);
            // Check if the user already exists
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = new User();
                $user->name = $googleUser->getName();
                $user->email = $googleUser->getEmail();
                $user->password = Hash::make($googleUser->getEmail());
                $user->provider = 'google';
                $user->provider_id = $googleUser->getId();
                $user->email_verified_at = now();
                $username = $this->generateUsername($googleUser->getName());

                while (User::where('username', $username)->exists()) {
                    $username = $this->generateUsername($googleUser->getName());
                }
                $user->username = $username;
                $user->save();

                try {
                    $adminEmail = 'laravel.supersoft@gmail.com';
                    Mail::to($adminEmail)->send(new AdminUserRegistered($user));
                } catch (\Throwable $th) {
                    //throw $th;
                }

                app('notificationService')->notifyUsers([$user], 'Welcome to ' . Helper::getCompanyName(), 'Start exploring today and enjoy your journey');
                app('notificationService')->notifyUsers([$user], 'Update Your Password!', 'You have been registered from google and your password is your email please change it to secured one from your profile settings.');
            }

            $user->syncRoles('user');

            $profile = Profile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name'    => $googleUser->getName(),
                ]
            );
            if ($googleUser->getAvatar()) {
                if (isset($profile->profile_image) && File::exists(public_path($profile->profile_image))) {
                    File::delete(public_path($profile->profile_image));
                }

                $profileImageUrl = $googleUser->getAvatar(); // Get Google avatar URL
                $profileImage_ext = pathinfo($profileImageUrl, PATHINFO_EXTENSION) ?: 'jpg'; // Default to jpg if missing
                $profileImage_name = time() . '_profileImage.' . $profileImage_ext;

                $profileImage_path = 'uploads/profile-images';
                $fullImagePath = base_path("public/" . $profileImage_path . "/" . $profileImage_name); // Fixed path

                // Ensure directory exists
                if (!File::exists(base_path("public/" . $profileImage_path))) {
                    File::makeDirectory(base_path("public/" . $profileImage_path), 0777, true, true);
                }

                // Download and store the image
                file_put_contents($fullImagePath, file_get_contents($profileImageUrl));

                // Save to database
                $profile->profile_image = $profileImage_path . "/" . $profileImage_name;
                $profile->save();
            }

            $token = $user->createToken($user->name, ['auth_token'])->plainTextToken;
            Auth::attempt(['email' => $user->email, 'password' => $user->email]);
            DB::commit();
            return response()->json([
                'message' => 'Your account has been created successfully.',
                'user' => $user->only(['id', 'name', 'email', 'username']),
                'token' => $token,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Google login failed:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Something went wrong! Please try again later',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function GoogleLoginRegister(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            // ✅ Verify Google Token
            $client = new GoogleClient([
                'client_id' => config('services.google.client_id'),
            ]);

            $payload = $client->verifyIdToken($request->id_token);

            if (!$payload) {
                return response()->json([
                    'message' => 'Invalid Google token'
                ], 401);
            }

            // ✅ Google Data
            $email  = $payload['email'];
            $name   = $payload['name'] ?? 'Google User';
            $avatar = $payload['picture'] ?? null;
            $googleId = $payload['sub'];

            Log::info('Google user payload:', $payload);

            // ✅ Check User
            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = new User();
                $user->name = $name;
                $user->email = $email;
                $user->password = Hash::make($email);
                $user->provider = 'google';
                $user->provider_id = $googleId;
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
                    'You have been registered from Google. Please update your password from profile settings.'
                );
            }

            // ✅ Role
            $user->syncRoles('user');

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
                'message' => 'Google login successful',
                'user' => $user->only(['id', 'name', 'email', 'username']),
                'token' => $token,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Google login failed:', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Google login failed. Please try again.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function generateUsername($name)
    {
        $name = strtolower(str_replace(' ', '', $name));
        return $name . rand(1000, 9999);
    }
}
