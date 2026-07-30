<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReviewController;
use App\Models\Review;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// トップページ（ウェルカム画面）
Route::get('/', function () {
    return view('welcome');
});

// ホームにアクセスされたら書籍一覧へリダイレクト
Route::get('/home', function () {
    return redirect('/books');
});

/*
|--------------------------------------------------------------------------
| 公開ルート（ゲストでもアクセス可能）
|--------------------------------------------------------------------------
*/
// 書籍一覧画面
Route::get('/books', [BookController::class, 'index'])->name('books.index');

// 評価ランキング画面
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');

/*
|--------------------------------------------------------------------------
| 認証が必要なルート
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // ==================== 書籍関連（書き込み系） ====================
    // ★ 必ず /books/{book} より前に定義する！
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');

    // 書籍登録処理
    Route::post('/books', [BookController::class, 'store'])->name('books.store');

    // 書籍編集フォーム画面
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');

    // 書籍更新処理
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');

    // 書籍削除処理
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');

    // お気に入り
    Route::post('/books/{book}/favorite', [BookController::class, 'toggleFavorite'])->name('favorites.toggle');
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');

    // ==================== レビュー関連 ====================
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('/reviews/{review}/like', function (Review $review) {
        auth()->user()->likedReviews()->toggle($review->id);

        return back();
    })->name('reviews.like');

    // ==================== ジャンル関連 ====================
    Route::resource('genres', GenreController::class);

});

// ★ 書籍詳細は最後に定義（create より後）
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
