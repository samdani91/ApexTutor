<?php

namespace App\Providers;

use App\Models\TutorProfile;
use App\Observers\TutorProfileObserver;
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
        TutorProfile::observe(TutorProfileObserver::class);
    }
}
