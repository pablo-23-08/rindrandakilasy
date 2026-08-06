<?php
// ═══════════════════════════════════════════════
// VUE room_schedule.php
// Calendrier des salles (créneaux horaires x salles) pour une journée
// donnée. N'affiche que les réservations VALIDÉES ("approved") : le
// contrôleur ne transmet déjà que des réservations "approved" (voir
// Reservation::findActiveByDate), mais on garde une vérification
// explicite ici par sécurité (une réservation "pending" ne doit jamais
// apparaître dans ce calendrier).
//
// Vue PARTAGÉE entre le service logistique (route logistics/calendar)
// et l'administrateur (route administrator/calendar) : les deux rôles utilisent
// exactement la même page (même logique, même présentation), seuls le
// menu latéral, le titre et la route du formulaire de filtre changent
// en fonction du rôle connecté. Cela évite de dupliquer le code dans
// deux fichiers (logistics_department_room_schedule.php + administrator_room_schedule.php).
//
// Variables attendues du controller :
//   $userName, $date, $rooms, $roomId, $timeSlots, $scheduleGrid
// ═══════════════════════════════════════════════

$role         = $_SESSION['user']['role'] ?? 'logistics_department';
$isAdmin      = $role === 'admin';
$calendarRoute = $isAdmin ? 'administrator/calendar' : 'logistics/calendar';

// Seul le statut "approved" est pertinent dans ce calendrier :
// les réservations en attente ne doivent pas y apparaître.
$statusStyles = [
    'approved' => 'bg-green-100 text-green-800 border-green-300',
];
$statusLabels = [
    'approved' => 'Validé',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $isAdmin ? 'Administrateur' : 'Service logistique' ?> - Calendrier des salles</title>
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
        <?php if ($isAdmin): ?>
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
            <a href="index.php?route=administrator/calendar" class="flex items-center gap-3 p-2 bg-blue-100 rounded">
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
        <?php else: ?>
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
        <?php endif; ?>
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
        <?= htmlspecialchars($userName ?? ($isAdmin ? 'Administrateur' : 'Service logistique')) ?>
    </header>

    <main class="flex-1 p-8 overflow-auto">

        <h1 class="text-2xl font-bold mb-6">Calendrier des salles</h1>

        <!-- Filtres -->
        <form action="index.php" method="GET" class="flex items-center gap-6 mb-6">
            <input type="hidden" name="route" value="<?= $calendarRoute ?>">

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

        <!-- Calendrier : uniquement les réservations validées ("approved") -->
        <div class="overflow-x-auto shadow-sm rounded-lg">
            <table class="w-full border-collapse bg-white text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-4 py-3 text-left font-semibold text-gray-700 w-48">Salles \ Heures</th>
                        <?php foreach ($timeSlots as $slot): ?>
                            <th class="border px-2 py-3 text-center whitespace-nowrap font-medium text-gray-600">
                                <?= $slot['label'] ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>

                    <?php if (empty($rooms)): ?>
                        <tr>
                            <td colspan="<?= count($timeSlots) + 1 ?>" class="border px-4 py-6 text-center text-gray-500">
                                Aucune salle correspondante.
                            </td>
                        </tr>
                    <?php else: ?>

                        <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td class="border px-4 py-3 font-medium bg-gray-50">
                                    <?= htmlspecialchars($room['name']) ?>
                                </td>

                                <?php foreach ($timeSlots as $slot): ?>
                                    <?php $cell = $scheduleGrid[$room['id']][$slot['start']] ?? null; ?>

                                    <?php if ($cell && $cell['status'] === 'approved'): ?>
                                        <td class="border px-2 py-2 text-center align-top <?= $statusStyles[$cell['status']] ?>">
                                            <div class="font-semibold text-xs"><?= htmlspecialchars($cell['requester_name']) ?></div>
                                            <div class="text-xs truncate"><?= htmlspecialchars($cell['purpose']) ?></div>
                                            <div class="text-[10px] mt-1 uppercase tracking-wide">
                                                <?= $statusLabels[$cell['status']] ?>
                                            </div>
                                        </td>
                                    <?php else: ?>
                                        <td class="border px-2 py-2"></td>
                                    <?php endif; ?>
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
