<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;   // 書籍登録時のバリデーション
use App\Http\Requests\UpdateBookRequest;  // 書籍更新時のバリデーション
use App\Models\Book;                      // 書籍モデル

class BookController extends Controller
{
    /**
     * 書籍一覧画面を表示
     */
    public function index()
    {
        // 最新順にページネーションで10件取得
        $books = Book::with('genres', 'user')   // ジャンルと作成者情報も一緒に取得
                        ->latest()
                        ->paginate(10);

        return view('books.index', compact('books'));
    }

    /**
     * 書籍登録フォームを表示
     */
    public function create()
    {
        $genres = \App\Models\Genre::all();  // ジャンル一覧を取得
        return view('books.create', compact('genres'));
    }

    /**
     * 書籍を新規登録する
     */
    public function store(StoreBookRequest $request)
    {
        $validated = $request->validated();  // バリデーション済みのデータ

        $book = Book::create([
            'user_id'        => auth()->id(),  // 現在ログイン中のユーザー
            'title'          => $validated['title'],
            'author'         => $validated['author'],
            'isbn'           => $validated['isbn'],
            'published_date' => $validated['published_date'] ?? null,
            'description'    => $validated['description'] ?? null,
            'image_url'      => $validated['image_url'] ?? null,
        ]);

        // 選択されたジャンルを紐付け
        $book->genres()->sync($validated['genres']);

        return redirect()->route('books.show', $book)
            ->with('success', '書籍を登録しました。');
    }

    /**
     * 書籍の詳細画面を表示
     */
    public function show(Book $book)
    {
        // 必要な関連データを一気に取得
        $book->load(['genres', 'user', 'reviews.user', 'reviews.likes']);

        return view('books.show', compact('book'));
    }

    /**
     * 書籍編集フォームを表示
     */
    public function edit(Book $book)
    {
        $genres = \App\Models\Genre::all();
        $bookGenreIds = $book->genres->pluck('id')->toArray();  // すでに選択されているジャンルID

        return view('books.edit', compact('book', 'genres', 'bookGenreIds'));
    }

    /**
     * 書籍情報を更新する
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        $validated = $request->validated();

        $book->update([
            'title'          => $validated['title'],
            'author'         => $validated['author'],
            'isbn'           => $validated['isbn'],
            'published_date' => $validated['published_date'] ?? null,
            'description'    => $validated['description'] ?? null,
            'image_url'      => $validated['image_url'] ?? null,
        ]);

        $book->genres()->sync($validated['genres']);

        return redirect()->route('books.show', $book)
            ->with('success', '書籍を更新しました。');
    }

    /**
     * 書籍を削除する
     */
    public function destroy(Book $book)
    {
        // 自分の書籍以外は削除できない
        if (auth()->id() !== $book->user_id) {
            abort(403, '自分の書籍以外は削除できません。');
        }

        $book->delete();

        return redirect()->route('books.index')
            ->with('success', '書籍を削除しました。');
    }

    /**
     * お気に入りのトグル（追加/削除）
     */
    public function toggleFavorite(Book $book)
    {
        auth()->user()->favoriteBooks()->toggle($book->id);

        return back();
    }
}
