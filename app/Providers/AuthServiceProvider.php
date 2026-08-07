<?php

namespace App\Providers;

use App\Models\Book;
use App\Models\Review;
use App\Policies\BookPolicy;
use App\Policies\ReviewPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\ReadingPlan;
use App\Policies\ReadingPlanPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Book::class => BookPolicy::class,
        Review::class => ReviewPolicy::class,
        ReadingPlan::class => ReadingPlanPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
