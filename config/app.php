<?php
declare(strict_types=1);

// Dados privados ficam FORA do htdocs, inclusive quando .htaccess está desativado.
// PIXELHUB_STORAGE permite escolher um diretório persistente em outro servidor.
$home = getenv('LOCALAPPDATA') ?: sys_get_temp_dir();
return [
    'name' => 'PixelHub',
    'storage' =>
        getenv('PIXELHUB_STORAGE') ?: $home . '/PixelHub/' . substr(hash('sha256', dirname(__DIR__)), 0, 12),
    'upload_limit' => 20 * 1024 * 1024,
];
