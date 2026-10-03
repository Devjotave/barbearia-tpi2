<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
$telefone = preg_replace('/\D/', '', $_POST['telefone'] ?? '');
$email = trim($_POST['email'] ?? '');
$especialidade = trim($_POST['especialidade'] ?? '');
$comissao = filter_var($_POST['comissao'] ?? null, FILTER_VALIDATE_FLOAT);

if ($nome === '' || $cpf === '' || $telefone === '' || $email === '' || $especialidade === '' || $comissao === false) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos corretamente.']);
    exit;
}

if (strlen($nome) < 3 || strlen($cpf) !== 11 || strlen($telefone) < 10 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Nome, CPF, telefone ou e-mail inválido.']);
    exit;
}

if ($comissao < 0 || $comissao > 100) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'A comissão deve estar entre 0% e 100%.']);
    exit;
}

$valorServicoExemplo = 50;
$valorComissao = $valorServicoExemplo * ($comissao / 100);
$valorFormatado = number_format($valorComissao, 2, ',', '.');

echo json_encode([
    'sucesso' => true,
    'mensagem' => "Barbeiro recebido com sucesso. Em um serviço de R$ 50,00, a comissão seria de R$ {$valorFormatado}."
]);
