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

// Recebe o ID do agendamento a ser editado via parâmetro GET na URL; se não informado, assume 0
$id = $_GET['id'] ?? 0;

// Prepara e executa uma consulta para buscar o agendamento e o aluno em que ele foi feito
$sql = "SELECT p.*, c.nome, c.matricula FROM agendamentos p JOIN alunos c ON p.aluno_id = c.id WHERE p.id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$agendamento = $res->fetch_assoc();

// Se não encontrou o agendamento, exibe mensagem e termina a execução
if (!$agendamento) {
    echo "Agendamento não encontrado.";
    exit();
}

// Processa o formulário quando enviado via POST para atualizar o agendamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta os dados atualizados do formulário
    $data = $_POST['data'];
    $assunto = trim($_POST['assunto'] ?? '');
    $horario = $_POST['horario'] ?? '';

    // Validação simples: data e descrição são obrigatórias
    if (!$data || !$assunto || !$horario) {
        echo "Data e assunto são obrigatórios.";
    } else {
        // Atualiza a data (e a descrição e o horario) do agendamento no banco de dados
        $sql = "UPDATE agendamentos SET data=?, assunto=?, horario=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $data, $assunto, $horario, $id);
        $stmt->execute();

        // Redireciona o usuário de volta para o histórico após atualizar
        header("Location: historico.php");
        exit();
    }
}
?>
<!-- Folha de estilo padrão do sistema (todas as telas usam o mesmo arquivo css/style.css) -->
<link rel="stylesheet" href="css/style.css">
<div class="topline"><a class="brand" href="principal.php">EscolaConecta <small>Gestão escolar</small></a><nav><a href="principal.php">Início</a><a href="responsaveis.php">Responsáveis</a><a href="alunos.php">Alunos</a><a href="agendamentos.php">Agendamentos</a><a href="historico.php">Histórico</a></nav><a class="logout" href="logout.php">Sair</a></div>

<h2>Editar agendamento</h2>

<!-- Mostra em qual aluno o agendamento foi feito -->
<p>Aluno: <?= htmlspecialchars($agendamento['nome'] . ' - ' . $agendamento['matricula']) ?></p>

<!-- Formulário para edição do agendamento com dados já preenchidos -->
<form method="post">
    <label for="data">Data:</label>
    <input type="date" id="data" name="data" value="<?= htmlspecialchars($agendamento['data']) ?>" required>
    <br><br>

    <label for="assunto">Assunto:</label>
    <input type="text" id="assunto" name="assunto" value="<?= htmlspecialchars($agendamento['assunto']) ?>" required>
    <br><br>

    <label for="horario">Horário:</label>
    <input type="time" id="horario" name="horario" required value="<?= htmlspecialchars($agendamento['horario']) ?>">
    <br><br>

    <button>Salvar</button>
</form>

<!-- Link para voltar ao histórico -->
<a href="historico.php">Voltar</a>
