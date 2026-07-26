# Sistema Médico

Sistema web de gestão do corpo clínico: cadastro de médicos, busca, edição e
exclusão, com área restrita por autenticação.

Foi o meu primeiro sistema web completo. Esta versão é uma **refatoração** da
original — mesma linguagem (PHP), arquitetura e segurança reescritas.

---

## O que mudou na refatoração

A versão original funcionava, mas tinha falhas que só ficam evidentes depois de
alguma estrada. Registro o antes/depois porque é a parte mais útil do projeto.

| Antes | Depois |
| --- | --- |
| `validaMedico.php` conferia a senha e redirecionava, **sem criar sessão** — bastava abrir `main.php` direto para entrar | Sessão real, `session_regenerate_id()` no login e guarda `auth_exigir_login()` em toda página interna |
| Senhas em **MD5** | `password_hash()` / `password_verify()` com bcrypt e rehash automático |
| `DELETE FROM medico WHERE id = $id_medico` com `$_GET` cru — **SQL injection** | PDO com prepared statements reais (`EMULATE_PREPARES = false`) em todas as consultas |
| Dados do banco ecoados direto no HTML — **XSS** | Helper `e()` obrigatório em toda saída |
| Exclusão por link `GET` | `POST` + token **CSRF** validado com `hash_equals()` |
| `mysqli_connect("localhost","root","")` no código | Credenciais por variável de ambiente, com `.env.example` |
| CSS duplicado dentro de cada arquivo `.php` | Layout compartilhado + uma folha de estilo |
| Arquivos soltos na raiz, todos acessíveis pelo navegador | `public/` como raiz do servidor; `src/`, `views/` e `database/` fora do alcance |
| Assets referenciados que não existiam no repositório | Projeto roda do zero com um comando |

---

## Rodando

Requisito: Docker.

```bash
git clone https://github.com/gabrielc-neto/sistema-medico.git
cd sistema-medico

docker compose up -d --build
docker compose exec app php database/seed.php   # cria o admin e mostra a senha
```

Acesse **http://localhost:8080** e entre com as credenciais impressas pelo seed.

Para definir a senha em vez de sortear:

```bash
docker compose exec -e ADMIN_SENHA=suasenhaforte app php database/seed.php
```

Encerrar (`-v` também apaga o banco):

```bash
docker compose down -v
```

---

## Estrutura

```
.
├── docker-compose.yml     # app (PHP/Apache) + db (MySQL 8)
├── Dockerfile
├── database/
│   ├── schema.sql         # carregado na primeira subida do MySQL
│   └── seed.php           # cria o usuário inicial com hash gerado na hora
├── src/
│   ├── config.php         # configuração via variáveis de ambiente
│   ├── db.php             # fábrica da conexão PDO
│   ├── auth.php           # sessão, login/logout e guarda de rota
│   ├── helpers.php        # escape, CSRF, flash, validação de CPF
│   └── MedicoRepository.php
├── views/                 # layout compartilhado
└── public/                # raiz do servidor — só o que deve ser acessível
    ├── index.php
    ├── login.php · logout.php
    ├── medicos.php · medico-form.php · medico-excluir.php
    └── assets/css/app.css
```

---

## Decisões

- **Sem framework e sem Composer.** O objetivo é mostrar os fundamentos —
  roteamento por arquivo, PDO na mão, sessão do próprio PHP. Um framework aqui
  esconderia justamente o que o projeto pretende demonstrar.
- **`EMULATE_PREPARES = false`.** Com a emulação ligada (padrão do driver), o
  PDO monta a query como string e apenas escapa os valores. Desligar delega o
  bind ao MySQL, que nunca interpreta o parâmetro como SQL.
- **Nenhum hash de senha versionado.** O usuário inicial vem do `seed.php`, que
  gera o hash na execução — um hash fixo em arquivo público é senha pública.
- **Mensagem de login genérica.** "E-mail ou senha incorretos" nos dois casos,
  para não revelar se o e-mail existe.
- **CPF validado pelos dígitos verificadores**, não só pelo tamanho.

---

## Stack

PHP 8.3 · MySQL 8 · Apache · Docker · CSS puro

---

## Licença

MIT
