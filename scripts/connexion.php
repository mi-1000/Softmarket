<?php

/**
 * Singleton se connectant à la base de données
 */
class Connexion
{
    private static $_instance = null;

    private static $_url;
    private static $_utilisateur;
    private static $_mdp;
    private static $_bdd;
    private static $_port;

    /**
     * Définit les modalités de connexion
     * 
     * @param string $url         L'url de connexion
     * @param string $utilisateur Le nom d'utilisateur
     * @param string $mdp         Le mot de passe
     * @param string $bdd         Le nom de la base de données
     * @param string $port        Le port
     */
    public static function definirConnexion($url, $utilisateur, $mdp, $bdd, $port)
    {
        self::$_url = $url;
        self::$_utilisateur = $utilisateur;
        self::$_mdp = $mdp;
        self::$_bdd = $bdd;
        self::$_port = $port;
    }

    /**
     * Renvoie l'instance de la connexion
     * 
     * @return mysqli L'instance
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new mysqli(self::$_url, self::$_utilisateur, self::$_mdp, self::$_bdd, self::$_port);
        }
        return self::$_instance;
    }

    /**
     * Ferme la connexion à la base de données
     */
    public static function fermerConnexion()
    {
        if (!is_null(self::$_instance)) {
            self::$_instance->close();
        }
    }
}