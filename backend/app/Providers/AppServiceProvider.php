<?php

namespace App\Providers;

use App\Models\TutorDocument;
use App\Models\TutorProfile;
use App\Observers\TutorDocumentObserver;
use App\Observers\TutorProfileObserver;
use App\Services\BulkSmsBdService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(BulkSmsBdService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS on production
        if (!app()->environment('local', 'testing')) {
            URL::forceScheme('https');
        }

        TutorProfile::observe(TutorProfileObserver::class);
        TutorDocument::observe(TutorDocumentObserver::class);
    }
}
