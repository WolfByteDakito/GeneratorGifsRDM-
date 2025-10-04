<?php

use PHPUnit\Framework\TestCase;
use GeneratorGifsRDM\GifService;

// Assurez-vous que l'autoloader trouve votre classe GifService
// Si vous utilisez Composer, l'autoload.php doit être chargé avant.

class GifServiceTest extends TestCase
{
    private $dummyApiKey = 'TEST_API_KEY';

    // Test 1 : Scénario nominal (Succès)
    public function testGetRandomGifUrlReturnsUrlOnSuccess(): void
    {
        // Données JSON simulées provenant de Giphy
        $mockSuccessData = [
            'data' => [
                'images' => [
                    'original' => ['url' => 'http://example.com/mock-gif.gif']
                ]
            ]
        ];
        
        // 1. Définir le MOCK (le simuler) pour l'appel HTTP.
        // Nous créons une fonction qui renvoie toujours la réponse JSON simulée.
        $mockHttpFetcher = function($url) use ($mockSuccessData) {
            // Optionnel : vérifier si l'URL contient bien l'apiKey et le tag
            $this->assertStringContainsString('TEST_API_KEY', $url);
            return json_encode($mockSuccessData);
        };

        // 2. Instancier la classe en lui injectant le mock
        $service = new GifService($this->dummyApiKey, $mockHttpFetcher);

        // 3. Exécuter la méthode testée
        $result = $service->getRandomGifUrl('cat');

        // 4. Assertion : Vérifier que le résultat est bien l'URL attendue
        $this->assertIsString($result);
        $this->assertEquals('http://example.com/mock-gif.gif', $result);
    }

    // Test 2 : Scénario "GIF Nul" (Logique de votre classe)
    public function testGetRandomGifUrlReturnsNullWhenGiphyReturnsNoGif(): void
    {
        // Données JSON simulées où 'original' est manquant ou vide (votre cas "gifs nul")
        $mockNoGifData = [
            'data' => [] // Giphy peut renvoyer data vide ou juste sans la bonne structure
        ];

        // 1. Définir le MOCK pour l'échec interne
        $mockHttpFetcher = function($url) use ($mockNoGifData) {
            return json_encode($mockNoGifData);
        };
        
        // 2. Instancier avec le mock
        $service = new GifService($this->dummyApiKey, $mockHttpFetcher);

        // 3. Exécuter
        $result = $service->getRandomGifUrl('azertyuiop-nonsense-tag');

        // 4. Assertion : Vérifier que le résultat est bien NULL
        $this->assertNull($result, "La méthode doit retourner NULL quand l'API ne trouve pas de GIF.");
    }

    // Test 3 : Échec de l'appel (Simuler 'file_get_contents' renvoyant FALSE)
    public function testGetRandomGifUrlReturnsNullOnConnectionFailure(): void
    {
        // 1. Définir le MOCK qui simule une erreur de connexion (timeout, DNS, etc.)
        $mockHttpFetcher = function($url) {
            return false; // Comme file_get_contents ou Guzzle lance une exception
        };
        
        // 2. Instancier avec le mock
        $service = new GifService($this->dummyApiKey, $mockHttpFetcher);

        // 3. Exécuter
        $result = $service->getRandomGifUrl('test');

        // 4. Assertion : Vérifier que le résultat est bien NULL
        $this->assertNull($result, "La méthode doit retourner NULL en cas d'échec de la connexion API.");
    }
}