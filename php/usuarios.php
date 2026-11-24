<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/auth.php';

$logado = exigirAdmin();
$pdo = conectar();
$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    $dados = $pdo->query("SELECT id, nome, email, perfil, ativo, DATE_FORMAT(criado_em, '%d/%m/%Y %H:%i') AS criado_em FROM usuarios ORDER BY nome")->fetchAll();
    responder(true, '', $dados);
}

if ($metodo === 'DELETE') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) responder(false, 'Usuário inválido.', null, 422);
    if ((int)$id === (int)$logado['id']) responder(false, 'Você não pode excluir o usuário que está atualmente logado.', null, 409);

    $stmt = $pdo->prepare('SELECT perfil, ativo FROM usuarios WHERE id=:id');
    $stmt->execute(['id'=>$id]);
    $alvo = $stmt->fetch();
    if (!$alvo) responder(false, 'Usuário não encontrado.', null, 404);

    if ($alvo['perfil'] === 'Administrador' && (int)$alvo['ativo'] === 1) {
        $admins = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE perfil='Administrador' AND ativo=1")->fetchColumn();
        if ($admins <= 1) responder(false, 'O sistema precisa manter pelo menos um administrador ativo.', null, 409);
    }

    try {
        $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id=:id');
        $stmt->execute(['id'=>$id]);
        responder(true, 'Usuário excluído com sucesso.');
    } catch (PDOException $erro) {
        if ($erro->getCode() === '23000') responder(false, 'Este usuário possui registros relacionados e não pode ser excluído.', null, 409);
        responder(false, 'Não foi possível excluir o usuário.', null, 500);
    }
}

if ($metodo !== 'POST') responder(false, 'Método não permitido.', null, 405);

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = (string)($_POST['senha'] ?? '');
$perfil = trim($_POST['perfil'] ?? '');
$ativo = ($_POST['ativo'] ?? '1') === '1' ? 1 : 0;

if (strlen($nome) < 3 || !filter_var($email, FILTER_VALIDATE_EMAIL)) responder(false, 'Nome ou e-mail inválido.', null, 422);
if (!in_array($perfil, ['Administrador','Atendente'], true)) responder(false, 'Perfil inválido.', null, 422);
if (!$id && strlen($senha) < 6) responder(false, 'A senha deve possuir pelo menos 6 caracteres.', null, 422);
if ($id && $senha !== '' && strlen($senha) < 6) responder(false, 'A nova senha deve possuir pelo menos 6 caracteres.', null, 422);
if ($id && (int)$id === (int)$logado['id'] && $ativo !== 1) responder(false, 'Você não pode inativar o usuário que está atualmente logado.', null, 409);

if ($id) {
    $stmt = $pdo->prepare('SELECT perfil, ativo FROM usuarios WHERE id=:id');
    $stmt->execute(['id'=>$id]);
    $anterior = $stmt->fetch();
    if (!$anterior) responder(false, 'Usuário não encontrado.', null, 404);

    if ($anterior['perfil'] === 'Administrador' && (int)$anterior['ativo'] === 1 && ($perfil !== 'Administrador' || $ativo !== 1)) {
        $admins = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE perfil='Administrador' AND ativo=1")->fetchColumn();
        if ($admins <= 1) responder(false, 'O sistema precisa manter pelo menos um administrador ativo.', null, 409);
    }
}

try {
    if ($id) {
        if ($senha !== '') {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE usuarios SET nome=:nome,email=:email,senha_hash=:senha_hash,perfil=:perfil,ativo=:ativo WHERE id=:id');
            $stmt->execute(['nome'=>$nome,'email'=>$email,'senha_hash'=>$senhaHash,'perfil'=>$perfil,'ativo'=>$ativo,'id'=>$id]);
        } else {
            $stmt = $pdo->prepare('UPDATE usuarios SET nome=:nome,email=:email,perfil=:perfil,ativo=:ativo WHERE id=:id');
            $stmt->execute(['nome'=>$nome,'email'=>$email,'perfil'=>$perfil,'ativo'=>$ativo,'id'=>$id]);
        }
        if ((int)$id === (int)$logado['id']) {
            $_SESSION['usuario']['nome'] = $nome;
            $_SESSION['usuario']['email'] = $email;
            $_SESSION['usuario']['perfil'] = $perfil;
        }
        $acao = 'atualizado';
    } else {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO usuarios (nome,email,senha_hash,perfil,ativo) VALUES (:nome,:email,:senha_hash,:perfil,:ativo)');
        $stmt->execute(['nome'=>$nome,'email'=>$email,'senha_hash'=>$senhaHash,'perfil'=>$perfil,'ativo'=>$ativo]);
        $acao = 'cadastrado';
    }
} catch (PDOException $erro) {
    if ($erro->getCode() === '23000') responder(false, 'Já existe um usuário com esse e-mail.', null, 409);
    responder(false, 'Não foi possível salvar o usuário.', null, 500);
}

responder(true, "Usuário {$acao} com sucesso.");
