<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'logout') {
    session_destroy();
    header('Location: /');
    exit;
}

require_once __DIR__ . '/../src/db_connection.php';

$userId = $_SESSION['user_id'];

$stmtUser = $conn->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmtUser->bind_param("i", $userId);
$stmtUser->execute();
$stmtUser->bind_result($username, $email, $createdAt);
$stmtUser->fetch();
$stmtUser->close();

$stmtGames = $conn->prepare("
    SELECT g.id, g.name, g.image, ug.date_added, ug.time_played
    FROM user_games ug
    JOIN games g ON g.id = ug.game_id
    WHERE ug.user_id = ?
    ORDER BY ug.date_added DESC
");
$stmtGames->bind_param("i", $userId);
$stmtGames->execute();
$gamesResult = $stmtGames->get_result();
$purchasedGames = $gamesResult->fetch_all(MYSQLI_ASSOC);
$stmtGames->close();

$totalAch = 0;
$unlockedAch = 0;
$totalPoints = 0;
$totalPlaytime = 0;
$gamesWithAchievements = [];

foreach ($purchasedGames as $game) {
    $gameId = $game['id'];
    $totalPlaytime += $game['time_played'];

    $stmtAch = $conn->prepare("
        SELECT a.id, a.name, a.description, a.icon, a.points,
               IF(ua.achievement_id IS NOT NULL, 1, 0) AS unlocked
        FROM achievements a
        LEFT JOIN user_achievements ua ON ua.achievement_id = a.id AND ua.user_id = ?
        WHERE a.game_id = ?
    ");
    $stmtAch->bind_param("ii", $userId, $gameId);
    $stmtAch->execute();
    $achResult = $stmtAch->get_result();
    $achievements = $achResult->fetch_all(MYSQLI_ASSOC);
    $stmtAch->close();

    foreach ($achievements as $ach) {
        $totalAch++;
        if ($ach['unlocked']) {
            $unlockedAch++;
            $totalPoints += $ach['points'];
        }
    }

    $gamesWithAchievements[] = array_merge($game, ['achievements' => $achievements]);
}

$conn->close();

$globalProgress = $totalAch > 0 ? round(($unlockedAch / $totalAch) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - <?php echo htmlspecialchars($username); ?></title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>
<div class="wrapper">
    <nav>
        <div class="nav-logo">🎮 Vapeur</div>
        <div class="nav-buttons">
            <a href="/home" class="nav-btn">Boutique</a>
            <form method="post" style="display:inline;">
                <input type="hidden" name="action" value="logout">
                <button type="submit" class="nav-btn">Déconnexion</button>
            </form>
        </div>
    </nav>

    <div class="profile-header">
        <div class="profile-content">
            <div class="profile-avatar">👾</div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($username); ?></h1>
                <p class="profile-email"><?php echo htmlspecialchars($email); ?></p>

                <div class="profile-stats">
                    <div class="stat">
                        <div class="stat-value"><?php echo count($purchasedGames); ?></div>
                        <div class="stat-label">Jeux possédés</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value"><?php echo $totalPlaytime; ?>h</div>
                        <div class="stat-label">Temps de jeu</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value"><?php echo $unlockedAch; ?>/<?php echo $totalAch; ?></div>
                        <div class="stat-label">Succès</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value"><?php echo $totalPoints; ?> pts</div>
                        <div class="stat-label">Points</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">Depuis <?php echo date('Y', strtotime($createdAt)); ?></div>
                        <div class="stat-label">Membre</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2 class="section-title">📊 Progression Globale</h2>
        <div class="progress-container">
            <div class="progress-label">
                <span>Succès débloqués</span>
                <span><?php echo $unlockedAch . ' / ' . $totalAch; ?></span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $globalProgress; ?>%;">
                    <?php echo $globalProgress; ?>%
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2 class="section-title">🎮 Mes Jeux</h2>
        <?php if (count($gamesWithAchievements) > 0): ?>
            <div class="games-container">
                <?php foreach ($gamesWithAchievements as $game): ?>
                    <div class="game-section">
                        <div class="game-header">
                            <div class="game-icon">
                                <?php if ($game['image']): ?>
                                    <img src="<?php echo htmlspecialchars($game['image']); ?>" alt="<?php echo htmlspecialchars($game['name']); ?>">
                                <?php else: ?>
                                    🎮
                                <?php endif; ?>
                            </div>
                            <div class="game-header-info">
                                <h2><?php echo htmlspecialchars($game['name']); ?></h2>
                                <p>Acheté le <?php echo date('d/m/Y', strtotime($game['date_added'])); ?></p>
                            </div>
                        </div>

                        <div class="game-body">
                            <div class="game-info-row">
                                <span class="info-label">Temps de jeu :</span>
                                <span class="info-value playtime"><?php echo $game['time_played']; ?>h</span>
                            </div>
                            <div class="game-info-row">
                                <span class="info-label">Succès :</span>
                                <span class="info-value"><?php
                                    $u = 0;
                                    foreach ($game['achievements'] as $ach) {
                                        if ($ach['unlocked']) $u++;
                                    }
                                    echo $u . ' / ' . count($game['achievements']);
                                    ?></span>
                            </div>

                            <?php if (count($game['achievements']) > 0): ?>
                                <div class="achievements-title">🏆 Succès & Réalisations</div>
                                <div class="achievements-grid">
                                    <?php foreach ($game['achievements'] as $achievement): ?>
                                        <div class="achievement <?php echo $achievement['unlocked'] ? 'unlocked' : ''; ?>">
                                            <div class="achievement-icon">
                                                <?php if ($achievement['icon']): ?>
                                                    <img src="<?php echo htmlspecialchars($achievement['icon']); ?>" alt="<?php echo htmlspecialchars($achievement['name']); ?>">
                                                <?php else: ?>
                                                    🏆
                                                <?php endif; ?>
                                            </div>
                                            <div class="achievement-name"><?php echo htmlspecialchars($achievement['name']); ?></div>
                                            <div class="achievement-desc"><?php echo htmlspecialchars($achievement['description']); ?></div>
                                            <div class="achievement-status <?php echo $achievement['unlocked'] ? 'achievement-unlocked' : 'achievement-locked'; ?>">
                                                <?php echo $achievement['unlocked'] ? '✓ Débloqué' : '🔒 Verrouillé'; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="no-achievements">Aucun succès disponible pour ce jeu.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-message">
                <p>Tu n'as pas encore acheté de jeux. Visite la boutique pour commencer ! 🛍️</p>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>© 2024 Vapeur • Plateforme de Jeux Innovants ⛏️</p>
    </footer>
</div>
</body>
</html>