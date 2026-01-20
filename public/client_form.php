<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();
$pdo = db();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$client = [
    'first_name' => '',
    'last_name'  => '',
    'phone'      => '',
    'email'      => '',
];

$errors = [];

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row) {
        $client = [
            'first_name' => (string)$row['first_name'],
            'last_name'  => (string)$row['last_name'],
            'phone'      => (string)($row['phone'] ?? ''),
            'email'      => (string)($row['email'] ?? ''),
        ];
    }
}

if (is_post()) {
    $client['first_name'] = trim($_POST['first_name'] ?? '');
    $client['last_name']  = trim($_POST['last_name'] ?? '');
    $client['phone']      = trim($_POST['phone'] ?? '');
    $client['email']      = trim($_POST['email'] ?? '');

    if (!validate_required($client['first_name'], 2)) $errors[] = "Imię min. 2 znaki.";
    if (!validate_required($client['last_name'], 2))  $errors[] = "Nazwisko min. 2 znaki.";
    if (!validate_email_optional($client['email']))   $errors[] = "Niepoprawny email.";

    // Prosta walidacja telefonu (opcjonalnie)
    if ($client['phone'] !== '' && mb_strlen($client['phone']) < 5) {
        $errors[] = "Telefon wygląda na zbyt krótki.";
    }

    if (!$errors) {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE clients SET first_name=?, last_name=?, phone=?, email=? WHERE id=?");
            $stmt->execute([$client['first_name'], $client['last_name'], $client['phone'], $client['email'], $id]);
            log_action("Edycja klienta id=$id");
            flash('success', "Zapisano zmiany klienta.");
        } else {
            $stmt = $pdo->prepare("INSERT INTO clients(first_name, last_name, phone, email) VALUES(?,?,?,?)");
            $stmt->execute([$client['first_name'], $client['last_name'], $client['phone'], $client['email']]);
            log_action("Dodano klienta {$client['first_name']} {$client['last_name']}");
            flash('success', "Dodano klienta.");
        }
        header('Location: clients_list.php');
        exit;
    }
}

$title = $id > 0 ? "Edycja klienta" : "Dodaj klienta";
require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-3"><?= e($title) ?></h1>

<?php if ($errors): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" class="card card-body" style="max-width:720px">
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Imię</label>
      <input class="form-control" name="first_name" value="<?= e($client['first_name']) ?>" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Nazwisko</label>
      <input class="form-control" name="last_name" value="<?= e($client['last_name']) ?>" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Telefon (opcjonalnie)</label>
      <input class="form-control" name="phone" value="<?= e($client['phone']) ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Email (opcjonalnie)</label>
      <input class="form-control" name="email" value="<?= e($client['email']) ?>">
    </div>
  </div>

  <div class="mt-3 d-flex gap-2">
    <button class="btn btn-primary">Zapisz</button>
    <a class="btn btn-secondary" href="clients_list.php">Anuluj</a>
  </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
