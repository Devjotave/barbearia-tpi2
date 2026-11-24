<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, 'Método não permitido.', null, 405);
}

$email = trim($_POST['email'] ?? '');
$senha = (string)($_POST['senha'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $senha === '') {
    responder(false, 'Informe e-mail e senha válidos.', null, 422);
}

$pdo = conectar();
$stmt = $pdo->prepare('SELECT id, nome, email, senha_hash, perfil, ativo FROM usuarios WHERE email = :email LIMIT 1');
$stmt->execute(['email' => $email]);
$usuario = $stmt->fetch();

if (!$usuario || (int)$usuario['ativo'] !== 1 || !password_verify($senha, $usuario['senha_hash'])) {
    responder(false, 'E-mail ou senha inválidos.', null, 401);
}

session_regenerate_id(true);
$_SESSION['usuario'] = [
    'id' => (int)$usuario['id'],
    'nome' => $usuario['nome'],
    'email' => $usuario['email'],
    'perfil' => $usuario['perfil'],
];

responder(true, 'Login realizado com sucesso.', $_SESSION['usuario']);
