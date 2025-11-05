<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Requests\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [ItemController::class, 'index'])->name('index');
Route::get('/item/{item_id}', [ItemController::class, 'show'])->name('item.detail');

Route::middleware('auth', 'verified')->group(function () {
    Route::get('/sell', [ItemController::class, 'add']);
    Route::post('/sell', [ItemController::class, 'store']);

    Route::get('/purchase/{item_id}', [PurchaseController::class, 'index'])->middleware('purchase')->name('purchase.index');
    Route::post('/purchase/{item_id}', [PurchaseController::class, 'purchase'])->middleware('purchase')->name('purchase.process');
    Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'edit'])->name('purchase.address.edit');
    Route::post('/purchase/address/{item_id}', [PurchaseController::class, 'update'])->name('purchase.address.update');
    Route::get('/purchase/success/{item_id}', [PurchaseController::class, 'success'])->name('purchase.success');
    Route::get('/purchase/cancel/{item_id}', [PurchaseController::class, 'cancel'])->name('purchase.cancel');

    Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

    //マイページ
    Route::get('/mypage', [ProfileController::class, 'index']);
    Route::get('/mypage/profile', [ProfileController::class, 'edit']);
    Route::patch('/mypage/profile', [ProfileController::class, 'update']);

    //お気に入り・コメント
    Route::post('/favorite/{item_id}', [FavoriteController::class, 'store']);
    Route::delete('/favorite/{item_id}', [FavoriteController::class, 'destroy']);
    Route::post('/comment', [CommentController::class, 'store']);

    // show chat
    Route::get('/conversations/{conversation}', [ConversationController::class,'show'])->name('conversations.show');

    // message actions
    Route::post('/conversations/{conversation}/messages', [MessageController::class,'store'])->name('messages.store');
    Route::put('/messages/{message}', [MessageController::class,'update'])->name('messages.update');
    Route::delete('/messages/{message}', [MessageController::class,'destroy'])->name('messages.destroy');

    // optional: server-side safe-complete (backup)
    Route::post('/conversations/{conversation}/complete', [ConversationController::class, 'complete'])->name('conversations.complete');

    // mark as read (ajax)
    Route::post('/messages/{message}/read', [MessageController::class,'markRead'])->name('messages.read');

    // 評価登録
    Route::post('/items/{item}/ratings', [RatingController::class, 'store'])->name('ratings.store');
});

Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('email');
Route::post('/register', [RegisteredUserController::class, 'store']);

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->name('verification.notice');

Route::post('/email/verification-notification', function (Request $request) {
    $user = $request->user();

    if (! $user) {
        $sessionUser = session('unauthenticated_user');
        $sessionUserId = session('unauthenticated_user_id');

        if ($sessionUser instanceof \App\Models\User) {
            $user = $sessionUser;
        } elseif ($sessionUserId) {
            $user = \App\Models\User::find($sessionUserId);
        }
    }

    if (! $user) {
        return redirect()->route('login')->with('error', '確認メールを再送するユーザー情報が見つかりません。ログインしてください。');
    }

    $user->sendEmailVerificationNotification();

    return back()->with('message', '確認メールを再送しました。');
})->middleware('throttle:6,1')->name('verification.send');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    session()->forget('unauthenticated_user');
    session()->forget('unauthenticated_user_id');
    return redirect('/mypage/profile');
})->middleware(['signed'])->name('verification.verify');
