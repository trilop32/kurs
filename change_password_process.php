<?php
require_once 'auth.php';
require_once 'functions.php'; 
if (!isLoggedIn()) {
    echo '<p class="error">Вы должны быть авторизованы, чтобы сменить пароль.</p>';
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old_password = $_POST["old_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];
    $user_id = $_SESSION['user_id'];
    $hashed_password = getUserPasswordHash($user_id);
    if (!password_verify($old_password, $hashed_password)) {
        echo '<p class="error">Неверный старый пароль.</p>';
        exit;
    }
    if ($new_password !== $confirm_password) {
        echo '<p class="error">Новый пароль и подтверждение не совпадают.</p>';
        exit;
    }
    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

    $update_result = updateUserPassword($user_id, $hashed_new_password);

    if ($update_result) {
        header("Location: profile.php?password_changed=1");
        exit();
    } else {
        echo '<p class="error">Ошибка при смене пароля.</p>';
        exit();
    }
}
?>