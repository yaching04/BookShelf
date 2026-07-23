<?php

namespace App\Http\Controllers;

use App\Models\Book;

class RankingController extends Controller
{
    /**
     * 評価ランキング（TOP10）を表示
     */
    public function index()
    {
        $rankedBooks = Book::withCount('reviews')                    // レビュー数
                            ->withAvg('reviews', 'rating')            // 平均評価
                            ->with('genres', 'user')
                            ->having('reviews_count', '>', 0)         // ← レビューが1件以上の本だけ
                            ->orderBy('reviews_avg_rating', 'desc')   // 平均評価が高い順
                            ->orderBy('reviews_count', 'desc')        // 同率ならレビュー数が多い順
                            ->take(10)                                // TOP10のみ
                            ->get();

        return view('ranking.index', compact('rankedBooks'));
    }
}
