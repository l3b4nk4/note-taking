<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

$user = getCurrentUser();
$note_id = (int) ($_GET['id'] ?? 0);
$note = findUserNoteById($user['id'], $note_id);

if (!$note) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($note['title']); ?> - NoteTaking</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body id="view">
    <header class="page-header">
        <div class="container">
            <div class="header-row">
                <a href="../" class="brand-link">NoteTaking</a>
                <nav class="nav-links">
                    <span>Welcome, <?php echo htmlspecialchars($user['username']); ?></span>
                    <a href="edit.php?id=<?php echo $note['id']; ?>" class="button primary">Edit</a>
                    <a href="index.php" class="button">Back to Notes</a>
                    <a href="../logout.php" class="button">Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <article class="card">
            <header>
                <h1><?php echo htmlspecialchars($note['title']); ?></h1>
                <div>
                    <?php if (!empty($note['category'])): ?>
                        <span class="badge"><?php echo htmlspecialchars($note['category']); ?></span>
                    <?php endif; ?>
                </div>
            </header>

            <div class="content-text">
                <?php echo nl2br(htmlspecialchars($note['content'])); ?>
            </div>

            <div class="row">
                <a href="edit.php?id=<?php echo $note['id']; ?>" class="button primary">
                    Edit Note
                </a>
                <a href="index.php" class="button">Back to Notes</a>
            </div>
        </article>
    </div>

    <script src="../assets/script.js"></script>
</body>
</html>
