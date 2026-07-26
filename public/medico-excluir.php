<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/MedicoRepository.php';

sessao_iniciar();
auth_exigir_login();

// Só POST: exclusão por GET podia ser disparada por um simples link ou
// por qualquer imagem apontando para a URL.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido.');
}

csrf_validar();

$id = (int) ($_POST['id'] ?? 0);
$usuario = auth_usuario();

if ($id <= 0) {
    flash('Registro inválido.', 'erro');
    redirecionar('medicos.php');
}

if ($id === $usuario['id']) {
    flash('Você não pode excluir o próprio cadastro.', 'erro');
    redirecionar('medicos.php');
}

MedicoRepository::criar()->excluir($id);
flash('Cadastro excluído.');

redirecionar('medicos.php');
