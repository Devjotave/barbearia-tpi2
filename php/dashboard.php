<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/auth.php';

exigirLogin();
$pdo = conectar();
$tabelas = ['clientes', 'barbeiros', 'servicos', 'produtos', 'agendamentos', 'usuarios'];
$dados = [];
foreach ($tabelas as $tabela) {
    $dados[$tabela] = (int)$pdo->query("SELECT COUNT(*) FROM {$tabela}")->fetchColumn();
}
responder(true, '', $dados);
