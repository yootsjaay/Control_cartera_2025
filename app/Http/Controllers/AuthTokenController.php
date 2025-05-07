<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class AuthTokenController extends Controller
{
    

    public function validar(Request $request)
    {
        $token = $request->bearerToken();
    
        if (!$token) {
            return response()->json(['message' => 'Token no proporcionado'], 400);
        }
    
        $accessToken = PersonalAccessToken::findToken($token);
    
        if (!$accessToken) {
            return response()->json(['message' => 'Token inválido'], 401);
        }
    
        $user = $accessToken->tokenable;
    
        return response()->json([
            'id'       => $user->id,
            'nombre'   => $user->name,
            'rol'      => $user->getRoleNames()->first(),
            'group_id' => $user->group_id,
            'email'    => $user->email
        ]);
    }
    
}
