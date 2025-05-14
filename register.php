<!DOCTYPE html>
<>
<>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="css/regist.css">
</head>
<body>
    <main>
    <?php
        require_once 'auth.php'; 

        $username_error = "";
        $password_error = "";
        $email_error = "";
        $success = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $email = $_POST['email'];
            if (strlen($username) < 3 || strlen($username) > 15) {
                $username_error = "Имя пользователя должно быть от 3 до 15 символов.";
            }
            if (strlen($password) < 6 || strlen($password) > 15) {
                $password_error = "Пароль должен быть от 6 до 15 символов.";
            }
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email_error = "Некорректный формат email.";
            }
            if (empty($username_error) && empty($password_error) && empty($email_error)) {
                $rights = "0";

                if (registerUser($username, $password, $email, $rights)) { 
                    $success = true;
                    header("Location: login.php");
                    exit();
                } else {
                    echo '<p class="error">Ошибка регистрации. Пожалуйста, попробуйте еще раз.</p>';
                }
            }
        }
        ?>
        <div class="login-container">
            <form method="post">
                <label for="username">Имя пользователя:</label>
                <input type="text" id="username" name="username" required value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"><br>
                <span class="error"><?php echo $username_error; ?></span><br>
                <label for="password">Пароль:</label>
                <input type="password" id="password" name="password" required><br>
                <span class="error"><?php echo $password_error; ?></span><br>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"><br>
                <span class="error"><?php echo $email_error; ?></span><br>
                <button type="submit" class="register-button">Зарегистрироваться</button>
            </form>
            <a href="login.php" class="login-link">Уже зарегистрированы? Войти</a>
        </div>
    </main>
</body>
</html>