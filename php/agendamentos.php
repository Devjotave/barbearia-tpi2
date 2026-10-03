<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

$cliente = trim($_POST['cliente'] ?? '');
$barbeiro = trim($_POST['barbeiro'] ?? '');
$servico = trim($_POST['servico'] ?? '');
$data = trim($_POST['data'] ?? '');
$horario = trim($_POST['horario'] ?? '');
$observacoes = trim($_POST['observacoes'] ?? '');

if ($cliente === '' || $barbeiro === '' || $servico === '' || $data === '' || $horario === '' || $observacoes === '') {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos.']);
    exit;
}

if (strlen($cliente) < 3 || strlen($observacoes) < 3) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Nome do cliente ou observações inválidas.']);
    exit;
}

$dataAgendamento = DateTime::createFromFormat('Y-m-d H:i', "{$data} {$horario}");
$agora = new DateTime();

if (!$dataAgendamento || $dataAgendamento->format('Y-m-d H:i') !== "{$data} {$horario}") {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Data ou horário inválido.']);
    exit;
}

if ($dataAgendamento < $agora) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'O agendamento deve ser para uma data e horário futuros.']);
    exit;
}

$hora = (int) $dataAgendamento->format('H');
$minuto = (int) $dataAgendamento->format('i');
$minutosDoDia = ($hora * 60) + $minuto;
$inicioAtendimento = 8 * 60;
$fimAtendimento = 19 * 60;

if ($minutosDoDia < $inicioAtendimento || $minutosDoDia > $fimAtendimento) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Escolha um horário entre 08:00 e 19:00.']);
    exit;
}

$diaSemana = (int) $dataAgendamento->format('N');

if ($diaSemana === 7) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'A barbearia não realiza agendamentos aos domingos.']);
    exit;
}

$diasAteAtendimento = (int) $agora->diff($dataAgendamento)->format('%a');
$quando = $diasAteAtendimento === 0 ? 'hoje' : "daqui a {$diasAteAtendimento} dia(s)";

echo json_encode([
    'sucesso' => true,
    'mensagem' => "Agendamento recebido com sucesso para {$barbeiro}, serviço {$servico}, {$quando}."
]);
