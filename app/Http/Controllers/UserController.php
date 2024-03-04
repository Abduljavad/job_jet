<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function attachFav(Request $request){
        $request->validate([
            'favourites' => 'array|required|exists:jobs,id'
        ]);
        auth()->user()->favourites()->attach($request->favourites);
        return $this->successResponse();
    }

    public function detachFav(Request $request){
        $request->validate([
            'favourites' => 'array|exists:jobs,id'
        ]);
        auth()->user()->favourites()->detach($request->favourites);
        return $this->successResponse();
    }
}
