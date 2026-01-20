<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        db()->prepare("DELETE FROM clients WHERE id=?")->execute([$id]);
        log_action("Usunięto klienta id=$id");
        flash('success', "Usunięto klienta.");
    } catch (PDOException $e) {
        // Najczęściej poleci przez FK (istnieją wypożyczenia)
        flash('error', "Nie można usunąć klienta (np. ma przypisane wypożyczenia).");
    }
}

header('Location: clients_list.php');
exit;
