<?php
// ═══════════════════════════════════════════════
// CONTROLLER RoomController
// Gère la page de gestion des salles (consultation avec filtres) ainsi que
// le formulaire d'ajout et de modification d'une salle, réservés à
// l'administrateur.
// La gestion de session (Session.php) et des utilisateurs (User.php) ne
// sont pas de la responsabilité de ce contrôleur (principe de
// responsabilité unique) : il ne s'occupe que des salles (Room.php) et
// des bâtiments (Building.php).
// ═══════════════════════════════════════════════

require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../models/Building.php';

class RoomController
{
    /**
     * Valeurs autorisées pour le filtre de statut de la liste (?status=...).
     * 'available'   => salles opérationnelles
     * 'unavailable' => salles inopérationnelles (maintenance ou désactivée)
     */
    private const ALLOWED_STATUS_FILTERS = ['available', 'unavailable'];

    /**
     * Affiche la liste des salles avec un filtre optionnel par statut et
     * une recherche libre optionnelle sur le nom de la salle.
     * (GET index.php?route=admin/rooms)
     */
    public function manageRooms()
    {
        checkRole('admin');

        $userName = htmlspecialchars($_SESSION['user']['name']);

        $status = $_GET['status'] ?? '';
        $status = in_array($status, self::ALLOWED_STATUS_FILTERS, true) ? $status : '';

        $search = trim($_GET['recherche'] ?? '');

        $rooms = Room::findAllForAdmin($status !== '' ? $status : null, $search !== '' ? $search : null);

        require __DIR__ . '/../views/users/administrator_manage_rooms.php';
    }

    /**
     * Affiche le formulaire d'ajout d'une nouvelle salle.
     * (GET index.php?route=admin/rooms/new)
     */
    public function newRoomForm()
    {
        checkRole('admin');

        $userName  = htmlspecialchars($_SESSION['user']['name']);
        $editRoom  = null; // null = mode création
        $buildings = Building::findAll();

        // Ré-affiche les valeurs saisies précédemment en cas d'erreur de validation
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);

        require __DIR__ . '/../views/users/administrator_room_form.php';
    }

    /**
     * Traite la création d'une nouvelle salle.
     * (POST index.php?route=admin/rooms/store)
     */
    public function storeRoom()
    {
        checkRole('admin');

        $this->saveRoom(null);
    }

    /**
     * Affiche le formulaire de modification d'une salle existante.
     * (GET index.php?route=admin/rooms/edit&id=...)
     */
    public function editRoomForm()
    {
        checkRole('admin');

        $id       = (int) ($_GET['id'] ?? 0);
        $editRoom = Room::findByIdForAdmin($id);

        if (!$editRoom) {
            $_SESSION['error'] = "Salle introuvable.";
            header('Location: index.php?route=admin/rooms');
            exit;
        }

        $userName  = htmlspecialchars($_SESSION['user']['name']);
        $buildings = Building::findAll();

        // Ré-affiche les valeurs saisies précédemment en cas d'erreur de validation
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);

        require __DIR__ . '/../views/users/administrator_room_form.php';
    }

    /**
     * Traite la modification d'une salle existante.
     * (POST index.php?route=admin/rooms/update)
     */
    public function updateRoom()
    {
        checkRole('admin');

        $roomId = (int) ($_POST['id'] ?? 0);

        if ($roomId <= 0) {
            $_SESSION['error'] = "Salle introuvable.";
            header('Location: index.php?route=admin/rooms');
            exit;
        }

        $this->saveRoom($roomId);
    }

    /**
     * Valide puis enregistre (création si $roomId est null, sinon
     * modification) une salle à partir des données soumises en POST.
     * Factorise le code commun à storeRoom() et updateRoom().
     */
    private function saveRoom(?int $roomId): void
    {
        $name        = trim($_POST['nom'] ?? '');
        $buildingId  = (int) ($_POST['batiment'] ?? 0);
        $capacity    = (int) ($_POST['capacite'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $status      = $_POST['statut'] ?? '';
        $equipments  = trim($_POST['equipements'] ?? '');

        $equipmentNames = array_values(array_filter(
            array_map('trim', explode(',', $equipments)),
            fn ($equipment) => $equipment !== ''
        ));

        // Conserve la saisie pour pré-remplir le formulaire en cas d'erreur
        $old = [
            'nom'         => $name,
            'batiment'    => $buildingId,
            'capacite'    => $capacity,
            'description' => $description,
            'statut'      => $status,
            'equipements' => $equipments,
        ];

        $formRoute = $roomId === null
            ? 'admin/rooms/new'
            : 'admin/rooms/edit&id=' . $roomId;

        if ($name === '' || $buildingId <= 0 || $capacity <= 0) {
            $_SESSION['error'] = "Veuillez renseigner le nom, le bâtiment et une capacité valide pour la salle.";
            $_SESSION['old']   = $old;
            header('Location: index.php?route=' . $formRoute);
            exit;
        }

        if (!in_array($status, Room::ALLOWED_STATUSES, true)) {
            $_SESSION['error'] = "Statut de salle invalide.";
            $_SESSION['old']   = $old;
            header('Location: index.php?route=' . $formRoute);
            exit;
        }

        $description = $description !== '' ? $description : null;

        if ($roomId === null) {
            Room::create($buildingId, $name, $capacity, $description, $status, $equipmentNames);
            $_SESSION['success'] = "La salle « {$name} » a bien été ajoutée.";
        } else {
            Room::update($roomId, $buildingId, $name, $capacity, $description, $status, $equipmentNames);
            $_SESSION['success'] = "La salle « {$name} » a bien été modifiée.";
        }

        header('Location: index.php?route=admin/rooms');
        exit;
    }
}
