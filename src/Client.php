<?php
class Client {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getByUser(int $userId, string $search = ''): array {
        $sql = 'SELECT * FROM clients WHERE user_id = ?';
        $params = [$userId];

        if ($search) {
            $sql .= ' AND (nom_client LIKE ? OR ice LIKE ? OR email LIKE ?)';
            $like = "%{$search}%";
            $params = array_merge($params, [$like, $like, $like]);
        }

        $sql .= ' ORDER BY created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById(int $id, int $userId): ?array {
        $stmt = $this->db->prepare('SELECT * FROM clients WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $userId, array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO clients (user_id, nom_client, ice, email, telephone, adresse, devise) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $data['nom_client'],
            $data['ice'] ?? null,
            $data['email'] ?? null,
            $data['telephone'] ?? null,
            $data['adresse'] ?? null,
            $data['devise'] ?? 'MAD'
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, int $userId, array $data): bool {
        $stmt = $this->db->prepare(
            'UPDATE clients SET nom_client = ?, ice = ?, email = ?, telephone = ?, adresse = ?, devise = ? WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([
            $data['nom_client'],
            $data['ice'] ?? null,
            $data['email'] ?? null,
            $data['telephone'] ?? null,
            $data['adresse'] ?? null,
            $data['devise'] ?? 'MAD',
            $id,
            $userId
        ]);
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare('DELETE FROM clients WHERE id = ? AND user_id = ?');
        return $stmt->execute([$id, $userId]);
    }

    public function count(int $userId): int {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM clients WHERE user_id = ?');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function countAll(): int {
        return (int) $this->db->query('SELECT COUNT(*) FROM clients')->fetchColumn();
    }
}
