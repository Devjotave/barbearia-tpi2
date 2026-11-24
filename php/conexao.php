<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/resposta.php';

function conectar(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $erro) {
        responder(false, 'Não foi possível conectar ao banco de dados. Verifique php/config.php e importe banco/barbearia_rafa.sql.', null, 500);
    }
}
