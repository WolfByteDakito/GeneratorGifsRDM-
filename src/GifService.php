<?php 

namespace GeneratorGifsRDM;

class GifService
{
    // Propriété privée pour stocker la clé API (pour l'encapsulation)
    private $apiKey;
    
    // Propriété privée pour l'URL de base de l'API
    private $baseUrl = "https://api.giphy.com/v1/gifs/random"; 
    private $httpFetcher; // Nous allons injecter ici la fonction ou l'objet qui fait l'appel réel

    /**
     * Constructeur pour initialiser la clé API.
     * @param string $apiKey La clé d'authentification Giphy.
     */
    public function __construct(string $apiKey, callable $httpFetcher)
    {
        // On s'assure que la clé est bien passée
        if (empty($apiKey)) {
            throw new \InvalidArgumentException("La clé API Giphy ne doit pas être vide.");
        }
        $this->apiKey = $apiKey;
        $this->httpFetcher = $httpFetcher;
    }

    /**
     * Récupère une URL de GIF aléatoire de l'API Giphy.
     * @param string $tag Un terme de recherche optionnel.
     * @return string|null L'URL du GIF ou null en cas d'échec.
     */
    public function getRandomGifUrl(string $tag = ""): ?string
    {
        // 1. Construction de la requête URL
        $params = [
            'api_key' => $this->apiKey,
            'tag'     => urlencode($tag),
            'rating'  => 'pg-13', // Filtre de contenu
        ];
        
        $url = $this->baseUrl . '?' . http_build_query($params);

        // 2. Appel HTTP (Ici, nous utilisons la méthode simple de PHP)
        // **NOTE critique : C'est ici que Guzzle serait bien meilleur !**
        $response = call_user_func($this->httpFetcher, $url); 

        if ($response === false) {
            // Échec de la connexion ou requête invalide
            error_log("Erreur lors de l'appel à l'API Giphy.");
            return null;
        }

        // 3. Traitement de la réponse JSON
        $data = json_decode($response, true);
        
        // VÉRIFICATION CRITIQUE : L'API a-t-elle renvoyé un résultat ?
        if (
            !isset($data['data']['images']['original']['url']) || 
            empty($data['data']['images']['original']['url'])
        ) {
            // C'est votre cas de "gifs nul" : on le signale.
            error_log("L'API n'a pas renvoyé de GIF valide pour le tag : " . $tag);
            return null;
        }

        return $data['data']['images']['original']['url'];
    }
}