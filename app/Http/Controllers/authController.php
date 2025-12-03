<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class authController extends Controller
{
    public function login(Request $request)
    {
        $user = User::where("email", $request->email)->first();

        if(!$user || !Hash::check($request->password, $user->password)){
            return response()->json([
                "message" => "Credenciales invalidas"
            ],401);
        }

        $token = $user ->createToken("api-token")->plainTextToken;
        return response()->json(["token" => $token],200);
    }

    public function logout(Request $request)
    {
        $request -> user() -> currentAccessToken() -> delete();
        return response()->json([
            "message"=> "Sesion cerrada exitosamente"
            ],200);
            
    }
}
