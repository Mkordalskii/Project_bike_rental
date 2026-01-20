<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

logout_user();
log_action("Wylogowanie użytkownika");
flash('success', "Wylogowano.");
header('Location: login.php');
exit;
