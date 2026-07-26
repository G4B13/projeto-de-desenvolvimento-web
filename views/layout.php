<?php

declare(strict_types=1);

/**
 * Abre o layout. As páginas definem $titulo antes de incluir este arquivo
 * e chamam layout_fim() ao final.
 */

$usuario = auth_usuario();
$aviso = flash();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($titulo ?? 'Sistema Médico') ?></title>
  <link rel="stylesheet" href="assets/css/app.css">
</head>

<body>
  <?php if ($usuario !== null): ?>
    <header class="topo">
      <div class="wrap topo-inner">
        <a class="marca" href="index.php">Sistema<span>Médico</span></a>
        <nav class="menu">
          <a href="index.php">Início</a>
          <a href="medicos.php">Médicos</a>
        </nav>
        <div class="usuario">
          <span><?= e($usuario['nome']) ?></span>
          <a class="btn btn--sutil" href="logout.php">Sair</a>
        </div>
      </div>
    </header>
  <?php endif; ?>

  <main class="wrap conteudo">
    <?php if ($aviso !== null): ?>
      <div class="aviso aviso--<?= e($aviso['tipo']) ?>"><?= e($aviso['mensagem']) ?></div>
    <?php endif; ?>
