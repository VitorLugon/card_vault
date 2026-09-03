<?php

declare(strict_types=1);

use App\Config\Database;

require_once dirname(__DIR__) . '/src/Config/Database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    Database::connection()->query('SELECT 1');
    echo json_encode(['status' => 'ok', 'database' => 'connected']);
} catch (\Throwable) {
    http_response_code(503);
    echo json_encode(['status' => 'error', 'database' => 'unavailable']);
}
