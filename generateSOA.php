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