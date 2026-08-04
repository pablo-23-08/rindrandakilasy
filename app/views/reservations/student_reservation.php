<?php
$statusOptions = [
    ''          => 'Filtrer par statut',
    'approved'  => 'Validé',
    'refused'   => 'Refusé',
    'cancelled' => 'Annulé',
    'pending'   => 'Attente',
];

$statusBadgeClasses = [
    'approved'  => 'bg-green-100 text-green-800',
    'refused'   => 'bg-red-100 text-red-800',
    'cancelled' => 'bg-gray-200 text-gray-700',
    'pending'   => 'bg-yellow-100 text-yellow-800',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mes réservations</title>
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

    <a href="index.php?route=student/dashboard" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/dashboard.svg" alt="Accueil" width="24" height="24">
      Accueil
    </a>

    <a href="index.php?route=student/reservations" class="flex items-center gap-3 p-2 bg-blue-100 rounded">
      <img src="assets/img/google-icons/event_available.svg" alt="Mes réservations" width="24" height="24">
      Mes réservations
    </a>

    <a href="index.php?route=student/new-reservation" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/edit_calendar.svg" alt="Faire une réservation" width="24" height="24">
      Faire une réservation
    </a>

    <a href="index.php?route=student/profile" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
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

    <h1 class="text-3xl font-bold mb-6">Mes réservations</h1>

    <!-- Filtre -->
    <div class="mb-6">
      <form method="GET" action="index.php">
        <input type="hidden" name="route" value="student/reservations">
        <select name="status" class="border rounded px-4 py-2 bg-white" onchange="this.form.submit()">
          <?php foreach ($statusOptions as $value => $label): ?>
            <option value="<?= htmlspecialchars($value) ?>" <?= $status === $value ? 'selected' : '' ?>>
              <?= htmlspecialchars($label) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full border-collapse border">
        <thead class="bg-gray-200">
          <tr>
            <th class="border px-4 py-2 text-center">SALLE</th>
            <th class="border px-4 py-2 text-center">DATE</th>
            <th class="border px-4 py-2 text-center">HORAIRE</th>
            <th class="border px-4 py-2 text-center">STATUT</th>
            <th class="border px-4 py-2 text-center">ACTION</th>
          </tr>
        </thead>
        <tbody>

          <?php if (empty($reservations)): ?>
            <tr>
              <td colspan="5" class="border px-4 py-6 text-center text-gray-500">
                Aucune réservation trouvée.
              </td>
            </tr>
          <?php else: ?>

            <?php foreach ($reservations as $reservation): ?>
              <?php $badgeClass = $statusBadgeClasses[$reservation['status']] ?? 'bg-gray-100 text-gray-700'; ?>
              <tr>
                <td class="border px-4 py-2 text-center">
                  <?= htmlspecialchars($reservation['room_name']) ?>
                </td>
                <td class="border px-4 py-2 text-center">
                  <?= date('d/m/Y', strtotime($reservation['start_datetime'])) ?>
                </td>
                <td class="border px-4 py-2 text-center">
                  <?= date('H:i', strtotime($reservation['start_datetime'])) ?> - <?= date('H:i', strtotime($reservation['end_datetime'])) ?>
                </td>
                <td class="border px-4 py-2 text-center">
                  <span class="px-3 py-1 rounded text-sm font-medium <?= $badgeClass ?>">
                    <?= htmlspecialchars(Reservation::statusLabel($reservation['status'])) ?>
                  </span>
                </td>
                <td class="border px-4 py-2 text-center">
                  <?php if (Reservation::isCancellable($reservation['status'])): ?>
                    <form method="POST" action="index.php?route=student/reservations/cancel"
                          onsubmit="return confirm('Confirmer l\'annulation de cette réservation ?');">
                      <input type="hidden" name="id" value="<?= (int) $reservation['id'] ?>">
                      <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
                      <button type="submit" class="bg-red-600 text-white px-4 py-1 rounded hover:bg-red-700 cursor-pointer">
                        Annuler
                      </button>
                    </form>
                  <?php else: ?>
                    -
                  <?php endif; ?>
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
