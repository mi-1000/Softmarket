var articles = [];

$(document).ready(async () => {
    articles = await recupererDonnees("./articles.json");
    $("#container-articles").html(afficherArticles(articles));
    $("#liste-categories").html(afficherCategories(articles));
    $("#liste-marques").html(afficherMarques(articles));
    $("#liste-sexes").html(afficherSexes(articles));
    $("#liste-couleurs .container-couleurs").html(afficherCouleurs(articles));
    afficherSliderPrix(articles);
    majContenu(articles);

    gererOnglets();
    gererFormulaires();

    var panier = await envoyerDonnees({ lancement: true }, "./bdd.php");
    var nbArticles = 0;
    panier.forEach(article => {
        nbArticles += article['quantite'];
    });
    $("#boutonPanier span").html(nbArticles);
    majPanier(panier);
});

/**
 * Encode les paramètres de la requête dans l'URL
 * 
 * @param {Array} params - Le tableau associatif contenant les paramètres 
 * @returns {String} Les paramètres encodés sous forme d'URL
 */
function construireURL(params) {
    let fParams = "?";

    for (const cle in params) {
        fParams += `${encodeURIComponent(cle)}=${encodeURIComponent(params[cle])}&`;
    }

    return fParams.slice(0, -1);
}

/**
 * Récupère les données depuis une URL
 * 
 * @param {String} url - L'URL
 * @param {Array} params - Le tableau associatif contenant les paramètres
 * @returns {Promise<any[]>} Le résultat de la requête
 */
async function recupererDonnees(url, params = {}) {

    let fParams = construireURL(params);

    const req = await fetch(url + fParams, {
        method: "GET",
        mode: "cors",
        headers: { "Content-Type": "application/json" }
    });

    const donnees = await req.json();

    return donnees;
}

/**
 * Renvoie le code HTML permettant d'afficher les articles
 * 
 * @param {Array} articles Les articles à afficher
 * @returns Le code HTML correspondant
 */
function afficherArticles(articles) {
    var html = "";
    Array.from(articles).forEach(article => {
        if (!article['id']) {
            return;
        }
        let img = `./images/${article['id']}.jpg`;
        html += `<div class="card article">` +
            `${!!article['nouveaute'] ? `<div class="nouvel-article p-2 rounded"></div>` : ``}` +
            `${!!article['reduction'] ? `<div class="article-reduction p-2 rounded" style="--pourcentage-reduction: '- ${article['reduction']} %'"></div>` : ``}` +
            `<img class="card-img-top" src="${img}" />` +
            `<div class="card-body d-flex flex-column justify-content-center align-items-center text-center">` +
            `<h4 class="card-title">` +
            `${article['description']}` +
            `</h4>` +
            `<p class="card-text"` +
            `<span>${!!article['reduction']
                ? `<span class="ancien-prix mx-1">${article['prix'].toLocaleString()} €</span><span class="prix-reduit mx-1">${calculerReduction(article['prix'], article['reduction']).toLocaleString()} €</span>`
                : `${article['prix'].toLocaleString()} €`}</span>` +
            `<br />` +
            `<span class="note-article">${noteToIcone(article['note'])}</span>` +
            `</p>` +
            `<button type="button" class="bouton-ajout-panier rounded" id="${article['id']}">Ajouter au panier</button>` +
            `</div>` +
            `</div>`;
    });
    return html;
}

/**
 * Met à jour le contenu de la page
 * 
 * @param {Array} articles Les articles
 */
function majContenu(articles) {
    if (articles.length === 0) {
        async () => {
            articles = await recupererDonnees("./articles.json");
        }
    }
    $("#container-articles").html(afficherArticles(articles));
    $("#liste-categories").html(afficherCategories(articles));
    $("#liste-marques").html(afficherMarques(articles));
    $("#liste-sexes").html(afficherSexes(articles));
    $("#liste-couleurs .container-couleurs").html(afficherCouleurs(articles));
    afficherSliderPrix(articles);
}

/**
 * Calcule le prix d'un article après réduction
 * 
 * @param {Number} prix Le prix de base
 * @param {Number} reduc Le pourcentage de réduction
 * @returns {Number} Le prix après réduction
 */
function calculerReduction(prix, reduc) {
    if (reduc <= 0 || reduc >= 100) {
        return prix;
    }
    let multiplicateur = 1 - reduc / 100;
    let res = prix * multiplicateur;
    return Math.round((res + Number.EPSILON) * 100) / 100;
}

/**
 * Convertit une note en représentation visuelle HTML
 * 
 * @param {Number} note La note
 * @returns {String | undefined} Le code HTML correspondant
 */
function noteToIcone(note) {
    const noteMax = 5;
    const etoilePleine = '<i class="bi bi-star-fill"></i>';
    const etoileMoitie = '<i class="bi bi-star-half"></i>';
    const etoileVide = '<i class="bi bi-star"></i>';

    if (note < 0 || note > noteMax || note % 0.5 !== 0) { return; }

    let html = '';

    for (let i = 1; i <= noteMax; i++) {
        if (note >= i) {
            html += etoilePleine;
        } else if (note >= i - 0.5) {
            html += etoileMoitie;
        } else {
            html += etoileVide;
        }
    }

    return html;
}

/**
 * Retourne le HTML affichant les différentes catégories d'articles disponibles
 * 
 * @param {Array} articles Les articles
 * @returns {String} Le HTML correspondant
 */
function afficherCategories(articles) {
    articles.sort((a, b) => { // Tri des catégories par ordre croissant
        const nomA = a.categorie.toLowerCase();
        const nomB = b.categorie.toLowerCase();

        if (nomA < nomB) {
            return -1;
        }
        if (nomA > nomB) {
            return 1;
        }
        return 0;
    });

    const compteCategories = {};
    let html = `<h4>Catégorie</h4>`;

    articles.forEach(article => {
        const categorie = article.categorie;
        if (compteCategories[categorie]) {
            compteCategories[categorie]++;
        } else {
            compteCategories[categorie] = 1;
        }
    });

    for (const categorie in compteCategories) {
        html += `<div><input type="checkbox" name=${`categorie-${categorie}`} id=${`categorie-${categorie}`} class="ms-2"/> <label for=${`categorie-${categorie}`}>${categorie}</label> <span>(${compteCategories[categorie]})</span></div>`;
    }

    return html;
}

/**
 * Retourne le HTML affichant les différentes marques d'articles disponibles
 * 
 * @param {Array} articles Les articles
 * @returns {String} Le HTML correspondant
 */
function afficherMarques(articles) {
    articles.sort((a, b) => { // Tri des marques par ordre croissant
        const nomA = a.marque.toLowerCase();
        const nomB = b.marque.toLowerCase();

        if (nomA < nomB) {
            return -1;
        }
        if (nomA > nomB) {
            return 1;
        }
        return 0;
    });

    const compteMarques = {};
    let html = `<h4>Marque</h4>`;

    articles.forEach(article => {
        const marque = article.marque;
        if (compteMarques[marque]) {
            compteMarques[marque]++;
        } else {
            compteMarques[marque] = 1;
        }
    });

    for (const marque in compteMarques) {
        html += `<div><input type="checkbox" name=${`marque-${marque}`} id=${`marque-${marque}`} class="ms-2"/> <label for=${`marque-${marque}`}>${marque}</label> <span>(${compteMarques[marque]})</span></div>`;
    }

    return html;
}

/**
 * Retourne le HTML affichant les articles disponibles pour les différents sexes
 * 
 * @param {Array} articles Les articles
 * @returns {String} Le HTML correspondant
 */
function afficherSexes(articles) {

    const compteSexes = {};
    let html = `<h4>Sexe</h4>`;

    articles.forEach(article => {
        const sexe = article.sexe;
        if (compteSexes[sexe]) {
            compteSexes[sexe]++;
        } else {
            compteSexes[sexe] = 1;
        }
    });

    for (const sexe in compteSexes) {
        html += `<div><input type="checkbox" name=${`sexe-${sexe}`} id=${`sexe-${sexe}`} class="ms-2"/> <label for=${`sexe-${sexe}`}>${sexe}</label> <span>(${compteSexes[sexe]})</span></div>`;
    }

    return html;
}

/**
 * Retourne le HTML affichant les différentes couleurs d'articles disponibles
 * 
 * @param {Array} articles Les articles
 * @returns {String} Le HTML correspondant
 */
function afficherCouleurs(articles) {
    articles.sort((a, b) => { // Tri des couleurs par ordre croissant
        const nomA = a.couleur.toLowerCase();
        const nomB = b.couleur.toLowerCase();

        if (nomA < nomB) {
            return -1;
        }
        if (nomA > nomB) {
            return 1;
        }
        return 0;
    });


    const compteCouleurs = {};
    let html = "";

    articles.forEach(article => {
        const couleur = article.couleur;
        if (compteCouleurs[couleur]) {
            compteCouleurs[couleur]++;
        } else {
            compteCouleurs[couleur] = 1;
        }
    });

    for (const couleur in compteCouleurs) {
        html += `<div><input type="checkbox" id=${`couleur-${couleur}`} name=${`couleur-${couleur}`} style="accent-color: ${getHex(couleur)};" class="ms-2"/> <label for=${`couleur-${couleur}`}>${couleur}</label> <span>(${compteCouleurs[couleur]})</span></div>`;
    }

    return html;
}

/**
 * Affiche le slider permettant de sélectionner une gamme de prix
 * 
 * @param {Array} articles Les articles
 */
function afficherSliderPrix(articles) {

    if (!articles || !articles.length) { return; }

    let prixMinimum = Number.MAX_VALUE;
    let prixMaximum = Number.MIN_VALUE;

    articles.forEach(article => {
        let prix = article.reduction ? calculerReduction(article.prix, article.reduction) : article.prix;

        if (prix < prixMinimum) {
            prixMinimum = Math.floor(prix);
        }
        if (prix > prixMaximum) {
            prixMaximum = Math.ceil(prix);
        }
    });

    $(function () {
        $("#slider-prix").slider({
            range: true,
            min: prixMinimum,
            max: prixMaximum,
            values: [prixMinimum, prixMaximum],
            animate: 500,
            step: 1,
            slide: function (event, ui) {
                $("#prix-selectionne").val(ui.values[0] + " € - " + ui.values[1] + " €");
            }
        });
        $("#prix-selectionne").val($("#slider-prix").slider("values", 0) +
            " € - " + $("#slider-prix").slider("values", 1) + " €");
    });
}

/**
 * Renvoie le code hexadécimal des couleurs de base en français
 * 
 * @param {String} nom 
 * @returns {String | null} Le code correspondant
 */
function getHex(nom) {
    const couleurs = {
        "Blanc": "#fff",
        "Noir": "#000",
        "Rouge": "#f00",
        "Vert": "#0f0",
        "Bleu": "#00f",
        "Jaune": "#ff0",
        "Orange": "#f80",
        "Rose": "#ff69b4",
        "Marron": "#8b4513",
        "Gris": "#888",
        "Multicolore": "linear-gradient(45deg, #ff0000 0%, #ff9a00 10%, #d0de21 20%, #4fdc4a 30%, #3fdad8 40%, #2fc9e2 50%, #1c7fee 60%, #5f15f2 70%, #ba0cf8 80%, #fb07d9 90%, #ff0000 100%)"
    };

    const nomMaj = nom.charAt(0).toUpperCase() + nom.slice(1).toLowerCase();
    return couleurs[nomMaj] || null;
}

/**
 * S'occupe de l'affichage de l'un ou l'autre onglet
 */
function gererOnglets() {
    $("#boutonAccueil").click(() => {
        if ($(".corps").css("display") !== "none") { return; }

        $(".panier").toggleClass("container-fluid d-flex flex-column d-none");
        $(".corps").toggleClass("container-fluid d-flex flex-row d-none");
    });

    $("#boutonPanier").click(() => {
        if ($(".panier").css("display") !== "none") { return; }

        $(".corps").toggleClass("container-fluid d-flex flex-row d-none");
        $(".panier").toggleClass("container-fluid d-flex flex-column d-none");
    });
}

/**
 * Gère les formulaires de la page
 */
function gererFormulaires() {
    $("form").submit(async function (e) {
        e.preventDefault();
        var donnees = Object.fromEntries(new FormData(e.target));
        $(this).trigger("reset");

        var nvDonnees = await envoyerDonnees(donnees);
        if (typeof nvDonnees === "object") {
            nvDonnees = Object.values(nvDonnees);
        }
        if (!!nvDonnees && nvDonnees.length) {
            majContenu(nvDonnees);
        }
        else {
            articles = await recupererDonnees("./articles.json");
            majContenu(articles);
            afficherSliderPrix();
            alert("Aucun article correspondant à vos critères n'a été trouvé.");
        }
    });

    $("#bouton-reinitialiser-filtres").click(async function (e) {
        articles = await recupererDonnees("./articles.json");
        majContenu(articles);
    });

    $(".bouton-ajout-panier").click(async function (e) {
        let id = [e.target.id];
        var panier = await envoyerDonnees(id, "./bdd.php");
        var nbArticles = 0;
        panier.forEach(article => {
            nbArticles += article['quantite'];
        });
        $("#boutonPanier span").html(nbArticles);
        majPanier(panier);
    });

    $(document).on('change', ".selecteurQuantite", async function (e) {
        if (e.target.value < 1) {
            e.target.value = 1;
        }
        let id = e.target.id.split('-')[1];
        var panier = await envoyerDonnees({ maj: id, nv: e.target.value }, "./bdd.php");
        var nbArticles = 0;
        panier.forEach(article => {
            nbArticles += article['quantite'];
        });
        $("#boutonPanier span").html(nbArticles);
        majPanier(panier);
    });

    $(document).on('click', ".supprimerArticle", async function (e) {
        let id = e.target.id.split('-')[1];
        var panier = await envoyerDonnees({ suppr: id }, "./bdd.php");
        var nbArticles = 0;
        panier.forEach(article => {
            nbArticles += article['quantite'];
        });
        $("#boutonPanier span").html(nbArticles);
        majPanier(panier);
    });
}

/**
 * Envoie des données au serveur
 * 
 * @param {Array} donnees Les données à envoyer
 * @param {String} url L'URL vers laquelle envoyer les données
 * @return {Promise<any>} La réponse du serveur
 */
async function envoyerDonnees(donnees, url = "./api.php") {
    let headers = new Headers();
    headers.append("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
    let config = {
        method: "POST",
        headers: headers,
        body: JSON.stringify(donnees),
    };
    const req = await fetch(url, config);

    return await req.json();
}

/**
 * Met à jour l'affichage du panier
 * 
 * @param {Array} panier Le tableau contenant les données du panier
 */
async function majPanier(panier) {
    if (panier.length > 0) {
        $("#tableau-panier tbody").html(afficherPanier(panier));
        $("#panier-vide").css("display", "none");
        $("#tableau-panier").css("display", "table");
        $(".section-bas-panier").css("display", "initial");
    } else {
        $("#panier-vide").css("display", "initial");
        $("#tableau-panier").css("display", "none");
        $(".section-bas-panier").css("display", "none");
    }
}

/**
 * Retourne le HTML permettant d'afficher le panier
 * 
 * @param {Array} panier Le tableau contenant les données du panier
 * @returns Le code HTML correspondant
 */
function afficherPanier(panier) {
    var html = "";
    var total = 0;
    panier.forEach(article => {
        let prix = article['reduction'] !== null
            ? calculerReduction(article['prix'], article['reduction'])
            : article['prix'];
        total += prix * article['quantite'];
        html += `<tr>
            <td><img src="./images/${article['id']}.jpg" alt="${article['description']}" /> ${article['description']}</td>
            <td>${prix.toLocaleString()} €</td>
            <td><input type="number" class="selecteurQuantite" name="selecteurQuantite-${article['id']}" id="selecteurQuantite-${article['id']}" value=${article['quantite']} /></td>
            <td>${(prix * article['quantite']).toLocaleString()} €</td>
            <td><button type="button" class="supprimerArticle" id="supprimerArticle-${article['id']}">x</button></td>
        </tr>`;
    });

    afficherTotal(total);
    
    return html;
}

/**
 * Affiche le total de la commande
 * 
 * @param {Number} total Le total
 */
function afficherTotal(total) {
    $("#recapitulatif-panier h6 span").html(`${total.toLocaleString()} €`);
}