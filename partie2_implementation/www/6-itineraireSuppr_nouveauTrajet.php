<!DOCTYPE html>
<html>
<?php
$bdd = new PDO('mysql:host=db;dbname=group12;charset=utf8', 'group12', 'qnvIHYuGXBFbmAEU');

$liste_itiniraire_lock = null;
if (
   $_SERVER['REQUEST_METHOD'] === 'POST' and isset($_POST["formulaire"]) and
   ($_POST["formulaire"] === "choisir_itineraire" || $_POST["formulaire"] === "ajouter_trajet")
) {
   $liste_itiniraire_lock = 'lock';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' and isset($_POST['action']) and $_POST['action'] === 'annuler')
   $liste_itiniraire_lock = null;



if ($_SERVER['REQUEST_METHOD'] === 'POST' and $_POST["formulaire"] == "supprimer_itineraire" and !empty($_POST["liste_itineraire_suppr"])) {
   $itineraire_id = $_POST["liste_itineraire_suppr"];
   try {
      $bdd->beginTransaction();
      $sql = 'DELETE FROM itineraire WHERE ID = :id';
      $req = $bdd->prepare($sql);
      $req->execute(array('id' => $itineraire_id));
      $bdd->commit();
      $message = "L'itinéraire " . htmlentities($itineraire_id) . " a été supprimé avec tous les trajets instanciés de celui-ci";
   } catch (\PDOException $e) {
      $bdd->rollBack();
      $message = "Erreur : " . htmlentities($e->getMessage());
   }
}

?>

<head>
   <title>Formulaire suppr itinéraire + ajout trajet</title>
</head>

<body>
   <center>
      <h1>Formulaire permettant de supprimer un itinéraire et ses trajets et d'ajouter des trajets à un itinéraire</h1>
   </center>
   <form method="post" action="6-itineraireSuppr_nouveauTrajet.php">
      <input type="hidden" name="formulaire" value="supprimer_itineraire">
      <?php
      $req = $bdd->query('SELECT ID AS ID_ITINERAIRE, NOM AS NOM_ITINERAIRE FROM itineraire;');
      echo '<label for="liste_itineraire_suppr">Menu déroulant contenant les itinéraires à supprimer</label><br>';
      echo "<select id='liste_itineraire_suppr' name='liste_itineraire_suppr'>";
      while ($tuple = $req->fetch()) {
         echo "<option value='" . htmlentities($tuple['ID_ITINERAIRE']) . "' >ID " . htmlentities($tuple['ID_ITINERAIRE']) . " - " . htmlentities($tuple['NOM_ITINERAIRE']) . "</option>";
      }
      echo "</select>";
      echo "<input type='submit' value='Supprimer'>";
      ?> <!--Fin formulaire supprimer_itineraire-->
   </form>

   <?php
   #Pour afficher le message de retour en dessous du formulaire supprimer_itineraire et au dessus de choisir_itineraire
   if (!empty($message))
      echo "<p> " . htmlentities($message) . "</p>";
   ?>

   <div style="height : 20px" ;></div>

   <form method="post" action="6-itineraireSuppr_nouveauTrajet.php">
      <input type="hidden" name="formulaire" value="choisir_itineraire">
      <?php
      $req = $bdd->query('SELECT ID AS ID_ITINERAIRE, NOM AS NOM_ITINERAIRE FROM itineraire;');

      if (isset($liste_itiniraire_lock) and $liste_itiniraire_lock === 'lock')
         echo "<select disabled name='liste_itineraire'>";
      else {
         echo '<label for="liste_itineraire">Menu déroulant contenant les itinéraires avec la direction</label><br>';
         echo "<select id='liste_itineraire' name='liste_itineraire'>";
      }
      while ($tuple = $req->fetch()) {

         # Permet de redéfinir le champ par défault qui a été choisi avant que la page se reload 
         # On vérifie juste s'il est définit, si oui et qu'il est égal au champ de la liste déroulante qu'on créée on met le flag selected sur celui-ci
         echo "<option value='" . htmlentities($tuple['ID_ITINERAIRE']) . "'"
            . ((isset($_POST['liste_itineraire']) && $_POST['liste_itineraire'] === strval($tuple['ID_ITINERAIRE'])) ? " selected" : "")
            . ">ID " . htmlentities($tuple['ID_ITINERAIRE']) . " - " . htmlentities($tuple['NOM_ITINERAIRE']) . "</option>";
      }
      echo "</select>";

      if (isset($liste_itiniraire_lock) and $liste_itiniraire_lock === 'lock')
         echo "<select disabled name='liste_direction'>";
      else
         echo "<select name='liste_direction'>";
      ?>
      <option value='0' <?php if (isset($_POST['liste_direction']) && $_POST['liste_direction'] == '0') echo 'selected'; ?>>0 Aller </option>
      <option value='1' <?php if (isset($_POST['liste_direction']) && $_POST['liste_direction'] == '1') echo 'selected'; ?>>1 Retour</option>
      </select>
      <input type='submit' value='Créer un trajet'>

      <?php
      if (isset($liste_itiniraire_lock) and $liste_itiniraire_lock === 'lock')
         echo "<button type='submit' name='action' value='annuler'>Annuler</button>";

      ?>

   </form> <!--Fin formulaire choisir_itineraire-->

   <div style="height : 20px" ;></div>

   <?php

   if ($_SERVER['REQUEST_METHOD'] === 'POST' and isset($_POST["formulaire"]) and ($_POST["formulaire"] === "choisir_itineraire" or $_POST["formulaire"] === "ajouter_trajet") and $liste_itiniraire_lock !== null) {

      # Afficher le formulaire pour ajouter_trajet
      echo '<form method="post" action="6-itineraireSuppr_nouveauTrajet.php">';
      echo '<input type="hidden" name="formulaire" value="ajouter_trajet">';
      echo '<input type="hidden" name="liste_itineraire" value="' . htmlentities($_POST['liste_itineraire']) . '">';
      echo '<input type="hidden" name="liste_direction" value="' . htmlentities($_POST['liste_direction']) . '">';
      $itineraire_id = $_POST['liste_itineraire'];
      $direction = $_POST['liste_direction'];

      echo '<label for="id_trajet">Trajet</label><br>';
      echo '<input required type="text" id="id_trajet" name="id_trajet" placeholder="ID_TRAJET"><br></br>';
      $req = $bdd->query('SELECT ID AS ID_SERVICE, NOM AS NOM_SERVICE FROM service;');

      echo '<label for="liste_service">Menu déroulant avec les services</label><br>';
      echo "<select id='liste_service' name='liste_service'>";

      while ($tuple = $req->fetch()) {

         echo "<option value='" . htmlentities($tuple['ID_SERVICE']) . "'"
            . ((isset($_POST['ajouter_trajet']) && $_POST['ajouter_trajet'] === strval($tuple['ID_SERVICE'])) ? " selected" : "")
            . ">ID " . htmlentities($tuple['ID_SERVICE']) . " - " . htmlentities($tuple['NOM_SERVICE']) . "</option>";
      }
      echo "</select><br></br>";
      echo '<label for="texte">Zone de texte pour saisir les arrêts du trajet avec leurs heures</label><br>';
      echo "<textarea required rows='5' cols='40' id='texte' name='texte' placeholder=\"STOP1,hdepart1\nSTOP2,harrivé2,hdepart2\nSTOP3,harrivé3,hdepart3\nSTOP4,harrivé4\"></textarea><br><br>";
      echo '<input type="submit" value="Soumettre">';
      echo '</form>';
      #Fin formulaire ajouter_trajet

      # Requete pour créer le trajet et les horaires en respectant les contraintes d'intégrité bien qu'elles soient aussi dans le sql
      if (!empty($_POST['id_trajet']) and !empty($_POST['liste_service']) and !empty($_POST['texte'])) {
         $trajet_id = $_POST['id_trajet'];
         $service_id = $_POST['liste_service'];

         try {
            $bdd->beginTransaction();
            $condition = $bdd->prepare('SELECT TRAJET_ID FROM trajet WHERE TRAJET_ID = :trajet_id');
            $condition->execute(array("trajet_id" => $trajet_id));
            if ($condition->rowCount() > 0) {
               echo "<p>Vous essayez d'ajouter un trajet déjà existant (même ID).</p>";
               $bdd->rollBack();
               return;
            }
            $sql = 'INSERT INTO trajet(TRAJET_ID, SERVICE_ID, ITINERAIRE_ID, DIRECTION) VALUES (:trajet_id, :service_id, :itineraire_id, :direction)';
            $req = $bdd->prepare($sql);


            if (!$req->execute(array(
               "trajet_id" => $trajet_id,
               "service_id" => $service_id,
               "itineraire_id" => $itineraire_id,
               "direction" => $direction
            ))) {
               $bdd->rollBack();
               echo "<p>" . $e->getMessage() . "</p>";
               return;
            }

            $ordre_sequence = $bdd->prepare("SELECT ARRET_ID, SEQUENCE FROM arret_desservi WHERE ITINERAIRE_ID = :itineraire_id");
            $ordre_sequence->execute(array("itineraire_id" => $itineraire_id));

            # Tableau pour mapper chaque arret à sa séquence dans l'itinéraire. 
            # Utile pour vérifier si l'arret est dans les arrets desservis 
            # et si les arrets définis ont une séquence croissante/décroissante en fonction de la direction et de ce que l'utilisateur entre dans la zone de texte
            $sequence_tableau_mapping = [];
            $arrets_id_input = [];
            while ($tuple = $ordre_sequence->fetch(PDO::FETCH_ASSOC)) {
               $sequence_tableau_mapping[$tuple["ARRET_ID"]] = $tuple["SEQUENCE"];
            }

            $sql = 'CALL ajouter_horaire(:trajet_id, :itineraire_id, :arret_id, :heure_arrivee, :heure_depart)';
            $req = $bdd->prepare($sql);

            $lines = explode("\n", $_POST['texte']);
            if ($lines) {
               $sequence_avant = null;
               $sequence_maintenant = null;

               $horaire_avant_tab = [];

               $nb_iterations = 0; # var pour savoir si l'heure est une arrivée ou un départ quand une des deux est nulle
               $lines = array_filter(array_map('trim', explode("\n", $_POST['texte'])));
               foreach ($lines as $line) {
                  $horaire_maintenant_tab = [];
                  if ($line != '') {
                     $parts = explode(",", trim($line));
                     if (count($parts) != 2 and count($parts) != 3) {
                        echo "<p>Veuillez saisir dans la zone de texte des informations du format suivante : STOP,harrivé,hdepart ou STOP,hdepart ou STOP,harrivé</p>";
                        $bdd->rollBack();
                        return;
                     }

                     $stop = $parts[0];
                     $arrets_id_input[] = $stop;
                     $condition = $bdd->prepare('SELECT ID FROM arret WHERE ID = :stop'); # Bien que ce soit déjà traité avec une clé étrangère dans l'enregistrement de la table
                     $condition->execute(array("stop" => $stop));
                     if ($condition->rowCount() == 0) {
                        echo "<p>L'arrêt $stop n'existe pas dans la base de données.</p>";
                        $bdd->rollBack();
                        return;
                     }

                     $harrivee = $parts[1] ?? null; #Si pas défini je le mets en null. En se rappelant que une des heures peut être nulle. On ferait un dépassement de tableau
                     $hdepart = $parts[2] ?? null;
                     if (($nb_iterations == 0 and count($parts) != 2) or ($nb_iterations == count($lines) - 1 and count($parts) != 2)) {
                        echo "<p>Le début et la fin du trajet ne peuvent pas admettre deux heures départs/arrivées.</p>";
                        $bdd->rollBack();
                        return;
                     }

                     if (count($parts) == 2) # début / fin d'un trajet : heures nulles ! On les redéfinit correctement si on est dans ce cas spécial
                        if ($nb_iterations == 0) {
                           $hdepart = $parts[1];
                           $harrivee = null;
                        } elseif ($nb_iterations == count($lines) - 1) {
                           $harrivee = $parts[1];
                           $hdepart = null;
                        } else {
                           echo "<p>Une heure de départ/arrêt est nulle au milieu du trajet.</p>";
                           $bdd->rollBack();
                           return;
                        }

                     if ($harrivee === '' or $hdepart === '') {
                        echo "<p>Veuillez saisir dans la zone de texte des informations du format suivante : STOP,harrivé,hdepart ou STOP,hdepart ou STOP,harrivé</p>";
                        $bdd->rollBack();
                        return;
                     }

                     if (!isset($sequence_tableau_mapping[$stop])) {
                        echo "<p>L'arrêt $stop ne fait pas partie de l'itinéraire $itineraire_id. Ajout invalide. </p>";
                        $bdd->rollBack();
                        return;
                     }
                     $sequence_maintenant = $sequence_tableau_mapping[$stop];
                     if ($sequence_avant != null) {
                        if ($direction == 0 && $sequence_maintenant <= $sequence_avant) {
                           echo "<p>Erreur : la séquence n'est pas croissante pour la direction 0.</p>";
                           $bdd->rollBack();
                           return;
                        }
                        if ($direction == 1 && $sequence_maintenant >= $sequence_avant) {
                           echo "<p>Erreur : la séquence n'est pas décroissante pour la direction 1.</p>";
                           $bdd->rollBack();
                           return;
                        }
                     }
                     $sequence_avant = $sequence_maintenant;

                     if (!empty($harrivee))
                        $horaire_maintenant_tab[] = $harrivee;
                     if (!empty($hdepart))
                        $horaire_maintenant_tab[] = $hdepart;

                     if ($horaire_avant_tab != null and $horaire_maintenant_tab != null) {
                        foreach ($horaire_maintenant_tab as $horaire_maintenant) {
                           foreach ($horaire_avant_tab as $horaire_avant) {
                              if (strtotime($horaire_maintenant) <= strtotime($horaire_avant)) {
                                 echo "<p>Erreur : l'horaire n'est pas croissant.</p>";
                                 $bdd->rollBack();
                                 return;
                              }
                           }
                        }
                     }
                     $horaire_avant_tab = $horaire_maintenant_tab;

                     if (!$req->execute(array(
                        "trajet_id" => $trajet_id,
                        "itineraire_id" => $itineraire_id,
                        "arret_id" => $stop,
                        "heure_arrivee" => $harrivee,
                        "heure_depart" => $hdepart
                     ))) {
                        $bdd->rollBack();
                        echo "<p>Erreur lors de l'ajout d'un itinéraire.</p>";
                        return;
                     }
                  }
                  $nb_iterations++;
               }
            }

            sort($arrets_id_input);
            $arrets_attendus = array_keys($sequence_tableau_mapping);
            sort($arrets_attendus);
            if ($arrets_attendus != $arrets_id_input) {
               $bdd->rollBack();
               echo "<p>Erreur le trajet ne contient pas tous les arrêts de l'itinéraire qu'il suit.</p>";
               return;
            }

            $bdd->commit();
            echo "<p>Succès : horaires ajoutés et trajet ajouté à l'itinéraire</p>";
         } catch (\PDOException $e) {
            $bdd->rollBack();
            echo "<p>" . $e->getMessage() . "</p>";
         }
      }
   }
   ?>
</body>

</html>