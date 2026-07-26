<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * Acesso à tabela de médicos.
 *
 * Concentrar o SQL aqui mantém as páginas sem consultas soltas e garante que
 * todo parâmetro vindo do usuário passe por bind — nunca por concatenação.
 */
final class MedicoRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public static function criar(): self
    {
        return new self(db());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listar(string $busca = ''): array
    {
        if ($busca === '') {
            return $this->pdo->query(
                'SELECT id, nome, cpf, email, telefone, especialidade, crm
                 FROM medicos ORDER BY nome'
            )->fetchAll();
        }

        // Com prepared statements nativos (EMULATE_PREPARES = false) o MySQL
        // exige um placeholder distinto por posição — repetir :termo dá
        // "Invalid parameter number". Por isso os quatro nomes numerados.
        $stmt = $this->pdo->prepare(
            'SELECT id, nome, cpf, email, telefone, especialidade, crm
             FROM medicos
             WHERE nome LIKE :termo1 OR email LIKE :termo2 OR crm LIKE :termo3
                OR especialidade LIKE :termo4
             ORDER BY nome'
        );

        $termo = '%' . $busca . '%';
        $stmt->execute([
            ':termo1' => $termo,
            ':termo2' => $termo,
            ':termo3' => $termo,
            ':termo4' => $termo,
        ]);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, nome, cpf, email, telefone, especialidade, crm
             FROM medicos WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function emailEmUso(string $email, ?int $ignorarId = null): bool
    {
        $sql = 'SELECT 1 FROM medicos WHERE email = :email';
        $parametros = [':email' => $email];

        if ($ignorarId !== null) {
            $sql .= ' AND id <> :id';
            $parametros[':id'] = $ignorarId;
        }

        $stmt = $this->pdo->prepare($sql . ' LIMIT 1');
        $stmt->execute($parametros);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * @param array<string, string> $dados
     */
    public function inserir(array $dados, string $senhaEmTexto): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO medicos (nome, cpf, email, telefone, especialidade, crm, senha)
             VALUES (:nome, :cpf, :email, :telefone, :especialidade, :crm, :senha)'
        );

        $stmt->execute([
            ':nome'          => $dados['nome'],
            ':cpf'           => $dados['cpf'],
            ':email'         => $dados['email'],
            ':telefone'      => $dados['telefone'],
            ':especialidade' => $dados['especialidade'],
            ':crm'           => $dados['crm'],
            ':senha'         => password_hash($senhaEmTexto, PASSWORD_DEFAULT),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @param array<string, string> $dados
     */
    public function atualizar(int $id, array $dados, ?string $senhaEmTexto = null): void
    {
        $sql = 'UPDATE medicos SET nome = :nome, cpf = :cpf, email = :email,
                telefone = :telefone, especialidade = :especialidade, crm = :crm';

        $parametros = [
            ':id'            => $id,
            ':nome'          => $dados['nome'],
            ':cpf'           => $dados['cpf'],
            ':email'         => $dados['email'],
            ':telefone'      => $dados['telefone'],
            ':especialidade' => $dados['especialidade'],
            ':crm'           => $dados['crm'],
        ];

        // Só troca a senha quando o formulário enviou uma nova.
        if ($senhaEmTexto !== null && $senhaEmTexto !== '') {
            $sql .= ', senha = :senha';
            $parametros[':senha'] = password_hash($senhaEmTexto, PASSWORD_DEFAULT);
        }

        $this->pdo->prepare($sql . ' WHERE id = :id')->execute($parametros);
    }

    public function excluir(int $id): void
    {
        $this->pdo->prepare('DELETE FROM medicos WHERE id = :id')
            ->execute([':id' => $id]);
    }

    public function total(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM medicos')->fetchColumn();
    }
}
