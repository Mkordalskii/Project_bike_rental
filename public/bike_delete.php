<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();
$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
  try {
    db()->prepare("DELETE FROM bikes WHERE id=?")->execute([$id]);
    log_action("Usunięto rower id=$id");
    flash('success', "Usunięto rower.");
  } catch (PDOException $e) {
    flash('error', "Nie można usunąć roweru (np. istnieją wypożyczenia).");
  }
}
header('Location: bikes_list.php');
exit;