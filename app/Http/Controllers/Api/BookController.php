<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with('genres', 'user')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        // キーワード検索
        if ($request->has('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        // ジャンル絞り込み
        if ($request->has('genre')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('name', $request->genre);
            });
        }

        $books = $query->latest()->paginate(20);

        return BookResource::collection($books);
    }

    public function show(Book $book)
    {
        $book->load(['genres', 'user', 'reviews.user']);
        $book->loadCount('reviews');
        $book->loadAvg('reviews', 'rating');

        return new BookResource($book);
    }

    public function store(StoreBookRequest $request)
    {
        $validated = $request->validated();

        $book = Book::create([
            'user_id' => 1, // 基本機能では仮のユーザーID（後で認証対応）
            'title' => $validated['title'],
            'author' => $validated['author'],
            'isbn' => $validated['isbn'],
            'published_date' => $validated['published_date'] ?? null,
            'description' => $validated['description'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
        ]);

        $book->genres()->sync($validated['genres']);

        return response()->json([
            'message' => '書籍を登録しました。',
            'data' => new BookResource($book),
        ], 201);
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
        $validated = $request->validated();

        $book->update([
            'title' => $validated['title'],
            'author' => $validated['author'],
            'isbn' => $validated['isbn'],
            'published_date' => $validated['published_date'] ?? null,
            'description' => $validated['description'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
        ]);

        $book->genres()->sync($validated['genres']);

        return response()->json([
            'message' => '書籍を更新しました。',
            'data' => new BookResource($book),
        ]);
    }

    public function destroy(Book $book)
    {
        // 関連データを削除（必要に応じて）
        $book->reviews()->delete();
        $book->favoriteUsers()->detach();
        $book->genres()->detach();

        $book->delete();

        return response()->json([
            'message' => '書籍を削除しました。',
        ], 204);
    }
}
