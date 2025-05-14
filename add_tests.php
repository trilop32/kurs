<?
require_once 'auth.php';
require_once 'functions.php';

if (!isUserLoggedIn()) {
    header("Location: login.php");
    exit();
}

$rights = getUserRights();
if ($rights !== '1') {
    echo "<p>У вас нет прав для доступа к этой странице.</p>";
    exit(); 
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $testTitle = isset($_POST["test_title"]) ? trim($_POST["test_title"]) : '';
    $testDescription = isset($_POST["test_description"]) ? trim($_POST["test_description"]) : '';
    $questionsData = isset($_POST["questions"]) ? $_POST["questions"] : [];
    $errors = [];
    if (empty($testTitle)) {
        $errors[] = "Название теста обязательно для заполнения.";
    }
    if (empty($testDescription)) {
        $errors[] = "Описание теста обязательно для заполнения.";
    }
    if (empty($questionsData)) {
        $errors[] = "Необходимо добавить хотя бы один вопрос.";
    } else {
        foreach ($questionsData as $questionIndex => $question) {
            if (empty($question['question_text'])) {
                $errors[] = "Текст вопроса #".($questionIndex+1)." обязателен для заполнения.";
            }

            if (!isset($question['question_type'])) {
                $errors[] = "Тип вопроса #".($questionIndex+1)." обязателен для заполнения.";
            }

            if (empty($question['answers'])) {
                $errors[] = "У вопроса #".($questionIndex+1)." должно быть хотя бы два варианта ответа.";
            } else {
                $correctAnswerFound = false;
                foreach ($question['answers'] as $answerIndex => $answer) {
                    if (empty($answer['answer_text'])) {
                        $errors[] = "Текст ответа #".($answerIndex+1)." вопроса #".($questionIndex+1)." обязателен для заполнения.";
                    }
                    if (isset($answer['is_correct']) && $answer['is_correct'] == '1') {
                        $correctAnswerFound = true;
                    }
                }
                if (!$correctAnswerFound && $question['question_type'] !== 'text') { // Для текстовых вопросов не требуется правильный ответ
                    $errors[] = "У вопроса #".($questionIndex+1)." должен быть указан хотя бы один правильный ответ.";
                }
            }
        }
    }
    if (!empty($errors)) {
        echo "<div class='error'>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul>";
        echo "</div>";
    } else {
        $testData = [
            'test_title' => $testTitle,
            'test_description' => $testDescription,
            'questions' => $questionsData
        ];
        if (saveTest($testData)) {
            echo "<div class='success'>Тест успешно сохранен!</div>";
            $testTitle = '';
            $testDescription = '';
            $questionsData = [];

        } else {
            echo "<div class='error'>Произошла ошибка при сохранении теста. Пожалуйста, попробуйте еще раз.</div>";
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Добавить тест</title>
    <link rel="stylesheet" href="css/add.css">    
</head>
<body>
    <header>
        <nav>
            <a href="tests.php" class="buttons">К списку тестов</a>
        </nav>
    </header>
        <?php
        $rights = getUserRights();
        if ($rights === '1') {?><a href="add_tests.php" class="button">Добавить тест</a><?php }?>


    <div class="form-container">
        <h2>Добавить новый тест</h2>
        <form method="post">
            <div class="form-group">
                <label for="test_title">Название теста:</label>
                <input type="text" id="test_title" name="test_title" value="<?php echo htmlspecialchars($testTitle ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="test_description">Описание теста:</label>
                <textarea id="test_description" name="test_description"><?php echo htmlspecialchars($testDescription ?? ''); ?></textarea>
            </div>

            <div id="questions-container">
                <?php if (!empty($questionsData)): ?>
                    <?php foreach ($questionsData as $questionIndex => $question): ?>
                        <div class="question-container">
                            <label>Вопрос #<?php echo $questionIndex + 1; ?>:</label>
                            <input type="hidden" name="questions[<?php echo $questionIndex; ?>][question_id]" value="<?php echo htmlspecialchars($question['question_id'] ?? ''); ?>">
                            <input type="text" name="questions[<?php echo $questionIndex; ?>][question_text]" value="<?php echo htmlspecialchars($question['question_text'] ?? ''); ?>">

                            <label for="question_type_<?php echo $questionIndex; ?>">Тип вопроса:</label>
                            <select id="question_type_<?php echo $questionIndex; ?>" name="questions[<?php echo $questionIndex; ?>][question_type]">
                                <option value="single" <?php echo (isset($question['question_type']) && $question['question_type'] == 'single') ? 'selected' : ''; ?>>Single</option>
                                <option value="multiple" <?php echo (isset($question['question_type']) && $question['question_type'] == 'multiple') ? 'selected' : ''; ?>>Multiple</option>
                                <option value="text" <?php echo (isset($question['question_type']) && $question['question_type'] == 'text') ? 'selected' : ''; ?>>Text</option>
                            </select>

                            <div class="answers-container">
                                <?php if (!empty($question['answers'])): ?>
                                    <?php foreach ($question['answers'] as $answerIndex => $answer): ?>
                                        <div class="answer-container">
                                            <label>Ответ #<?php echo $answerIndex + 1; ?>:</label>
                                            <input type="hidden" name="questions[<?php echo $questionIndex; ?>][answers][<?php echo $answerIndex; ?>][answer_id]" value="<?php echo htmlspecialchars($answer['answer_id'] ?? ''); ?>">
                                            <input type="text" name="questions[<?php echo $questionIndex; ?>][answers][<?php echo $answerIndex; ?>][answer_text]" value="<?php echo htmlspecialchars($answer['answer_text'] ?? ''); ?>">
                                            <?php if ($question['question_type'] !== 'text'): ?>
                                                <label><input type="checkbox" name="questions[<?php echo $questionIndex; ?>][answers][<?php echo $answerIndex; ?>][is_correct]" value="1" <?php echo (isset($answer['is_correct']) && $answer['is_correct'] == '1') ? 'checked' : ''; ?>>Правильный ответ</label>
                                            <?php endif; ?>
                                            <button type="button" class="remove-answer-btn" onclick="removeAnswer(this)">Удалить ответ</button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <button type="button" class="add-answer-btn" onclick="addAnswer(this, <?php echo $questionIndex; ?>)">Добавить ответ</button>
                            </div>
                            <button type="button" class="remove-question-btn" onclick="removeQuestion(this)">Удалить вопрос</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <button type="button" id="add-question-btn">Добавить вопрос</button>
            <button type="submit">Сохранить тест</button>
        </form>
    </div>

    <script>
        let questionCounter = <?php echo count($questionsData); ?>; 
        function addQuestion() {
            questionCounter++;
            const questionContainer = document.createElement('div');
            questionContainer.classList.add('question-container');
            questionContainer.innerHTML = `
                <label>Вопрос #${questionCounter}:</label>
                <input type="text" name="questions[${questionCounter - 1}][question_text]">
                <label for="question_type_${questionCounter - 1}">Тип вопроса:</label>
                <select id="question_type_${questionCounter - 1}" name="questions[${questionCounter - 1}][question_type]">
                    <option value="single">Single</option>
                    <option value="multiple">Multiple</option>
                    <option value="text">Text</option>
                </select>
                <div class="answers-container">
                    <button type="button" class="add-answer-btn" onclick="addAnswer(this, ${questionCounter - 1})">Добавить ответ</button>
                </div>
                <button type="button" class="remove-question-btn" onclick="removeQuestion(this)">Удалить вопрос</button>
            `;
            document.getElementById('questions-container').appendChild(questionContainer);
        }

        function removeQuestion(button) {
            const questionContainer = button.parentNode;
            questionContainer.remove();
            updateQuestionNumbers();
        }

        let answerCounter = []; 

        function addAnswer(button, questionIndex) {
            if (typeof answerCounter[questionIndex] === 'undefined') {
                answerCounter[questionIndex] = 0;
            }
            answerCounter[questionIndex]++;
            const questionType = document.querySelector(`#question_type_${questionIndex}`).value;

            const answersContainer = button.parentNode;
            const answerContainer = document.createElement('div');
            answerContainer.classList.add('answer-container');
            let checkboxHTML = '';
            if (questionType !== 'text') {
                checkboxHTML = `<label><input type="checkbox" name="questions[${questionIndex}][answers][${answerCounter[questionIndex] - 1}][is_correct]" value="1">Правильный ответ</label>`;
            }

            answerContainer.innerHTML = `
                <label>Ответ #${answerCounter[questionIndex]}:</label>
                <input type="text" name="questions[${questionIndex}][answers][${answerCounter[questionIndex] - 1}][answer_text]">
                ${checkboxHTML}
                <button type="button" class="remove-answer-btn" onclick="removeAnswer(this)">Удалить ответ</button>
            `;
            answersContainer.insertBefore(answerContainer, button);

        }

        function removeAnswer(button) {
            const answerContainer = button.parentNode;
            answerContainer.remove();
        }

        function updateQuestionNumbers() {
            const questionContainers = document.querySelectorAll('.question-container');
            questionContainers.forEach((container, index) => {
                container.querySelector('label').textContent = `Вопрос #${index + 1}:`;
            });
        }

        document.getElementById('add-question-btn').addEventListener('click', addQuestion);
    </script>
</body>
</html>
