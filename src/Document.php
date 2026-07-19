<?php
class Document {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    private function generateNumero(int $userId, string $typeDocument): string {
        $prefixMap = [
            'devis'         => 'prefixe_devis',
            'facture'       => 'prefixe_facture',
            'bon_livraison' => 'prefixe_livraison',
        ];
        $col = $prefixMap[$typeDocument] ?? 'DEV';

        $stmt = $this->db->prepare("SELECT {$col} FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $prefix = $stmt->fetchColumn() ?: 'DEV';

        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM documents WHERE user_id = ? AND type_document = ?'
        );
        $stmt->execute([$userId, $typeDocument]);
        $next = (int) $stmt->fetchColumn() + 1;

        return $prefix . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function getByUser(int $userId, string $type = '', string $search = ''): array {
        $sql = 'SELECT d.*, c.nom_client FROM documents d JOIN clients c ON d.client_id = c.id WHERE d.user_id = ?';
        $params = [$userId];

        if ($type && in_array($type, ['devis', 'facture', 'bon_livraison'])) {
            $sql .= ' AND d.type_document = ?';
            $params[] = $type;
        }
        if ($search) {
            $sql .= ' AND (c.nom_client LIKE ? OR d.type_document LIKE ?)';
            $like = "%{$search}%";
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= ' ORDER BY d.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById(int $id, int $userId): ?array {
        $stmt = $this->db->prepare(
            'SELECT d.*, c.nom_client, c.ice AS client_ice, c.email AS client_email, c.telephone AS client_telephone, c.adresse AS client_adresse
             FROM documents d JOIN clients c ON d.client_id = c.id WHERE d.id = ? AND d.user_id = ?'
        );
        $stmt->execute([$id, $userId]);
        return $stmt->fetch() ?: null;
    }

    public function getItems(int $documentId): array {
        $stmt = $this->db->prepare('SELECT * FROM document_items WHERE document_id = ? ORDER BY id');
        $stmt->execute([$documentId]);
        return $stmt->fetchAll();
    }

    public function create(int $userId, array $data, array $items): int {
        $this->db->beginTransaction();
        try {
            $totalHT = 0;
            foreach ($items as $item) {
                $totalHT += $item['quantite'] * $item['prix_unitaire'];
            }

            $numero = $this->generateNumero($userId, $data['type_document']);

            $stmt = $this->db->prepare(
                'INSERT INTO documents (numero, user_id, client_id, type_document, date_document, total_ht, total_ttc, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $numero,
                $userId,
                $data['client_id'],
                $data['type_document'],
                $data['date_document'],
                $totalHT,
                $totalHT,
                $data['statut'] ?? 'brouillon'
            ]);

            $docId = (int) $this->db->lastInsertId();

            $stmtItem = $this->db->prepare(
                'INSERT INTO document_items (document_id, produit_service_id, designation, quantite, prix_unitaire, total_ligne) VALUES (?, ?, ?, ?, ?, ?)'
            );
            foreach ($items as $item) {
                $totalLigne = $item['quantite'] * $item['prix_unitaire'];
                $stmtItem->execute([
                    $docId,
                    $item['produit_service_id'] ?? null,
                    $item['designation'],
                    $item['quantite'],
                    $item['prix_unitaire'],
                    $totalLigne
                ]);
            }

            $this->db->commit();
            return $docId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, int $userId, array $data, array $items): bool {
        $this->db->beginTransaction();
        try {
            $totalHT = 0;
            foreach ($items as $item) {
                $totalHT += $item['quantite'] * $item['prix_unitaire'];
            }

            $stmt = $this->db->prepare(
                'UPDATE documents SET client_id = ?, type_document = ?, date_document = ?, total_ht = ?, total_ttc = ?, statut = ? WHERE id = ? AND user_id = ?'
            );
            $stmt->execute([
                $data['client_id'],
                $data['type_document'],
                $data['date_document'],
                $totalHT,
                $totalHT,
                $data['statut'] ?? 'brouillon',
                $id,
                $userId
            ]);

            $this->db->prepare('DELETE FROM document_items WHERE document_id = ?')->execute([$id]);

            $stmtItem = $this->db->prepare(
                'INSERT INTO document_items (document_id, produit_service_id, designation, quantite, prix_unitaire, total_ligne) VALUES (?, ?, ?, ?, ?, ?)'
            );
            foreach ($items as $item) {
                $totalLigne = $item['quantite'] * $item['prix_unitaire'];
                $stmtItem->execute([
                    $id,
                    $item['produit_service_id'] ?? null,
                    $item['designation'],
                    $item['quantite'],
                    $item['prix_unitaire'],
                    $totalLigne
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateStatus(int $id, int $userId, string $statut): bool {
        $stmt = $this->db->prepare('UPDATE documents SET statut = ? WHERE id = ? AND user_id = ?');
        return $stmt->execute([$statut, $id, $userId]);
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare('DELETE FROM documents WHERE id = ? AND user_id = ?');
        return $stmt->execute([$id, $userId]);
    }

    public function count(int $userId): int {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM documents WHERE user_id = ?');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function totalCA(int $userId): float {
        $stmt = $this->db->prepare('SELECT COALESCE(SUM(total_ttc), 0) FROM documents WHERE user_id = ? AND type_document = "facture" AND statut != "annule"');
        $stmt->execute([$userId]);
        return (float) $stmt->fetchColumn();
    }

    public function countByType(int $userId): array {
        $stmt = $this->db->prepare('SELECT type_document, COUNT(*) as nb FROM documents WHERE user_id = ? GROUP BY type_document');
        $stmt->execute([$userId]);
        $result = ['devis' => 0, 'facture' => 0, 'bon_livraison' => 0];
        while ($row = $stmt->fetch()) {
            $result[$row['type_document']] = (int) $row['nb'];
        }
        return $result;
    }

    public function countAll(): int {
        return (int) $this->db->query('SELECT COUNT(*) FROM documents')->fetchColumn();
    }

    public function totalCAAll(): float {
        return (float) $this->db->query('SELECT COALESCE(SUM(total_ttc), 0) FROM documents WHERE type_document = "facture" AND statut != "annule"')->fetchColumn();
    }

    public function countByTypeAll(): array {
        $stmt = $this->db->query('SELECT type_document, COUNT(*) as nb FROM documents GROUP BY type_document');
        $result = ['devis' => 0, 'facture' => 0, 'bon_livraison' => 0];
        while ($row = $stmt->fetch()) {
            $result[$row['type_document']] = (int) $row['nb'];
        }
        return $result;
    }
}
