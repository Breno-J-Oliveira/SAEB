CREATE DATABASE IF NOT EXISTS escola_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE escola_db;

CREATE TABLE usuarios (id INT AUTO_INCREMENT PRIMARY KEY, nome VARCHAR(100) NOT NULL UNIQUE, senha VARCHAR(255) NOT NULL);
CREATE TABLE responsavel (id INT AUTO_INCREMENT PRIMARY KEY, nome VARCHAR(100) NOT NULL, cpf VARBINARY(255) NOT NULL, telefone VARCHAR(20), email VARCHAR(100));
CREATE TABLE alunos (id INT AUTO_INCREMENT PRIMARY KEY, responsavel_id INT NOT NULL, nome VARCHAR(100) NOT NULL, turma VARCHAR(50) NOT NULL, matricula VARCHAR(30), turno VARCHAR(30), serie VARCHAR(30), FOREIGN KEY (responsavel_id) REFERENCES responsavel(id) ON DELETE CASCADE);
CREATE TABLE agendamentos (id INT AUTO_INCREMENT PRIMARY KEY, aluno_id INT NOT NULL, data DATE NOT NULL, assunto VARCHAR(255) NOT NULL, horario VARCHAR(5) NOT NULL, FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE);

-- Crie um usuário com: php criar_usuario.php (no terminal, após configurar acesso ao banco)
-- Ajuste a chave CPF_ENCRYPTION_KEY no ambiente ANTES de inserir dados.
INSERT INTO responsavel (nome,cpf,telefone,email) VALUES
('Ana Souza',AES_ENCRYPT('11122233344','troque-esta-chave-local'),'(11) 99999-1111','ana@email.com'),
('Pedro Lima',AES_ENCRYPT('55566677788','troque-esta-chave-local'),'(11) 98888-2222','pedro@email.com'),
('Lucia Alves',AES_ENCRYPT('99900011122','troque-esta-chave-local'),'(11) 97777-3333','lucia@email.com');
INSERT INTO alunos (responsavel_id,nome,turma,matricula,turno,serie) VALUES
(1,'Mariana Souza','Turma A','MAT2026001','Manhã','7º ano'),
(1,'Lucas Souza','Turma B','MAT2026002','Tarde','5º ano'),
(2,'Gabriel Lima','Turma C','MAT2026003','Manhã','8º ano'),
(3,'Laura Alves','Turma D','MAT2026004','Tarde','6º ano');
INSERT INTO agendamentos(aluno_id,data,assunto,horario) VALUES
(1,'2026-10-20','Reunião de acompanhamento pedagógico','09:00'),
(2,'2026-10-21','Conversa sobre desempenho escolar','14:30'),
(3,'2026-10-22','Reunião com coordenação','10:00');
