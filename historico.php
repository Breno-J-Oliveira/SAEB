<?php

// Inicia a sessão para acessar dados do usuário autenticado
session_start();

// Verifica se o usuário está logado; se não, redireciona para a página de login
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

// Monta a consulta SQL para buscar o histórico de agendamentos
// Traz TODOS os dados do aluno (nome, turma, matricula, turno e serie) e do responsavel (nome, CPF, telefone e e-mail)
// A consulta une as tabelas agendamentos, alunos e responsavel seguindo os relacionamentos:
// agendamentos.aluno_id -> alunos.id e alunos.responsavel_id -> responsavel.id
// Os registros são ordenados pela data do agendamento (do mais antigo para o mais novo)
// Prova 01 (por dia): ORDER BY p.data
// Prova 02 (por mês): ORDER BY MONTH(p.data), p.data
// Prova 03 (por serie): ORDER BY YEAR(p.data), p.data
//                   t.telefone, t.email
//            FROM agendamentos a
//            ORDER BY a.data")
$sql = "SELECT p.id, p.data, p.assunto, p.horario,
               c.nome, c.turma, c.matricula, c.turno, c.serie,
               d.nome AS responsavel_nome, CAST(AES_DECRYPT(d.cpf, '$chave') AS CHAR) AS responsavel_cpf,
               d.telefone, d.email
        FROM agendamentos p
        JOIN alunos c ON p.aluno_id = c.id
        JOIN responsavel d ON c.responsavel_id = d.id
        ORDER BY p.data";

// Executa a consulta no banco de dados
$result = $conn->query($sql);
?>
<!-- Folha de estilo padrão do sistema (todas as telas usam o mesmo arquivo css/style.css) -->
<link rel="stylesheet" href="css/style.css">
<div class="topline"><a class="brand" href="principal.php">EscolaConecta <small>Gestão escolar</small></a><nav><a href="principal.php">Início</a><a href="responsaveis.php">Responsáveis</a><a href="alunos.php">Alunos</a><a href="agendamentos.php">Agendamentos</a><a href="historico.php">Histórico</a></nav><a class="logout" href="logout.php">Sair</a></div>

<!-- Título da página -->
<h2>Histórico de Agendamentos</h2>

<!-- Tabela para exibir o histórico com todos os dados do agendamento, do aluno e do responsavel -->
<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Data</th>
        <th>Agendamento</th>
        <th>Horário</th>
        <th>Nome</th>
        <th>Turma</th>
        <th>Matrícula</th>
        <th>Turno</th>
        <th>Série</th>
        <th>Responsável</th>
        <th>CPF</th>
        <th>Telefone</th>
        <th>E-mail</th>
        <th>Ações</th>
    </tr>

    <!-- Iteração sobre cada registro retornado do banco para popular as linhas da tabela -->
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <!-- Exibe o ID do agendamento -->
        <td><?= intval($row['id']) ?></td>
        <!-- Exibe a data do agendamento no formato dia/mês/serie -->
        <td><?= date('d/m/Y', strtotime($row['data'])) ?></td>
        <!-- Exibe a descrição do agendamento realizado -->
        <td><?= htmlspecialchars($row['assunto']) ?></td>
        <!-- Exibe o horario do agendamento com duas casas decimais -->
        <td><?= htmlspecialchars(substr((string)$row['horario'], 0, 5)) ?></td>
        <!-- Dados do aluno -->
        <td><?= htmlspecialchars($row['nome']) ?></td>
        <td><?= htmlspecialchars($row['turma']) ?></td>
        <td><?= htmlspecialchars($row['matricula'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['turno'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['serie'] ?? '') ?></td>
        <!-- Dados do responsavel (o CPF aparece descriptografado) -->
        <td><?= htmlspecialchars($row['responsavel_nome']) ?></td>
        <td><?= htmlspecialchars($row['responsavel_cpf'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['telefone'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
        <td>
            <!-- Link para editar o agendamento (atualizar a data), passando o ID via GET -->
            <a href="editar_agendamento.php?id=<?= $row['id'] ?>">Editar</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<!-- Link para voltar à página principal do sistema -->
<a href="principal.php">Voltar</a>
