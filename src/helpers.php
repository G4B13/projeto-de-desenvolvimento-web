<?php

declare(strict_types=1);

/**
 * Escapa saída para HTML. Toda interpolação em view passa por aqui —
 * era a origem do XSS na versão anterior, que ecoava o banco direto.
 */
function e(?string $valor): string
{
    return htmlspecialchars($valor ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirecionar(string $destino): never
{
    header('Location: ' . $destino);
    exit;
}

/**
 * Mensagem de uso único, exibida na próxima requisição e então descartada.
 */
function flash(?string $mensagem = null, string $tipo = 'sucesso'): ?array
{
    if ($mensagem !== null) {
        $_SESSION['flash'] = ['mensagem' => $mensagem, 'tipo' => $tipo];

        return null;
    }

    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $flash;
}

/**
 * Token CSRF por sessão. Sem ele, qualquer página externa consegue submeter
 * formulários em nome de um usuário autenticado.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function csrf_validar(): void
{
    $enviado = $_POST['_token'] ?? '';

    // hash_equals compara em tempo constante, evitando timing attack.
    // 403 e não 419: o Apache traduz códigos fora do padrão HTTP para 500.
    if (!is_string($enviado) || !hash_equals(csrf_token(), $enviado)) {
        http_response_code(403);
        exit('Token de segurança inválido. Recarregue a página e tente novamente.');
    }
}

/**
 * Valida CPF pelos dois dígitos verificadores.
 */
function cpf_valido(string $cpf): bool
{
    $cpf = preg_replace('/\D/', '', $cpf) ?? '';

    if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }

    foreach ([9, 10] as $posicao) {
        $soma = 0;

        for ($i = 0; $i < $posicao; $i++) {
            $soma += (int) $cpf[$i] * (($posicao + 1) - $i);
        }

        $digito = (($soma * 10) % 11) % 10;

        if ((int) $cpf[$posicao] !== $digito) {
            return false;
        }
    }

    return true;
}

function cpf_formatar(string $cpf): string
{
    $cpf = preg_replace('/\D/', '', $cpf) ?? '';

    return strlen($cpf) === 11
        ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf)
        : $cpf;
}
