<?php
class Logger {
    /**
     * Registra una acción en la tabla activity_log
     * 
     * @param PDO $pdo Conexión PDO
     * @param int $user_id ID del usuario que realiza la acción
     * @param string $action Tipo de acción (create, update, delete, login, etc.)
     * @param string $entity Nombre de la entidad afectada (property, reservation, payment, etc.)
     * @param int|null $entity_id ID del registro afectado (puede ser null)
     * @param string $description Descripción breve de la acción
     */
    public static function log($pdo, $user_id, $action, $entity, $entity_id = null, $description = '') {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO activity_log (user_id, action, entity, entity_id, description, created_at)
                VALUES (:u, :a, :e, :i, :d, NOW())
            ");
            $stmt->execute([
                ':u' => $user_id,
                ':a' => $action,
                ':e' => $entity,
                ':i' => $entity_id,
                ':d' => $description
            ]);
        } catch (Exception $e) {
            // No interrumpimos el flujo del sistema por fallos en el log
            error_log("Error al registrar log: " . $e->getMessage());
        }
    }
}
