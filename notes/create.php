<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category = trim($_POST['category']);

    if (!empty($title)) {
        if (createUserNote($user['id'], [
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
    <title>Create Note - NoteTaking</title>
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
            <h1>Create New Note</h1>
        </div>

        <form method="POST" class="card">
            <div class="grid">
                <div class="form-field">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title" required 
                           value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                </div>

                <div class="form-field">
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category" list="categories"
                           value="<?php echo isset($_POST['category']) ? htmlspecialchars($_POST['category']) : ''; ?>">
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
                <textarea id="content" name="content" rows="15" 
                          placeholder="Start typing your note here..."><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
            </div>

            <div class="row">
                <button type="submit" class="button primary">
                    Create Note
                </button>
                <a href="index.php" class="button">Cancel</a>
            </div>
        </form>
    </div>

    <script src="../assets/script.js"></script>
</body>
</html>
