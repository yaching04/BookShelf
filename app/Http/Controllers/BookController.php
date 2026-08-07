<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BookController extends Controller
{
    /**
     * 書籍一覧画面を表示
     */
    public function index(Request $request)
    {
        $query = Book::with('genres', 'user')
            ->withAvg('reviews', 'rating');

        // キーワード検索（タイトル・著者）
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        // ジャンル絞り込み
        if ($request->filled('genre')) {
            $genreId = $request->input('genre');
            $query->whereHas('genres', function ($q) use ($genreId) {
                $q->where('genres.id', $genreId);
            });
        }

        // 並び順
        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'oldest' => $query->orderBy('created_at', 'asc'),
            'rating' => $query->orderByDesc('reviews_avg_rating')->orderByDesc('created_at'),
            'title'  => $query->orderBy('title', 'asc'),
            default  => $query->orderByDesc('created_at'), // newest
        };

        $books = $query->paginate(10)->withQueryString();
        $genres = Genre::orderBy('name')->get();

        return view('books.index', compact('books', 'genres'));
    }

    /**
     * 書籍登録フォームを表示
     */
    public function create()
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    /**
     * 書籍を新規登録する
     */
    public function store(StoreBookRequest $request)
    {
        $validated = $request->validated();

        $book = Book::create([
            'user_id'        => auth()->id(),
            'title'          => $validated['title'],
            'author'         => $validated['author'],
            'isbn'           => $validated['isbn'],
            'published_date' => $validated['published_date'] ?? null,
            'description'    => $validated['description'] ?? null,
            'image_url'      => $validated['image_url'] ?? null,
        ]);

        $book->genres()->sync($validated['genres']);

        return redirect()->route('books.show', $book)
            ->with('success', '書籍を登録しました。');
    }

    /**
     * 書籍の詳細画面を表示
     */
    public function show(Book $book)
    {
        $book->load(['genres', 'user', 'reviews.user', 'reviews.likes']);

        return view('books.show', compact('book'));
    }

    /**
     * 書籍編集フォームを表示
     */
    public function edit(Book $book)
    {
        $genres = Genre::all();
        $bookGenreIds = $book->genres->pluck('id')->toArray();

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

    /**
     * ISBNから書籍情報を取得（Google Books API）
     */
    public function fetchByIsbn(string $isbn)
    {
        if (!preg_match('/^[0-9]{13}$/', $isbn)) {
            return response()->json([
                'error' => 'ISBNは13桁の数字で入力してください。',
            ], 422);
        }

        try {
            $response = Http::timeout(10)->get('https://openlibrary.org/api/books', [
                'bibkeys' => 'ISBN:' . $isbn,
                'format'  => 'json',
                'jscmd'   => 'data',
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'error' => '書籍情報の取得に失敗しました。',
                ], 502);
            }

            $data = $response->json('ISBN:' . $isbn);

            if (empty($data)) {
                return response()->json([
                    'error' => '該当する書籍が見つかりませんでした。',
                ], 404);
            }

            $authors = collect($data['authors'] ?? [])
                ->pluck('name')
                ->filter()
                ->implode(', ');

            $publishedDate = $data['publish_date'] ?? null;
            // "2005" や "Jan 01, 2005" など形式がバラバラなので、年だけでも入れる
            if ($publishedDate && preg_match('/(\d{4})/', $publishedDate, $m)) {
                $publishedDate = $m[1] . '-01-01';
            } else {
                $publishedDate = null;
            }

            $imageUrl = $data['cover']['medium']
                ?? $data['cover']['large']
                ?? $data['cover']['small']
                ?? null;

            return response()->json([
                'title'          => $data['title'] ?? '',
                'author'         => $authors,
                'description'    => $data['notes'] ?? ($data['subtitle'] ?? ''),
                'published_date' => $publishedDate,
                'image_url'      => $imageUrl,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => '通信エラーが発生しました。',
            ], 500);
        }
    }
}
