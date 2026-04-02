<?php

namespace App\Providers;

use App\Cache\NewsCache;
use App\Models\News;
use App\Observers\NewsObserver;
use App\Repositories\Categories\CategoryRepositoryInterface;
use App\Repositories\Categories\EloquentCategoryRepository;
use App\Repositories\Contacts\ContactRepositoryInterface;
use App\Repositories\Contacts\EloquentContactRepository;
use App\Repositories\FeedConfigs\EloquentFeedConfigRepository;
use App\Repositories\FeedConfigs\FeedConfigRepositoryInterface;
use App\Repositories\MarketInstruments\EloquentMarketInstrumentRepository;
use App\Repositories\MarketInstruments\MarketInstrumentRepositoryInterface;
use App\Repositories\News\EloquentNewsRepository;
use App\Repositories\News\NewsRepositoryInterface;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NewsRepositoryInterface::class, EloquentNewsRepository::class);
        $this->app->singleton(ContactRepositoryInterface::class, EloquentContactRepository::class);
        $this->app->singleton(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->singleton(MarketInstrumentRepositoryInterface::class, EloquentMarketInstrumentRepository::class);
        $this->app->singleton(FeedConfigRepositoryInterface::class, EloquentFeedConfigRepository::class);
        $this->app->singleton(NewsCache::class);
    }

    public function boot(): void
    {
        $forceHttps = $this->app->environment('production')
            || filter_var(env('FORCE_HTTPS', false), FILTER_VALIDATE_BOOLEAN);

        if ($forceHttps) {
            URL::forceScheme('https');
        }

        News::observe(NewsObserver::class);
    }
}
