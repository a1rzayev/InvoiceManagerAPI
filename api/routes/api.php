<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('user-profile', [AuthController::class, 'userProfile']);
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('users')->middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
    
    Route::get('/admins/list', [UserController::class, 'getAdmins']);
    Route::get('/sellers/list', [UserController::class, 'getSellers']);
    Route::get('/clients/list', [UserController::class, 'getClients']);
});



Route::prefix('invoices')->middleware('auth:api')->group(function () {
    Route::get('/', [InvoiceController::class, 'index']);
    Route::get('/{id}', [InvoiceController::class, 'show']);
    Route::get('/status/{status}', [InvoiceController::class, 'getByStatus']);
    Route::get('/seller/{sellerId}', [InvoiceController::class, 'getBySeller']);
    Route::get('/client/{clientId}', [InvoiceController::class, 'getByClient']);
    
    Route::middleware('any.role:seller|admin')->group(function () {
        Route::post('/', [InvoiceController::class, 'store']);
        Route::put('/{id}', [InvoiceController::class, 'update']);
        Route::delete('/{id}', [InvoiceController::class, 'destroy']);
        Route::patch('/{id}/status', [InvoiceController::class, 'updateStatus']);
    });
});



Route::prefix('products')->middleware('auth:api')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::get('/search/query', [ProductController::class, 'search']);
    Route::get('/price-range/filter', [ProductController::class, 'getByPriceRange']);
    Route::get('/expensive/list', [ProductController::class, 'getExpensiveProducts']);
    Route::get('/cheap/list', [ProductController::class, 'getCheapProducts']);
    
    Route::middleware('any.role:seller|admin')->group(function () {
        Route::post('/', [ProductController::class, 'store']);
        Route::put('/{id}', [ProductController::class, 'update']);
        Route::delete('/{id}', [ProductController::class, 'destroy']);
    });
});
