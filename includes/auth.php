<?php
require_once 'config.php';

function isLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    if (!findStoredUserById($_SESSION['user_id'])) {
        unset($_SESSION['user_id'], $_SESSION['username']);
        return false;
    }

    return true;
}

function findStoredUserById($userId) {
    $data = readStorageData();

    foreach ($data['users'] as $user) {
        if ((int) ($user['id'] ?? 0) === (int) $userId) {
            return $user;
        }
    }

    return null;
}

function findStoredUserByLogin($usernameOrEmail) {
    $value = trim((string) $usernameOrEmail);
    $data = readStorageData();

    foreach ($data['users'] as $user) {
        if (
            strcasecmp((string) ($user['username'] ?? ''), $value) === 0 ||
            strcasecmp((string) ($user['email'] ?? ''), $value) === 0
        ) {
            return $user;
        }
    }

    return null;
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }

    $user = findStoredUserById($_SESSION['user_id']);

    if (!$user) {
        unset($_SESSION['user_id'], $_SESSION['username']);
        return null;
    }

    return [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
    ];
}

function loginUser($username, $password) {
    $user = findStoredUserByLogin($username);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }

    return false;
}

function registerUser($username, $email, $password) {
    $username = trim((string) $username);
    $email = trim((string) $email);
    $data = readStorageData();

    foreach ($data['users'] as $user) {
        if (
            strcasecmp((string) ($user['username'] ?? ''), $username) === 0 ||
            strcasecmp((string) ($user['email'] ?? ''), $email) === 0
        ) {
            return false;
        }
    }

    $data['users'][] = [
        'id' => nextStorageId($data, 'last_user_id'),
        'username' => $username,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
    ];

    return writeStorageData($data);
}

function logoutUser() {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
    header('Location: login.php');
    exit();
}
?>
