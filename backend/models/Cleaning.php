<?php
class Cleaning {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Verificar si ya hubo limpieza completada para una reserva
    private function hasCompletedCleaning($reservation_id, $exclude_id = null) {
        $sql = "SELECT COUNT(*) FROM cleanings WHERE reservation_id = :res AND is_active = 1";
        if ($exclude_id) $sql .= " AND id != :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':res', $reservation_id);
        if ($exclude_id) $stmt->bindValue(':id', $exclude_id);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // Validar fecha de limpieza
    public function validateCleaningDate($cleaning, $reservation_id) {
        // Obtener fecha de finalización de la reserva
        $stmt = $this->pdo->prepare("SELECT end_date FROM reservations WHERE id = ?");
        $stmt->execute([$reservation_id]);
        $reservation = $stmt->fetch();
        if (!$reservation) {
            die("Error: Reserva no encontrada.");
        }

        $cleaningDate = new DateTime($cleaning);
        $endDate = new DateTime($reservation['end_date']);
        return $cleaningDate < $endDate;
    }

    // Validación general de datos de reserva
    private function validateData($data, $exclude_id = null) {
        // No permitir doble limpieza para la misma reserva
        if ($this->hasCompletedCleaning($data['reservation_id'], $exclude_id)) {
            die("Error: Ya existe una limpieza registrada para esta reserva.");
        }
        // No se pueden registrar limpiezas en el futuro
        if (strtotime($data['cleaning_date']) > time()) {
            die("Error: La fecha de limpieza no puede ser futura.");
        }
        // Validar fechas en orden lógico
        if ($this->validateCleaningDate($data['cleaning_date'], $data['reservation_id'])) {
            die("Error: La fecha de limpieza debe ser posterior a la fecha de finalización de la reserva.");
        }
    }

    // Obtener todas las limpiezas (con filtro por rol de usuario)
    public function getAll($user_id = null, $role_id = null) {
        $sql = "
            SELECT c.*, 
                p.title AS property_name,
                r.id AS reservation_code,
                cr.name AS responsible_name
            FROM cleanings c
            JOIN properties p ON c.property_id = p.id
            JOIN reservations r ON c.reservation_id = r.id
            JOIN cleaning_responsibles cr ON c.responsible_id = cr.id
            WHERE c.is_active = 1
        ";

        // Si es personal de limpieza (role_id = 3) filtra solo sus limpiezas
        if ($role_id == 3) {
            $sql .= " AND cr.user_id = :uid";
        }

        $sql .= " ORDER BY c.cleaning_date DESC";

        $stmt = $this->pdo->prepare($sql);

        if ($role_id == 3) {
            $stmt->bindValue(':uid', $user_id, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Obtener una limpieza por ID
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM cleanings WHERE id = ? AND is_active = 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Obtener limpiezas con filtros aplicados
    public function getFiltered($filters = [], $user_id = null, $role_id = null) {
        $sql = "
            SELECT c.*, 
                p.title AS property_name, 
                r.id AS reservation_code,
                cr.name AS responsible_name
            FROM cleanings c
            JOIN properties p ON c.property_id = p.id
            JOIN reservations r ON c.reservation_id = r.id
            JOIN cleaning_responsibles cr ON c.responsible_id = cr.id
            WHERE c.is_active = 1
        ";

        $params = [];

        // Filtros normales
        if (!empty($filters['property_id'])) {
            $sql .= " AND c.property_id = :property_id";
            $params[':property_id'] = $filters['property_id'];
        }

        if (!empty($filters['responsible_id'])) {
            $sql .= " AND c.responsible_id = :responsible_id";
            $params[':responsible_id'] = $filters['responsible_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND c.cleaning_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND c.cleaning_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        // Restricción para personal de limpieza
        if ($role_id == 3) {
            $sql .= " AND cr.user_id = :user_id";
            $params[':user_id'] = $user_id;
        }

        // Ordenamiento
        if (!empty($filters['order'])) {
            $orderBy = match($filters['order']) {
                'id' => 'c.id',
                'reservation' => 'r.id',
                'date' => 'c.cleaning_date',
                default => 'c.id'
            };
            $sql .= " ORDER BY $orderBy ASC";
        } else {
            $sql .= " ORDER BY c.id ASC";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Agregar limpieza
    public function add($data) {
        // Validar datos
        $this->validateData($data);

        $sql = "INSERT INTO cleanings (reservation_id, property_id, cleaning_date, responsible_id, observations, cost)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['reservation_id'],
            $data['property_id'],
            $data['cleaning_date'],
            $data['responsible_id'],
            $data['observations'],
            $data['cost']
        ]);
    }

    // Actualizar limpieza
    public function update($id, $data) {
        // Validar datos
        $this->validateData($data, $id);

        $sql = "UPDATE cleanings 
                SET reservation_id=?, property_id=?, cleaning_date=?, responsible_id=?, observations=?, cost=? 
                WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['reservation_id'],
            $data['property_id'],
            $data['cleaning_date'],
            $data['responsible_id'],
            $data['observations'],
            $data['cost'],
            $id
        ]);
    }

    // Borrado (lógico)
    public function deactivate($id) {
        $stmt = $this->pdo->prepare("UPDATE cleanings SET is_active = 0, deleted_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
    }

    // Filtro por fecha
    public function getByDateRange($start, $end) {
        $sql = "SELECT c.*, p.title AS property_name, cr.name AS responsible_name
                FROM cleanings c
                JOIN properties p ON c.property_id = p.id
                JOIN cleaning_responsibles cr ON c.responsible_id = cr.id
                WHERE c.is_active = 1 AND c.cleaning_date BETWEEN ? AND ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$start, $end]);
        return $stmt->fetchAll();
    }

    // Obtener reservas activas con propiedad
    public function getActiveReservationsWithProperty() {
        $stmt = $this->pdo->query("
            SELECT r.id, r.end_date, p.title AS property_name, p.cleaning_cost
            FROM reservations r
            JOIN properties p ON r.property_id = p.id
            WHERE r.is_active = 1
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveProperties() {
        $stmt = $this->pdo->prepare("SELECT id, title FROM properties WHERE is_active = 1");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
