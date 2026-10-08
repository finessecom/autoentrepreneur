<?php
class ClientEchange {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getByClient(int $clientId): array {
        $stmt = $this->db->prepare('SELECT * FROM client_echanges WHERE client_id = ? ORDER BY date_echange DESC, created_at DESC');
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare('SELECT * FROM client_echanges WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $clientId, int $userId, array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO client_echanges (client_id, user_id, date_echange, titre, message, document_url) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $clientId,
            $userId,
            $data['date_echange'],
            $data['titre'],
            $data['message'],
            $data['document_url'] ?? null
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare('DELETE FROM client_echanges WHERE id = ? AND user_id = ?');
        return $stmt->execute([$id, $userId]);
    }

    public function count(int $clientId): int {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM client_echanges WHERE client_id = ?');
        $stmt->execute([$clientId]);
        return (int) $stmt->fetchColumn();
    }
}
