<?php
declare(strict_types=1);

// Cria um convite de uso único para uma conta NOVA, sem senha padrão ou envio de e-mail.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/bootstrap.php';
use App\Core\Database as DB;
$email = mb_strtolower(trim($argv[1] ?? ''));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { fwrite(STDERR,"Uso: php scripts/invite-admin.php email@exemplo.com\n"); exit(1); }
if (DB::one('SELECT id FROM users WHERE email=?',[$email])) { fwrite(STDERR,"A conta já existe. Use scripts/admin.php para promovê-la sem alterar a senha.\n"); exit(1); }
$token=bin2hex(random_bytes(32));
DB::run('INSERT INTO admin_invitations(email,token_hash,expires_at) VALUES (?,?,?) ON CONFLICT(email) DO UPDATE SET token_hash=excluded.token_hash,expires_at=excluded.expires_at,used_at=NULL',[$email,hash('sha256',$token),time()+86400]);
echo "Convite válido por 24 horas, uma única utilização:\nhttp://127.0.0.1:8081/index.php?page=admin-setup&token=$token\n";
