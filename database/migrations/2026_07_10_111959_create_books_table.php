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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();   // 作成者:削除時に書籍も削除
            $table->string('title');   // 書籍タイトル
            $table->string('author');   // 著者名
            $table->string('isbn')->unique();   // ISBNコード（一意）
            $table->date('published_date')->nullable();   // 発行日（任意）
            $table->text('description')->nullable();   // 書籍の説明（任意）
            $table->string('image_url')->nullable(); // 書籍の画像URL（任意）
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
