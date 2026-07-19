<?php
class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function update(int $id, array $data): bool {
        $fields = [
            'nom_complet', 'nom_affichage', 'titre_pro', 'titre_pro_ar', 'ville',
            'langue_principale', 'bio', 'bio_ar', 'whatsapp', 'telephone',
            'site_web', 'mots_cles', 'raison_sociale', 'email_pro',
            'cnie', 'ice', 'identifiant_fiscal', 'taxe_professionnelle',
            'prefixe_devis', 'prefixe_facture', 'prefixe_livraison',
            'signature_taille', 'prefixe_devis', 'prefixe_facture', 'prefixe_livraison'
        ];

        $sets = [];
        $values = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $sets[] = "$field = ?";
                $values[] = trim($data[$field]);
            }
        }

        if (!empty($data['reseaux_sociaux']) && is_array($data['reseaux_sociaux'])) {
            $sets[] = 'reseaux_sociaux = ?';
            $values[] = json_encode($data['reseaux_sociaux']);
        }

        if (empty($sets)) return false;

        $values[] = $id;
        $sql = 'UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    public function updatePassword(int $id, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare('UPDATE users SET password = ? WHERE id = ?');
        return $stmt->execute([$hash, $id]);
    }

    public function updateSignature(int $id, string $path): bool {
        $stmt = $this->db->prepare('UPDATE users SET signature_url = ? WHERE id = ?');
        return $stmt->execute([$path, $id]);
    }

    public function updateLogo(int $id, string $path): bool {
        $stmt = $this->db->prepare('UPDATE users SET logo_pdf_url = ? WHERE id = ?');
        return $stmt->execute([$path, $id]);
    }

    public function getAll(): array {
        $stmt = $this->db->query('SELECT id, nom_complet, email, role, ville, created_at FROM users ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    public function count(): int {
        return (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }
}
