<?php
    header('Access-Control-Allow-Origin: *');
    header("Content-Type: text/html; charset=UTF-8");

    function envoiJSON($tab){
    header('Content-Type: application/json');
    $json = json_encode($tab, JSON_UNESCAPED_UNICODE);
    echo $json;
    }
    $host = 'localhost'; //variables de connexion
    $dbname = 'poulpy2';
    $username = 'root';
    $password = '';
    try {
        $bdd = new PDO('mysql:host='. $host .';dbname='. $dbname .';charset=utf8',
        $username, $password);
        } catch(Exception $e) {
        // Si erreur, tout arrêter
        die('Erreur : '. $e->getMessage());
        }

        $requete = "SELECT nom_prof FROM `PROFESSEUR` WHERE id_prof LIKE '".$_GET['id_prof']."'";
        $resultat = $bdd->query($requete);
        $tab = $resultat->fetchAll();
        
        envoiJSON($tab);
    
?>