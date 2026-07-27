<?php

namespace App\Core;

class Auth
{
    public static function id(): ?int
    {
        return $_SESSION['usuario_id'] ?? null;
    }

    public static function nome(): ?string
    {
        return $_SESSION['usuario_nome'] ?? null;
    }

    public static function nivel(): ?string
    {
        return $_SESSION['usuario_nivel'] ?? null;
    }

    public static function logado(): bool
    {
        return !empty($_SESSION['usuario_id']);
    }

    public static function isSuperAdmin(): bool
    {
        return self::nivel() === 'super_admin';
    }

    public static function exigirLogin(): void
    {
        if (!self::logado()) {
            header('Location: /login');
            exit;
        }
    }

    public static function exigirSuperAdmin(): void
    {
        self::exigirLogin();
        if (!self::isSuperAdmin()) {
            http_response_code(403);
            echo 'Acesso restrito ao Super Admin.';
            exit;
        }
    }
}