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

        // Obtener el primer grupo al que pertenece el usuario
        $groupId = $user->groups()->pluck('groups.id')->first(); // Toma el primer grupo

        return response()->json([
            'id'       => $user->id,
            'nombre'   => $user->name,
            'rol'      => $user->getRoleNames()->first(),
            'group_id' => $groupId,  // Devuelve solo un grupo, el primero
            'email'    => $user->email
        ]);
    }
}
