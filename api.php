<?php

require_once("./scripts/session.php");

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
        if (isset($_POST['prix-selectionne'])) {
            $reponse = getReponse("panneau");
            echo json_encode($reponse);
        } else if (isset($_POST['search'])) {
            $reponse = getReponse("recherche");
            echo json_encode($reponse);
        } else json_encode(null);
    }
}

/**
 * Renvoie la réponse de la requête
 * 
 * @param string $mode Le mode de requête
 * @return array|null La réponse
 */
function getReponse(string $mode)
{
    $res = null;
    switch ($mode) {
        case "panneau":
            $res = getReponsePanneau();
            break;
        case "recherche":
            $res = getReponseRecherche();
            break;
        default:
            return;
    }

    return ($res);
}

/**
 * Renvoie la réponse pour une requête effectuée sur le panneau latéral
 */
function getReponsePanneau()
{
    $donnees = getDonnees();
    filtrerPrix($donnees);

    unset($_POST['prix-selectionne']);

    $categories = [];
    $marques = [];
    $sexes = [];
    $couleurs = [];

    foreach ($_POST as $cle => $valeur) {
        if ($cle == "categorie-T-shirt") {
            array_push($categories, "T-shirt");
        } else {
            $filtre = explode('-', $cle);
            switch ($filtre[0]) {
                case "categorie":
                    array_push($categories, $filtre[1]);
                    break;
                case "marque":
                    array_push($marques, $filtre[1]);
                    break;
                case "sexe":
                    array_push($sexes, $filtre[1]);
                    break;
                case "couleur":
                    array_push($couleurs, $filtre[1]);
                    break;
            }
        }
    }

    filtrer($donnees, "categorie", $categories);
    filtrer($donnees, "marque", $marques);
    filtrer($donnees, "sexe", $sexes);
    filtrer($donnees, "couleur", $couleurs);

    return $donnees;
}

/**
 * Filtre les données par prix
 * 
 * @param array &$donnees Les données
 */
function filtrerPrix(array &$donnees)
{
    $prix = $_POST['prix-selectionne'];
    $bornes = explode(' - ', $prix);
    $prixMin = (float) trim($bornes[0], ' €');
    $prixMax = (float) trim($bornes[1], ' €');

    foreach ($donnees as $cle => $article) {
        if ($article['prix'] < $prixMin || $article['prix'] > $prixMax) {
            unset($donnees[$cle]);
        }
    }
}

/**
 * Filtre les données par section
 * 
 * @param array &$donnees Les données
 * @param string $cle La clé du tableau de données à filtrer
 * @param array $section Les valeurs sélectionnées pour une section donnée du formulaire
 */
function filtrer(&$donnees, $cle, $section)
{
    if (empty($section)) {
        return;
    }
    $donnees = array_filter($donnees, function ($article) use ($cle, $section) {
        return in_array($article[$cle], $section);
    });
}

/**
 * Renvoie la réponse pour une requête effectuée dans la barre de recherche
 * 
 * @return array La réponse
 */
function getReponseRecherche()
{
    $donnees = getDonnees();
    $chaineRecherche = $_POST['search'];

    $donneesFiltrees = array_filter($donnees, function ($article) use ($chaineRecherche) {
        $description = supprimerDiacritiques($article['description']);
        $search = supprimerDiacritiques($chaineRecherche);
        return stristr($description, $search) !== false;
    });

    return ($donneesFiltrees);
}

/**
 * Supprime les diacritiques d'une chaîne de caractères
 * 
 * @param string $string La chaîne à modifier
 * @return string La chaîne non accentuée
 */
function supprimerDiacritiques($string)
{
    return iconv('UTF-8', 'ASCII//TRANSLIT', $string);
}

/**
 * Lance le script
 */
function lancerScript()
{
    recupererRequete();
}

lancerScript();
