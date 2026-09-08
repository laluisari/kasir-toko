<?php

return [
    'print_mode' => env('THERMAL_MODE', 'browser'),
    'device_path' => env('THERMAL_DEVICE_PATH', '/dev/cu.RPP02N'),
    'cups_queue' => env('THERMAL_CUPS_QUEUE', 'RPP02N-raw'),
    'store_name' => env('THERMAL_STORE_NAME', 'TOKO SERBAGUNA'),
    'store_address' => env('THERMAL_STORE_ADDRESS', 'Jl. Raya No. 123'),
    'line_width' => (int) env('THERMAL_LINE_WIDTH', 42),
    'auto_cut' => (bool) env('THERMAL_AUTO_CUT', true),
    'baud_rate' => (int) env('THERMAL_BAUD_RATE', 9600),
    'flow_control' => (bool) env('THERMAL_FLOW_CONTROL', false),
    'chunk_size' => (int) env('THERMAL_CHUNK_SIZE', 64),
    'chunk_delay_us' => (int) env('THERMAL_CHUNK_DELAY_US', 20000),
    'verify_channel' => (bool) env('THERMAL_VERIFY_CHANNEL', false),
    'verify_timeout' => (float) env('THERMAL_VERIFY_TIMEOUT', 1.5),
    'barcode_height' => (int) env('THERMAL_BARCODE_HEIGHT', 50),
    'barcode_width' => (int) env('THERMAL_BARCODE_WIDTH', 2),
    'barcode_copies' => (int) env('THERMAL_BARCODE_COPIES', 1),
];
