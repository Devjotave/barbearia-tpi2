# Barbearia do Rafa - Tecnologias para Internet II - Momento II

Sistema administrativo desenvolvido em HTML, CSS, JavaScript, PHP e MySQL/MariaDB.

## Requisitos

- XAMPP (Apache + MySQL/MariaDB)
- PHP 8+
- Navegador moderno

## Instalação em outra máquina

1. Inicie Apache e MySQL no XAMPP.
2. Importe o arquivo `banco/barbearia_rafa.sql` pelo phpMyAdmin.
3. Descompacte a pasta completa do projeto em qualquer diretório dentro de `htdocs`.
4. Caso o MySQL possua usuário/senha diferentes do padrão do XAMPP, ajuste `php/config.php`.
5. Acesse no navegador `http://localhost/NOME_DA_PASTA/login.html`.

O projeto usa apenas caminhos relativos. Portanto, o nome da pasta dentro de `htdocs` pode ser alterado sem quebrar os links.

## Acesso inicial

- E-mail: `admin@barbearia.local`
- Senha: `Admin@123`

Após entrar, o administrador pode cadastrar outros usuários no módulo **Usuários**.

## Funcionalidades

- Login consultando a tabela `usuarios`.
- Sessão PHP para manter o usuário autenticado.
- CRUD de usuários com perfis Administrador e Atendente.
- CRUD de clientes.
- CRUD de barbeiros.
- CRUD de serviços.
- CRUD de produtos.
- CRUD de agendamentos com relacionamentos entre cliente, barbeiro e serviço.
- Dashboard com contagens consultadas no banco.
- Validações no navegador, JavaScript e servidor PHP.

## Estrutura principal

- `banco/barbearia_rafa.sql`: criação completa do banco e dados iniciais.
- `php/config.php`: configuração da conexão MySQL.
- `php/conexao.php`: conexão PDO.
- `php/login.php`, `php/logout.php`, `php/sessao.php`: autenticação e sessão.
- `php/usuarios.php`: CRUD de usuários.
- Demais arquivos `php/*.php`: CRUDs e consultas dos módulos.
- `js/`: comunicação via Fetch API e comportamento das telas.
- `documentacao/`: relatório do Momento II.
