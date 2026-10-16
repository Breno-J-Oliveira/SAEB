<div align="center">

# EscolaConecta

**Sistema de gestão escolar e agendamento de reuniões**

Organização de responsáveis, alunos e reuniões escolares em uma aplicação web simples, construída com PHP e MySQL.

![PHP](https://img.shields.io/badge/PHP-8%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Banco%20de%20Dados-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-Interface-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-Estilos-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![XAMPP](https://img.shields.io/badge/XAMPP-Ambiente%20Local-FB7A24?style=for-the-badge&logo=xampp&logoColor=white)

**Projeto didático • PHP + MySQL • Execução local com XAMPP**

</div>

---

## O que é o EscolaConecta?

O **EscolaConecta** é um sistema web de administração escolar criado a partir da estrutura de um sistema de oficina mecânica. Sua proposta é **organizar responsáveis, alunos e reuniões** sem acrescentar funcionalidades desnecessárias.

A adaptação preserva a lógica de cadastros, relacionamentos, autenticação e operações CRUD da aplicação original:

| Oficina | EscolaConecta |
|---|---|
| Donos | Responsáveis (pais ou responsáveis legais) |
| Carros | Alunos |
| Procedimentos | Agendamentos de reuniões |

Este repositório representa um **projeto acadêmico para aprendizado e execução local**, não uma solução pronta para armazenar dados reais de estudantes em produção.

---

## Funcionalidades

### Autenticação e painel

- Acesso com nome de usuário e senha armazenada como **hash** (`password_hash` / `password_verify`).
- Sessão PHP com expiração após **30 minutos de inatividade**.
- Logout e redirecionamento para a tela de login.
- Painel com total de responsáveis, alunos e reuniões cadastradas.
- Listagem das **cinco próximas reuniões**, quando existirem.

### Responsáveis

- Cadastro: nome, CPF, telefone e e-mail.
- Consulta e busca por nome.
- Edição e exclusão de cadastros.
- CPF armazenado criptografado no MySQL com `AES_ENCRYPT`.

### Alunos

- Cadastro: nome, turma, matrícula, turno, série e responsável.
- Pesquisa por nome ou matrícula.
- Edição e exclusão.
- Vínculo de cada aluno com **um responsável**.

### Reuniões

- Agendamento de reunião com aluno, data, horário e assunto.
- Alteração da data, horário e assunto de uma reunião existente.
- Histórico com informações da reunião, aluno e responsável reunidas por `JOIN`.

> **Escopo atual:** não há confirmação por e-mail, envio de notificações, cancelamento individual de reunião, controle de conflitos de horários ou níveis de permissão distintos.

---

## Como funciona?

```text
Funcionário da escola
         |
         v
    Login (PHP)
         |
         v
  Painel principal
         |
         +---- Responsáveis
         |         |
         |         +---- Alunos vinculados
         |                      |
         |                      +---- Agendamentos
         |
         +---- Histórico de reuniões (JOIN)
```

Cada reunião pertence a um aluno; cada aluno pertence a um responsável. O banco aplica `ON DELETE CASCADE` aos vínculos: **excluir um responsável remove seus alunos e as reuniões deles**; excluir um aluno remove suas reuniões.

---

## Tecnologias

| Camada | Tecnologia | Utilização |
|---|---|---|
| Back-end | PHP | Processamento de formulários e sessões |
| Banco de dados | MySQL | Tabelas, relacionamentos e consultas |
| Acesso ao banco | MySQLi | Queries e instruções preparadas |
| Front-end | HTML5 + CSS3 | Telas, formulários, tabelas e estilos |
| Servidor local | Apache (XAMPP) | Execução da aplicação |
| Administração do banco | phpMyAdmin | Importação e consulta do SQL |

Não há dependências do Composer, framework JavaScript ou processo de build no projeto atual.

---

## Como executar (Windows + XAMPP)

### Pré-requisitos

- XAMPP com **Apache, MySQL e PHP** instalados.
- phpMyAdmin (normalmente incluído no XAMPP).
- Um navegador moderno.

### 1. Coloque os arquivos no servidor

Extraia a pasta `escola` dentro de:

```text
C:\xampp\htdocs\escola
```

### 2. Inicie Apache e MySQL

Abra o painel de controle do XAMPP e inicie **Apache** e **MySQL**. Acesse:

- phpMyAdmin: http://localhost/phpmyadmin/

### 3. Importe o banco de dados

No phpMyAdmin, clique em **Importar** e selecione o arquivo [`saep_db.sql`](saep_db.sql).

O script cria o banco **`escola_db`**, quatro tabelas e alguns registros fictícios de exemplo. Os dados de usuários **não** incluem uma conta de login pronta.

> Evite importar o script de dados repetidamente em um banco já utilizado; os comandos `INSERT` de demonstração podem criar registros duplicados.

### 4. Configure a conexão

Em [`config.php`](config.php), os valores padrão para um XAMPP local são:

| Configuração | Valor padrão |
|---|---|
| Servidor MySQL | `localhost` |
| Usuário MySQL | `root` |
| Senha MySQL | vazia, ou variável `DB_PASSWORD` |
| Banco | `escola_db` ou variável `DB_NAME` |
| Chave de CPF | `troque-esta-chave-local` ou variável `CPF_ENCRYPTION_KEY` |

**Importante:** a chave `CPF_ENCRYPTION_KEY` precisa ser a mesma usada na criptografia dos registros importados pelo SQL. A chave padrão serve **somente para testes locais**.

### 5. Crie um usuário administrador de teste

Abra o PowerShell e execute:

```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\escola\criar_usuario.php admin Admin@123
```

O script cria o login no banco usando um hash de senha. Se o nome de usuário já existir, a restrição `UNIQUE` da tabela `usuarios` impedirá outro cadastro igual.

### 6. Acesse o sistema

Abra **http://localhost/escola/** e use:

| Campo | Credencial de teste |
|---|---|
| Usuário | `admin` |
| Senha | `Admin@123` |

**Essas credenciais não existem até você executar o passo 5.** Não utilize a senha de exemplo fora do ambiente de testes.

---

## Estrutura do projeto

```text
escola/
├── css/
│   └── style.css                # Aparência das páginas
├── docs/
│   └── Guia_de_Estudos_EscolaConecta.docx
├── index.php                    # Redirecionamento inicial
├── login.php                    # Autenticação
├── logout.php                   # Encerramento da sessão
├── principal.php                # Painel com contadores e próximas reuniões
├── config.php                   # Conexão com MySQL e chave do CPF
├── responsaveis.php             # Cadastro e consulta de responsáveis
├── editar_responsavel.php       # Edição de responsável
├── deletar_responsavel.php      # Exclusão de responsável
├── alunos.php                   # Cadastro e consulta de alunos
├── editar_aluno.php             # Edição de aluno
├── deletar_aluno.php            # Exclusão de aluno
├── agendamentos.php             # Cadastro de reuniões
├── editar_agendamento.php       # Edição de reuniões
├── historico.php                # Histórico com JOIN das três tabelas
├── criar_usuario.php           # Criação de login via terminal
├── saep_db.sql                  # Banco e dados fictícios
└── README.md                    # Este arquivo
```

---

## Banco de dados

O arquivo [`saep_db.sql`](saep_db.sql) define:

| Tabela | Função | Relação |
|---|---|---|
| `usuarios` | Credenciais de acesso | Independente |
| `responsavel` | Informações dos pais ou responsáveis | Um para muitos alunos |
| `alunos` | Dados escolares do estudante | Pertence a um responsável |
| `agendamentos` | Reunião com data, assunto e horário | Pertence a um aluno |

**Exemplo do JOIN usado no histórico:**

```sql
SELECT a.data, a.horario, a.assunto,
       l.nome AS aluno, r.nome AS responsavel
FROM agendamentos a
JOIN alunos l ON l.id = a.aluno_id
JOIN responsavel r ON r.id = l.responsavel_id
ORDER BY a.data;
```

O histórico real apresenta também turma, matrícula, turno, série e informações do responsável.

---

## Segurança e limitações

O EscolaConecta utiliza algumas práticas adequadas ao aprendizado: senha com hash, sessões PHP, consultas preparadas nas operações principais e criptografia de CPF no banco.

**Atenção antes de qualquer publicação na internet:**

- A exclusão de responsáveis e alunos é acionada atualmente por **links GET**; uma aplicação real deveria usar POST, validação de autorização e proteção CSRF.
- A chave de CPF de demonstração precisa ser substituída e administrada fora do código e do SQL, com estratégia de migração dos dados já criptografados.
- As consultas atuais ainda podem ser aprimoradas na parametrização, validação de entradas e tratamento de erros.
- Não há política de permissões por cargo, auditoria ou testes de segurança abrangentes.
- Por envolver informações de estudantes e responsáveis, **não use dados pessoais reais** nesta versão didática sem melhorias de proteção e revisão de conformidade com a LGPD.

---

## Testes sugeridos

- [ ] Fazer login com as credenciais de teste.
- [ ] Verificar a mensagem para senha inválida.
- [ ] Confirmar proteção de páginas sem sessão.
- [ ] Cadastrar, buscar e editar um responsável.
- [ ] Cadastrar, buscar e editar um aluno associado.
- [ ] Criar e editar uma reunião.
- [ ] Consultar o histórico e conferir os dados relacionados.
- [ ] Validar o comportamento de exclusão em cascata com dados fictícios.
- [ ] Verificar o logout e a expiração de sessão.

**Status:** checklist proposto para validação manual. Não equivale a testes automatizados ou integração local já executada.

---

## Documentação

A apostila adaptada do modelo de **Guia SAEP** está disponível em:

📘 **[`docs/Guia_de_Estudos_EscolaConecta.docx`](docs/Guia_de_Estudos_EscolaConecta.docx)**

Ela inclui equivalências entre oficina e escola, esquema SQL, explicações do CRUD, autenticação, reuniões, `JOIN`, instalação com XAMPP, exemplos de código e checklist de testes.

---

## Próximos passos possíveis

| Melhoria | Situação |
|---|---|
| Evitar agendamentos com conflito de horário | Não implementado |
| Cancelar reunião individualmente | Não implementado |
| Melhorar exclusões com POST e CSRF | Recomendado |
| Refinar validação de formulários | Recomendado |
| Diferenciar acesso da secretaria e coordenação | Não implementado |

---

## Autor e contato

Projeto acadêmico baseado na conversão de uma aplicação PHP para o contexto escolar.

[![GitHub](https://img.shields.io/badge/GitHub-Breno--J--Oliveira-181717?style=for-the-badge&logo=github&logoColor=white)](https://github.com/Breno-J-Oliveira)

---

<div align="center">

**EscolaConecta — uma solução escolar simples, com foco em aprender, organizar e evoluir.**

</div>
