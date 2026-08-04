<?php
// Valeurs par défaut pour ré-afficher le formulaire tel qu'il était rempli
// avant une éventuelle erreur de validation (voir ReservationController::storeStudentReservation).
$old = $old ?? [];

$oldDate   = htmlspecialchars($old['date'] ?? '');
$oldFrom   = htmlspecialchars($old['de'] ?? '');
$oldTo     = htmlspecialchars($old['a'] ?? '');
$oldRoomId = (int) ($old['salle'] ?? 0);
$oldMotif  = htmlspecialchars($old['motif'] ?? '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Faire une réservation</title>
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

    <a href="index.php?route=student/reservations" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/event_available.svg" alt="Mes réservations" width="24" height="24">
      Mes réservations
    </a>

    <a href="index.php?route=student/new-reservation" class="flex items-center gap-3 p-2 bg-blue-100 rounded">
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

    <h1 class="text-3xl font-bold mb-6">Faire une réservation</h1>

    <!-- Message d'erreur -->
    <?php if (!empty($_SESSION['error'])): ?>
      <div class="max-w-3xl bg-red-100 text-red-700 border border-red-300 rounded px-4 py-3 mb-6">
        <?= htmlspecialchars($_SESSION['error']) ?>
      </div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Message de succès -->
    <?php if (!empty($_SESSION['success'])): ?>
      <div class="max-w-3xl bg-green-100 text-green-700 border border-green-300 rounded px-4 py-3 mb-6">
        <?= htmlspecialchars($_SESSION['success']) ?>
      </div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form action="index.php?route=student/new-reservation/store" method="POST" class="max-w-3xl space-y-8">

      <!-- Quand souhaitez-vous réserver ? -->
      <section>
        <h2 class="text-lg font-semibold mb-4">Quand souhaitez-vous réserver ?</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

          <div>
            <label for="date" class="block text-sm font-medium mb-2">Date :</label>
            <input
              type="date"
              id="date"
              name="date"
              value="<?= $oldDate ?>"
              min="<?= date('Y-m-d') ?>"
              required
              class="w-full border rounded px-4 py-2"
            >
          </div>

          <div>
            <label for="de" class="block text-sm font-medium mb-2">De :</label>
            <input
              type="time"
              id="de"
              name="de"
              value="<?= $oldFrom ?>"
              required
              class="w-full border rounded px-4 py-2"
            >
          </div>

          <div>
            <label for="a" class="block text-sm font-medium mb-2">À :</label>
            <input
              type="time"
              id="a"
              name="a"
              value="<?= $oldTo ?>"
              required
              class="w-full border rounded px-4 py-2"
            >
          </div>

        </div>
      </section>

      <!-- Choix de la salle -->
      <section>
        <h2 class="text-lg font-semibold mb-2">Choix de la salle</h2>
        <p class="text-sm mb-4">Sélectionnez une salle disponible pour ce créneau :</p>

        <div class="space-y-3">

          <?php if (empty($rooms)): ?>
            <p class="text-sm text-gray-500">Aucune salle n'est disponible pour le moment.</p>
          <?php else: ?>

            <?php foreach ($rooms as $room): ?>
              <label class="flex items-center gap-3">
                <input
                  type="radio"
                  name="salle"
                  value="<?= (int) $room['id'] ?>"
                  <?= $oldRoomId === (int) $room['id'] ? 'checked' : '' ?>
                  required
                >
                <span>
                  <?= htmlspecialchars($room['name']) ?>
                  | Capacité: <?= (int) $room['capacity'] ?>
                  | Équipements: <?= htmlspecialchars($room['equipments'] ?: 'Aucun') ?>
                </span>
              </label>
            <?php endforeach; ?>

          <?php endif; ?>

        </div>
      </section>

      <!-- Détails de la réservation -->
      <section>
        <h2 class="text-lg font-semibold mb-4">Détails de la réservation</h2>

        <label for="motif" class="block text-sm font-medium mb-2">Motif / Activité prévue :</label>
        <input
          type="text"
          id="motif"
          name="motif"
          value="<?= $oldMotif ?>"
          required
          class="w-full border rounded px-4 py-2"
        >
      </section>

      <!-- Soumettre -->
      <div class="flex justify-center">
        <button
          type="submit"
          class="bg-blue-600 text-white px-6 py-2 rounded cursor-pointer hover:bg-blue-700"
        >
          SOUMETTRE
        </button>
      </div>

    </form>

  </main>

</div>

</body>
</html>
