<?php

namespace App\Console\Commands;

use App\Services\CloudflareTunnelService;
use Illuminate\Console\Command;

class TunnelCommand extends Command
{
    protected $signature = 'tunnel {action=status : Action to perform (start, stop, restart, status)}';
    protected $description = 'Manage Cloudflare Tunnel remote access';

    public function handle(CloudflareTunnelService $tunnelService): int
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'start':
                $this->info('Starting Cloudflare tunnel...');
                $result = $tunnelService->start();
                if ($result['success']) {
                    $this->info($result['message']);
                    return 0;
                }
                $this->error($result['message']);
                return 1;

            case 'stop':
                $this->info('Stopping Cloudflare tunnel...');
                $result = $tunnelService->stop();
                $this->info($result['message']);
                return 0;

            case 'restart':
                $this->info('Restarting Cloudflare tunnel...');
                $result = $tunnelService->restart();
                if ($result['success']) {
                    $this->info($result['message']);
                    return 0;
                }
                $this->error($result['message']);
                return 1;

            case 'status':
            default:
                $status = $tunnelService->getStatus();
                $this->table(
                    ['Key', 'Value'],
                    [
                        ['Binary Installed', $status['is_installed'] ? 'Yes (' . $status['binary_path'] . ')' : 'No'],
                        ['Tunnel Running', $status['is_running'] ? 'YES (PID: ' . $status['pid'] . ')' : 'NO'],
                        ['Enabled in Settings', $status['enabled'] ? 'Yes' : 'No'],
                        ['Auto-start on Boot', $status['auto_start'] ? 'Yes' : 'No'],
                        ['Custom Domain', $status['custom_domain'] ?: '(Not configured)'],
                        ['Public URL', $status['public_url'] ?: '(None)'],
                        ['Has Token', $status['has_token'] ? 'Yes' : 'No'],
                    ]
                );
                return 0;
        }
    }
}
