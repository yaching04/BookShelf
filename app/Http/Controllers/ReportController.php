<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $userId = auth()->id();

        // 自分のレビュー一覧
        $reviews = Review::where('user_id', $userId)->get();

        // 基本統計
        $totalReviews = $reviews->count();
        $booksRead = $reviews->pluck('book_id')->unique()->count();
        $averageRating = $totalReviews > 0
            ? round($reviews->avg('rating'), 1)
            : 0;

        // 評価分布（1〜5）
        $ratingDistribution = collect([1, 2, 3, 4, 5])->map(function ($rating) use ($reviews) {
            return $reviews->where('rating', $rating)->count();
        });

        // 高評価書籍 TOP5（自分のレビューで4以上）
        $topRatedBooks = Review::where('user_id', $userId)
            ->where('rating', '>=', 4)
            ->with('book')
            ->orderByDesc('rating')
            ->orderByDesc('created_at')
            ->get()
            ->unique('book_id')
            ->take(5)
            ->map(function ($review) {
                return [
                    'id'     => $review->book->id,
                    'title'  => $review->book->title,
                    'author' => $review->book->author,
                    'rating' => $review->rating,
                ];
            })
            ->values();

        // ジャンル別評価傾向 TOP5
        $genreRatings = DB::table('reviews')
            ->join('books', 'reviews.book_id', '=', 'books.id')
            ->join('book_genre', 'books.id', '=', 'book_genre.book_id')
            ->join('genres', 'book_genre.genre_id', '=', 'genres.id')
            ->where('reviews.user_id', $userId)
            ->select(
                'genres.id',
                'genres.name',
                DB::raw('AVG(reviews.rating) as average_rating'),
                DB::raw('COUNT(reviews.id) as count')
            )
            ->groupBy('genres.id', 'genres.name')
            ->orderByDesc('average_rating')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                return [
                    'id'             => $row->id,
                    'name'           => $row->name,
                    'count'          => $row->count,
                    'average_rating' => round($row->average_rating, 1),
                ];
            });

        $stats = [
            'summary' => [
                'total_reviews'  => $totalReviews,
                'books_read'     => $booksRead,
                'average_rating' => $averageRating,
            ],
            'rating_distribution' => $ratingDistribution,
            'top_rated_books'     => $topRatedBooks,
            'genre_ratings'       => $genreRatings,
        ];

        return view('reports.index', compact('stats'));
    }
}
