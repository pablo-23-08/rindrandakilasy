<?php

return [
    // Quand l'utilisateur va sur la racine "/" -> on affiche la vue
    '/'       => ['view' => 'auth/login.php'],
    
    // Quand le formulaire est soumis (POST) -> on appelle le contrôleur
    'login'   => ['controller' => 'AuthController', 'action' => 'login'],
    
    // Déconnexion
    'logout'  => ['controller' => 'AuthController', 'action' => 'logout'],

    // Dashboards par rôle
    'student/dashboard'   => ['view' => 'users/student_dashboard.php'],
    'teacher/dashboard'   => ['view' => 'users/teacher_dashboard.php'],
    'logistics/dashboard' => ['view' => 'users/logistics_department_dashboard.php'],
    'administrator/dashboard'     => ['view' => 'users/administrator_dashboard.php'],
];

