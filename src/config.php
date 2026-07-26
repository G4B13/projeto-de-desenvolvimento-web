<?php

declare(strict_types=1);

/**
 * Configuração da aplicação.
 *
 * Os valores vêm de variáveis de ambiente para que credenciais não fiquem
 * versionadas. Os defaults servem apenas para o ambiente Docker local.
 */

function env(string $chave, ?string $padrao = null): ?string
{
    $valor = getenv($chave);

    return $valor === false || $valor === '' ? $padrao : $valor;
}

return [
    'db' => [
        'host'  => env('DB_HOST', 'db'),
        'port'  => env('DB_PORT', '3306'),
        'name'  => env('DB_NAME', 'sistema_medico'),
        'user'  => env('DB_USER', 'sistema'),
        'pass'  => env('DB_PASS', 'sistema'),
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'nome'   => 'Sistema Médico',
        'debug'  => env('APP_DEBUG', '0') === '1',
    ],
];
