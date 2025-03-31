<?php
require_once "./connexion.php";

/**
 * Modèle URL - Gère les interactions avec ladd pour les URLs raccourcies
 */
class ModeleURL extends Connexion {
    
    /**
     * Crée une nouvelle URL raccourcie
     */
    public function creerURL($originalUrl, $shortCode = null, $userId = null) {
        // Si aucun code court n'est fourni, en générer un
        if ($shortCode === null) {
            $shortCode = $this->genererCodeUnique();
        }        
        try {
            // Si l'utilisateur est connecté
            if ($userId !== null) {
                $req = self::$bdd->prepare("INSERT INTO shortened_urls (original_url, short_code, user_id, created_at) 
                                           VALUES (:original_url, :short_code, :user_id, NOW())");
                $req->bindParam(':user_id', $userId, PDO::PARAM_INT);
            } else {
                // URL anonyme (user_id par défaut = 1 pour "anonymous")
                $req = self::$bdd->prepare("INSERT INTO shortened_urls (original_url, short_code, user_id, created_at) 
                                           VALUES (:original_url, :short_code, 1, NOW())");
            }
            
            $req->bindParam(':original_url', $originalUrl);
            $req->bindParam(':short_code', $shortCode);
            $req->execute();
            
            $id = self::$bdd->lastInsertId();
            return $this->getURLById($id);
            
        } catch (PDOException $e) {
            return ['erreur' => "Erreur lors de la création de l'URL: " . $e->getMessage()];
        }
    }
    
    // Récupère une URL raccourcie par son ID
    public function getURLById($id) {
        $req = self::$bdd->prepare("SELECT * FROM shortened_urls WHERE id = :id");
        $req->bindParam(':id', $id, PDO::PARAM_INT);
        $req->execute();
        
        return $req->fetch(PDO::FETCH_ASSOC);
    }

    //Vérifie si un code court existe déja
    public function codeExiste($shortCode) {
        $req = self::$bdd->prepare("SELECT COUNT(*) FROM shortened_urls WHERE short_code = :short_code");
        $req->bindParam(':short_code', $shortCode);
        $req->execute();
        
        return $req->fetchColumn() > 0;
    }
    
    // Génère un code court unique
    public function genererCodeUnique($longueur = 6) {
        $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $maxTentatives = 5;
        
        for ($tentative = 0; $tentative < $maxTentatives; $tentative++) {
            // Augmenter la longueur après chaque tentative
            $longueurActuelle = $longueur + $tentative;
            $code = '';
            
            for ($i = 0; $i < $longueurActuelle; $i++) {
                $code .= $caracteres[mt_rand(0, strlen($caracteres) - 1)];
            }
            
            // Vérifier si le code existe déjà
            if (!$this->codeExiste($code)) {
                return $code;
            }
        }
        
        // Si on ne peut pas générer un code unique après plusieurs tentatives,
        // utiliser un timestamp pour garantir l'unicité
        return $code . substr(time(), -4);
    }
}
