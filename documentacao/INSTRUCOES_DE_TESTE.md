# Instruções de teste - Momento II

1. Inicie Apache e MySQL no XAMPP.
2. No phpMyAdmin, importe `banco/barbearia_rafa.sql`.
3. Coloque/descompacte a pasta completa em qualquer diretório dentro de `htdocs`.
4. Acesse `http://localhost/NOME_DA_PASTA/login.html`.
5. Entre com:
   - E-mail: `admin@barbearia.local`
   - Senha: `Admin@123`
6. Abra o módulo **Usuários** e teste cadastro, edição e exclusão de um usuário.
7. Teste logout e login com o usuário criado.
8. Em Clientes, Barbeiros, Serviços e Produtos, teste salvar, editar, listar e excluir.
9. Em Agendamentos, confirme que cliente, barbeiro e serviço vêm do banco e teste as validações de data, horário, domingo e confirmação.
10. Renomeie a pasta do projeto dentro de `htdocs` e acesse novamente pelo novo nome para confirmar que não existem links fixos.

## Resultado esperado

- Usuário não autenticado é direcionado para `login.html`.
- Login consulta a tabela `usuarios`.
- Sessão mantém o usuário autenticado.
- Endpoints protegidos recusam acesso sem sessão.
- Todos os cadastros exibem dados armazenados no banco.
- O sistema continua funcionando após alterar o nome da pasta dentro de `htdocs`.
