<?php
require_once 'config.php';

function getAllTests() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM tests");
    return $stmt->fetchAll();
}
function getTestById($testId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM tests WHERE id = ?");
    $stmt->execute([$testId]);
    return $stmt->fetch();
}

function createTest($title, $description) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO tests (title, description) VALUES (?, ?)");
    $stmt->execute([$title, $description]);
    return $pdo->lastInsertId(); 
}
function getQuestionsByTestId($testId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE test_id = ?");
    $stmt->execute([$testId]);
    return $stmt->fetchAll();
}

function createQuestion($testId, $questionText, $questionType, $imagePath = null, $weight = 1) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO questions (test_id, question_text, question_type, image_path, weight) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$testId, $questionText, $questionType, $imagePath, $weight]);
    return $pdo->lastInsertId(); 
}
function getAnswersByQuestionId($questionId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM answers WHERE question_id = ?");
    $stmt->execute([$questionId]);
    return $stmt->fetchAll();
}

function createAnswer($questionId, $answerText, $isCorrect = false) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
    $stmt->execute([$questionId, $answerText, $isCorrect]);
    return $pdo->lastInsertId(); 
}
function saveResult($userId, $testId, $score, $attemptNumber) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO results (user_id, test_id, score, attempt_number) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $testId, $score, $attemptNumber]);
}

function getResultsByUserIdAndTestId($userId, $testId) {
  global $pdo;
  $stmt = $pdo->prepare("SELECT * FROM results WHERE user_id = ? AND test_id = ? ORDER BY completed_at DESC");
  $stmt->execute([$userId, $testId]);
  return $stmt->fetchAll();
}
function startAttempt($userId, $testId) {
    global $pdo;
    $allowed_attempts = getAllowedAttempts($userId);
    $used_attempts = getNumberOfAttempts($userId, $testId);

    if ($used_attempts >= $allowed_attempts) {
        return false; 
    }
    $attempt_number = $used_attempts + 1;
    $stmt = $pdo->prepare("INSERT INTO attempts (user_id, test_id, attempt_number) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $testId, $attempt_number]);
    return true;
}

function getNumberOfAttempts($userId, $testId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM attempts WHERE user_id = ? AND test_id = ?");
    $stmt->execute([$userId, $testId]);
    return $stmt->fetchColumn(); 
}

function getAllowedAttempts($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT attempts_allowed FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}
function getCorrectAnswerId($questionId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM answers WHERE question_id = ? AND is_correct = TRUE LIMIT 1");
    $stmt->execute([$questionId]);
    $result = $stmt->fetch();
    return $result ? $result['id'] : null;
}
function getCorrectAnswerIds($questionId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM answers WHERE question_id = ? AND is_correct = TRUE");
    $stmt->execute([$questionId]);
    $results = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    return $results;
}
function getCorrectAnswerText($questionId) {
  global $pdo;
  $stmt = $pdo->prepare("SELECT answer_text FROM answers WHERE question_id = ? AND is_correct = TRUE LIMIT 1");
  $stmt->execute([$questionId]);
  $result = $stmt->fetch();
  return $result ? $result['answer_text'] : null;
}
function getTotalWeightForTest($testId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT SUM(weight) FROM questions WHERE test_id = ?");
    $stmt->execute([$testId]);
    $result = $stmt->fetch();
    return $result ? $result[0] : 0; 
}
function isAdmin() {
    if (!isLoggedIn()) {
        return false;
    }
    global $db; 

    $username = $_SESSION['username'];

    $query = "SELECT role FROM users WHERE username = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row['role'] === 'admin';
    }
    return false;
}
function updateUserInfo($user_id, $new_username, $new_email) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?"); 
         $stmt->execute([$new_username, $new_email, $user_id]);
        return true;
    } catch (PDOException $e) {
        error_log("Ошибка при обновлении профиля: " . $e->getMessage());
        return false;
    }
}
function getUserPasswordHash($user_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?"); 
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['password'] : false;
    } catch (PDOException $e) {
        error_log("Ошибка при получении хеша пароля: " . $e->getMessage());
        return false;
    }
}

function updateUserPassword($user_id, $hashed_new_password) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_new_password, $user_id]);
        return true;
    } catch (PDOException $e) {
        error_log("Ошибка при обновлении пароля: " . $e->getMessage());
        return false;
    }
}
function getAllResultsForTest(int $testId): array
{
    global $pdo; 
    $stmt = $pdo->prepare("SELECT user_id, score, attempt_number FROM results WHERE test_id = ?"); 
    $stmt->execute([$testId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getResultsForUserAndTest(int $userId, int $testId): array
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT score, attempt_number FROM results WHERE user_id = ? AND test_id = ?");
    $stmt->execute([$userId, $testId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUsernameById(int $userId): string
{
    global $pdo; 
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?"); 
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ? $user['username'] : 'Неизвестный пользователь';
}
function isUserLoggedIn() {
    session_start();
    return isset($_SESSION['user_id']);
}
function saveTest($testData) {
    $host = 'MySQL-8.2';
    $dbname = 'test';
    $username = 'root';
    $password = '';
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try {
        $db->beginTransaction();
        $stmt = $db->prepare("INSERT INTO tests (title, description, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$testData['test_title'], $testData['test_description']]);
        $testId = $db->lastInsertId();

        $questionOrder = 1;
        foreach ($testData['questions'] as $question) {
            $stmt = $db->prepare("INSERT INTO questions (test_id, question_order, question_text, question_type) VALUES (?, ?, ?, ?)");
            $stmt->execute([$testId, $questionOrder, $question['question_text'], $question['question_type']]);
            $questionId = $db->lastInsertId();
            $questionOrder++;
            foreach ($question['answers'] as $answer) {
                $isCorrect = isset($answer['is_correct']) && $answer['is_correct'] == '1' ? 1 : 0;
                $stmt = $db->prepare("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
                $stmt->execute([$questionId, $answer['answer_text'], $isCorrect]);
            }
        }

        $db->commit();
        return true;

    } catch (PDOException $e) {
        $db->rollBack();
        error_log("Ошибка при сохранении теста: " . $e->getMessage()); 
        return false;
    }
}
function getTestBy($testId) {
    $db = new PDO('mysql:host=localhost;dbname=your_database', 'username', 'password'); 
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try {
        $stmt = $db->prepare("SELECT * FROM tests WHERE id = ?");
        $stmt->execute([$testId]);
        $test = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($test) {
            $stmt = $db->prepare("SELECT * FROM questions WHERE test_id = ? ORDER BY question_order ASC");
            $stmt->execute([$testId]);
            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($questions as &$question) {
                $stmt = $db->prepare("SELECT * FROM answers WHERE question_id = ?");
                $stmt->execute([$question['id']]);
                $question['answers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            $test['questions'] = $questions;
            return $test;
        } else {
            return false;
        }

    } catch (PDOException $e) {
        error_log("Ошибка при получении теста: " . $e->getMessage());
        return false;
    }
}

function getTest($testId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM tests WHERE id = :test_id");
    $stmt->bindParam(':test_id', $testId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getQuestionsByTest($testId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE test_id = :test_id");
    $stmt->bindParam(':test_id', $testId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAnswersByQuestion($questionId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM answers WHERE question_id = :question_id");
    $stmt->bindParam(':question_id', $questionId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateTest($testId, $title, $description) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE tests SET title = :title, description = :description WHERE id = :test_id");
    $stmt->bindParam(':test_id', $testId, PDO::PARAM_INT);
    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt->execute();
}
?>
