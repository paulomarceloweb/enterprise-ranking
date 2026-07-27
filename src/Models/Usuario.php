<?php

namespace App\Models;

use App\Core\Database;

class Usuario
{
    public static function buscarPorEmail(string $email): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = :email AND ativo = 1 LIMIT 1');
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();
        return $usuario ?: null;
    }

    public static function buscarPorId(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $usuario = $stmt->fetch();
        return $usuario ?: null;
    }
}