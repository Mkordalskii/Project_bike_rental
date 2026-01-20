<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$user = require_login();
$pdo = db();

$title = "Nowe wypożyczenie";
$errors = [];

// Lista klientów
$clients = $pdo->query("SELECT id, first_name, last_name FROM clients ORDER BY last_name, first_name")->fetchAll();

// Lista dostępnych rowerów (ważne wymaganie!)
$bikes = $pdo->query("SELECT id, frame_no, model, hour_price FROM bikes WHERE status='available' ORDER BY id DESC")->fetchAll();

$data = [
  'client_id' => '',
  'bike_id' => '',
  'deposit' => '0.00',
];

if (is_post()) {
    $data['client_id'] = trim($_POST['client_id'] ?? '');
    $data['bike_id']   = trim($_POST['bike_id'] ?? '');
    $data['deposit']   = trim($_POST['deposit'] ?? '0');

    if (!ctype_digit($data['client_id'])) $errors[] = "Wybierz klienta.";
    if (!ctype_digit($data['bike_id']))   $errors[] = "Wybierz rower.";
    if (!validate_money($data['deposit'])) $errors[] = "Niepoprawna kaucja.";

    // Dodatkowa kontrola: czy rower na pewno dostępny (nawet jeśli ktoś otworzył stronę wcześniej)
    if (!$errors) {
        $stmt = $pdo->prepare("SELECT id FROM bikes WHERE id=? AND status='available'");
        $stmt->execute([(int)$data['bike_id']]);
        if (!$stmt->fetch()) {
            $errors[] = "Wybrany rower nie jest już dostępny.";
        }
    }

    if (!$errors) {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("
                INSERT INTO rentals(bike_id, client_id, user_id, start_at, deposit, status)
                VALUES(?,?,?,?,?, 'active')
            ");
            $stmt->execute([
                (int)$data['bike_id'],
                (int)$data['client_id'],
                (int)$user['id'],
                date('Y-m-d H:i:s'),
                $data['deposit']
            ]);

            // Zmiana statusu roweru na rented
            $pdo->prepare("UPDATE bikes SET status='rented' WHERE id=?")->execute([(int)$data['bike_id']]);

            $pdo->commit();

            log_action("Nowe wypożyczenie: bike_id={$data['bike_id']} client_id={$data['client_id']} user_id={$user['id']}");
            flash('success', "Utworzono wypożyczenie.");
            header('Location: rentals_list.php');
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = "Błąd zapisu wypożyczenia.";
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-3">Nowe wypożyczenie</h1>

<?php if ($errors): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php if (!$clients): ?>
  <div class="alert alert-warning">
    Brak klientów w bazie. Najpierw dodaj klienta.
  </div>
<?php endif; ?>

<?php if (!$bikes): ?>
  <div class="alert alert-warning">
    Brak dostępnych rowerów do wypożyczenia.
  </div>
<?php endif; ?>

<form method="post" class="card card-body" style="max-width:820px">
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Klient</label>
      <select class="form-select" name="client_id" required <?= !$clients ? 'disabled' : '' ?>>
        <option value="">-- wybierz --</option>
        <?php foreach ($clients as $c): ?>
          <option value="<?= e((string)$c['id']) ?>" <?= $data['client_id']===(string)$c['id']?'selected':'' ?>>
            <?= e($c['last_name'].' '.$c['first_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-6">
      <label class="form-label">Rower (tylko dostępne)</label>
      <select class="form-select" name="bike_id" required <?= !$bikes ? 'disabled' : '' ?>>
        <option value="">-- wybierz --</option>
        <?php foreach ($bikes as $b): ?>
          <option value="<?= e((string)$b['id']) ?>" <?= $data['bike_id']===(string)$b['id']?'selected':'' ?>>
            <?= e($b['frame_no'].' — '.$b['model'].' ('.$b['hour_price'].'/h)') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-4">
      <label class="form-label">Kaucja</label>
      <input class="form-control" name="deposit" value="<?= e($data['deposit']) ?>" placeholder="np. 50.00">
    </div>
  </div>

  <div class="mt-3 d-flex gap-2">
    <button class="btn btn-primary" <?= (!$clients || !$bikes) ? 'disabled' : '' ?>>Utwórz</button>
    <a class="btn btn-secondary" href="rentals_list.php">Anuluj</a>
  </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
