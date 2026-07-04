# Partie 2 - Implémentation

Cette deuxième partie reprend la modélisation pour en faire une vraie petite application de base de données. On y trouve une base MySQL chargée avec des CSV, des vues/procédures SQL, et quelques pages PHP pour interagir avec les données ferroviaires.

Les fonctionnalités principales sont assez simples mais couvrent bien le projet :

- filtrer des agences, horaires et exceptions ;
- ajouter un service avec ses jours actifs et ses exceptions ;
- rechercher les services disponibles par date ;
- calculer des temps moyens d'arrêt ;
- rechercher une gare ;
- supprimer un itinéraire avec ses trajets liés ;
- modifier les informations d'un arrêt.

## Lancer le projet

Il faut Docker. Depuis ce dossier :

```bash
docker compose up --build
```

Puis ouvrir :

- `http://localhost` pour l'interface PHP ;
- `http://localhost:8080` pour phpMyAdmin.

Le dossier `dump/` contient le schéma, les données CSV, les triggers, les vues et les procédures chargées au démarrage de MySQL.

Ce projet reste un travail étudiant : l'interface est volontairement basique, mais elle permet de tester les requêtes et les contraintes importantes de la BDD.
