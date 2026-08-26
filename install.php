<?php
/**
 * CargoFlow — Web installer
 * ---------------------------------------------------------------------
 * One-time setup helper. Checks the PHP environment, connects to MySQL
 * using the credentials in config/config.php, runs database/schema.sql
 * and verifies the admin login.
 *
 * DELETE this file after successful installation.
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
session_start();

require_once __DIR__ . '/config/config.php';

$checks = [];
$results = [];
$installed = false;

function check(string $name, bool $ok, string $detail = ''): void
{
    global $checks;
    $checks[] = ['name' => $name, 'ok' => $ok, 'detail' => $detail];
}

// 1. PHP version
check('PHP version >= 7.4', version_compare(PHP_VERSION, '7.4.0', '>='), 'Running PHP ' . PHP_VERSION);

// 2. Extensions
foreach (['pdo', 'pdo_mysql', 'mbstring', 'json'] as $ext) {
    check("Extension $ext", extension_loaded($ext));
}

// 3. Writable config
check('config/config.php is readable', is_readable(__DIR__ . '/config/config.php'));

$dbOk = false;
$connectionError = '';

// 4. Database connection
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $dbOk = true;
    check('MySQL connection', true, DB_HOST . '/' . DB_NAME);
} catch (PDOException $e) {
    $connectionError = $e->getMessage();
    check('MySQL connection', false, 'Check DB_HOST/DB_NAME/DB_USER/DB_PASS in config/config.php');
}

// 5. Auto-import schema if requested
$schemaImported = false;
if ($dbOk && ($_POST['action'] ?? '') === 'import' && isset($_POST['confirm_import'])) {
    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    if ($sql === false) {
        check('Schema file readable', false, 'database/schema.sql missing');
    } else {
        try {
            // Strip comment lines and inline comments, then split on ";".
            $lines = preg_split('/\R/', $sql);
            $clean = [];
            foreach ($lines as $line) {
                $line = preg_replace('/--.*$/', '', $line); // inline "--" comments
                $line = trim($line);
                if ($line === '' || strncmp($line, '--', 2) === 0 || $line[0] === '#' || strncmp($line, '/*', 2) === 0) {
                    continue;
                }
                $clean[] = $line;
            }
            $cleanedSql = implode("\n", $clean);

            // Split safely (statements end with ";").
            $statements = array_filter(array_map('trim', explode(';', $cleanedSql)));

            $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
            foreach ($statements as $statement) {
                if ($statement === '') {
                    continue;
                }
                $pdo->exec($statement);
            }
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
            $schemaImported = true;
            check('Schema imported', true, 'database/schema.sql executed');
        } catch (PDOException $e) {
            check('Schema imported', false, $e->getMessage());
        }
    }
}

// 6. Verify tables / admin login
if ($dbOk) {
    try {
        $hasUsers = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'users'")->fetchColumn();
        check('Required tables exist', $hasUsers > 0, $hasUsers ? 'Found core tables' : 'Run schema import');
        if ($hasUsers) {
            $admin = $pdo->query("SELECT username FROM users WHERE role = 'admin' LIMIT 1")->fetchColumn();
            check('Admin account present', (bool) $admin, $admin ? "Admin: $admin (password admin123)" : 'No admin user found');
        }
    } catch (PDOException $e) {
        check('Table verification', false, $e->getMessage());
    }
}

$installed = $dbOk && $schemaImported;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CargoFlow Installer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{background:#0f172a;color:#e2e8f0}.card{background:#1e293b;border:1px solid #334155}</style>
</head>
<body>
<div class="container py-5" style="max-width:760px">
    <div class="text-center mb-4">
        <h1 class="fw-bold">CargoFlow <span class="text-primary">Installer</span></h1>
        <p class="text-muted">Environment check &amp; database setup</p>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <h5 class="mb-3">System checks</h5>
            <ul class="list-unstyled mb-0">
                <?php foreach ($checks as $c): ?>
                <li class="d-flex justify-content-between py-2 border-bottom border-secondary">
                    <span><?= htmlspecialchars($c['name']) ?></span>
                    <span class="<?= $c['ok'] ? 'text-success' : 'text-danger' ?>">
                        <?= $c['ok'] ? '&#10003; Pass' : '&#10007; ' . htmlspecialchars($c['detail']) ?>
                    </span>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php if (!$dbOk && $connectionError): ?>
                <div class="alert alert-danger mt-3 mb-0"><strong>Connection error:</strong> <?= htmlspecialchars($connectionError) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($dbOk && !$schemaImported): ?>
    <div class="card shadow mb-4">
        <div class="card-body">
            <h5>Import database schema</h5>
            <p class="text-muted mb-3">This will create all CargoFlow tables and insert demo data (customers, shipments, invoices, admin user).</p>
            <form method="post">
                <input type="hidden" name="action" value="import">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="confirm_import" name="confirm_import" value="1" required>
                    <label class="form-check-label" for="confirm_import">I understand this will drop and recreate existing CargoFlow tables.</label>
                </div>
                <button class="btn btn-primary" type="submit">Import schema &amp; seed data</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($installed): ?>
        <div class="alert alert-success">
            <strong>Installation complete!</strong> You can now
            <a href="index.php" class="alert-link">view the public site</a> or
            <a href="login.php" class="alert-link">log into the admin</a> (admin / admin123).
            <br><strong>Important:</strong> delete <code>install.php</code> for security.
        </div>
    <?php endif; ?>

    <p class="text-center text-muted small">CargoFlow &middot; cPanel-ready PHP + MySQL application</p>
</div>
</body>
</html>
