<?php
require 'vendor/autoload.php'; 

date_default_timezone_set('Asia/Manila');

use PhpOffice\PhpSpreadsheet\IOFactory;
use Mpdf\Mpdf;

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data.']);
    exit;
}

$studentId = $data['studentId'];
$totalAmount = $data['totalAmount'];
$items = $data['items']; // Contains the fees being paid
$dateOfPayment = date('Y-m-d');

$spreadsheetFile = 'assets/docs/spreadsheets/student_info.xlsm';
$studentFullName = "Unknown Student";
$yearStrand = "N/A";

try {
    // 1. Load the spreadsheet to read student info and update fees
    $spreadsheet = IOFactory::load($spreadsheetFile);
    $sheet = $spreadsheet->getActiveSheet();
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    $studentRowIndex = -1;

    // Find the student row (starting at Row 4 per screenshot)
    for ($row = 4; $row <= $highestRow; $row++) {
        $currentId = trim($sheet->getCell("A$row")->getValue());
        if ($currentId == $studentId) {
            $studentRowIndex = $row;
            
            // Collect Student Info for Receipt
            $lastName = $sheet->getCell("B$row")->getValue();
            $firstName = $sheet->getCell("C$row")->getValue();
            $middleName = $sheet->getCell("D$row")->getValue();
            $studentFullName = strtoupper("$lastName, $firstName $middleName");

            $year = $sheet->getCell("E$row")->getValue();
            $strand = $sheet->getCell("F$row")->getValue();
            $yearStrand = is_null($strand) ? "Grade $year" : "Grade $year - $strand";
            break;
        }
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Spreadsheet Error: ' . $e->getMessage()]);
    exit;
}

if ($studentRowIndex === -1) {
    echo json_encode(['success' => false, 'message' => 'Student ID not found in records.']);
    exit;
}

$spreadsheetFile = 'assets/docs/spreadsheets/student_record.xlsm';
try {
    $spreadsheet = IOFactory::load($spreadsheetFile);
    $sheet = $spreadsheet->getActiveSheet();
    $highestColumn = $sheet->getHighestColumn();

    foreach ($items as $paidItem) {
        $feeName = trim($paidItem['name']);
        $paymentAmount = floatval($paidItem['amount']);
        $isFull = $paidItem['isFull'] ?? false;
        
        for ($col = 'A'; $col <= $highestColumn; $col++) {
            $headerValue = trim($sheet->getCell($col . "3")->getValue());
            
            if ($headerValue == $feeName) {
                $cellCoordinate = $col . $studentRowIndex;
                
                if ($isFull) {
                    // Option A: If they checked 'Full Payment', just mark PAID
                    $sheet->setCellValue($cellCoordinate, "PAID");
                } else {
                    // Option B: Partial Payment subtraction
                    $currentValue = $sheet->getCell($cellCoordinate)->getValue();
                    
                    // If the cell is currently "PAID" or empty, treat as 0, otherwise subtract
                    $currentBalance = is_numeric($currentValue) ? floatval($currentValue) : 0;
                    $newBalance = $currentBalance - $paymentAmount;

                    if ($newBalance <= 0) {
                        $sheet->setCellValue($cellCoordinate, "PAID");
                    } else {
                        $sheet->setCellValue($cellCoordinate, round($newBalance, 2));
                    }
                }
            }    
        }
    }

    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($spreadsheetFile);        
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Spreadsheet Error: ' . $e->getMessage()]);
    exit;
}

// --- 3. Prepare Receipt Rows (Ensuring at least 3 rows) ---
$displayItems = $items;
while (count($displayItems) < 3) {
    $displayItems[] = ['name' => '&nbsp;', 'amount' => '&nbsp;'];
}

// --- 4. Capture and Generate PDF using mPDF ---
ob_start();
$name = $studentFullName;
$yearCourse = $yearStrand;
$date = $dateOfPayment;
$receiptItems = $displayItems;
$total = $totalAmount;
$referenceNumber = date('YmdHis');
include 'receiptTemplate.php';
$html = ob_get_clean();

try {
    $mpdf = new Mpdf(['format' => 'A5']);
    $mpdf->WriteHTML($html);

    $savePath = "assets/docs/receipts/" . $studentId . "/";
    if (!is_dir($savePath)) mkdir($savePath, 0777, true);

    $fileName = "{$studentId}-{$referenceNumber}.pdf";
    $fullPath = $savePath . $fileName;
    $mpdf->Output($fullPath, \Mpdf\Output\Destination::FILE);

    echo json_encode([
        'success' => true, 
        'message' => 'Payment recorded and receipt saved.',
        'path' => $fullPath
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'mPDF Error: ' . $e->getMessage()]);
}