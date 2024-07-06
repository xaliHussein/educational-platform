<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BannersController;
use App\Http\Controllers\EnrollmentsController;

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

Route::get('/', function () {
    return view('welcome');
});
// route::get("test", [EnrollmentsController::class, "test"]);
route::get("/pament_check", [EnrollmentsController::class, "pamentCheck"])->name('redirect');

Route::get('/test', [App\Http\Controllers\EnrollmentsController::class, 'test']);

