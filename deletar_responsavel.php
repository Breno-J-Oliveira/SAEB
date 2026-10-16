<?php

// Inicia a sessão para gerenciar autenticação do usuário
session_start();

// Verifica se o usuário está logado, caso contrário redireciona para a página de login
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

// Obtém o ID do responsavel a ser deletado passado via URL (método GET), 0 se não informado
$id = $_GET['id'] ?? 0;

// Prepara a instrução SQL para deletar o responsavel com o ID especificado, prevenindo SQL Injection
// Como a chave estrangeira usa ON DELETE CASCADE, o banco apaga junto os alunos
// desse responsavel e os agendamentos desses alunos
$sql = "DELETE FROM responsavel WHERE id=?";

// Prepara o comando para execução
$stmt = $conn->prepare($sql);

// Associa o parâmetro ID à instrução SQL
$stmt->bind_param('i', $id);

// Executa a instrução de exclusão
$stmt->execute();

// Após excluir, redireciona o usuário de volta para a página de listagem de responsaveis
header("Location: responsaveis.php");

// Encerra o script para garantir que o redirecionamento ocorra imediatamente
exit();
