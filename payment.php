<?php
    require_once 'x-head.php'; 
    require_once 'fetchFees.php'; 

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");

    /*if (!isset($_SESSION['username'])) {
        header("Location: logIn.php");
        exit();
    } */

    $currentPage = basename($_SERVER['PHP_SELF']);
    $fees = [];
    $searchPerformed = false;

    // Handle the Search Form Submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['student-search-input'])) {
        $studentId = $_POST['student-search-input'];
        $fees = getStudentFees($studentId);
        $searchPerformed = true;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/payment.css">
</head>
<body>
    <div class="main-container">
        <?php include 'header.php' ?>
        <div class="content-container">
            <div class="balance-container">
                Total: Php <span class="balance-display" id="balance-display">0.00</span>
            </div>
            <form method="post" autocomplete="off">
                <div class="search-row">
                    <div class="student-search-container">
                        <label for="student-search-input" class="student-search-label">Student ID:</label>
                        <input 
                            type="text" 
                            name="student-search-input" 
                            id="student-id" 
                            placeholder="e.g. 2026-0000"
                            maxlength="8"
                            pattern="[0-9]{4}-[0-9]{3}" 
                            title="Only numbers and one hyphen are allowed (e.g., 2026-0000)"
                            oninput="validateStudentId(this)"
                            value="<?php echo isset($studentId) ? htmlspecialchars($studentId) : ''; ?>" 
                            class="student-search-input" 
                            required>
                    </div>
                    <div class="btn-container">
                        <input type="submit" value="Search" class="btn btn-submit">
                        <input type="button" value="Reset" class="btn btn-reset" onclick="resetPaymentPage()">
                    </div>
                </div>
            </form>
            <div class="fee-breakdown-container">
                <div class="content-header"><span class="content-title">Fees to be paid:</span></div>
                <table id="fee-table">
                    <thead>
                        <tr>
                            <th>Pay</th>
                            <th>Fee Name</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($searchPerformed && !empty($fees)): ?>
                            <?php foreach ($fees as $fee): ?>
                                <tr>                                                                 
                                    <td><?php echo htmlspecialchars($fee['name']); ?></td>
                                    <td>Php <?php echo number_format($fee['amount'], 2); ?></td>
                                    <td><input type="checkbox" class="fee-checkbox" data-price="<?php echo $fee['amount']; ?>"></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php elseif ($searchPerformed): ?>
                            <?php if ($studentExists): ?>
                                <tr><td colspan="3">No unpaid fees found for this ID.</td></tr>
                            <?php else: ?>
                                <tr><td colspan="3">Student record for this ID does not exist.</td></tr>
                            <?php endif ?>
                        <?php else: ?>
                            <tr><td colspan="3">Please enter a Student ID to view fees.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="other-payment-container">
                <div class="content-header"><span class="content-title">Other payments:</span></div>

            </div>
            <div class="payment-btn-container">

            </div>
        </div>
    </div>

    <script src="js/updateBalance.js"></script>
    <script src="js/validateStudentId.js"></script>
    <script src="js/resetPaymentPage.js"></script>
</body>
</html>