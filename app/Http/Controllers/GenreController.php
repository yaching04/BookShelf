<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGenreRequest;   // ジャンル登録時のバリデーション
use App\Http\Requests\UpdateGenreRequest;  // ジャンル更新時のバリデーション
use App\Models\Genre;

class GenreController extends Controller
{
    /**
     * ジャンル一覧を表示
     */
    public function index()
    {
        // 書籍数も一緒に取得
        $genres = Genre::withCount('books')
                        ->latest()
                        ->get();

        return view('genres.index', compact('genres'));
    }

    /**
     * ジャンル登録フォームを表示
     */
    public function create()
    {
        return view('genres.create');
    }

    /**
     * ジャンルを新規登録
     */
    public function store(StoreGenreRequest $request)
    {
        $validated = $request->validated();

        Genre::create([
            'name' => $validated['name'],
        ]);

        return redirect()->route('genres.index')
            ->with('success', 'ジャンルを登録しました。');
    }

    /**
     * ジャンル詳細（そのジャンルの書籍一覧）を表示
     */
    public function show(Genre $genre)
    {
        $books = $genre->books()
                        ->with('genres', 'user')
                        ->latest()
                        ->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    /**
     * ジャンル編集フォームを表示
     */
    public function edit(Genre $genre)
    {
        return view('genres.edit', compact('genre'));
    }

    /**
     * ジャンルを更新
     */
    public function update(UpdateGenreRequest $request, Genre $genre)
    {
        $validated = $request->validated();

        $genre->update([
            'name' => $validated['name'],
        ]);

        return redirect()->route('genres.index')
            ->with('success', 'ジャンルを更新しました。');
    }

    /**
     * ジャンルを削除
     */
    public function destroy(Genre $genre)
    {
        // 書籍が紐づいている場合は削除できない
        if ($genre->books()->exists()) {
            return redirect()->route('genres.index')
                ->with('error', 'このジャンルには書籍が登録されているため削除できません。');
        }

        $genre->delete();

        return redirect()->route('genres.index')
            ->with('success', 'ジャンルを削除しました。');
    }
}
