<?php
// api.php - الـ Backend: تسجيل الدخول + إدارة الطلاب + المكتبة (يتطلب PHP 8+)
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

function out($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function need($role) {
    if (($_SESSION['role'] ?? '') !== $role) out(['ok' => false, 'msg' => 'غير مصرح'], 403);
}

// بيانات الاتصال في config.php (انسخه من config.sample.php)
if (!is_file(__DIR__ . '/config.php')) out(['ok' => false, 'msg' => 'ملف config.php مش موجود. انسخ config.sample.php باسم config.php وعدّل بياناتك.'], 500);
$cfg = require __DIR__ . '/config.php';
try {
    $pdo = new PDO("mysql:host={$cfg['host']};dbname={$cfg['db']};charset=utf8mb4", $cfg['user'], $cfg['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    out(['ok' => false, 'msg' => 'تعذر الاتصال بقاعدة البيانات. راجع بيانات config.php'], 500);
}

$a  = $_GET['action'] ?? '';
$in = json_decode(file_get_contents('php://input'), true) ?: [];

// يقبل bcrypt، ولو الباسورد القديم نص عادي يقبله ثم يحوّله لـ bcrypt تلقائيًا
function check($plain, $stored) {
    return str_starts_with($stored, '$2y$') ? password_verify($plain, $stored) : hash_equals($stored, $plain);
}

function has_paid($pdo) {
    $s = $pdo->prepare('SELECT Paid FROM Students WHERE StudentID = ?');
    $s->execute([$_SESSION['id']]);
    return (bool)$s->fetchColumn();
}

switch ($a) {
case 'login':
    $role = ($in['role'] ?? '') === 'admin' ? 'admin' : 'student';
    $t  = $role === 'admin' ? 'Admins' : 'Students';
    $id = $role === 'admin' ? 'AdminID' : 'StudentID';
    $s = $pdo->prepare("SELECT * FROM $t WHERE Username = ?");
    $s->execute([trim($in['username'] ?? '')]);
    $u = $s->fetch();
    $pw = (string)($in['password'] ?? '');
    if (!$u || !check($pw, $u['PasswordHash'])) out(['ok' => false, 'msg' => 'اسم المستخدم أو كلمة السر غير صحيحة'], 401);
    if (!str_starts_with($u['PasswordHash'], '$2y$'))
        $pdo->prepare("UPDATE $t SET PasswordHash = ? WHERE $id = ?")->execute([password_hash($pw, PASSWORD_DEFAULT), $u[$id]]);
    session_regenerate_id(true);
    $_SESSION = ['role' => $role, 'id' => $u[$id], 'name' => $u['FullName']];
    out(['ok' => true, 'role' => $role]);

case 'logout':
    session_destroy();
    out(['ok' => true]);

case 'me':
    if (empty($_SESSION['role'])) out(['ok' => false], 401);
    $me = ['ok' => true, 'role' => $_SESSION['role'], 'name' => $_SESSION['name']];
    if ($_SESSION['role'] === 'student') {
        $s = $pdo->prepare('SELECT StudentID, FullName, Username, Department, CreatedAt, Paid FROM Students WHERE StudentID = ?');
        $s->execute([$_SESSION['id']]);
        $me['student'] = $s->fetch();
    }
    out($me);

case 'list':
    need('admin');
    out(['ok' => true, 'data' => $pdo->query('SELECT StudentID, FullName, Username, Department, Paid, PaidAt FROM Students ORDER BY StudentID')->fetchAll()]);

case 'add':
    need('admin');
    $n = trim($in['name'] ?? ''); $u = trim($in['username'] ?? ''); $p = (string)($in['password'] ?? ''); $d = $in['dept'] ?? 'عام';
    if ($n === '' || !preg_match('/^[A-Za-z0-9_.]{3,50}$/', $u) || strlen($p) < 6)
        out(['ok' => false, 'msg' => 'بيانات غير صحيحة (كلمة السر 6 أحرف على الأقل)'], 422);
    $max = $pdo->query('SELECT MAX(CAST(StudentID AS UNSIGNED)) FROM Students')->fetchColumn();
    $id  = (string)($max ? $max + 1 : 2024030001);
    try {
        $pdo->prepare('INSERT INTO Students (StudentID, FullName, Username, PasswordHash, Department, Paid, PaidAt) VALUES (?,?,?,?,?,?,?)')
            ->execute([$id, $n, $u, password_hash($p, PASSWORD_DEFAULT), $d, (int)!empty($in['paid']), !empty($in['paid']) ? date('Y-m-d H:i:s') : null]);
    } catch (PDOException $e) {
        out(['ok' => false, 'msg' => 'اسم المستخدم مستخدم بالفعل'], 409);
    }
    out(['ok' => true, 'id' => $id]);

case 'delete':
    need('admin');
    $pdo->prepare('DELETE FROM Students WHERE StudentID = ?')->execute([$in['id'] ?? '']);
    out(['ok' => true]);

case 'password':
    need('student');
    $s = $pdo->prepare('SELECT PasswordHash FROM Students WHERE StudentID = ?');
    $s->execute([$_SESSION['id']]);
    if (!check((string)($in['old'] ?? ''), $s->fetchColumn())) out(['ok' => false, 'msg' => 'كلمة السر الحالية غير صحيحة'], 422);
    if (strlen((string)($in['new'] ?? '')) < 6) out(['ok' => false, 'msg' => 'كلمة السر الجديدة 6 أحرف على الأقل'], 422);
    $pdo->prepare('UPDATE Students SET PasswordHash = ? WHERE StudentID = ?')->execute([password_hash($in['new'], PASSWORD_DEFAULT), $_SESSION['id']]);
    out(['ok' => true]);

case 'export':
    need('admin');
    header('Content-Type: text/plain; charset=utf-8');
    if (isset($_GET['dl'])) header('Content-Disposition: attachment; filename="cairo_institute_students.sql"');
    echo "-- Cairo Institute DB export - " . date('Y-m-d') . "\nUSE CairoInstituteDB;\n\n";
    $rows = $pdo->query('SELECT StudentID, FullName, Username, PasswordHash, Department, Paid, PaidAt FROM Students ORDER BY StudentID')->fetchAll(PDO::FETCH_NUM);
    echo "INSERT INTO Students (StudentID, FullName, Username, PasswordHash, Department, Paid, PaidAt) VALUES\n";
    echo implode(",\n", array_map(fn($r) => '(' . implode(', ', array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote($v), $r)) . ')', $rows)) . ";\n";
    exit;

case 'pay':
    need('admin');
    $paid = !empty($in['paid']);
    $pdo->prepare('UPDATE Students SET Paid = ?, PaidAt = ? WHERE StudentID = ?')->execute([(int)$paid, $paid ? date('Y-m-d H:i:s') : null, $in['id'] ?? '']);
    out(['ok' => true]);

case 'books':   // الأدمن يشوف الكل، والطالب بس لو دافع (بيتفحص من الداتا بيز كل مرة)
    if (empty($_SESSION['role'])) out(['ok' => false], 401);
    if ($_SESSION['role'] === 'student' && !has_paid($pdo))
        out(['ok' => false, 'locked' => true, 'msg' => 'يجب سداد المصروفات للدخول إلى المكتبة'], 402);
    out(['ok' => true, 'data' => $pdo->query('SELECT BookID, Title, Author, Category, (FileName IS NOT NULL) AS Local FROM Books ORDER BY BookID DESC')->fetchAll()]);

case 'book_add':
    need('admin');
    $t = trim($_POST['title'] ?? ''); $au = trim($_POST['author'] ?? ''); $c = trim($_POST['category'] ?? '') ?: 'عام'; $url = trim($_POST['url'] ?? ''); $fn = null;
    if ($t === '') out(['ok' => false, 'msg' => 'اسم الكتاب مطلوب'], 422);
    if (!empty($_FILES['file']['tmp_name'])) {
        $tmp = $_FILES['file']['tmp_name'];
        if (strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION)) !== 'pdf' || file_get_contents($tmp, false, null, 0, 4) !== '%PDF')
            out(['ok' => false, 'msg' => 'الملف لازم يكون PDF'], 422);
        @mkdir(__DIR__ . '/books', 0755, true);
        $fn = bin2hex(random_bytes(8)) . '.pdf';
        if (!move_uploaded_file($tmp, __DIR__ . '/books/' . $fn)) out(['ok' => false, 'msg' => 'فشل رفع الملف'], 500);
    } elseif (!preg_match('~^https?://~i', $url)) {
        out(['ok' => false, 'msg' => 'ارفع ملف PDF أو اكتب رابط صحيح (أقصى حجم رفع حسب إعدادات PHP)'], 422);
    }
    $pdo->prepare('INSERT INTO Books (Title, Author, Category, FileName, Url) VALUES (?,?,?,?,?)')->execute([$t, $au, $c, $fn, $fn ? null : $url]);
    out(['ok' => true]);

case 'book_delete':
    need('admin');
    $s = $pdo->prepare('SELECT FileName FROM Books WHERE BookID = ?'); $s->execute([$in['id'] ?? 0]);
    if ($f = $s->fetchColumn()) @unlink(__DIR__ . '/books/' . basename($f));
    $pdo->prepare('DELETE FROM Books WHERE BookID = ?')->execute([$in['id'] ?? 0]);
    out(['ok' => true]);

case 'read':    // الطريق الوحيد لفتح الكتاب، ومحمي بالسداد
    if (empty($_SESSION['role'])) { http_response_code(401); exit('سجّل الدخول أولًا'); }
    if ($_SESSION['role'] === 'student' && !has_paid($pdo)) { http_response_code(402); exit('لازم تسدد المصروفات علشان تقرأ الكتب'); }
    $s = $pdo->prepare('SELECT FileName, Url FROM Books WHERE BookID = ?'); $s->execute([$_GET['id'] ?? 0]);
    $b = $s->fetch();
    if (!$b) { http_response_code(404); exit('الكتاب غير موجود'); }
    if ($b['Url']) { header('Location: ' . $b['Url']); exit; }
    $p = __DIR__ . '/books/' . basename($b['FileName']);
    if (!is_file($p)) { http_response_code(404); exit('الملف غير موجود'); }
    header('Content-Type: application/pdf'); header('Content-Disposition: inline; filename="book.pdf"');
    header('X-Content-Type-Options: nosniff'); header('Content-Length: ' . filesize($p));
    readfile($p); exit;

default:
    out(['ok' => false, 'msg' => 'طلب غير معروف'], 400);
}
