<!DOCTYPE html>
<html>
<?php
$bdd = new PDO('mysql:host=db;dbname=group12;charset=utf8', 'group12', 'qnvIHYuGXBFbmAEU');
?>

<head>
   <title>Formulaire gares</title>
</head>

<body>
   <center>
      <h1>Formulaire permettant de rechercher les noms de gares contenant une chaîne donnée ainsi que le nombre d'arrêts par service</h1>
      <form method="post" action="5-rechercher_gare.php">
         <input type="hidden" name="formulaire" value="rechercher_gare">
         <label for="partie_nom">Nom gare (partiel ou pas)</label><br>
         <input type="text" id="partie_nom" name="partie_nom" placeholder="NOM (PARTIEL OU PAS)"><br></br>
         <label for="nombre_min">Nombre minimum d'arrêts</label><br>
         <input type="text" id="nombre_min" name="nombre_min" placeholder="NOMBRE MIN"><br></br>
         <input type="submit" value="Soumettre">
      </form>
      <?php
      if (!empty($_POST["partie_nom"])) {
         # COLLATE utf8mb4_general_ci pour le case insensitive (ci = case insensitive)
         $sql = 'SELECT * FROM nb_trains_gare_service
               WHERE LOWER(NOM_GARE) COLLATE utf8mb4_general_ci LIKE :nom AND (NB_ARRETS >= :nombre OR NB_ARRIVEES >= :nombre OR NB_DEPARTS >= :nombre) ORDER BY NB_ARRETS DESC, NB_ARRIVEES DESC, NB_DEPARTS DESC;';
         $sth = $bdd->prepare($sql);
         $nombre = 0;
         if (!empty($_POST["nombre_min"])) {
            $nombre = $_POST["nombre_min"];
         }

         if ($sth->execute(array("nom" => "%" . strtolower($_POST["partie_nom"]) . "%", "nombre" => $nombre))) {
            if ($tuple = $sth->fetch(PDO::FETCH_ASSOC)) {
               echo "<table style='width:100%'>
               <tr>
                  <th>NOM_GARE</th>
                  <th>NOM_SERVICE</th>
                  <th>NB_ARRETS</th>
                  <th>NB_ARRIVEES</th>
                  <th>NB_DEPARTS</th>
               </tr>";

               do {
                  echo "<tr>";
                  echo "<td style='text-align: center;'>" . htmlentities($tuple["NOM_GARE"]) . "</td>";
                  echo "<td style='text-align: center;'>" . htmlentities($tuple["NOM_SERVICE"]) . "</td>";
                  echo "<td style='text-align: center;'>" . htmlentities($tuple["NB_ARRETS"]) . "</td>";
                  echo "<td style='text-align: center;'>" . htmlentities($tuple["NB_ARRIVEES"]) . "</td>";
                  echo "<td style='text-align: center;'>" . htmlentities($tuple["NB_DEPARTS"]) . "</td>";
                  echo "</tr>";
               } while ($tuple = $sth->fetch(PDO::FETCH_ASSOC));
               echo "</table>";
            } else {
               echo "<p>Aucun résultat trouvé</p>";
            }
         }
      } else if (isset($_POST["partie_nom"])) {
         echo "<p>Vous n'avez pas saisi un nom de gare </p>";
      }
      ?>
   </center>
</body>

</html>