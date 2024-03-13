<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function profile(ProfileRequest $request)
    {
        auth()->user()->profile()->updateOrCreate(['user_id'=> auth()->user()->id], $request->validated());

        return $this->successResponse('profile updated');
    }

    public function attachFav(Request $request)
    {
        $request->validate([
            'favourites' => 'array|required|exists:job_applications,id',
        ]);
        auth()->user()->favourites()->attach($request->favourites);

        return $this->successResponse();
    }

    public function detachFav(Request $request)
    {
        $request->validate([
            'favourites' => 'array|exists:job_applications,id',
        ]);
        auth()->user()->favourites()->detach($request->favourites);

        return $this->successResponse();
    }
}
