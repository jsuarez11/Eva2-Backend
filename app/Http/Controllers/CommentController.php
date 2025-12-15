<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * Obtener comentarios de un producto
     * GET /api/comments/{product_id}
     */
    public function index($productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return response()->json([
                "message" => "Producto no encontrado",
                "status" => 404
            ], 404);
        }

        // Traemos los comentarios junto con el nombre del usuario que comentó
        $comments = $product->comments()->with('user:id,name,username,isAdmin')->latest()->paginate(10);

        return response()->json([
            "comments" => $comments,
            "status" => 200
        ], 200);
    }

    /**
     * Crear un comentario
     * POST /api/comments
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "product_id" => "required|exists:products,id",
            "content" => "required|string|max:500"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "errors" => $validator->errors(),
                "status" => 400
            ], 400);
        }

        $comment = $request->user()->comments()->create([
            "product_id" => $request->input('product_id'),
            "content" => $request->input('content')
        ]);

        // Retornamos el comentario cargando el usuario para que el front lo pinte de una
        $comment->load('user:id,name,username');

        return response()->json([
            "message" => "Comentario agregado",
            "comment" => $comment,
            "status" => 201
        ], 201);
    }

    /**
     * Eliminar un comentario
     * DELETE /api/comments/{id}
     */
    public function destroy(Request $request, $id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json([
                "message" => "Comentario no encontrado",
                "status" => 404
            ], 404);
        }

        // Verificamos permisos: Solo el dueño del comentario O un admin pueden borrarlo
        $user = $request->user();

        if ($user->id !== $comment->user_id && !$user->isAdmin) {
            return response()->json([
                "message" => "No tienes permiso para eliminar este comentario",
                "status" => 403
            ], 403);
        }

        $comment->delete();

        return response()->json([
            "message" => "Comentario eliminado",
            "status" => 200
        ], 200);
    }
}
