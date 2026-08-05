<?php
// ═══════════════════════════════════════════════
// VUE logistics_department_room_schedule.php
// Calendrier des salles pour le service logistique : affiche, pour une
// journée donnée, l'occupation de chaque salle sur des créneaux fixes
// d'une heure (07:00 - 08:00, 08:00 - 09:00, ..., 16:00 - 17:00).
// Variables attendues du controller (ReservationController::roomSchedule) :
// $userName, $date, $roomId, $rooms (toutes les salles, pour le filtre),
// $displayRooms (salles à afficher dans le tableau), $timeSlots, $scheduleGrid
// ═══════════════════════════════════════════════

// Libellés lisibles pour les statuts affichés dans les cellules occupées.
$statusStyles = [
    'approved' => 'bg-green-100 text-green-800 border border-green-300',
    'pending'  => 'bg-yellow-100 text-yellow-800 border border-yellow-300',
];

$statusLabels = [
    'approved' => 'Validé',
    'pending'  => 'Attente',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Service logistique - Calendrier des salles</title>
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

    <a href="index.php?route=logistics/calendar" class="flex items-center gap-3 p-2 bg-blue-100 rounded">
      <img src="assets/img/google-icons/calendar_month.svg" alt="Calendrier des salles" width="24" height="24">
      Calendrier des salles
    </a>

    <a href="index.php?route=logistics/history" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
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

    <h1 class="text-2xl font-bold mb-6">Calendrier des salles</h1>

    <!-- Filtres -->
    <form action="index.php" method="GET" class="flex items-center gap-6 mb-6">
      <input type="hidden" name="route" value="logistics/calendar">

      <div class="flex items-center gap-2">
        <label for="date" class="text-sm font-medium">Date :</label>
        <input
          type="date"
          id="date"
          name="date"
          value="<?= htmlspecialchars($date) ?>"
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

      <noscript>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded cursor-pointer hover:bg-blue-700">
          Filtrer
        </button>
      </noscript>
    </form>

    <!-- Légende -->
    <div class="flex items-center gap-6 mb-4 text-sm">
      <div class="flex items-center gap-2">
        <span class="inline-block w-4 h-4 rounded bg-green-100 border border-green-300"></span>
        Validé
      </div>
      <div class="flex items-center gap-2">
        <span class="inline-block w-4 h-4 rounded bg-yellow-100 border border-yellow-300"></span>
        En attente
      </div>
    </div>

    <!-- Calendrier -->
    <div class="overflow-x-auto">
      <table class="w-full border-collapse border bg-white">
        <thead class="bg-gray-200">
          <tr>
            <th class="border px-4 py-2 text-center">
              <div>Heures</div>
              <div>Salles</div>
            </th>
            <?php foreach ($timeSlots as $slot): ?>
              <th class="border px-4 py-2 text-center whitespace-nowrap"><?= $slot['label'] ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>

          <?php if (empty($displayRooms)): ?>
            <tr>
              <td colspan="<?= count($timeSlots) + 1 ?>" class="border px-4 py-6 text-center text-gray-500">
                Aucune salle à afficher.
              </td>
            </tr>
          <?php else: ?>

            <?php foreach ($displayRooms as $room): ?>
              <tr>
                <td class="border px-4 py-4 text-center font-medium"><?= htmlspecialchars($room['name']) ?></td>

                <?php foreach ($timeSlots as $slot): ?>
                  <?php $reservation = $scheduleGrid[(int) $room['id']][$slot['start']] ?? null; ?>

                  <td class="border px-2 py-2 text-center align-middle">
                    <?php if ($reservation): ?>
                      <div class="rounded px-2 py-2 text-xs <?= $statusStyles[$reservation['status']] ?? 'bg-gray-100 text-gray-800 border' ?>">
                        <div class="font-semibold truncate" title="<?= htmlspecialchars($reservation['requester_name']) ?>">
                          <?= htmlspecialchars($reservation['requester_name']) ?>
                        </div>
                        <div class="opacity-75 truncate" title="<?= htmlspecialchars($reservation['purpose']) ?>">
                          <?= htmlspecialchars($reservation['purpose']) ?>
                        </div>
                        <div class="font-medium">
                          <?= $statusLabels[$reservation['status']] ?? htmlspecialchars($reservation['status']) ?>
                        </div>
                      </div>
                    <?php endif; ?>
                  </td>
                <?php endforeach; ?>
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
