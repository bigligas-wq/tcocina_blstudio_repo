<?php

namespace App\Providers;

use App\Models\BusinessSetting;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
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
        // Cargar configuraciones de negocio globalmente en todas las vistas
        // Cacheado 5 minutos; se invalida automáticamente en BusinessSetting::set()
        try {
            $settings = Cache::remember('business_settings_all', 300, function () {
                return BusinessSetting::withoutGlobalScopes()->get();
            });
            
            // Decodificar valores según su tipo (especialmente JSON)
            $businessSettings = [];
            foreach ($settings as $setting) {
                $value = BusinessSetting::castValue($setting->value, $setting->type);
                
                // Si el tipo es JSON y después de decodificar sigue siendo un string (doble encoding), decodificar nuevamente
                if ($setting->type === 'json' && is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (is_array($decoded) || is_object($decoded)) {
                        $value = $decoded;
                    }
                }
                
                $businessSettings[$setting->key] = $value;
            }

            View::share('businessSettings', $businessSettings);
            // siteOffline quick helper for layout overlay
            View::share('siteOffline', (bool)($businessSettings['site_offline'] ?? false));
            // loyaltyOffline: ocultar botón Google y álbum cuando está apagado
            View::share('loyaltyOffline', (bool)($businessSettings['loyalty_offline'] ?? false));
        } catch (\Exception $e) {
            // Si la tabla no existe aún o hay error, usar array vacío
            View::share('businessSettings', []);
            View::share('siteOffline', false);
            View::share('loyaltyOffline', false);
        }

        // Cuando el sitio está offline, mostrar el pedido activo del usuario en el banner
        // Se ejecuta al renderizar la vista (auth() disponible en este punto)
        View::composer('layouts.app', function ($view) {
            $siteOffline = View::shared('siteOffline') ?? false;
            $bannerActiveOrder = null;
            if ($siteOffline && auth()->check()) {
                try {
                    $bannerActiveOrder = Order::where('user_id', auth()->id())
                        ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready', 'on_the_way'])
                        ->whereDate('created_at', today())
                        ->latest()
                        ->first();
                } catch (\Exception $e) {
                    // silenciar si la tabla no existe
                }
            }
            $view->with('bannerActiveOrder', $bannerActiveOrder);
        });
    }
}
