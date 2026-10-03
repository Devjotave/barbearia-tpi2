<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
$nascimento = trim($_POST['nascimento'] ?? '');
$telefone = preg_replace('/\D/', '', $_POST['telefone'] ?? '');
$email = trim($_POST['email'] ?? '');
$observacoes = trim($_POST['observacoes'] ?? '');

if ($nome === '' || $cpf === '' || $nascimento === '' || $telefone === '' || $email === '' || $observacoes === '') {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos.']);
    exit;
}

if (strlen($nome) < 3 || strlen($cpf) !== 11 || strlen($telefone) < 10 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Nome, CPF, telefone ou e-mail inválido.']);
    exit;
}

$dataNascimento = DateTime::createFromFormat('Y-m-d', $nascimento);
$hoje = new DateTime('today');

if (!$dataNascimento || $dataNascimento->format('Y-m-d') !== $nascimento || $dataNascimento > $hoje) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Data de nascimento inválida.']);
    exit;
}

$idade = $dataNascimento->diff($hoje)->y;
$classificacao = $idade >= 18 ? 'maior de idade' : 'menor de idade';

echo json_encode([
    'sucesso' => true,
    'mensagem' => "Cliente recebido com sucesso. Idade calculada: {$idade} anos ({$classificacao})."
]);
