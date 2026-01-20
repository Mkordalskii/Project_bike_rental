<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();
$title = "Wypożyczenia";
require_once __DIR__ . '/../includes/header.php';

$q = trim($_GET['q'] ?? '');
$status = trim($_GET['status'] ?? '');
//dynamiczne budowanie zapytania, 1=1 zawsze prawda, a później dodajemy AND
$sql = "
SELECT r.*,
       b.frame_no, b.model,
       c.first_name, c.last_name
FROM rentals r
JOIN bikes b ON b.id = r.bike_id
JOIN clients c ON c.id = r.client_id
WHERE 1=1
";
$params = [];

if ($q !== '') {
    $sql .= " AND (c.last_name LIKE ? OR c.first_name LIKE ? OR b.frame_no LIKE ? OR b.model LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}
// filtracja po statusie, typ też musi pasować dlatego true
if (in_array($status, ['active','closed'], true)) {
    $sql .= " AND r.status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY r.id DESC";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$rentals = $stmt->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Wypożyczenia</h1>
  <a class="btn btn-primary" href="rental_new.php">+ Nowe wypożyczenie</a>
</div>

<form class="row g-2 mb-3" method="get">
  <div class="col-md-6">
    <input class="form-control" name="q" placeholder="Szukaj: nazwisko, imię, nr ramy, model" value="<?= e($q) ?>">
  </div>
  <div class="col-md-3">
    <select class="form-select" name="status">
      <option value="">-- status --</option>
      <option value="active" <?= $status==='active'?'selected':'' ?>>Aktywne</option>
      <option value="closed" <?= $status==='closed'?'selected':'' ?>>Zakończone</option>
    </select>
  </div>
  <div class="col-md-3 d-grid">
    <button class="btn btn-dark">Filtruj</button>
  </div>
</form>

<div class="card">
  <div class="table-responsive">
    <table class="table table-striped table-hover mb-0">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Klient</th>
          <th>Rower</th>
          <th>Start</th>
          <th>Koniec</th>
          <th>Status</th>
          <th>Kaucja</th>
          <th>Koszt</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rentals as $r): ?>
          <tr>
            <td><?= e((string)$r['id']) ?></td>
            <td><?= e($r['first_name'].' '.$r['last_name']) ?></td>
            <td><?= e($r['frame_no'].' — '.$r['model']) ?></td>
            <td><?= e((string)$r['start_at']) ?></td>
            <td><?= e((string)($r['end_at'] ?? '')) ?></td>
            <td>
              <?php $badge = $r['status']==='active' ? 'warning' : 'success'; ?>
              <span class="badge bg-<?= e($badge) ?>"><?= e($r['status']) ?></span>
            </td>
            <td><?= e((string)$r['deposit']) ?></td>
            <td><?= e((string)($r['price_total'] ?? '')) ?></td>
            <td class="text-end">
              <?php if ($r['status'] === 'active'): ?>
                <a class="btn btn-sm btn-outline-success"
                   href="rental_return.php?id=<?= e((string)$r['id']) ?>">
                   Zwrot
                </a>
              <?php endif; ?>
              <a class="btn btn-sm btn-outline-danger"
                 href="rental_delete.php?id=<?= e((string)$r['id']) ?>"
                 onclick="return confirm('Usunąć wypożyczenie?')">Usuń</a>
            </td>
          </tr>
        <?php endforeach; ?>

        <?php if (!$rentals): ?>
          <tr><td colspan="9" class="text-center text-muted py-4">Brak wyników</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
