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
                Php <span class="balance-display" id="balance-display">0.00</span>
            </div>
            <form action="studentSearchAuth.php" method="post" autocomplete="off">
                <div class="search-row">
                    <div class="student-search-container">
                        <label for="student-search-input" class="student-search-label">Student ID:</label>
                        <input type="text" name="student-search-input" id="student-id" class="student-search-input">
                    </div>
                    <div class="btn-container">
                        <input type="submit" value="Search" class="btn btn-submit">
                        <input type="reset" value="Reset" class="btn btn-reset">
                    </div>
                </div>
            </form>
            <div class="fee-breakdown-container">
                <div class="content-header"><span class="content-title">Remaining Fees:</span></div>
                <table id="fee-table">
                    <thead>
                        <tr><th>Pay</th><th>Fee Name</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php 
                        $fees = getFeeData();
                        foreach ($fees as $fee): ?>
                            <tr>                                
                                <td><?php echo $fee['name']; ?></td>
                                <td>Php <?php echo number_format($fee['amount'], 2); ?></td>
                                <td><input type="checkbox" class="fee-checkbox" data-price="<?php echo $fee['amount']; ?>"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="other-payment-container">

            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.fee-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                let total = 0;
                document.querySelectorAll('.fee-checkbox:checked').forEach(checkedBox => {
                    total += parseFloat(checkedBox.getAttribute('data-price'));
                });
                document.getElementById('balance-display').innerText = total.toFixed(2);
            });
        });
    </script>
</body>
</html>