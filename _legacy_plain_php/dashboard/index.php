<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

start_secure_session();
require_login();

$flash = consume_flash();
$search = trim((string) ($_GET['search'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 8;
$offset = ($page - 1) * $perPage;

$where = '';
$params = [];

if ($search !== '') {
    $where = 'WHERE fullname LIKE :search_name OR email LIKE :search_email OR course LIKE :search_course';
    $params[':search_name'] = '%' . $search . '%';
    $params[':search_email'] = '%' . $search . '%';
    $params[':search_course'] = '%' . $search . '%';
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM students {$where}");
$countStmt->execute($params);
$totalStudents = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalStudents / $perPage));

if ($page > $totalPages) {
    $query = http_build_query(['search' => $search, 'page' => $totalPages]);
    header('Location: index.php?' . $query);
    exit;
}

$studentsStmt = $pdo->prepare("SELECT id, fullname, email, course, created_at FROM students {$where} ORDER BY id DESC LIMIT :limit OFFSET :offset");
foreach ($params as $key => $value) {
    $studentsStmt->bindValue($key, $value, PDO::PARAM_STR);
}
$studentsStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$studentsStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$studentsStmt->execute();
$students = $studentsStmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | Student Record System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="app-layout">
        <aside class="sidebar glass-panel">
            <div class="sidebar-brand">
                <span class="brand-mark small">SR</span>
                <span>School DB</span>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php" class="active"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
                <a href="../auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </nav>
        </aside>

        <main class="dashboard-main">
            <header class="topbar">
                <div>
                    <p class="eyebrow">Admin Console</p>
                    <h1>Student Records</h1>
                </div>
                <button type="button" class="btn btn-neon" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                    <i class="bi bi-plus-lg"></i> Add Student
                </button>
            </header>

            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
                    <?= e($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <section class="stats-grid">
                <div class="stat-card glass-card">
                    <span>Total Students</span>
                    <strong><?= e((string) $totalStudents) ?></strong>
                </div>
                <div class="stat-card glass-card">
                    <span>Current Page</span>
                    <strong><?= e((string) $page) ?> / <?= e((string) $totalPages) ?></strong>
                </div>
            </section>

            <section class="table-shell glass-card">
                <div class="table-toolbar">
                    <form method="get" class="search-form">
                        <i class="bi bi-search"></i>
                        <input type="search" name="search" value="<?= e($search) ?>" class="form-control" placeholder="Search by name, email, or course">
                    </form>
                    <?php if ($search !== ''): ?>
                        <a href="index.php" class="btn btn-outline-light btn-sm">Clear</a>
                    <?php endif; ?>
                </div>

                <?php if (!$students): ?>
                    <div class="empty-state">
                        <i class="bi bi-person-lines-fill"></i>
                        <h2>No students found</h2>
                        <p>Add a new student or adjust your search filter.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle record-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Course</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td>#<?= e((string) $student['id']) ?></td>
                                        <td><?= e($student['fullname']) ?></td>
                                        <td><?= e($student['email']) ?></td>
                                        <td><?= e($student['course']) ?></td>
                                        <td>
                                            <div class="action-row">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-edit"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editStudentModal"
                                                    data-id="<?= e((string) $student['id']) ?>"
                                                    data-fullname="<?= e($student['fullname']) ?>"
                                                    data-email="<?= e($student['email']) ?>"
                                                    data-course="<?= e($student['course']) ?>"
                                                >
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </button>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-delete"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteStudentModal"
                                                    data-id="<?= e((string) $student['id']) ?>"
                                                    data-name="<?= e($student['fullname']) ?>"
                                                >
                                                    <i class="bi bi-trash3"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <nav aria-label="Student pagination" class="pagination-wrap">
                        <ul class="pagination pagination-sm mb-0">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="index.php?<?= e(http_build_query(['search' => $search, 'page' => $i])) ?>"><?= e((string) $i) ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <div class="modal fade" id="addStudentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="post" action="add_student.php" class="modal-content glass-modal">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <div class="modal-header">
                    <h2 class="modal-title fs-5">Add Student</h2>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="add_fullname">Full Name</label>
                        <input class="form-control" id="add_fullname" name="fullname" maxlength="100" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="add_email">Email</label>
                        <input type="email" class="form-control" id="add_email" name="email" maxlength="100" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="add_course">Course</label>
                        <input class="form-control" id="add_course" name="course" maxlength="100" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-neon">Save Student</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editStudentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="post" action="edit_student.php" class="modal-content glass-modal">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h2 class="modal-title fs-5">Edit Student</h2>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="edit_fullname">Full Name</label>
                        <input class="form-control" id="edit_fullname" name="fullname" maxlength="100" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="edit_email">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" maxlength="100" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="edit_course">Course</label>
                        <input class="form-control" id="edit_course" name="course" maxlength="100" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-neon">Update Student</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="deleteStudentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="post" action="delete_student.php" class="modal-content glass-modal">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" id="delete_id">
                <div class="modal-header">
                    <h2 class="modal-title fs-5">Delete Student</h2>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Delete <strong id="delete_name"></strong>? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-delete">Delete</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
