# BookShelf 書籍レビューアプリ

## 概要

本プロジェクトは、プログラミングスクールの模擬案件として作成した書籍レビューアプリです。

ユーザーは書籍の登録・閲覧・レビュー投稿・お気に入り登録などができ、評価ランキングやジャンル管理機能も備えています。

### 実装した主な機能

- 会員登録・ログイン・ログアウト（Laravel Fortify）
- 書籍のCRUD（登録・一覧・詳細・編集・削除）
- レビューの投稿・編集・削除・いいね
- お気に入り機能
- ジャンル管理
- 評価ランキング（平均評価順）
- 書籍API（一覧・詳細・登録・更新・削除）
- 日本語バリデーションメッセージ
- Feature / Unit テスト

---

## 使用技術

- PHP 8.2
- Laravel 10
- MySQL 8.0
- Docker / Laravel Sail
- Tailwind CSS 3
- Alpine.js
- Vite
- Laravel Fortify
- PHPUnit
- Laravel Pint

---

## 開発環境URL

- アプリケーション: http://localhost1/books
- phpMyAdmin: http://localhost:8080
- Vite開発サーバー: http://localhost:5173

---

## 環境構築手順

### 1. Laravelプロジェクトの作成（Laravel 10.x）

```bash
docker run --rm \
  -u "$$   (id -u):   $$(id -g)" \
  -v "$(pwd):/var/www/html" \
  -w /var/www/html \
  -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
  laravelsail/php82-composer:latest \
  composer create-project laravel/laravel:^10.0 bookshelf-app
```

### 2. Laravel Sailのインストール

```bash
cd bookshelf-app

docker run --rm \
  -u "$$   (id -u):   $$(id -g)" \
  -v "$(pwd):/var/www/html" \
  -w /var/www/html \
  -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
  laravelsail/php82-composer:latest \
  composer require laravel/sail --dev

docker run --rm \
  -u "$$   (id -u):   $$(id -g)" \
  -v "$(pwd):/var/www/html" \
  -w /var/www/html \
  -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
  laravelsail/php82-composer:latest \
  php artisan sail:install --with=mysql
  ```

### 3. .env ファイルの設定

```bash
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

### 4. フロントエンドのセットアップ

```bash
# コンテナ起動
./vendor/bin/sail up -d

# 依存パッケージのインストール
./vendor/bin/sail npm install
./vendor/bin/sail npm install alpinejs
./vendor/bin/sail npm install -D tailwindcss@^3.4.0 @tailwindcss/forms postcss autoprefixer

# Tailwind設定ファイルの生成
./vendor/bin/sail npx tailwindcss init -p
```
提供Bladeテンプレートを導入後、Viteを起動：

```bash
./vendor/bin/sail npm run dev
```

### 5. phpMyAdminの追加

```bash
phpmyadmin:
    image: 'phpmyadmin:latest'
    ports:
        - '${FORWARD_PHPMYADMIN_PORT:-8080}:80'
    environment:
        PMA_HOST: mysql
        PMA_USER: '${DB_USERNAME}'
        PMA_PASSWORD: '${DB_PASSWORD}'
    networks:
        - sail
    depends_on:
        - mysql
```

### 6. Sailの起動とエイリアス設定

```bash
./vendor/bin/sail up -d

# bashの場合
echo "alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'" >> ~/.bashrc
source ~/.bashrc
```

### 7. アプリケーションキーの生成

```bash
sail artisan migrate --seed
```

### 8. マイグレーションとシーダー

```bash
sail artisan migrate --seed
```

### 9. 日本語化

```bash
config/app.php の locale を ja に設定
lang/ja/ にメッセージファイルを手動配置
```

## ER図

<img width="835" height="720" alt="Screenshot 2026-07-25 154341" src="https://github.com/user-attachments/assets/9cb18d64-6ba2-4efc-88a3-9fdeb9797974" />


## APIエンドポイント一覧

GET  /api/v1/books  書籍一覧取得（ページネーション対応）  
GET  /api/v1/books/{book}  書籍詳細取得  
POST  /api/v1/books  書籍登録  
PUT  /api/v1/books/{book}  書籍更新  
DELETE  /api/v1/books/{book}  書籍削除 

## 作成者
谷内　流星
