<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        foreach ($users as $user) {
            // 各ユーザーに3〜5冊をランダムに選んでお気に入り登録
            $favoriteBooks = $books->random(rand(3, 5));

            // syncWithoutDetaching で追加（既存のお気に入りを保持したまま追加）
            $user->favoriteBooks()->syncWithoutDetaching($favoriteBooks->pluck('id'));
        }
    }
}
