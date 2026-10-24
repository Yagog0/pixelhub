<?php
declare(strict_types=1);

// Entrada administrativa quando public/ é a raiz do servidor.
$_GET['page'] = 'admin';
require __DIR__ . '/index.php';
