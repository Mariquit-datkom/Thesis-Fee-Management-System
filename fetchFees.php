<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

function getFeeData() {
    $inputFileName = 'assets/docs/spreadsheets/fees.xlsx';
    $spreadsheet = IOFactory::load($inputFileName);
    $sheet = $spreadsheet->getActiveSheet();
    
    $names = $sheet->rangeToArray('A3:E3')[0];
    $amounts = $sheet->rangeToArray('A4:E4')[0];
    
    $fees = [];
    foreach ($names as $index => $name) {
        $fees[] = ['name' => $name, 'amount' => $amounts[$index]];
    }
    return $fees;
}
?>