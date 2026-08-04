<?php
// ═══════════════════════════════════════════════
// VUE logistics_department_booking_requests.php
// Interface du service logistique pour valider ou refuser les demandes de réservation.
// Variables attendues du controller : $userName, $reservations
// ═══════════════════════════════════════════════
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Service logistique - Demandes de réservation</title>
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

    <a href="index.php?route=logistics/requests" class="flex items-center gap-3 p-2 bg-blue-100 rounded">
      <img src="assets/img/google-icons/inbox.svg" alt="Demandes de réservation" width="24" height="24">
      Demandes de réservation
    </a>

    <a href="index.php?route=logistics/calendar" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
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

  <main class="flex-1 p-8">

    <h1 class="text-2xl font-bold mb-6">Demandes de réservation</h1>

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

    <!-- Table des demandes -->
    <div class="overflow-x-auto">
      <table class="w-full border-collapse border bg-white">
        <thead class="bg-gray-200">
          <tr>
            <th class="border px-4 py-2 text-center">DEMANDEUR</th>
            <th class="border px-4 py-2 text-center">SALLE</th>
            <th class="border px-4 py-2 text-center">DATE</th>
            <th class="border px-4 py-2 text-center">HORAIRE</th>
            <th class="border px-4 py-2 text-center">MOTIF</th>
            <th class="border px-4 py-2 text-center">ACTION</th>
          </tr>
        </thead>
        <tbody>

          <?php if (empty($reservations)): ?>
            <tr>
              <td colspan="6" class="border px-4 py-6 text-center text-gray-500">
                Aucune demande de réservation en attente.
              </td>
            </tr>
          <?php else: ?>

            <?php foreach ($reservations as $reservation): ?>
              <tr>
                <td class="border px-4 py-2 text-center font-medium">
                  <?= htmlspecialchars($reservation['requester_name']) ?>
                </td>
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
                  <?= htmlspecialchars($reservation['purpose']) ?>
                </td>
                <td class="border px-4 py-2 text-center">
                  <div class="flex justify-center gap-2">
                    
                    <!-- Formulaire de validation -->
                    <form method="POST" action="index.php?route=logistics/requests/approve"
                          onsubmit="return confirm('Confirmer la validation de cette réservation ?');">
                      <input type="hidden" name="id" value="<?= (int) $reservation['id'] ?>">
                      <button type="submit" class="bg-green-600 text-white px-4 py-1 rounded hover:bg-green-700 transition-colors cursor-pointer">
                        Valider
                      </button>
                    </form>

                    <!-- Formulaire de refus -->
                    <form method="POST" action="index.php?route=logistics/requests/refuse"
                          onsubmit="return confirm('Confirmer le refus de cette réservation ?');">
                      <input type="hidden" name="id" value="<?= (int) $reservation['id'] ?>">
                      <button type="submit" class="bg-red-600 text-white px-4 py-1 rounded hover:bg-red-700 transition-colors cursor-pointer">
                        Refuser
                      </button>
                    </form>

                  </div>
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