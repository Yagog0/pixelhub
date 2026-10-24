<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Controllers\SiteController;
use App\Controllers\ActionController;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        (new ActionController())->handle();
    } else {
        (new SiteController())->handle();
    }
} catch (Throwable $exception) {
    error_log('[PixelHub] ' . $exception->getMessage());
    http_response_code(500);
    render('message', [
        'title' => 'Uma pausa inesperada',
        'message' => 'Não foi possível concluir a operação. Tente novamente em alguns instantes.',
    ]);
}
