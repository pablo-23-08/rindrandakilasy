<?php
// ═══════════════════════════════════════════════
// VUE administrator_room_form.php
// Formulaire d'ajout OU de modification d'une salle.
// Même principe que administrator_user_form.php : une seule vue, utilisée
// à la fois par RoomController::newRoomForm() (création) et
// RoomController::editRoomForm() (modification).
// Variables attendues du contrôleur :
//   $userName, $editRoom (null en création), $buildings, $old
// ═══════════════════════════════════════════════

$editRoom  = $editRoom ?? null;
$buildings = $buildings ?? [];
$old       = $old ?? [];
$isEdit    = $editRoom !== null;

$formName        = htmlspecialchars($old['nom'] ?? ($editRoom['name'] ?? ''));
$formBuildingId  = (int) ($old['batiment'] ?? ($editRoom['building_id'] ?? 0));
$formCapacity    = htmlspecialchars((string) ($old['capacite'] ?? ($editRoom['capacity'] ?? '')));
$formEquipments  = htmlspecialchars($old['equipements'] ?? ($editRoom['equipments'] ?? ''));
$formStatus      = htmlspecialchars($old['statut'] ?? ($editRoom['status'] ?? 'available'));
$formDescription = htmlspecialchars($old['description'] ?? ($editRoom['description'] ?? ''));

$pageTitle    = $isEdit ? "Modifier « {$editRoom['name']} »" : "Ajouter une nouvelle salle";
$pageSubtitle = $isEdit
    ? "Mettez à jour les informations de la salle"
    : "Renseignez les informations ci-dessous pour ajouter une nouvelle salle";

$formAction = $isEdit ? 'index.php?route=administrator/rooms/update' : 'index.php?route=administrator/rooms/store';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Administrateur - <?= $pageTitle ?></title>
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
    <?= htmlspecialchars($userName ?? 'Admin') ?>
  </header>

  <main class="flex-1 p-8 overflow-auto">

    <!-- En-tête de page explicatif -->
    <div class="max-w-lg mb-6">
      <h1 class="text-2xl font-bold text-gray-800"><?= $pageTitle ?></h1>
      <p class="text-sm text-gray-500 mt-1"><?= $pageSubtitle ?></p>
    </div>

    <!-- Notifications Flash -->
    <?php if (!empty($_SESSION['error'])): ?>
      <div class="max-w-lg bg-red-100 text-red-700 border border-red-300 rounded px-4 py-3 mb-6">
        <?= htmlspecialchars($_SESSION['error']) ?>
      </div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
      <div class="max-w-lg bg-green-100 text-green-700 border border-green-300 rounded px-4 py-3 mb-6">
        <?= htmlspecialchars($_SESSION['success']) ?>
      </div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form method="POST" action="<?= $formAction ?>" class="max-w-lg bg-white border rounded-lg p-6 space-y-4 shadow-sm">

      <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int) $editRoom['id'] ?>">
      <?php endif; ?>

      <div>
        <label class="block text-sm font-medium mb-1" for="nom">Nom de la salle</label>
        <input type="text" id="nom" name="nom" required value="<?= $formName ?>" class="w-full border rounded px-4 py-2">
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1" for="batiment">Bâtiment</label>
          <select id="batiment" name="batiment" required class="w-full border rounded px-4 py-2 bg-white">
            <?php if (!$isEdit): ?>
              <option value="">Sélectionner...</option>
            <?php endif; ?>
            <?php foreach ($buildings as $building): ?>
              <option value="<?= (int) $building['id'] ?>" <?= (int) $building['id'] === $formBuildingId ? 'selected' : '' ?>>
                <?= htmlspecialchars($building['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1" for="capacite">Capacité</label>
          <input type="number" id="capacite" name="capacite" min="1" required value="<?= $formCapacity ?>" class="w-full border rounded px-4 py-2">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1" for="equipements">Équipements (séparés par des virgules)</label>
        <input type="text" id="equipements" name="equipements" placeholder="Tableau blanc, Projecteur, Wifi"
               value="<?= $formEquipments ?>" class="w-full border rounded px-4 py-2">
      </div>

      <div>
        <label class="block text-sm font-medium mb-1" for="statut">Statut</label>
        <select id="statut" name="statut" required class="w-full border rounded px-4 py-2 bg-white">
          <option value="available" <?= $formStatus === 'available' ? 'selected' : '' ?>>Opérationnelle (disponible)</option>
          <option value="maintenance" <?= $formStatus === 'maintenance' ? 'selected' : '' ?>>Inopérationnelle (en maintenance)</option>
          <option value="disabled" <?= $formStatus === 'disabled' ? 'selected' : '' ?>>Inopérationnelle (désactivée)</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1" for="description">Description (facultatif)</label>
        <textarea id="description" name="description" rows="2" class="w-full border rounded px-4 py-2"><?= $formDescription ?></textarea>
      </div>

      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="window.location.href='index.php?route=administrator/rooms'"
                class="px-4 py-2 rounded border cursor-pointer hover:bg-gray-100">
          Annuler
        </button>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded cursor-pointer hover:bg-blue-700">
          <?= $isEdit ? 'Enregistrer' : 'Ajouter' ?>
        </button>
      </div>

    </form>

  </main>

</div>

</body>
</html>
