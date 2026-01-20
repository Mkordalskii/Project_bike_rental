<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

start_session();
$title = "Logowanie";
$errors = [];

if (is_post()) {
    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if (!validate_required($username, 1)) $errors[] = "Podaj login.";
    if (!validate_required($password, 1)) $errors[] = "Podaj hasło.";

    if (!$errors) {
        $stmt = db()->prepare("SELECT id, password_hash FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $u = $stmt->fetch();

        if ($u && password_verify($password, $u['password_hash'])) {
            login_user((int)$u['id']);
            log_action("Logowanie: $username");
            flash('success', "Zalogowano.");
            header('Location: index.php');
            exit;
        }
        $errors[] = "Błędny login lub hasło.";
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-3">Logowanie</h1>

<?php if ($errors): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" class="card card-body" style="max-width:520px">
  <div class="mb-3">
    <label class="form-label">Login</label>
    <input class="form-control" name="username" value="<?= e($_POST['username'] ?? '') ?>" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Hasło</label>
    <input class="form-control" type="password" name="password" required>
  </div>
  <button class="btn btn-dark">Zaloguj</button>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
