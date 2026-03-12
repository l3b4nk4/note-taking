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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category = trim($_POST['category']);

    if (!empty($title)) {
        if (updateUserNote($user['id'], $note_id, [
            'title' => $title,
            'content' => $content,
            'category' => $category,
        ])) {
            header("Location: index.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Note - NoteTaking</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body id="edit">
    <header class="page-header">
        <div class="container">
            <div class="header-row">
                <a href="../" class="brand-link">NoteTaking</a>
                <nav class="nav-links">
                    <span>Welcome, <?php echo htmlspecialchars($user['username']); ?></span>
                    <a href="index.php" class="button">Back to Notes</a>
                    <a href="../logout.php" class="button">Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h1>Edit Note</h1>
        </div>

        <form method="POST" class="card">
            <div class="grid">
                <div class="form-field">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title" required
                        value="<?php echo htmlspecialchars($note['title']); ?>">
                </div>

                <div class="form-field">
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category" list="categories"
                        value="<?php echo htmlspecialchars($note['category']); ?>">
                    <datalist id="categories">
                        <option value="Personal">
                        <option value="Work">
                        <option value="Ideas">
                        <option value="To-Do">
                        <option value="Study">
                    </datalist>
                </div>
            </div>

            <div class="form-field">
                <label for="content">Content</label>
                <textarea id="content" name="content" rows="15"><?php echo htmlspecialchars($note['content']); ?></textarea>
            </div>

            <div class="row">
                <button type="submit" class="button primary">
                    Update Note
                </button>
                <a href="index.php" class="button">Cancel</a>
            </div>
        </form>
    </div>

    <script src="../assets/script.js"></script>
</body>

</html>