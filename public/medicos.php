<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/MedicoRepository.php';

sessao_iniciar();
auth_exigir_login();

$busca = trim((string) ($_GET['busca'] ?? ''));
$medicos = MedicoRepository::criar()->listar($busca);

$titulo = 'Médicos — Sistema Médico';
require __DIR__ . '/../views/layout.php';
?>

<div class="cabecalho-pagina">
  <div>
    <h1>Médicos cadastrados</h1>
    <p class="apoio"><?= count($medicos) ?> registro(s) encontrado(s).</p>
  </div>
  <a class="btn btn--primario" href="medico-form.php">Cadastrar médico</a>
</div>

<form method="get" class="busca">
  <input type="search" name="busca" placeholder="Buscar por nome, e-mail, CRM ou especialidade"
    value="<?= e($busca) ?>">
  <button class="btn btn--sutil" type="submit">Buscar</button>
  <?php if ($busca !== ''): ?>
    <a class="btn btn--sutil" href="medicos.php">Limpar</a>
  <?php endif; ?>
</form>

<?php if ($medicos === []): ?>
  <div class="vazio">
    <p>Nenhum médico encontrado<?= $busca !== '' ? ' para essa busca' : '' ?>.</p>
  </div>
<?php else: ?>
  <div class="tabela-rolagem">
    <table class="tabela">
      <thead>
        <tr>
          <th>Nome</th>
          <th>CRM</th>
          <th>Especialidade</th>
          <th>E-mail</th>
          <th>Telefone</th>
          <th class="col-acoes">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($medicos as $medico): ?>
          <tr>
            <td>
              <strong><?= e($medico['nome']) ?></strong>
              <small><?= e(cpf_formatar((string) $medico['cpf'])) ?></small>
            </td>
            <td><?= e($medico['crm']) ?></td>
            <td><?= e($medico['especialidade']) ?></td>
            <td><?= e($medico['email']) ?></td>
            <td><?= e($medico['telefone']) ?></td>
            <td class="col-acoes">
              <a class="btn btn--sutil btn--pequeno"
                href="medico-form.php?id=<?= (int) $medico['id'] ?>">Editar</a>

              <?php /* Exclusão é POST + CSRF: um GET permitiria apagar via link. */ ?>
              <form method="post" action="medico-excluir.php" class="embutido"
                onsubmit="return confirm('Excluir <?= e($medico['nome']) ?>? Esta ação não pode ser desfeita.')">
                <?= csrf_campo() ?>
                <input type="hidden" name="id" value="<?= (int) $medico['id'] ?>">
                <button class="btn btn--perigo btn--pequeno" type="submit">Excluir</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../views/layout-fim.php'; ?>
