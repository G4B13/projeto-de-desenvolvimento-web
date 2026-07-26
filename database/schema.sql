-- Schema do Sistema Médico.
-- Carregado automaticamente pelo container MySQL na primeira subida.

CREATE TABLE IF NOT EXISTS medicos (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome          VARCHAR(120)  NOT NULL,
    cpf           CHAR(11)      NOT NULL,
    email         VARCHAR(160)  NOT NULL,
    telefone      VARCHAR(20)   NULL,
    especialidade VARCHAR(80)   NULL,
    crm           VARCHAR(20)   NOT NULL,
    -- 255 acomoda o hash bcrypt (60 chars) e algoritmos futuros mais longos.
    senha         VARCHAR(255)  NOT NULL,
    criado_em     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_medicos_email (email),
    UNIQUE KEY uk_medicos_cpf   (cpf),
    UNIQUE KEY uk_medicos_crm   (crm),
    KEY idx_medicos_nome (nome)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- O usuário inicial NÃO é criado aqui de propósito: um hash fixo em arquivo
-- versionado vira senha pública. Use `database/seed.php` — ele gera o hash
-- com password_hash() no momento da execução.
