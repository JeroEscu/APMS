<?php
class Guest {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Verifica si existe otro huésped con el mismo documento
    public function existsByDocument($document_number, $exclude_id = null) {
        $sql = "SELECT COUNT(*) FROM guests WHERE document_number = :doc AND is_active = 1";
        if ($exclude_id) {
            $sql .= " AND id != :id";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':doc', $document_number);
        if ($exclude_id) {
            $stmt->bindValue(':id', $exclude_id);
        }
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // Validación
    private function validateData($data, $exclude_id = null) {
        // Documento único
        if ($this->existsByDocument($data['document_number'], $exclude_id)) {
            die("Error: Ya existe un huésped registrado con ese número de documento.");
        }

        // Documento obligatorio y válido
        if (empty($data['document_number'])) {
            die("Error: El número de documento es obligatorio.");
        }
        if (empty($data['document_number']) || !is_numeric($data['document_number']) || $data['document_number'] <= 0) {
            die("Error: El número de documento es inválido.");
        }

        // Email válido
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            die("Error: El formato del correo electrónico no es válido.");
        }

        // Teléfono válido
        if (!empty($data['phone']) && !preg_match('/^\+?[0-9]{7,15}$/', $data['phone'])) {
            die("Error: El formato del teléfono no es válido.");
        }

    }

    // Obtener todos los huéspedes activos
    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM guests WHERE is_active = 1 ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Obtener un huésped por ID
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM guests WHERE id = ? AND is_active = 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Obtener limpiezas con filtros y ordenadas
    public function getFiltered($filters = []) {
        $sql = "
            SELECT * 
            FROM guests
            WHERE is_active = 1
        ";

        $params = [];

        // Filtros
        if (!empty($filters['document_type'])) {
            $sql .= " AND document_type = :document_type";
            $params[':document_type'] = $filters['document_type'];
        }

        // Orden
        $allowedOrders = ['id', 'name'];
        $orderBy = in_array($filters['order'] ?? '', $allowedOrders) ? $filters['order'] : 'id';
        $sql .= " ORDER BY $orderBy ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Agregar nuevo huésped
    public function add($data) {
        // Validar datos
        $this->validateData($data);

        $sql = "INSERT INTO guests (name, email, phone, document_type, document_number)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['document_type'],
            $data['document_number']
        ]);
    }

    // Actualizar huésped
    public function update($id, $data) {
        // Validar datos
        $this->validateData($data, $id);
        
        $sql = "UPDATE guests SET name=?, email=?, phone=?, document_type=?, document_number=? WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['document_type'],
            $data['document_number'],
            $id
        ]);

    }

    // Borrado (lógico)
    public function deactivate($id) {
        $sql = "UPDATE guests SET is_active = 0, deleted_at = NOW() WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
    }
}
