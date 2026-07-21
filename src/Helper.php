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

        if ($whole === 0) return 'zéro';

        $result = '';
        if ($whole >= 1000000) {
            $millions = (int) ($whole / 1000000);
            $result .= ($millions === 1 ? 'un million' : self::numberToWords($millions) . ' millions') . ' ';
            $whole %= 1000000;
        }
        if ($whole >= 1000) {
            $thousands = (int) ($whole / 1000);
            $result .= ($thousands === 1 ? 'mille' : self::numberToWords($thousands) . ' mille') . ' ';
            $whole %= 1000;
        }
        if ($whole > 0) {
            $result .= self::numberToWords($whole);
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
                $result .= $ones[$hundreds] . ' cent' . ($hundreds > 1 ? 's ' : ' ');
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
