<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

start_secure_session();
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    flash('danger', 'Invalid request token.');
    header('Location: index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    flash('danger', 'Invalid student selected.');
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('DELETE FROM students WHERE id = :id');
$stmt->execute([':id' => $id]);

flash($stmt->rowCount() > 0 ? 'success' : 'warning', $stmt->rowCount() > 0 ? 'Student deleted successfully.' : 'Student was not found.');

header('Location: index.php');
exit;
