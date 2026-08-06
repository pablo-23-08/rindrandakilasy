<?php

// Table de routage : chaque route pointe vers un Controller + une action.
// Même principe que dans mpandova : ['controller' => ..., 'action' => ...]

return [

    // ── Authentification ──
    'home'   => ['controller' => 'AuthController', 'action' => 'home'],
    'login'  => ['controller' => 'AuthController', 'action' => 'login'],
    'logout' => ['controller' => 'AuthController', 'action' => 'logout'],

    // ── Tableaux de bord (un par rôle) ──
    'student/dashboard'       => ['controller' => 'UserController', 'action' => 'studentDashboard'],
    'teacher/dashboard'       => ['controller' => 'UserController', 'action' => 'teacherDashboard'],
    'logistics/dashboard'     => ['controller' => 'UserController', 'action' => 'logisticsDashboard'],
    'administrator/dashboard' => ['controller' => 'UserController', 'action' => 'administratorDashboard'],

    // ── Réservations (étudiant) ──
    'student/reservations'        => ['controller' => 'ReservationController', 'action' => 'studentReservations'],
    'student/reservations/cancel' => ['controller' => 'ReservationController', 'action' => 'cancelStudentReservation'],
    'student/new-reservation'       => ['controller' => 'ReservationController', 'action' => 'newStudentReservationForm'],
    'student/new-reservation/store' => ['controller' => 'ReservationController', 'action' => 'storeStudentReservation'],

    // ── Réservations (enseignant) ──
    'teacher/reservations'        => ['controller' => 'ReservationController', 'action' => 'teacherReservations'],
    'teacher/reservations/cancel' => ['controller' => 'ReservationController', 'action' => 'cancelTeacherReservation'],
    'teacher/new-reservation'       => ['controller' => 'ReservationController', 'action' => 'newTeacherReservationForm'],
    'teacher/new-reservation/store' => ['controller' => 'ReservationController', 'action' => 'storeTeacherReservation'],

    // ── Profil (commun à TOUS les rôles) ──
    // Une seule vue (app/views/users/profile.php) et une seule paire d'actions
    // (UserController::profile / updateProfile) gèrent la page "Mon profil"
    // pour les 4 rôles : student, teacher, logistics_department, admin.
    'student/profile'        => ['controller' => 'UserController', 'action' => 'profile'],
    'student/profile/update' => ['controller' => 'UserController', 'action' => 'updateProfile'],

    'teacher/profile'        => ['controller' => 'UserController', 'action' => 'profile'],
    'teacher/profile/update' => ['controller' => 'UserController', 'action' => 'updateProfile'],

    'logistics/profile'        => ['controller' => 'UserController', 'action' => 'profile'],
    'logistics/profile/update' => ['controller' => 'UserController', 'action' => 'updateProfile'],

    'administrator/profile'        => ['controller' => 'UserController', 'action' => 'profile'],
    'administrator/profile/update' => ['controller' => 'UserController', 'action' => 'updateProfile'],


    // ── Réservations (service logistique) ──
    'logistics/requests'         => ['controller' => 'ReservationController', 'action' => 'logisticsRequests'],
    'logistics/requests/approve' => ['controller' => 'ReservationController', 'action' => 'approveReservation'],
    'logistics/requests/refuse'  => ['controller' => 'ReservationController', 'action' => 'refuseReservation'],
    'logistics/calendar'         => ['controller' => 'ReservationController', 'action' => 'roomSchedule'],
    'logistics/history'          => ['controller' => 'ReservationController', 'action' => 'history'],

    // ── Calendrier des salles / Historique (administrateur) ──
    // Réutilisent EXACTEMENT les mêmes actions du contrôleur et les mêmes
    // vues (app/views/reservations/room_schedule.php et historical.php)
    // que le service logistique : une seule page pour les deux rôles.
    'administrator/calendar' => ['controller' => 'ReservationController', 'action' => 'roomSchedule'],
    'administrator/history'  => ['controller' => 'ReservationController', 'action' => 'history'],

        // ── Gestion des utilisateurs (administrateur) ──
    'administrator/users'         => ['controller' => 'UserController', 'action' => 'manageUsers'],
    'administrator/users/new'     => ['controller' => 'UserController', 'action' => 'newUserForm'],
    'administrator/users/store'   => ['controller' => 'UserController', 'action' => 'storeUser'],
    'administrator/users/edit'    => ['controller' => 'UserController', 'action' => 'editUserForm'],
    'administrator/users/update'  => ['controller' => 'UserController', 'action' => 'updateUser'],

    // ── Gestion des salles (administrateur) ──
    'administrator/rooms'         => ['controller' => 'RoomController', 'action' => 'manageRooms'],
    'administrator/rooms/new'     => ['controller' => 'RoomController', 'action' => 'newRoomForm'],
    'administrator/rooms/store'   => ['controller' => 'RoomController', 'action' => 'storeRoom'],
    'administrator/rooms/edit'    => ['controller' => 'RoomController', 'action' => 'editRoomForm'],
    'administrator/rooms/update'  => ['controller' => 'RoomController', 'action' => 'updateRoom'],


    // ── Rapports (administrateur) ──
    'administrator/reports'          => ['controller' => 'ReportController', 'action' => 'index'],
    'administrator/reports/export'   => ['controller' => 'ReportController', 'action' => 'export'],
    'administrator/reports/download' => ['controller' => 'ReportController', 'action' => 'download'],

];
