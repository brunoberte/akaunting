<?php

namespace App\Providers;

use App\Util;
use Illuminate\Support\ServiceProvider;
use Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Laravel db fix
        Schema::defaultStringLength(191);

        \Blade::directive('money', function($expression) {
            [$amount, $currency] = explode(', ', $expression);

            return "<?php echo \App\Util::money($amount, $currency); ?>";
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
