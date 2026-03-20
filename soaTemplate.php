<?php
// Note: $name, $yearCourse, $date, $receiptItems, and $total 
// are provided by the include context in handlePayment.php
/*
$name = '';
$yearCourse = '';
$date = '';
$soaItems = [];
$total = 0; */

date_default_timezone_set('Asia/Manila');
?>

<!DOCTYPE html>
<html>
<head>
<style>
    table {
        width: 100%;
        max-width: 800px;
        border-collapse: collapse;
        font-family: Arial, sans-serif;
    }
    th, td {
        padding: 10px;
        text-align: left;
    }
    .center {
        text-align: center;
    }
    .right {
        text-align: right;
    }
    .bold {
        font-weight: bold;
    }
    .header-title {
        font-size: 1.6em;
        text-transform: uppercase;
    }
</style>
</head>
<body>

<table style="border: 1px solid black; width: 100%; max-width: 800px; border-collapse: collapse; font-family: Arial, sans-serif;">
    <tr>
        <td colspan="2" class="center" style="border-bottom: 1px solid black; padding: 10px;">
            <img src="assets/images/school_logo.png" style="vertical-align: middle; height: 100px; width: auto;">
            <span class="header-title bold"> Colegio de Porta Vaga </span>
        </td>
    </tr>

    <tr>
        <td colspan="2" class="center bold" style="font-size: 1.2em; padding: 15px; border-bottom: 1px solid black;">
            STATEMENT OF ACCOUNT
        </td>
    </tr>

    <tr>
        <td colspan="2" style="border-bottom: 1px solid black; padding: 10px;">
            <span class="bold">Name:</span> <?php echo htmlspecialchars($name); ?>
        </td>
    </tr>

    <tr>
        <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 10px; width: 50%;">
            <span class="bold">Year & Course:</span> <?php echo htmlspecialchars($yearCourse); ?>
        </td>
        <td style="border-bottom: 1px solid black; padding: 10px; width: 50%;">
            <span class="bold">Balance as of:</span> <?php echo htmlspecialchars($date); ?>
        </td>
    </tr>

    <tr>
        <th class="center" style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 10px;"></th>
        <th class="center" style="border-bottom: 1px solid black; padding: 10px;">Amount:</th>
    </tr>
    
    <?php 
    foreach ($soaItems as $item): 
    ?>
    <tr style="border: 1px solid black; border-top: none;">
        <td class="center" style="border-right: 1px solid black;"><?php echo $item['name']; ?></td>
        <td class="center"><?php echo (is_numeric($item['amount'])) ? number_format($item['amount'], 2) : $item['amount']; ?></td>
    </tr>
    <?php endforeach; ?>
    
    <tr>
        <td class="right bold" style="border-right: 1px solid black; border-top: 1px solid black; padding: 10px;">Total:</td>
        <td class="center bold" style="border-top: 1px solid black; padding: 10px;">Php <?php echo number_format($total, 2); ?></td>
    </tr>
    
    <tr>
        <td colspan="2" class="center" style="border-top: 1px solid black; padding: 5px;">
            <span style="font-size: 10px">MYP-GBY Bldg., E. Aguinaldo Highway., Bayan Luma 7, Imus City, Cavite</span>
        </td>
    </tr>
</table>



</body>
</html>