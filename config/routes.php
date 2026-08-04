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

];
