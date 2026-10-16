<?php
// CLI somente: php criar_usuario.php "Nome" "SenhaSegura"
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Acesso restrito'); }
if ($argc < 3) { exit("Uso: php criar_usuario.php \"Nome\" \"Senha\"\n"); }
require 'config.php';
$hash=password_hash($argv[2], PASSWORD_DEFAULT);
$stmt=$conn->prepare('INSERT INTO usuarios(nome,senha) VALUES(?,?)');$stmt->bind_param('ss',$argv[1],$hash);
$stmt->execute(); echo "Usuário criado.\n";
