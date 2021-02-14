<?php

use App\Http\Controllers\CommandController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\SlideshowController;
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

//slideshow
Route::get('/slideshow/{device}', [SlideshowController::class, 'slideshow']);
Route::put('/slideshow/triggerNextAction/{device}', [SlideshowController::class, 'triggerNextAction']);
Route::put('/slideshow/nextActionDone/{device}', [SlideshowController::class, 'nextActionDone']);

//index
Route::get('/index/directories', [IndexController::class, 'directories']);
Route::get('/index/state', [IndexController::class, 'state']);
Route::put('/index/update', [IndexController::class, 'update']);
Route::get('/index/statistics', [IndexController::class, 'statistics']);
Route::get('/index/years', [IndexController::class, 'years']);
Route::get('/index/favorites', [IndexController::class, 'favorites']);
Route::put('/index/toggleFavorite/{index}', [IndexController::class, 'toggleFavorite']);
Route::delete('/index/{index}', [IndexController::class, 'deleteImage']);

//queue
Route::post('/queue/create', [QueueController::class, 'create']);
Route::get('/queue/current', [QueueController::class, 'current']);
Route::get('/queue/statistics', [QueueController::class, 'statistics']);
Route::get('/queue/nextBatch', [QueueController::class, 'nextBatch']);
Route::get('/queue/previousBatch', [QueueController::class, 'previousBatch']);
Route::put('/queue/move', [QueueController::class, 'move']);

//playlist
Route::resource('playlists', PlaylistController::class);
