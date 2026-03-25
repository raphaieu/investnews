<?php

namespace App\Providers;

use App\Cache\NewsCache;
use App\Models\News;
use App\Observers\NewsObserver;
use App\Repositories\Contacts\ContactRepositoryInterface;
use App\Repositories\Contacts\EloquentContactRepository;
use App\Repositories\News\EloquentNewsRepository;
use App\Repositories\News\NewsRepositoryInterface;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(NewsRepositoryInterface::class, EloquentNewsRepository::class);
        $this->app->singleton(ContactRepositoryInterface::class, EloquentContactRepository::class);
        $this->app->singleton(NewsCache::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Evita mixed content: página em HTTPS mas @vite/asset() gerando http://
        $forceHttps = $this->app->environment('production')
            || filter_var(env('FORCE_HTTPS', false), FILTER_VALIDATE_BOOLEAN);

        if ($forceHttps) {
            URL::forceScheme('https');
        }

        News::observe(NewsObserver::class);
    }
}
