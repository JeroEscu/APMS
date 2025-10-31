<?php
class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Obtener todos los usuarios activos
    public function getAll() {
        $stmt = $this->pdo->query("
            SELECT u.*, 
                   CASE 
                        WHEN u.role_id = 1 THEN 'Administrador'
                        WHEN u.role_id = 2 THEN 'Personal Administrativo'
                        WHEN u.role_id = 3 THEN 'Personal de Limpieza'
                        ELSE 'Desconocido'
                   END AS role_name
            FROM users u
            WHERE u.is_active = 1
            ORDER BY u.id ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener usuario por ID
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Validación de datos
    private function validateData($data, $exclude_id = null) {
        if (empty($data['username']) || empty($data['full_name']) || empty($data['email'])) {
            die("Error: Todos los campos son obligatorios.");
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            die("Error: El formato del correo electrónico no es válido.");
        }

        // Verificar usuario duplicado
        $sql = "SELECT COUNT(*) FROM users WHERE username = :username";
        if ($exclude_id) $sql .= " AND id != :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':username', $data['username']);
        if ($exclude_id) $stmt->bindValue(':id', $exclude_id);
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            die("Error: El nombre de usuario ya existe.");
        }
    }

    // Crear usuario
    public function add($data) {
        $this->validateData($data);

        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);

        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, password, full_name, email, role_id, created_at, is_active)
            VALUES (:username, :password, :full_name, :email, :role_id, NOW(), 1)
        ");
        $stmt->execute([
            ':username' => $data['username'],
            ':password' => $passwordHash,
            ':full_name' => $data['full_name'],
            ':email' => $data['email'],
            ':role_id' => $data['role_id']
        ]);
    }

    // Actualizar usuario
    public function update($id, $data) {
        $this->validateData($data, $id);

        $sql = "UPDATE users SET username = :username, full_name = :full_name, email = :email, role_id = :role_id";
        $params = [
            ':username' => $data['username'],
            ':full_name' => $data['full_name'],
            ':email' => $data['email'],
            ':role_id' => $data['role_id'],
            ':id' => $id
        ];

        // Si se cambió la contraseña
        if (!empty($data['password'])) {
            $sql .= ", password = :password";
            $params[':password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $sql .= " WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    // Borrado (lógico)
    public function deactivate($id) {
        $stmt = $this->pdo->prepare("UPDATE users SET is_active = 0, deleted_at = NOW() WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
}
