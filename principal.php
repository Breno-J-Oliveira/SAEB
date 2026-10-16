<?php
session_start();
if (!isset($_SESSION['usuario'])) { header('Location: login.php'); exit; }
if (time()-($_SESSION['ultimo_acesso']??0)>1800) { session_destroy(); header('Location: login.php?expirou=1'); exit; }
$_SESSION['ultimo_acesso']=time();
require 'config.php';
$counts=[];
foreach (['responsavel'=>'responsaveis','alunos'=>'alunos','agendamentos'=>'reunioes'] as $table=>$key) {
  $result=$conn->query("SELECT COUNT(*) total FROM $table");
  $counts[$key]=(int)$result->fetch_assoc()['total'];
}
$proximas=$conn->query("SELECT a.data,a.horario,a.assunto,l.nome aluno,r.nome responsavel FROM agendamentos a JOIN alunos l ON l.id=a.aluno_id JOIN responsavel r ON r.id=l.responsavel_id WHERE a.data>=CURDATE() ORDER BY a.data,a.horario LIMIT 5");
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Painel | EscolaConecta</title><link rel="stylesheet" href="css/style.css"></head><body>
<div class="topline"><a class="brand" href="principal.php">EscolaConecta <small>Gestão escolar</small></a><nav><a href="principal.php">Início</a><a href="responsaveis.php">Responsáveis</a><a href="alunos.php">Alunos</a><a href="agendamentos.php">Agendamentos</a><a href="historico.php">Histórico</a></nav><a class="logout" href="logout.php">Sair</a></div>
<div class="hero"><div><span class="eyebrow">PAINEL</span><h1>Olá, <?= htmlspecialchars($_SESSION['usuario']['nome']) ?>!</h1><p>Gerencie alunos, responsáveis e reuniões da escola.</p></div><a class="btn" href="agendamentos.php">+ Agendar reunião</a></div>
<div class="stats"><div class="stat"><span>Responsáveis</span><strong><?= $counts['responsaveis'] ?></strong><a href="responsaveis.php">Ver responsáveis →</a></div><div class="stat"><span>Alunos</span><strong><?= $counts['alunos'] ?></strong><a href="alunos.php">Ver alunos →</a></div><div class="stat"><span>Reuniões cadastradas</span><strong><?= $counts['reunioes'] ?></strong><a href="historico.php">Ver reuniões →</a></div></div>
<div class="panel"><div class="panel-heading"><h2>Próximas reuniões</h2><a href="historico.php">Ver histórico →</a></div><div class="table-wrap"><table><thead><tr><th>Data</th><th>Horário</th><th>Aluno</th><th>Responsável</th><th>Assunto</th></tr></thead><tbody>
<?php if ($proximas->num_rows===0): ?><tr><td colspan="5">Nenhuma reunião futura agendada.</td></tr><?php endif; ?>
<?php while($r=$proximas->fetch_assoc()): ?><tr><td><?= date('d/m/Y',strtotime($r['data'])) ?></td><td><?= htmlspecialchars($r['horario']) ?></td><td><?= htmlspecialchars($r['aluno']) ?></td><td><?= htmlspecialchars($r['responsavel']) ?></td><td><?= htmlspecialchars($r['assunto']) ?></td></tr><?php endwhile; ?>
</tbody></table></div></div><footer>EscolaConecta · Gestão escolar</footer></body></html>
