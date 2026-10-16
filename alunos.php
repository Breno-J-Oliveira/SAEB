<?php

// Inicia a sessão para verificar se o usuário está autenticado
session_start();

// Se não houver usuário logado, redireciona para a página de login
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Verifica o tempo de expiração da sessão: se ficou mais de 30 minutos sem usar o sistema,
// encerra a sessão e volta para o login avisando que expirou
if (time() - ($_SESSION['ultimo_acesso'] ?? 0) > 30 * 60) {
    session_destroy();
    header('Location: login.php?expirou=1');
    exit();
}

// Ainda está no prazo: renova o horário do último acesso
$_SESSION['ultimo_acesso'] = time();

// Inclui o arquivo de configuração para conexão com o banco de dados
include 'config.php';

// Verifica se o formulário de cadastro foi enviado via POST e o botão 'add' foi clicado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    // Prepara uma consulta segura para inserir um novo aluno, evitando SQL Injection
    // O campo responsavel_id liga o aluno ao seu responsavel (um responsavel tem muitos alunos)
    $stmt = $conn->prepare(
        "INSERT INTO alunos (responsavel_id, nome, turma, matricula, turno, serie)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    // Liga os parâmetros enviados no formulário aos marcadores da consulta preparada
    $stmt->bind_param("isssss", $_POST['responsavel_id'], $_POST['nome'], $_POST['turma'], $_POST['matricula'], $_POST['turno'], $_POST['serie']);
    // Executa a inserção no banco
    $stmt->execute();
    // Após adicionar o aluno, redireciona para a mesma página para atualizar a lista
    header("Location: alunos.php");
    exit();
}

// Recebe o termo de busca enviado via GET, ou vazio se não houver
$busca = $_GET['busca'] ?? '';

// Protege o termo de busca contra SQL Injection antes de colocar na consulta
$termo = $conn->real_escape_string($busca);

// Monta a cláusula WHERE da consulta para filtrar alunos pelo nome ou matricula, se houver termo de busca
$where = $busca ? "WHERE c.nome LIKE '%$termo%' OR c.matricula LIKE '%$termo%'" : '';

// Consulta SQL que une alunos e responsavel (JOIN) para mostrar o nome do responsavel de cada aluno
//            $where
//            ORDER BY p.nome")
$query = "SELECT c.*, d.nome AS responsavel_nome
          FROM alunos c
          JOIN responsavel d ON c.responsavel_id = d.id
          $where
          ORDER BY c.nome";

// Executa a consulta e armazena o resultado
$alunos = $conn->query($query);

// Busca todos os responsaveis para preencher o select do formulário de cadastro
$responsaveis = $conn->query("SELECT id, nome FROM responsavel ORDER BY nome");
?>
<!-- Folha de estilo padrão do sistema (todas as telas usam o mesmo arquivo css/style.css) -->
<link rel="stylesheet" href="css/style.css">
<div class="topline"><a class="brand" href="principal.php">EscolaConecta <small>Gestão escolar</small></a><nav><a href="principal.php">Início</a><a href="responsaveis.php">Responsáveis</a><a href="alunos.php">Alunos</a><a href="agendamentos.php">Agendamentos</a><a href="historico.php">Histórico</a></nav><a class="logout" href="logout.php">Sair</a></div>

<h2>Cadastro de alunos</h2>

<!-- Formulário para pesquisa de alunos -->
<form method="get">
    <input name="busca" placeholder="Buscar por nome ou matrícula..." value="<?= htmlspecialchars($busca) ?>">
    <button type="submit">Buscar</button>
    <a href="principal.php">Voltar</a>
</form>

<!-- Tabela exibindo os alunos buscados/listados -->
<table border="1" cellpadding="5">
    <tr><th>Nome</th><th>Turma</th><th>Matrícula</th><th>Turno</th><th>Série</th><th>Responsável</th><th>Ações</th></tr>
    <?php while($row = $alunos->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['nome']) ?></td>
        <td><?= htmlspecialchars($row['turma']) ?></td>
        <td><?= htmlspecialchars($row['matricula'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['turno'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['serie'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['responsavel_nome']) ?></td>
        <td>
            <!-- Link para editar o aluno, passando o ID via GET -->
            <a href="editar_aluno.php?id=<?= $row['id'] ?>">Editar</a>
            <!-- Link para deletar o aluno, com confirmação (os agendamentos dele também são apagados) -->
            <a href="deletar_aluno.php?id=<?= $row['id'] ?>" onclick="return confirm('Excluir o aluno apaga também os agendamentos dele. Confirma?')">Excluir</a>
        </td>
      </tr>
    <?php endwhile; ?>
</table>

<!-- Formulário para cadastro de novo aluno -->
<h3>Novo aluno</h3>
<form method="post">
    <!-- Select com os responsaveis cadastrados: o horario enviado é o id do responsavel -->
    <select name="responsavel_id" required>
        <option value="">Selecione o responsável</option>
        <?php while ($d = $responsaveis->fetch_assoc()): ?>
            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nome']) ?></option>
        <?php endwhile; ?>
    </select>
    <input name="nome" required placeholder="Nome">
    <input name="turma" required placeholder="Turma">
    <input name="matricula" placeholder="Matrícula">
    <input name="turno" placeholder="Turno">
    <input type="text" name="serie" required placeholder="Série">
    <button name="add">Cadastrar</button>
</form>
