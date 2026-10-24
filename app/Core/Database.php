<?php
declare(strict_types=1);
namespace App\Core;

use PDO;

final class Database
{
    private static PDO $pdo;
    public static string $storage;

    public static function connect(string $storage): void
    {
        self::$storage = $storage;
        if (!is_dir($storage)) {
            mkdir($storage, 0700, true);
        }
        if (!is_dir($storage . '/builds')) {
            mkdir($storage . '/builds', 0700, true);
        }
        self::$pdo = new PDO('sqlite:' . $storage . '/pixelhub.sqlite', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        self::$pdo->exec('PRAGMA foreign_keys = ON; PRAGMA busy_timeout = 5000; PRAGMA journal_mode = WAL;');
        self::$pdo->exec(file_get_contents(ROOT . '/database/schema.sql'));
        if (!self::one('SELECT id FROM projects LIMIT 1')) {
            self::$pdo->beginTransaction();
            try {
                require ROOT . '/database/seed.php';
                self::$pdo->commit();
            } catch (\Throwable $e) {
                self::$pdo->rollBack();
                throw $e;
            }
        }
        require ROOT . '/database/achievements.php';
    }
    public static function run(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    public static function one(string $sql, array $params = []): ?array
    {
        return self::run($sql, $params)->fetch() ?: null;
    }
    public static function all(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }
    public static function id(): int
    {
        return (int) self::$pdo->lastInsertId();
    }
}
