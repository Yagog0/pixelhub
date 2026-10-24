<?php
declare(strict_types=1);

// Entrada administrativa no Apache em subpasta. A autorização fica no controller.
$_GET['page'] = 'admin';
require __DIR__ . '/public/index.php';
