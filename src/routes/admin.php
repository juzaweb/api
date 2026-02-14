<?php

use Juzaweb\Modules\Api\Http\Controllers\ApiKeyController;

Route::get('api-keys', [ApiKeyController::class, 'index'])->name('admin.api.keys.index');
Route::post('api-keys', [ApiKeyController::class, 'store'])->name('admin.api.keys.store');
Route::delete('api-keys/{id}', [ApiKeyController::class, 'destroy'])->name('admin.api.keys.destroy');
