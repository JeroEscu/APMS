<?php
class Owner {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Obtener propietarios ordenados (si es el caso)
    public function getAll($order = 'id') {
        // Validar que el orden solo pueda ser por columnas permitidas
        $allowedOrders = ['id', 'name'];
        if (!in_array($order, $allowedOrders)) {
            $order = 'id';
        }

        $sql = "SELECT * FROM owners WHERE deleted_at IS NULL ORDER BY $order ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Obtener un propietario por ID
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM owners WHERE id = ? AND is_active = 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Agregar nuevo propietario
    public function add($data) {
        $sql = "INSERT INTO owners (name, email, phone, address)
                VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['address']
        ]);
    }

    // Actualizar propietario
    public function update($id, $data) {
        $sql = "UPDATE owners SET name=?, email=?, phone=?, address=? WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $id
        ]);
    }

    // Borrado (lógico)
    public function deactivate($id) {
        $sql = "UPDATE owners SET is_active = 0, deleted_at = NOW() WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
    }
}
