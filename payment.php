<?php
    require_once 'x-head.php'; 

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
                <span class="balance-display" id="balance-display">Php 0.00</span>
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

            </div>
            <div class="other-payment-container">
                
            </div>
        </div>
    </div>
</body>
</html>