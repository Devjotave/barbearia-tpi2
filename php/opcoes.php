<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/auth.php';

exigirLogin();
$tipo = $_GET['tipo'] ?? '';
$pdo = conectar();

$sql = match ($tipo) {
    'clientes' => 'SELECT id, nome AS label FROM clientes ORDER BY nome',
    'barbeiros' => 'SELECT id, nome AS label FROM barbeiros WHERE ativo = 1 ORDER BY nome',
    'servicos' => "SELECT id, CONCAT(nome, ' - R$ ', CAST(preco AS CHAR)) AS label FROM servicos WHERE ativo = 1 ORDER BY nome",
    default => null,
};

if (!$sql) {
    responder(false, 'Tipo de opção inválido.', null, 422);
}

$dados = $pdo->query($sql)->fetchAll();
responder(true, '', $dados);
