/* =======================================================
   BUILDINGS
======================================================= */

INSERT INTO buildings (name, description) VALUES
('Bâtiment A', 'Salles de cours principales'),
('Bâtiment B', 'Département Informatique'),
('Bâtiment C', 'Amphithéâtres et conférences');


/* =======================================================
   ROOMS
======================================================= */

INSERT INTO rooms (building_id, name, capacity, description, status) VALUES
(1, 'Salle A101', 30, 'Salle de cours standard', 'available'),
(1, 'Salle A102', 40, 'Salle avec vidéoprojecteur', 'available'),
(1, 'Salle A103', 25, 'Salle de TD', 'maintenance'),

(2, 'Laboratoire Info 1', 25, 'Salle informatique', 'available'),
(2, 'Laboratoire Info 2', 35, 'Laboratoire réseau', 'available'),
(2, 'Salle B201', 60, 'Grande salle', 'available'),

(3, 'Amphi Rouge', 200, 'Grand amphithéâtre', 'available'),
(3, 'Amphi Bleu', 150, 'Amphithéâtre secondaire', 'disabled');


/* =======================================================
   EQUIPMENTS
======================================================= */

INSERT INTO equipments (name) VALUES
('Vidéoprojecteur'),
('Tableau blanc'),
('Ordinateur'),
('Climatisation'),
('Connexion Internet'),
('Microphone'),
('Haut-parleurs');


/* =======================================================
   ROOM_EQUIPMENTS
======================================================= */

INSERT INTO room_equipments VALUES
(1,2),

(2,1),
(2,2),
(2,4),

(3,2),

(4,1),
(4,2),
(4,3),
(4,5),

(5,1),
(5,2),
(5,3),
(5,5),

(6,1),
(6,2),
(6,6),

(7,1),
(7,2),
(7,6),
(7,7),

(8,2),
(8,6);


/* =======================================================
   RESERVATIONS
======================================================= */

/*
Utilisateurs

1 = Admin
2 = Pablo (Étudiant)
3 = Logistics
4 = Escobar (Professeur)
*/

INSERT INTO reservations
(id_room, id_user, purpose, start_datetime, end_datetime, status, validated_at, validated_by, refusal_reason)
VALUES

(
4,
4,
'Cours de Base de données',
'2026-08-05 08:00:00',
'2026-08-05 10:00:00',
'approved',
NOW(),
1,
NULL
),

(
7,
4,
'Soutenance de mémoire',
'2026-08-08 13:00:00',
'2026-08-08 16:00:00',
'approved',
NOW(),
1,
NULL
),

(
2,
2,
'Réunion Association Informatique',
'2026-08-06 14:00:00',
'2026-08-06 16:00:00',
'pending',
NULL,
NULL,
NULL
),

(
6,
2,
'Hackathon étudiant',
'2026-08-10 08:00:00',
'2026-08-10 18:00:00',
'approved',
NOW(),
3,
NULL
),

(
1,
2,
'Réunion Bureau des étudiants',
'2026-08-12 09:00:00',
'2026-08-12 11:00:00',
'refused',
NOW(),
3,
'La salle est réservée pour un examen.'
),

(
5,
4,
'TP Réseau',
'2026-08-07 10:00:00',
'2026-08-07 12:00:00',
'cancelled',
NULL,
NULL,
NULL
);


/* =======================================================
   RESERVATION LOGS
======================================================= */

INSERT INTO reservation_logs
(reservation_id, id_user, action)
VALUES

(1,4,'Création'),
(1,1,'Validation'),

(2,4,'Création'),
(2,1,'Validation'),

(3,2,'Création'),

(4,2,'Création'),
(4,3,'Validation'),

(5,2,'Création'),
(5,3,'Refus'),

(6,4,'Création'),
(6,4,'Annulation');


/* =======================================================
   REPORTS
======================================================= */

INSERT INTO reports
(title, type, file_path, generated_by)
VALUES

(
'Occupation_Aout_2026',
'pdf',
'reports/occupation_aout2026.pdf',
1
),

(
'Reservations_Professeurs',
'csv',
'reports/reservations_professeurs.csv',
1
),

(
'Statistiques_Salles',
'pdf',
'reports/statistiques_salles.pdf',
3
);