<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/auth.php';

exigirLogin();
$pdo = conectar();
$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    $dados = $pdo->query("SELECT id, nome, cpf, telefone, email, especialidade, comissao, ativo, DATE_FORMAT(criado_em, '%d/%m/%Y %H:%i') AS criado_em FROM barbeiros ORDER BY nome")->fetchAll();
    responder(true, '', $dados);
}

if ($metodo === 'DELETE') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) responder(false, 'Barbeiro inválido.', null, 422);
    try {
        $stmt = $pdo->prepare('DELETE FROM barbeiros WHERE id=:id');
        $stmt->execute(['id' => $id]);
        if ($stmt->rowCount() === 0) responder(false, 'Barbeiro não encontrado.', null, 404);
        responder(true, 'Barbeiro excluído com sucesso.');
    } catch (PDOException $erro) {
        if ($erro->getCode() === '23000') responder(false, 'Este barbeiro possui agendamentos relacionados. Inative-o em vez de excluir.', null, 409);
        responder(false, 'Não foi possível excluir o barbeiro.', null, 500);
    }
}

if ($metodo !== 'POST') responder(false, 'Método não permitido.', null, 405);

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
$nome = trim($_POST['nome'] ?? '');
$cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
$telefone = preg_replace('/\D/', '', $_POST['telefone'] ?? '');
$email = trim($_POST['email'] ?? '');
$especialidade = trim($_POST['especialidade'] ?? '');
$comissao = filter_var($_POST['comissao'] ?? null, FILTER_VALIDATE_FLOAT);
$ativo = ($_POST['ativo'] ?? '1') === '1' ? 1 : 0;
$especialidades = ['Corte masculino', 'Barba', 'Visagismo', 'Química capilar'];

if ($nome === '' || $cpf === '' || $telefone === '' || $email === '' || $especialidade === '' || $comissao === false) responder(false, 'Preencha todos os campos corretamente.', null, 422);
if (strlen($nome) < 3 || strlen($cpf) !== 11 || strlen($telefone) < 10 || !filter_var($email, FILTER_VALIDATE_EMAIL)) responder(false, 'Nome, CPF, telefone ou e-mail inválido.', null, 422);
if (!in_array($especialidade, $especialidades, true)) responder(false, 'Especialidade inválida.', null, 422);
if ($comissao < 0 || $comissao > 100) responder(false, 'A comissão deve estar entre 0% e 100%.', null, 422);

try {
    if ($id) {
        $stmt = $pdo->prepare('UPDATE barbeiros SET nome=:nome, cpf=:cpf, telefone=:telefone, email=:email, especialidade=:especialidade, comissao=:comissao, ativo=:ativo WHERE id=:id');
        $stmt->execute(compact('nome', 'cpf', 'telefone', 'email', 'especialidade', 'comissao', 'ativo', 'id'));
        $acao = 'atualizado';
    } else {
        $stmt = $pdo->prepare('INSERT INTO barbeiros (nome, cpf, telefone, email, especialidade, comissao, ativo) VALUES (:nome,:cpf,:telefone,:email,:especialidade,:comissao,:ativo)');
        $stmt->execute(compact('nome', 'cpf', 'telefone', 'email', 'especialidade', 'comissao', 'ativo'));
        $acao = 'cadastrado';
    }
} catch (PDOException $erro) {
    if ($erro->getCode() === '23000') responder(false, 'CPF ou e-mail já cadastrado para outro barbeiro.', null, 409);
    responder(false, 'Não foi possível salvar o barbeiro.', null, 500);
}

$valorComissao = 50 * ($comissao / 100);
responder(true, 'Barbeiro ' . $acao . ' com sucesso. Em um serviço de R$ 50,00, a comissão seria de R$ ' . number_format($valorComissao, 2, ',', '.') . '.');
