<?php
declare(strict_types=1);
// Diagnóstico local: nunca disponível como endpoint público.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit();
}
require dirname(__DIR__) . '/app/bootstrap.php';
echo 'Banco: ' . App\Core\Database::$storage . '/pixelhub.sqlite' . PHP_EOL;
echo 'Builds: ' . App\Core\Database::$storage . '/builds' . PHP_EOL;
echo 'PHP: ' . PHP_VERSION . PHP_EOL;
