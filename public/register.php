<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php'; //tylko dla start_session()

start_session();
$title = "Rejestracja";
$errors = [];
// sprawdza czy formularz został wysłany
if (is_post()) {
    $username = trim($_POST['username'] ?? '');
    $pass1 = (string)($_POST['password'] ?? '');
    $pass2 = (string)($_POST['password2'] ?? '');
// walidacja danych
    if (!validate_required($username, 3)) $errors[] = "Login min. 3 znaki.";
    if (!validate_required($pass1, 6)) $errors[] = "Hasło min. 6 znaków.";
    if ($pass1 !== $pass2) $errors[] = "Hasła nie są takie same.";
// jeśli brak błędów, tworzy użytkownika
    if (!$errors) {
        $hash = password_hash($pass1, PASSWORD_DEFAULT);
        try {
            $stmt = db()->prepare("INSERT INTO users(username, password_hash) VALUES(?, ?)");
            $stmt->execute([$username, $hash]);
            log_action("Rejestracja użytkownika: $username");
            flash('success', "Konto utworzone. Zaloguj się.");
            header('Location: login.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = "Login jest zajęty lub błąd bazy.";
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-3">Rejestracja</h1>

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
  <div class="mb-3">
    <label class="form-label">Powtórz hasło</label>
    <input class="form-control" type="password" name="password2" required>
  </div>
  <button class="btn btn-primary">Utwórz konto</button>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
