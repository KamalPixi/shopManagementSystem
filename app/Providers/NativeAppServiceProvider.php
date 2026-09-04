<?php

namespace App\Providers;

use Native\Desktop\Facades\Window;
use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\Menu;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        Menu::create(
            Menu::app(),
            Menu::edit(),
        );

        Window::open()
        ->title('TECH PARISIEN')
        ->width(1024)
        ->height(768)
        ->showDevTools(false);

        // Auto-start Cloudflare tunnel if enabled in Shop Settings
        try {
            $tunnelService = app(\App\Services\CloudflareTunnelService::class);
            $config = $tunnelService->getConfig();
            if ($config['enabled'] && $config['auto_start'] && !empty($config['tunnel_token'])) {
                $tunnelService->start();
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Cloudflare tunnel auto-start encountered error: ' . $e->getMessage());
        }
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
        ];
    }
}

