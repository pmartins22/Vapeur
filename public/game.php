<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/../src/db_connection.php';

$userId = $_SESSION['user_id'];
$uriParts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$gameId = (int) end($uriParts);

if (!$gameId) {
    header('Location: /home');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'buy_game') {
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

$stmtGame = $conn->prepare("SELECT id, name, type, description, image, difficulty, price, year FROM games WHERE id = ?");
$stmtGame->bind_param("i", $gameId);
$stmtGame->execute();
$gameRow = $stmtGame->get_result()->fetch_assoc();
$stmtGame->close();

if (!$gameRow) {
    header('Location: /home');
    exit;
}

$checkOwned = $conn->prepare("SELECT id FROM user_games WHERE user_id = ? AND game_id = ?");
$checkOwned->bind_param("ii", $userId, $gameId);
$checkOwned->execute();
$checkOwned->store_result();
$owned = $checkOwned->num_rows > 0;
$checkOwned->close();

$stmtAch = $conn->prepare("
    SELECT a.id, a.name, a.description, a.icon, a.points,
           IF(ua.achievement_id IS NOT NULL, 1, 0) AS unlocked
    FROM achievements a
    LEFT JOIN user_achievements ua ON ua.achievement_id = a.id AND ua.user_id = ?
    WHERE a.game_id = ?
");
$stmtAch->bind_param("ii", $userId, $gameId);
$stmtAch->execute();
$achievements = $stmtAch->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtAch->close();

$conn->close();

$unlockedAch = 0;
$totalPoints = 0;
foreach ($achievements as $ach) {
    if ($ach['unlocked']) {
        $unlockedAch++;
        $totalPoints += $ach['points'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vapeur - <?php echo htmlspecialchars($gameRow['name']); ?></title>
    <link rel="stylesheet" href="/game.css">
</head>
<body>
<div class="wrapper">
    <nav>
        <div class="nav-logo">🎮 Vapeur</div>
        <div class="nav-buttons">
            <a href="/home" class="nav-btn">Boutique</a>
        </div>
    </nav>

    <div class="game-hero">
        <div class="game-hero-image">
            <?php if ($gameRow['image']): ?>
                <img src="<?php echo htmlspecialchars($gameRow['image']); ?>" alt="<?php echo htmlspecialchars($gameRow['name']); ?>">
            <?php else: ?>
                <span>🎮</span>
            <?php endif; ?>
        </div>

        <div class="game-hero-info">
            <div class="game-type-badge"><?php echo htmlspecialchars($gameRow['type']); ?></div>
            <h1><?php echo htmlspecialchars($gameRow['name']); ?></h1>
            <p class="game-hero-description"><?php echo htmlspecialchars($gameRow['description']); ?></p>

            <div class="game-hero-stats">
                <div class="stat">
                    <div class="stat-value">€<?php echo number_format($gameRow['price'], 2); ?></div>
                    <div class="stat-label">Prix</div>
                </div>
                <div class="stat">
                    <div class="stat-value"><?php echo htmlspecialchars($gameRow['difficulty']); ?></div>
                    <div class="stat-label">Difficulté</div>
                </div>
                <div class="stat">
                    <div class="stat-value"><?php echo htmlspecialchars($gameRow['year']); ?></div>
                    <div class="stat-label">Année</div>
                </div>
                <div class="stat">
                    <div class="stat-value"><?php echo count($achievements); ?></div>
                    <div class="stat-label">Succès</div>
                </div>
            </div>

            <div class="buy-section">
                <form method="post">
                    <input type="hidden" name="action" value="buy_game">
                    <input type="hidden" name="game_id" value="<?php echo $gameRow['id']; ?>">
                    <button type="submit"
                            class="buy-btn <?php echo $owned ? 'buy-btn-owned' : ''; ?>"
                        <?php echo $owned ? 'disabled' : ''; ?>>
                        <?php echo $owned ? '✓ Acheté' : 'Acheter — €' . number_format($gameRow['price'], 2); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php if (count($achievements) > 0): ?>
        <div class="section">
            <h2 class="section-title">🏆 Succès & Réalisations</h2>

            <?php if ($owned): ?>
                <div class="achievements-summary">
                    <span><?php echo $unlockedAch; ?> / <?php echo count($achievements); ?> débloqués</span>
                    <span><?php echo $totalPoints; ?> pts</span>
                </div>
            <?php endif; ?>

            <div class="achievements-grid">
                <?php foreach ($achievements as $achievement): ?>
                    <div class="achievement <?php echo ($owned && $achievement['unlocked']) ? 'unlocked' : ''; ?> <?php echo !$owned ? 'locked-preview' : ''; ?>">
                        <div class="achievement-icon">
                            <?php if ($achievement['icon']): ?>
                                <img src="<?php echo htmlspecialchars($achievement['icon']); ?>" alt="<?php echo htmlspecialchars($achievement['name']); ?>">
                            <?php else: ?>
                                🏆
                            <?php endif; ?>
                        </div>
                        <div class="achievement-name"><?php echo htmlspecialchars($achievement['name']); ?></div>
                        <div class="achievement-desc"><?php echo htmlspecialchars($achievement['description']); ?></div>
                        <div class="achievement-points"><?php echo $achievement['points']; ?> pts</div>
                        <?php if ($owned): ?>
                            <div class="achievement-status <?php echo $achievement['unlocked'] ? 'achievement-unlocked' : 'achievement-locked'; ?>">
                                <?php echo $achievement['unlocked'] ? '✓ Débloqué' : '🔒 Verrouillé'; ?>
                            </div>
                        <?php else: ?>
                            <div class="achievement-status achievement-locked">🔒 Achetez pour débloquer</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <footer>
        <p>© 2024 Vapeur • Plateforme de Jeux Innovants ⛏️</p>
    </footer>
</div>
</body>
</html>