<?php
// ═══════════════════════════════════════════════
// VUE administrator_reports.php
// Interface de l'administrateur pour générer et exporter des rapports
// (taux d'occupation des salles, nombre de réservations par salle,
// statistiques par utilisateur) au format PDF ou CSV.
// Variables attendues du contrôleur (ReportController::index) :
//   $userName, $old, $recentReports
// ═══════════════════════════════════════════════

$old           = $old ?? [];
$recentReports = $recentReports ?? [];

$selectedType   = $old['type_rapport'] ?? 'taux_occupation';
$selectedFormat = $old['format'] ?? 'pdf';
$dateDebut      = $old['date_debut'] ?? '';
$dateFin        = $old['date_fin'] ?? '';

$typeLabels = [
    'taux_occupation'    => "Taux d'occupation des salles",
    'nb_reservations'    => 'Nombre de réservations par salle',
    'stats_utilisateurs' => 'Statistiques par utilisateur',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Administrateur - Rapports</title>
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

    <a href="index.php?route=administrator/rooms" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/meeting_room.svg" alt="Salles" width="24" height="24">
      Salles
    </a>

    <a href="index.php?route=administrator/calendar" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
      <img src="assets/img/google-icons/calendar_month.svg" alt="Calendrier des salles" width="24" height="24">
      Calendrier des salles
    </a>

    <a href="index.php?route=administrator/reports" class="flex items-center gap-3 p-2 bg-blue-100 rounded">
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

    <h1 class="text-2xl font-bold mb-6">Rapports</h1>

    <!-- Notifications Flash -->
    <?php if (!empty($_SESSION['error'])): ?>
      <div class="max-w-2xl bg-red-100 text-red-700 border border-red-300 rounded px-4 py-3 mb-6">
        <?= htmlspecialchars($_SESSION['error']) ?>
      </div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
      <div class="max-w-2xl bg-green-100 text-green-700 border border-green-300 rounded px-4 py-3 mb-6">
        <?= htmlspecialchars($_SESSION['success']) ?>
      </div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form action="index.php?route=administrator/reports/export" method="POST" class="max-w-md space-y-8">

      <!-- Type de rapport -->
      <section>
        <label for="type_rapport" class="block text-sm font-medium mb-2">Type de rapport :</label>
        <select
          id="type_rapport"
          name="type_rapport"
          class="w-full border rounded px-4 py-2 bg-white"
        >
          <?php foreach ($typeLabels as $value => $label): ?>
            <option value="<?= htmlspecialchars($value) ?>" <?= $selectedType === $value ? 'selected' : '' ?>>
              <?= htmlspecialchars($label) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </section>

      <!-- Période -->
      <section>
        <span class="block text-sm font-medium mb-2">Période :</span>

        <div class="flex items-center gap-2">
          <label for="date_debut" class="text-sm">Du</label>
          <input
            type="date"
            id="date_debut"
            name="date_debut"
            value="<?= htmlspecialchars($dateDebut) ?>"
            class="border rounded px-4 py-2"
          >
          <label for="date_fin" class="text-sm">au</label>
          <input
            type="date"
            id="date_fin"
            name="date_fin"
            value="<?= htmlspecialchars($dateFin) ?>"
            class="border rounded px-4 py-2"
          >
        </div>
        <p class="text-xs text-gray-500 mt-2">
          Si aucune date n'est précisée, le rapport porte sur le mois en cours.
        </p>
      </section>

      <!-- Format d'export -->
      <section>
        <span class="block text-sm font-medium mb-2">Format d'export :</span>

        <div class="flex items-center gap-8">
          <label class="flex items-center gap-2">
            <input type="radio" name="format" value="pdf" <?= $selectedFormat === 'pdf' ? 'checked' : '' ?>>
            <span>pdf</span>
          </label>

          <label class="flex items-center gap-2">
            <input type="radio" name="format" value="csv" <?= $selectedFormat === 'csv' ? 'checked' : '' ?>>
            <span>csv</span>
          </label>
        </div>
      </section>

      <!-- Bouton Exporter -->
      <div class="flex justify-center">
        <button
          type="submit"
          class="bg-blue-600 text-white px-6 py-2 rounded cursor-pointer hover:bg-blue-700"
        >
          Exporter
        </button>
      </div>

    </form>

    <!-- Historique des exports -->
    <div class="max-w-4xl mt-12">
      <h2 class="text-lg font-bold mb-4">Rapports générés récemment</h2>

      <div class="overflow-x-auto">
        <table class="w-full border-collapse border bg-white">
          <thead class="bg-gray-200">
            <tr>
              <th class="border px-4 py-2 text-center">RAPPORT</th>
              <th class="border px-4 py-2 text-center">FORMAT</th>
              <th class="border px-4 py-2 text-center">GÉNÉRÉ PAR</th>
              <th class="border px-4 py-2 text-center">DATE</th>
              <th class="border px-4 py-2 text-center">ACTION</th>
            </tr>
          </thead>
          <tbody>

            <?php if (empty($recentReports)): ?>
              <tr>
                <td colspan="5" class="border px-4 py-6 text-center text-gray-500">
                  Aucun rapport généré pour le moment.
                </td>
              </tr>
            <?php else: ?>

              <?php foreach ($recentReports as $recentReport): ?>
                <tr>
                  <td class="border px-4 py-2"><?= htmlspecialchars($recentReport['title']) ?></td>
                  <td class="border px-4 py-2 text-center uppercase"><?= htmlspecialchars($recentReport['type']) ?></td>
                  <td class="border px-4 py-2 text-center"><?= htmlspecialchars($recentReport['generated_by_name'] ?? '—') ?></td>
                  <td class="border px-4 py-2 text-center">
                    <?= htmlspecialchars(date('d/m/Y H:i', strtotime($recentReport['generated_at']))) ?>
                  </td>
                  <td class="border px-4 py-2 text-center">
                    <a
                      href="index.php?route=administrator/reports/download&id=<?= (int) $recentReport['id'] ?>"
                      class="text-blue-600 hover:underline"
                    >
                      Télécharger
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>

            <?php endif; ?>

          </tbody>
        </table>
      </div>
    </div>

  </main>

</div>

</body>
</html>
