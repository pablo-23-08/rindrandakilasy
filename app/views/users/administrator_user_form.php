<?php
// ═══════════════════════════════════════════════
// VUE administrator_user_form.php
// Formulaire d'ajout OU de modification d'un utilisateur
// ═══════════════════════════════════════════════

$editUser = $editUser ?? null;
$old      = $old ?? [];
$isEdit   = $editUser !== null;

$formName  = htmlspecialchars($old['nom'] ?? ($editUser['name'] ?? ''));
$formEmail = htmlspecialchars($old['email'] ?? ($editUser['email'] ?? ''));
$formRole  = htmlspecialchars($old['role'] ?? ($editUser['role'] ?? 'student')); // 'student' par défaut

$pageTitle    = $isEdit ? "Modification du compte utilisateur" : "Création d'un nouvel utilisateur";
$pageSubtitle = $isEdit 
    ? "Mettez à jour les informations, le rôle ou réinitialisez le mot de passe" 
    : "Renseignez les informations ci-dessous pour créer un nouvel accès";

$formAction = $isEdit ? 'index.php?route=administrator/users/update' : 'index.php?route=administrator/users/store';
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

  <header class="h-16 bg-white border-b flex justify-end items-center px-6 font-semibold">
    <?= htmlspecialchars($userName ?? 'Administrateur') ?>
  </header>

  <main class="flex-1 p-8 overflow-auto">

    <!-- En-tête de page explicatif -->
    <div class="max-w-xl mb-6">
      <h1 class="text-2xl font-bold text-gray-800"><?= $pageTitle ?></h1>
      <p class="text-sm text-gray-500 mt-1"><?= $pageSubtitle ?></p>
    </div>

    <!-- Message d'erreur -->
    <?php if (!empty($_SESSION['error'])): ?>
      <div class="max-w-xl bg-red-100 text-red-700 border border-red-300 rounded px-4 py-3 mb-6 flex items-center gap-3">
        <span class="font-medium"><?= htmlspecialchars($_SESSION['error']) ?></span>
      </div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form action="<?= $formAction ?>" method="POST" class="max-w-xl bg-white border rounded-lg p-6 space-y-6 shadow-sm">

      <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int) $editUser['id'] ?>">
      <?php endif; ?>

      <!-- SECTION 1 : Identité & Profil -->
      <div class="space-y-4">
        <div>
          <label for="nom" class="block text-sm font-medium mb-1 text-gray-700">Nom :</label>
          <input
            type="text"
            id="nom"
            name="nom"
            value="<?= $formName ?>"
            placeholder=""
            autocomplete="name"
            required
            class="w-full border rounded px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none"
          >
        </div>

        <div>
          <label for="email" class="block text-sm font-medium mb-1 text-gray-700">Adresse e-mail :</label>
          <input
            type="email"
            id="email"
            name="email"
            value="<?= $formEmail ?>"
            placeholder=""
            autocomplete="email"
            required
            class="w-full border rounded px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none"
          >
        </div>

        <!-- Sélection du rôle via boutons radio (UX directe) -->
        <div>
          <span class="block text-sm font-medium mb-2 text-gray-700">Rôle :</span>
          <div class="grid grid-cols-2 gap-3">
            <label class="flex items-center gap-2 rounded p-3 cursor-pointer hover:bg-gray-50 transition-colors">
              <input 
                type="radio" 
                name="role" 
                value="student" 
                <?= $formRole === 'student' ? 'checked' : '' ?>
                required
                class="text-blue-600 focus:ring-blue-500"
              >
              <span class="text-sm font-medium text-gray-700">Étudiant</span>
            </label>

            <label class="flex items-center gap-2 rounded p-3 cursor-pointer hover:bg-gray-50 transition-colors">
              <input 
                type="radio" 
                name="role" 
                value="teacher" 
                <?= $formRole === 'teacher' ? 'checked' : '' ?>
                required
                class="text-blue-600 focus:ring-blue-500"
              >
              <span class="text-sm font-medium text-gray-700">Enseignant</span>
            </label>
          </div>
        </div>
      </div>

      <!-- SECTION 2 : Sécurité -->
      <div class="space-y-4 pt-2">

        <div>
          <label for="password" class="block text-sm font-medium mb-1 text-gray-700">
            <?= $isEdit ? 'Nouveau mot de passe :' : 'Mot de passe :' ?>
          </label>
          <input
            type="password"
            id="password"
            name="password"
            placeholder=""
            autocomplete="new-password"
            <?= $isEdit ? '' : 'required' ?>
            class="w-full border rounded px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none"
          >
        </div>

        <div>
          <label for="confirm_password" class="block text-sm font-medium mb-1 text-gray-700">
            Confirmer le mot de passe :
          </label>
          <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            placeholder=""
            autocomplete="new-password"
            <?= $isEdit ? '' : 'required' ?>
            class="w-full border rounded px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none"
          >
        </div>
      </div>

      <!-- Actions de formulaire (Boutons de taille identique) -->
      <div class="grid grid-cols-2 gap-3 pt-4">
        <a
          href="index.php?route=administrator/users"
          class="border text-gray-600 hover:bg-gray-100 px-4 py-2 rounded text-sm font-medium transition-colors text-center flex items-center justify-center"
        >
          Annuler
        </a>
        <button 
          type="submit" 
          class="border bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium transition-colors cursor-pointer shadow-sm text-center flex items-center justify-center"
        >
          <?= $isEdit ? 'Enregistrer les modifications' : 'Créer' ?>
        </button>
      </div>

    </form>

  </main>

</div>

</body>
</html>