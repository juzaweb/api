<?php

use Juzaweb\Modules\Api\Http\Controllers\Api\Pages\PageController;
use Juzaweb\Modules\Api\Http\Controllers\Api\ProfileController;
use Juzaweb\Modules\Api\Http\Controllers\Api\SettingController;
use Juzaweb\Modules\Api\Http\Controllers\Api\TranslationController;

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
});
