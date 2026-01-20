<?php
declare(strict_types=1); // włącza ścisłe typowanie

require_once __DIR__ . '/db.php'; // dołącza plik z funkcją db() aby mieć dostęp do bazy danych
// Uruchamia sesję, jeśli nie jest już uruchomiona
// sesja jest potrzebna do przechowywania informacji o zalogowanym użytkowniku w $_SESSION czyli pliku cookie na serwerze
function start_session(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}
// Zwraca aktualnie zalogowanego użytkownika jako tablicę asocjacyjną lub null, jeśli nikt nie jest zalogowany
function current_user(): ?array {
    start_session();
    if (!isset($_SESSION['user_id'])) return null;

    $stmt = db()->prepare("SELECT id, username, role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch(); // pobiera użytkownika z bazy
    return $u ?: null; // zwraca użytkownika lub null, jeśli nie znaleziono
}
// Wymusza zalogowanie użytkownika, przekierowując na stronę logowania, jeśli nikt nie jest zalogowany
// Zwraca tablicę z danymi zalogowanego użytkownika
function require_login(): array {
    $u = current_user();
    if (!$u) {
        header('Location: login.php');
        exit;
    }
    return $u;
}
// Zapisuje identyfikator użytkownika w sesji, oznaczając go jako zalogowanego
//current_user() będzie teraz zwracać dane tego użytkownika
function login_user(int $userId): void {
    start_session();
    $_SESSION['user_id'] = $userId;
}

function logout_user(): void {
    start_session();
    $_SESSION = [];// czyści wszystkie dane sesji
    session_destroy();// niszczy sesję, cookie sesyjne przestaje być ważne
}
