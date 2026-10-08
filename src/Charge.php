<?php
class Charge {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getByUser(int $userId, string $search = ''): array {
        $sql = 'SELECT * FROM charges WHERE user_id = ?';
        $params = [$userId];

        if ($search) {
            $sql .= ' AND designation LIKE ?';
            $params[] = "%{$search}%";
        }

        $sql .= ' ORDER BY date_charge DESC, created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById(int $id, int $userId): ?array {
        $stmt = $this->db->prepare('SELECT * FROM charges WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $userId, array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO charges (user_id, date_charge, designation, montant_eur, montant_mad) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $data['date_charge'],
            $data['designation'],
            $data['montant_eur'] ?? 0,
            $data['montant_mad'] ?? 0
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, int $userId, array $data): bool {
        $stmt = $this->db->prepare(
            'UPDATE charges SET date_charge = ?, designation = ?, montant_eur = ?, montant_mad = ? WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([
            $data['date_charge'],
            $data['designation'],
            $data['montant_eur'] ?? 0,
            $data['montant_mad'] ?? 0,
            $id,
            $userId
        ]);
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare('DELETE FROM charges WHERE id = ? AND user_id = ?');
        return $stmt->execute([$id, $userId]);
    }

    public function count(int $userId): int {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM charges WHERE user_id = ?');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function totalEUR(int $userId): float {
        $stmt = $this->db->prepare('SELECT COALESCE(SUM(montant_eur), 0) FROM charges WHERE user_id = ?');
        $stmt->execute([$userId]);
        return (float) $stmt->fetchColumn();
    }

    public function totalMAD(int $userId): float {
        $stmt = $this->db->prepare('SELECT COALESCE(SUM(montant_mad), 0) FROM charges WHERE user_id = ?');
        $stmt->execute([$userId]);
        return (float) $stmt->fetchColumn();
    }
}
