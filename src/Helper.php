<?php
class Helper {
    public static function sanitize(string $value): string {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    public static function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }

    public static function setSuccess(string $message): void {
        $_SESSION['flash_success'] = $message;
    }

    public static function setError(string $message): void {
        $_SESSION['flash_error'] = $message;
    }

    public static function getSuccess(): ?string {
        if (isset($_SESSION['flash_success'])) {
            $msg = $_SESSION['flash_success'];
            unset($_SESSION['flash_success']);
            return $msg;
        }
        return null;
    }

    public static function getError(): ?string {
        if (isset($_SESSION['flash_error'])) {
            $msg = $_SESSION['flash_error'];
            unset($_SESSION['flash_error']);
            return $msg;
        }
        return null;
    }

    public static function flash(): void {
        $success = self::getSuccess();
        $error = self::getError();
        if ($success) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . self::sanitize($success) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        }
        if ($error) {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . self::sanitize($error) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        }
    }

    public static function formatMoney(float $amount, string $devise = 'MAD'): string {
        return number_format($amount, 2, '.', ',') . ' ' . strtoupper($devise);
    }

    public static function deviseLabel(string $devise): string {
        $labels = ['MAD' => 'Dirham marocain', 'EUR' => 'Euro', 'USD' => 'Dollar américain', 'GBP' => 'Livre sterling'];
        return $labels[strtoupper($devise)] ?? strtoupper($devise);
    }

    public static function devises(): array {
        return ['MAD', 'EUR', 'USD', 'GBP'];
    }

    public static function validateUpload(array $file, array $allowedExtensions, int $maxSize = 5242880): ?string {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return 'Erreur lors de l\'upload du fichier.';
        }
        if ($file['size'] > $maxSize) {
            return 'Le fichier est trop volumineux (max ' . round($maxSize / 1048576, 1) . ' Mo).';
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions)) {
            return 'Type de fichier non autorisé. Autorisés : ' . implode(', ', $allowedExtensions);
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowedMimes = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
            'gif' => 'image/gif', 'webp' => 'image/webp', 'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf', 'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
        if (isset($allowedMimes[$ext]) && $mime !== $allowedMimes[$ext]) {
            return 'Le contenu du fichier ne correspond pas à son extension.';
        }
        return null;
    }

    public static function generateRef(string $type, int $year, int $id): string {
        $prefixes = ['devis' => 'DEV', 'facture' => 'FAC', 'bon_livraison' => 'BON'];
        $prefix = $prefixes[$type] ?? 'DOC';
        return sprintf('%s-%d-%04d', $prefix, $year, $id);
    }

    public static function montantEnLettres(float $amount, string $devise = 'MAD'): string {
        $whole = (int) floor($amount);
        $cents = (int) round(($amount - $whole) * 100);

        $currencyNames = [
            'MAD' => ['singular' => 'dirham', 'plural' => 'dirhams', 'sub' => 'centime', 'subPlural' => 'centimes'],
            'EUR' => ['singular' => 'euro', 'plural' => 'euros', 'sub' => 'centime', 'subPlural' => 'centimes'],
            'USD' => ['singular' => 'dollar', 'plural' => 'dollars', 'sub' => 'cent', 'subPlural' => 'cents'],
            'GBP' => ['singular' => 'livre', 'plural' => 'livres', 'sub' => 'penny', 'subPlural' => 'pence'],
        ];
        $curr = $currencyNames[strtoupper($devise)] ?? $currencyNames['MAD'];

        if ($whole === 0 && $cents === 0) return 'zéro';

        $result = '';
        $remaining = $whole;
        if ($remaining >= 1000000) {
            $millions = (int) ($remaining / 1000000);
            $result .= ($millions === 1 ? 'un million' : self::numberToWords($millions) . ' millions') . ' ';
            $remaining %= 1000000;
        }
        if ($remaining >= 1000) {
            $thousands = (int) ($remaining / 1000);
            $result .= ($thousands === 1 ? 'mille' : self::numberToWords($thousands) . ' mille') . ' ';
            $remaining %= 1000;
        }
        if ($remaining > 0) {
            $result .= self::numberToWords($remaining);
        }

        $result = trim($result);
        $currencyWord = $whole > 1 ? $curr['plural'] : $curr['singular'];
        if ($cents > 0) {
            $result .= ' ' . $currencyWord . ' et ' . self::numberToWords($cents) . ' ' . $curr['subPlural'];
        } else {
            $result .= ' ' . $currencyWord;
        }

        return ucfirst($result);
    }

    private static function numberToWords(int $num): string {
        $ones = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf',
                 'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize',
                 'dix-sept', 'dix-huit', 'dix-neuf'];
        $tens = ['', '', 'vingt', 'trente', 'quarante', 'cinquante',
                'soixante', 'soixante-dix', 'quatre-vingts', 'quatre-vingt-dix'];

        $result = '';
        if ($num >= 100) {
            $hundreds = (int) ($num / 100);
            if ($hundreds === 1) {
                $result .= 'cent ';
            } else {
                $result .= $ones[$hundreds] . ' cent' . ($num % 100 === 0 && $hundreds > 1 ? 's ' : ' ');
            }
            $num %= 100;
        }
        if ($num >= 20) {
            $result .= $tens[(int)($num / 10)] . ' ';
            $num %= 10;
            if ($num === 1 && (int)($num * 10 / 10) !== 8) {
                $result = rtrim($result) . '-';
            }
        }
        if ($num > 0) {
            $result .= $ones[$num];
        }
        return trim($result);
    }
}
