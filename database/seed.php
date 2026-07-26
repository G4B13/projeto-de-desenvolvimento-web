<?php

declare(strict_types=1);

/**
 * Cria o usuário administrador inicial.
 *
 * Uso:
 *   docker compose exec app php database/seed.php
 *
 * A senha pode vir de ADMIN_SENHA; se não vier, uma é sorteada e impressa.
 * Em nenhum caso um hash fica versionado no repositório.
 */

if (PHP_SAPI !== 'cli') {
    exit('Este script só roda pela linha de comando.');
}

require __DIR__ . '/../src/db.php';

$email = getenv('ADMIN_EMAIL') ?: 'admin@sistemamedico.local';
$senha = getenv('ADMIN_SENHA') ?: bin2hex(random_bytes(6));

$pdo = db();

$existe = $pdo->prepare('SELECT id FROM medicos WHERE email = :email');
$existe->execute([':email' => $email]);

if ($existe->fetchColumn()) {
    fwrite(STDERR, "Já existe um cadastro com o e-mail {$email}. Nada a fazer.\n");
    exit(1);
}

$pdo->prepare(
    'INSERT INTO medicos (nome, cpf, email, telefone, especialidade, crm, senha)
     VALUES (:nome, :cpf, :email, :telefone, :especialidade, :crm, :senha)'
)->execute([
    ':nome'          => 'Administrador',
    ':cpf'           => '00000000191',
    ':email'         => $email,
    ':telefone'      => '',
    ':especialidade' => 'Clínica Geral',
    ':crm'           => 'CRM-000000',
    ':senha'         => password_hash($senha, PASSWORD_DEFAULT),
]);

echo "Usuário administrador criado.\n";
echo "  E-mail: {$email}\n";
echo "  Senha:  {$senha}\n";
echo "\nTroque a senha após o primeiro acesso.\n";
