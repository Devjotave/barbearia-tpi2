<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');
$duracao = filter_var($_POST['duracao'] ?? null, FILTER_VALIDATE_INT);
$preco = filter_var($_POST['preco'] ?? null, FILTER_VALIDATE_FLOAT);
$nivel = trim($_POST['nivel'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');

if ($nome === '' || $categoria === '' || $duracao === false || $preco === false || $nivel === '' || $descricao === '') {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos corretamente.']);
    exit;
}

if (strlen($nome) < 3 || $duracao < 10 || $duracao > 240 || $preco <= 0 || strlen($descricao) < 5) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Verifique nome, duração, preço e descrição do serviço.']);
    exit;
}

$valorHora = ($preco / $duracao) * 60;
$valorFormatado = number_format($valorHora, 2, ',', '.');

echo json_encode([
    'sucesso' => true,
    'mensagem' => "Serviço recebido com sucesso. Valor equivalente por hora: R$ {$valorFormatado}."
]);
