<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$user = require_login();
$pdo = db();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    flash('error', "Niepoprawne ID wypożyczenia.");
    header('Location: rentals_list.php');
    exit;
}

// Pobieramy wypożyczenie + dane roweru + klienta
$stmt = $pdo->prepare("
SELECT r.*,
       b.hour_price, b.frame_no, b.model,
       c.first_name, c.last_name
FROM rentals r
JOIN bikes b ON b.id = r.bike_id
JOIN clients c ON c.id = r.client_id
WHERE r.id = ?
");
$stmt->execute([$id]);
$rental = $stmt->fetch();

if (!$rental) {
    flash('error', "Nie znaleziono wypożyczenia.");
    header('Location: rentals_list.php');
    exit;
}

if ($rental['status'] !== 'active') {
    flash('error', "To wypożyczenie jest już zakończone.");
    header('Location: rentals_list.php');
    exit;
}

$title = "Zwrot roweru";
$errors = [];

$endAt = new DateTime(); // teraz
$startAt = new DateTime((string)$rental['start_at']);
$hourPrice = (float)$rental['hour_price'];
$cost = calc_cost_by_hours($startAt, $endAt, $hourPrice);

if (is_post()) {
    // Możesz pozwolić na korektę kosztu (opcjonalnie). Tu trzymamy prosto:
    $pdo->beginTransaction();
    try {
        // 1) zamykamy wypożyczenie
        $stmt = $pdo->prepare("UPDATE rentals SET end_at=?, price_total=?, status='closed' WHERE id=? AND status='active'");
        $stmt->execute([$endAt->format('Y-m-d H:i:s'), $cost, $id]);

        // 2) ustawiamy rower jako dostępny
        $pdo->prepare("UPDATE bikes SET status='available' WHERE id=?")->execute([(int)$rental['bike_id']]);

        $pdo->commit();

        log_action("Zwrot wypożyczenia id=$id, koszt=$cost, user_id={$user['id']}");
        flash('success', "Zakończono wypożyczenie. Koszt: $cost");
        header('Location: rentals_list.php');
        exit;
    } catch (Throwable $e) {
        $pdo->rollBack();
        $errors[] = "Błąd przy zwrocie.";
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-3">Zwrot roweru</h1>

<?php if ($errors): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="card card-body" style="max-width:920px">
  <div class="row g-3">
    <div class="col-md-6">
      <div class="text-muted">Klient</div>
      <div class="fw-semibold"><?= e($rental['first_name'].' '.$rental['last_name']) ?></div>
    </div>
    <div class="col-md-6">
      <div class="text-muted">Rower</div>
      <div class="fw-semibold"><?= e($rental['frame_no'].' — '.$rental['model']) ?></div>
    </div>
    <div class="col-md-4">
      <div class="text-muted">Start</div>
      <div><?= e((string)$rental['start_at']) ?></div>
    </div>
    <div class="col-md-4">
      <div class="text-muted">Zwrot (teraz)</div>
      <div><?= e($endAt->format('Y-m-d H:i:s')) ?></div>
    </div>
    <div class="col-md-4">
      <div class="text-muted">Stawka</div>
      <div><?= e((string)$hourPrice) ?> /h</div>
    </div>
    <div class="col-md-4">
      <div class="text-muted">Kaucja</div>
      <div><?= e((string)$rental['deposit']) ?></div>
    </div>
    <div class="col-md-4">
      <div class="text-muted">Wyliczony koszt</div>
      <div class="fs-5 fw-bold"><?= e((string)$cost) ?></div>
    </div>
  </div>

  <form method="post" class="mt-3 d-flex gap-2">
    <button class="btn btn-success">Zatwierdź zwrot</button>
    <a class="btn btn-secondary" href="rentals_list.php">Anuluj</a>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
