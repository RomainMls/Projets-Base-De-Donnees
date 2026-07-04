<!DOCTYPE html>
<html>
<?php
$bdd = new PDO('mysql:host=db;dbname=group12;charset=utf8', 'group12', 'qnvIHYuGXBFbmAEU');
?>

<head>
   <title>Filtres / Contraintes</title>
</head>

<body>
   <center>
      <h1>Contraintes de contenance et d'égalité sur les tables AGENCE, HORAIRE et EXCEPTION</h1>
   </center>
   <center>
      <table border="1" style="width:80%; text-align:center;">
         <tr>
            <th>Agence</th>
            <th>Horaire</th>
            <th>Exception</th>
         </tr>
         <tr>
            <td>
               <form method="post" action="1-filtre_agence_horaire_exception.php">
                  <input type="hidden" name="formulaire" value="agence">
                  <label for="id">Id</label><br>
                  <input type="text" id="id" name="id" placeholder="ID"><br></br>
                  <label for="nom">Nom</label><br>
                  <input type="text" id="nom" name="nom" placeholder="NOM"><br></br>
                  <label for="url">Url</label><br>
                  <input type="text" id="url" name="url" placeholder="URL"><br></br>
                  <label for="fuseau_horaire">Fuseau horaire</label><br>
                  <input type="text" id="fuseau_horaire" name="fuseau_horaire" placeholder="FUSEAU_HORAIRE"><br></br>
                  <label for="telephone">Téléphone</label><br>
                  <input type="text" id="telephone" name="telephone" placeholder="TELEPHONE"><br></br>
                  <label for="siege">Siège</label><br>
                  <input type="text" id="siege" name="siege" placeholder="SIEGE"><br></br>
                  <input type="submit" value="Soumettre">
               </form>
            </td>
            <td>
               <form method="post" action="1-filtre_agence_horaire_exception.php">
                  <input type="hidden" name="formulaire" value="horaire">
                  <label for="trajet_id">Trajet ID</label><br>
                  <input type="text" id="trajet_id" name="trajet_id" placeholder="TRAJET_ID"><br></br>
                  <label for="itineraire_id">Itinéraire ID</label><br>
                  <input type="text" id="itineraire_id" name="itineraire_id" placeholder="ITINERAIRE_ID"><br></br>
                  <label for="arret_id">Arret ID</label><br>
                  <input type="text" id="arret_id" name="arret_id" placeholder="ARRET_ID"><br></br>
                  <label for="heure_arrivee">Heure arrivée</label><br>
                  <input type="text" id="heure_arrivee" name="heure_arrivee" placeholder="HEURE_ARRIVEE"><br></br>
                  <label for="heure_depart">Heure départ</label><br>
                  <input type="text" id="heure_depart" name="heure_depart" placeholder="HEURE_DEPART"><br></br>
                  <input type="submit" value="Soumettre">
               </form>
            </td>
            <td>
               <form method="post" action="1-filtre_agence_horaire_exception.php">
                  <input type="hidden" name="formulaire" value="exception">
                  <label for="service_id">Service ID</label><br>
                  <input type="text" id="service_id" name="service_id" placeholder="SERVICE_ID"><br></br>
                  <label for="date">Date</label><br>
                  <input type="text" id="date" name="date" placeholder="DATE"><br></br>
                  <label for="code">Code</label><br>
                  <input type="text" id="code" name="code" placeholder="CODE"><br></br>
                  <input type="submit" value="Soumettre">
               </form>
            </td>
         </tr>
         <tr>
            <td>
               <p>
                  <?php
                  if ($_SERVER['REQUEST_METHOD'] === "POST") {
                     if (!empty($_POST) && $_POST['formulaire'] == 'agence') {
                        $sql = 'SELECT * FROM agence';
                        $arraySqlFields = [];
                        $arraySqlConditions = [];
                        $resultat = false;

                        if (!empty($_POST['id'])) {
                           $arraySqlFields['id'] = $_POST["id"];
                           $arraySqlConditions[] = "ID = :id";
                        }
                        if (!empty($_POST['nom'])) {
                           $arraySqlFields['nom'] = "%" . $_POST["nom"] . "%";
                           $arraySqlConditions[] = "NOM LIKE :nom";
                        }
                        if (!empty($_POST['url'])) {
                           $arraySqlFields['url'] = "%" . $_POST["url"] . "%";
                           $arraySqlConditions[] = "URL LIKE :url";
                        }
                        if (!empty($_POST['fuseau_horaire'])) {
                           $arraySqlFields['fuseau_horaire'] = "%" . $_POST["fuseau_horaire"] . "%";
                           $arraySqlConditions[] = "FUSEAU_HORAIRE LIKE :fuseau_horaire";
                        }
                        if (!empty($_POST['telephone'])) {
                           $arraySqlFields['telephone'] = "%" . $_POST["telephone"] . "%";
                           $arraySqlConditions[] = "TELEPHONE LIKE :telephone";
                        }
                        if (!empty($_POST['siege'])) {
                           $arraySqlFields['siege'] = "%" . $_POST["siege"] . "%";
                           $arraySqlConditions[] = "SIEGE LIKE :siege";
                        }

                        if (!empty($arraySqlConditions)) {
                           $sql .= " WHERE " . implode(" AND ", $arraySqlConditions);
                        }

                        $req = $bdd->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                        $req->execute($arraySqlFields);

                        $premierTuple = $req->fetch(PDO::FETCH_ASSOC);#PDO renvoit 2 tableaux PDO::FETCH_ASSOC permet de juste prendre le tableau associatif

                        //Faire la table avec les tuples retournés
                        if ($premierTuple) {
                           echo '<table border="1" style="margin:auto; width:80%; text-align:center;"><tr>';

                           foreach (array_keys($premierTuple) as $colonne) {
                              echo '<th>' . htmlentities($colonne) . '</th>';
                           }
                           echo '</tr>';
                           echo '<tr>';
                           foreach ($premierTuple as $value) {
                              echo '<td>' . htmlentities($value) . '</td>';
                           }
                           echo '</tr>';

                           while ($tuple = $req->fetch(PDO::FETCH_ASSOC)) {
                              echo '<tr>';
                              foreach ($tuple as $value) {
                                 echo '<td>' . htmlentities($value) . '</td>';
                              }
                              echo '</tr>';
                           }

                           echo '</table>';
                        } else {
                           echo "<p>Il n'y a pas agence avec ces conditions</p>";
                        }
                     }
                  }
                  ?>
               </p>
            </td>
            <td>
               <p>
                  <?php
                  if (!empty($_POST) && $_POST['formulaire'] == 'horaire') {
                     $sql = 'SELECT * FROM horaire';
                     $arraySqlFields = [];
                     $arraySqlConditions = [];

                     if (!empty($_POST['trajet_id'])) {
                        $arraySqlFields['trajet_id'] = "%" . $_POST["trajet_id"] . "%";
                        $arraySqlConditions[] = "TRAJET_ID =:trajet_id";
                     }
                     if (!empty($_POST['itineraire_id'])) {
                        $arraySqlFields['itineraire_id'] = $_POST["itineraire_id"];
                        $arraySqlConditions[] = "ITINERAIRE_ID = :itineraire_id";
                     }
                     if (!empty($_POST['arret_id'])) {
                        $arraySqlFields['arret_id'] = $_POST["arret_id"];
                        $arraySqlConditions[] = "ARRET_ID = :arret_id";
                     }
                     if (!empty($_POST['heure_arrivee'])) {
                        $arraySqlFields['heure_arrivee'] = $_POST["heure_arrivee"];
                        $arraySqlConditions[] = "HEURE_ARRIVEE = :heure_arrivee";
                     }
                     if (!empty($_POST['heure_depart'])) {
                        $arraySqlFields['heure_depart'] = $_POST["heure_depart"];
                        $arraySqlConditions[] = "HEURE_DEPART = :heure_depart";
                     }

                     if (!empty($arraySqlConditions)) {
                        $sql .= " WHERE " . implode(" AND ", $arraySqlConditions);
                     }
                     $req = $bdd->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                     $req->execute($arraySqlFields);

                     $premierTuple = $req->fetch(PDO::FETCH_ASSOC);

                     //Faire la table avec les tuples retournés
                     if ($premierTuple) {
                        echo '<table border="1" style="margin:auto; width:80%; text-align:center;"><tr>';

                        foreach (array_keys($premierTuple) as $colonne) {
                           echo '<th>' . htmlentities($colonne) . '</th>';
                        }
                        echo '</tr>';
                        echo '<tr>';
                        foreach ($premierTuple as $value) {
                           echo '<td>' . htmlentities($value) . '</td>';
                        }
                        echo '</tr>';

                        while ($tuple = $req->fetch(PDO::FETCH_ASSOC)) {#PDO renvoit 2 tableaux PDO::FETCH_ASSOC permet de juste prendre le tableau associatif
                           echo '<tr>';
                           foreach ($tuple as $value) {
                              echo '<td>' . htmlentities($value) . '</td>';
                           }
                           echo '</tr>';
                        }

                        echo '</table>';
                     } else {
                        echo "<p>Il n'y a pas d'horaire avec ces conditions</p>";
                     }
                  }
                  ?>
               </p>
            </td>
            <td>
               <p>
                  <?php
                  if (!empty($_POST) && $_POST['formulaire'] == 'exception') {
                     $sql = 'SELECT * FROM exception';
                     $arraySqlFields = [];
                     $arraySqlConditions = [];
                     $resultat = false;

                     if (!empty($_POST['service_id'])) {
                        $arraySqlFields['service_id'] = $_POST["service_id"];
                        $arraySqlConditions[] = "SERVICE_ID = :service_id";
                     }
                     if (!empty($_POST['date'])) {
                        $arraySqlFields['date'] = "%" . $_POST["date"] . "%";
                        $arraySqlConditions[] = "DATE =:date";
                     }
                     if (!empty($_POST['code'])) {
                        $arraySqlFields['code'] = $_POST["code"];
                        $arraySqlConditions[] = "CODE = :code";
                     }

                     if (!empty($arraySqlConditions)) {
                        $sql .= " WHERE " . implode(" AND ", $arraySqlConditions);
                     }
                     $req = $bdd->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                     $req->execute($arraySqlFields);

                     $premierTuple = $req->fetch(PDO::FETCH_ASSOC);

                     //Faire la table avec les tuples retournés
                     if ($premierTuple) {
                        echo '<table border="1" style="margin:auto; width:80%; text-align:center;"><tr>';

                        foreach (array_keys($premierTuple) as $colonne) {
                           echo '<th>' . htmlentities($colonne) . '</th>';
                        }
                        echo '</tr>';
                        echo '<tr>';
                        foreach ($premierTuple as $value) {
                           echo '<td>' . htmlentities($value) . '</td>';
                        }
                        echo '</tr>';

                        while ($tuple = $req->fetch(PDO::FETCH_ASSOC)) {#PDO renvoit 2 tableaux PDO::FETCH_ASSOC permet de juste prendre le tableau associatif
                           echo '<tr>';
                           foreach ($tuple as $value) {
                              echo '<td>' . htmlentities($value) . '</td>';
                           }
                           echo '</tr>';
                        }

                        echo '</table>';
                     } else {
                        echo "<p>Il n'y a pas d'exception avec ces conditions</p>";
                     }
                  }
                  ?>
               </p>
            </td>
         </tr>
      </table>
   </center>
   <?php

   ?>
</body>

</html>