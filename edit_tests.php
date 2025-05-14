<?php
session_start();
require_once 'auth.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}
$rights = getUserRights();
if ($rights !== '1') { 
    echo '<p class="error">You do not have permission to edit tests.</p>';
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];


$testId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$testId) {
    echo '<p class="error">No test ID specified.</p>';
    exit;
}

$test = getTest($testId);
if (!$test) {
    echo '<p class="error">Test not found.</p>';
    exit;
}

$questions = getQuestionsByTest($testId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token) {
        die("CSRF token validation failed.");
    }
    $updatedTestTitle = $_POST['test_title'];
    $updatedTestDescription = $_POST['test_description'];
    $updatedTestTitle = htmlspecialchars($updatedTestTitle);
    $updatedTestDescription = htmlspecialchars($updatedTestDescription);
    updateTest($testId, $updatedTestTitle, $updatedTestDescription);
    header('Location: tests.php?message=Test updated successfully');
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Test</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Edit Test</h1>
        <nav>
        <a href="tests.php" class="button">Back to Test List</a>
        </nav>
    </header>

    <main>
        <h2>Editing Test: <?php echo htmlspecialchars($test['title']); ?></h2>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>"> 

            <label for="test_title">Test Title:</label>
            <input type="text" id="test_title" name="test_title" value="<?php echo htmlspecialchars($test['title']); ?>" required><br><br>

            <label for="test_description">Test Description:</label>
            <textarea id="test_description" name="test_description"><?php echo htmlspecialchars($test['description']); ?></textarea><br><br>

            <h3>Questions:</h3>
            <?php foreach ($questions as $question): ?>
                <div class="question">
                    <label for="question_<?php echo htmlspecialchars($question['id']); ?>">Question Text:</label>
                    <input type="text" id="question_<?php echo htmlspecialchars($question['id']); ?>" name="question_<?php echo htmlspecialchars($question['id']); ?>" value="<?php echo htmlspecialchars($question['question_text']); ?>"><br><br>

                    <?php
                    $answers = getAnswersByQuestion($question['id']);
                    foreach ($answers as $answer): ?>
                        <div class="answer">
                            <label for="answer_<?php echo htmlspecialchars($answer['id']); ?>">Answer Text:</label>
                            <input type="text" id="answer_<?php echo htmlspecialchars($answer['id']); ?>" name="answer_<?php echo htmlspecialchars($answer['id']); ?>" value="<?php echo htmlspecialchars($answer['answer_text']); ?>"><br><br>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <input type="submit" value="Update Test">
        </form>
    </main>
</body>
</html>