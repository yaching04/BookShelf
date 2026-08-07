<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ReadingPlan $readingPlan,
        public string $timing // three_days_before | on_due_date | three_days_after
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $bookTitle = $this->readingPlan->book->title;
        $targetDate = $this->readingPlan->target_date->format('Y-m-d');

        [$title, $body] = match ($this->timing) {
            'three_days_before' => [
                '読書期日が近づいています',
                "『{$bookTitle}』の期日（{$targetDate}）まであと3日です。",
            ],
            'on_due_date' => [
                '本日が読書期日です',
                "『{$bookTitle}』の期日は本日（{$targetDate}）です。",
            ],
            'three_days_after' => [
                '読書期日を過ぎています',
                "『{$bookTitle}』の期日（{$targetDate}）から3日が経過しました。",
            ],
            default => [
                '読書計画の通知',
                "『{$bookTitle}』の通知です。",
            ],
        };

        return [
            'title'           => $title,
            'body'            => $body,
            'timing'          => $this->timing,
            'reading_plan_id' => $this->readingPlan->id,
            'book_id'         => $this->readingPlan->book_id,
            'target_date'     => $targetDate,
        ];
    }
}
