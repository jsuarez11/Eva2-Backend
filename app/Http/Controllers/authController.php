<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class authController extends Controller
{
    public function register(Request $request)
    {
        //Validar datos
        $request->validate([
            "name" => "required|string",
            "apellido" => "required|string",
            "username" => "required|string|unique:users,username",
            "password" => "required|string|min:6",
        ]);

        //CREACION USUARIO
        $user = User::create([
            "name" => $request->name,
            "apellido" => $request->apellido,
            "username" => $request->username,
            "password" => Hash::make($request->password),
        ]);

        //CREAR TOKEN DE SANCTUM
        $token = $user->createToken("auth_token")->plainTextToken;

        //RETORNO
        return response()->json([
            "message" => "Usuario registrado con exito",
            "token" => $token,
            "user" => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $user = User::where("username", $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                "message" => "Credenciales invalidas"
            ], 401);
        }

        $token = $user->createToken("api-token")->plainTextToken;
        return response()->json(["token" => $token], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            "message" => "Cierre de sesión exitoso"
        ], 200);
    }
}
