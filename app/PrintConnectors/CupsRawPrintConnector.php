<?php

/**
 * Connector CUPS yang mengirim byte apa adanya (ESC/POS) lewat `lp -o raw`.
 *
 * macOS modern tidak lagi mendukung queue `-m raw` (lpadmin menolak),
 * tapi opsi job `-o raw` masih melewati filter driver sehingga byte
 * sampai ke printer tanpa diubah menjadi PDL/PDF.
 */

namespace App\PrintConnectors;

use Exception;
use Mike42\Escpos\PrintConnectors\PrintConnector;

class CupsRawPrintConnector implements PrintConnector
{
    private array $buffer = [];

    private string $printerName;

    public function __construct(string $dest)
    {
        $valid = $this->getLocalPrinters();

        if (count($valid) === 0) {
            throw new Exception("Tidak ada printer CUPS. Periksa dengan 'lpstat -a'.");
        }

        if (!in_array($dest, $valid, true)) {
            throw new Exception("Queue printer '{$dest}' tidak ditemukan. Tersedia: [" . implode(', ', $valid) . ']');
        }

        $this->printerName = $dest;
    }

    public function __destruct()
    {
        if ($this->buffer !== []) {
            trigger_error('Print connector was not finalized. Did you forget to close the printer?', E_USER_NOTICE);
        }
    }

    public function write($data)
    {
        $this->buffer[] = $data;
    }

    public function read($len)
    {
        return false;
    }

    public function finalize()
    {
        $data = implode($this->buffer);
        $this->buffer = [];

        $tmpfname = tempnam(sys_get_temp_dir(), 'print-');
        file_put_contents($tmpfname, $data);

        $cmd = sprintf(
            "lp -d %s -o document-format=application/vnd.cups-raw %s",
            escapeshellarg($this->printerName),
            escapeshellarg($tmpfname)
        );

        try {
            $this->run($cmd);
        } catch (Exception $e) {
            unlink($tmpfname);
            throw $e;
        }

        unlink($tmpfname);
    }

    private function run(string $cmd): string
    {
        $descriptors = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($cmd, $descriptors, $fd);

        if (!is_resource($process)) {
            throw new Exception("Gagal menjalankan: {$cmd}");
        }

        $outputStr = stream_get_contents($fd[1]);
        fclose($fd[1]);
        $errorStr = stream_get_contents($fd[2]);
        fclose($fd[2]);
        $retval = proc_close($process);

        if ($retval !== 0) {
            throw new Exception("Command gagal: {$errorStr}");
        }

        return (string) $outputStr;
    }

    private function getLocalPrinters(): array
    {
        $outpStr = $this->run('lpstat -a');
        $printers = [];

        foreach (explode("\n", trim($outpStr)) as $line) {
            $pos = strpos($line, ' ');
            $printers[] = $pos === false ? $line : substr($line, 0, $pos);
        }

        return array_values(array_filter($printers, static fn (string $name) => $name !== ''));
    }
}