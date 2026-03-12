<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

$user = getCurrentUser();
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$notes = getUserNotes($user['id'], $search, $category);
$categories = getUserCategories($user['id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Notes - NoteTaking</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body id="notes">
    <header class="page-header">
        <div class="container">
            <div class="header-row">
                <a href="../" class="brand-link">NoteTaking</a>
                <nav class="nav-links">
                    <span>Welcome, <?php echo htmlspecialchars($user['username']); ?></span>
                    <a href="create.php" class="button primary">
                        New Note
                    </a>
                    <a href="../logout.php" class="button">Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h1>My Notes</h1>
        </div>

        <div class="card">
            <form method="GET">
                <div class="row">
                    <input type="text" name="search" placeholder="Search notes..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="button primary">Search</button>
                </div>
                
                <select name="category" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>"
                            <?php echo $category == $cat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <?php if (!empty($search) || !empty($category)): ?>
                    <a href="index.php" class="button">Clear Filters</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="grid">
            <?php if (empty($notes)): ?>
                <div class="card">
                    <h3>No notes found</h3>
                    <p><?php echo empty($search) ? 'Create your first note to get started!' : 'Try adjusting your search criteria'; ?></p>
                    <?php if (empty($search)): ?>
                        <a href="create.php" class="button primary">Create Note</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($notes as $note): ?>
                    <div class="card note-card">
                        <div>
                            <h3><?php echo htmlspecialchars($note['title']); ?></h3>
                            <div>
                                <a href="view.php?id=<?php echo $note['id']; ?>" title="View" class="button button-small">
                                    View
                                </a>
                                <a href="edit.php?id=<?php echo $note['id']; ?>" title="Edit" class="button button-small">
                                    Edit
                                </a>
                                <a href="delete.php?id=<?php echo $note['id']; ?>" 
                                   title="Delete" 
                                   onclick="return confirm('Are you sure?')"
                                   class="button button-small button-danger">
                                    Delete
                                </a>
                            </div>
                        </div>

                        <div class="content-text">
                            <?php echo nl2br(htmlspecialchars(substr($note['content'], 0, 200))); ?>
                            <?php if (strlen($note['content']) > 200): ?>...<?php endif; ?>
                        </div>

                        <?php if (!empty($note['category'])): ?>
                            <div>
                                <span class="badge"><?php echo htmlspecialchars($note['category']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/script.js"></script>
</body>

</html>
