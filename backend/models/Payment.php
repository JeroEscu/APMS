<?php
class Payment {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Verificar si ya hay un pago completado
    private function hasCompletedPayment($reservation_id, $exclude_id = null) {
        $sql = "SELECT COUNT(*) FROM payments WHERE reservation_id = :res AND status = 'completed' AND is_active = 1";
        if ($exclude_id) $sql .= " AND id != :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':res', $reservation_id);
        if ($exclude_id) $stmt->bindValue(':id', $exclude_id);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // Validaciones
    private function validateData($data, $exclude_id = null) {
        // No se pueden registrar pagos del futuro
        if (strtotime($data['payment_date']) > time()) {
            die("Error: La fecha de pago no puede ser futura.");
        }
        // No permitir doble pago completado
        if ($this->hasCompletedPayment($data['reservation_id'], $exclude_id)) {
            die("Error: Ya existe un pago completado para esta reserva.");
        }
    }

    // Obtener todos los pagos activos
    public function getAll() {
        $sql = "SELECT p.*, 
                       r.id AS reservation_code,
                       g.name AS guest_name,
                       pr.title AS property_name
                FROM payments p
                JOIN reservations r ON p.reservation_id = r.id
                JOIN guests g ON r.guest_id = g.id
                JOIN properties pr ON r.property_id = pr.id
                WHERE p.is_active = 1
                ORDER BY p.payment_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Obtener pago por ID
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM payments WHERE id = ? AND is_active = 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Obtener pagos con filtros y ordenados
    public function getFiltered($filters = []) {
        $sql = "
            SELECT p.*, 
                r.id AS reservation_code, 
                pr.title AS property_name, 
                g.name AS guest_name
            FROM payments p
            JOIN reservations r ON p.reservation_id = r.id
            JOIN properties pr ON r.property_id = pr.id
            JOIN guests g ON r.guest_id = g.id
            WHERE p.is_active = 1
        ";

        $params = [];

        // Filtros
        if (!empty($filters['property_id'])) {
            $sql .= " AND pr.id = :property_id";
            $params[':property_id'] = $filters['property_id'];
        }

        if (!empty($filters['method'])) {
            $sql .= " AND p.method = :method";
            $params[':method'] = $filters['method'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND p.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $sql .= " AND p.payment_date BETWEEN :from AND :to";
            $params[':from'] = $filters['date_from'];
            $params[':to'] = $filters['date_to'];
        } elseif (!empty($filters['date_from'])) {
            $sql .= " AND p.payment_date >= :from";
            $params[':from'] = $filters['date_from'];
        } elseif (!empty($filters['date_to'])) {
            $sql .= " AND p.payment_date <= :to";
            $params[':to'] = $filters['date_to'];
        }

        // Orden
        $allowedOrders = ['id', 'reservation_code', 'amount', 'payment_date'];
        $orderBy = in_array($filters['order'] ?? '', $allowedOrders) ? $filters['order'] : 'id';
        $sql .= " ORDER BY $orderBy ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Agregar pago
    public function add($data) {
        // Validar datos
        $this->validateData($data);

        $sql = "INSERT INTO payments (reservation_id, amount, payment_date, method, status, created_by)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['reservation_id'],
            $data['amount'],
            $data['payment_date'],
            $data['method'],
            $data['status'],
            $data['created_by']
        ]);
    }

    // Actualizar pago
    public function update($id, $data) {
        // Validar datos
        $this->validateData($data, $id);

        $sql = "UPDATE payments 
                SET reservation_id=?, amount=?, payment_date=?, method=?, status=? 
                WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['reservation_id'],
            $data['amount'],
            $data['payment_date'],
            $data['method'],
            $data['status'],
            $id
        ]);
    }

    // Borrado (lógico)
    public function deactivate($id) {
        $stmt = $this->pdo->prepare("UPDATE payments SET is_active = 0, deleted_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
    }

    // Obtener reservas activas
    public function getActiveReservations() {
        $stmt = $this->pdo->prepare("SELECT id FROM reservations WHERE is_active = 1");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Obtener reservas activas con costo total
    public function getActiveReservationsWithCost() {
        $stmt = $this->pdo->query("
            SELECT r.id, r.total_cost, p.title AS property_title, g.name AS guest_name
            FROM reservations r
            JOIN properties p ON r.property_id = p.id
            JOIN guests g ON r.guest_id = g.id
            WHERE r.is_active = 1
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Filtro por fecha
    public function getByDateRange($start, $end) {
        $sql = "SELECT p.*, r.id AS reservation_code, pr.title AS property_name, g.name AS guest_name
                FROM payments p
                JOIN reservations r ON p.reservation_id = r.id
                JOIN properties pr ON r.property_id = pr.id
                JOIN guests g ON r.guest_id = g.id
                WHERE p.is_active = 1 AND p.payment_date BETWEEN ? AND ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$start, $end]);
        return $stmt->fetchAll();
    }
}
