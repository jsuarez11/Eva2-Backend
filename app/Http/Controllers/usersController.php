<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class usersController extends Controller
{
    public function index(Request $request){
        $query= User::query();


    }

    public function addFavorite($productId)
{
    $user = auth()->user();
    $user->favorites()->syncWithoutDetaching([$productId]);
    return response()->json(["message" => "Agregado a favoritos"], 200);
}

public function removeFavorite($productId)
{
    $user = auth()->user();
    $user->favorites()->detach($productId);
    return response()->json(["message" => "Eliminado de favoritos"], 200);
}
}
