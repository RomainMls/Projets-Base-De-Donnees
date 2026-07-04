<!DOCTYPE html>
<html>

<?php
$bdd = new PDO('mysql:host=db;dbname=group12;charset=utf8', 'group12', 'qnvIHYuGXBFbmAEU');
?>

<head>
   <title>Moyenne de temps d'arrêt</title>
</head>

<!-- requete pour trouver le temps d'arrêt moyen par trajet et par itinéraire-->
<?php
$sql = "SELECT itineraire.nom, horaire.trajet_id, SEC_TO_TIME(AVG(TIME_TO_SEC(TIMEDIFF(horaire.HEURE_DEPART, horaire.HEURE_ARRIVEE)))) AS AVG_STOP_TIME FROM horaire
JOIN itineraire ON horaire.itineraire_ID = itineraire.ID
WHERE horaire.HEURE_ARRIVEE IS NOT NULL AND horaire.HEURE_DEPART IS NOT NULL
GROUP BY itineraire.NOM, horaire.TRAJET_ID WITH ROLLUP ORDER BY itineraire.NOM, AVG_STOP_TIME";

$resultat = $bdd->query($sql);
?>

<body>
    
    <center>
    <h1>Moyenne de temps d'arrêt par trajet</h1>
    </center>

    <center>
        <table border="1" style="width:80%; text-align:center;">
            <tr>
                <th>Itinéraire</th>
                <th>Trajet id</th>
                <th>Moyenne temps d'arrêt</th>
            </tr>
            <?php
            
            // crée chaque ligne du tableau 
            $soustotaux; // pour stocker les lignes de sous-totaux
            $total; // pour stocker la ligne du total
            $ordre_ligne = [];
                while ($row = $resultat->fetch()){
                    $nom_courant = $row['nom'];

                    if (!is_null($nom_courant) && $nom_courant !== $nom_precedent && isset($soustotaux)) {
                        $ordre_ligne[] = $soustotaux;
                    }

                    if( is_null($row['nom']))
                        $total = $row;
                    else if(is_null($row['trajet_id']))
                        $soustotaux = $row;
                    else
                        $ordre_ligne[] = $row;

                    if (!is_null($nom_courant)){
                        $nom_precedent = $nom_courant;
                    }
                }
                $ordre_ligne[] = $soustotaux;
                $ordre_ligne[] = $total;

                foreach ($ordre_ligne as $ligne) {
                    echo "<tr>";
                
                    // Ligne du total général
                    if (is_null($ligne['nom']) && is_null($ligne['trajet_id'])) {
                        echo "<td><strong>Total général</strong></td><td></td><td>" . htmlentities($ligne['AVG_STOP_TIME']) . "</td>";
                    }
                    // Sous-total
                    else if (!is_null($ligne['nom']) && is_null($ligne['trajet_id'])) {
                        echo "<td><strong>" . htmlentities($ligne['nom']) . "</strong></td><td><em>Sous-total</em></td><td>" . htmlentities($ligne['AVG_STOP_TIME']) . "</td>";
                    }
                    // Ligne normale
                    else {
                        echo "<td>" . htmlentities($ligne['nom']) . "</td><td>" . htmlentities($ligne['trajet_id']) . "</td><td>" . htmlentities($ligne['AVG_STOP_TIME']) . "</td>";
                    }
                
                    echo "</tr>";
                }
            ?>
        </table>
    </center>
</body>
</html>