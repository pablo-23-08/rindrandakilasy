# **RindranDakilasy**

RindranDakilasy est une application web de réservation de salles conçue pour un établissement d'enseignement.

Le projet est né d'un constat simple : les réservations sont actuellement gérées à l'aide d'un fichier Excel partagé. Cette méthode fonctionne pour de petites utilisations, mais elle entraîne rapidement des conflits lorsque plusieurs personnes réservent la même salle au même moment. Le suivi des validations est également manuel, ce qui demande du temps au service logistique.

L'objectif de cette application est de centraliser la gestion des réservations, de permettre aux utilisateurs de connaître les salles disponibles en temps réel et de simplifier le travail du service logistique.

## 

## **Fonctionnalités**

L'application permet de :

1. consulter les salles disponibles ;  
2. rechercher une salle selon sa capacité ou ses équipements ;  
3. réserver une salle ;  
4. empêcher les conflits de réservation ;  
5. permettre au service logistique de valider ou refuser les demandes des étudiants ;  
6. envoyer automatiquement un e-mail après une validation ou un refus ;  
7. consulter des statistiques d'utilisation des salles ;  
8. exporter des données pour les rapports.

## 

## **Utilisateurs**

L'application distingue plusieurs types d'utilisateurs.

### **Étudiant/Association**

1. consulter les salles disponibles ;  
2. rechercher une salle selon la capacité ou les équipements ;  
3. effectuer une demande de réservation ;  
4. consulter l'état de ses demandes.  
   Les réservations des étudiants/associations doivent être validées par le service logistique.

### **Enseignant**

1. consulter les salles ;  
2. réserver directement une salle ;  
3. consulter son historique de réservation.  
   Les réservations des enseignants sont automatiquement acceptées.

   ### **Service logistique**

1. consulter les demandes des étudiants ;  
2. accepter ou refuser une réservation ;  
3. gérer les salles ;  
4. suivre l'occupation des salles.

   ###  

1. gérer les utilisateurs ;  
2. gérer les salles ;  
3. consulter les statistiques ;  
4. exporter les rapports ;  
5. administrer l'ensemble de l'application.

## 

## **Règles de gestion**

1. Une salle ne peut pas être réservée deux fois au même créneau.  
2. Les étudiants et les associations doivent attendre la validation du service logistique.  
3. Les enseignants peuvent réserver directement.  
4. Chaque salle possède ses propres caractéristiques(capacité,équipements).  
5. Les utilisateurs n'accèdent qu'aux fonctionnalités correspondant à leur rôle.

## 

## **Technologies utilisées**

### **Front-end**

1. HTML  
2. Tailwind CSS

   ### **Back-end**

1. PHP natif structuré  
2. Architecture MVC

   ### **Base de données**

1. MySQL

   ### **Autres**

1. Authentification par identifiant et mot de passe  
2. Envoi automatique d'e-mails  
3. Export de rapports(pdf,csv)  
4. Tableau de bord statistique

## 

## **Architecture du projet**

Le projet suit une architecture **MVC (Modèle  Vue  Contrôleur)**.

Cette organisation permet de séparer la logique métier, l'interface utilisateur et l'accès aux données afin de rendre le code plus lisible, plus facile à maintenir et plus simple à faire évoluer.

**`rindrandakilasy/`**  
**`├── app/`**  
**`│   ├── models/`**  
**`│   │   ├── User.php`**  
**`│   │   ├── Teacher.php`**  
**`│   │   ├── StudentAssociation.php`**  
**`│   │   ├── LogisticsDepartment.php`**  
**`│   │   ├── Admin.php`**  
**`│   │   ├── Room.php`**  
**`│   │   ├── Reservation.php`**  
**`│   │   └── Statistical.php`**  
**`│   ├── views/`**  
**`│   │   ├── auth/`**  
**`│   │   │   └── login.php`**  
**`│   │   ├── users/`**  
**`│   │   │   ├── student_dashboard.php`**  
**`│   │   │   ├── teacher_dashboard.php`**  
**`│   │   │   ├── logistics_department_dashboard.php`**  
**`│   │   │   └── administrator_dashboard.php`**  
**`│   │   ├── rooms/`**  
**`│   │   ├── reservations/`**  
**`│   │   ├── statistics/`**  
**`│   │   ├── reports/`**  
**`│   │   ├── layouts/`**  
**`│   │   └── errors/`**  
**`│   ├── controllers/`**  
**`│   │   ├── AuthController.php`**  
**`│   │   ├── UserController.php`**  
**`│   │   ├── RoomController.php`**  
**`│   │   ├── ReservationController.php`**  
**`│   │   ├── StatisticalController.php`**  
**`│   │   └── ReportController.php`**  
**`│   └── core/`**  
**`│       ├── Database.php`**  
**`│       └── Router.php`**  
**`├── config/`**  
**`│   ├── database.php`**  
**`│   └── routes.php`**  
**`└── public/`**  
    **`├── assets/`**  
    **`│   ├── css/`**  
    **`│   ├── img/`**  
    **`│   │   └── google-icons/`**  
    **`│   └── js/`**  
    **`└── index.php`** 

## 

## **Objectifs du projet**

1. remplacer le système actuel basé sur Excel ;  
2. éviter les conflits de réservation ;  
3. simplifier le travail du service logistique ;  
4. centraliser toutes les réservations sur une seule plateforme ;  
5. fournir des statistiques pour faciliter le suivi de l'utilisation des salles.

## **Structure des réservations**

Une réservation contient notamment :

1. le demandeur ;  
2. la salle ;  
3. la date ;  
4. l'heure de début ;  
5. l'heure de fin ;  
6. le motif ;  
7. le statut (en attente, validée ou refusée).

## **Sécurité**

L'application met en place plusieurs mesures de sécurité :

1. authentification des utilisateurs ;  
2. gestion des rôles et des permissions ;  
3. protection des données ;  
4. sauvegarde de la base de données ;  
5. contrôle des conflits de réservation.

## **Évolutions possibles**

Quelques améliorations pourront être ajoutées par la suite :

1. calendrier interactif ;  
2. notifications en temps réel ;  
3. réservation récurrente ;  
4. gestion des équipements indépendamment des salles ;  
5. tableau de bord plus détaillé ;  
6. version mobile.
