<?php require_once("./scripts/session.php"); ?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TP Web</title>
    <link rel="stylesheet" href="./scripts/bootstrap-icons.css">
    <link rel="stylesheet" href="./scripts/bootstrap.min.css">
    <link rel="stylesheet" href="./scripts/jquery-ui.min.css">
    <link rel="stylesheet" href="./scripts/animate.min.css">
    <link rel="stylesheet" href="./style.css">
    <script src="./scripts/jquery-3.7.0.min.js"></script>
    <script src="./scripts/jquery-ui-1.12.1.js"></script>
    <script src="./app.js"></script>
</head>

<body>
    <nav class="navbar navbar-expand-sm sticky-top container-fluid bandeau mb-2">
        <div class="d-flex align-items-center justify-content-between w-100">
            <div class="d-flex align-items-center">
                <div class="col-auto m-2">
                    <a href="javascript:scrollTo(0,0)">
                        <div class="logo"></div>
                    </a>
                </div>
                <div class="col-auto boutonMenu p-2 m-2" id="boutonAccueil">
                    <a href="javascript:void(0)" class="p-1">Accueil</a>
                </div>
                <div class="col-auto barreRecherche p-2 m-2">
                    <form class="d-inline-flex">
                        <input type="text" name="search" id="search" placeholder="Rechercher…" class="rounded mx-1 d-flex align-items-center">
                        <button type="sumbit" class="mx-1" id="bouton-recherche"><i class="bi bi-search d-flex align-items-center justify-content-center"></i></button>
                    </form>
                </div>
            </div>
            <div class="col-auto boutonMenu p-2 m-2" id="boutonPanier">
                <a href="javascript:void(0)" class="p-1">Mon panier (<span>0</span>)</a>
            </div>
        </div>
    </nav>

    <div class="corps container-fluid d-flex flex-column">
        <div class="row">
            <div class="tri-articles rounded w-25 d-flex flex-column">
                <form method="post" action="./api.php" id="formulaire-filtre">
                    <div id="liste-categories" class="d-flex flex-column align-items-start"></div>
                    <div id="liste-marques" class="d-flex flex-column align-items-start mt-2"></div>
                    <div id="liste-sexes" class="d-flex flex-column align-items-start mt-2"></div>
                    <div id="liste-couleurs" class="container mt-2">
                        <div class="row">
                            <h4>Couleur</h4>
                        </div>
                        <div class="row container-couleurs justify-content-center"></div>
                    </div>
                    <div id="choix-prix-articles">
                        <h4 class="mt-2">Prix</h4>
                        <div class="d-flex flex-column justify-content-center align-items-center">
                            <input type="text" name="prix-selectionne" id="prix-selectionne" class="text-center" readonly>
                        </div>
                        <div id="slider-prix"></div>
                    </div>
                    <div class="my-2 d-flex justify-content-center">
                        <button type="submit" class="rounded p-2" id="bouton-filtrer">Filtrer</button>
                    </div>
                    <div class="my-2 d-flex justify-content-center">
                        <button type="button" class="rounded p-2" id="bouton-reinitialiser-filtres">Réinitialiser</button>
                    </div>
                </form>
            </div>
            <div class="articles w-75">
                <div class="container">
                    <div class="row align-items-center justify-content-center m-2 p-2 rounded" id="container-banniere">
                        <div class="logo"></div>
                    </div>
                    <div class="row align-items-end justify-content-center" id="container-articles">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="panier d-none">
        <p id="panier-vide">Le panier est vide.</p>
        <table id="tableau-panier" class="rounded">
            <thead>
                <tr>
                    <th>Article</th>
                    <th>Prix</th>
                    <th>Quantité</th>
                    <th>Sous-total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <div class="container-fluid section-bas-panier">
            <div class="row justify-content-center align-items-center">
                <div class="col-auto panneau-bas-panier d-flex flex-column justify-content-center align-items-center rounded x-2 p-2">
                    <h5>Estimation des frais de livraison</h5>
                    <p>Entrez votre adresse pour obtenir une estimation :</p>
                    <form class="container d-flex flex-column">
                        <div class="row">
                            <label for="saisie-pays" class="saisie-obligatoire">Pays : </label>
                            <input type="text" name="saisie-pays" id="saisie-pays" placeholder="Pays">
                        </div>
                        <div class="row">
                            <label for="saisie-region" class="saisie-obligatoire">Région : </label>
                            <input type="text" name="saisie-region" id="saisie-region" placeholder="Région">
                        </div>
                        <div class="row">
                            <label for="saisie-cp" class="saisie-obligatoire">Code postal : </label>
                            <input type="text" name="saisie-cp" id="saisie-cp" placeholder="Code postal">
                        </div>
                        <div class="row">
                            <div class="col-auto mx-auto my-2">
                                <button type="button" class="bouton-bas-panier rounded">Envoyer</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-auto panneau-bas-panier d-flex flex-column justify-content-center align-items-center rounded m-2 p-2">
                    <h5>Coupon de réduction</h5>
                    <p>Saisissez votre coupon de réduction si vous en avez un :</p>
                    <form class="container d-flex flex-column">
                        <div class="row">
                            <label for="saisie-coupon">Coupon : </label>
                            <input type="text" name="saisie-coupon" id="saisie-coupon" placeholder="Coupon">
                        </div>
                        <div class="row">
                            <div class="col-auto mx-auto my-2">
                                <button type="button" class="bouton-bas-panier rounded">Envoyer</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div id="recapitulatif-panier" class="col-auto panneau-bas-panier d-flex flex-column justify-content-center align-items-center rounded m-2 p-2">
                    <div class="row">
                        <div class="col-auto">
                            <h5>Récapitulatif de la commande</h5>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-auto">
                            <h6 id="sous-total">Sous-total : <span></span></h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-auto">
                            <h6 id="grand-total">Total : <span></span></h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-auto">
                            <button type="button" class="bouton-bas-panier rounded">Payer</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-5 justify-content-center">
        <div class="col-auto m-1" id="panneau-1"></div>
        <div class="col-auto m-1" id="panneau-2"></div>
        <div class="col-auto m-1" id="panneau-3"></div>
        <div class="col-auto m-1" id="panneau-4"></div>
    </div>
    <div class="row mt-2 justify-content-center">
        <div class="col-auto m-1" id="panneau-bas"></div>
    </div>
    <div class="row m-1 justify-content-between logos-marques">
        <div class="col-auto d-flex align-items-center justify-content-center">
            <img src="./ressources/img-19.png" alt="Facebook">
            <img src="./ressources/img-20.png" alt="Twitter">
            <img src="./ressources/img-21.png" alt="YouTube">
            <img src="./ressources/img-22.png" alt="RSS">
        </div>
        <div class="col-auto d-flex align-items-center justify-content-center">
            <img src="./ressources/img-25.png" alt="PayPal">
            <img src="./ressources/img-26.png" alt="Visa">
            <img src="./ressources/img-23.png" alt="Mastercard">
            <img src="./ressources/img-24.png" alt="?">
        </div>
    </div>
</body>

</html>