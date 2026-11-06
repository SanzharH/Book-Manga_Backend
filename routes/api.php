<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\OrderController;

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

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

Route::prefix('auth')
    ->controller(AuthController::class)
    ->group(function (): void {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
        Route::post('/logout', 'logout')->middleware('auth:sanctum');
    });

Route::middleware(['auth:sanctum', 'role:admin'])
    ->prefix('admin')
    ->group(function (): void {
        Route::apiResource('categories', AdminCategoryController::class);
        Route::apiResource('products', AdminProductController::class);
    });

    Route::middleware('auth:sanctum')->group(function (): void {
    Route::apiResource('orders', OrderController::class)
        ->only(['index', 'store', 'show']);
});

    
  