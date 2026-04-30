<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

start_secure_session();
ensure_default_admin($pdo);
flash('success', 'Default admin account is ready. Username: admin, Password: admin123');

header('Location: login.php');
exit;
