<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Review;

class ReviewLikeSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $reviews = Review::all();

        foreach ($reviews as $review) {
            // 自分のレビューを除いたユーザー一覧
            $otherUsers = $users->where('id', '!=', $review->user_id);

            // 0〜3人をランダムに選ぶ
            $likers = $otherUsers->random(rand(0, 3));

            // syncWithoutDetaching でいいねを追加
            $review->likedByUsers()->syncWithoutDetaching($likers->pluck('id'));
        }
    }
}
