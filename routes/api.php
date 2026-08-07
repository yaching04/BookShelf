<?php

use App\Http\Controllers\Api\BookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    // 読み取り：認証なし
    Route::get('books', [BookController::class, 'index'])->name('api.books.index');
    Route::get('books/{book}', [BookController::class, 'show'])->name('api.books.show');

    // 書き込み：認証必須
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('books', [BookController::class, 'store'])->name('api.books.store');
        Route::put('books/{book}', [BookController::class, 'update'])->name('api.books.update');
        Route::patch('books/{book}', [BookController::class, 'update']);
        Route::delete('books/{book}', [BookController::class, 'destroy'])->name('api.books.destroy');
    });
});


// 学習・テスト用：メールとパスワードでトークン発行
Route::post('/v1/login', function (Request $request) {
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    $user = \App\Models\User::where('email', $request->email)->first();

    if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
        return response()->json([
            'message' => 'メールアドレスまたはパスワードが正しくありません。',
        ], 401);
    }

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user'  => [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
        ],
    ]);
});
