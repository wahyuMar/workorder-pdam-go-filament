<?php

namespace App\Providers;

use App\Models\Complaint;
use App\Models\ComplaintFollowUp;
use App\Observers\ComplaintFollowUpObserver;
use App\Observers\ComplaintObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Complaint::observe(ComplaintObserver::class);
        ComplaintFollowUp::observe(ComplaintFollowUpObserver::class);
    }
}
