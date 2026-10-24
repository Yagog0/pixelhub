<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Database as DB;
use App\Models\Project;
use App\Models\Achievement;

final class SiteController
{
    public function handle(): void
    {
        $page = (string) ($_GET['page'] ?? 'discover');
        switch ($page) {
            case 'admin-setup':
                (new AdminSetupController())->show();
                break;
            case 'random':
                $filters = array_filter(
                    array_intersect_key($_GET, array_flip(['genre', 'type', 'platform', 'collection'])),
                    'is_string',
                );
                $games = Project::catalog($filters);
                if (($_GET['library_only'] ?? '') === '1') {
                    $u = require_user();
                    $ids = array_column(Project::library($u['id']), 'id');
                    $games = array_values(array_filter($games, fn($g) => in_array($g['id'], $ids)));
                }
                if (isset($_GET['draw'])) {
                    if (!$games) {
                        flash(
                            'Nenhum jogo combina com estes filtros. Experimente ampliar a seleção.',
                            'error',
                        );
                        redirect('random');
                    }
                    $picked = $games[random_int(0, count($games) - 1)];
                    flash('O universo escolheu: ' . $picked['title'] . '!');
                    redirect('game', ['id' => $picked['id']]);
                }
                render('random', ['title' => 'Deixe o universo escolher', 'total' => count($games)]);
                break;
            case 'achievements':
                $u = require_user();
                Achievement::syncLibrary($u['id']);
                render('achievements', [
                    'title' => 'Suas conquistas',
                    'achievements' => Achievement::forUser($u['id']),
                ]);
                break;
            case 'discover':
            case 'catalog':
                $filters = array_intersect_key(
                    $_GET,
                    array_flip(['q', 'genre', 'type', 'platform', 'collection', 'sort']),
                );
                // Rejeita arrays enviados onde os filtros esperam texto.
                $filters = array_filter($filters, 'is_string');
                render($page, [
                    'title' => $page === 'discover' ? 'Descobrir' : 'Explorar catálogo',
                    'games' => Project::catalog($filters),
                    'filters' => $filters,
                    'settings' => \App\Models\SiteSettings::get(),
                ]);
                break;
            case 'game':
                $game = Project::find((int) ($_GET['id'] ?? 0));
                $u = user();
                if (
                    !$game ||
                    ($game['status'] !== 'published' &&
                        (!$u || ((int) $game['owner_id'] !== (int) $u['id'] && $u['role'] !== 'admin')))
                ) {
                    $this->notFound();
                    return;
                }
                $entry = $u
                    ? DB::one('SELECT * FROM library WHERE user_id = ? AND project_id = ?', [
                        $u['id'],
                        $game['id'],
                    ])
                    : null;
                render('game', ['title' => $game['title'], 'game' => $game, 'entry' => $entry]);
                break;
            case 'library':
            case 'favorites':
                $u = require_user();
                render('library', [
                    'title' => $page === 'favorites' ? 'Seus favoritos' : 'Sua biblioteca',
                    'games' => Project::library($u['id'], $page === 'favorites'),
                ]);
                break;
            case 'login':
            case 'register':
                if (user()) {
                    redirect('profile');
                }
                render('auth', [
                    'title' => $page === 'login' ? 'Bem-vindo de volta' : 'Seu próximo mundo começa aqui',
                ]);
                break;
            case 'creator':
                $u = require_user();
                render('creator', [
                    'title' => 'Espaço do criador',
                    'projects' => DB::all(
                        'SELECT p.*, (SELECT COUNT(*) FROM library l WHERE l.project_id = p.id) AS additions FROM projects p WHERE owner_id = ? ORDER BY id DESC',
                        [$u['id']],
                    ),
                ]);
                break;
            case 'editor':
                $u = require_user();
                $game = isset($_GET['id']) ? Project::find((int) $_GET['id']) : null;
                if (isset($_GET['id']) && (!$game || (int) $game['owner_id'] !== (int) $u['id'])) {
                    $this->notFound();
                    return;
                }
                render('editor', [
                    'title' => $game ? 'Editar projeto' : 'Uma nova ideia ganha vida',
                    'game' => $game,
                ]);
                break;
            case 'profile':
                $u = require_user();
                render('profile', ['title' => 'Seu perfil', 'count' => count(Project::library($u['id']))]);
                break;
            case 'admin':
                (new AdminController())->show();
                break;
            case 'download':
                $this->download();
                break;
            case 'export':
                $u = require_user();
                header('Content-Type: application/json; charset=utf-8');
                header('Content-Disposition: attachment; filename="pixelhub-meus-dados.json"');
                echo json_encode(
                    [
                        'profile' => $u,
                        'library' => Project::library($u['id']),
                        'achievements' => Achievement::forUser($u['id']),
                        'projects' => DB::all('SELECT * FROM projects WHERE owner_id=?', [$u['id']]),
                        'reports' => DB::all('SELECT * FROM reports WHERE user_id=?', [$u['id']]),
                    ],
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE,
                );
                break;
            case 'play':
                $game = Project::find(1);
                if (!$game || !$game['playable'] || $game['status'] !== 'published') {
                    $this->notFound();
                    return;
                }
                render('play', ['title' => 'Órbita de Papel']);
                break;
            case 'collections':
                render('collections', ['title' => 'Coleções para descobrir']);
                break;
            case 'about':
            case 'privacy':
            case 'launcher':
                render('info', [
                    'title' => [
                        'about' => 'Pequenos criadores. Novos universos.',
                        'privacy' => 'Sua privacidade, sem mistério',
                        'launcher' => 'Sua próxima aventura, no desktop',
                    ][$page],
                ]);
                break;
            default:
                $this->notFound();
        }
    }
    private function download(): void
    {
        $u = require_user();
        $game = Project::find((int) ($_GET['id'] ?? 0));
        $review = $u['role'] === 'admin' && ($_GET['review'] ?? '') === '1';
        $entry = $game
            ? DB::one('SELECT 1 FROM library WHERE user_id=? AND project_id=?', [$u['id'], $game['id']])
            : null;
        if (!$game || !$game['build_path'] || (!$review && ($game['status'] !== 'published' || !$entry))) {
            $this->notFound();
            return;
        }
        $path = DB::$storage . '/builds/' . basename($game['build_path']);
        if (!is_file($path) || !hash_equals($game['build_hash'], hash_file('sha256', $path))) {
            http_response_code(409);
            render('message', [
                'title' => 'Arquivo indisponível',
                'message' => 'A verificação de integridade falhou. O download foi bloqueado.',
            ]);
            return;
        }
        header('Content-Type: application/zip');
        header(
            'Content-Disposition: attachment; filename="' . $game['slug'] . '-' . $game['version'] . '.zip"',
        );
        header('Content-Length: ' . filesize($path));
        // Um início autorizado não comprova transferência concluída ou instalação.
        if (!$review) DB::run('INSERT INTO download_events(project_id) VALUES (?)', [$game['id']]);
        session_write_close();
        readfile($path);
    }
    private function notFound(): void
    {
        http_response_code(404);
        render('message', [
            'title' => 'Este mundo não foi encontrado',
            'message' =>
                'O projeto pode ter sido retirado ou o endereço está incorreto. Explore o catálogo para encontrar outra aventura.',
        ]);
    }
}
