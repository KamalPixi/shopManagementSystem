<?php

namespace App\Printing;

use Closure;
use Mike42\Escpos\PrintConnectors\PrintConnector;
use RuntimeException;
use Symfony\Component\Process\Process;

/** Sends ESC/POS bytes to an installed Windows queue, without printer sharing. */
final class WindowsSpoolerConnector implements PrintConnector
{
    private ?string $buffer = '';

    public function __construct(private string $printerName, private ?Closure $submit = null) {}

    public function __destruct() {}

    public function read($len)
    {
        return false;
    }

    public function write($data)
    {
        if ($this->buffer === null) {
            throw new RuntimeException('The print job is already closed.');
        }
        $this->buffer .= $data;
    }

    public function finalize()
    {
        if ($this->buffer === null) {
            return;
        }
        $data = $this->buffer;
        $this->buffer = null;
        if ($this->submit) {
            ($this->submit)($this->printerName, $data);
            return;
        }

        $path = tempnam(sys_get_temp_dir(), 'receipt-');
        if ($path === false) {
            throw new RuntimeException('Could not create the print job file.');
        }
        try {
            if (file_put_contents($path, $data) !== strlen($data)) {
                throw new RuntimeException('Could not write the print job file.');
            }
            $process = new Process([
                'powershell.exe', '-NoProfile', '-NonInteractive', '-ExecutionPolicy', 'Bypass',
                '-File', resource_path('printing/windows-raw-print.ps1'),
                base64_encode($this->printerName), base64_encode($path),
            ]);
            $process->setTimeout(30);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new RuntimeException(trim($process->getErrorOutput() ?: $process->getOutput()) ?: 'Windows rejected the print job.');
            }
        } finally {
            @unlink($path);
        }
    }
}
