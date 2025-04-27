<?php

use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\Admin\adminAuthController;
use App\Http\Controllers\Admin\EventController;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\OrderController;

Route::get('/admin/orders/total-count', [OrderController::class, 'getTotalOrderCount']);
Route::get('/admin/orders', [OrderController::class, 'getAllOrders']);
Route::post('/vendor/orders/update-status', [OrderController::class, 'updateOrderStatus']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/vendor/orders', [OrderController::class, 'getVendorOrders']);

Route::post('/payhere/callback', [OrderController::class, 'handlePayHereCallback']);
Route::get('/products/{productId}/reviews', [ReviewController::class, 'getReviewsByProduct']);
Route::post('/wishlist/getItems', [WishlistController::class, 'getWishlistItems']);
Route::post('/wishlist/add', [WishlistController::class, 'addToWishlist']);
// Remove a product from the cart
Route::get('/products/{productId}/reviews', [ReviewController::class, 'getReviews']);
Route::post('/reviews/add', [ReviewController::class, 'addReview']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::post('/cart/delete', [CartController::class, 'deleteCartItem']);
Route::post('/cart/get-items', [CartController::class, 'getCartItems']);
Route::post('/cart/update', [CartController::class, 'updateCartItem']);
Route::post('/cart/add', [CartController::class, 'addToCart']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/admin/events', [EventController::class, 'getAllEvents']);
Route::put('/admin/products/{id}/approve', [ProductController::class, 'approveProduct']);
Route::put('/admin/products/{id}/reject', [ProductController::class, 'rejectProduct']);
Route::get('/admin/products/pending', [ProductController::class, 'getPendingProducts']);
Route::get('/admin/products/pending-count', [ProductController::class, 'getPendingProductsCount']);
Route::get('/admin/customers/total', [CustomerAuthController::class, 'getTotalCustomers']);
Route::get('/admin/vendors/approved-count', [VendorController::class, 'getApprovedVendorsCount']);
Route::get('/admin/vendors/pending-count', [VendorController::class, 'getPendingVendorsCount']);
Route::get('/admin/vendors/pending', [VendorController::class, 'pending']);
Route::put('/admin/vendors/{id}/approve', [VendorController::class, 'approve']);
Route::put('/admin/vendors/{id}/reject', [VendorController::class, 'reject']);
Route::get('/admin/events', [EventController::class, 'index']);
Route::post('/admin/events', [EventController::class, 'store']);
Route::post('/admin/login', [adminAuthController::class, 'login']);
Route::get('/admin/username', [adminAuthController::class, 'getAdminUsername']);
Route::post('/send-otp', [CustomerAuthController::class, 'sendOtp']);
Route::post('/verify-otp', [CustomerAuthController::class, 'verifyOtp']);
Route::post('/register-customer', [CustomerAuthController::class, 'registerCustomer']);
Route::post('/customer-login', [CustomerAuthController::class, 'customerLogin']);
// Route::put('/customer/update', [CustomerAuthController::class, 'update']);

Route::post('/customers/update', [CustomerAuthController::class, 'update']);
// Route::put('/customers/{id}', [CustomerAuthController::class, 'update']);

Route::post('/vendor/login',[VendorController::class,'vendorLogin']);
Route::post('/vendor/send-otp', [VendorController::class,'sendOtp']);
Route::post('/vendor/verifyOtp', [VendorController::class,'verifyOtp']);
Route::post('/vendor/update', [VendorController::class,'update']);

//AddProduct
Route::post('/products/store',[ProductController::class,'store']);
//RenderProductVendorPage
Route::get('/vendor/products/{vendorId}', [ProductController::class, 'getVendorProducts']);
//Update Product
Route::post('/vendor/product/update', [ProductController::class, 'updateProduct']);

Route::get('/vendor/session', function () {
    $vendor = Session::get('vendor');
    return response()->json(['vendor' => $vendor]);
});
Route::middleware('auth:vendor')->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
});

Route::middleware('auth:vendor')->get('/vendor/details', [VendorController::class, 'getVendorDetails']);


//Route::middleware('auth:sanctum')->post('/vendor/logout', [AuthController::class, 'logout']);