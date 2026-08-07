<?php

namespace Database\Seeders;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReadingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        if ($users->isEmpty() || $books->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            $selectedBooks = $books->random(min(3, $books->count()));

            foreach ($selectedBooks as $index => $book) {
                $exists = ReadingPlan::where('user_id', $user->id)
                    ->where('book_id', $book->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                // 0: 予定中 / 1: 読了 / 2: 期限切れ
                $status = match ($index % 3) {
                    0 => ReadingPlanStatus::Planned,
                    1 => ReadingPlanStatus::Completed,
                    2 => ReadingPlanStatus::Expired,
                };

                $targetDate = match ($status) {
                    ReadingPlanStatus::Planned   => now()->addDays(rand(7, 45))->toDateString(),
                    ReadingPlanStatus::Completed => now()->subDays(rand(5, 30))->toDateString(),
                    ReadingPlanStatus::Expired   => now()->subDays(rand(1, 14))->toDateString(),
                };

                ReadingPlan::create([
                    'user_id'      => $user->id,
                    'book_id'      => $book->id,
                    'target_date'  => $targetDate,
                    'status'       => $status->value,
                    'completed_at' => $status === ReadingPlanStatus::Completed
                        ? now()->subDays(rand(1, 10))
                        : null,
                ]);
            }
        }
    }
}
