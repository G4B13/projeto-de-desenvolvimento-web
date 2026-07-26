<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/auth.php';

sessao_iniciar();

if (auth_usuario() !== null) {
    redirecionar('index.php');
}

$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();

    $email = trim((string) ($_POST['email'] ?? ''));
    $senha = (string) ($_POST['senha'] ?? '');

    if ($email === '' || $senha === '') {
        $erro = 'Informe e-mail e senha.';
    } elseif (auth_login($email, $senha)) {
        redirecionar('index.php');
    } else {
        // Mensagem genérica de propósito: não revela se o e-mail existe.
        $erro = 'E-mail ou senha incorretos.';
    }
}

$titulo = 'Entrar — Sistema Médico';
require __DIR__ . '/../views/layout.php';
?>

<div class="cartao cartao--estreito">
  <h1>Entrar no sistema</h1>
  <p class="apoio">Acesso restrito a profissionais cadastrados.</p>

  <?php if ($erro !== null): ?>
    <div class="aviso aviso--erro"><?= e($erro) ?></div>
  <?php endif; ?>

  <form method="post" class="formulario">
    <?= csrf_campo() ?>

    <div class="campo">
      <label for="email">E-mail</label>
      <input id="email" name="email" type="email" required autocomplete="username"
        value="<?= e($_POST['email'] ?? '') ?>">
    </div>

    <div class="campo">
      <label for="senha">Senha</label>
      <input id="senha" name="senha" type="password" required autocomplete="current-password">
    </div>

    <button class="btn btn--primario btn--bloco" type="submit">Entrar</button>
  </form>
</div>

<?php require __DIR__ . '/../views/layout-fim.php'; ?>
