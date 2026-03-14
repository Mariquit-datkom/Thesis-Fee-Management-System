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
    <title>Dashboard - Admin</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/adminDashboard.css">
</head>
<body>
    <div class="main-container">
        <?php include 'header.php' ?>
        <div class="content-container">

        </div>
    </div>
</body>
</html>