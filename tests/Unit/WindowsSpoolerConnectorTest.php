<?php

namespace Tests\Unit;

use App\Printing\WindowsSpoolerConnector;
use PHPUnit\Framework\TestCase;

class WindowsSpoolerConnectorTest extends TestCase
{
    public function test_binary_job_and_exact_printer_name_are_submitted_once(): void
    {
        $jobs = [];
        $name = "Receipt (USB) / Café's 100%!";
        $connector = new WindowsSpoolerConnector($name, function ($printer, $bytes) use (&$jobs) {
            $jobs[] = [$printer, $bytes];
        });
        $connector->write("\x1b@Receipt\x00");
        $connector->write("\x1dV\x01");
        $this->assertSame([], $jobs);
        $connector->finalize();
        $connector->finalize();
        $this->assertSame([[$name, "\x1b@Receipt\x00\x1dV\x01"]], $jobs);
    }
}
