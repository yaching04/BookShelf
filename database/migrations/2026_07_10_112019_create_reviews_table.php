<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();   // 投稿者
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();   // 対象書籍
            $table->unsignedTinyInteger('rating');   // 評価（1〜5）
            $table->text('comment');   // コメント
            $table->timestamps();

            // 1ユーザーが同じ本に複数レビューできないようにする
            $table->unique(['user_id', 'book_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
