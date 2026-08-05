<?php
// ═══════════════════════════════════════════════
// VUE logistics_department_room_schedule.php
// Calendrier des salles pour le service logistique.
// Affiche uniquement les réservations VALIDÉES ("approved").
// Le contrôleur ne transmet déjà que des réservations "approved"
// (voir Reservation::findActiveByDate), mais on ajoute ici une
// vérification explicite dans la vue : une réservation "pending"
// (en attente) ne doit jamais être affichée dans ce calendrier.
// ═══════════════════════════════════════════════

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

                    <?php if (empty($displayRooms)): ?>
                        <tr>
                            <td colspan="<?= count($timeSlots) + 1 ?>" class="border px-4 py-8 text-center text-gray-500">
                                Aucune salle correspondante.
                            </td>
                        </tr>
                    <?php else: ?>

                        <?php foreach ($displayRooms as $room): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="border px-4 py-3 font-semibold text-gray-800 bg-gray-50">
                                    <?= htmlspecialchars($room['name']) ?>
                                </td>

                                <?php foreach ($timeSlots as $slot): ?>
                                    <?php $reservation = $scheduleGrid[(int) $room['id']][$slot['start']] ?? null; ?>

                                    <!--
                                        Garde-fou explicite : même si le contrôleur ne transmet déjà
                                        que des réservations "approved" (Reservation::findActiveByDate),
                                        on vérifie ici le statut avant tout affichage. Une réservation
                                        "pending" (en attente) ne doit jamais apparaître dans ce calendrier.
                                    -->
                                    <?php $isApproved = $reservation && $reservation['status'] === 'approved'; ?>

                                    <td class="border p-1 align-top text-center w-32 h-16">
                                        <?php if ($isApproved): ?>
                                            <?php $style = $statusStyles['approved']; ?>
                                            <div class="border rounded p-1.5 h-full flex flex-col justify-center <?= $style ?>">
                                                <span class="font-bold text-xs truncate" title="<?= htmlspecialchars($reservation['requester_name']) ?>">
                                                    <?= htmlspecialchars($reservation['requester_name']) ?>
                                                </span>
                                                <span class="opacity-80 text-[10px] truncate" title="<?= htmlspecialchars($reservation['purpose']) ?>">
                                                    <?= htmlspecialchars($reservation['purpose']) ?>
                                                </span>
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
