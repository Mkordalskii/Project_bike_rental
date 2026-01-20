<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();
$pdo = db();

$types = ['MTB','Miejski','Szosowy','Gravel','E-bike'];
$statuses = ['available'=>'Dostępny','rented'=>'Wypożyczony','service'=>'Serwis'];
// Pobranie id roweru z parametru GET, lub 0 jeśli dodajemy nowy
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$bike = ['frame_no'=>'','model'=>'','type'=>'MTB','hour_price'=>'0.00','status'=>'available','notes'=>''];
$errors = [];

if ($id > 0) {
  $stmt = $pdo->prepare("SELECT * FROM bikes WHERE id=?");
  $stmt->execute([$id]);
  $bike = $stmt->fetch() ?: $bike;
}

if (is_post()) {
  $bike['frame_no'] = trim($_POST['frame_no'] ?? '');
  $bike['model'] = trim($_POST['model'] ?? '');
  $bike['type'] = trim($_POST['type'] ?? 'MTB');
  $bike['hour_price'] = trim($_POST['hour_price'] ?? '0');
  $bike['status'] = trim($_POST['status'] ?? 'available');
  $bike['notes'] = trim($_POST['notes'] ?? '');

  if (!validate_required($bike['frame_no'], 3)) $errors[] = "Numer ramy min. 3 znaki.";
  if (!validate_required($bike['model'], 2)) $errors[] = "Model min. 2 znaki.";
  // sprawdza czy typ jest jednym z dozwolonych
  if (!in_array($bike['type'], $types, true)) $errors[] = "Niepoprawny typ.";
  // sprawdza czy cena jest poprawną wartością pieniężną
  if (!validate_money($bike['hour_price'])) $errors[] = "Niepoprawna cena/h.";
  // sprawdza czy status jest jednym z dozwolonych
  if (!array_key_exists($bike['status'], $statuses)) $errors[] = "Niepoprawny status.";

  if (!$errors) {
    try {
      if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE bikes SET frame_no=?, model=?, type=?, hour_price=?, status=?, notes=? WHERE id=?");
        $stmt->execute([$bike['frame_no'], $bike['model'], $bike['type'], $bike['hour_price'], $bike['status'], $bike['notes'], $id]);
        log_action("Edycja roweru id=$id");
        flash('success', "Zapisano zmiany roweru.");
      } else {
        $stmt = $pdo->prepare("INSERT INTO bikes(frame_no, model, type, hour_price, status, notes) VALUES(?,?,?,?,?,?)");
        $stmt->execute([$bike['frame_no'], $bike['model'], $bike['type'], $bike['hour_price'], $bike['status'], $bike['notes']]);
        log_action("Dodano rower frame_no={$bike['frame_no']}");
        flash('success', "Dodano rower.");
      }
      header('Location: bikes_list.php');
      exit;
    } catch (PDOException $e) {
      $errors[] = "Numer ramy musi być unikalny (albo błąd bazy).";
    }
  }
}

$title = $id > 0 ? "Edycja roweru" : "Dodaj rower";
require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-3"><?= e($title) ?></h1>

<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0">
    <?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?>
  </ul></div>
<?php endif; ?>

<form method="post" class="card card-body" style="max-width:720px">
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Numer ramy</label>
      <input class="form-control" name="frame_no" value="<?= e($bike['frame_no']) ?>" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Model</label>
      <input class="form-control" name="model" value="<?= e($bike['model']) ?>" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Typ</label>
      <select class="form-select" name="type">
        <?php foreach ($types as $t): ?>
          <option value="<?= e($t) ?>" <?= $bike['type']===$t?'selected':'' ?>><?= e($t) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Cena za godzinę</label>
      <input class="form-control" name="hour_price" value="<?= e((string)$bike['hour_price']) ?>" placeholder="np. 12.50">
    </div>
    <div class="col-md-4">
      <label class="form-label">Status</label>
      <select class="form-select" name="status">
        <?php foreach ($statuses as $k=>$v): ?>
          <option value="<?= e($k) ?>" <?= $bike['status']===$k?'selected':'' ?>><?= e($v) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12">
      <label class="form-label">Notatki</label>
      <input class="form-control" name="notes" value="<?= e($bike['notes'] ?? '') ?>">
    </div>
  </div>

  <div class="mt-3 d-flex gap-2">
    <button class="btn btn-primary">Zapisz</button>
    <a class="btn btn-secondary" href="bikes_list.php">Anuluj</a>
  </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
