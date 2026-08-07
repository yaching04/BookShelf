<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessReadingPlans extends Command
{
    protected $signature = 'reading-plans:process';

    protected $description = '読書計画の期限切れ更新とリマインダー通知を処理する';

    public function handle(): int
    {
        $today = Carbon::today();

        // 1. planned かつ 期日超過 → expired に更新
        $expiredCount = ReadingPlan::query()
            ->where('status', ReadingPlanStatus::Planned)
            ->whereDate('target_date', '<', $today)
            ->update(['status' => ReadingPlanStatus::Expired->value]);

        $this->info("期限切れに更新: {$expiredCount} 件");

        // 2. リマインダー通知
        $this->sendReminders($today->copy()->addDays(3), 'three_days_before');
        $this->sendReminders($today, 'on_due_date');
        $this->sendReminders($today->copy()->subDays(3), 'three_days_after');

        $this->info('処理が完了しました。');

        return self::SUCCESS;
    }

    private function sendReminders(Carbon $targetDate, string $timing): void
    {
        $plans = ReadingPlan::query()
            ->with(['user', 'book'])
            ->whereIn('status', [
                ReadingPlanStatus::Planned->value,
                ReadingPlanStatus::Expired->value,
            ])
            ->whereDate('target_date', $targetDate)
            ->get();

        $count = 0;

        foreach ($plans as $plan) {
            // 同じ timing の通知を既に送っていないか確認（簡易）
            $alreadySent = $plan->user->notifications()
                ->where('type', ReadingPlanReminderNotification::class)
                ->where('data->reading_plan_id', $plan->id)
                ->where('data->timing', $timing)
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $plan->user->notify(new ReadingPlanReminderNotification($plan, $timing));
            $count++;
        }

        $this->info("通知送信 ({$timing}): {$count} 件");
    }
}
