<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Listar todos los usuarios
     * GET /api/users
     * Requiere ser Admin
     */
    public function index()
    {
        // Paginamos los usuarios para no sobrecargar
        $users = User::paginate(10);

        return response()->json([
            "users" => $users,
            "status" => 200
        ], 200);
    }

    /**
     * Cambiar rol de usuario (Admin <-> User)
     * PATCH /api/users/{id}/role
     * Requiere ser Admin
     */
    public function changeRole(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                "message" => "Usuario no encontrado",
                "status" => 404
            ], 404);
        }

        // PROTECCIÓN: No permitir quitar admin al usuario ID 1
        if ($user->id == 1) {
            return response()->json([
                "message" => "No se puede modificar el rol del Super Administrador principal",
                "status" => 403
            ], 403);
        }

        $request->validate([
            'isAdmin' => 'required|boolean'
        ]);

        $user->isAdmin = $request->isAdmin;
        $user->save();

        return response()->json([
            "message" => "Rol de usuario actualizado correctamente",
            "user" => $user,
            "status" => 200
        ], 200);
    }
}
