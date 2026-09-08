<?php

namespace App\PrintConnectors;

use Mike42\Escpos\PrintConnectors\PrintConnector;
use RuntimeException;
use Throwable;

class BluetoothSerialPrintConnector implements PrintConnector
{
    protected $fp;

    protected string $device;

    protected int $baudRate;

    protected bool $flowControl;

    protected int $chunkSize;

    protected int $chunkDelayUs;

    public function __construct(
        string $device = '/dev/cu.RPP02N',
        int $baudRate = 9600,
        bool $flowControl = false,
        int $chunkSize = 64,
        int $chunkDelayUs = 20000
    ) {
        $this->device = $device;
        $this->baudRate = $baudRate;
        $this->flowControl = $flowControl;
        $this->chunkSize = max(1, $chunkSize);
        $this->chunkDelayUs = max(0, $chunkDelayUs);

        $this->configurePort();

        $this->fp = fopen($device, 'wb+');
        if ($this->fp === false) {
            throw new RuntimeException("Cannot open serial device {$device}.");
        }
    }

    protected function configurePort(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            if (!function_exists('shell_exec')) {
                return;
            }

            shell_exec(sprintf(
                'mode %s BAUD=%d PARITY=N DATA=8 STOP=1',
                escapeshellarg($this->device),
                $this->baudRate
            ));

            return;
        }

        if (!function_exists('shell_exec')) {
            return;
        }

        $flags = sprintf(
            '%d cs8 -parenb -cstopb clocal %s -ixon -ixoff -opost -raw',
            $this->baudRate,
            $this->flowControl ? 'crtscts' : '-crtscts'
        );

        $result = shell_exec(sprintf(
            'stty -f %s %s 2>&1',
            escapeshellarg($this->device),
            $flags
        ));
    }

    public function __destruct()
    {
        if ($this->fp !== false) {
            trigger_error('Print connector was not finalized. Did you forget to close the printer?', E_USER_NOTICE);
        }
    }

    public function finalize()
    {
        if ($this->fp !== false) {
            usleep(100000);
            fclose($this->fp);
            $this->fp = false;
        }
    }

    public function read($len)
    {
        if ($this->fp === false) {
            throw new RuntimeException('PrintConnector has been closed, cannot read input.');
        }

        return fread($this->fp, $len);
    }

    public function write($data)
    {
        if ($this->fp === false) {
            throw new RuntimeException('PrintConnector has been closed, cannot send output.');
        }

        $total = strlen($data);
        $offset = 0;

        while ($offset < $total) {
            $chunk = substr($data, $offset, $this->chunkSize);
            $written = fwrite($this->fp, $chunk);

            if ($written === false || $written === 0) {
                throw new RuntimeException('Serial write failed or timed out.');
            }

            $offset += $written;

            if ($this->chunkDelayUs > 0 && $offset < $total) {
                usleep($this->chunkDelayUs);
            }
        }
    }

    public static function probeChannel(string $device, float $timeoutSec = 2.0): array
    {
        $fp = @fopen($device, 'r+b');
        if ($fp === false) {
            return ['alive' => false, 'reason' => 'open-failed'];
        }

        if (function_exists('stream_set_blocking')) {
            stream_set_blocking($fp, false);
        }

        fwrite($fp, "\x1d\x72\x01");
        fflush($fp);

        $response = '';
        $deadline = microtime(true) + $timeoutSec;

        while (microtime(true) < $deadline) {
            $c = fread($fp, 32);
            if ($c !== false && $c !== '') {
                $response .= $c;
            } else {
                usleep(30000);
            }
        }

        fclose($fp);

        if ($response === '') {
            return ['alive' => false, 'reason' => 'no-status-response'];
        }

        return [
            'alive' => true,
            'reason' => 'ok',
            'bytes' => strtoupper(bin2hex($response)),
        ];
    }

    public static function isBluetoothSerial(string $device): bool
    {
        return str_contains(strtolower($device), 'cu.') || str_contains(strtolower($device), 'tty.');
    }
}