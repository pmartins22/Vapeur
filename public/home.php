<?php
// Données des jeux
$games = [
    [
        'id' => 1,
        'title' => 'Echoes of Tomorrow',
        'image' => 'assets/echoes-of-tomorrow.png',
        'description' => 'Un archiviste temporel explore les souvenirs du futur dans une boucle paradoxale.',
        'category' => 'Action / Aventure',
        'price' => '39,99 €',
        'coop' => 'Non',
        'year' => '2024'
    ],
    [
        'id' => 2,
        'title' => 'Project: Deep Colony',
        'image' => '../assets/deep-colony.png',
        'description' => 'Survie en colonie sous une planète océanique qui apprend de tes erreurs.',
        'category' => 'Gestion / Survie',
        'price' => '29,99 €',
        'coop' => 'Oui (4j)',
        'year' => '2024'
    ],
    [
        'id' => 3,
        'title' => 'Neon Veil',
        'image' => '../assets/neon-veil.png',
        'description' => 'Voleur d\'identités mentales infiltrant des mégalopoles cyberpunk.',
        'category' => 'Cyberpunk / RPG',
        'price' => '44,99 €',
        'coop' => 'Non',
        'year' => '2024'
    ],
    [
        'id' => 4,
        'title' => 'Mythforge Arena',
        'image' => '../assets/mythforge-arena.png',
        'description' => 'Roguelike avec pouvoirs de différentes mythologies fusionnées.',
        'category' => 'Action / Roguelike',
        'price' => '24,99 €',
        'coop' => 'Oui (3j)',
        'year' => '2024'
    ],
    [
        'id' => 5,
        'title' => 'The Last Librarian',
        'image' => '../assets/the-last-librarian.png',
        'description' => 'Enquête puzzle où chaque livre débloque une mécanique unique.',
        'category' => 'Enquête / Puzzle',
        'price' => '19,99 €',
        'coop' => 'Non',
        'year' => '2024'
    ],
    [
        'id' => 6,
        'title' => 'Drift Protocol',
        'image' => '../assets/drift-protocol.png',
        'description' => 'Courses interdimensionnelles où les circuits se transforment en temps réel.',
        'category' => 'Course / Sci-Fi',
        'price' => '34,99 €',
        'coop' => 'Oui (12j)',
        'year' => '2024'
    ],
    [
        'id' => 7,
        'title' => 'Eden.exe',
        'image' => '../assets/eden-exe.png',
        'description' => 'Simulation où tu crées une IA qui remet en question tes décisions.',
        'category' => 'Simulation / IA',
        'price' => '27,99 €',
        'coop' => 'Non',
        'year' => '2024'
    ]
];

// Fonction pour obtenir la couleur du tag selon la catégorie
function getTagColor($category) {
    $colors = [
        'Action' => '#ff6b6b',
        'Aventure' => '#7eca58',
        'Sci-Fi' => '#4ecdc4',
        'Gestion' => '#ffd93d',
        'Survie' => '#a29bfe',
        'Stratégie' => '#fd79a8',
        'Cyberpunk' => '#ff7675',
        'RPG' => '#74b9ff',
        'Infiltration' => '#9f8ad6',
        'Roguelike' => '#fdcb6e',
        'Mythologie' => '#e17055',
        'Enquête' => '#55efc4',
        'Puzzle' => '#fab1a0',
        'Course' => '#ff9ff3',
        'Simulation' => '#54a0ff',
        'IA' => '#48dbfb'
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
    <title>Page de jeu - Studio Création</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>
    <div class="wrapper">
        <header>
            <h1>🎮 Page de jeu</h1>
            <p class="subtitle">Une collection de titres innovants et immersifs</p>
            <div class="games-count"><?php echo count($games); ?> jeux disponibles</div>
        </header>

        <div class="games-grid">
            <?php foreach ($games as $game): ?>
                <div class="game-card">
                    <div class="game-image">
                        <?php 
                            $ext = pathinfo($game['image'], PATHINFO_EXTENSION);
                            if ($ext === 'html') {
                                echo '<iframe src="' . htmlspecialchars($game['image']) . '" class="game-preview" title="' . htmlspecialchars($game['title']) . '"></iframe>';
                            } else {
                                echo '<img src="' . htmlspecialchars($game['image']) . '" alt="' . htmlspecialchars($game['title']) . '">';
                            }
                        ?>
                    </div>
                    
                    <div class="game-content">
                        <div class="game-category" style="background-color: <?php echo getTagColor($game['category']); ?>;">
                            <?php echo $game['category']; ?>
                        </div>
                        <h2 class="game-title"><?php echo htmlspecialchars($game['title']); ?></h2>
                        <p class="game-description"><?php echo htmlspecialchars($game['description']); ?></p>
                        
                        <div class="game-meta">
                            <div class="meta-row">
                                <span class="meta-label">Prix :</span>
                                <span class="price"><?php echo $game['price']; ?></span>
                            </div>
                            <div class="meta-row">
                                <span class="meta-label">Coop :</span>
                                <span class="meta-value"><?php echo htmlspecialchars($game['coop']); ?></span>
                            </div>
                            <div class="meta-row">
                                <span class="meta-label">Année :</span>
                                <span class="meta-value"><?php echo $game['year']; ?></span>
                            </div>
                        </div>

                        <div class="game-buttons">
                            <button class="game-button">Acheter</button>
                            <button class="game-button game-button-secondary">Infos</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <footer>
            <p>© 2024 Ma Collection de Jeux • Conçu avec passion ⛏️</p>
        </footer>
    </div>
</body>
</html>