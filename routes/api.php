<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScorerController;
use App\Http\Controllers\ViewrController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::controller(AuthController::class)->group(function(){
    Route::post('/login','login');
    Route::get('/logout','logout');
});


Route::middleware(['auth:sanctum','scorer'])->group(function(){
    Route::get('start/{bat}',[ScorerController::class,'start']);
    Route::get('start_inning/{num}',[ScorerController::class,'start_inning']);
    Route::post('add_score/',[ScorerController::class,'add_score']);
    Route::get('undo/',[ScorerController::class,'undo']);
});

Route::middleware(['auth:sanctum'])->group(function(){
    Route::get('current_score/',[ViewrController::class,'current_score']);
    Route::get('score_card/',[ViewrController::class,'score_card']);
});