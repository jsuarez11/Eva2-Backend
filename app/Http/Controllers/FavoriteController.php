<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
    /**
     * List user's favorites
     * GET /api/favorites
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $favorites = $user->favourites()->paginate(10);

        return response()->json([
            "favorites" => $favorites,
            "status" => 200
        ], 200);
    }

    /**
     * Add a product to favorites
     * POST /api/favorites
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "product_id" => "required|exists:products,id"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "errors" => $validator->errors(),
                "status" => 400
            ], 400);
        }

        $user = $request->user();
        $productId = $request->product_id;

        // attach añade el registro, syncWithoutDetaching evita duplicados si ya existe
        $user->favourites()->syncWithoutDetaching([$productId]);

        return response()->json([
            "message" => "Producto agregado a favoritos",
            "status" => 201
        ], 201);
    }

    /**
     * Remove a product from favorites
     * DELETE /api/favorites/{product_id}
     */
    public function destroy(Request $request, $productId)
    {
        $user = $request->user();

        // detach elimina la asociación en la tabla pivote
        $result = $user->favourites()->detach($productId);

        if ($result === 0) {
            return response()->json([
                "message" => "El producto no estaba en favoritos o no existe",
                "status" => 404
            ], 404);
        }

        return response()->json([
            "message" => "Producto eliminado de favoritos",
            "status" => 200
        ], 200);
    }
}
