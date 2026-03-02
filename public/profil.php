<?php
session_start();

require_once __DIR__ . '/../src/db_connection.php';

if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'username' => 'NeonRider',
        'level' => 15,
        'totalPlaytime' => 245,
        'joinDate' => '2023-06-15',
        'bio' => 'Passionné de jeux innovants et d\'aventures sci-fi 🚀'
    ];
}

$user = $_SESSION['user'];

$purchasedGames = [
    [
        'id' => 1,
        'title' => 'Echoes of Tomorrow',
        'emoji' => '⏰',
        'purchaseDate' => '2024-01-15',
        'playtime' => 87,
        'achievements' => [
            ['name' => 'Première Boucle', 'description' => 'Compléter le premier arc temporel', 'unlocked' => true, 'icon' => '🌀'],
            ['name' => 'Paradoxe Maîtrisé', 'description' => 'Résoudre 10 énigmes temporelles', 'unlocked' => true, 'icon' => '🧩'],
            ['name' => 'Archiviste Légendaire', 'description' => 'Trouver tous les secrets temporels', 'unlocked' => false, 'icon' => '👑'],
            ['name' => 'Voyageur Temporel', 'description' => 'Compléter le jeu en mode difficile', 'unlocked' => false, 'icon' => '⏱️'],
        ]
    ],
    [
        'id' => 4,
        'title' => 'Mythforge Arena',
        'emoji' => '⚡',
        'purchaseDate' => '2024-02-10',
        'playtime' => 156,
        'achievements' => [
            ['name' => 'Champion Débutant', 'description' => "Gagner 5 combats d'arène", 'unlocked' => true, 'icon' => '🏆'],
            ['name' => 'Maître des Dieux', 'description' => 'Débloquer tous les pouvoirs mythologiques', 'unlocked' => true, 'icon' => '⚡'],
            ['name' => 'Invincible', 'description' => 'Gagner 50 combats consécutifs', 'unlocked' => true, 'icon' => '🔥'],
            ['name' => 'Légende Vivante', 'description' => 'Atteindre le rang suprême', 'unlocked' => false, 'icon' => '👑'],
        ]
    ],
    [
        'id' => 6,
        'title' => 'Drift Protocol',
        'emoji' => '🏎️',
        'purchaseDate' => '2024-03-05',
        'playtime' => 203,
        'achievements' => [
            ['name' => 'Premier Drift', 'description' => 'Compléter ta première course', 'unlocked' => true, 'icon' => '🏁'],
            ['name' => 'Pilote Chevronné', 'description' => 'Remporter 10 courses', 'unlocked' => true, 'icon' => '🥇'],
            ['name' => 'Maître du Drift', 'description' => 'Obtenir le temps parfait sur 5 circuits', 'unlocked' => true, 'icon' => '💫'],
            ['name' => 'Champion Interdimensionnel', 'description' => 'Gagner le championnat complet', 'unlocked' => false, 'icon' => '👑'],
        ]
    ]
];

function calculateGlobalProgress($games) {
    $totalAchievements = 0;
    $unlockedAchievements = 0;
    foreach ($games as $game) {
        foreach ($game['achievements'] as $achievement) {
            $totalAchievements++;
            if ($achievement['unlocked']) $unlockedAchievements++;
        }
    }
    return $totalAchievements > 0 ? round(($unlockedAchievements / $totalAchievements) * 100) : 0;
}

$globalProgress = calculateGlobalProgress($purchasedGames);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - <?php echo htmlspecialchars($user['username']); ?></title>
    <link rel="stylesheet" href="profil.css">
</head>
<body>
    <div class="wrapper">
        <!-- Navigation -->
        <nav>
            <div class="nav-logo">🎮 Gameverse</div>
            <div class="nav-buttons">
                <a href="/home" class="nav-btn">Boutique</a>
                <button class="nav-btn">Déconnexion</button>
            </div>
        </nav>

        <!-- Profil Header -->
        <div class="profile-header">
            <div class="profile-content">
                <div class="profile-avatar">👾</div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($user['username']); ?></h1>
                    <div class="profile-badge">Niveau <?php echo $user['level']; ?></div>
                    <p class="profile-bio"><?php echo htmlspecialchars($user['bio']); ?></p>

                    <div class="profile-stats">
                        <div class="stat">
                            <div class="stat-value"><?php echo count($purchasedGames); ?></div>
                            <div class="stat-label">Jeux possédés</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value"><?php echo $user['totalPlaytime']; ?>h</div>
                            <div class="stat-label">Temps de jeu</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value"><?php echo $globalProgress; ?>%</div>
                            <div class="stat-label">Succès</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value">Depuis <?php echo date('Y', strtotime($user['joinDate'])); ?></div>
                            <div class="stat-label">Membre</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progression Globale -->
        <div class="section">
            <h2 class="section-title">📊 Progression Globale</h2>
            <div class="progress-container">
                <div class="progress-label">
                    <span>Succès débloqués</span>
                    <span><?php
                        $totalAch = 0;
                        $unlockedAch = 0;
                        foreach ($purchasedGames as $game) {
                            foreach ($game['achievements'] as $ach) {
                                $totalAch++;
                                if ($ach['unlocked']) $unlockedAch++;
                            }
                        }
                        echo $unlockedAch . ' / ' . $totalAch;
                    ?></span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $globalProgress; ?>%;">
                        <?php echo $globalProgress; ?>%
                    </div>
                </div>
            </div>
        </div>

        <!-- Jeux Achetés -->
        <div class="section">
            <h2 class="section-title">🎮 Mes Jeux</h2>
            <?php if (count($purchasedGames) > 0): ?>
                <div class="games-container">
                    <?php foreach ($purchasedGames as $game): ?>
                        <div class="game-section">
                            <div class="game-header">
                                <div class="game-icon"><?php echo $game['emoji']; ?></div>
                                <div class="game-header-info">
                                    <h2><?php echo htmlspecialchars($game['title']); ?></h2>
                                    <p>Acheté le <?php echo date('d/m/Y', strtotime($game['purchaseDate'])); ?></p>
                                </div>
                            </div>

                            <div class="game-body">
                                <div class="game-info-row">
                                    <span class="info-label">Temps de jeu :</span>
                                    <span class="info-value playtime"><?php echo $game['playtime']; ?> heures</span>
                                </div>
                                <div class="game-info-row">
                                    <span class="info-label">Succès :</span>
                                    <span class="info-value"><?php
                                        $unlocked = 0;
                                        foreach ($game['achievements'] as $ach) {
                                            if ($ach['unlocked']) $unlocked++;
                                        }
                                        echo $unlocked . ' / ' . count($game['achievements']);
                                    ?></span>
                                </div>

                                <div class="achievements-title">
                                    🏆 Succès & Réalisations
                                </div>
                                <div class="achievements-grid">
                                    <?php foreach ($game['achievements'] as $achievement): ?>
                                        <div class="achievement <?php echo $achievement['unlocked'] ? 'unlocked' : ''; ?>">
                                            <div class="achievement-icon"><?php echo $achievement['icon']; ?></div>
                                            <div class="achievement-name"><?php echo htmlspecialchars($achievement['name']); ?></div>
                                            <div class="achievement-desc"><?php echo htmlspecialchars($achievement['description']); ?></div>
                                            <div class="achievement-status <?php echo $achievement['unlocked'] ? 'achievement-unlocked' : 'achievement-locked'; ?>">
                                                <?php echo $achievement['unlocked'] ? '✓ Débloqué' : '🔒 Verrouillé'; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
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

        <!-- Footer -->
        <footer>
            <p>© 2024 Gamverse • Plateforme de Jeux Innovants ⛏️</p>
        </footer>
    </div>
</body>
</html>