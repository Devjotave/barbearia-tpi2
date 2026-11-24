\begin{titlepage}
\centering
\vspace*{2.5cm}
{\Large UNIVERSIDADE DE UBERABA\par}
\vspace{0.5cm}
{\large Curso de Sistemas de Informação\par}
\vspace{2.5cm}
{\LARGE\bfseries TECNOLOGIAS PARA INTERNET II\par}
\vspace{0.7cm}
{\Large Trabalho - Momento II\par}
\vspace{0.4cm}
{\Large Barbearia do Rafa - Sistema Administrativo\par}
\vfill
{\large João Vitor Cardoso de Paula - RA 5174971\par}
{\large Mateus Almeida Oliveira - RA 5170787\par}
\vspace{1.0cm}
{\large Professor: Luiz Carlos Felix Carvalho\par}
\vfill
{\large Uberlândia - MG\par}
{\large 2026\par}
\end{titlepage}

## 1. Introdução

Este relatório apresenta a evolução do sistema administrativo Barbearia do Rafa para o Momento II da disciplina Tecnologias para Internet II. A versão anterior utilizava formulários HTML, JavaScript para envio de requisições e PHP para validações. Nesta etapa, o sistema foi integrado a um banco de dados MySQL/MariaDB e passou a possuir autenticação por usuários armazenados no banco e controle de sessão em PHP.

## 2. Banco de dados

O arquivo completo para criação e instalação do banco encontra-se em:

`banco/barbearia_rafa.sql`

O script cria o banco `barbearia_rafa`, remove e recria as tabelas necessárias, define chaves primárias e estrangeiras e insere um usuário administrador inicial. Também são inseridos alguns registros de demonstração para facilitar os testes.

As tabelas utilizadas são: `usuarios`, `clientes`, `barbeiros`, `servicos`, `produtos`, `agendamentos`, `vendas` e `pagamentos`.

## 3. Autenticação e sessão

A tela `login.html` envia e-mail e senha para `php/login.php` por meio de JavaScript e Fetch API. O PHP consulta a tabela `usuarios`, verifica se o usuário está ativo e valida a senha com `password_verify`. Após o login, os dados essenciais do usuário são armazenados em `$_SESSION`.

Os módulos protegidos consultam `php/sessao.php` para verificar a autenticação. Os endpoints PHP também validam a sessão no servidor, evitando acesso às operações apenas por chamada direta das URLs.

## 4. CRUD de usuários

O módulo de usuários permite cadastrar, listar, editar e excluir usuários do sistema. As senhas são armazenadas com hash criado por `password_hash`. O sistema impede a exclusão do próprio usuário logado e evita que o último administrador ativo seja removido ou inativado.

## 5. Comunicação com o banco nas telas

Todos os módulos existentes do sistema passaram a utilizar o banco de dados:

- Clientes: cadastro, listagem, edição e exclusão.
- Barbeiros: cadastro, listagem, edição, exclusão e controle de situação.
- Serviços: cadastro, listagem, edição, exclusão e controle de situação.
- Produtos: cadastro, listagem, edição, exclusão e controle de estoque.
- Agendamentos: cadastro, listagem, edição e exclusão, com cliente, barbeiro e serviço carregados do banco.
- Página inicial: exibe indicadores consultados diretamente no banco.
- Usuários: CRUD completo e base para autenticação.

## 6. Regras adicionais preservadas e ampliadas

Foram mantidas as lógicas do Momento I e integradas à persistência. O módulo de clientes calcula a idade; barbeiros calcula um exemplo de comissão; serviços calcula o valor equivalente por hora; produtos calcula o valor estimado do estoque e informa estoque baixo; agendamentos valida data futura para atendimentos ativos, horário entre 08:00 e 19:00, bloqueio de domingos e forma de confirmação.

## 7. Portabilidade

Não foram utilizados links com nome fixo de diretório dentro do `htdocs`. As páginas usam caminhos relativos, por exemplo `php/clientes.php`, `css/style.css` e `index.html`. Assim, a pasta do sistema pode ser descompactada com qualquer nome dentro de `htdocs`.

Para instalar em outra máquina, basta importar `banco/barbearia_rafa.sql`, colocar a pasta completa dentro de `htdocs`, ajustar `php/config.php` caso as credenciais do banco sejam diferentes e acessar `login.html` pelo `localhost`.

## 8. Credencial inicial

O script SQL cria o seguinte usuário para o primeiro acesso:

- E-mail: `admin@barbearia.local`
- Senha: `Admin@123`

Depois do login, outros usuários podem ser cadastrados pelo módulo Usuários.

## 9. Conclusão

O Momento II integrou persistência e autenticação ao sistema desenvolvido anteriormente. O projeto agora conecta HTML, JavaScript, PHP e MySQL/MariaDB, utiliza sessão para manter o usuário autenticado, possui CRUD de usuários e mantém os módulos administrativos integrados ao banco. Os caminhos relativos permitem executar o sistema em outra instalação do XAMPP sem depender do nome da pasta.

## 10. Referências

PHP Documentation. https://www.php.net/  
MDN Web Docs - Fetch API. https://developer.mozilla.org/  
MySQL Reference Manual. https://dev.mysql.com/doc/  
