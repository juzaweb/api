<?php

use Illuminate\Http\Request;

use Juzaweb\Modules\Api\Http\Controllers\Api\SettingController;
use Juzaweb\Modules\Api\Http\Controllers\Api\TranslationController;
use Juzaweb\Modules\Api\Http\Controllers\Api\Pages\PageController;

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
