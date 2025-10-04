// Point d'entrée de votre service PHP (qui utilise la classe GifService)
const API_ENDPOINT = 'index.php'; // Ce fichier PHP va retourner le JSON

const gifImage = document.getElementById('random-gif');
const messageElement = document.getElementById('message');
const generateButton = document.getElementById('generate-button');
const tagInput = document.getElementById('tag-input');

/**
 * Met à jour l'état visuel (message, bouton, image)
 * @param {string} text Le message à afficher.
 * @param {boolean} isLoading Indicateur si le chargement est en cours.
 */
function updateStatus(text, isLoading = false) {
    messageElement.textContent = text;
    messageElement.style.display = isLoading || text ? 'block' : 'none';
    gifImage.style.display = 'none';
    generateButton.disabled = isLoading;
    generateButton.textContent = isLoading ? 'Chargement...' : 'Nouveau GIF Aléatoire';
}

/**
 * Fonction principale pour générer et afficher un GIF.
 * @param {boolean} isRetry Indique si c'est une tentative après un premier échec.
 */
async function generateGif(isRetry = false) {
    const tag = tagInput.value.trim();
    
    // 1. Indiquer le chargement
    updateStatus(isRetry ? 'Recherche d\'un autre GIF...' : 'Chargement du GIF...', true);

    // 2. Préparation de la requête
    const fullUrl = `${API_ENDPOINT}?tag=${encodeURIComponent(tag)}`;

    try {
        // 3. Appel à votre service PHP
        const response = await fetch(fullUrl);
        const data = await response.json();

        // 4. Traitement de la réponse du PHP (Succès ou Échec "GIF Nul")
        if (response.ok && data.success) {
            // Succès : l'URL est là !
            displayGif(data.url);
        } else {
            // Échec logique : le PHP a renvoyé un code d'erreur (404 ou 500)
            // C'est votre cas de "GIF Nul" ou "Erreur Serveur"
            console.error('Erreur du service PHP:', data.message);
            
            // CHALLENGE et CORRECTION : Au lieu de recharger la page, on relance la fonction une seule fois.
            if (!isRetry) {
                // Tentative avec un tag vide si le premier tag a échoué
                tagInput.value = ''; // On efface le tag
                console.log("Tentative avec un tag générique.");
                await generateGif(true); // Relance la fonction pour une 2ème chance
            } else {
                // Échec après le premier échec : on affiche un message
                updateStatus('🚫 Impossible de trouver un GIF. Veuillez réessayer.', false);
            }
        }
    } catch (error) {
        // 5. Traitement de l'erreur réseau (Cas "pas de connexion")
        console.error('Erreur de connexion:', error);
        updateStatus('❌ Vous n\'avez pas de connexion ou le serveur est inaccessible.', false);
    }
}

/**
 * Insère l'URL dans la balise <img> et gère les erreurs de chargement d'image.
 * @param {string} url L'URL du GIF.
 */
function displayGif(url) {
    // 1. Réinitialiser les événements
    gifImage.onload = null;
    gifImage.onerror = null;

    // 2. Gestion de la VRAIE ERREUR DE CONNEXION (ou image invalide)
    // Si le fetch réussit, mais l'image elle-même ne peut pas être chargée par le navigateur.
    gifImage.onerror = () => {
        updateStatus('❌ Vous n\'avez pas de connexion ou l\'image n\'a pas pu être chargée.', false);
    };

    // 3. Gestion du Succès de chargement de l'image
    gifImage.onload = () => {
        updateStatus('', false); // Enlève le message
        gifImage.style.display = 'block';
    };

    // 4. Déclenchement du chargement
    gifImage.src = url;
}


// Événement d'écoute
generateButton.addEventListener('click', () => {
    generateGif();
});

// Lancement initial au chargement de la page
generateGif();