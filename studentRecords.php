<?php
require_once 'x-head.php'; 
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

$currentPage = basename($_SERVER['PHP_SELF']);

// --- Data Fetching Logic ---
$students = [];
$spreadsheetFile = 'assets/docs/spreadsheets/student_info.xlsm';

if (file_exists($spreadsheetFile)) {
    try {
        $spreadsheet = IOFactory::load($spreadsheetFile);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Start from index 3 (Row 4) as per handlePayment.php logic 
        foreach ($rows as $index => $row) {
            if ($index < 3 || empty($row[0])) continue; 

            $students[] = [
                'id'    => $row[0], // Column A
                'name'  => strtoupper(($row[1] ?? '') . ", " . ($row[2] ?? '') . " " . ($row[3] ?? '')), // Col B, C, D
                'year'  => $row[4] ?? 'N/A', // Column E
                'course'=> $row[5] ?? 'N/A'  // Column F
            ];
        }
    } catch (Exception $e) {
        $error = "Error loading student records: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Records</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/studentRecords.css">
</head>
<body>
    <div class="main-container">
        <?php include 'header.php' ?>
        
        <div class="content-container">
            <form method="post" autocomplete="off">
                <div class="search-row">                       
                    <input type="text" class="student-search-input" id="student-id" placeholder="Search..." oninput="filterTable()">
                    <button type="button" class="search-btn"><i class="fa fa-search"></i></button>
                </div>
            </form>    

            <div class="content-header">
                <span class="content-title">Student Information Registry</span>
            </div>

            <div class="scrollable-table">                
                <?php if (isset($error)): ?>
                    <p style="padding: 20px; color: red;"><?php echo $error; ?></p>
                <?php else: ?>
                    <table class="record-table" id="studentTable">
                        <thead>
                            <tr>
                                <th onclick="sortTable(0)">Student ID <i class="fa fa-sort sort-icon"></i></th>
                                <th onclick="sortTable(1)">Full Name <i class="fa fa-sort sort-icon"></i></th>
                                <th onclick="sortTable(2)">Year Level <i class="fa fa-sort sort-icon"></i></th>
                                <th onclick="sortTable(3)">Course/Strand <i class="fa fa-sort sort-icon"></i></th>
                                <th style="text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['id']); ?></td>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td>Grade <?php echo htmlspecialchars($student['year']); ?></td>
                                    <td><?php echo htmlspecialchars($student['course']); ?></td>
                                    <td style="text-align: center; vertical-align: middle">
                                        <button type="button" class="btn-generate-soa" data-id="<?php echo htmlspecialchars($student['id']); ?>">
                                        <i class="fa fa-book"></i> Generate SOA
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="js/sortStudentRecord.js"></script>
    <script src="js/generateSOA.js"></script>
</body>
</html>