<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

/**
 * Inicia a sessão com cookie endurecido.
 *
 * httponly bloqueia leitura por JavaScript; samesite=Lax corta CSRF via
 * navegação de terceiros; secure ativa quando a requisição já é HTTPS.
 */
function sessao_iniciar(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);

    session_start();
}

/**
 * Autentica pelo e-mail e devolve se o login teve sucesso.
 *
 * A verificação usa password_verify (bcrypt). A versão anterior comparava
 * hashes MD5 dentro da própria query — além de quebrado como hash, permitia
 * enumerar usuários pelo tempo de resposta.
 */
function auth_login(string $email, string $senha): bool
{
    $stmt = db()->prepare(
        'SELECT id, nome, email, senha FROM medicos WHERE email = :email LIMIT 1'
    );
    $stmt->execute([':email' => $email]);
    $medico = $stmt->fetch();

    if (!$medico || !password_verify($senha, $medico['senha'])) {
        return false;
    }

    // Reidrata o hash se o custo padrão do PHP tiver aumentado desde o cadastro.
    if (password_needs_rehash($medico['senha'], PASSWORD_DEFAULT)) {
        $novo = password_hash($senha, PASSWORD_DEFAULT);
        db()->prepare('UPDATE medicos SET senha = :senha WHERE id = :id')
            ->execute([':senha' => $novo, ':id' => $medico['id']]);
    }

    // Novo ID de sessão a cada login, contra session fixation.
    session_regenerate_id(true);

    $_SESSION['medico'] = [
        'id'    => (int) $medico['id'],
        'nome'  => $medico['nome'],
        'email' => $medico['email'],
    ];

    return true;
}

function auth_logout(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }

    session_destroy();
}

function auth_usuario(): ?array
{
    return $_SESSION['medico'] ?? null;
}

/**
 * Guarda de rota. Toda página interna chama isto na primeira linha —
 * é exatamente o que faltava antes, quando main.php era acessível direto.
 */
function auth_exigir_login(): void
{
    if (auth_usuario() === null) {
        redirecionar('login.php');
    }
}
