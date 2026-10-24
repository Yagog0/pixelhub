<?php
declare(strict_types=1);

// Executar exclusivamente no terminal: php scripts/admin.php email@exemplo.com
// O administrador primeiro se cadastra normalmente; não existe senha padrão.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit();
}
require dirname(__DIR__) . '/app/bootstrap.php';
use App\Core\Database;
$email = mb_strtolower(trim($argv[1] ?? ''));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Uso: php scripts/admin.php email@exemplo.com\n");
    exit(1);
}
$user = Database::one('SELECT id FROM users WHERE email=?', [$email]);
if (!$user) {
    fwrite(STDERR, "Cadastre essa conta no site antes de promover a administrador.\n");
    exit(1);
}
Database::run("UPDATE users SET role='admin' WHERE id=?", [$user['id']]);
echo "Conta promovida. Entre no site e acesse Moderação.\n";
