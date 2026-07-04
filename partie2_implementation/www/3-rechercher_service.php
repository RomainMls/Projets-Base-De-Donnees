<!DOCTYPE html>
<html>
<?php
$bdd = new PDO('mysql:host=db;dbname=group12;charset=utf8', 'group12', 'qnvIHYuGXBFbmAEU');
?>
<head>
    <title>Trouver tous les services disponibles pour une date</title>
</head>
<body>
    <center>
    <h1>Tableau des services disponibles par date</h1>
        <table border="1">
        <tr>
            <th>Date</th>
            <th>Services disponibles</th>
        </tr>
        <?php
        $sql = "SELECT * FROM tous_services;";
        $req = $bdd->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $req->execute();
        while ($tuple = $req->fetch(PDO::FETCH_ASSOC))
        { #PDO renvoit 2 tableaux PDO::FETCH_ASSOC permet de juste prendre le tableau associatif
            echo "<tr>";
            foreach ($tuple as $attribut => $value) {
               echo "<td>" . htmlentities($value) . "</td>";
            }
            echo "</tr>";
        }

        ?>
        </table>
    </center>
</html>