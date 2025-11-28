<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AI\AIProviderInterface;
use App\Services\AI\OpenAIProvider;
use App\Services\AI\LocalVisionProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
                // Register AI Provider based on configuration
        $this->app->bind(AIProviderInterface::class, function ($app) {
            $provider = config('ai.provider', 'openai');
            
            return match ($provider) {
                'openai' => new OpenAIProvider(),
                'local' => new LocalVisionProvider(),
                default => new OpenAIProvider(),
            };
        });

        // Register AI services as singletons
        $this->app->singleton(\App\Services\AI\PlantAnomalyDetectionService::class);
        $this->app->singleton(\App\Services\AI\PlantClassificationService::class);
        $this->app->singleton(\App\Services\AI\RetrieverService::class);
        $this->app->singleton(\App\Services\AI\CultivationChatbotService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
