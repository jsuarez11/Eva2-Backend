<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get("/students", function (){
    return "Estoy en la ruta de students";
});