<?php

namespace App\Providers;

use Native\Desktop\Facades\Window;
use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\Menu;
use Native\Desktop\Events\Windows\WindowClosed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

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
            Log::warning('Cloudflare tunnel auto-start encountered error: ' . $e->getMessage());
        }

        // When the main window is closed, kill ALL background processes so the
        // app can be reopened cleanly (especially important on Windows).
        Event::listen(WindowClosed::class, function () {
            $this->killAllBackgroundProcesses();
        });
    }

    /**
     * Kill all known background processes (cloudflared, any orphaned PHP workers, etc.)
     * This is critical on Windows where child processes are not automatically cleaned up.
     */
    protected function killAllBackgroundProcesses(): void
    {
        // 1. Stop the Cloudflare tunnel
        try {
            app(\App\Services\CloudflareTunnelService::class)->stop();
            Log::info('Cloudflare tunnel stopped on app quit.');
        } catch (\Throwable $e) {
            Log::warning('Error stopping Cloudflare tunnel on quit: ' . $e->getMessage());
        }

        // 2. On Windows: forcefully kill any remaining cloudflared.exe processes by name
        //    in case the PID file was stale or the process was orphaned.
        if (PHP_OS_FAMILY === 'Windows') {
            @shell_exec('taskkill /F /IM cloudflared.exe /T 2>NUL');
            Log::info('Forced kill of cloudflared.exe on Windows quit.');
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

