<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/../src/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'buy_game') {
    $gameId = (int) $_POST['game_id'];
    $userId = $_SESSION['user_id'];

    $check = $conn->prepare("SELECT id FROM user_games WHERE user_id = ? AND game_id = ?");
    $check->bind_param("ii", $userId, $gameId);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $timePlayed = rand(0, 100);

        $stmt = $conn->prepare("INSERT INTO user_games (user_id, game_id, time_played) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $userId, $gameId, $timePlayed);
        $stmt->execute();
        $stmt->close();

        $achResult = $conn->prepare("SELECT id FROM achievements WHERE game_id = ?");
        $achResult->bind_param("i", $gameId);
        $achResult->execute();
        $achievements = $achResult->get_result()->fetch_all(MYSQLI_ASSOC);
        $achResult->close();

        foreach ($achievements as $ach) {
            if (rand(0, 1)) {
                $unlock = $conn->prepare("INSERT IGNORE INTO user_achievements (user_id, achievement_id) VALUES (?, ?)");
                $unlock->bind_param("ii", $userId, $ach['id']);
                $unlock->execute();
                $unlock->close();
            }
        }
    }

    $check->close();
}

$userId = $_SESSION['user_id'];

$ownedResult = $conn->prepare("SELECT game_id FROM user_games WHERE user_id = ?");
$ownedResult->bind_param("i", $userId);
$ownedResult->execute();
$ownedRows = $ownedResult->get_result();
$ownedGames = [];
while ($row = $ownedRows->fetch_assoc()) {
    $ownedGames[] = $row['game_id'];
}
$ownedResult->close();

$games = $conn->query("SELECT id, name, type, description, image, difficulty, price, year FROM games ORDER BY created_at DESC");
$conn->close();

function getTagColor($category) {
    $colors = [
            'Action'     => '#ff6b6b',
            'Aventure'   => '#7eca58',
            'Sci-Fi'     => '#4ecdc4',
            'Gestion'    => '#ffd93d',
            'Survie'     => '#a29bfe',
            'Stratégie'  => '#fd79a8',
            'Cyberpunk'  => '#ff7675',
            'RPG'        => '#74b9ff',
            'Roguelike'  => '#fdcb6e',
            'Enquête'    => '#55efc4',
            'Puzzle'     => '#fab1a0',
            'Course'     => '#ff9ff3',
            'Simulation' => '#54a0ff',
    ];
    
    foreach ($colors as $key => $color) {
        if (stripos($category, $key) !== false) {
            return $color;
        }
    }
    return '#7eca58';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vapeur - Home</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>
<div class="wrapper">
    <header>
        <div class="header-nav">
            <a href="/profile" class="profile-btn">👤 Mon Profil</a>
        </div>
        <h1>🎮 Vapeur</h1>
        <p class="subtitle">Une collection de titres innovants et immersifs</p>
        <div class="games-count"><?php echo $games->num_rows; ?> jeux disponibles</div>
    </header>

    <div class="games-grid">
        <?php while ($game = $games->fetch_assoc()): ?>
            <?php $owned = in_array($game['id'], $ownedGames); ?>
            <div class="game-card">
                <div class="game-image">
                    <?php if ($game['image']): ?>
                        <img src="<?php echo htmlspecialchars($game['image']); ?>" alt="<?php echo htmlspecialchars($game['name']); ?>">
                    <?php else: ?>
                        <span>🎮</span>
                    <?php endif; ?>
                </div>

                <div class="game-content">
                    <div class="game-category" style="background-color: <?php echo getTagColor($game['type']); ?>;">
                        <?php echo htmlspecialchars($game['type']); ?>
                    </div>
                    <h2 class="game-title"><?php echo htmlspecialchars($game['name']); ?></h2>
                    <p class="game-description"><?php echo htmlspecialchars($game['description']); ?></p>

                    <div class="game-meta">
                        <div class="meta-row">
                            <span class="meta-label">Prix :</span>
                            <span class="price">€<?php echo number_format($game['price'], 2); ?></span>
                        </div>
                        <div class="meta-row">
                            <span class="meta-label">Difficulté :</span>
                            <span class="meta-value"><?php echo htmlspecialchars($game['difficulty']); ?></span>
                        </div>
                        <div class="meta-row">
                            <span class="meta-label">Année :</span>
                            <span class="meta-value"><?php echo htmlspecialchars($game['year']); ?></span>
                        </div>
                    </div>

                    <div class="game-buttons">
                        <form method="post" style="flex: 1;">
                            <input type="hidden" name="action" value="buy_game">
                            <input type="hidden" name="game_id" value="<?php echo $game['id']; ?>">
                            <button type="submit"
                                    class="game-button <?php echo $owned ? 'game-button-owned' : ''; ?>"
                                    <?php echo $owned ? 'disabled' : ''; ?>>
                                <?php echo $owned ? 'Acheté' : 'Acheter'; ?>
                            </button>
                        </form>
                        <a href="/game/<?php echo $game['id']; ?>" class="game-button game-button-secondary">Infos</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <footer>
        <p>© 2024 Vapeur • Conçu avec passion ⛏️</p>
    </footer>
</div>
</body>
</html>