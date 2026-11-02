<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

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