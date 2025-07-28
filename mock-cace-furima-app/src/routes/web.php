<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;

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

// Fortify登録のオーバーライド
Route::post('/register', [RegisteredUserController::class, 'store']);

//  未認証でもアクセス可能なページ
Route::get('/', [ItemController::class, 'index']);
Route::get('/item/{item_id}', [ItemController::class, 'show']);

//  ログイン後にしかアクセスできないページ
Route::middleware('auth')->group(function () {
    Route::get('/sell', [ItemController::class, 'add']);
    Route::post('/sell', [ItemController::class, 'store']);
    Route::get('/purchase/{item_id}', [PurchaseController::class, 'purchase']);
    Route::post('/purchase/{item_id}', [PurchaseController::class, 'store']);
    Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'edit']);
    Route::post('/purchase/address/{item_id}', [PurchaseController::class, 'update']);
    Route::get('/purchase/success/{item_id}', [PurchaseController::class, 'success'])->name('purchase.success');
    Route::get('/purchase/cancel/{item_id}', [PurchaseController::class, 'cancel'])->name('purchase.cancel');
    Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);
    Route::get('/checkout', [PaymentController::class, 'checkout'])->name('payment.checkout');
    Route::get('/success', fn () => '支払い成功！')->name('payment.success');
    Route::get('/cancel', fn () => '支払いキャンセル')->name('payment.cancel');
    Route::get('/mypage', [ProfileController::class, 'index']);
    Route::get('/mypage/profile', [ProfileController::class, 'edit']);
    Route::patch('/mypage/profile', [ProfileController::class, 'update']);
    Route::post('/favorite/{item_id}', [FavoriteController::class, 'store']);
    Route::delete('/favorite/{item_id}', [FavoriteController::class, 'destroy']);
    Route::post('/comment', [CommentController::class, 'store']);
});
