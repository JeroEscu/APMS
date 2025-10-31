<?php
class City {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT id, name FROM cities");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
