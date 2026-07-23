<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Genre;

class GenreSeeder extends Seeder
{
    public function run(): void
    {
        $genres = [
            '小説',
            'ビジネス',
            '技術書',
            '自己啓発',
            'エッセイ',
            '歴史',
            '科学',
            '芸術',
            '料理',
            '旅行',
        ];

        foreach ($genres as $genreName) {
            Genre::firstOrCreate(
                ['name' => $genreName],           // この条件で探す
                []                                // 見つからなかったら作成（name以外に追加するカラムはないので空でOK）
            );
        }
    }
}
