CREATE VIEW dates_periode AS
WITH RECURSIVE dates_r(SERVICE_ID, date_comprise) AS
(
  SELECT ID as SERVICE_ID, DATE_DEBUT AS date_comprise
  FROM service

  UNION ALL

  SELECT r.SERVICE_ID, DATE_ADD(r.date_comprise, INTERVAL 1 DAY)
  FROM dates_r r JOIN service s ON s.ID = r.SERVICE_ID
  WHERE DATE_ADD(r.date_comprise, INTERVAL 1 DAY) <= s.DATE_FIN AND DATE_ADD(r.date_comprise, INTERVAL 1 DAY) < DATE_ADD(s.DATE_DEBUT, INTERVAL 1000 DAY)
)
SELECT SERVICE_ID, date_comprise FROM dates_r;

CREATE VIEW dates_actives AS
SELECT * FROM
(
  SELECT dp.SERVICE_ID, dp.date_comprise
  FROM dates_periode dp JOIN service s ON dp.SERVICE_ID = s.ID
  WHERE (DAYOFWEEK(dp.date_comprise) = 2 AND s.LUNDI = 1)
    OR (DAYOFWEEK(dp.date_comprise) = 3 AND s.MARDI = 1)
    OR (DAYOFWEEK(dp.date_comprise) = 4 AND s.MERCREDI = 1)
    OR (DAYOFWEEK(dp.date_comprise) = 5 AND s.JEUDI = 1)
    OR (DAYOFWEEK(dp.date_comprise) = 6 AND s.VENDREDI = 1)
    OR (DAYOFWEEK(dp.date_comprise) = 7 AND s.SAMEDI = 1)
    OR (DAYOFWEEK(dp.date_comprise) = 1 AND s.DIMANCHE = 1)

  UNION

  SELECT SERVICE_ID, DATE AS date_comprise
  FROM exception
  WHERE CODE = 1

  EXCEPT

  SELECT SERVICE_ID, DATE AS date_comprise
  FROM exception
  WHERE CODE = 2
)
AS finale;

CREATE VIEW tous_services AS
SELECT da.date_comprise,
GROUP_CONCAT( s.NOM ) as "services_actifs"
FROM dates_actives da JOIN service s ON da.SERVICE_ID = s.ID
GROUP BY da.date_comprise;

CREATE VIEW nb_trains_gare_service AS
SELECT a.NOM AS NOM_GARE, s.NOM AS NOM_SERVICE, COUNT(*) AS NB_ARRETS, COUNT(h.HEURE_ARRIVEE) AS NB_ARRIVEES, COUNT(h.HEURE_DEPART) AS NB_DEPARTS FROM arret a
  JOIN horaire h ON h.ARRET_ID = a.ID
  JOIN trajet t ON h.TRAJET_ID = t.TRAJET_ID
  JOIN service s ON t.SERVICE_ID = s.ID
  GROUP BY a.ID, s.ID ORDER BY NB_ARRETS DESC, NB_ARRIVEES DESC, NB_DEPARTS DESC;

DELIMITER $$

CREATE PROCEDURE ajouter_horaire(
  IN trajet_id VARCHAR(50),
  IN itineraire_id BIGINT,
  IN arret_id BIGINT,
  IN heure_arrivee TIME,
  IN heure_depart TIME

)
BEGIN
  DECLARE heure_voisin TIME;
  DECLARE sequence_voisin INT;
  DECLARE sequence INT;
  DECLARE arret_id_voisin BIGINT;
  DECLARE direction TINYINT;

  -- START TRANSACTION; On laisse php gérer la transaction alors il s'entremêle avec la transaction de php que nous faisons
  IF NOT EXISTS(SELECT * FROM arret_desservi WHERE ITINERAIRE_ID = itineraire_id AND ARRET_ID = arret_id) THEN
    -- ROLLBACK;
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Erreur lors de l ajout d un horaire, l arret ne fait pas partie de l itineraire';
  END IF;

  SELECT DIRECTION INTO direction FROM trajet t WHERE t.TRAJET_ID = trajet_id AND t.ITINERAIRE_ID = itineraire_id;
  IF direction = 0 THEN
    SELECT SEQUENCE INTO sequence FROM arret_desservi ad WHERE ad.ITINERAIRE_ID = itineraire_id AND ad.ARRET_ID = arret_id;
    IF sequence = 1 AND (heure_arrivee IS NOT NULL OR heure_depart IS NULL) THEN
      -- ROLLBACK;
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Erreur lors de l ajout d un horaire, l arret est le premier de la sequence (direction 0) mais il n a pas d heure de depart ou il a une heure d arrivee (pas possible)';
    END IF;

    SET sequence_voisin = sequence - 1;
    SELECT ARRET_ID INTO arret_id_voisin FROM arret_desservi ad WHERE ad.itineraire_id = itineraire_id AND ad.SEQUENCE = sequence_voisin;
    SELECT HEURE_DEPART INTO heure_voisin FROM horaire h WHERE h.TRAJET_ID = trajet_id AND h.ITINERAIRE_ID = itineraire_id AND h.ARRET_ID = arret_id_voisin;
    IF heure_voisin IS NOT NULL AND heure_arrivee IS NOT NULL AND heure_voisin > heure_arrivee THEN
      -- ROLLBACK;
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Erreur lors de l ajout d un horaire, la date d arrivee ne correspond pas avec la date de depart du voisin';
    END IF;

  ELSEIF direction = 1 THEN
    SELECT SEQUENCE INTO sequence FROM arret_desservi ad WHERE ad.ITINERAIRE_ID = itineraire_id AND ad.ARRET_ID = arret_id;
    IF sequence = 1 AND (heure_depart IS NOT NULL OR heure_arrivee IS NULL) THEN
      -- ROLLBACK;
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Erreur lors de l ajout d un horaire, l arret est le dernier de la sequence (direction 1) mais il n a pas d heure d arrivee ou il a une heure de depart (pas possible)';
    END IF;

    SET sequence_voisin = sequence + 1;
    SELECT ARRET_ID INTO arret_id_voisin FROM arret_desservi ad JOIN horaire ON ad.itineraire_id = itineraire_id WHERE ad.SEQUENCE = sequence_voisin;
    SELECT HEURE_DEPART INTO heure_voisin FROM horaire h WHERE h.TRAJET_ID = trajet_id AND h.ITINERAIRE_ID = itineraire_id AND h.ARRET_ID = arret_id_voisin;
    IF heure_voisin IS NOT NULL AND heure_arrivee IS NOT NULL AND heure_voisin > heure_arrivee THEN
      -- ROLLBACK;
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Erreur lors de l ajout d un horaire, la date d arrivee ne correspond pas avec la date de depart du voisin';
    END IF;
  END IF;

  INSERT INTO horaire(TRAJET_ID, ITINERAIRE_ID, ARRET_ID, HEURE_ARRIVEE, HEURE_DEPART) VALUES (trajet_id, itineraire_id, arret_id, heure_arrivee, heure_depart);

  -- COMMIT; On laisse php gérer la transaction alors il s'entremêle avec la transaction de php
END $$
DELIMITER ;