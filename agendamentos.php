<?php

// Inicia a sessão para acessar dados do usuário logado e verificar autenticação
session_start();

// Se o usuário não estiver autenticado, redireciona para a página de login e encerra o script
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
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

// Inclui o arquivo de configuração que contém a conexão com o banco de dados
include 'config.php';

// Inicializa variável para armazenar mensagens de alerta que poderão ser exibidas para o usuário
$alerta = '';

// Verifica se o formulário foi submetido via método POST para registrar o agendamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe e sanitiza os dados enviados pelo formulário
    $aluno_id = intval($_POST['aluno_id']); // Aluno em que o agendamento foi feito
    $data = $_POST['data'];
    $assunto = trim($_POST['assunto'] ?? '');
    $horario = $_POST['horario'] ?? '';

    // Verifica se os campos obrigatórios foram preenchidos
    if (!$aluno_id || !$data || !$assunto || !$horario) {
        $alerta = "Aluno, data, assunto e horário são obrigatórios.";
    } else {
        // Prepara a inserção do agendamento no banco de dados
        // O campo aluno_id liga o agendamento ao aluno (um aluno tem muitos agendamentos)
        $stmt = $conn->prepare("INSERT INTO agendamentos (aluno_id, data, assunto, horario) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $aluno_id, $data, $assunto, $horario);
        $stmt->execute();

        // Redireciona para o histórico para mostrar o agendamento registrado
        header('Location: historico.php');
        exit;
    }
}

// Consulta SQL para buscar todos os alunos (com o nome do responsavel) para popular o select no formulário
//            ORDER BY p.nome")
$sql = "SELECT c.id, c.nome, c.matricula, d.nome AS responsavel_nome
        FROM alunos c
        JOIN responsavel d ON c.responsavel_id = d.id
        ORDER BY c.nome";
$res = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Agendar reunião</title>
    <!-- Folha de estilo padrão do sistema (todas as telas usam o mesmo arquivo css/style.css) -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body><div class="topline"><a class="brand" href="principal.php">EscolaConecta <small>Gestão escolar</small></a><nav><a href="principal.php">Início</a><a href="responsaveis.php">Responsáveis</a><a href="alunos.php">Alunos</a><a href="agendamentos.php">Agendamentos</a><a href="historico.php">Histórico</a></nav><a class="logout" href="logout.php">Sair</a></div>

<h2>Agendar reunião escolar</h2>

<!-- Exibe mensagem de alerta em destaque, caso tenha sido definida -->
<?php if ($alerta): ?>
    <div style="color: red; font-weight: bold; margin-bottom: 1em;">
        <?= htmlspecialchars($alerta) ?>
    </div>
<?php endif; ?>

<!-- Formulário de registro do agendamento -->
<form action="" method="post">
    <label for="aluno_id">Aluno:</label>
    <select id="aluno_id" name="aluno_id" required>
        <!-- Preenche o select com os alunos obtidos do banco (nome, matricula e responsavel) -->
        <?php while ($row = $res->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nome'] . ' - ' . $row['matricula'] . ' (' . $row['responsavel_nome'] . ')') ?></option>
        <?php endwhile; ?>
    </select>
    <br><br>

    <label for="data">Data:</label>
    <input type="date" id="data" name="data" required>
    <br><br>

    <label for="assunto">Assunto:</label>
    <input type="text" id="assunto" name="assunto" required>
    <br><br>

    <label for="horario">Horário:</label>
    <input type="time" id="horario" name="horario" required>
    <br><br>

    <button type="submit">Registrar</button>
</form>

<br>
<a href="principal.php">Voltar</a>

</body>
</html>
