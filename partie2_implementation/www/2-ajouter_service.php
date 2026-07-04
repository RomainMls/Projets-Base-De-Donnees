<!DOCTYPE html>
<html>

<head>
   <title>Ajouter service</title>
</head>

<body>
   <center>
      <h1>Formulaire pour ajouter un service</h1>
   </center>
   <center>

      <form method="post" action="2-ajouter_service.php">
         <input type="hidden" name="formulaire" value="nom_service">
         <label for="nom">Nom</label><br>
         <input type="text" id="nom" name="nom" placeholder="NOM"><br></br>

         <div style="display: inline-block; text-align: left;">
            <input type="checkbox" id="lundi" name="lundi" value="1"><label for="lundi">Lundi</label><br>
            <input type="checkbox" id="mardi" name="mardi" value="1"><label for="mardi">Mardi</label><br>
            <input type="checkbox" id="mercredi" name="mercredi" value="1"><label for="mercredi">Mercredi</label><br>
            <input type="checkbox" id="jeudi" name="jeudi" value="1"><label for="jeudi">Jeudi</label><br>
            <input type="checkbox" id="vendredi" name="vendredi" value="1"><label for="vendredi">Vendredi</label><br>
            <input type="checkbox" id="samedi" name="samedi" value="1"><label for="samedi">Samedi</label><br>
            <input type="checkbox" id="dimanche" name="dimanche" value="1"><label for="dimanche">Dimanche</label><br>
         </div>

         <br></br>
         <label for="date_debut">Date début</label><br>
         <input type="date" id="date_debut" name="date_debut"><br><br>
         <label for="date_fin">Date fin</label><br>
         <input type="date" id="date_fin" name="date_fin"><br><br>
         <label for="text">Zone de texte pour les exceptions</label><br>
         <textarea rows="5" id="text" name="text"></textarea><br></br>
         <input type="submit" value="Soumettre">
      </form>

      <?php
      $bdd = new PDO('mysql:host=db;dbname=group12;charset=utf8', 'group12', 'qnvIHYuGXBFbmAEU');

      if ($_SERVER['REQUEST_METHOD'] === "POST" and !empty($_POST['nom']) and !empty($_POST['date_debut']) and !empty($_POST['date_fin'])) {
         $_POST['lundi'] = isset($_POST['lundi']) ? 1 : 0;
         $_POST['mardi'] = isset($_POST['mardi']) ? 1 : 0;
         $_POST['mercredi'] = isset($_POST['mercredi']) ? 1 : 0;
         $_POST['jeudi'] = isset($_POST['jeudi']) ? 1 : 0;
         $_POST['vendredi'] = isset($_POST['vendredi']) ? 1 : 0;
         $_POST['samedi'] = isset($_POST['samedi']) ? 1 : 0;
         $_POST['dimanche'] = isset($_POST['dimanche']) ? 1 : 0;
         try {
            $bdd->beginTransaction();

            $sql = 'INSERT INTO service(NOM, LUNDI, MARDI, MERCREDI, JEUDI, VENDREDI, SAMEDI, DIMANCHE, DATE_DEBUT, DATE_FIN)
            VALUES (:nom, :lundi, :mardi, :mercredi, :jeudi, :vendredi, :samedi, :dimanche, :date_debut, :date_fin)';
            $sth = $bdd->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $matching = array(
               'nom' => $_POST['nom'],
               'lundi' => $_POST['lundi'],
               'mardi' => $_POST['mardi'],
               'mercredi' => $_POST['mercredi'],
               'jeudi' => $_POST['jeudi'],
               'vendredi' => $_POST['vendredi'],
               'samedi' => $_POST['samedi'],
               'dimanche' => $_POST['dimanche'],
               'date_debut' => $_POST['date_debut'],
               'date_fin' => $_POST['date_fin']
            );
            if (!$sth->execute($matching)) {
               $bdd->rollBack();
               $error = $sth->errorInfo();
               echo "<p>Erreur SQL : " . htmlentities($error[2]) . "</p>";
               echo "<p>Rollback: aucune ligne insérée</p>";
               return;
            }

            if (!empty($_POST['text'])) {

               $id = $bdd->lastInsertId();
               $sql = 'INSERT INTO exception(SERVICE_ID, DATE, CODE) VALUES (:id, :date, :code)';
               $sth = $bdd->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

               $lines = explode("\n", $_POST['text']);
               if ($lines) {
                  foreach ($lines as $line) {
                     if ($line != '') {
                        $parts = explode(" ", trim($line));
                        if (count($parts) != 2) {
                           $bdd->rollBack();
                           echo "<p>Rollback: aucune ligne insérée. Le format des exceptions n'est pas correcte</p>";
                           return;
                        }
                        $date = $parts[0];
                        if ($date < $_POST['date_debut'] || $date > $_POST['date_fin']) { # Bien qu'on le vérifie déjà dans le modèle sql
                           $bdd->rollBack();
                           echo "<p>Rollback: aucune ligne insérée. Une des dates des exceptions n'est pas valide</p>";
                           return;
                        }

                        $type = strtoupper($parts[1]);
                        if ($type === 'INCLUS') {
                           $code = 1;
                        } elseif ($type === 'EXCLUS') {
                           $code = 2;
                        } else {
                           $bdd->rollBack();
                           echo "<p>Rollback: aucune ligne insérée. Type de l'exception inconnu</p>";
                           return;
                        }
                        $matching = array(
                           'id' => $id,
                           'date' => $date,
                           'code' => $code
                        );
                        if (!$sth->execute($matching)) {
                           $bdd->rollBack();
                           echo "<p>Rollback: aucune ligne insérée</p>";
                           $error = $sth->errorInfo();
                           echo "<p>Erreur SQL : " . htmlentities($error[2]) . "</p>";

                           return;
                        }
                     }
                  }
               }
            }

            $c = $bdd->query('SELECT COUNT(*) AS COUNT FROM service')->fetch();
            echo '<p>Nous avons maintenant ' .  $c['COUNT'] . ' services.</p>';

            $c = $bdd->query('SELECT COUNT(*) AS COUNT FROM exception')->fetch();
            echo '<p>Nous avons maintenant ' .  $c['COUNT'] . ' exceptions.</p>';

            $bdd->commit();
         } catch (\PDOException $e) {
            $bdd->rollBack();
            die($e->getMessage());
         }
      } else if ($_SERVER['REQUEST_METHOD'] === "POST" and isset($_POST['formulaire'])) {
         echo "<p>Pas d'ajout: un champ obligatoire est manquant (Nom, date début ou date fin)</p>";
      }
      ?>
   </center>
</body>

</html>