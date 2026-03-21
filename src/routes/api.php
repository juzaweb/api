<?php

use Juzaweb\Modules\Api\Http\Controllers\API\NotificationController;
use Juzaweb\Modules\Api\Http\Controllers\API\Pages\PageController;
use Juzaweb\Modules\Api\Http\Controllers\API\ProfileController;
use Juzaweb\Modules\Api\Http\Controllers\API\SettingController;
use Juzaweb\Modules\Api\Http\Controllers\API\TranslationController;

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

Route::get('settings', [SettingController::class, 'index']);
Route::get('translations/{locale}', [TranslationController::class, 'index']);
Route::get('pages/{slug}', [PageController::class, 'show']);

Route::middleware('auth:api')->group(function () {
    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);
    Route::put('profile/password', [ProfileController::class, 'updatePassword']);

    Route::get('notifications', [NotificationController::class, 'index']);
});
