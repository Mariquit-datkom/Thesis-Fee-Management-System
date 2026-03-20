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

            $studentEmail = $sheet->getCell("G$row")->getValue();
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
$date = date('jS \o\f F, Y');
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

    require_once 'mailHandler.php';

    if ($studentEmail) {
        $subject = "Official Receipt - Ref #" . $referenceNumber;
        
        $body = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: auto; border: 1px solid #eee; padding: 20px;'>
                <div style='text-align: center; border-bottom: 2px solid #004a99; padding-bottom: 10px;'>
                    <h2 style='color: #004a99; margin: 0;'>Colegio de Porta Vaga</h2>
                    <p style='font-size: 12px; color: #777;'>Finance Office - Official Notification</p>
                </div>

                <div style='padding: 20px 0;'>
                    <p>Dear <strong>$studentFullName</strong>,</p>
                    <p>This is to confirm that your payment has been successfully processed and recorded in the school's Fee Management System.</p>
                    
                    <div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <p style='margin: 5px 0;'><strong>Transaction Reference:</strong> $referenceNumber</p>
                        <p style='margin: 5px 0;'><strong>Date of Transaction:</strong> " . $date . "</p>
                        <p style='margin: 5px 0;'><strong>Document Type:</strong> Official Receipt</p>
                    </div>

                    <p>Please find your electronic receipt attached to this email for your personal records and future reference.</p>
                </div>

                <div style='font-size: 12px; color: #888; border-top: 1px solid #eee; padding-top: 20px;'>
                    <p><strong>Note:</strong> This is an automated message. If you did not authorize this transaction or believe this was sent in error, please visit the Finance Office immediately.</p>
                    <p><em>Confidentiality Notice: This email and any files transmitted with it are confidential and intended solely for the use of the individual to whom they are addressed.</em></p>
                </div>
            </div>
            ";
        
        sendEmailWithAttachment($studentEmail, $studentFullName, $subject, $body, $fullPath);
    }

    echo json_encode([
        'success' => true, 
        'message' => 'Payment recorded and receipt saved.',
        'path' => $fullPath
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'mPDF Error: ' . $e->getMessage()]);
}