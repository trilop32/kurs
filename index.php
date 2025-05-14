<!DOCTYPE html>
<html>
<head>
    <title>Тестовый портал</title>
    <link href="css/main.css" rel="stylesheet" >
</head>
<body>
    <header>
        <nav>
            <?php
            require_once 'auth.php';

            if (!isLoggedIn()) {
                header("Location: login.php");
                exit();
            } else {
                header("Location: tests.php");
                exit();    
            }
            ?>
        </nav>
    </header>
</body>
</html>