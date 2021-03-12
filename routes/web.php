<?php

use App\Http\Controllers\BasketController;
use App\Http\Controllers\ResetController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::resource('/', MainController::class)->name('index', 'index');

Auth::routes([
    'reset' => false,
    'confirm' => false,
    'verify' => false,
]);

Route::get('/reset', [ResetController::class, 'reset'])->name('reset');

Route::get('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('get-logout');

Route::middleware(['auth'])->group(function() {
    Route::group([
        'prefix' => 'person',
        'as' => 'person.',
    ], function() {
        Route::get('/orders', [App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
    });

    Route::group([
        'middleware' => 'auth',
        'prefix' => 'admin',
    ], function () {
        Route::group(['middleware' => 'is_admin'], function () {
            Route::get('/orders', [App\Http\Controllers\Admin\OrderController::class, 'index'])->name('home');
            Route::get('/orders/{order}', [App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
        });

        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
        Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
    });
});



Route::get('/categories', [MainController::class, 'categories'])->name('categories');
Route::post('subscription/{product}', [MainController::class, 'subscribe'])->name('subscription');

//Route::get('/home', [App\Http\Controllers\Admin\OrderController::class, 'index'])->name('home');

Route::post('basket/add/{id}', [BasketController::class, 'basketAdd'])->name('basket-add');

Route::group([
    'middleware' => 'basket_not_empty',
    'prefix' => 'basket', // задает префикс, т.е все маршруты ниже будут начинаться с basket/...
], function () {
    Route::get('/', [BasketController::class, 'basket'])->name('basket'); // basket считает как роут category
    Route::get('/place', [BasketController::class, 'basketPlace'])->name('basket-place');
    Route::post('/remove/{id}', [BasketController::class, 'basketRemove'])->name('basket-remove');
    Route::post('/place', [BasketController::class, 'basketConfirm'])->name('basket-confirm');
});

Route::get('/{category}', [MainController::class, 'category'])->name('category');
Route::get('/{category}/{product}', [MainController::class, 'product'])->name('product');
