<?php
require_once __DIR__ . '/auth.php';

$usuario = usuarioAtual();
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'autenticado' => (bool)$usuario,
    'usuario' => $usuario,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
