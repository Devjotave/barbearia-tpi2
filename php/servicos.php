<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/auth.php';

exigirLogin();
$pdo = conectar();
$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    $dados = $pdo->query("SELECT id, nome, categoria, duracao, preco, nivel, descricao, ativo, DATE_FORMAT(criado_em, '%d/%m/%Y %H:%i') AS criado_em FROM servicos ORDER BY nome")->fetchAll();
    responder(true, '', $dados);
}

if ($metodo === 'DELETE') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) responder(false, 'Serviço inválido.', null, 422);
    try {
        $stmt = $pdo->prepare('DELETE FROM servicos WHERE id=:id');
        $stmt->execute(['id' => $id]);
        if ($stmt->rowCount() === 0) responder(false, 'Serviço não encontrado.', null, 404);
        responder(true, 'Serviço excluído com sucesso.');
    } catch (PDOException $erro) {
        if ($erro->getCode() === '23000') responder(false, 'Este serviço possui agendamentos relacionados. Inative-o em vez de excluir.', null, 409);
        responder(false, 'Não foi possível excluir o serviço.', null, 500);
    }
}

if ($metodo !== 'POST') responder(false, 'Método não permitido.', null, 405);

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
$nome = trim($_POST['nome'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');
$duracao = filter_var($_POST['duracao'] ?? null, FILTER_VALIDATE_INT);
$preco = filter_var($_POST['preco'] ?? null, FILTER_VALIDATE_FLOAT);
$nivel = trim($_POST['nivel'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$ativo = ($_POST['ativo'] ?? '1') === '1' ? 1 : 0;

if ($nome === '' || $categoria === '' || $duracao === false || $preco === false || $nivel === '' || $descricao === '') responder(false, 'Preencha todos os campos corretamente.', null, 422);
if (strlen($nome) < 3 || $duracao < 10 || $duracao > 240 || $preco <= 0 || strlen($descricao) < 5) responder(false, 'Verifique nome, duração, preço e descrição do serviço.', null, 422);
if (!in_array($categoria, ['Cabelo','Barba','Combo','Tratamento'], true) || !in_array($nivel, ['Baixa','Média','Alta'], true)) responder(false, 'Categoria ou nível de demanda inválido.', null, 422);

if ($id) {
    $stmt = $pdo->prepare('UPDATE servicos SET nome=:nome,categoria=:categoria,duracao=:duracao,preco=:preco,nivel=:nivel,descricao=:descricao,ativo=:ativo WHERE id=:id');
    $stmt->execute(compact('nome','categoria','duracao','preco','nivel','descricao','ativo','id'));
    $acao = 'atualizado';
} else {
    $stmt = $pdo->prepare('INSERT INTO servicos (nome,categoria,duracao,preco,nivel,descricao,ativo) VALUES (:nome,:categoria,:duracao,:preco,:nivel,:descricao,:ativo)');
    $stmt->execute(compact('nome','categoria','duracao','preco','nivel','descricao','ativo'));
    $acao = 'cadastrado';
}

$valorHora = ($preco / $duracao) * 60;
responder(true, 'Serviço ' . $acao . ' com sucesso. Valor equivalente por hora: R$ ' . number_format($valorHora, 2, ',', '.') . '.');
