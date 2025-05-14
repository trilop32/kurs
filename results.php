<!DOCTYPE html>
<html>
<head>
    <title>Результаты теста</title>
    <link rel="stylesheet" href="css/users.css">
    <link rel="stylesheet" href="css/tests.css">
    <style>
        .button {
            background-color: black;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 10px;
        }
        .button:hover {
            background-color: #333;
        }
            .results-container {
        display: block; 
        margin-bottom: 20px; 
        }

        .results-container h3 {
            display: block; 
        }

        .results-container table {
            display: block; 
        } 
    </style>
</head>
<body>
    <header>
        <h1>Результаты теста</h1>
        <nav>
            <a href="tests.php" class="button">К списку тестов</a>
        </nav>
        <div class="user-profile">
        <?php
        require_once 'auth.php';
        require_once 'functions.php';

        if (isLoggedIn()) {
            $avatar_url = isset($_SESSION['avatar']) ? $_SESSION['avatar'] : '/images/kristal.jpg'; // Или URL по умолчанию
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
        echo '<p class="error">Вы должны быть авторизованы, чтобы просматривать результаты.</p>';
        exit;
    }

    $testId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if (!$testId) {
        echo '<p class="error">Не указан ID теста.</p>';
        exit;
    }

    $test = getTestById($testId);

    if (!$test) {
        echo '<p class="error">Тест не найден.</p>';
        exit;
    }

    $userId = getUserId();

    echo '<h2>Результаты теста: ' . (isset($test['title']) ? htmlspecialchars($test['title']) : 'Название теста не указано') . '</h2>'; 

    echo '<div class="results-container">';
    echo '<h3>Результаты всех пользователей:</h3>';
    $allResults = getAllResultsForTest($testId);

    if (empty($allResults)) {
        echo '<p>Пока никто не проходил этот тест.</p>';
    } else {
        echo '<table border="1">
                <thead>
                    <tr>
                        <th>Пользователь</th>
                        <th>Попытка</th>
                        <th>Результат (%)</th>
                        <th>Дата завершения</th>
                    </tr>
                </thead>
                <tbody>';
        foreach ($allResults as $result) {
            echo '<tr>
                    <td>' . htmlspecialchars(getUsernameById($result['user_id'])) . '</td>
                    <td>' . htmlspecialchars($result['attempt_number']) . '</td>
                    <td>' . htmlspecialchars($result['score']) . '</td>
                    <td>' . (isset($result['completed_at']) ? htmlspecialchars($result['completed_at']) : 'Не указано') . '</td>  
                  </tr>';
        }
        echo '</tbody>
              </table>';
    }
    echo '</div>';
    echo '<div class="results-container">';
    echo '<h3>Ваша личная статистика:</h3>';
    $userResults = getResultsByUserIdAndTestId($userId, $testId);

    if (empty($userResults)) {
        echo '<p>Вы еще не проходили этот тест.</p>';
    } else {
        echo '<table border="1">
                <thead>
                    <tr>
                        <th>Попытка</th>
                        <th>Результат (%)</th>
                        <th>Дата завершения</th>
                    </tr>
                </thead>
                <tbody>';
        foreach ($userResults as $result) {
            echo '<tr>
                    <td>' . htmlspecialchars($result['attempt_number']) . '</td>
                    <td>' . htmlspecialchars($result['score']) . '</td>
                    <td>' . (isset($result['completed_at']) ? htmlspecialchars($result['completed_at']) : 'Не указано') . '</td> 
                  </tr>';
        }
        echo '</tbody>
              </table>';
    }
    echo '</div>';
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