<!DOCTYPE html>
<html>
<head>
    <title>Прохождение теста</title>
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
    </style>
</head>
<body>
<header>
        <h1>Прохождение теста</h1>
        <nav>
            <a href="tests.php" class="button">К списку тестов</a>
            <a class="button" href="results.php?id=<?php 
                $testId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
               if (isset($testId)) {
                  echo htmlspecialchars($testId); 
               } else {
                  echo '0'; 
               }
            ?>">Посмотреть результаты</a>
            <?php
            require_once 'auth.php';
            require_once 'functions.php';
            $rights = getUserRights();
            if ($rights === '1') {?><a href="edit_tests.php" class="button">Редактировать тест</a><?php }?>
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
            echo '<p class="error">Вы должны быть авторизованы, чтобы проходить тесты.</p>';
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
        $allowed_attempts = getAllowedAttempts($userId);
        $used_attempts = getNumberOfAttempts($userId, $testId);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $score = 0;
            $questions = getQuestionsByTestId($testId);
            foreach ($questions as $question) {
                $questionId = $question['id'];
                $questionWeight = $question['weight'];
                if ($question['question_type'] === 'single') {
                    $selectedAnswerId = isset($_POST['question_' . $questionId]) ? (int)$_POST['question_' . $questionId] : 0;
                    $correctAnswer = getCorrectAnswerId($questionId);
                    if ($selectedAnswerId === $correctAnswer) {
                        $score += $questionWeight;
                    }
                } else if ($question['question_type'] === 'multiple') {
                    $selectedAnswerIds = isset($_POST['question_' . $questionId]) ? $_POST['question_' . $questionId] : [];
                    $selectedAnswerIds = array_map('intval', $selectedAnswerIds);
                    sort($selectedAnswerIds);
                    $correctAnswerIds = getCorrectAnswerIds($questionId);
                    sort($correctAnswerIds);
                    if ($selectedAnswerIds === $correctAnswerIds) {
                        $score += $questionWeight;
                    }
                } else if ($question['question_type'] === 'text') {
                    $userAnswer = isset($_POST['question_' . $questionId]) ? trim($_POST['question_' . $questionId]) : '';
                    $correctAnswerText = getCorrectAnswerText($questionId);
                    if (strcasecmp($userAnswer, $correctAnswerText) === 0) {
                        $score += $questionWeight;
                    }
                }
            }
            $totalWeight = getTotalWeightForTest($testId);
            $scorePercentage = ($totalWeight > 0) ? ($score / $totalWeight) * 100 : 0;
            $attemptNumber = $used_attempts + 1;
            saveResult($userId, $testId, $scorePercentage, $attemptNumber);
            echo '<p class="success">Тест завершен! Ваш результат: ' . htmlspecialchars($scorePercentage) . '%</p>';
        } 
        else {
            $questions = getQuestionsByTestId($testId);
            if (empty($questions)) {
                echo '<p>В этом тесте нет вопросов.</p>';
            } else {
                    echo '<form method="post">';
                    foreach ($questions as $question) {
                        echo '<h3>' . htmlspecialchars($question['question_text']) . '</h3>';
                        if ($question['image_path']) {
                            echo '<img src="' . htmlspecialchars($question['image_path']) . '" alt="Изображение к вопросу">';
                        }
                        if ($question['code']) {
                            echo '<pre><code>' . htmlspecialchars($question['code']) . '</code></pre>';
                        }
                        $answers = getAnswersByQuestionId($question['id']);
                        if ($question['question_type'] === 'single' || $question['question_type'] === 'multiple') {
                            foreach ($answers as $answer) {
                                echo '<label>';
                                $inputName = 'question_' . $question['id'];
                                if ($question['question_type'] === 'single') {
                                    echo '<input type="radio" name="' . $inputName . '" value="' . htmlspecialchars($answer['id']) . '"> ';
                                } else {
                                    echo '<input type="checkbox" name="' . $inputName . '[]" value="' . htmlspecialchars($answer['id']) . '"> ';
                                }
                                echo htmlspecialchars($answer['answer_text']) . '</label><br>';
                            }
                        } elseif ($question['question_type'] === 'text') {
                            echo '<input type="text" name="question_' . $question['id'] . '">';
                        }
                        echo '<br>';
                    }
                    echo '<input type="submit" value="Завершить тест">';
                    echo '</form>';                
            }
        }
        ?>
    </main>
</body>
</html>