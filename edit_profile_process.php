<?php
require_once 'auth.php';
require_once 'functions.php'; 
if (!isLoggedIn()) {
    echo '<p class="error">Вы должны быть авторизованы, чтобы редактировать свой профиль.</p>';
    exit;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = $_POST["new_username"];
    $new_email = $_POST["new_email"];
    $user_id = $_SESSION['user_id'];
    $update_result = updateUserInfo($user_id, $new_username, $new_email);
    if ($update_result) {
        $_SESSION['username'] = $new_username;
        $_SESSION['email'] = $new_email;
        header("Location: profile.php?success=1");
        exit();
    } else {
        echo '<p class="error">Ошибка при обновлении профиля.</p>';
        exit();
    }
}
?>