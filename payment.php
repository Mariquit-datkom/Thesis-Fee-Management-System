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
        $result = getStudentFees($studentId);
        $fees = $result['fees'];
        $studentExists = $result['exists'];
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
                            <th>Fee Name</th>
                            <th>Amount</th>
                            <th>Full Payment</th>
                            <th>Partial Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($searchPerformed && !empty($fees)): ?>
                            <?php foreach ($fees as $fee): ?>
                                <tr class="fee-row">                                                                 
                                    <td><?php echo htmlspecialchars($fee['name']); ?></td>
                                    <td>Php <?php echo number_format($fee['amount'], 2); ?></td>
                                    <td><input type="checkbox" class="full-pay-checkbox" data-price="<?php echo $fee['amount']; ?>"></td>
                                    <td><input type="number" class="partial-amount-input" placeholder="0.00" step="1" min="0" oninput="partialPaymentLimiter(this) ,calculateGrandTotal()"></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php elseif ($searchPerformed): ?>
                            <?php if ($studentExists): ?>
                                <tr><td colspan="4">No unpaid fees found for this ID.</td></tr>
                            <?php else: ?>
                                <tr><td colspan="4">Student record for this ID does not exist.</td></tr>
                            <?php endif ?>
                        <?php else: ?>
                            <tr><td colspan="4">Please enter a Student ID to view fees.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="other-payment-container">
                <div class="content-header">
                    <span class="content-title">Other payments:</span>
                    <button type="button" class="btn btn-add-row" id="add-payment-row" style="background-color: #3c8fe3; padding: 5px 10px; margin-left: 10px;">+ Add Item</button>
                </div>
                <table id="other-payments-table">
                    <thead>
                        <tr>
                            <th>Payment For</th> <th>Amount (Php)</th> <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="other-payments-body">
                        </tbody>
                </table>
            </div>
            <div class="payment-btn-container">
                <input type="button" value="Pay" class="pay-btn" onclick="processPayment()">
            </div>
        </div>
    </div>

    <script src="js/validateStudentId.js"></script>
    <script src="js/resetPaymentPage.js"></script>
    <script src="js/otherPayments.js"></script>
    <script src="js/processPayment.js"></script>
</body>
</html>