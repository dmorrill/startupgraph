<?php

namespace App\Providers;

use App\Models\FundingRound;
use App\Models\HeadcountSnapshot;
use App\Observers\FundingRoundObserver;
use App\Observers\HeadcountSnapshotObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        FundingRound::observe(FundingRoundObserver::class);
        HeadcountSnapshot::observe(HeadcountSnapshotObserver::class);

        // Generous by design — agents are the native users. Keyed by token
        // user when authenticated, IP otherwise.
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(120)
            ->by($request->user()?->id ?: $request->ip()));
    }
}
