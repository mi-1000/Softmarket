<?php

require_once("./scripts/session.php");
require_once("./scripts/connexion.php");

/**
 * Récupère les données depuis une URL
 * 
 * @param string $url L'URL
 * @return array|null Les données
 */
function getDonnees(string $url = "./articles.json")
{
    if (file_get_contents($url)) {
        return json_decode(file_get_contents($url), true);
    }
    return null;
}

/**
 * Récupère le contenu d'une requête POST
 */
function recupererRequete()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $_POST = json_decode(file_get_contents('php://input'), true);
        if (isset($_POST['lancement'])) {
            $reponse = getPanier();
            echo json_encode($reponse);
        } else if (isset($_POST['maj']) && isset($_POST['nv'])) {
            $reponse = majPanier();
            echo json_encode($reponse);
        } else if (isset($_POST['suppr'])) {
            $reponse = supprElement();
            echo json_encode($reponse);
        } else if (isset($_POST[0])) {
            $reponse = getReponse();
            echo json_encode($reponse);
        } else echo json_encode(null);
    }
}

/**
 * Renvoie la réponse à la requête
 */
function getReponse()
{
    $session_id = session_id();
    $rep = null;
    foreach (getDonnees() as $article) {
        if ($article['id'] === $_POST[0]) {
            $rep = $article;
            break;
        }
    }

    $stmt = Connexion::getInstance()->prepare("SELECT * FROM `session` WHERE `id` = ?;");
    $stmt->bind_param("s", $session_id);
    if ($stmt->execute()) {
        $resultat = $stmt->get_result();
        if ($resultat->num_rows === 0) {
            $stmt = Connexion::getInstance()->prepare("INSERT INTO `session`(`id`, `timestamp`) VALUES (?, ?);");
            $date = new DateTime();
            $date = $date->format('Y-m-d H:i:s');
            $stmt->bind_param("ss", $session_id, $date);
            $stmt->execute();
        }
    }

    $stmt = Connexion::getInstance()->prepare("SELECT `quantite` FROM `panier` WHERE `id` = ? AND `session_id` = ?;");
    $id = $rep['id'];
    $stmt->bind_param("is", $id, $session_id);
    if ($stmt->execute()) {
        if ($resultat = $stmt->get_result()) {
            if ($resultat->num_rows > 0) {
                $quantite = 0;
                if ($ligne = $resultat->fetch_assoc()) {
                    $quantite = (int) $ligne['quantite'];
                }
                $quantite++;
                $stmt = Connexion::getInstance()->prepare("UPDATE `panier` SET `quantite` = ? WHERE `id` = ?;");
                $stmt->bind_param("ii", $quantite, $rep['id']);
                $stmt->execute();
            } else {
                $requete = "INSERT INTO `panier`(`id`, `marque`, `couleur`, `description`, `categorie`, `sexe`, `prix`, `note`, `reduction`, `nouveaute`, `session_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = Connexion::getInstance()->prepare($requete);
                $id = $rep['id'];
                $marque = $rep['marque'];
                $couleur = $rep['couleur'];
                $description = $rep['description'];
                $categorie = $rep['categorie'];
                $sexe = $rep['sexe'];
                $prix = $rep['prix'];
                $note = $rep['note'];
                $reduction = isset($rep['reduction']) ? $rep['reduction'] : null;
                $nouveaute = isset($rep['nouveaute']) ? $rep['nouveaute'] : null;
                $stmt->bind_param("isssssddiis", $id, $marque, $couleur, $description, $categorie, $sexe, $prix, $note, $reduction, $nouveaute, $session_id);
                $stmt->execute();
            }
        }
    }

    return getPanier();
}

/**
 * Met à jour le panier
 * 
 * @return array Le panier mis à jour
 */
function majPanier()
{
    $stmt = Connexion::getInstance()->prepare("UPDATE `panier` SET `quantite` = ? WHERE `id` = ? AND `session_id` = ?;");
    $quantite = $_POST['nv'];
    $id = $_POST['maj'];
    $session_id = session_id();
    $stmt->bind_param("iis", $quantite, $id, $session_id);
    $stmt->execute();

    return getPanier();
}

/**
 * Supprime un élément du panier
 * 
 * @return array Le panier mis à jour
 */
function supprElement() {
    $stmt = Connexion::getInstance()->prepare("DELETE FROM `panier` WHERE `id` = ? AND `session_id` = ?;");
    $id = $_POST['suppr'];
    $session_id = session_id();
    $stmt->bind_param("is", $id, $session_id);
    $stmt->execute();

    return getPanier();
}

/**
 * Renvoie le contenu du panier associé à la session actuelle
 * 
 * @return array Le panier
 */
function getPanier()
{
    $stmt = Connexion::getInstance()->prepare("SELECT `id`, `description`, `prix`, `reduction`, `quantite` FROM `panier` WHERE `session_id` = ?;");
    $session_id = session_id();
    $stmt->bind_param("s", $session_id);
    if ($stmt->execute()) {
        $resultat = $stmt->get_result();
        if ($resultat->num_rows > 0) {
            $donnees = array();
            while ($ligne = $resultat->fetch_assoc()) {
                $donnees[] = $ligne;
            }
            header("Content-Type: application/json");
            return $donnees;
        }
    }
    return [];
}

/**
 * Lance le script
 */
function lancerScript()
{
    Connexion::definirConnexion("mysql-mi-1000.alwaysdata.net", "mi-1000_projet", "Lj3hxN9JiZe5Jq4d*?yFJyGO3Um-fCb7jd-.T3h*MF8qpgxAzn", "mi-1000_projet", "3306");
    recupererRequete();
    Connexion::fermerConnexion();
}

lancerScript();
