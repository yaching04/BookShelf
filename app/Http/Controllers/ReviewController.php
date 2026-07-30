<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;   // レビュー投稿時のバリデーション
use App\Http\Requests\UpdateReviewRequest;  // レビュー更新時のバリデーション
use App\Models\Book;
use App\Models\Review;

class ReviewController extends Controller
{
    /**
     * レビューを投稿する
     */
    public function store(StoreReviewRequest $request, Book $book)
    {
        $validated = $request->validated();

        Review::create([
            'user_id' => auth()->id(),
            'book_id' => $book->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return back()->with('success', 'レビューを投稿しました。');
    }

    /**
     * レビュー編集フォームを表示
     */
    public function edit(Review $review)
    {
        // 自分のレビュー以外は編集できない
        if (auth()->id() !== $review->user_id) {
            abort(403, '自分のレビュー以外は編集できません。');
        }

        return view('reviews.edit', compact('review'));
    }

    /**
     * レビューを更新する
     */
    public function update(UpdateReviewRequest $request, Review $review)
    {
        // 自分のレビュー以外は更新できない
        if (auth()->id() !== $review->user_id) {
            abort(403);
        }

        $validated = $request->validated();

        $review->update([
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return redirect()->route('books.show', $review->book_id)
            ->with('success', 'レビューを更新しました。');
    }

    /**
     * レビューを削除する
     */
    public function destroy(Review $review)
    {
        // 自分のレビュー以外は削除できない
        if (auth()->id() !== $review->user_id) {
            abort(403, '自分のレビュー以外は削除できません。');
        }

        $review->delete();

        return back()->with('success', 'レビューを削除しました。');
    }
}
