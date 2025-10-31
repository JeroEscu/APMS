<?php
class Reservation {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Verificar si hay solapamiento de fechas en la misma propiedad
    public function isOverlapping($property_id, $start, $end, $exclude_id = null) {
        $sql = "
            SELECT COUNT(*) 
            FROM reservations 
            WHERE property_id = :prop
            AND (
                (:start BETWEEN start_date AND end_date)
                OR (:end BETWEEN start_date AND end_date)
                OR (start_date BETWEEN :start AND :end)
            )
            AND status != 'cancelled'
            AND is_active = 1
        ";

        if ($exclude_id) {
            $sql .= " AND id != :id";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':prop', $property_id);
        $stmt->bindValue(':start', $start);
        $stmt->bindValue(':end', $end);
        if ($exclude_id) $stmt->bindValue(':id', $exclude_id);

        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // Verificar si hay solapamiento de fechas con el mismo huésped
    public function guestIsOverlapping($guest_id, $start, $end, $exclude_id = null) {
        $sql = "
            SELECT COUNT(*) 
            FROM reservations 
            WHERE guest_id = :guest
            AND (
                (:start BETWEEN start_date AND end_date)
                OR (:end BETWEEN start_date AND end_date)
                OR (start_date BETWEEN :start AND :end)
            )
            AND status != 'cancelled'
            AND is_active = 1
        ";

        if ($exclude_id) {
            $sql .= " AND id != :id";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':guest', $guest_id);
        $stmt->bindValue(':start', $start);
        $stmt->bindValue(':end', $end);
        if ($exclude_id) $stmt->bindValue(':id', $exclude_id);

        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // Validar rango de fechas correcto
    public function validateDates($start, $end) {
        $startDate = new DateTime($start);
        $endDate = new DateTime($end);
        return $startDate < $endDate;
    }

    // Validación general de datos de reserva
    private function validateData($data, $exclude_id = null) {
        // Validar fechas en orden lógico
        if (!$this->validateDates($data['start_date'], $data['end_date'])) {
            die("Error: La fecha de inicio debe ser anterior a la fecha de finalización.");
        }

        // Verificar solapamiento
        if ($this->isOverlapping($data['property_id'], $data['start_date'], $data['end_date'], $exclude_id)) {
            die("Error: Ya existe una reserva activa en esa propiedad para ese rango de fechas.");
        }

        //Verificar solapamiento del huésped
        if ($this->guestIsOverlapping($data['guest_id'], $data['start_date'], $data['end_date'], $exclude_id)) {
            die("Error: Ya existe una reserva activa para ese huésped en ese rango de fechas.");
        }
    }

    // Obtener todas las reservas activas con info de propiedad y huésped
    public function getAll() {
        $sql = "SELECT r.*, 
                       p.title AS property_title, 
                       g.name AS guest_name
                FROM reservations r
                JOIN properties p ON r.property_id = p.id
                JOIN guests g ON r.guest_id = g.id
                WHERE r.is_active = 1
                ORDER BY r.id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Obtener una reserva por ID
    public function getById($id) {
        $sql = "SELECT * FROM reservations WHERE id = ? AND is_active = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Obtener reservas con filtros y ordenadas
    public function getFiltered($filters = []) {
        $sql = "
            SELECT r.*, 
                p.title AS property_title,
                g.name AS guest_name
            FROM reservations r
            JOIN properties p ON r.property_id = p.id
            JOIN guests g ON r.guest_id = g.id
            WHERE r.is_active = 1
        ";

        $params = [];

        // Filtros
        if (!empty($filters['property_id'])) {
            $sql .= " AND r.property_id = :property_id";
            $params[':property_id'] = $filters['property_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND r.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['start_date_from']) && !empty($filters['start_date_to'])) {
            $sql .= " AND r.start_date BETWEEN :from AND :to";
            $params[':from'] = $filters['start_date_from'];
            $params[':to'] = $filters['start_date_to'];
        } elseif (!empty($filters['start_date_from'])) {
            $sql .= " AND r.start_date >= :from";
            $params[':from'] = $filters['start_date_from'];
        } elseif (!empty($filters['start_date_to'])) {
            $sql .= " AND r.start_date <= :to";
            $params[':to'] = $filters['start_date_to'];
        }

        // Orden
        $allowedOrders = ['id', 'guest_name', 'start_date'];
        $orderBy = in_array($filters['order'] ?? '', $allowedOrders) ? $filters['order'] : 'id';
        $sql .= " ORDER BY $orderBy ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Agregar reserva
    public function add($data) {
        // Validar datos
        $this->validateData($data);

        $sql = "INSERT INTO reservations 
                (property_id, guest_id, start_date, end_date, nights, total_cost, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['property_id'],
            $data['guest_id'],
            $data['start_date'],
            $data['end_date'],
            $data['nights'],
            $data['total_cost'],
            $data['status']
        ]);
    }

    // Actualizar reserva
    public function update($id, $data) {
        // Validar datos
        $this->validateData($data, $id);

        $sql = "UPDATE reservations 
                SET property_id=?, guest_id=?, start_date=?, end_date=?, nights=?, total_cost=?, status=?
                WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['property_id'],
            $data['guest_id'],
            $data['start_date'],
            $data['end_date'],
            $data['nights'],
            $data['total_cost'],
            $data['status'],
            $id
        ]);
    }

    // Borrado lógico
    public function deactivate($id) {
        $sql = "UPDATE reservations SET is_active = 0, deleted_at = NOW() WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
    }

    // Obtener propiedades activas 
    public function getActiveProperties() {
        $stmt = $this->pdo->prepare("SELECT id, title, nightly_price FROM properties WHERE is_active = 1");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Obtener huéspedes activos
    public function getActiveGuests() {
        $stmt = $this->pdo->prepare("SELECT id, name FROM guests WHERE is_active = 1");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
