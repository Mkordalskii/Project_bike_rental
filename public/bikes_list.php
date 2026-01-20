<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();
$title = "Rowery";
require_once __DIR__ . '/../includes/header.php';

$q = trim($_GET['q'] ?? '');
$status = trim($_GET['status'] ?? '');
//dynamiczne budowanie zapytania, 1=1 zawsze prawda, a później dodajemy AND
$sql = "SELECT * FROM bikes WHERE 1=1";
$params = [];

if ($q !== '') {
  $sql .= " AND (frame_no LIKE ? OR model LIKE ?)";
  $params[] = "%$q%";
  $params[] = "%$q%";
}
// filtracja po statusie, typ też musi pasować dlatego true
if (in_array($status, ['available','rented','service'], true)) {
  $sql .= " AND status = ?";
  $params[] = $status;
}

$sql .= " ORDER BY id DESC";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$bikes = $stmt->fetchAll(); // pobiera wszystkie pasujące wiersze

$types = ['MTB','Miejski','Szosowy','Gravel','E-bike']; // tablica PHP w pamięci
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Rowery</h1>
  <a class="btn btn-primary" href="bike_form.php">+ Dodaj rower</a>
</div>

<form class="row g-2 mb-3" method="get">
  <div class="col-md-6">
    <input class="form-control" name="q" placeholder="Szukaj: numer ramy lub model" value="<?= e($q) ?>">
  </div>
  <div class="col-md-3">
    <select class="form-select" name="status">
      <option value="">-- status --</option>
      <?php foreach (['available'=>'Dostępny','rented'=>'Wypożyczony','service'=>'Serwis'] as $k=>$v): ?>
        <option value="<?= e($k) ?>" <?= $status===$k?'selected':'' ?>><?= e($v) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3 d-grid">
    <button class="btn btn-dark">Szukaj</button>
  </div>
</form>

<div class="card">
  <div class="table-responsive">
    <table class="table table-striped table-hover mb-0">
      <thead class="table-light">
        <tr>
          <th>ID</th><th>Nr ramy</th><th>Model</th><th>Typ</th><th>Cena/h</th><th>Status</th><th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($bikes as $b): ?>
          <tr>
            <td><?= e((string)$b['id']) ?></td>
            <td><?= e($b['frame_no']) ?></td>
            <td><?= e($b['model']) ?></td>
            <td><?= e($b['type']) ?></td>
            <td><?= e((string)$b['hour_price']) ?></td>
            <td>
              <?php
              //mapowanie statusu na klasę badge w Bootstrap
                $badge = match($b['status']) {
                  'available' => 'success',
                  'rented' => 'warning',
                  default => 'secondary'
                };
              ?>
              <span class="badge bg-<?= e($badge) ?> badge-status"><?= e($b['status']) ?></span>
            </td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-primary" href="bike_form.php?id=<?= e((string)$b['id']) ?>">Edytuj</a>
              <a class="btn btn-sm btn-outline-danger" href="bike_delete.php?id=<?= e((string)$b['id']) ?>"
                 onclick="return confirm('Usunąć rower?')">Usuń</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$bikes): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">Brak wyników</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
