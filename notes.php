<?php
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

        $requete = "SELECT id_etudiant,nom_etudiant FROM `ETUDIANT` WHERE id_classe LIKE 1 ";
        $resultat = $bdd->query($requete);
        $tab = $resultat->fetchAll();
        
        foreach ($tab as $cellule)
        {
            echo $cellule['id_etudiant']." : ".$cellule['nom_etudiant']."<br>";
        }
    
?>