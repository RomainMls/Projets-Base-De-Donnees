<!DOCTYPE html>
<html>
<?php
$bdd = new PDO('mysql:host=db;dbname=group12;charset=utf8', 'group12', 'qnvIHYuGXBFbmAEU');
?>

<head>
   <title>Modifier un arrêt</title>
</head>

<body>
   <center>
      <table border="1" style="width:80%; text-align:center;">
         <tr>
            <th>Modifier un arrêt</th>
         </tr>
         <tr>
            <td>
               <form method="post" action="7-modifier_arret.php">
                  <input type="hidden" name="formulaire" value="arret">

                  <label for="id">ID</label><br>
                  <input type="number" id="id" name="id" placeholder="ID"><bf></br>

                  <label for="nom">Nom</label><br>
                  <input type="text" id="nom" name="nom" placeholder="NOM"><br></br>

                  <label for="newid">Nouvel ID</label><br>
                  <input type="number" id="newid" name="newid" placeholder="NOUVEL ID"><bf></br>

                  <label for="newnom">Nouveau Nom</label><br>
                  <input type="text" id="newnom" name="newnom" placeholder="NOUVEAU NOM"><bf></br>

                  <label for="newlongitude">Nouvelle Longitude</label><br>
                  <input type="number" id="newlongitude" name="newlongitude" placeholder="NOUVELLE LONGITUDE" step="any"><bf></br>

                  <label for="newlatitude">Nouvelle Latitude</label><br>
                  <input type="number" id="newlatitude" name="newlatitude" placeholder="NOUVELLE LATITUDE" step="any"><br></br>
                  
                  <input type="submit" name = "submit" value="Soumettre">
               </form>
            </td>
         </tr>
         <tr>
            <td>
               <p>
                  <?php
                  $bdd->beginTransaction();
                  try
                  {
                     if (!empty($_POST) && isset($_POST['formulaire']) && $_POST['formulaire'] == 'arret')
                     {
                        # partie 1 verifier l'identification
                        if(empty($_POST['id']) && empty($_POST['nom']))
                        {
                           echo "Entrez l'id ou le nom de l'arrêt que vous souhaitez modifier";
                           $bdd->rollBack();
                           return;
                        }
                        $sql = 'SELECT * FROM arret';
                        $arraySqlFields = [];
                        $arraySqlConditions = [];

                        if (!empty($_POST['id'])) {
                           $arraySqlFields['id'] = $_POST["id"];
                           $arraySqlConditions[] = "ID = :id";
                        }

                        if (!empty($_POST['nom'])) {
                           $arraySqlFields['nom'] = "%" . $_POST["nom"] . "%";
                           $arraySqlConditions[] = "NOM LIKE :nom";
                        }
                        if (!empty($arraySqlConditions)) {
                           $sql .= " WHERE " . implode(" AND ", $arraySqlConditions);
                        }
                        $req = $bdd->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                        $req->execute($arraySqlFields);
                        $result = $req->fetchAll(PDO::FETCH_ASSOC);
                        $nb_arret = count($result);
                        if($nb_arret < 1)
                        {
                           echo "Aucun arrêt trouvé";
                           $bdd->rollBack();
                           return;
                        }
                        if($nb_arret > 1)
                        {
                           echo "Plusieurs arrêts trouvés, veuillez être plus spécifique";
                        }
                        foreach($result as $tuple)
                        {
                           echo "<div>";
                           echo "<p>";
                           foreach ($tuple as $attribut => $value) {
                              echo "<strong>" . htmlentities($attribut) . "</strong>: " . htmlentities($value) . "<br>";
                           }
                           echo "</p>";
                           echo "</div><br>";
                        }
                        if($nb_arret > 1)
                        {
                           $bdd->rollBack();
                           return;
                        }
                        # partie 2 modification
                        if(empty($_POST['newid']) && empty($_POST['newnom']) && empty($_POST['newlongitude']) && empty($_POST['newlatitude']))
                        {
                           $bdd->rollBack();
                           return;
                        }
                        $sql = 'UPDATE arret';
                        $arraySqlConditions2 = [];
                        if (!empty($_POST['newid'])) {
                           $arraySqlFields['newid'] = $_POST["newid"];
                           $arraySqlConditions2[] = "ID = :newid";
                        }

                        if (!empty($_POST['newnom'])) {
                           $arraySqlFields['newnom'] = $_POST["newnom"];
                           $arraySqlConditions2[] = "NOM = :newnom";
                        }
                        if (!empty($_POST['newlongitude'])) {
                           $newlongitude = floatval($_POST['newlongitude']);
                           if($newlongitude > 6.15665815596 || $newlongitude < 2.51357303225)
                           {
                              echo "La nouvelle longitude doit être comprise dans [2.51357303225, 6.15665815596]";
                              $bdd->rollBack();
                              return;
                           }
                           $arraySqlFields['newlongitude'] = $newlongitude;
                           $arraySqlConditions2[] = "LONGITUDE = :newlongitude";
                        }
                        if (!empty($_POST['newlatitude'])) {
                           $newlatitude = floatval($_POST['newlatitude']);
                           if($newlatitude > 51.4750237087 || $newlatitude < 49.5294835476)
                           {
                              echo "La nouvelle latitude doit être comprise dans [49.5294835476, 51.4750237087]";
                              $bdd->rollBack();
                              return;
                           }
                           $arraySqlFields['newlatitude'] = $newlatitude;
                           $arraySqlConditions2[] = "LATITUDE = :newlatitude";
                        }
                        if (!empty($arraySqlConditions)) {
                           $sql .= " SET " . implode(", ", $arraySqlConditions2);
                           $sql .= " WHERE " . implode(" AND ", $arraySqlConditions);
                        }
                        $req = $bdd->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                        $result = $req->execute($arraySqlFields);
                        if($result)
                        {
                           $bdd->commit();
                           print("succès");
                           return;
                        }
                        $bdd->rollBack();
                        print("fail");
                     }
                  }
                  catch(Exception $e)
                  {
                     $bdd->rollBack();
                     echo "Error: " . $e->getMessage();
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