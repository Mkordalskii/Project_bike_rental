<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();
$pdo = db();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: rentals_list.php');
    exit;
}

// jeżeli usuwasz aktywne wypożyczenie, dobrze jest przywrócić status roweru.
// bezpiecznie w transakcji.
$stmt = $pdo->prepare("SELECT id, bike_id, status FROM rentals WHERE id=?");
$stmt->execute([$id]);
$r = $stmt->fetch();

if (!$r) {
    flash('error', "Nie znaleziono wypożyczenia.");
    header('Location: rentals_list.php');
    exit;
}

$pdo->beginTransaction();
try {
    if ($r['status'] === 'active') {
        // przywróć rower jako dostępny (bo wypożyczenie znika)
        $pdo->prepare("UPDATE bikes SET status='available' WHERE id=?")->execute([(int)$r['bike_id']]);
    }

    $pdo->prepare("DELETE FROM rentals WHERE id=?")->execute([$id]);

    $pdo->commit();
    log_action("Usunięto wypożyczenie id=$id (status={$r['status']})");
    flash('success', "Usunięto wypożyczenie.");
} catch (Throwable $e) {
    $pdo->rollBack();
    flash('error', "Nie udało się usunąć wypożyczenia.");
}

header('Location: rentals_list.php');
exit;
