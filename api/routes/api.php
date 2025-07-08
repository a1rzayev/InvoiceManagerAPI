<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
    
    Route::get('/admins/list', [UserController::class, 'getAdmins']);
    Route::get('/sellers/list', [UserController::class, 'getSellers']);
    Route::get('/clients/list', [UserController::class, 'getClients']);
});



Route::prefix('invoices')->group(function () {
    Route::get('/', [InvoiceController::class, 'index']);
    Route::post('/', [InvoiceController::class, 'store']);
    Route::get('/{id}', [InvoiceController::class, 'show']);
    Route::put('/{id}', [InvoiceController::class, 'update']);
    Route::delete('/{id}', [InvoiceController::class, 'destroy']);
    
    Route::get('/status/{status}', [InvoiceController::class, 'getByStatus']);
    Route::get('/seller/{sellerId}', [InvoiceController::class, 'getBySeller']);
    Route::get('/client/{clientId}', [InvoiceController::class, 'getByClient']);
    Route::patch('/{id}/status', [InvoiceController::class, 'updateStatus']);
});



Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::post('/', [ProductController::class, 'store']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);
    
    Route::get('/search/query', [ProductController::class, 'search']);
    Route::get('/price-range/filter', [ProductController::class, 'getByPriceRange']);
    Route::get('/expensive/list', [ProductController::class, 'getExpensiveProducts']);
    Route::get('/cheap/list', [ProductController::class, 'getCheapProducts']);
});
