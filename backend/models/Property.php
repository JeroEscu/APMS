<?php
class Property {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Verificar si ya existe una propiedad con el mismo título para el mismo propietario
    public function existsByOwnerAndTitle($owner_id, $title, $exclude_id = null) {
        $sql = "SELECT COUNT(*) FROM properties WHERE owner_id = :owner_id AND title = :title AND is_active = 1";
        if ($exclude_id) {
            $sql .= " AND id != :id";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':owner_id', $owner_id);
        $stmt->bindValue(':title', $title);
        if ($exclude_id) {
            $stmt->bindValue(':id', $exclude_id);
        }

        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // Obtener todas las propiedades activas
    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT p.*, o.name AS owner_name, c.name AS city, pt.name AS type
                                     FROM properties p
                                     LEFT JOIN owners o ON p.owner_id = o.id
                                     LEFT JOIN cities c ON p.city_id = c.id
                                     LEFT JOIN property_types pt ON p.type_id = pt.id
                                     WHERE p.is_active = 1
                                     ORDER BY p.id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Obtener una propiedad por ID
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM properties WHERE id = ? AND is_active = 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Obtener propiedades con filtros y ordenadas
    public function getFiltered($filters = []) {
        $sql = "
            SELECT p.*, o.name AS owner_name, c.name AS city, pt.name AS type
            FROM properties p
            LEFT JOIN owners o ON p.owner_id = o.id
            LEFT JOIN cities c ON p.city_id = c.id
            LEFT JOIN property_types pt ON p.type_id = pt.id
            WHERE p.is_active = 1
        ";

        $params = [];

        // Filtros
        if (!empty($filters['owner_id'])) {
            $sql .= " AND p.owner_id = :owner_id";
            $params[':owner_id'] = $filters['owner_id'];
        }

        if (!empty($filters['city_id'])) {
            $sql .= " AND p.city_id = :city_id";
            $params[':city_id'] = $filters['city_id'];
        }

        if (!empty($filters['type'])) {
            $sql .= " AND pt.name = :type";
            $params[':type'] = $filters['type'];
        }

        // Orden
        $allowedOrders = ['id', 'title'];
        $orderBy = in_array($filters['order'] ?? '', $allowedOrders) ? $filters['order'] : 'id';
        $sql .= " ORDER BY p.$orderBy ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Agregar una nueva propiedad (con validaciones)
    public function add($data) {
        // Validaciones
        $this->validateData($data);

        $sql = "INSERT INTO properties (owner_id, title, description, city_id, address, type_id, nightly_price, cleaning_cost, max_guests)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['owner_id'],
            $data['title'],
            $data['description'],
            $data['city_id'],
            $data['address'],
            $data['type_id'],
            $data['nightly_price'],
            $data['cleaning_cost'],
            $data['max_guests']
        ]);
    }

    // Actualizar una propiedad (con validaciones)
    public function update($id, $data) {
        // Validaciones
        $this->validateData($data, $id);
        
        $sql = "UPDATE properties SET owner_id=?, title=?, description=?, city_id=?, address=?, 
                type_id=?, nightly_price=?, cleaning_cost=?, max_guests=? WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['owner_id'],
            $data['title'],
            $data['description'],
            $data['city_id'],
            $data['address'],
            $data['type_id'],
            $data['nightly_price'],
            $data['cleaning_cost'],
            $data['max_guests'],
            $id
        ]);
    }

    // Validaciones
    private function validateData($data, $exclude_id = null) {
        // Título único por propietario
        if ($this->existsByOwnerAndTitle($data['owner_id'], $data['title'], $exclude_id)) {
            die("Error: El propietario ya tiene una propiedad registrada con ese título.");
        }

        // Precio por noche positivo
        if (!isset($data['nightly_price']) || $data['nightly_price'] <= 0) {
            die("Error: El precio por noche debe ser mayor a cero.");
        }

        // Costo de limpieza positivo o nulo
        if (isset($data['cleaning_cost']) && $data['cleaning_cost'] < 0) {
            die("Error: El costo de limpieza no puede ser negativo.");
        }

        // Capacidad máxima positiva
        if (!isset($data['max_guests']) || $data['max_guests'] <= 0) {
            die("Error: La capacidad máxima de huéspedes debe ser mayor a cero.");
        }
    }

    // Borrado (lógico) con manejo de reservas asociadas
    public function deactivateWithReservations($id, $user_id = null) {
        try {
            $this->pdo->beginTransaction();

            // Desactivar la propiedad
            $sql = "UPDATE properties 
                    SET is_active = 0, deleted_at = NOW() 
                    WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);

            // Cancelar reservas futuras asociadas
            $sql = "UPDATE reservations
                    SET status = 'cancelled', is_active = 0
                    WHERE property_id = :id AND start_date > CURDATE()";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
