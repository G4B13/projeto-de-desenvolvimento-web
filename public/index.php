<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/MedicoRepository.php';

sessao_iniciar();
auth_exigir_login();

$repositorio = MedicoRepository::criar();
$total = $repositorio->total();
$usuario = auth_usuario();

$titulo = 'Início — Sistema Médico';
require __DIR__ . '/../views/layout.php';
?>

<h1>Olá, <?= e($usuario['nome']) ?></h1>
<p class="apoio">Painel de gestão do corpo clínico.</p>

<div class="indicadores">
  <div class="indicador">
    <span class="indicador-rotulo">Médicos cadastrados</span>
    <strong class="indicador-valor"><?= $total ?></strong>
  </div>
</div>

<div class="acoes">
  <a class="btn btn--primario" href="medico-form.php">Cadastrar médico</a>
  <a class="btn btn--sutil" href="medicos.php">Ver cadastros</a>
</div>

<?php require __DIR__ . '/../views/layout-fim.php'; ?>
