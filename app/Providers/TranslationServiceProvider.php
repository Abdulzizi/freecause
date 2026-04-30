<?php

namespace App\Providers;

use App\Services\TranslationService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class TranslationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TranslationService::class, function ($app) {
            return new TranslationService();
        });
    }

    public function boot(): void
    {
        // Register helper function
        if (!function_exists('trans_db')) {
            function trans_db(string $key, array $params = [], ?string $locale = null): string
            {
                $service = app(TranslationService::class);
                $locale = $locale ?? app()->getLocale();
                
                // Parse key: group.key or just key (default to 'common')
                $parts = explode('.', $key, 2);
                if (count($parts) === 2) {
                    $group = $parts[0];
                    $key = $parts[1];
                } else {
                    $group = 'common';
                }
                
                return $service->get($locale, $group, $key, $params);
            }
        }

        // Register Blade directive
        Blade::directive('transdb', function ($expression) {
            return "<?php echo trans_db($expression); ?>";
        });
    }
}
