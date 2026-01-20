<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$user = require_login();

$title = "Panel";
require_once __DIR__ . '/../includes/header.php';
// Pobiera statystyki z bazy danych
$pdo = db();
$counts = [
  'bikes' => (int)$pdo->query("SELECT COUNT(*) c FROM bikes")->fetch()['c'],
  'clients' => (int)$pdo->query("SELECT COUNT(*) c FROM clients")->fetch()['c'],
  'active' => (int)$pdo->query("SELECT COUNT(*) c FROM rentals WHERE status='active'")->fetch()['c'],
  'available' => (int)$pdo->query("SELECT COUNT(*) c FROM bikes WHERE status='available'")->fetch()['c'],
];
?>
<h1 class="h3 mb-3">Panel</h1>
<!-- Wyświetla statystyki w kartach -->
<div class="row g-3">
  <?php foreach ($counts as $k => $v): ?>
    <div class="col-6 col-lg-3">
      <div class="card">
        <div class="card-body">
          <div class="text-muted"><?= e($k) ?></div>
          <div class="display-6"><?= e((string)$v) ?></div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>