<?php
require_once 'config.php';

function registerUser($username, $password, $email, $rights) {
    global $pdo;
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT); 
    $stmt = $pdo->prepare("INSERT INTO users (username, password, email, rights) VALUES (?, ?, ?, ?)");
    try {
        $stmt->execute([$username, $hashedPassword, $email, $rights]);
        return true;
    } catch (PDOException $e) {
        return false;
    }

}
function loginUser($username, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, password, rights FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $username;
        $_SESSION['rights'] = $user['rights']; 
        return true;
    } else {
        return false;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function logoutUser() {
    session_unset();
    session_destroy();
}

function getUserId() {
  return $_SESSION['user_id'] ?? null;
}

function getUserRights() {
    return $_SESSION['rights'] ?? null; 
}
?>