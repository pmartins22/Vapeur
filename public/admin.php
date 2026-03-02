<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header('Location: /home');
    exit;
}

require_once __DIR__ . '/../src/db_connection.php';

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {

        if ($_POST['action'] === 'ban_user') {
            $userId = (int) $_POST['user_id'];
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
            $stmt->bind_param("i", $userId);
            if ($stmt->execute()) {
                $message = "User banned successfully.";
                $messageType = "success";
            } else {
                $message = "Error banning user.";
                $messageType = "danger";
            }
            $stmt->close();
        }

        if ($_POST['action'] === 'delete_game') {
            $gameId = (int) $_POST['game_id'];
            $stmt = $conn->prepare("DELETE FROM games WHERE id = ?");
            $stmt->bind_param("i", $gameId);
            if ($stmt->execute()) {
                $message = "Game deleted successfully.";
                $messageType = "success";
            } else {
                $message = "Error deleting game.";
                $messageType = "danger";
            }
            $stmt->close();
        }

        if ($_POST['action'] === 'add_game') {
            $name        = $_POST['name'];
            $type        = $_POST['type'];
            $description = $_POST['description'];
            $image       = $_POST['image'];
            $difficulty  = $_POST['difficulty'];
            $price       = (float) $_POST['price'];
            $year        = (int) $_POST['year'];

            $stmt = $conn->prepare("INSERT INTO games (name, type, description, image, difficulty, price, year) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssdi", $name, $type, $description, $image, $difficulty, $price, $year);
            if ($stmt->execute()) {
                $message = "Game added successfully.";
                $messageType = "success";
                $lastGameId = $conn->insert_id;

                if (!empty($_POST['achievement_name'][0])) {
                    $achStmt = $conn->prepare("INSERT INTO achievements (game_id, name, description, icon, points) VALUES (?, ?, ?, ?, ?)");
                    foreach ($_POST['achievement_name'] as $i => $achName) {
                        if (empty($achName)) continue;
                        $achDesc   = $_POST['achievement_desc'][$i] ?? '';
                        $achIcon   = $_POST['achievement_icon'][$i] ?? '';
                        $achPoints = (int) ($_POST['achievement_points'][$i] ?? 0);
                        $achStmt->bind_param("isssi", $lastGameId, $achName, $achDesc, $achIcon, $achPoints);
                        $achStmt->execute();
                    }
                    $achStmt->close();
                }
            } else {
                $message = "Error adding game.";
                $messageType = "danger";
            }
            $stmt->close();
        }
    }
}

$users = $conn->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
$games = $conn->query("SELECT id, name, type, difficulty, price, year FROM games ORDER BY created_at DESC");
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vapeur - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/admin.css">
</head>
<body>
<div class="decoration deco-1">🧱</div>
<div class="decoration deco-2">⛏️</div>

<div class="admin-wrapper">
    <header class="admin-header">
        <h1>Admin Panel</h1>
        <a href="/home" class="btn btn-back">Back to Home</a>
    </header>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="admin-grid">
        <section class="panel">
            <h2>Add New Game</h2>
            <form method="post">
                <input type="hidden" name="action" value="add_game">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <input type="text" name="type" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Image URL</label>
                    <input type="text" name="image" class="form-control">
                </div>
                <div class="form-group">
                    <label>Difficulty</label>
                    <select name="difficulty" class="form-control" required>
                        <option value="facile">Facile</option>
                        <option value="moyen">Moyen</option>
                        <option value="difficile">Difficile</option>
                        <option value="expert">Expert</option>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Price (EUR)</label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Year</label>
                        <input type="number" name="year" class="form-control" min="1970" max="2099" required>
                    </div>
                </div>

                <h3>Achievements</h3>
                <div id="achievements-list">
                    <div class="achievement-row">
                        <input type="text"   name="achievement_name[]"   placeholder="Name"        class="form-control">
                        <input type="text"   name="achievement_desc[]"   placeholder="Description" class="form-control">
                        <input type="text"   name="achievement_icon[]"   placeholder="Icon URL"    class="form-control">
                        <input type="number" name="achievement_points[]" placeholder="Points"      class="form-control">
                    </div>
                </div>
                <button type="button" class="btn btn-secondary" onclick="addAchievement()">+ Add Achievement</button>
                <button type="submit" class="btn btn-primary">Add Game</button>
            </form>
        </section>

        <section class="panel">
            <h2>Users</h2>
            <div class="users-list">
                <?php while ($user = $users->fetch_assoc()): ?>
                    <div class="user-row">
                        <div class="user-info">
                            <span class="user-name"><?php echo htmlspecialchars($user['username']); ?></span>
                            <span class="user-email"><?php echo htmlspecialchars($user['email']); ?></span>
                            <span class="user-meta">
                                    <span class="badge badge-<?php echo $user['role']; ?>"><?php echo $user['role']; ?></span>
                                    <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                                </span>
                        </div>
                        <?php if ($user['role'] !== 'admin'): ?>
                            <form method="post" onsubmit="return confirm('Ban <?php echo htmlspecialchars($user['username']); ?>?')">
                                <input type="hidden" name="action" value="ban_user">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="btn btn-danger">Ban</button>
                            </form>
                        <?php else: ?>
                            <span class="protected">Protected</span>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <section class="panel">
            <h2>Delete Game</h2>
            <div class="users-list">
                <?php while ($game = $games->fetch_assoc()): ?>
                    <div class="user-row">
                        <div class="user-info">
                            <span class="user-name"><?php echo htmlspecialchars($game['name']); ?></span>
                            <span class="user-meta">
                                    <span class="badge badge-user"><?php echo htmlspecialchars($game['type']); ?></span>
                                    <?php echo htmlspecialchars($game['difficulty']); ?>
                                    &middot; EUR <?php echo number_format($game['price'], 2); ?>
                                    &middot; <?php echo htmlspecialchars($game['year']); ?>
                                </span>
                        </div>
                        <form method="post" onsubmit="return confirm('Delete <?php echo htmlspecialchars($game['name']); ?>?')">
                            <input type="hidden" name="action" value="delete_game">
                            <input type="hidden" name="game_id" value="<?php echo $game['id']; ?>">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
    </div>
</div>

<script>
    function addAchievement() {
        const list = document.getElementById('achievements-list');
        const row = document.createElement('div');
        row.className = 'achievement-row';
        row.innerHTML = `
                <input type="text"   name="achievement_name[]"   placeholder="Name"        class="form-control">
                <input type="text"   name="achievement_desc[]"   placeholder="Description" class="form-control">
                <input type="text"   name="achievement_icon[]"   placeholder="Icon URL"    class="form-control">
                <input type="number" name="achievement_points[]" placeholder="Points"      class="form-control">
                <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">x</button>
            `;
        list.appendChild(row);
    }
</script>
</body>
</html>