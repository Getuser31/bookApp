<?php

namespace App\Http\Controllers\Api;

use App\Models\DefaultLanguage;
use App\Models\UserPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class UserController
{
    
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // Authentication passed...
            $user = Auth::user();
            $role = $user->checkAdmin();
            $userPreference = UserPreference::getUserPreference($user->id);
            if(!$userPreference){
                $defaultLanguage = DefaultLanguage::first();
                UserPreference::create([
                    'user_id' => $user->id
                ]);
            } else {
                $defaultLanguage = $userPreference->defaultLanguage;
            }
            Session::put('admin', $role);
            Session::put('language', $defaultLanguage->language);

            // Create Sanctum Token
            $deviceName = $request->userAgent(); // Or a custom device identifier
            $abilities = ['*']; // Grant all permissions or customize
            $token = $user->createToken($deviceName, $abilities)->plainTextToken;

            // Store Token in Session
            Session::put('api_token', $token);
            return response()->json(['success' => 'login successful'], 200);
        } else {
            return Response()->json(['error' => 'Wrong username or password'], 401);
        }
    }
}
