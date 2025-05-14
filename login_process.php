<?php
    session_start();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST['username'];
        $password = $_POST['password'];
        if ($username == "user" && $password == "password") {
            $_SESSION['username'] = $username;
            header("Location: tests.php"); 
            exit();
        } else {
            $error_message = "Неверное имя пользователя или пароль.";
        }
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Ошибка входа</title>
    </head>
    <body>
        <h1>Ошибка входа</h1>
        <p><?php echo $error_message; ?></p>
        <a href="login.php">Попробовать снова</a>
    </body>
    </html>