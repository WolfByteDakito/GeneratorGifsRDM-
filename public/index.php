<?php


use GeneratorGifsRDM\GifService;

// On charge l'autoloader de Composer
require __DIR__ . '/../vendor/autoload.php';

// Le fichier de notre classe se charge automatiquement grâce à l'autoloading

// 1. Initialisation : Vous devez obtenir une clé Giphy et la stocker de manière sécurisée.
// Charger la librairie de gestion des variables d'environnement
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// 🔑 Maintenant, on récupère la clé de manière sécurisée
$apiKey = $_ENV['GIPHY_API_KEY'] ?? null; 

if (!$apiKey) {
    throw new \Exception("La clé GIPHY_API_KEY n'est pas définie dans le fichier .env !");
}

// --- Définition du fetcher HTTP réel ---
// Cette fonction utilise une méthode PHP native pour faire l'appel réseau
$realHttpFetcher = function (string $url): string|false {
    // NOTE : Nous utilisons file_get_contents ici, mais Guzzle serait recommandé
    $context = stream_context_create([
        'http' => [
            'ignore_errors' => true // Pour éviter que file_get_contents ne jette une erreur sur 404/500
        ]
    ]);
    return @file_get_contents($url, false, $context);
};

// ----------------------------------------

try {
    // 2. Instanciation avec les DEUX arguments
    // On passe la clé API ET la fonction de fetching réelle
    $gifService = new GeneratorGifsRDM\GifService($apiKey, $realHttpFetcher); // Correction ici !

    // ... (suite de votre code pour traiter la requête) ...

    // Récupération d'un tag (par exemple, si le client le demande)
    $tag = $_GET['tag'] ?? 'programming'; 

    $gifUrl = $gifService->getRandomGifUrl($tag);

    // 2. Réponse JSON
    header('Content-Type: application/json');

    if ($gifUrl) {
        // Succès : retourne l'URL du GIF
        echo json_encode(['success' => true, 'url' => $gifUrl]);
    } else {
        // Échec : retourne une erreur (votre cas "GIF Nul")
        http_response_code(404); // Code standard pour "Non trouvé"
        echo json_encode(['success' => false, 'message' => 'Aucun GIF trouvé pour ce terme.']);
    }

} catch (\Exception $e) {
    // Erreur critique (clé API manquante, etc.)
    http_response_code(500); // Code standard pour "Erreur serveur"
    echo json_encode(['success' => false, 'message' => 'Erreur interne du serveur.']);
}