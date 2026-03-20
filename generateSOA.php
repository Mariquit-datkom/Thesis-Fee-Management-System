<?php
// Disable error reporting from displaying as HTML to avoid polluting JSON
ini_set('display_errors', 0); 
error_reporting(E_ALL);

require 'vendor/autoload.php'; 
date_default_timezone_set('Asia/Manila');

use PhpOffice\PhpSpreadsheet\IOFactory;
use Mpdf\Mpdf;

$studentId = $_GET['id'] ?? null;

// Start buffering early to catch any accidental output from includes
ob_start();

try {
    if (!$studentId) {
        throw new Exception("Student ID is required.");
    }

    $infoFile = 'assets/docs/spreadsheets/student_info.xlsm';
    $recordFile = 'assets/docs/spreadsheets/student_record.xlsm';

    $name = "Unknown";
    $yearCourse = "N/A";

    if (file_exists($infoFile)) {
        $spreadsheet = IOFactory::load($infoFile);
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($sheet->toArray() as $index => $row) {
            if ($index < 3) continue;
            if ($row[0] == $studentId) {
                $name = strtoupper(($row[1] ?? '') . ", " . ($row[2] ?? '') . " " . ($row[3] ?? ''));
                $yearCourse = "Grade " . ($row[4] ?? '') . " - " . ($row[5] ?? '');
                $studentEmail = $row[6] ?? '';
                break;
            }
        }
    }

    // --- 2. Fetch Ledger/Balance Items Dynamically ---
    $soaItems = [];
    $total = 0;

    if (file_exists($recordFile)) {
        $spreadsheet = IOFactory::load($recordFile);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Identify the Header Row (Row 3 / Index 2 in your spreadsheet)
        // index 0 is Student ID, so we look at everything from index 1 onwards
        $headerRow = $rows[2] ?? []; 

        foreach ($rows as $index => $row) {
            // Skip headers (Rows 1-3) and empty rows
            if ($index < 3 || empty($row[0])) continue; 

            // Match the Student ID (Column A / Index 0)
            if ($row[0] == $studentId) {
                
                // Loop through each column starting from Column B (Index 1)
                foreach ($row as $colIndex => $value) {
                    if ($colIndex === 0) continue; // Skip the ID column
                    
                    $feeName = $headerRow[$colIndex] ?? "Unknown Fee";
                    $cleanValue = trim($value);

                    // Validation: Only add if not 'PAID', not empty, and is a number
                    if (strtoupper($cleanValue) !== 'PAID' && $cleanValue !== '' && is_numeric($cleanValue)) {
                        $amount = (float)$cleanValue;
                        $soaItems[] = [
                            'name'   => $feeName,
                            'amount' => $amount
                        ];
                        $total += $amount;
                    }
                }
                break; // Student found and processed; exit loop
            }
        }
    }
    // PDF Generation
    $date = date('F d, Y');
    $filename = "SOA-" . $studentId . "-" . date('Y-m-d') . ".pdf";
    $directory = 'assets/docs/soa/' . $studentId . '/';
    $savePath = $directory . $filename;

    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    // Capture the Template HTML
    // We do this inside a sub-buffer to keep it isolated
    ob_start();
    include 'soaTemplate.php'; 
    $html = ob_get_clean();

    $mpdf = new Mpdf();
    $mpdf->WriteHTML($html);
    $mpdf->Output($savePath, \Mpdf\Output\Destination::FILE);

    // CRITICAL: Clear the main buffer to remove any warnings/notices 
    // that might have leaked out during the spreadsheet loading
    ob_end_clean(); 

    require_once 'mailHandler.php';

    if (!empty($studentEmail)) {
        $subject = "Statement of Account - " . $studentId;
        $body = "
            <div style='font-family: Segoe UI, Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: auto; border: 1px solid #e0e0e0; border-top: 5px solid #d9534f; padding: 30px;'>
                <div style='text-align: center; margin-bottom: 20px;'>
                    <h2 style='color: #d9534f; margin: 0;'>Statement of Account</h2>
                    <p style='font-size: 14px; color: #666;'>Colegio de Porta Vaga - Finance Office</p>
                </div>

                <div style='margin-bottom: 25px;'>
                    <p>Dear <strong>$studentFullName</strong>,</p>
                    <p>This is a formal notification regarding your outstanding balance for the current school term. Please find your detailed <strong>Statement of Account (SOA)</strong> attached to this email.</p>
                </div>

                <div style='background-color: #fdf2f2; border-left: 4px solid #d9534f; padding: 15px; margin-bottom: 25px;'>
                    <p style='margin: 0; font-size: 15px;'><strong>Current Balance:</strong> Php " . number_format($total, 2) . "</p>
                    <p style='margin: 5px 0 0 0; font-size: 13px; color: #555;'>Date Generated: " . date('jS \o\f F, Y') . "</p>
                </div>

                <p style='font-size: 14px;'>To avoid any inconvenience during exams or enrollment periods, we kindly request that you settle the remaining balance at the Finance Office at your earliest convenience.</p>

                <div style='margin-top: 30px; padding-top: 15px; border-top: 1px solid #eee; font-size: 12px; color: #888;'>
                    <p><em>Note: This is an automated billing notice. If you have already made a payment within the last 24 hours, please disregard this message.</em></p>
                </div>
            </div>
            ";
        
        sendEmailWithAttachment($studentEmail, $name, $subject, $body, $savePath);
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => "SOA generated successfully.",
        'path' => $savePath
    ]);

} catch (Exception $e) {
    ob_end_clean(); // Clear buffer so only the error JSON is sent
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;