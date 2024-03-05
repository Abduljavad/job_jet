<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('notfound', function () {
    return view('notFound');
})->name('not-found');

Route::get('/', function () {
    return response()->json(['message' => 'Dont be a pirate this is a Battle-Station']);
});
