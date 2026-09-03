<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Models\Shop;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Session::has('locale')) {
            $locale = Session::get('locale');
        } else {
            try {
                $shop = Shop::first();
                $locale = $shop?->settings['language'] ?? ((strcasecmp($shop?->country ?? '', 'France') === 0) ? 'fr' : 'en');
            } catch (\Throwable $e) {
                $locale = 'en';
            }
        }

        if (!in_array($locale, ['en', 'fr'])) {
            $locale = 'en';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
