<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();
$title = "Klienci";
require_once __DIR__ . '/../includes/header.php';

$q = trim($_GET['q'] ?? '');
//dynamiczne budowanie zapytania, 1=1 zawsze prawda, a później dodajemy AND
$sql = "SELECT * FROM clients WHERE 1=1";
$params = [];

if ($q !== '') {
    $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

$sql .= " ORDER BY id DESC";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$clients = $stmt->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Klienci</h1>
  <a class="btn btn-primary" href="client_form.php">+ Dodaj klienta</a>
</div>

<form class="row g-2 mb-3" method="get">
  <div class="col-md-9">
    <input class="form-control" name="q" placeholder="Szukaj: imię, nazwisko, telefon, email" value="<?= e($q) ?>">
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
          <th>ID</th>
          <th>Imię</th>
          <th>Nazwisko</th>
          <th>Telefon</th>
          <th>Email</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($clients as $c): ?>
          <tr>
            <td><?= e((string)$c['id']) ?></td>
            <td><?= e($c['first_name']) ?></td>
            <td><?= e($c['last_name']) ?></td>
            <td><?= e($c['phone'] ?? '') ?></td>
            <td><?= e($c['email'] ?? '') ?></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-primary" href="client_form.php?id=<?= e((string)$c['id']) ?>">Edytuj</a>
              <a class="btn btn-sm btn-outline-danger"
                 href="client_delete.php?id=<?= e((string)$c['id']) ?>"
                 onclick="return confirm('Usunąć klienta?')">Usuń</a>
            </td>
          </tr>
        <?php endforeach; ?>

        <?php if (!$clients): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">Brak wyników</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>