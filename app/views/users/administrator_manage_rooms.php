<?php
// ═══════════════════════════════════════════════
// VUE administrator_manage_rooms.php
// Interface de l'administrateur pour consulter et filtrer les salles.
// L'ajout et la modification d'une salle se font désormais sur une page
// dédiée (administrator_room_form.php), au même principe que pour les
// utilisateurs (administrator_user_form.php).
// Variables attendues du contrôleur (RoomController::manageRooms) :
//   $userName, $rooms, $status, $search
// ═══════════════════════════════════════════════

$status = $status ?? '';
$search = $search ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Administrateur - Salles</title>
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

    <a href="index.php?route=administrator/users" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/group.svg" alt="Utilisateurs" width="24" height="24">
      Utilisateurs
    </a>

    <a href="index.php?route=administrator/rooms" class="flex items-center gap-3 p-2 bg-blue-100 rounded">
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

  <header class="h-16 bg-white border-b flex justify-end items-center px-6 font-semibold">
    <?= $userName ?? 'Admin' ?>
  </header>

  <main class="flex-1 p-8 overflow-auto">

    <h1 class="text-2xl font-bold mb-6">Gestion des salles</h1>

    <!-- Notifications Flash -->
    <?php if (!empty($_SESSION['error'])): ?>
      <div class="max-w-4xl bg-red-100 text-red-700 border border-red-300 rounded px-4 py-3 mb-6">
        <?= htmlspecialchars($_SESSION['error']) ?>
      </div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
      <div class="max-w-4xl bg-green-100 text-green-700 border border-green-300 rounded px-4 py-3 mb-6">
        <?= htmlspecialchars($_SESSION['success']) ?>
      </div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Actions & Filtres -->
    <div class="flex items-center gap-4 mb-6 flex-wrap">

      <button
        type="button"
        onclick="window.location.href='index.php?route=administrator/rooms/new'"
        class="bg-blue-600 text-white px-4 py-2 rounded cursor-pointer hover:bg-blue-700"
      >
        Ajouter une nouvelle salle
      </button>

      <form action="index.php" method="GET" class="ml-auto flex items-center gap-4 flex-wrap">
        <input type="hidden" name="route" value="administrator/rooms">

        <select name="status" onchange="this.form.submit()" class="border rounded px-4 py-2 bg-white">
          <option value="">Toutes les salles</option>
          <option value="available" <?= $status === 'available' ? 'selected' : '' ?>>Opérationnelle</option>
          <option value="unavailable" <?= $status === 'unavailable' ? 'selected' : '' ?>>Inopérationnelle</option>
        </select>

        <div class="flex items-center gap-2">
          <input
            type="text"
            name="recherche"
            value="<?= htmlspecialchars($search) ?>"
            placeholder="Nom de la salle..."
            class="border rounded px-4 py-2"
          >
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded cursor-pointer hover:bg-blue-700">
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
            <th class="border px-4 py-2 text-center">SALLE</th>
            <th class="border px-4 py-2 text-center">CAPACITE</th>
            <th class="border px-4 py-2 text-center">EQUIPEMENTS</th>
            <th class="border px-4 py-2 text-center">STATUS</th>
            <th class="border px-4 py-2 text-center">NOMBRE D'UTILISATION</th>
            <th class="border px-4 py-2 text-center">ACTION</th>
          </tr>
        </thead>
        <tbody>

          <?php if (empty($rooms)): ?>
            <tr>
              <td colspan="6" class="border px-4 py-6 text-center text-gray-500">
                Aucune salle trouvée.
              </td>
            </tr>
          <?php else: ?>

            <?php foreach ($rooms as $room): ?>
              <?php
                $isAvailable = $room['status'] === 'available';
                $badgeClass  = $isAvailable ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
              ?>
              <tr>
                <td class="border px-4 py-2 text-center font-medium"><?= htmlspecialchars($room['name']) ?></td>
                <td class="border px-4 py-2 text-center"><?= (int) $room['capacity'] ?></td>
                <td class="border px-4 py-2 text-center"><?= htmlspecialchars($room['equipments'] ?? '') ?: '—' ?></td>
                <td class="border px-4 py-2 text-center">
                  <span class="px-2 py-1 rounded text-xs font-semibold <?= $badgeClass ?>">
                    <?= htmlspecialchars(Room::statusLabel($room['status'])) ?>
                  </span>
                  <div class="text-xs text-gray-500 mt-1"><?= htmlspecialchars(Room::statusDetailLabel($room['status'])) ?></div>
                </td>
                <td class="border px-4 py-2 text-center"><?= (int) $room['usage_count'] ?></td>
                <td class="border px-4 py-2 text-center">
                  <button
                    type="button"
                    onclick="window.location.href='index.php?route=administrator/rooms/edit&id=<?= (int) $room['id'] ?>'"
                    class="bg-blue-600 text-white px-4 py-1 rounded cursor-pointer hover:bg-blue-700"
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
