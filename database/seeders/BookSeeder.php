<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\User;
use App\Models\Genre;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        // 登録者は User::first()（山田太郎）固定
        $user = User::first();

        // ジャンル名からジャンルIDを取得できるようにしておく
        $genres = Genre::pluck('id', 'name');

        $books = [
            [
                'title' => '吾輩は猫である',
                'author' => '夏目漱石',
                'isbn' => '9784101010014',
                'published_date' => '1905-01-01',
                'genre_names' => ['小説'],
                'number' => 1,
            ],
            [
                'title' => '人を動かす',
                'author' => 'D・カーネギー',
                'isbn' => '9784422100524',
                'published_date' => '1936-10-01',
                'genre_names' => ['ビジネス', '自己啓発'],
                'number' => 2,
            ],
            [
                'title' => 'リーダブルコード',
                'author' => 'Dustin Boswell',
                'isbn' => '9784873115658',
                'published_date' => '2012-06-23',
                'genre_names' => ['技術書'],
                'number' => 3,
            ],
            [
                'title' => '7つの習慣',
                'author' => 'スティーブン・R・コヴィー',
                'isbn' => '9784863940246',
                'published_date' => '2013-08-30',
                'genre_names' => ['ビジネス', '自己啓発'],
                'number' => 4,
            ],
            [
                'title' => '坊っちゃん',
                'author' => '夏目漱石',
                'isbn' => '9784101010021',
                'published_date' => '1906-04-01',
                'genre_names' => ['小説'],
                'number' => 5,
            ],
            [
                'title' => 'サピエンス全史',
                'author' => 'ユヴァル・ノア・ハラリ',
                'isbn' => '9784309226712',
                'published_date' => '2016-09-08',
                'genre_names' => ['歴史', '科学'],
                'number' => 6,
            ],
            [
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'isbn' => '9784048930598',
                'published_date' => '2017-12-18',
                'genre_names' => ['技術書'],
                'number' => 7,
            ],
            [
                'title' => '嫌われる勇気',
                'author' => '岸見一郎・古賀史健',
                'isbn' => '9784478025819',
                'published_date' => '2013-12-13',
                'genre_names' => ['自己啓発'],
                'number' => 8,
            ],
            [
                'title' => '火花',
                'author' => '又吉直樹',
                'isbn' => '9784163902302',
                'published_date' => '2015-03-11',
                'genre_names' => ['小説'],
                'number' => 9,
            ],
            [
                'title' => 'FACTFULNESS',
                'author' => 'ハンス・ロスリング',
                'isbn' => '9784822289607',
                'published_date' => '2019-01-11',
                'genre_names' => ['ビジネス', '科学'],
                'number' => 10,
            ],
            [
                'title' => 'コンテナ物語',
                'author' => 'マルク・レビンソン',
                'isbn' => '9784822251468',
                'published_date' => '2007-01-18',
                'genre_names' => ['ビジネス', '歴史'],
                'number' => 11,
            ],
        ];

        foreach ($books as $bookData) {
            // firstOrCreate（ISBNで重複防止）
            $book = Book::firstOrCreate(
                ['isbn' => $bookData['isbn']],
                [
                    'user_id'        => $user->id,
                    'title'          => $bookData['title'],
                    'author'         => $bookData['author'],
                    'published_date' => $bookData['published_date'],
                    'description'    => 'ダミー説明文です。',
                    'image_url'      => "https://placehold.co/200x300/e2e8f0/475569?text={$bookData['number']}",
                ]
            );

            // ジャンル紐付け（syncを使用）
            $genreIds = $genres->only($bookData['genre_names'])->values();
            $book->genres()->sync($genreIds);
        }
    }
}
