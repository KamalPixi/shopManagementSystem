<?php

namespace App\Services;

use App\Models\Shop;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CloudflareTunnelService
{
    protected string $pidFile;
    protected string $logFile;

    public function __construct()
    {
        $this->pidFile = storage_path('app/cloudflare_tunnel.pid');
        $this->logFile = storage_path('logs/cloudflare_tunnel.log');
    }

    /**
     * Resolve the cloudflared binary executable path.
     */
    public function getBinaryPath(): ?string
    {
        $possiblePaths = [
            storage_path('app/bin/cloudflared'),
            storage_path('app/bin/cloudflared.exe'),
            '/opt/homebrew/bin/cloudflared',
            '/usr/local/bin/cloudflared',
            '/usr/bin/cloudflared',
        ];

        foreach ($possiblePaths as $path) {
            if (File::exists($path) && is_executable($path)) {
                return $path;
            }
        }

        // Try 'which cloudflared' on Unix or 'where cloudflared' on Windows
        $command = PHP_OS_FAMILY === 'Windows' ? 'where cloudflared' : 'which cloudflared 2>/dev/null';
        $output = trim((string) @shell_exec($command));
        if ($output && File::exists($output) && is_executable($output)) {
            return $output;
        }

        return null;
    }

    /**
     * Retrieve the current tunnel configuration from Shop settings.
     */
    public function getConfig(): array
    {
        $shop = Shop::first();
        $settings = $shop?->settings ?? [];
        $cf = $settings['cloudflare'] ?? [];

        return [
            'enabled' => (bool) ($cf['enabled'] ?? false),
            'auto_start' => (bool) ($cf['auto_start'] ?? false),
            'custom_domain' => trim($cf['custom_domain'] ?? ''),
            'tunnel_token' => trim($cf['tunnel_token'] ?? ''),
        ];
    }

    /**
     * Check if the tunnel process is currently running.
     */
    public function isRunning(): bool
    {
        $pid = $this->getPid();
        if (!$pid) {
            return false;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $output = @shell_exec("tasklist /FI \"PID eq $pid\" 2>NUL");
            return $output && str_contains($output, (string) $pid);
        }

        // Unix / macOS: kill -0 checks process existence without signaling
        if (function_exists('posix_kill')) {
            return (bool) @posix_kill($pid, 0);
        }

        $check = trim((string) @shell_exec("ps -p $pid -o pid="));
        return !empty($check);
    }

    /**
     * Get the stored PID if available.
     */
    public function getPid(): ?int
    {
        if (File::exists($this->pidFile)) {
            $pid = (int) trim(File::get($this->pidFile));
            if ($pid > 0) {
                return $pid;
            }
        }
        return null;
    }

    /**
     * Start the Cloudflare Tunnel.
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function start(): array
    {
        if ($this->isRunning()) {
            return [
                'success' => true,
                'message' => 'Tunnel is already running with PID ' . $this->getPid(),
            ];
        }

        $binary = $this->getBinaryPath();
        if (!$binary) {
            return [
                'success' => false,
                'message' => 'cloudflared binary not found. Please install cloudflared (e.g. brew install cloudflared) or place it in storage/app/bin/',
            ];
        }

        $config = $this->getConfig();
        $token = $config['tunnel_token'];

        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'No Cloudflare Tunnel token provided. Please enter your tunnel token in Shop Settings.',
            ];
        }

        // Ensure log directory exists
        if (!File::exists(dirname($this->logFile))) {
            File::makeDirectory(dirname($this->logFile), 0755, true);
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = sprintf(
                'start /B "" "%s" tunnel run --token "%s" > "%s" 2>&1',
                $binary,
                $token,
                $this->logFile
            );
            pclose(popen($cmd, 'r'));
        } else {
            $cmd = sprintf(
                '%s tunnel run --token %s >> %s 2>&1 & echo $!',
                escapeshellcmd($binary),
                escapeshellarg($token),
                escapeshellarg($this->logFile)
            );
            $pid = (int) trim((string) shell_exec($cmd));
            if ($pid > 0) {
                File::put($this->pidFile, (string) $pid);
            }
        }

        // Give process a moment to initialize
        usleep(400000);

        if ($this->isRunning()) {
            Log::info('Cloudflare tunnel started successfully.', ['pid' => $this->getPid()]);
            return [
                'success' => true,
                'message' => 'Cloudflare tunnel started successfully.',
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to start tunnel. Check storage/logs/cloudflare_tunnel.log for details.',
        ];
    }

    /**
     * Stop the Cloudflare Tunnel.
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function stop(): array
    {
        $pid = $this->getPid();

        if (!$pid && !$this->isRunning()) {
            if (File::exists($this->pidFile)) {
                File::delete($this->pidFile);
            }
            return [
                'success' => true,
                'message' => 'Tunnel is not running.',
            ];
        }

        if ($pid) {
            if (PHP_OS_FAMILY === 'Windows') {
                @shell_exec("taskkill /F /PID $pid 2>NUL");
            } else {
                if (function_exists('posix_kill')) {
                    @posix_kill($pid, SIGTERM);
                } else {
                    @shell_exec("kill -15 $pid 2>/dev/null");
                }
                usleep(300000);
                if ($this->isRunning()) {
                    if (function_exists('posix_kill')) {
                        @posix_kill($pid, SIGKILL);
                    } else {
                        @shell_exec("kill -9 $pid 2>/dev/null");
                    }
                }
            }
        }

        if (File::exists($this->pidFile)) {
            File::delete($this->pidFile);
        }

        Log::info('Cloudflare tunnel stopped.');

        return [
            'success' => true,
            'message' => 'Cloudflare tunnel stopped successfully.',
        ];
    }

    /**
     * Restart the tunnel.
     */
    public function restart(): array
    {
        $this->stop();
        usleep(300000);
        return $this->start();
    }

    /**
     * Get detailed status information.
     */
    public function getStatus(): array
    {
        $running = $this->isRunning();
        $pid = $this->getPid();
        $binary = $this->getBinaryPath();
        $config = $this->getConfig();

        $domain = $config['custom_domain'];
        $url = null;
        if (!empty($domain)) {
            $url = str_starts_with($domain, 'http') ? $domain : 'https://' . $domain;
        }

        $recentLog = '';
        if (File::exists($this->logFile)) {
            $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (!empty($lines)) {
                $recentLog = implode("\n", array_slice($lines, -5));
            }
        }

        return [
            'is_installed' => (bool) $binary,
            'binary_path' => $binary,
            'is_running' => $running,
            'pid' => $running ? $pid : null,
            'enabled' => $config['enabled'],
            'auto_start' => $config['auto_start'],
            'custom_domain' => $config['custom_domain'],
            'public_url' => $url,
            'has_token' => !empty($config['tunnel_token']),
            'recent_log' => $recentLog,
        ];
    }
}
