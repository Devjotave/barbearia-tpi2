<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/auth.php';

exigirLogin();
$pdo = conectar();
$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    $sql = "SELECT id, nome, cpf, DATE_FORMAT(nascimento, '%Y-%m-%d') AS nascimento, telefone, email, observacoes,
                   TIMESTAMPDIFF(YEAR, nascimento, CURDATE()) AS idade,
                   DATE_FORMAT(criado_em, '%d/%m/%Y %H:%i') AS criado_em
            FROM clientes ORDER BY nome";
    responder(true, '', $pdo->query($sql)->fetchAll());
}

if ($metodo === 'DELETE') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) responder(false, 'Cliente inválido.', null, 422);
    try {
        $stmt = $pdo->prepare('DELETE FROM clientes WHERE id = :id');
        $stmt->execute(['id' => $id]);
        if ($stmt->rowCount() === 0) responder(false, 'Cliente não encontrado.', null, 404);
        responder(true, 'Cliente excluído com sucesso.');
    } catch (PDOException $erro) {
        if ($erro->getCode() === '23000') responder(false, 'Este cliente possui registros relacionados e não pode ser excluído.', null, 409);
        responder(false, 'Não foi possível excluir o cliente.', null, 500);
    }
}

if ($metodo !== 'POST') responder(false, 'Método não permitido.', null, 405);

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
$nome = trim($_POST['nome'] ?? '');
$cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
$nascimento = trim($_POST['nascimento'] ?? '');
$telefone = preg_replace('/\D/', '', $_POST['telefone'] ?? '');
$email = trim($_POST['email'] ?? '');
$observacoes = trim($_POST['observacoes'] ?? '');

if ($nome === '' || $cpf === '' || $nascimento === '' || $telefone === '' || $email === '' || $observacoes === '') responder(false, 'Preencha todos os campos.', null, 422);
if (strlen($nome) < 3 || strlen($cpf) !== 11 || strlen($telefone) < 10 || !filter_var($email, FILTER_VALIDATE_EMAIL)) responder(false, 'Nome, CPF, telefone ou e-mail inválido.', null, 422);

$dataNascimento = DateTime::createFromFormat('Y-m-d', $nascimento);
$hoje = new DateTime('today');
if (!$dataNascimento || $dataNascimento->format('Y-m-d') !== $nascimento || $dataNascimento > $hoje) responder(false, 'Data de nascimento inválida.', null, 422);

try {
    if ($id) {
        $stmt = $pdo->prepare('UPDATE clientes SET nome=:nome, cpf=:cpf, nascimento=:nascimento, telefone=:telefone, email=:email, observacoes=:observacoes WHERE id=:id');
        $stmt->execute(compact('nome', 'cpf', 'nascimento', 'telefone', 'email', 'observacoes', 'id'));
        $acao = 'atualizado';
    } else {
        $stmt = $pdo->prepare('INSERT INTO clientes (nome, cpf, nascimento, telefone, email, observacoes) VALUES (:nome, :cpf, :nascimento, :telefone, :email, :observacoes)');
        $stmt->execute(compact('nome', 'cpf', 'nascimento', 'telefone', 'email', 'observacoes'));
        $acao = 'cadastrado';
    }
} catch (PDOException $erro) {
    if ($erro->getCode() === '23000') responder(false, 'CPF ou e-mail já cadastrado para outro cliente.', null, 409);
    responder(false, 'Não foi possível salvar o cliente.', null, 500);
}

$idade = $dataNascimento->diff($hoje)->y;
$classificacao = $idade >= 18 ? 'maior de idade' : 'menor de idade';
responder(true, "Cliente {$acao} com sucesso. Idade: {$idade} anos ({$classificacao}).");
