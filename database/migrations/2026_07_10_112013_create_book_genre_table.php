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
        Schema::create('book_genre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();   // booksテーブルのidを参照する外部キー
            $table->foreignId('genre_id')->constrained()->cascadeOnDelete();   // genresテーブルのidを参照する外部キー
            $table->timestamps();

            // 同じ本とジャンルの組み合わせが重複しないようにする
            $table->unique(['book_id', 'genre_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_genre');
    }
};
