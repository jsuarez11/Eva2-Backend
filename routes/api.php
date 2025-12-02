<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\productController;

Route::get("/students", function (){
    return "Estoy en la ruta de students";
});

Route::get("/product", [productController::class, 'index']);