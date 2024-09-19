<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class UserController
{
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json(['message' => 'Login successful'], 200);
    }
}
