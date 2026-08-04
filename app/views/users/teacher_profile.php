<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mon profil</title>
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

    <a href="index.php?route=teacher/dashboard" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/dashboard.svg" alt="Accueil" width="24" height="24">
      Accueil
    </a>

    <a href="index.php?route=teacher/reservations" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/event_available.svg" alt="Mes réservations" width="24" height="24">
      Mes réservations
    </a>

    <a href="index.php?route=teacher/new-reservation" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/edit_calendar.svg" alt="Faire une réservation" width="24" height="24">
      Faire une réservation
    </a>

    <a href="index.php?route=teacher/profile" class="flex items-center gap-3 p-2 bg-blue-100 rounded">
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

    <h1 class="text-3xl font-bold mb-6">Mon profil</h1>

    <!-- Messages flash -->
    <?php if (!empty($_SESSION['success'])): ?>
      <p class="max-w-2xl bg-green-100 text-green-800 text-sm rounded px-4 py-3 mb-6">
        <?= htmlspecialchars($_SESSION['success']) ?>
      </p>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
      <p class="max-w-2xl bg-red-100 text-red-800 text-sm rounded px-4 py-3 mb-6">
        <?= htmlspecialchars($_SESSION['error']) ?>
      </p>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form action="index.php?route=teacher/profile/update" method="POST" class="max-w-2xl space-y-8">

      <!-- Photo de profil -->
      <div>
        <img
          src="assets/img/profile-placeholder.png"
          alt="Photo de profil"
          width="150"
          height="150"
          class="border rounded"
        >
      </div>

      <!-- Informations personnelles -->
      <section class="space-y-4">

        <div>
          <label for="nom" class="block text-sm font-medium mb-2">Nom complet :</label>
          <input
            type="text"
            id="nom"
            name="nom"
            value="<?= htmlspecialchars($user['name']) ?>"
            required
            class="w-full max-w-sm border rounded px-4 py-2"
          >
        </div>

        <div>
          <label for="email" class="block text-sm font-medium mb-2">Adresse Email :</label>
          <input
            type="email"
            id="email"
            name="email"
            value="<?= htmlspecialchars($user['email']) ?>"
            required
            class="w-full max-w-sm border rounded px-4 py-2"
          >
        </div>

      </section>

      <!-- Modification du mot de passe -->
      <section>
        <h2 class="text-sm font-semibold mb-4">MODIFICATION DU MOT DE PASSE</h2>

        <div class="space-y-4">

          <div>
            <label for="new_password" class="block text-sm font-medium mb-2">Nouveau mot de passe :</label>
            <input
              type="password"
              id="new_password"
              name="new_password"
              autocomplete="new-password"
              minlength="8"
              placeholder="Laisser vide pour ne pas modifier"
              class="w-full max-w-sm border rounded px-4 py-2"
            >
          </div>

          <div>
            <label for="confirm_password" class="block text-sm font-medium mb-2">Confirmer le mot de passe :</label>
            <input
              type="password"
              id="confirm_password"
              name="confirm_password"
              autocomplete="new-password"
              minlength="8"
              placeholder="Laisser vide pour ne pas modifier"
              class="w-full max-w-sm border rounded px-4 py-2"
            >
          </div>

        </div>
      </section>

      <!-- Bouton Enregistrer -->
      <div class="flex justify-end max-w-2xl">
        <button
          type="submit"
          class="bg-blue-600 text-white px-6 py-2 rounded cursor-pointer hover:bg-blue-700"
        >
          ENREGISTRER
        </button>
      </div>

    </form>

  </main>

</div>

</body>
</html>
