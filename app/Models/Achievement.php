<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Database as DB;

final class Achievement
{
    public static function unlock(int $userId, ?int $projectId, string $key, string $source): ?string
    {
        $achievement = DB::one('SELECT * FROM achievements WHERE project_id IS ? AND key=?', [
            $projectId,
            $key,
        ]);
        if (!$achievement) {
            return null;
        }
        $insert = DB::run('INSERT OR IGNORE INTO unlocks(user_id,achievement_id,source) VALUES (?,?,?)', [
            $userId,
            $achievement['id'],
            $source,
        ]);
        return $insert->rowCount() ? $achievement['title'] : null;
    }
    public static function syncLibrary(int $userId): void
    {
        $counts = DB::one(
            'SELECT COUNT(*) AS total, COALESCE(SUM(favorite),0) AS favorites FROM library WHERE user_id=?',
            [$userId],
        );
        if ($counts['total'] >= 1) {
            self::unlock($userId, null, 'first-world', 'platform');
        }
        if ($counts['total'] >= 3) {
            self::unlock($userId, null, 'collector', 'platform');
        }
        if ($counts['favorites'] >= 1) {
            self::unlock($userId, null, 'favorite', 'platform');
        }
    }
    public static function forUser(int $userId): array
    {
        return DB::all(
            "SELECT a.*, p.title AS game_title, p.art, u.created_at AS unlocked_at, u.source FROM achievements a LEFT JOIN projects p ON p.id=a.project_id LEFT JOIN unlocks u ON u.achievement_id=a.id AND u.user_id=? WHERE a.project_id IS NULL OR p.status='published' OR u.user_id IS NOT NULL ORDER BY a.project_id IS NOT NULL, a.project_id, a.id",
            [$userId],
        );
    }
}
