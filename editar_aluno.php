<?php

// Inicia a sessão para acessar os dados do usuário logado
session_start();

// Verifica se o usuário está autenticado; caso contrário, redireciona para a página de login
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

// Inclui o arquivo de configuração para estabelecer a conexão com a base de dados
include 'config.php';

// Recebe o ID do aluno a ser editado via parâmetro GET na URL; se não informado, assume 0
$id = $_GET['id'] ?? 0;

// Prepara e executa uma consulta para buscar os dados do aluno com o ID informado
$sql = "SELECT * FROM alunos WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$aluno = $res->fetch_assoc();

// Se não encontrou o aluno, exibe mensagem e termina a execução
if (!$aluno) {
    echo "Aluno não encontrado.";
    exit();
}

// Processa o formulário quando enviado via POST para atualizar o aluno
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta os dados atualizados do formulário
    $responsavel_id = (int)($_POST['responsavel_id'] ?? 0);
    $nome = trim($_POST['nome'] ?? '');
    $turma = trim($_POST['turma'] ?? '');
    $matricula = trim($_POST['matricula'] ?? '');
    $turno = trim($_POST['turno'] ?? '');
    $serie = trim($_POST['serie'] ?? '');

    // Validação simples: responsavel, nome e turma são obrigatórios
    if (!$responsavel_id || !$nome || !$turma) {
        echo "Responsável, nome e turma são obrigatórios.";
    } else {
        // Atualiza os dados do aluno no banco de dados
        $sql = "UPDATE alunos SET responsavel_id=?, nome=?, turma=?, matricula=?, turno=?, serie=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssssi", $responsavel_id, $nome, $turma, $matricula, $turno, $serie, $id);
        $stmt->execute();

        // Redireciona o usuário de volta para a lista de alunos após atualizar
        header("Location: alunos.php");
        exit();
    }
}

// Busca todos os responsaveis para preencher o select (permite trocar o responsavel do aluno)
$responsaveis = $conn->query("SELECT id, nome FROM responsavel ORDER BY nome");
?>
<!-- Folha de estilo padrão do sistema (todas as telas usam o mesmo arquivo css/style.css) -->
<link rel="stylesheet" href="css/style.css">
<div class="topline"><a class="brand" href="principal.php">EscolaConecta <small>Gestão escolar</small></a><nav><a href="principal.php">Início</a><a href="responsaveis.php">Responsáveis</a><a href="alunos.php">Alunos</a><a href="agendamentos.php">Agendamentos</a><a href="historico.php">Histórico</a></nav><a class="logout" href="logout.php">Sair</a></div>

<h2>Editar aluno</h2>

<!-- Formulário para edição do aluno com dados já preenchidos -->
<form method="post">
    <select name="responsavel_id" required>
        <!-- Deixa selecionado o responsavel atual do aluno -->
        <?php while ($d = $responsaveis->fetch_assoc()): ?>
            <option value="<?= $d['id'] ?>" <?= $d['id'] == $aluno['responsavel_id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['nome']) ?></option>
        <?php endwhile; ?>
    </select>
    <input name="nome" placeholder="Nome" value="<?= htmlspecialchars($aluno['nome']) ?>" required>
    <input name="turma" placeholder="Turma" value="<?= htmlspecialchars($aluno['turma']) ?>" required>
    <input name="matricula" placeholder="Matrícula" value="<?= htmlspecialchars($aluno['matricula'] ?? '') ?>">
    <input name="turno" placeholder="Turno" value="<?= htmlspecialchars($aluno['turno'] ?? '') ?>">
    <input type="text" name="serie" required placeholder="Série" value="<?= htmlspecialchars($aluno['serie'] ?? '') ?>">
    <button>Salvar</button>
</form>

<!-- Link para voltar à página de listagem de alunos -->
<a href="alunos.php">Voltar</a>
