<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль</title>
    <link rel="stylesheet" href="css/profill.css">
</head>
<body>
<header>
<nav>
    <a href="tests.php" class="button">К списку тестов</a>
</nav>

<?php
require_once 'auth.php';
require_once 'functions.php';
?>
<div class="profile-details">
    <img src="<?php echo htmlspecialchars($_SESSION['avatar'] ?? 'kristal.jpg'); ?>" alt="Avatar">
    <h2><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
</div>
</header>

<main>
<?php
if (!isLoggedIn()) {
    echo '<p class="error">Вы должны быть авторизованы, чтобы просматривать тесты.</p>';
    exit;
}
?>

<div class="profile-details">
    <button id="toggle-edit-profile">Редактировать профиль</button>

    <div id="edit-profile-container" style="display: none;">
        <?php include 'edit_profile.php'; ?>
    </div>
</div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleEditButton = document.getElementById('toggle-edit-profile');
    const editProfileContainer = document.getElementById('edit-profile-container');

    toggleEditButton.addEventListener('click', function() {
        if (editProfileContainer.style.display === 'none') {
            editProfileContainer.style.display = 'block';
            toggleEditButton.textContent = 'Скрыть редактирование';
        } else {
            editProfileContainer.style.display = 'none';
            toggleEditButton.textContent = 'Редактировать профиль';
        }
    });
});
</script>
</body>
</html>