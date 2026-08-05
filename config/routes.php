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

    'admin/profile'        => ['controller' => 'UserController', 'action' => 'profile'],
    'admin/profile/update' => ['controller' => 'UserController', 'action' => 'updateProfile'],


    // ── Réservations (service logistique) ──
    'logistics/requests'         => ['controller' => 'ReservationController', 'action' => 'logisticsRequests'],
    'logistics/requests/approve' => ['controller' => 'ReservationController', 'action' => 'approveReservation'],
    'logistics/requests/refuse'  => ['controller' => 'ReservationController', 'action' => 'refuseReservation'],
    'logistics/calendar'         => ['controller' => 'ReservationController', 'action' => 'roomSchedule'],
    'logistics/history'          => ['controller' => 'ReservationController', 'action' => 'logisticsHistory'],

        // ── Gestion des utilisateurs (administrateur) ──
    'admin/users'         => ['controller' => 'UserController', 'action' => 'manageUsers'],
    'admin/users/new'     => ['controller' => 'UserController', 'action' => 'newUserForm'],
    'admin/users/store'   => ['controller' => 'UserController', 'action' => 'storeUser'],
    'admin/users/edit'    => ['controller' => 'UserController', 'action' => 'editUserForm'],
    'admin/users/update'  => ['controller' => 'UserController', 'action' => 'updateUser'],

    // ── Gestion des salles (administrateur) ──
    'admin/rooms'        => ['controller' => 'RoomController', 'action' => 'manage'],
    'admin/rooms/store'  => ['controller' => 'RoomController', 'action' => 'store'],
    'admin/rooms/update' => ['controller' => 'RoomController', 'action' => 'update'],



];
