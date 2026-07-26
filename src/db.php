<?php

declare(strict_types=1);

/**
 * Conexão PDO única por requisição.
 *
 * ERRMODE_EXCEPTION faz erros de SQL virarem exceções em vez de warnings
 * silenciosos, e EMULATE_PREPARES = false garante prepared statements reais
 * no servidor MySQL — não a emulação do driver, que só escapa strings.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = require __DIR__ . '/config.php';
    $db = $config['db'];

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $db['host'],
        $db['port'],
        $db['name'],
        $db['charset']
    );

    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    return $pdo;
}
