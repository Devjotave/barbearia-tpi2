<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');
$marca = trim($_POST['marca'] ?? '');
$estoque = filter_var($_POST['estoque'] ?? null, FILTER_VALIDATE_INT);
$preco = filter_var($_POST['preco'] ?? null, FILTER_VALIDATE_FLOAT);
$fornecedor = trim($_POST['fornecedor'] ?? '');

if ($nome === '' || $categoria === '' || $marca === '' || $estoque === false || $preco === false || $fornecedor === '') {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos corretamente.']);
    exit;
}

if (strlen($nome) < 2 || $estoque < 0 || $preco <= 0) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Nome, estoque ou preço inválido.']);
    exit;
}

$valorEstoque = $estoque * $preco;
$valorFormatado = number_format($valorEstoque, 2, ',', '.');
$alerta = $estoque <= 5 ? ' Estoque baixo: considerar reposição.' : '';

echo json_encode([
    'sucesso' => true,
    'mensagem' => "Produto recebido com sucesso. Valor estimado do estoque: R$ {$valorFormatado}.{$alerta}"
]);
