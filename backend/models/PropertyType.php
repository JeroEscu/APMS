<?php
class PropertyType {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT id, name FROM property_types");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
