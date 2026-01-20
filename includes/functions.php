<?php
declare(strict_types=1); // włącza ścisłe typowanie

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } // zabezpiecza przed XSS
//flash pokazuje komunikat jednorazowy po przekierowaniu np. po dodaniu roweru
function flash(string $key, ?string $value = null): ?string { // funkcja do komunikatów jednorazowych z sesją
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();

    if ($value !== null) {
        $_SESSION['flash'][$key] = $value; //jesli value nie null, to ustawia komunikat np. flash('success', 'Zapisano');
        return null;
    }
    $msg = $_SESSION['flash'][$key] ?? null; // jesli nie ma klucza zwraca null
    unset($_SESSION['flash'][$key]); // usuwa komunikat po odczytaniu
    return $msg;
}
// Zapisuje akcję do pliku logów z timestampem
function log_action(string $text): void {
    // Tworzy katalog logs jeśli nie istnieje
    $dir = __DIR__ . '/../logs';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true); // 0777 - pełne uprawnienia, true - tworzy katalogi rekursywnie
    }
    $line = "[" . date('Y-m-d H:i:s') . "] " . $text . PHP_EOL;
    $path = __DIR__ . '/../logs/actions.log';
    file_put_contents($path, $line, FILE_APPEND); // dopisuje do pliku na końcu
}

function is_post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'; }

function validate_required(string $value, int $minLen = 1): bool {
    return mb_strlen(trim($value)) >= $minLen; //sprawdza czy wartość po przycięciu ma długość co najmniej minLen
}

// Waliduje email, dopuszcza puste wartości (null lub pusty string)
function validate_email_optional(?string $email): bool {
    if ($email === null || trim($email) === '') return true;
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}
// Waliduje wartość pieniężną w formacie liczby zmiennoprzecinkowej z maksymalnie dwiema cyframi po przecinku
function validate_money(string $value): bool {
    return preg_match('/^\d+(\.\d{1,2})?$/', $value) === 1 && (float)$value >= 0;
}
//Liczy koszt na podstawie godzin.
function calc_cost_by_hours(DateTime $start, DateTime $end, float $hourPrice): float {
    $diffSeconds = max(0, $end->getTimestamp() - $start->getTimestamp()); // różnica w sekundach, min 0
    $hours = (int)ceil($diffSeconds / 3600); // zaokrągla w górę do najbliższej godziny
    return round($hours * $hourPrice, 2); // zaokrągla do 2 miejsc po przecinku
}
