<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WelcomeController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('register', [UserController::class, 'register']);
Route::post('login', [Usercontroller::class, 'login']);
Route::post('logout', [Usercontroller::class, 'logout']);
Route::post('forgot_password', [Usercontroller::class, 'forgot_password']);
Route::post('change_forgotten_password', [Usercontroller::class, 'change_forgotten_password']);
Route::post('new_address', [UserController::class, 'new_address']);
Route::post('change_address', [UserController::class, 'change_address']);
Route::get('addresses', [UserController::class, 'addresses']);
Route::post('delete_address', [UserController::class, 'delete_address']);
Route::post('update_address', [UserController::class, 'update_address']);




Route::get('categories', [ProductController::class, 'categories']);
Route::get('categoriesWithproducts', [ProductController::class, 'categoriesWithproducts']);
Route::post('category_by_id', [ProductController::class, 'category_by_id']);
Route::post('add_whitelist', [ProductController::class, 'add_whitelist']);
Route::post('myWhiteList', [ProductController::class, 'myWhiteList']);
Route::post('get_product_by_id', [ProductController::class, 'get_product_by_id']);
Route::post('search_in_category', [ProductController::class, 'search_in_category']);
Route::post('search', [ProductController::class, 'search']);
Route::post('search_filter', [ProductController::class, 'search_filter']);
Route::get('latest_products', [ProductController::class, 'latest_products']);





Route::get('welcome', [WelcomeController::class, 'welcome']);
Route::get('banner', [WelcomeController::class, 'banner']);
Route::get('privacy', [WelcomeController::class, 'privacy']);
Route::get('about_us', [WelcomeController::class, 'about_us']);





Route::post('addToCart', [CartController::class, 'addToCart']);
Route::post('my_cart', [CartController::class, 'my_cart']);



Route::post('checkout', [OrderController::class, 'checkout']);
Route::get('place_order', [OrderController::class, 'place_order']);
Route::get('OnProgressOrders', [OrderController::class, 'OnProgressOrders']);
Route::get('CompletedOrders', [OrderController::class, 'CompletedOrders']);
Route::post('Order_by_id', [OrderController::class, 'Order_by_id']);
Route::post('order_review', [OrderController::class, 'order_review']);
