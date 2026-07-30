<?php

namespace App\Http\Controllers;

class FavoriteController extends Controller
{
    /**
     * ユーザーのお気に入り一覧を表示
     */
    public function index()
    {
        $user = auth()->user();

        // お気に入りした書籍を最新順に取得
        $books = $user->favoriteBooks()
            ->with('genres', 'user')
            ->latest()
            ->paginate(10);

        return view('favorites.index', compact('books'));
    }
}
