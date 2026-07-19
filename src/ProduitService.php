<?php
class ProduitService {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getByUser(int $userId, string $type = ''): array {
        $sql = 'SELECT * FROM produits_services WHERE user_id = ?';
        $params = [$userId];

        if ($type && in_array($type, ['commerce', 'service'])) {
            $sql .= ' AND type_activite = ?';
            $params[] = $type;
        }

        $sql .= ' ORDER BY created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById(int $id, int $userId): ?array {
        $stmt = $this->db->prepare('SELECT * FROM produits_services WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $userId, array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO produits_services (user_id, type_activite, designation, prix_unitaire, image_url) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $data['type_activite'],
            $data['designation'],
            $data['prix_unitaire'],
            $data['image_url'] ?? null
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, int $userId, array $data): bool {
        $stmt = $this->db->prepare(
            'UPDATE produits_services SET type_activite = ?, designation = ?, prix_unitaire = ?, image_url = ? WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([
            $data['type_activite'],
            $data['designation'],
            $data['prix_unitaire'],
            $data['image_url'] ?? null,
            $id,
            $userId
        ]);
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare('DELETE FROM produits_services WHERE id = ? AND user_id = ?');
        return $stmt->execute([$id, $userId]);
    }

    public function count(int $userId): int {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM produits_services WHERE user_id = ?');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }
}
