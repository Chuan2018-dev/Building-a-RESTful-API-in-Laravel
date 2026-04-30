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
$fullname = trim((string) ($_POST['fullname'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$course = trim((string) ($_POST['course'] ?? ''));

if ($id <= 0 || $fullname === '' || $email === '' || $course === '') {
    flash('danger', 'All fields are required.');
    header('Location: index.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('danger', 'Enter a valid email address.');
    header('Location: index.php');
    exit;
}

try {
    $stmt = $pdo->prepare('UPDATE students SET fullname = :fullname, email = :email, course = :course WHERE id = :id');
    $stmt->execute([
        ':fullname' => $fullname,
        ':email' => $email,
        ':course' => $course,
        ':id' => $id,
    ]);

    flash('success', $stmt->rowCount() > 0 ? 'Student updated successfully.' : 'No changes were made.');
} catch (PDOException $exception) {
    $message = $exception->getCode() === '23000' ? 'That email address already exists.' : 'Unable to update student.';
    flash('danger', $message);
}

header('Location: index.php');
exit;
