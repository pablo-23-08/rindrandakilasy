<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title><?= $pageTitle ?></title>
  <link href="assets/css/output.css" rel="stylesheet">
  <link rel="icon" href="assets/img/google-icons/chess_rook.svg" type="image/x-icon">
</head>

<body class="bg-gray-50 h-screen flex">

<!-- SIDEBAR -->
<nav class="w-64 bg-white border-r flex flex-col">
  <div class="h-16 flex items-center px-6 border-b font-bold text-xl">
    <img src="assets/img/google-icons/chess_rook.svg" alt="Logo" width="48" height="48">
    RindranDakilasy
  </div>

  <div class="flex-1 p-4 space-y-2">

    <a href="index.php?route=administrator/dashboard" class="flex items-center gap-3 p-2 bg-blue-100 rounded">
      <img src="assets/img/google-icons/dashboard.svg" alt="Accueil" width="24" height="24">
      Accueil
    </a>

    <a href="index.php?route=admin/users" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/group.svg" alt="Utilisateurs" width="24" height="24">
      Utilisateurs
    </a>

    <a href="index.php?route=admin/rooms" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/meeting_room.svg" alt="Salles" width="24" height="24">
      Salles
    </a>

    <a href="index.php?route=admin/calendar" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/calendar_month.svg" alt="Calendrier des salles" width="24" height="24">
      Calendrier des salles
    </a>

    <a href="index.php?route=admin/reports" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/description.svg" alt="Rapports" width="24" height="24">
      Rapports
    </a>

    <a href="index.php?route=admin/history" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/history.svg" alt="Historique" width="24" height="24">
      Historique
    </a>

    <a href="index.php?route=admin/profile" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/person.svg" alt="Mon profil" width="24" height="24">
      Mon profil
    </a>

  </div>

  <div class="p-4 border-t">
    <a href="index.php?route=logout" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/logout.svg" alt="Déconnexion" width="24" height="24">
      Déconnexion
    </a>
  </div>
</nav>

<!-- MAIN -->
<div class="flex-1 flex flex-col">

  <header class="h-16 bg-white border-b flex justify-end items-center px-6">
    <?= $userName ?>
  </header>

  <main class="flex-1 p-8">

    <h1 class="text-3xl font-bold mb-6">Tableau de bord</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

      <div class="bg-white border rounded p-6 flex flex-col justify-between">
        <h3 class="font-semibold text-lg">Gestion Utilisateurs</h3>
        <h4 class="text-sm">Gérer les accès et les rôles</h4>
        <button class="mt-4 bg-blue-600 text-white px-4 py-2 rounded cursor-pointer" onclick="window.location.href='index.php?route=admin/users'">
          Aller
        </button>
      </div>

      <div class="bg-white border rounded p-6 flex flex-col justify-between">
        <h3 class="font-semibold text-lg">Gestion des Salles</h3>
        <h4 class="text-sm">Gérer les équipements</h4>
        <button class="mt-4 bg-blue-600 text-white px-4 py-2 rounded cursor-pointer" onclick="window.location.href='index.php?route=admin/rooms'">
          Aller
        </button>
      </div>

      <div class="bg-white border rounded p-6 flex flex-col justify-between">
        <h3 class="font-semibold text-lg">Calendrier des salles</h3>
        <h4 class="text-sm">Voir la disponibilité des salles</h4>
        <button class="mt-4 bg-blue-600 text-white px-4 py-2 rounded cursor-pointer" onclick="window.location.href='index.php?route=admin/calendar'">
          Aller
        </button>
      </div>

      <div class="bg-white border rounded p-6 flex flex-col justify-between">
        <h3 class="font-semibold text-lg">Rapports</h3>
        <h4 class="text-sm">Exporter un rapport de statistiques</h4>
        <button class="mt-4 bg-blue-600 text-white px-4 py-2 rounded cursor-pointer" onclick="window.location.href='index.php?route=admin/reports'">
          Aller
        </button>
      </div>

      <div class="bg-white border rounded p-6 flex flex-col justify-between">
        <h3 class="font-semibold text-lg">Historique</h3>
        <h4 class="text-sm">consulter les actions récentes</h4>
        <button class="mt-4 bg-blue-600 text-white px-4 py-2 rounded cursor-pointer" onclick="window.location.href='index.php?route=admin/history'">
          Aller
        </button>
      </div>

    </div>

  </main>

</div>

</body>
</html>
