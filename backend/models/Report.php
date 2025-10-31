<?php
class Report {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getProperties() {
        $sql = "SELECT id, title, city_id, nightly_price, cleaning_cost, is_active
                FROM properties";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReservations() {
        $sql = "SELECT r.id, p.title AS property, g.name AS guest, r.start_date, r.end_date, r.total_cost, r.status
                FROM reservations r
                JOIN properties p ON r.property_id = p.id
                JOIN guests g ON r.guest_id = g.id
                WHERE r.deleted_at IS NULL";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPayments() {
        $sql = "SELECT pay.id, res.id AS reservation_id, pay.amount, pay.payment_date, pay.method, pay.status
                FROM payments pay
                JOIN reservations res ON pay.reservation_id = res.id
                WHERE pay.deleted_at IS NULL";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCleanings() {
        $sql = "SELECT c.id, p.title AS property, r.id AS reservation_id, cr.name AS responsible,
                       c.cleaning_date, c.cost, c.observations
                FROM cleanings c
                JOIN properties p ON c.property_id = p.id
                JOIN reservations r ON c.reservation_id = r.id
                JOIN cleaning_responsibles cr ON c.responsible_id = cr.id
                WHERE c.deleted_at IS NULL";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActivityLog() {
        $sql = "SELECT a.id, a.entity, a.entity_id, a.action, u.username AS user, a.description, a.created_at
                FROM activity_log a
                LEFT JOIN users u ON a.user_id = u.id
                ORDER BY a.created_at DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

