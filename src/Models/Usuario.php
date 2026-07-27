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

    public static function listar(): array
    {
        $pdo = Database::getConnection();
        return $pdo->query('SELECT * FROM usuarios ORDER BY nome')->fetchAll();
    }

    public static function emailJaExiste(string $email, ?int $ignorarId = null): bool
    {
        $pdo = Database::getConnection();
        $sql = 'SELECT id FROM usuarios WHERE email = :email';
        $parametros = ['email' => $email];
        if ($ignorarId !== null) {
            $sql .= ' AND id != :id';
            $parametros['id'] = $ignorarId;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($parametros);
        return (bool) $stmt->fetch();
    }

    public static function criar(string $nome, string $email, string $senha, string $nivel): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            INSERT INTO usuarios (nome, email, senha_hash, nivel, ativo)
            VALUES (:nome, :email, :senha_hash, :nivel, 1)
        ');
        $stmt->execute([
            'nome' => $nome,
            'email' => $email,
            'senha_hash' => password_hash($senha, PASSWORD_DEFAULT),
            'nivel' => $nivel,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function atualizar(int $id, string $nome, string $email, string $nivel, bool $ativo): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            UPDATE usuarios SET nome = :nome, email = :email, nivel = :nivel, ativo = :ativo
            WHERE id = :id
        ');
        $stmt->execute([
            'nome' => $nome,
            'email' => $email,
            'nivel' => $nivel,
            'ativo' => $ativo ? 1 : 0,
            'id' => $id,
        ]);
    }

    public static function atualizarSenha(int $id, string $novaSenha): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE usuarios SET senha_hash = :senha_hash WHERE id = :id');
        $stmt->execute([
            'senha_hash' => password_hash($novaSenha, PASSWORD_DEFAULT),
            'id' => $id,
        ]);
    }

    public static function excluir(int $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}