<?php
declare(strict_types=1); // włącza ścisłe typowanie

function load_config(string $path): array { // ładuje plik ini jako tablicę asocjacyjną
    if (!file_exists($path)) {
        throw new RuntimeException("Brak pliku config: $path");
    }
    $cfg = parse_ini_file($path, true, INI_SCANNER_TYPED);//parsuje plik ini do tablicy asocjacyjnej
    if (!$cfg || !isset($cfg['db'])) {
        throw new RuntimeException("Niepoprawny format config.ini");
    }
    return $cfg;
}

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo; // singleton - zwraca istniejące połączenie, jeśli już jest nawiązane

    $cfg = load_config(__DIR__ . '/../data/config.ini')['db'];

    $dsn = "mysql:host={$cfg['host']};dbname={$cfg['name']};charset={$cfg['charset']}";//data source name
    $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //fetch() zwraca tablicę asocjacyjną
    ]);
    return $pdo;
}
