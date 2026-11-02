<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::get('/health', function () {
    try {
        DB::select('select 1');

        return response()->json([
            'application' => config('app.name'),
            'time' => now()->toIso8601String(),
            'database' => 'connected',
        ]);
    } catch (\Throwable $exception) {
        return response()->json([
            'application' => config('app.name'),
            'time' => now()->toIso8601String(),
            'database' => 'unavailable',
        ], 503);
    }
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->controller(AuthController::class)->group(function (): void {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->middleware('auth:sanctum');
});