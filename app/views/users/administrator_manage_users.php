<?php
// ═══════════════════════════════════════════════
// VUE administrator_manage_users.php
// Page "Gestion des utilisateurs" de l'administrateur : liste des étudiants
// et enseignants avec leurs statistiques de réservations, filtre par rôle
// et recherche par nom.
// Variables attendues du controller : $userName, $users, $roleFilter, $search
// (UserController::manageUsers)
// ═══════════════════════════════════════════════

$roleFilter = $roleFilter ?? '';
$search     = $search ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Administrateur - Utilisateurs</title>
  <link href="assets/css/output.css" rel="stylesheet">
  <link rel="icon" href="assets/img/google-icons/chess_rook.svg" type="image/x-icon">
</head>

<body class="bg-gray-50 h-screen flex">

<!-- SIDEBAR -->
<nav class="w-64 bg-white border-r flex flex-col">
  <div class="h-16 flex items-center px-6 border-b font-bold text-xl">
    <img src="assets/img/google-icons/chess_rook.svg" alt="RindranDakilasy" width="48" height="48">
    RindranDakilasy
  </div>

  <div class="flex-1 p-4 space-y-2">

    <a href="index.php?route=administrator/dashboard" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/dashboard.svg" alt="Accueil" width="24" height="24">
      Accueil
    </a>

    <a href="index.php?route=administrator/users" class="flex items-center gap-3 p-2 bg-blue-100 rounded">
      <img src="assets/img/google-icons/group.svg" alt="Utilisateurs" width="24" height="24">
      Utilisateurs
    </a>

    <a href="index.php?route=administrator/rooms" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/meeting_room.svg" alt="Salles" width="24" height="24">
      Salles
    </a>

    <a href="index.php?route=administrator/calendar" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/calendar_month.svg" alt="Calendrier des salles" width="24" height="24">
      Calendrier des salles
    </a>

    <a href="index.php?route=administrator/reports" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/description.svg" alt="Rapports" width="24" height="24">
      Rapports
    </a>

    <a href="index.php?route=administrator/history" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/history.svg" alt="Historique" width="24" height="24">
      Historique
    </a>

    <a href="index.php?route=administrator/profile" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
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

  <main class="flex-1 p-8 overflow-auto">

    <h1 class="text-2xl font-bold mb-6">Utilisateurs</h1>

    <!-- Notifications Flash -->
    <?php if (!empty($_SESSION['error'])): ?>
      <div class="bg-red-100 text-red-700 border-red-300 rounded px-4 py-3 mb-6">
        <?= htmlspecialchars($_SESSION['error']) ?>
      </div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
      <div class="bg-green-100 text-green-700 border-green-300 rounded px-4 py-3 mb-6">
        <?= htmlspecialchars($_SESSION['success']) ?>
      </div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Actions & Filtres -->
    <div class="flex items-center gap-4 mb-6 flex-wrap">

      <button
        type="button"
        class="bg-blue-600 text-white px-4 py-2 rounded cursor-pointer"
        onclick="window.location.href='index.php?route=administrator/users/new'"
      >
        Ajouter un nouvel utilisateur
      </button>

      <form method="GET" action="index.php" class="ml-auto flex items-center gap-4 flex-wrap">
        <input type="hidden" name="route" value="administrator/users">

        <select name="role" class="border rounded px-4 py-2 bg-white" onchange="this.form.submit()">
          <option value="" <?= $roleFilter === '' ? 'selected' : '' ?>>Tous les utilisateurs</option>
          <option value="student" <?= $roleFilter === 'student' ? 'selected' : '' ?>>Etudiant</option>
          <option value="teacher" <?= $roleFilter === 'teacher' ? 'selected' : '' ?>>Enseignant</option>
        </select>

        <div class="flex items-center gap-2">
          <input
            type="text"
            name="search"
            value="<?= htmlspecialchars($search) ?>"
            placeholder="Rechercher un nom..."
            class="border rounded px-4 py-2"
          >
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded cursor-pointer">
            Rechercher
          </button>
        </div>
      </form>

    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full border-collapse border bg-white">
        <thead class="bg-gray-200">
          <tr>
            <th class="border px-4 py-2 text-center">UTILISATEUR</th>
            <th class="border px-4 py-2 text-center">ROLE</th>
            <th class="border px-4 py-2 text-center">RESERVATION FAITE</th>
            <th class="border px-4 py-2 text-center">RESERVATION VALIDEE</th>
            <th class="border px-4 py-2 text-center">RESERVATION REFUSEE</th>
            <th class="border px-4 py-2 text-center">ACTION</th>
          </tr>
        </thead>
        <tbody>

          <?php if (empty($users)): ?>
            <tr>
              <td colspan="6" class="border px-4 py-6 text-center text-gray-500">
                Aucun utilisateur trouvé.
              </td>
            </tr>
          <?php else: ?>

            <?php foreach ($users as $user): ?>
              <tr>
                <td class="border px-4 py-2 text-center font-medium">
                  <?= htmlspecialchars($user['name']) ?>
                </td>
                <td class="border px-4 py-2 text-center">
                  <?= htmlspecialchars(User::roleLabel($user['role'])) ?>
                </td>
                <td class="border px-4 py-2 text-center">
                  <?= (int) $user['reservations_made'] ?>
                </td>
                <td class="border px-4 py-2 text-center">
                  <?= (int) $user['reservations_approved'] ?>
                </td>
                <td class="border px-4 py-2 text-center">
                  <?= (int) $user['reservations_refused'] ?>
                </td>
                <td class="border px-4 py-2 text-center">
                  <button
                    type="button"
                    class="bg-blue-600 text-white px-4 py-1 rounded cursor-pointer"
                    onclick="window.location.href='index.php?route=administrator/users/edit&id=<?= (int) $user['id'] ?>'"
                  >
                    Modifier
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>

          <?php endif; ?>

        </tbody>
      </table>
    </div>

  </main>

</div>

</body>
</html>
