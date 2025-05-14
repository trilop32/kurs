<?php
require_once 'auth.php';
if (!isLoggedIn()) {
    echo '<p class="error">Вы должны быть авторизованы, чтобы редактировать свой профиль.</p>';
    exit;
}
$current_username = $_SESSION['username'];
$current_email = isset($_SESSION['email']) ? $_SESSION['email'] : ''; 
?>

<form method="post" action="edit_profile_process.php" id="edit-profile-form">
    <label for="new_username">Имя пользователя:</label><br>
    <input type="text" id="new_username" name="new_username" value="<?php echo htmlspecialchars($current_username); ?>"><br><br>

    <label for="new_email">Email:</label><br>
    <input type="email" id="new_email" name="new_email" value="<?php echo htmlspecialchars($current_email); ?>"><br><br>

    <button type="submit" id="update-profile-button" disabled>Сохранить изменения</button>
</form>

<h3>Сменить пароль</h3>
<form method="post" action="change_password_process.php" id="change-password-form">
    <label for="old_password">Старый пароль:</label><br>
    <input type="password" id="old_password" name="old_password"><br><br>

    <label for="new_password">Новый пароль:</label><br>
    <input type="password" id="new_password" name="new_password"><br><br>

    <label for="confirm_password">Подтвердите новый пароль:</label><br>
    <input type="password" id="confirm_password" name="confirm_password"><br><br>

    <button type="submit">Сменить пароль</button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('edit-profile-form');
    const usernameInput = document.getElementById('new_username');
    const emailInput = document.getElementById('new_email');
    const updateButton = document.getElementById('update-profile-button');

    let originalUsername = usernameInput.value;
    let originalEmail = emailInput.value;

    function checkChanges() {
        if (usernameInput.value !== originalUsername || emailInput.value !== originalEmail) {
            updateButton.disabled = false;
        } else {
            updateButton.disabled = true;
        }
    }

    usernameInput.addEventListener('input', checkChanges);
    emailInput.addEventListener('input', checkChanges);
});
</script>