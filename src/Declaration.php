<?php
class Declaration {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getByUser(int $userId, ?int $year = null): array {
        $sql = 'SELECT * FROM declarations WHERE user_id = ?';
        $params = [$userId];

        if ($year) {
            $sql .= ' AND annee = ?';
            $params[] = $year;
        }

        $sql .= ' ORDER BY annee DESC, trimestre DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById(int $id, int $userId): ?array {
        $stmt = $this->db->prepare('SELECT * FROM declarations WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        return $stmt->fetch() ?: null;
    }

    public function getByYearQuarter(int $userId, int $year, int $trimestre): ?array {
        $stmt = $this->db->prepare('SELECT * FROM declarations WHERE user_id = ? AND annee = ? AND trimestre = ?');
        $stmt->execute([$userId, $year, $trimestre]);
        return $stmt->fetch() ?: null;
    }

    public function calculerIR(float $caCommerce, float $caService): array {
        $plafondCommerce = 500000;
        $plafondService = 200000;

        $caCommercePlafonne = min($caCommerce, $plafondCommerce);
        $caServicePlafonne = min($caService, $plafondService);

        $irCommerce = $caCommercePlafonne * 0.005;
        $irService = $caServicePlafonne * 0.01;
        $irTotal = $irCommerce + $irService;

        return [
            'ir_commerce' => $irCommerce,
            'ir_service' => $irService,
            'ir_total' => $irTotal,
            'alerte_commerce' => $caCommerce > $plafondCommerce,
            'alerte_service' => $caService > $plafondService,
        ];
    }

    public function calculerCNSS(float $caTotal): array {
        $tranches = [
            ['min' => 0,     'max' => 2000,    'taux' => 0.00],
            ['min' => 2000,  'max' => 3000,    'taux' => 0.0448],
            ['min' => 3000,  'max' => 4000,    'taux' => 0.0448],
            ['min' => 4000,  'max' => 5000,    'taux' => 0.0448],
            ['min' => 5000,  'max' => 6000,    'taux' => 0.0448],
            ['min' => 6000,  'max' => 8000,    'taux' => 0.0448],
            ['min' => 8000,  'max' => 10000,   'taux' => 0.0448],
            ['min' => 10000, 'max' => PHP_INT_MAX, 'taux' => 0.0448],
        ];

        $cnssTrimestre = $caTotal / 4;
        $cotisation = 0;
        $details = [];

        foreach ($tranches as $i => $tranche) {
            if ($cnssTrimestre <= $tranche['min']) break;

            $base = min($cnssTrimestre, $tranche['max']) - $tranche['min'];
            $montant = $base * $tranche['taux'];
            $cotisation += $montant;

            $details[] = [
                'tranche' => 'T' . ($i + 1),
                'base' => $base,
                'taux' => $tranche['taux'] * 100,
                'montant' => $montant,
            ];
        }

        return [
            'cnss_trimestre' => $cotisation,
            'cnss_annuelle' => $cotisation * 4,
            'details' => $details,
        ];
    }

    public function calculerRAS(float $caClient, ?float $seuil = 80000): array {
        $applicable = $caClient > $seuil;
        $retenue = $applicable ? $caClient * 0.30 : 0;

        return [
            'applicable' => $applicable,
            'retenue' => $retenue,
            'seuil' => $seuil,
        ];
    }

    public function create(int $userId, array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO declarations (user_id, annee, trimestre, ca_commerce, ca_service, ir_calcule, cnss_calcule, cnss_tranche, retenue_source, total_a_payer, est_declare, est_paye, mode_paiement, date_declaration, ref_declaration, ref_paiement, date_paiement)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
             ca_commerce = VALUES(ca_commerce), ca_service = VALUES(ca_service),
             ir_calcule = VALUES(ir_calcule), cnss_calcule = VALUES(cnss_calcule),
             cnss_tranche = VALUES(cnss_tranche),
             retenue_source = VALUES(retenue_source), total_a_payer = VALUES(total_a_payer),
             est_declare = VALUES(est_declare), est_paye = VALUES(est_paye),
             mode_paiement = VALUES(mode_paiement),
             date_declaration = VALUES(date_declaration),
             ref_declaration = VALUES(ref_declaration),
             ref_paiement = VALUES(ref_paiement),
             date_paiement = VALUES(date_paiement)'
        );
        $stmt->execute([
            $userId,
            $data['annee'],
            $data['trimestre'],
            $data['ca_commerce'] ?? 0,
            $data['ca_service'] ?? 0,
            $data['ir_calcule'] ?? 0,
            $data['cnss_calcule'] ?? 0,
            $data['cnss_tranche'] ?? 'T0',
            $data['retenue_source'] ?? 0,
            $data['total_a_payer'] ?? 0,
            $data['est_declare'] ?? 0,
            $data['est_paye'] ?? 0,
            $data['mode_paiement'] ?? null,
            $data['date_declaration'] ?? null,
            $data['ref_declaration'] ?? null,
            $data['ref_paiement'] ?? null,
            $data['date_paiement'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public static function cnssTranches(): array {
        return [
            'T0' => ['label' => 'Gratuit', 'min' => 0, 'max' => 0, 'montant' => 0],
            'T1' => ['label' => '0 – 499 MAD', 'min' => 0, 'max' => 499, 'montant' => 300],
            'T2' => ['label' => '500 – 1 000 MAD', 'min' => 500, 'max' => 1000, 'montant' => 390],
            'T3' => ['label' => '1 001 – 2 500 MAD', 'min' => 1001, 'max' => 2500, 'montant' => 570],
            'T4' => ['label' => '2 501 – 5 000 MAD', 'min' => 2501, 'max' => 5000, 'montant' => 720],
            'T5' => ['label' => '5 001 – 10 000 MAD', 'min' => 5001, 'max' => 10000, 'montant' => 1050],
            'T6' => ['label' => '10 001 – 25 000 MAD', 'min' => 10001, 'max' => 25000, 'montant' => 1500],
            'T7' => ['label' => '25 001 – 50 000 MAD', 'min' => 25001, 'max' => 50000, 'montant' => 2250],
            'T8' => ['label' => '> 50 000 MAD', 'min' => 50001, 'max' => PHP_INT_MAX, 'montant' => 3600],
        ];
    }

    public static function cnssMontant(string $tranche): float {
        $tranches = self::cnssTranches();
        return $tranches[$tranche]['montant'] ?? 0;
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare('DELETE FROM declarations WHERE id = ? AND user_id = ?');
        return $stmt->execute([$id, $userId]);
    }

    public function count(int $userId): int {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM declarations WHERE user_id = ?');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function countAll(): int {
        return (int) $this->db->query('SELECT COUNT(*) FROM declarations')->fetchColumn();
    }

    public function getTotalsByYear(int $userId, int $year): array {
        $stmt = $this->db->prepare(
            'SELECT
                COALESCE(SUM(ir_calcule), 0) AS total_ir,
                COALESCE(SUM(cnss_calcule), 0) AS total_cnss,
                COALESCE(SUM(retenue_source), 0) AS total_ras,
                COALESCE(SUM(total_a_payer), 0) AS total_general,
                COUNT(*) AS nb_declarations
             FROM declarations WHERE user_id = ? AND annee = ?'
        );
        $stmt->execute([$userId, $year]);
        return $stmt->fetch();
    }

    public function getDeclaredInvoicesTotal(int $userId, int $year): array {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(d.total_ht), 0) AS total_ht, COUNT(*) AS nb_factures
             FROM documents d
             WHERE d.user_id = ? AND d.decl_annee = ? AND d.statut = "paye"'
        );
        $stmt->execute([$userId, $year]);
        return $stmt->fetch();
    }
}
