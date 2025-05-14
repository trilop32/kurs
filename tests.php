<!DOCTYPE html>
<html>
<head>
    <title>Доступные тесты</title>
    <link rel="stylesheet" href="css/users.css">
    <link rel="stylesheet" href="css/tests.css">
    <style>
        
    </style>
</head>
<body>
<header>
    <h1>Доступные тесты</h1>
    <nav>
        <?php
        require_once 'auth.php';
        require_once 'functions.php';
        $rights = getUserRights();     
        if ($rights === '1') {?><a href="add_tests.php" class="button">Добавить тест</a><?php }?>
        
    </nav>
    <div class="user-profile">
        <?php        

        if (isLoggedIn()) {
            $avatar_url = isset($_SESSION['avatar']) ? $_SESSION['avatar'] : '/images/kristal.jpg'; 
            echo '<img src="' . htmlspecialchars($avatar_url) . '" alt="Avatar">';
            echo '<a href="profile.php"><span class="nickname">' . htmlspecialchars($_SESSION['username']) . '</span></a>';
            echo '<div class="settings-container">';
            echo '<span class="settings-icon">&#9881;</span>';
            echo '<div class="settings-dropdown">';
            echo '<a href="profile.php">Редактировать профиль</a>';
            echo '<a href="logout.php">Выход</a>';
            echo '</div>';
            echo '</div>';

        } else {
            echo '<a href="login.php">Войти</a>';
        }
        ?>
    </div>
</header>
    <main>
        <?php
        if (!isLoggedIn()) {
            echo '<p class="error">Вы должны быть авторизованы, чтобы просматривать тесты.</p>';
            exit;
        }

        $tests = getAllTests();

        if (empty($tests)) {
            echo '<p>Нет доступных тестов.</p>';
        } else {
            foreach ($tests as $test) {
                echo '<div class="test-card">';
                echo '<a href="take_test.php?id=' . htmlspecialchars($test['id']) . '">' . htmlspecialchars($test['title']) . '</a>';
                echo '</div>';
            }
        }
        ?>
    </main>
</body>
   <script>
   document.addEventListener('DOMContentLoaded', function() {
       const settingsIcon = document.querySelector('.settings-icon');
       const settingsDropdown = document.querySelector('.settings-dropdown');

       settingsIcon.addEventListener('click', function() {
           settingsDropdown.classList.toggle('show'); 
       });
       window.addEventListener('click', function(event) {
           if (!event.target.matches('.settings-icon')) {
               if (settingsDropdown.classList.contains('show')) {
                   settingsDropdown.classList.remove('show');
               }
           }
       });
   });
   </script>
</html>