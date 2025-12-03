<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class usersController extends Controller
{
    public function index(Request $request){
        $query= User::query();

        if($request->has("")){
        
        }

    }
    
}
