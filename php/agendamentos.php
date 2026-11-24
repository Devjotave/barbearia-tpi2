<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/auth.php';

exigirLogin();
$pdo = conectar();
$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    $sql = "SELECT a.id, a.cliente_id, a.barbeiro_id, a.servico_id,
                   DATE_FORMAT(a.data_agendamento, '%Y-%m-%d') AS data,
                   DATE_FORMAT(a.data_agendamento, '%H:%i') AS horario,
                   a.confirmacao, a.observacoes, a.status,
                   c.nome AS cliente_nome, b.nome AS barbeiro_nome, s.nome AS servico_nome,
                   DATE_FORMAT(a.criado_em, '%d/%m/%Y %H:%i') AS criado_em
            FROM agendamentos a
            INNER JOIN clientes c ON c.id = a.cliente_id
            INNER JOIN barbeiros b ON b.id = a.barbeiro_id
            INNER JOIN servicos s ON s.id = a.servico_id
            ORDER BY a.data_agendamento DESC";
    responder(true, '', $pdo->query($sql)->fetchAll());
}

if ($metodo === 'DELETE') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) responder(false, 'Agendamento inválido.', null, 422);
    $stmt = $pdo->prepare('DELETE FROM agendamentos WHERE id=:id');
    $stmt->execute(['id' => $id]);
    if ($stmt->rowCount() === 0) responder(false, 'Agendamento não encontrado.', null, 404);
    responder(true, 'Agendamento excluído com sucesso.');
}

if ($metodo !== 'POST') responder(false, 'Método não permitido.', null, 405);

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
$clienteId = filter_var($_POST['cliente_id'] ?? null, FILTER_VALIDATE_INT);
$barbeiroId = filter_var($_POST['barbeiro_id'] ?? null, FILTER_VALIDATE_INT);
$servicoId = filter_var($_POST['servico_id'] ?? null, FILTER_VALIDATE_INT);
$data = trim($_POST['data'] ?? '');
$horario = trim($_POST['horario'] ?? '');
$confirmacao = trim($_POST['confirmacao'] ?? '');
$status = trim($_POST['status'] ?? '');
$observacoes = trim($_POST['observacoes'] ?? '');

if (!$clienteId || !$barbeiroId || !$servicoId || $data === '' || $horario === '' || $confirmacao === '' || $status === '' || $observacoes === '') responder(false, 'Preencha todos os campos.', null, 422);
if (mb_strlen($observacoes) < 3) responder(false, 'As observações devem possuir pelo menos 3 caracteres.', null, 422);
if (!in_array($confirmacao, ['WhatsApp','Telefone','E-mail'], true)) responder(false, 'Forma de confirmação inválida.', null, 422);
if (!in_array($status, ['Agendado','Confirmado','Concluído','Cancelado'], true)) responder(false, 'Status inválido.', null, 422);

$dataAgendamento = DateTime::createFromFormat('Y-m-d H:i', "{$data} {$horario}");
$agora = new DateTime();
if (!$dataAgendamento || $dataAgendamento->format('Y-m-d H:i') !== "{$data} {$horario}") responder(false, 'Data ou horário inválido.', null, 422);
if ($dataAgendamento < $agora && !in_array($status, ['Concluído','Cancelado'], true)) responder(false, 'Agendamentos ativos devem possuir data e horário futuros.', null, 422);

$minutos = ((int)$dataAgendamento->format('H') * 60) + (int)$dataAgendamento->format('i');
if ($minutos < 480 || $minutos > 1140) responder(false, 'Escolha um horário entre 08:00 e 19:00.', null, 422);
if ((int)$dataAgendamento->format('N') === 7) responder(false, 'A barbearia não realiza agendamentos aos domingos.', null, 422);

$checks = [
    ['SELECT COUNT(*) FROM clientes WHERE id=:id', $clienteId, 'Cliente não encontrado.'],
    ['SELECT COUNT(*) FROM barbeiros WHERE id=:id AND ativo=1', $barbeiroId, 'Barbeiro não encontrado ou inativo.'],
    ['SELECT COUNT(*) FROM servicos WHERE id=:id AND ativo=1', $servicoId, 'Serviço não encontrado ou inativo.'],
];
foreach ($checks as [$sql, $valor, $mensagem]) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $valor]);
    if ((int)$stmt->fetchColumn() === 0) responder(false, $mensagem, null, 422);
}

$dataHora = $dataAgendamento->format('Y-m-d H:i:s');
if ($id) {
    $stmt = $pdo->prepare('UPDATE agendamentos SET cliente_id=:cliente_id, barbeiro_id=:barbeiro_id, servico_id=:servico_id, data_agendamento=:data_agendamento, confirmacao=:confirmacao, observacoes=:observacoes, status=:status WHERE id=:id');
    $stmt->execute([
        'cliente_id'=>$clienteId, 'barbeiro_id'=>$barbeiroId, 'servico_id'=>$servicoId,
        'data_agendamento'=>$dataHora, 'confirmacao'=>$confirmacao, 'observacoes'=>$observacoes, 'status'=>$status, 'id'=>$id
    ]);
    $acao = 'atualizado';
} else {
    $stmt = $pdo->prepare('INSERT INTO agendamentos (cliente_id, barbeiro_id, servico_id, data_agendamento, confirmacao, observacoes, status) VALUES (:cliente_id,:barbeiro_id,:servico_id,:data_agendamento,:confirmacao,:observacoes,:status)');
    $stmt->execute([
        'cliente_id'=>$clienteId, 'barbeiro_id'=>$barbeiroId, 'servico_id'=>$servicoId,
        'data_agendamento'=>$dataHora, 'confirmacao'=>$confirmacao, 'observacoes'=>$observacoes, 'status'=>$status
    ]);
    $acao = 'cadastrado';
}

$dias = max(0, (int)$agora->diff($dataAgendamento)->format('%a'));
$quando = $dias === 0 ? 'hoje' : "daqui a {$dias} dia(s)";
responder(true, "Agendamento {$acao} com sucesso ({$quando}). Confirmação por {$confirmacao}.");
