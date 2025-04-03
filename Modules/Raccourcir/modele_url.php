<?php
class ModeleURL extends Connexion {
    /**
     * Génère un code court unique pour l'URL
     * 
     * @param int $longueur Longueur du code (défaut 6)
     * @param bool $personnalise Indique si c'est un code personnalisé
     * @return string Code court unique
     */
    public function genererCodeUnique($longueur = 6, $personnalise = false) {
        // Caractères utilisables pour générer le code
        $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        
        // Nombre maximum de tentatives pour générer un code unique
        $maxTentatives = 10;
        
        for ($tentative = 0; $tentative < $maxTentatives; $tentative++) {
            // Ajuster la longueur si nécessaire
            $longueurActuelle = $longueur + $tentative;
            
            // Générer le code
            $code = '';
            for ($i = 0; $i < $longueurActuelle; $i++) {
                $code .= $caracteres[random_int(0, strlen($caracteres) - 1)];
            }
            
            // Vérifier si le code existe déjà dans la base de données
            if (!$this->codeExiste($code)) {
                return $code;
            }
        }
        
        // Si génération impossible, utiliser un timestamp
        return substr(base_convert(time(), 10, 36), 0, $longueur);
    }

    /**
     * Crée une nouvelle URL raccourcie
     * 
     * @param string $originalUrl URL à raccourcir
     * @param string|null $shortCode Code personnalisé (optionnel)
     * @param int|null $userId ID de l'utilisateur (optionnel)
     * @param int|null $expireAfter Durée d'expiration en heures (optionnel)
     * @return array Informations sur l'URL raccourcie
     */
    public function creerURL($originalUrl, $shortCode = null, $userId = null, $expireAfter = null) {
        // Valider l'URL
        if (!filter_var($originalUrl, FILTER_VALIDATE_URL)) {
            return ['erreur' => "URL invalide"];
        }

        // Générer un code court si non fourni
        if ($shortCode === null) {
            $shortCode = $this->genererCodeUnique();
        } else {
            // Vérifier si le code personnalisé est déjà utilisé
            if ($this->codeExiste($shortCode)) {
                return ['erreur' => "Ce code est déjà utilisé"];
            }
        }

        // Définir la date d'expiration
        $expiryDatetime = null;
        if ($expireAfter !== null) {
            $expiryDatetime = date('Y-m-d H:i:s', strtotime("+{$expireAfter} hours"));
        }

        try {
            // Utiliser l'ID utilisateur anonyme par défaut si non spécifié
            $effectiveUserId = $userId ?? 1;

            // Préparer et exécuter la requête
            $req = self::$bdd->prepare("
                INSERT INTO shortened_urls 
                (original_url, short_code, user_id, created_at, expiry_datetime) 
                VALUES (:original_url, :short_code, :user_id, NOW(), :expiry_datetime)
            ");

            $req->bindParam(':original_url', $originalUrl);
            $req->bindParam(':short_code', $shortCode);
            $req->bindParam(':user_id', $effectiveUserId, PDO::PARAM_INT);
            $req->bindParam(':expiry_datetime', $expiryDatetime);

            $req->execute();
            
            // Récupérer et retourner les informations de l'URL
            $id = self::$bdd->lastInsertId();
            return $this->getURLById($id);
            
        } catch (PDOException $e) {
            return ['erreur' => "Erreur lors de la création de l'URL: " . $e->getMessage()];
        }
    }

    /**
     * Vérifie si un code court existe déjà
     * 
     * @param string $shortCode Code court à vérifier
     * @return bool True si le code existe, false sinon
     */
    public function codeExiste($shortCode) {
        try {
            $req = self::$bdd->prepare("SELECT COUNT(*) FROM shortened_urls WHERE short_code = :short_code");
            $req->bindParam(':short_code', $shortCode);
            $req->execute();
            
            return $req->fetchColumn() > 0;
        } catch (PDOException $e) {
            // Gérer l'erreur de manière appropriée
            error_log("Erreur lors de la vérification du code: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les informations d'une URL par son ID
     * 
     * @param int $id ID de l'URL
     * @return array|null Informations de l'URL
     */
    public function getURLById($id) {
        try {
            $req = self::$bdd->prepare("
                SELECT id, original_url, short_code, user_id, created_at, expiry_datetime 
                FROM shortened_urls 
                WHERE id = :id
            ");
            $req->bindParam(':id', $id, PDO::PARAM_INT);
            $req->execute();
            
            return $req->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de l'URL: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère une URL par son code court
     * 
     * @param string $shortCode Code court de l'URL
     * @return array|null Informations de l'URL
     */
    public function getURLByShortCode($shortCode) {
        try {
            $req = self::$bdd->prepare("
                SELECT id, original_url, short_code, user_id, created_at, expiry_datetime 
                FROM shortened_urls 
                WHERE short_code = :short_code AND (expiry_datetime IS NULL OR expiry_datetime > NOW())
            ");
            $req->bindParam(':short_code', $shortCode);
            $req->execute();
            
            return $req->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de l'URL par code court: " . $e->getMessage());
            return null;
        }
    }
}