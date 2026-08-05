<?php
// ═══════════════════════════════════════════════
// VUE logistics_department_historical.php
// Historique des réservations déjà traitées (validées, refusées ou
// annulées) pour le service logistique, avec filtres par semaine, par
// salle et par recherche libre (demandeur / motif).
// Variables attendues du controller :
//   $userName, $reservations, $rooms, $roomId, $weekOf, $search
// ═══════════════════════════════════════════════

$statusStyles = [
    'approved'  => 'bg-green-100 text-green-800',
    'refused'   => 'bg-red-100 text-red-800',
    'cancelled' => 'bg-gray-200 text-gray-700',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Service logistique - Historique</title>
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

    <a href="index.php?route=logistics/dashboard" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/dashboard.svg" alt="Accueil" width="24" height="24">
      Accueil
    </a>

    <a href="index.php?route=logistics/requests" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/inbox.svg" alt="Demandes de réservation" width="24" height="24">
      Demandes de réservation
    </a>

    <a href="index.php?route=logistics/calendar" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/calendar_month.svg" alt="Calendrier des salles" width="24" height="24">
      Calendrier des salles
    </a>

    <a href="index.php?route=logistics/history" class="flex items-center gap-3 p-2 bg-blue-100 rounded">
      <img src="assets/img/google-icons/history.svg" alt="Historique" width="24" height="24">
      Historique
    </a>

    <a href="index.php?route=logistics/profile" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
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
    <?= $userName ?? 'Service logistique' ?>
  </header>

  <main class="flex-1 p-8 overflow-auto">

    <h1 class="text-2xl font-bold mb-6">Historique</h1>

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

    <!-- Filtres -->
    <form action="index.php" method="GET" class="flex items-center gap-6 mb-6 flex-wrap">
      <input type="hidden" name="route" value="logistics/history">

      <div class="flex items-center gap-2">
        <label for="semaine" class="text-sm font-medium">Semaine du :</label>
        <input
          type="date"
          id="semaine"
          name="semaine"
          value="<?= htmlspecialchars($weekOf) ?>"
          onchange="this.form.submit()"
          class="border rounded px-4 py-2"
        >
      </div>

      <div>
        <select name="salle" onchange="this.form.submit()" class="border rounded px-4 py-2 bg-white">
          <option value="0">Toutes les salles</option>
          <?php foreach ($rooms as $room): ?>
            <option value="<?= (int) $room['id'] ?>" <?= $roomId === (int) $room['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($room['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="flex items-center gap-2 ml-auto">
        <input
          type="text"
          name="recherche"
          value="<?= htmlspecialchars($search) ?>"
          placeholder="Demandeur ou motif..."
          class="border rounded px-4 py-2"
        >
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded cursor-pointer hover:bg-blue-700">
          Rechercher
        </button>
      </div>

    </form>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full border-collapse border">
        <thead class="bg-gray-200">
          <tr>
            <th class="border px-4 py-2 text-center">DEMANDEUR</th>
            <th class="border px-4 py-2 text-center">SALLE</th>
            <th class="border px-4 py-2 text-center">DATE</th>
            <th class="border px-4 py-2 text-center">HORAIRE</th>
            <th class="border px-4 py-2 text-center">MOTIF</th>
            <th class="border px-4 py-2 text-center">STATUS</th>
          </tr>
        </thead>
        <tbody>

          <?php if (empty($reservations)): ?>
            <tr>
              <td colspan="6" class="border px-4 py-8 text-center text-gray-500">
                Aucune réservation trouvée pour cette semaine.
              </td>
            </tr>
          <?php else: ?>

            <?php foreach ($reservations as $reservation): ?>
              <?php
                $badge = $statusStyles[$reservation['status']] ?? 'bg-gray-100 text-gray-700';
              ?>
              <tr>
                <td class="border px-4 py-2 text-center"><?= htmlspecialchars($reservation['requester_name']) ?></td>
                <td class="border px-4 py-2 text-center"><?= htmlspecialchars($reservation['room_name']) ?></td>
                <td class="border px-4 py-2 text-center"><?= date('d/m/Y', strtotime($reservation['start_datetime'])) ?></td>
                <td class="border px-4 py-2 text-center">
                  <?= date('H:i', strtotime($reservation['start_datetime'])) ?> - <?= date('H:i', strtotime($reservation['end_datetime'])) ?>
                </td>
                <td class="border px-4 py-2 text-center"><?= htmlspecialchars($reservation['purpose']) ?></td>
                <td class="border px-4 py-2 text-center">
                  <span class="px-2 py-1 rounded text-xs font-semibold <?= $badge ?>">
                    <?= htmlspecialchars(Reservation::statusLabel($reservation['status'])) ?>
                  </span>
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
