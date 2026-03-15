<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$studentExists = false;

function getStudentFees($studentId) {
    $inputFileName = 'assets/docs/spreadsheets/student_record.xlsm';
    
    if (!file_exists($inputFileName)) {
        return [];
    }

    $spreadsheet = IOFactory::load($inputFileName);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    $headers = $rows[2];
    $studentFees = [];

    foreach ($rows as $index => $row) {
        if ($index === 0) continue; 

        if ($row[0] == $studentId) {

            for ($i = 1; $i < count($row); $i++) {

                $studentExists = true;

                if (isset($row[$i]) && $row[$i] !== "PAID") {
                    $studentFees[] = [
                        'name' => $headers[$i],
                        'amount' => (float)$row[$i]
                    ];
                }
            }
            break;
        }
    }
    return $studentFees;
}