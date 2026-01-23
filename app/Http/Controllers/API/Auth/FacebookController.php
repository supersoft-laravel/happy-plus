<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

            $token = $user->createToken($user->name, ['auth_token'])->plainTextToken;

            DB::commit();

            return response()->json([
                'message' => 'Login successful',
                'user' => $user->only(['id','name','email']),
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
}
