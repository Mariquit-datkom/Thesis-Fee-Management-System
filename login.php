<?php    
    require_once 'x-head.php';
    session_start();

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");

    $confirmationMessage = "";
    if(isset($_SESSION['error'])) {
        $confirmationMessage = $_SESSION['error'];
        unset($_SESSION['error']);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="form-container">
        <img src="assets/images/school_logo.png" alt="school-logo" class="school-logo">
        <div class="form-title-container">
            <h2 class="form-title">Welcome</h2>
            <?php echo $confirmationMessage ?>
        </div>
        <div class="form-main">
            <form action="loginAuth.php" method="post" autocomplete="off">
                <div class="form-group">
                    <label for="username" class="form-label"><i class="fa fa-user"></i></label>
                    <input type="text" name="username" id="username" class="form-input" placeholder="Username">
                </div>
                <div class="form-group">
                    <label for="password" class="form-label"><i class="fa fa-key"></i></label>
                    <input type="password" name="password" id="password" class="form-input" placeholder="Password">
                </div>
                <div class="btn-container">
                    <input type="submit" value="Log In" class="btn">
                </div>
            </form>
        </div>
    </div>    

    <script src="js/formCleaner.js"></script>
</body>
</html>