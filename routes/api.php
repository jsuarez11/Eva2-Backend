<?php

use App\Http\Controllers\authController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use Kreait\Firebase\Contract\Storage;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\CommentController;


Route::middleware("auth:sanctum")->group(function () {
    // Rutas públicas para usuarios autenticados
    Route::get("/products", [ProductController::class, "index"]);
    Route::get("/products/{id}", [ProductController::class, "show"]);

    // Rutas administrativas
    Route::middleware("is_admin")->group(function () {
        Route::post("/products", [ProductController::class, "store"]);
        Route::put("/products/{id}", [ProductController::class, "update"]);
        Route::delete("/products/{id}", [ProductController::class, "destroy"]);

        // Listar usuarios
        Route::get("/users", [App\Http\Controllers\UserController::class, "index"]);

        // Cambiar rol (Admin/User)
        Route::patch("/users/{id}/role", [App\Http\Controllers\UserController::class, "changeRole"]);
    });
});

//PARA AUTH
Route::post("/register", [authController::class, "register"]);
Route::post("/login", [authController::class, "login"]);
Route::middleware("auth:sanctum")->post("/logout", [authController::class, "logout"]);

//MOSTRAR DATOS DE USUARIO LOGUEADO
Route::middleware("auth:sanctum")->get("/me", function (Request $request) {
    return $request->user();
});

// FAVORITOS
Route::middleware("auth:sanctum")->group(function () {
    Route::get("/favorites", [FavoriteController::class, "index"]);
    Route::post("/favorites", [FavoriteController::class, "store"]);
    Route::delete("/favorites/{product_id}", [FavoriteController::class, "destroy"]);
});

// COMENTARIOS
Route::middleware("auth:sanctum")->group(function () {
    Route::post("/comments", [CommentController::class, "store"]);
    Route::get("/comments/{product_id}", [CommentController::class, "index"]);
    Route::delete("/comments/{id}", [CommentController::class, "destroy"]); // Extra: Para poder borrarlos
});
