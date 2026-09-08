<?php
/**
 * Debug script untuk test thermal printer RPP02N.
 * Run: php test-printer-debug.php
 */

require 'vendor/autoload.php';

use App\PrintConnectors\BluetoothSerialPrintConnector;
use Mike42\Escpos\Printer;

$devicePath = getenv('THERMAL_DEVICE_PATH') ?: '/dev/cu.RPP02N';
$baud = (int) (getenv('THERMAL_BAUD_RATE') ?: 115200);
$flow = (bool) (getenv('THERMAL_FLOW_CONTROL') ?: false);

function out(string $label, string $status): void
{
    echo sprintf("%-42s : %s\n", $label, $status);
}

echo "=== Thermal Printer Debug Test ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "OS: " . PHP_OS_FAMILY . " (" . php_uname('s') . ' ' . php_uname('r') . ")\n";
echo "PHP: " . PHP_VERSION . "\n";
echo "Device: {$devicePath} (baud={$baud}, flow=" . ($flow ? 'on' : 'off') . ")\n";
echo "\n";

echo "--- 1. Device existence & permissions ---\n";
out('file_exists', file_exists($devicePath) ? 'YES' : 'NO');
out('is_readable', is_readable($devicePath) ? 'YES' : 'NO');
out('is_writable', is_writable($devicePath) ? 'YES' : 'NO');
out('device type', BluetoothSerialPrintConnector::isBluetoothSerial($devicePath) ? 'bluetooth-serial' : 'plain-file');
echo "\n";

echo "--- 2. Data channel liveness (ESC/POS status readback) ---\n";
$probe = BluetoothSerialPrintConnector::probeChannel($devicePath, 2.0);
if ($probe['alive']) {
    out('GS r status', 'ALIVE (response: ' . ($probe['bytes'] ?? '') . ')');
} else {
    out('GS r status', 'DEAD (' . $probe['reason'] . ')');
}
echo "  => IF DEAD: data yang dikirim ke {$devicePath} tidak pernah sampai ke printer.\n";
echo "     Printer bluetooth ini kemungkinan tidak mendukung SPP dari macOS.\n";
echo "\n";

echo "--- 3. Send actual print job (chunked, stty-configured) ---\n";
try {
    $connector = new BluetoothSerialPrintConnector($devicePath, $baud, $flow);
    $printer = new Printer($connector);
    $printer->initialize();
    $printer->text("=== DEBUG PRINT ===\n");
    $printer->text("chunked + delayed\n\n\n");
    $printer->close();

    out('print job', 'SENT (did it physically print?)');
} catch (Throwable $e) {
    out('print job', 'FAILED: ' . $e->getMessage());
}

echo "\n";
echo "If channel in step 2 is DEAD, printing will never work on this macOS no matter the code.\n";
echo "Kan check: unpair & re-pair the printer, then re-run this script.\n";