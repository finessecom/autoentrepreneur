<?php
class Annonce {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(): array {
        $stmt = $this->db->query('SELECT a.*, u.nom_affichage AS auteur FROM annonces a JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC');
        return $stmt->fetchAll();
    }

    public function getByType(string $type): array {
        $stmt = $this->db->prepare('SELECT a.*, u.nom_affichage AS auteur FROM annonces a JOIN users u ON a.user_id = u.id WHERE a.type = ? ORDER BY a.created_at DESC');
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare('SELECT * FROM annonces WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $userId, string $type, string $titre, string $texte): int {
        $stmt = $this->db->prepare('INSERT INTO annonces (user_id, type, titre, texte) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $type, $titre, $texte]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $type, string $titre, string $texte): bool {
        $stmt = $this->db->prepare('UPDATE annonces SET type = ?, titre = ?, texte = ? WHERE id = ?');
        return $stmt->execute([$type, $titre, $texte, $id]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM annonces WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function count(): int {
        return (int) $this->db->query('SELECT COUNT(*) FROM annonces')->fetchColumn();
    }
}
