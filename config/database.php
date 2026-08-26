<?php
/**
 * CargoFlow — PDO database connection (singleton)
 * ---------------------------------------------------------------------
 * Uses PDO with prepared statements and ERRMODE_EXCEPTION so every
 * query is safe from SQL injection and errors are surfaced consistently.
 */

if (!defined('DB_HOST')) {
    require_once __DIR__ . '/config.php';
}

/**
 * Return a shared PDO instance.
 *
 * @return PDO
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_STRINGIFY_FETCHES  => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // In production never leak credentials; show a friendly message.
        if (APP_DEBUG) {
            die('Database connection failed: ' . $e->getMessage());
        }
        http_response_code(500);
        die('Database connection failed. Please check your configuration in <code>config/config.php</code>.');
    }

    return $pdo;
}

/**
 * Run a prepared query and return the statement.
 *
 * @param string $sql
 * @param array  $params
 * @return PDOStatement
 */
function query(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch all rows.
 */
function fetchAll(string $sql, array $params = []): array
{
    return query($sql, $params)->fetchAll();
}

/**
 * Fetch a single row (or null).
 */
function fetchOne(string $sql, array $params = []): ?array
{
    $row = query($sql, $params)->fetch();
    return $row === false ? null : $row;
}

/**
 * Fetch a single scalar value.
 */
function fetchValue(string $sql, array $params = [])
{
    return query($sql, $params)->fetchColumn();
}

/**
 * Insert a row and return the new auto-increment id.
 */
function insertRow(string $table, array $data): int
{
    $columns = array_keys($data);
    $placeholders = array_map(fn($c) => ':' . $c, $columns);

    $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
    query($sql, $data);

    return (int) db()->lastInsertId();
}

/**
 * Update rows by id and return affected row count.
 */
function updateRow(string $table, int $id, array $data): int
{
    $sets = [];
    foreach ($data as $col => $val) {
        $sets[] = $col . ' = :' . $col;
    }
    $data['__id'] = $id;
    $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $sets) . ' WHERE id = :__id';
    return query($sql, $data)->rowCount();
}

/**
 * Delete a row by id.
 */
function deleteRow(string $table, int $id): int
{
    return query('DELETE FROM ' . $table . ' WHERE id = ?', [$id])->rowCount();
}
