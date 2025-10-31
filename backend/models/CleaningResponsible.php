<?php
class CleaningResponsible {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    
    public function getAll($order = 'id', $includeInactive = false) {
        $sql = "SELECT * FROM cleaning_responsibles";

        if (!$includeInactive) {
            $sql .= " WHERE is_active = 1 AND deleted_at IS NULL";
        }

        // Validar que el orden solo pueda ser por columnas permitidas
        $allowedOrders = ['id', 'name'];
        if (!in_array($order, $allowedOrders)) {
            $order = 'id';
        }
        
        $sql .= " ORDER BY $order ASC";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    // Obtener un responsable por ID
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM cleaning_responsibles WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener un responsable por user_id
    public function getByUserId($user_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM cleaning_responsibles WHERE user_id = :uid LIMIT 1");
        $stmt->execute([':uid' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Agregar un nuevo responsable
    public function add($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO cleaning_responsibles (name, created_at, is_active)
            VALUES (:name, NOW(), 1)
        ");
        return $stmt->execute([
            ':name' => $data['name']
        ]);
    }

    // Actualizar un responsable
    public function update($id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE cleaning_responsibles 
            SET name = :name 
            WHERE id = :id AND deleted_at IS NULL
        ");
        return $stmt->execute([
            ':name' => $data['name'],
            ':id' => $id
        ]);
    }

    // Borrado (lógico)
    public function delete($id) {
        $stmt = $this->pdo->prepare("
            UPDATE cleaning_responsibles 
            SET is_active = 0, deleted_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([':id' => $id]);
    }
}
