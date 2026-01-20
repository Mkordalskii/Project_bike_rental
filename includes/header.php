<?php
declare(strict_types=1); // włącza ścisłe typowanie
require_once __DIR__ . '/functions.php'; // dołącza plik z funkcjami pomocniczymi
require_once __DIR__ . '/auth.php'; // dołącza plik z funkcjami autoryzacji

$user = current_user(); // pobiera aktualnie zalogowanego użytkownika lub null
?>
<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'Wypożyczalnia rowerów') ?></title> <!-- ustawia tytuł strony -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-dark navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="index.php">BikeRent</a>
    <div class="d-flex gap-2">
      <?php if ($user): ?>
        <a class="btn btn-outline-light btn-sm" href="bikes_list.php">Rowery</a>
        <a class="btn btn-outline-light btn-sm" href="clients_list.php">Klienci</a>
        <a class="btn btn-outline-light btn-sm" href="rentals_list.php">Wypożyczenia</a>
        <span class="navbar-text text-light ms-2">Zalogowany: <?= e($user['username']) ?></span>
        <a class="btn btn-warning btn-sm ms-2" href="logout.php">Wyloguj</a>
      <?php else: ?>
        <a class="btn btn-outline-light btn-sm" href="login.php">Logowanie</a>
        <a class="btn btn-outline-light btn-sm" href="register.php">Rejestracja</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<main class="container my-4">
  <?php if ($m = flash('success')): ?>
    <div class="alert alert-success"><?= e($m) ?></div>
  <?php endif; ?>
  <?php if ($m = flash('error')): ?>
    <div class="alert alert-danger"><?= e($m) ?></div>
  <?php endif; ?>
