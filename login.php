<!DOCTYPE html>
<html>
<head>
    <title>Вход</title>
    <link rel="stylesheet" href="css/log.css">    
</head>
<body>
    <main>
        <?php
        require_once 'auth.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];

            if (loginUser($username, $password)) {
                header("Location: index.php");
                exit;
            } else {
                echo '<p class="error">Неверное имя пользователя или пароль.</p>';
            }
        }
        ?>
        <div class="login-container">
            <form method="post">
                <label for="username">Username:</label><br>
                <input type="text" id="username" name="username" required><br>

                <label for="password">Password:</label><br>
                <input type="password" id="password" name="password" required><br>

                <button type="submit" class="login-button">Войти</button>
            </form>
            <a href="register.php" class="register-link">Регистрация</a>
        </div>
    </main>
</body>
</html>