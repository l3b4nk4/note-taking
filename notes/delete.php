<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

$user = getCurrentUser();

if (isset($_GET['id'])) {
    deleteUserNote($user['id'], (int) $_GET['id']);
}

header("Location: index.php");
exit();
?>
