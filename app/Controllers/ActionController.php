<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Database as DB;
use App\Models\Project;
use App\Models\Achievement;
use RuntimeException;

final class ActionController
{
    private function input(string $key, int $max = 2000): string
    {
        $value = $_POST[$key] ?? '';
        if (!is_string($value) || mb_strlen($value) > $max) {
            throw new RuntimeException(
                'O campo ' . $key . ' ultrapassou o limite de ' . $max . ' caracteres.',
            );
        }
        return trim($value);
    }
    public function handle(): void
    {
        // Integrações de jogos usam token limitado ao jogador/projeto; não cookies de sessão.
        if (($_GET['page'] ?? '') === 'api-unlock') {
            $this->apiUnlock();
            return;
        }
        if (!is_string($_POST['csrf'] ?? null) || !hash_equals(csrf(), $_POST['csrf'])) {
            http_response_code(419);
            render('message', [
                'title' => 'Vamos atualizar sua sessão',
                'message' => 'A sessão deste formulário expirou. Reabra a página e tente novamente.',
            ]);
            return;
        }
        $action = $this->input('action', 30);
        if ($action === 'activate_admin') {
            (new AdminSetupController())->activate();
            return;
        }
        if (str_starts_with($action, 'admin_')) {
            (new AdminController())->action($action);
            return;
        }
        try {
            switch ($action) {
                case 'game_achievement':
                    $u = require_user();
                    $key = $this->input('key', 60);
                    if (
                        !in_array($key, ['first-flight', 'five-stars', 'fifteen-stars'], true) ||
                        Project::find(1)['status'] !== 'published'
                    ) {
                        throw new RuntimeException('Conquista inválida.');
                    }
                    $title = Achievement::unlock($u['id'], 1, $key, 'local-demo');
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['unlocked' => $title], JSON_UNESCAPED_UNICODE);
                    return;
                case 'game_token':
                    $u = require_user();
                    $id = (int) ($_POST['id'] ?? 0);
                    $g = Project::find($id);
                    if (
                        !$g ||
                        $g['status'] !== 'published' ||
                        !DB::one('SELECT 1 FROM library WHERE user_id=? AND project_id=?', [$u['id'], $id])
                    ) {
                        throw new RuntimeException('Adicione este jogo à biblioteca para gerar uma chave.');
                    }
                    DB::run('DELETE FROM game_tokens WHERE (user_id=? AND project_id=?) OR expires_at < ?', [
                        $u['id'],
                        $id,
                        time(),
                    ]);
                    $token = bin2hex(random_bytes(32));
                    DB::run('INSERT INTO game_tokens(hash,user_id,project_id,expires_at) VALUES (?,?,?,?)', [
                        hash('sha256', $token),
                        $u['id'],
                        $id,
                        time() + 3600,
                    ]);
                    $_SESSION['new_game_token'] = ['id' => $id, 'token' => $token];
                    redirect('game', ['id' => $id]);
                case 'achievement_definition':
                    $g = $this->owned();
                    if (!in_array($g['status'], ['draft', 'rejected'], true)) {
                        throw new RuntimeException('Defina as conquistas antes de enviar para revisão.');
                    }
                    $key = $this->input('key', 60);
                    $title = $this->input('title', 100);
                    $description = $this->input('description', 500);
                    if (
                        !preg_match('/^[a-z0-9-]{3,60}$/', $key) ||
                        mb_strlen($title) < 3 ||
                        mb_strlen($description) < 10
                    ) {
                        throw new RuntimeException(
                            'Use uma chave de 3 a 60 letras minúsculas, números ou hífens, título de 3 e descrição de 10 caracteres.',
                        );
                    }
                    if (
                        DB::one('SELECT COUNT(*) AS total FROM achievements WHERE project_id=?', [$g['id']])[
                            'total'
                        ] >= 20
                    ) {
                        throw new RuntimeException('Limite de 20 conquistas por projeto.');
                    }
                    DB::run(
                        'INSERT INTO achievements(project_id,key,title,description,secret) VALUES (?,?,?,?,?) ON CONFLICT(project_id,key) DO UPDATE SET title=excluded.title,description=excluded.description,secret=excluded.secret',
                        [$g['id'], $key, $title, $description, empty($_POST['secret']) ? 0 : 1],
                    );
                    flash('Conquista salva. A mesma chave atualiza sua definição.');
                    redirect('editor', ['id' => $g['id']]);
                case 'login':
                case 'register':
                    $this->authenticate($action);
                    break;
                case 'logout':
                    $_SESSION = [];
                    session_regenerate_id(true);
                    redirect('discover');
                case 'library':
                case 'favorite':
                case 'remove':
                    $this->library($action);
                    break;
                case 'save_project':
                    $this->saveProject();
                    break;
                case 'submit':
                    $this->submit();
                    break;
                case 'review':
                    $this->review();
                    break;
                case 'profile':
                    $u = require_user();
                    $name = $this->input('name', 60);
                    if (mb_strlen($name) < 2) {
                        throw new RuntimeException('Informe um nome com pelo menos 2 caracteres.');
                    }
                    DB::run('UPDATE users SET name=?, bio=? WHERE id=?', [
                        $name,
                        $this->input('bio', 400),
                        $u['id'],
                    ]);
                    flash('Perfil atualizado. Sua biblioteca continua privada.');
                    redirect('profile');
                case 'report':
                    $u = require_user();
                    $id = (int) ($_POST['id'] ?? 0);
                    $g = Project::find($id);
                    $reason = $this->input('reason', 1500);
                    if (!$g || $g['status'] !== 'published' || mb_strlen($reason) < 10) {
                        throw new RuntimeException('Descreva o problema em pelo menos 10 caracteres.');
                    }
                    if (
                        DB::one(
                            "SELECT COUNT(*) AS total FROM reports WHERE user_id=? AND created_at > datetime('now', '-1 hour')",
                            [$u['id']],
                        )['total'] >= 5
                    ) {
                        throw new RuntimeException(
                            'Limite de 5 relatos por hora atingido. Tente mais tarde.',
                        );
                    }
                    DB::run('INSERT INTO reports(user_id,project_id,reason) VALUES (?,?,?)', [
                        $u['id'],
                        $id,
                        $reason,
                    ]);
                    flash('Relato registrado. A equipe pode acompanhá-lo na área de revisão.');
                    redirect('game', ['id' => $id]);
                case 'resolve':
                    $u = require_user();
                    if ($u['role'] !== 'admin') {
                        throw new RuntimeException('Acesso restrito.');
                    }
                    DB::run("UPDATE reports SET status='resolved' WHERE id=?", [(int) ($_POST['id'] ?? 0)]);
                    $report = DB::one('SELECT project_id FROM reports WHERE id=?', [(int) ($_POST['id'] ?? 0)]);
                    if ($report) DB::run('INSERT INTO audit(actor_id,project_id,action) VALUES (?,?,?)', [$u['id'], $report['project_id'], 'Denúncia #' . (int)$_POST['id'] . ' marcada como analisada']);
                    flash('Relato marcado como analisado.');
                    redirect('admin', ['tab'=>'reports']);
                default:
                    throw new RuntimeException('Ação não reconhecida.');
            }
        } catch (RuntimeException $error) {
            flash($error->getMessage(), 'error');
            // Em caso de erro, mantém os campos do projeto, mas nunca guarda senhas.
            if ($action === 'save_project') {
                $_SESSION['old_project'] = array_filter($_POST, 'is_string');
                redirect('editor', empty($_POST['id']) ? [] : ['id' => (int) $_POST['id']]);
            }
            $return = match ($action) {
                'login', 'register' => $action,
                'submit' => 'creator',
                'review', 'resolve' => 'admin',
                'profile' => 'profile',
                'report', 'game_token' => 'game',
                'achievement_definition' => 'editor',
                default => 'library',
            };
            redirect(
                $return,
                in_array($return, ['game', 'editor']) ? ['id' => (int) ($_POST['id'] ?? 0)] : [],
            );
        }
    }
    private function authenticate(string $action): void
    {
        $email = mb_strtolower($this->input('email', 190));
        $password = $_POST['password'] ?? '';
        if (!is_string($password) || strlen($password) > 200 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Confira o e-mail e a senha informados.');
        }
        $key = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'local');
        $attempt = DB::one('SELECT * FROM login_attempts WHERE key=?', [$key]);
        if ($attempt && $attempt['started'] > time() - 900 && $attempt['attempts'] >= 15) {
            throw new RuntimeException('Muitas tentativas. Aguarde 15 minutos antes de tentar novamente.');
        }
        DB::run(
            'INSERT INTO login_attempts(key,attempts,started) VALUES (?,1,?) ON CONFLICT(key) DO UPDATE SET attempts=CASE WHEN started < ? THEN 1 ELSE attempts+1 END, started=CASE WHEN started < ? THEN excluded.started ELSE started END',
            [$key, time(), time() - 900, time() - 900],
        );
        if ($action === 'register') {
            $name = $this->input('name', 60);
            if (mb_strlen($name) < 2 || strlen($password) < 10) {
                throw new RuntimeException(
                    'Use um nome com 2 ou mais caracteres e uma senha com pelo menos 10 caracteres.',
                );
            }
            if (empty($_POST['terms'])) {
                throw new RuntimeException('Leia e aceite o aviso de privacidade para criar a conta.');
            }
            if (DB::one('SELECT id FROM users WHERE email=?', [$email])) {
                throw new RuntimeException(
                    'Não foi possível cadastrar esse e-mail. Tente entrar na sua conta.',
                );
            }
            DB::run('INSERT INTO users(name,email,password) VALUES (?,?,?)', [
                $name,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
            ]);
            $id = DB::id();
        } else {
            $u = DB::one('SELECT * FROM users WHERE email=?', [$email]);
            if (!$u || !password_verify($password, $u['password'])) {
                throw new RuntimeException('E-mail ou senha incorretos.');
            }
            $id = $u['id'];
        }
        DB::run('DELETE FROM login_attempts WHERE key=?', [$key]);
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
        unset($_SESSION['csrf']);
        flash(
            $action === 'register'
                ? 'Sua conta está pronta. Bem-vindo à PixelHub!'
                : 'Bom ter você de volta!',
        );
        $destination = ($_SESSION['after_login'] ?? '') === 'admin' ? 'admin' : 'discover';
        unset($_SESSION['after_login']);
        redirect($destination);
    }
    private function library(string $action): void
    {
        $u = require_user();
        $id = (int) ($_POST['id'] ?? 0);
        $g = Project::find($id);
        if (!$g || ($action !== 'remove' && $g['status'] !== 'published')) {
            throw new RuntimeException('Este jogo não está disponível no catálogo.');
        }
        if ($action === 'remove') {
            DB::run('DELETE FROM library WHERE user_id=? AND project_id=?', [$u['id'], $id]);
            flash('Jogo removido da biblioteca.');
        } else {
            DB::run('INSERT OR IGNORE INTO library(user_id,project_id) VALUES (?,?)', [$u['id'], $id]);
            if ($action === 'favorite') {
                DB::run('UPDATE library SET favorite=1-favorite WHERE user_id=? AND project_id=?', [
                    $u['id'],
                    $id,
                ]);
            }
            Achievement::syncLibrary($u['id']);
            flash($action === 'favorite' ? 'Favoritos atualizados.' : 'Mais um mundo na sua biblioteca!');
        }
        redirect('library');
    }
    private function owned(): array
    {
        $u = require_user();
        $g = Project::find((int) ($_POST['id'] ?? 0));
        if (!$g || (int) $g['owner_id'] !== (int) $u['id']) {
            throw new RuntimeException('Projeto não encontrado ou sem permissão.');
        }
        return $g;
    }
    private function saveProject(): void
    {
        global $config;
        $u = require_user();
        $g = !empty($_POST['id']) ? $this->owned() : null;
        if ($g && !in_array($g['status'], ['draft', 'rejected'], true)) {
            throw new RuntimeException(
                'Projetos publicados ou em revisão não podem ser alterados. Crie um novo projeto para outra versão neste protótipo.',
            );
        }
        if (
            !$g &&
            DB::one('SELECT COUNT(*) AS total FROM projects WHERE owner_id=?', [$u['id']])['total'] >= 3
        ) {
            throw new RuntimeException('O beta permite até 3 projetos por criador.');
        }
        $title = $this->input('title', 80);
        $summary = $this->input('summary', 180);
        $description = $this->input('description', 6000);
        if (mb_strlen($title) < 3 || mb_strlen($summary) < 10 || mb_strlen($description) < 20) {
            throw new RuntimeException('Preencha título (3 caracteres), resumo (10) e descrição (20).');
        }
        $genre = $this->input('genre', 40);
        $type = $this->input('type', 40);
        $platform = $this->input('platform', 40);
        $art = $this->input('art', 20);
        if (
            !in_array($genre, ['Aventura', 'Puzzle', 'Plataforma', 'RPG', 'Corrida', 'Exploração'], true) ||
            !in_array($type, ['Jogo completo', 'Demo', 'Homebrew'], true) ||
            !in_array($platform, ['Windows', 'Linux', 'Navegador'], true) ||
            !in_array($art, ['orbit', 'garden', 'circuit', 'valley', 'fire', 'sea'], true)
        ) {
            throw new RuntimeException('Escolha uma categoria, plataforma e capa válidas.');
        }
        $version = $this->input('version', 30);
        if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            throw new RuntimeException('Use uma versão no formato 1.0.0.');
        }
        $values = [
            $title,
            $u['name'],
            $summary,
            $description,
            $genre,
            $type,
            $platform,
            $this->input('language', 80),
            $this->input('duration', 80),
            $this->input('requirements', 1500),
            $this->input('controls', 500),
            $this->input('content_notice', 500),
            $this->input('license', 500),
            $this->input('rights', 4000),
            $art,
            $version,
            $this->input('changelog', 2000),
        ];
        $build = null;
        if (isset($_FILES['build']) && $_FILES['build']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['build'];
            if (
                $file['error'] !== UPLOAD_ERR_OK ||
                $file['size'] > $config['upload_limit'] ||
                $file['size'] < 4
            ) {
                throw new RuntimeException(
                    'Envie um ZIP válido de até 20 MB. Confira também o limite de upload do PHP.',
                );
            }
            $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
            if (
                !in_array($mime, ['application/zip', 'application/x-zip-compressed'], true) ||
                strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'zip'
            ) {
                throw new RuntimeException('A build precisa ser um arquivo ZIP.');
            }
            $build = [
                bin2hex(random_bytes(24)) . '.zip',
                (int) $file['size'],
                hash_file('sha256', $file['tmp_name']),
            ];
            if (!move_uploaded_file($file['tmp_name'], DB::$storage . '/builds/' . $build[0])) {
                throw new RuntimeException('Não foi possível guardar a build. Tente novamente.');
            }
        }
        if ($g) {
            DB::run(
                "UPDATE projects SET title=?,creator=?,summary=?,description=?,genre=?,type=?,platform=?,language=?,duration=?,requirements=?,controls=?,content_notice=?,license=?,rights=?,art=?,version=?,changelog=?,status='draft',review_note='' WHERE id=?",
                [...$values, $g['id']],
            );
            $id = $g['id'];
        } else {
            $slug =
                strtolower(
                    trim(
                        preg_replace('/[^a-zA-Z0-9]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $title)),
                        '-',
                    ),
                ) .
                '-' .
                bin2hex(random_bytes(3));
            DB::run(
                'INSERT INTO projects(title,creator,summary,description,genre,type,platform,language,duration,requirements,controls,content_notice,license,rights,art,version,changelog,owner_id,slug) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                [...$values, $u['id'], $slug],
            );
            $id = DB::id();
        }
        if ($build) {
            DB::run('UPDATE projects SET build_path=?,build_size=?,build_hash=? WHERE id=?', [
                ...$build,
                $id,
            ]);
            if ($g && $g['build_path']) {
                $old = DB::$storage . '/builds/' . basename($g['build_path']);
                if (is_file($old)) {
                    unlink($old);
                }
            }
        }
        unset($_SESSION['old_project']);
        flash('Rascunho salvo. Confira a prévia antes de enviar para revisão.');
        redirect('creator');
    }
    private function submit(): void
    {
        $g = $this->owned();
        if (!in_array($g['status'], ['draft', 'rejected'], true)) {
            throw new RuntimeException('Este projeto já foi enviado.');
        }
        foreach (['rights', 'license', 'requirements', 'changelog', 'build_path'] as $field) {
            if (empty($g[$field])) {
                throw new RuntimeException(
                    'Antes de enviar, preencha direitos, licença, requisitos e changelog, e anexe a build ZIP.',
                );
            }
        }
        DB::run("UPDATE projects SET status='pending' WHERE id=?", [$g['id']]);
        DB::run('INSERT INTO audit(actor_id,project_id,action) VALUES (?,?,?)', [
            user()['id'],
            $g['id'],
            'submitted',
        ]);
        Achievement::unlock(user()['id'], null, 'creator', 'platform');
        flash('Projeto enviado para revisão. O arquivo permanece privado.');
        redirect('creator');
    }
    private function review(): void
    {
        $u = require_user();
        if ($u['role'] !== 'admin') {
            throw new RuntimeException('Acesso restrito à moderação.');
        }
        $g = Project::find((int) ($_POST['id'] ?? 0));
        $decision = $this->input('decision', 20);
        $note = $this->input('review_note', 2000);
        if (!$g || !in_array($decision, ['published', 'rejected', 'withdrawn'], true)) {
            throw new RuntimeException('Decisão inválida.');
        }
        if (in_array($decision, ['published', 'rejected'], true) && $g['status'] !== 'pending') {
            throw new RuntimeException('Somente projetos em revisão podem receber esta decisão.');
        }
        if ($decision === 'withdrawn' && $g['status'] !== 'published') {
            throw new RuntimeException('Somente projetos publicados podem ser retirados.');
        }
        if (mb_strlen($note) < 10) {
            throw new RuntimeException('Registre uma justificativa com pelo menos 10 caracteres.');
        }
        if ($decision === 'published' && empty($_POST['checked'])) {
            throw new RuntimeException('Confirme a revisão manual do arquivo e dos direitos.');
        }
        if (
            $decision === 'published' &&
            (!$g['build_path'] ||
                !is_file(DB::$storage . '/builds/' . basename($g['build_path'])) ||
                !hash_equals(
                    $g['build_hash'],
                    hash_file('sha256', DB::$storage . '/builds/' . basename($g['build_path'])),
                ))
        ) {
            throw new RuntimeException('A integridade da build precisa ser corrigida antes da publicação.');
        }
        DB::run('UPDATE projects SET status=?,review_note=? WHERE id=?', [$decision, $note, $g['id']]);
        DB::run('INSERT INTO audit(actor_id,project_id,action) VALUES (?,?,?)', [
            $u['id'],
            $g['id'],
            $decision . ': ' . $note,
        ]);
        flash('Revisão registrada. O catálogo já reflete a decisão.');
        redirect('admin');
    }
    private function apiUnlock(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = str_starts_with($authorization, 'Bearer ') ? substr($authorization, 7) : '';
        $access = DB::one(
            "SELECT t.* FROM game_tokens t JOIN projects p ON p.id=t.project_id JOIN library l ON l.project_id=t.project_id AND l.user_id=t.user_id WHERE t.hash=? AND t.expires_at>? AND p.status='published'",
            [hash('sha256', $token), time()],
        );
        if (!$access) {
            http_response_code(401);
            echo json_encode(['error' => 'TOKEN_INVALID_OR_EXPIRED']);
            return;
        }
        $body = json_decode(file_get_contents('php://input'), true);
        $key = is_array($body) ? $body['key'] ?? null : null;
        if (
            !is_string($key) ||
            strlen($key) > 60 ||
            !DB::one('SELECT id FROM achievements WHERE project_id=? AND key=?', [
                $access['project_id'],
                $key,
            ])
        ) {
            http_response_code(422);
            echo json_encode(['error' => 'ACHIEVEMENT_NOT_FOUND']);
            return;
        }
        $title = Achievement::unlock($access['user_id'], $access['project_id'], $key, 'game-client');
        echo json_encode(
            ['ok' => true, 'new_unlock' => $title !== null, 'title' => $title],
            JSON_UNESCAPED_UNICODE,
        );
    }
}
