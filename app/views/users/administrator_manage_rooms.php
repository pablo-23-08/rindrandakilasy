<?php
// ═══════════════════════════════════════════════
// VUE administrator_manage_rooms.php
// Interface de l'administrateur pour consulter, filtrer, ajouter et
// modifier les salles.
// Variables attendues du contrôleur (RoomController::manage) :
//   $userName, $rooms, $buildings, $status, $search
// ═══════════════════════════════════════════════
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

    <a href="index.php?route=admin/users" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/group.svg" alt="Utilisateurs" width="24" height="24">
      Utilisateurs
    </a>

    <a href="index.php?route=admin/rooms" class="flex items-center gap-3 p-2 bg-blue-100 rounded">
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

      <button type="button" onclick="document.getElementById('add-room-modal').showModal()"
              class="bg-blue-600 text-white px-4 py-2 rounded cursor-pointer hover:bg-blue-700">
        Ajouter une nouvelle salle
      </button>

      <form action="index.php" method="GET" class="ml-auto flex items-center gap-4 flex-wrap">
        <input type="hidden" name="route" value="admin/rooms">

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
                  <button type="button"
                          onclick="document.getElementById('edit-room-modal-<?= (int) $room['id'] ?>').showModal()"
                          class="bg-blue-600 text-white px-4 py-1 rounded cursor-pointer hover:bg-blue-700">
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

<!-- ═══════════════════════════════════════════════
     MODALE : Ajouter une nouvelle salle
     ═══════════════════════════════════════════════ -->
<dialog id="add-room-modal" class="rounded-lg p-0 w-full max-w-lg backdrop:bg-black/40">
  <form method="POST" action="index.php?route=admin/rooms/store" class="p-6 space-y-4">

    <h2 class="text-xl font-bold mb-2">Ajouter une nouvelle salle</h2>

    <div>
      <label class="block text-sm font-medium mb-1" for="add-nom">Nom de la salle</label>
      <input type="text" id="add-nom" name="nom" required class="w-full border rounded px-4 py-2">
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1" for="add-batiment">Bâtiment</label>
        <select id="add-batiment" name="batiment" required class="w-full border rounded px-4 py-2 bg-white">
          <option value="">Sélectionner...</option>
          <?php foreach ($buildings as $building): ?>
            <option value="<?= (int) $building['id'] ?>"><?= htmlspecialchars($building['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1" for="add-capacite">Capacité</label>
        <input type="number" id="add-capacite" name="capacite" min="1" required class="w-full border rounded px-4 py-2">
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1" for="add-equipements">Équipements (séparés par des virgules)</label>
      <input type="text" id="add-equipements" name="equipements" placeholder="Tableau blanc, Projecteur, Wifi"
             class="w-full border rounded px-4 py-2">
    </div>

    <div>
      <label class="block text-sm font-medium mb-1" for="add-statut">Statut</label>
      <select id="add-statut" name="statut" required class="w-full border rounded px-4 py-2 bg-white">
        <option value="available">Opérationnelle (disponible)</option>
        <option value="maintenance">Inopérationnelle (en maintenance)</option>
        <option value="disabled">Inopérationnelle (désactivée)</option>
      </select>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1" for="add-description">Description (facultatif)</label>
      <textarea id="add-description" name="description" rows="2" class="w-full border rounded px-4 py-2"></textarea>
    </div>

    <div class="flex justify-end gap-3 pt-2">
      <button type="button" onclick="document.getElementById('add-room-modal').close()"
              class="px-4 py-2 rounded border cursor-pointer hover:bg-gray-100">
        Annuler
      </button>
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded cursor-pointer hover:bg-blue-700">
        Ajouter
      </button>
    </div>

  </form>
</dialog>

<!-- ═══════════════════════════════════════════════
     MODALES : Modifier une salle (une par ligne)
     ═══════════════════════════════════════════════ -->
<?php foreach ($rooms as $room): ?>
  <dialog id="edit-room-modal-<?= (int) $room['id'] ?>" class="rounded-lg p-0 w-full max-w-lg backdrop:bg-black/40">
    <form method="POST" action="index.php?route=admin/rooms/update" class="p-6 space-y-4">

      <h2 class="text-xl font-bold mb-2">Modifier « <?= htmlspecialchars($room['name']) ?> »</h2>

      <input type="hidden" name="id" value="<?= (int) $room['id'] ?>">

      <div>
        <label class="block text-sm font-medium mb-1" for="edit-nom-<?= (int) $room['id'] ?>">Nom de la salle</label>
        <input type="text" id="edit-nom-<?= (int) $room['id'] ?>" name="nom" required
               value="<?= htmlspecialchars($room['name']) ?>" class="w-full border rounded px-4 py-2">
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1" for="edit-batiment-<?= (int) $room['id'] ?>">Bâtiment</label>
          <select id="edit-batiment-<?= (int) $room['id'] ?>" name="batiment" required class="w-full border rounded px-4 py-2 bg-white">
            <?php foreach ($buildings as $building): ?>
              <option value="<?= (int) $building['id'] ?>" <?= (int) $building['id'] === (int) $room['building_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($building['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1" for="edit-capacite-<?= (int) $room['id'] ?>">Capacité</label>
          <input type="number" id="edit-capacite-<?= (int) $room['id'] ?>" name="capacite" min="1" required
                 value="<?= (int) $room['capacity'] ?>" class="w-full border rounded px-4 py-2">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1" for="edit-equipements-<?= (int) $room['id'] ?>">Équipements (séparés par des virgules)</label>
        <input type="text" id="edit-equipements-<?= (int) $room['id'] ?>" name="equipements"
               value="<?= htmlspecialchars($room['equipments'] ?? '') ?>" class="w-full border rounded px-4 py-2">
      </div>

      <div>
        <label class="block text-sm font-medium mb-1" for="edit-statut-<?= (int) $room['id'] ?>">Statut</label>
        <select id="edit-statut-<?= (int) $room['id'] ?>" name="statut" required class="w-full border rounded px-4 py-2 bg-white">
          <option value="available" <?= $room['status'] === 'available' ? 'selected' : '' ?>>Opérationnelle (disponible)</option>
          <option value="maintenance" <?= $room['status'] === 'maintenance' ? 'selected' : '' ?>>Inopérationnelle (en maintenance)</option>
          <option value="disabled" <?= $room['status'] === 'disabled' ? 'selected' : '' ?>>Inopérationnelle (désactivée)</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1" for="edit-description-<?= (int) $room['id'] ?>">Description (facultatif)</label>
        <textarea id="edit-description-<?= (int) $room['id'] ?>" name="description" rows="2"
                  class="w-full border rounded px-4 py-2"><?= htmlspecialchars($room['description'] ?? '') ?></textarea>
      </div>

      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="document.getElementById('edit-room-modal-<?= (int) $room['id'] ?>').close()"
                class="px-4 py-2 rounded border cursor-pointer hover:bg-gray-100">
          Annuler
        </button>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded cursor-pointer hover:bg-blue-700">
          Enregistrer
        </button>
      </div>

    </form>
  </dialog>
<?php endforeach; ?>

</body>
</html>
