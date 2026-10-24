<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Database as DB;

final class Project
{
    public static function catalog(array $filters = []): array
    {
        $settings = SiteSettings::get();
        $where = ["p.status = 'published'", 'COALESCE(d.hidden, 0) = 0'];
        if (!$settings['show_demos']) {
            // demo identifica dados fictícios do seed, não o tipo comercial "Demo".
            $where[] = 'p.demo = 0';
        }
        $values = [];
        if (!empty($filters['q'])) {
            $where[] = '(p.title LIKE ? OR p.creator LIKE ?)';
            $values[] = '%' . $filters['q'] . '%';
            $values[] = '%' . $filters['q'] . '%';
        }
        foreach (['genre', 'type', 'platform', 'collection'] as $key) {
            if (!empty($filters[$key])) {
                $where[] = ($key === 'collection' ? "COALESCE(NULLIF(d.collection, ''), p.collection)" : "p.$key") . ' = ?';
                $values[] = $filters[$key];
            }
        }
        $order =
            ($filters['sort'] ?? '') === 'name'
                ? 'p.title COLLATE NOCASE'
                : (($filters['sort'] ?? '') === 'new'
                    ? 'p.id DESC'
                    : 'COALESCE(d.featured, 0) DESC, COALESCE(d.position, 100) ASC, ' . ($settings['automatic_order'] === 'name' ? 'p.title COLLATE NOCASE, p.id ASC' : 'p.id DESC'));
        return DB::all(
            "SELECT p.*, COALESCE(NULLIF(d.collection, ''), p.collection) AS collection FROM projects p LEFT JOIN project_display d ON d.project_id=p.id WHERE " . implode(' AND ', $where) . ' ORDER BY ' . $order,
            $values,
        );
    }
    public static function find(int $id): ?array
    {
        return DB::one('SELECT * FROM projects WHERE id = ?', [$id]);
    }
    public static function library(int $userId, bool $favorites = false): array
    {
        return DB::all(
            'SELECT p.*, l.favorite FROM projects p JOIN library l ON l.project_id = p.id WHERE l.user_id = ?' .
                ($favorites ? ' AND l.favorite = 1' : '') .
                ' ORDER BY l.created_at DESC',
            [$userId],
        );
    }
}
