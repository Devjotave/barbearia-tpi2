<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/auth.php';

exigirLogin();
$pdo = conectar();
$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    $dados = $pdo->query("SELECT id, nome, categoria, marca, estoque, preco, fornecedor, ativo, DATE_FORMAT(criado_em, '%d/%m/%Y %H:%i') AS criado_em FROM produtos ORDER BY nome")->fetchAll();
    responder(true, '', $dados);
}

if ($metodo === 'DELETE') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) responder(false, 'Produto inválido.', null, 422);
    try {
        $stmt = $pdo->prepare('DELETE FROM produtos WHERE id=:id');
        $stmt->execute(['id' => $id]);
        if ($stmt->rowCount() === 0) responder(false, 'Produto não encontrado.', null, 404);
        responder(true, 'Produto excluído com sucesso.');
    } catch (PDOException $erro) {
        if ($erro->getCode() === '23000') responder(false, 'Este produto possui registros relacionados e não pode ser excluído.', null, 409);
        responder(false, 'Não foi possível excluir o produto.', null, 500);
    }
}

if ($metodo !== 'POST') responder(false, 'Método não permitido.', null, 405);

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
$nome = trim($_POST['nome'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');
$marca = trim($_POST['marca'] ?? '');
$estoque = filter_var($_POST['estoque'] ?? null, FILTER_VALIDATE_INT);
$preco = filter_var($_POST['preco'] ?? null, FILTER_VALIDATE_FLOAT);
$fornecedor = trim($_POST['fornecedor'] ?? '');
$ativo = ($_POST['ativo'] ?? '1') === '1' ? 1 : 0;

if ($nome === '' || $categoria === '' || $marca === '' || $estoque === false || $preco === false || $fornecedor === '') responder(false, 'Preencha todos os campos corretamente.', null, 422);
if (strlen($nome) < 2 || $estoque < 0 || $preco <= 0) responder(false, 'Nome, estoque ou preço inválido.', null, 422);
if (!in_array($categoria, ['Shampoo','Pomada','Óleo','Higiene','Outro'], true)) responder(false, 'Categoria inválida.', null, 422);

if ($id) {
    $stmt = $pdo->prepare('UPDATE produtos SET nome=:nome,categoria=:categoria,marca=:marca,estoque=:estoque,preco=:preco,fornecedor=:fornecedor,ativo=:ativo WHERE id=:id');
    $stmt->execute(compact('nome','categoria','marca','estoque','preco','fornecedor','ativo','id'));
    $acao = 'atualizado';
} else {
    $stmt = $pdo->prepare('INSERT INTO produtos (nome,categoria,marca,estoque,preco,fornecedor,ativo) VALUES (:nome,:categoria,:marca,:estoque,:preco,:fornecedor,:ativo)');
    $stmt->execute(compact('nome','categoria','marca','estoque','preco','fornecedor','ativo'));
    $acao = 'cadastrado';
}

$valorEstoque = $estoque * $preco;
$alerta = $estoque <= 5 ? ' Estoque baixo: considerar reposição.' : '';
responder(true, 'Produto ' . $acao . ' com sucesso. Valor estimado do estoque: R$ ' . number_format($valorEstoque, 2, ',', '.') . '.' . $alerta);
