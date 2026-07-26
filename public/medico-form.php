<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/MedicoRepository.php';

sessao_iniciar();
auth_exigir_login();

$repositorio = MedicoRepository::criar();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$editando = $id > 0;
$erros = [];

$medico = [
    'nome' => '', 'cpf' => '', 'email' => '',
    'telefone' => '', 'especialidade' => '', 'crm' => '',
];

if ($editando) {
    $registro = $repositorio->buscarPorId($id);

    if ($registro === null) {
        flash('Médico não encontrado.', 'erro');
        redirecionar('medicos.php');
    }

    $medico = $registro;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();

    foreach (array_keys($medico) as $campo) {
        if ($campo !== 'id') {
            $medico[$campo] = trim((string) ($_POST[$campo] ?? ''));
        }
    }

    $senha = (string) ($_POST['senha'] ?? '');

    if ($medico['nome'] === '') {
        $erros['nome'] = 'Informe o nome.';
    }

    if (!cpf_valido((string) $medico['cpf'])) {
        $erros['cpf'] = 'CPF inválido.';
    }

    if (!filter_var($medico['email'], FILTER_VALIDATE_EMAIL)) {
        $erros['email'] = 'E-mail inválido.';
    } elseif ($repositorio->emailEmUso((string) $medico['email'], $editando ? $id : null)) {
        $erros['email'] = 'Já existe um cadastro com este e-mail.';
    }

    if ($medico['crm'] === '') {
        $erros['crm'] = 'Informe o CRM.';
    }

    // Na criação a senha é obrigatória; na edição, só valida se foi preenchida.
    if (!$editando && strlen($senha) < 8) {
        $erros['senha'] = 'A senha deve ter pelo menos 8 caracteres.';
    } elseif ($editando && $senha !== '' && strlen($senha) < 8) {
        $erros['senha'] = 'A nova senha deve ter pelo menos 8 caracteres.';
    }

    if ($erros === []) {
        $medico['cpf'] = preg_replace('/\D/', '', (string) $medico['cpf']);

        if ($editando) {
            $repositorio->atualizar($id, $medico, $senha !== '' ? $senha : null);
            flash('Cadastro atualizado com sucesso.');
        } else {
            $repositorio->inserir($medico, $senha);
            flash('Médico cadastrado com sucesso.');
        }

        redirecionar('medicos.php');
    }
}

$titulo = ($editando ? 'Editar' : 'Cadastrar') . ' médico — Sistema Médico';
require __DIR__ . '/../views/layout.php';
?>

<div class="cartao">
  <h1><?= $editando ? 'Editar cadastro' : 'Cadastrar médico' ?></h1>
  <p class="apoio">Campos marcados com * são obrigatórios.</p>

  <form method="post" class="formulario">
    <?= csrf_campo() ?>

    <div class="campo">
      <label for="nome">Nome *</label>
      <input id="nome" name="nome" type="text" required value="<?= e((string) $medico['nome']) ?>">
      <?php if (isset($erros['nome'])): ?><small class="erro"><?= e($erros['nome']) ?></small><?php endif; ?>
    </div>

    <div class="linha">
      <div class="campo">
        <label for="cpf">CPF *</label>
        <input id="cpf" name="cpf" type="text" required inputmode="numeric"
          value="<?= e(cpf_formatar((string) $medico['cpf'])) ?>">
        <?php if (isset($erros['cpf'])): ?><small class="erro"><?= e($erros['cpf']) ?></small><?php endif; ?>
      </div>

      <div class="campo">
        <label for="crm">CRM *</label>
        <input id="crm" name="crm" type="text" required value="<?= e((string) $medico['crm']) ?>">
        <?php if (isset($erros['crm'])): ?><small class="erro"><?= e($erros['crm']) ?></small><?php endif; ?>
      </div>
    </div>

    <div class="linha">
      <div class="campo">
        <label for="email">E-mail *</label>
        <input id="email" name="email" type="email" required value="<?= e((string) $medico['email']) ?>">
        <?php if (isset($erros['email'])): ?><small class="erro"><?= e($erros['email']) ?></small><?php endif; ?>
      </div>

      <div class="campo">
        <label for="telefone">Telefone</label>
        <input id="telefone" name="telefone" type="tel" value="<?= e((string) $medico['telefone']) ?>">
      </div>
    </div>

    <div class="campo">
      <label for="especialidade">Especialidade</label>
      <input id="especialidade" name="especialidade" type="text"
        value="<?= e((string) $medico['especialidade']) ?>">
    </div>

    <div class="campo">
      <label for="senha">Senha <?= $editando ? '' : '*' ?></label>
      <input id="senha" name="senha" type="password" autocomplete="new-password"
        <?= $editando ? '' : 'required' ?>>
      <small class="apoio">
        <?= $editando ? 'Deixe em branco para manter a senha atual.' : 'Mínimo de 8 caracteres.' ?>
      </small>
      <?php if (isset($erros['senha'])): ?><small class="erro"><?= e($erros['senha']) ?></small><?php endif; ?>
    </div>

    <div class="acoes">
      <button class="btn btn--primario" type="submit"><?= $editando ? 'Salvar' : 'Cadastrar' ?></button>
      <a class="btn btn--sutil" href="medicos.php">Cancelar</a>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../views/layout-fim.php'; ?>
