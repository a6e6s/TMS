<?php

namespace App\Providers;

use App\Services\Pms\FlexFlowPmsConnector;
use App\Services\Pms\GenericHttpPmsConnector;
use App\Services\Pms\PmsConnectorInterface;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PmsConnectorInterface::class, fn () => match (strtolower((string) config('pms.default_provider'))) {
            'flexflow' => new FlexFlowPmsConnector,
            default => new GenericHttpPmsConnector,
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureRateLimiting();
    }

    /**
     * Ingestion is limited per source-app token, client endpoints per tracking token,
     * and public webhooks per IP address.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('ticket-ingestion', fn (Request $request) => Limit::perMinute(60)
            ->by('ingest:'.($request->user()?->getAuthIdentifier() ?? $request->ip())));

        RateLimiter::for('client-tracking', fn (Request $request) => [
            Limit::perMinute(30)->by('client-token:'.hash('sha256', (string) $request->header('X-Tracking-Token', ''))),
            Limit::perMinute(60)->by('client-ip:'.$request->ip()),
        ]);

        RateLimiter::for('public-webhooks', fn (Request $request) => Limit::perMinute(120)
            ->by('webhook:'.$request->ip()));
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
