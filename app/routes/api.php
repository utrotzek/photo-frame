<?php

use App\Http\Controllers\CommandController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\QueueController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//commands
Route::delete('/commands/clearView/{view}', [CommandController::class, 'clearView']);
Route::apiResources([
    'commands' => CommandController::class
]);

//index
Route::get('/index/state', [IndexController::class, 'state']);
Route::put('/index/update', [IndexController::class, 'update']);
Route::get('/index/statistics', [IndexController::class, 'statistics']);
Route::post('/queue/create', [QueueController::class, 'create']);
Route::get('/queue/current', [QueueController::class, 'current']);
Route::get('/queue/nextBatch', [QueueController::class, 'nextBatch']);
Route::get('/queue/previousBatch', [QueueController::class, 'previousBatch']);
Route::put('/queue/move', [QueueController::class, 'move']);
