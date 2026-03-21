<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Main entry point to get data. Checks timestamps to see if refresh is needed.
 */
function getCachedData($type) {
    $paths = [
        'fees' => [
            'excel' => 'assets/docs/spreadsheets/student_record.xlsm',
            'json'  => 'assets/docs/cache/student_record_cache.json'
        ],
        'info' => [
            'excel' => 'assets/docs/spreadsheets/student_info.xlsm',
            'json'  => 'assets/docs/cache/student_info_cache.json'
        ]
    ];

    $config = $paths[$type];
    if (!file_exists($config['excel'])) return [];

    $cacheFolder = dirname($config['json']);
    if (!is_dir($cacheFolder)) {
        // Creates the 'cache' folder with full permissions
        mkdir($cacheFolder, 0777, true);
    }

    // Sync if JSON is missing or Excel is newer
    if (!file_exists($config['json']) || filemtime($config['excel']) > filemtime($config['json'])) {
        if ($type === 'fees') refreshFeesCache($config['excel'], $config['json']);
        else refreshInfoCache($config['excel'], $config['json']);
    }

    return json_decode(file_get_contents($config['json']), true);
}

function refreshFeesCache($excelFile, $jsonFile) {
    $spreadsheet = IOFactory::load($excelFile);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();
    $headers = $rows[2]; //

    $data = [];
    foreach ($rows as $index => $row) {
        if ($index < 3 || empty($row[0])) continue; 
        $studentId = $row[0];
        $fees = [];
        for ($i = 1; $i < count($row); $i++) {
            if (isset($row[$i]) && strtoupper(trim($row[$i])) !== "PAID") {
                $fees[] = ['name' => $headers[$i], 'amount' => (float)$row[$i]];
            }
        }
        $data[$studentId] = $fees;
    }
    file_put_contents($jsonFile, json_encode($data));
}

function refreshInfoCache($excelFile, $jsonFile) {
    $spreadsheet = IOFactory::load($excelFile);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    $data = [];
    foreach ($rows as $index => $row) {
        if ($index < 3 || empty($row[0])) continue; // Skip headers
        
        $data[$row[0]] = [
            'id'    => $row[0],
            'name'  => strtoupper(($row[1] ?? '') . ", " . ($row[2] ?? '') . " " . ($row[3] ?? '')),
            'year'  => $row[4] ?? 'N/A',
            'course'=> $row[5] ?? 'N/A',
            'email' => $row[6] ?? ''
        ];
    }
    file_put_contents($jsonFile, json_encode($data));
}