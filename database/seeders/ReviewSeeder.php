<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        $comments = [
            'とても面白かったです。',
            '内容が濃くて勉強になりました。',
            'もう少し深掘りしてほしかった。',
            '読みやすくておすすめです。',
            '期待以上でした！',
            '少し難しかったですが良かったです。',
            '実践的な内容が多く参考になりました。',
            'ストーリーが魅力的でした。',
            '考えさせられる本でした。',
            'また読み返したいと思います。',
        ];

        $totalReviews = 0;

        foreach ($books as $book) {
            // 各書籍に2〜4件のレビューを割り当て
            $reviewCount = rand(2, 4);

            // その本の作成者（user_id）を除外してユーザー一覧を作成
            $possibleUsers = $users->where('id', '!=', $book->user_id);

            // レビューを付けるユーザーを重複なく選ぶ
            $selectedUsers = $possibleUsers->random(min($reviewCount, $possibleUsers->count()));

            foreach ($selectedUsers as $user) {
                if ($totalReviews >= 32) {
                    break;
                }

                Review::create([
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                    'rating' => rand(3, 5),
                    'comment' => $comments[array_rand($comments)],
                ]);

                $totalReviews++;
            }

            if ($totalReviews >= 32) {
                break;
            }
        }
    }
}
