<?php
declare(strict_types=1);

use App\Core\Database;

// Todo conteúdo dinâmico passa por escape no momento de renderizar HTML.
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function url(string $page = 'discover', array $params = []): string
{
    if ($page === 'admin') return 'caba%C3%A7o.php' . ($params ? '?' . http_build_query($params) : '');
    return 'index.php?' . http_build_query(['page' => $page] + $params);
}
function asset(string $path): string
{
    $public =
        realpath(dirname($_SERVER['SCRIPT_FILENAME'] ?? ROOT . '/index.php')) === realpath(ROOT . '/public');
    return ($public ? '' : 'public/') . 'assets/' . $path;
}
function redirect(string $page, array $params = []): never
{
    header('Location: ' . url($page, $params));
    exit();
}
function csrf(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf()) . '">';
}
function user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return Database::one('SELECT id, name, email, bio, role, created_at FROM users WHERE id = ?', [
        $_SESSION['user_id'],
    ]);
}
function require_user(): array
{
    $u = user();
    if (!$u) {
        flash('Entre na sua conta para continuar.');
        redirect('login');
    }
    return $u;
}
function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}
function render(string $view, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $currentUser = user();
    $page = (string) ($_GET['page'] ?? 'discover');
    require ROOT . '/views/layout.php';
}
function icon(string $name, int $size = 20): string
{
    $paths = [
        'trophy' =>
            '<path d="M8 3h8v6a4 4 0 0 1-8 0ZM8 5H4v3a4 4 0 0 0 4 4m8-7h4v3a4 4 0 0 1-4 4m-4 1v5m-5 3h10M9 18h6v3"/>',
        'compass' => '<circle cx="12" cy="12" r="9"/><path d="m16 8-2.5 5.5L8 16l2.5-5.5Z"/>',
        'grid' =>
            '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'library' => '<path d="M4 4v16M9 4v16M14 4v16m3-15 4 14"/>',
        'heart' => '<path d="M20 5c-3-3-6-1-8 1-2-2-5-4-8-1s-1 7 8 14c9-7 11-11 8-14Z"/>',
        'code' => '<path d="m8 6-6 6 6 6m8-12 6 6-6 6m-3-14-2 16"/>',
        'arrow' => '<path d="M4 12h16m-6-6 6 6-6 6"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
        'download' => '<path d="M12 3v12m-5-5 5 5 5-5M4 16v5h16v-5"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
        'spark' => '<path d="m12 2 3 7 7 3-7 3-3 7-3-7-7-3 7-3Z"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'shield' => '<path d="m12 2 8 4v6c0 5-8 10-8 10S4 17 4 12V6Z"/><path d="m8 12 3 3 5-6"/>',
        'close' => '<path d="m6 6 12 12M6 18 18 6"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'play' => '<path d="m8 4 12 8-12 8Z"/>',
        'windows' => '<path d="M3 5 11 4v7H3Zm10-1 8-1v8h-8ZM3 13h8v7l-8-1Zm10 0h8v8l-8-1Z"/>',
    ];
    return '<svg width="' .
        $size .
        '" height="' .
        $size .
        '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' .
        ($paths[$name] ?? $paths['spark']) .
        '</svg>';
}
function status_label(string $status): string
{
    return [
        'draft' => 'Rascunho',
        'pending' => 'Em revisão',
        'published' => 'Publicado',
        'rejected' => 'Ajustes necessários',
        'withdrawn' => 'Retirado',
    ][$status] ?? $status;
}
