<?php
require_once __DIR__ . '/resposta.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

function usuarioAtual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function exigirLogin(): array
{
    $usuario = usuarioAtual();
    if (!$usuario) {
        responder(false, 'Sessão expirada. Faça login novamente.', null, 401);
    }
    return $usuario;
}

function exigirAdmin(): array
{
    $usuario = exigirLogin();
    if (($usuario['perfil'] ?? '') !== 'Administrador') {
        responder(false, 'Acesso permitido apenas para administradores.', null, 403);
    }
    return $usuario;
}
