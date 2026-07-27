<?php

namespace App\Controllers;

use App\Models\Usuario;

class AuthController
{
    public function loginForm(): void
    {
        if (!empty($_SESSION['usuario_id'])) {
            header('Location: /dashboard');
            exit;
        }
        $erro = $_SESSION['erro_login'] ?? null;
        unset($_SESSION['erro_login']);
        require __DIR__ . '/../Views/auth/login.php';
    }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        $usuario = Usuario::buscarPorEmail($email);

        if (!$usuario || !password_verify($senha, $usuario['senha_hash'] ?? '')) {
            $_SESSION['erro_login'] = 'E-mail ou senha inválidos.';
            header('Location: /login');
            exit;
        }

        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_nivel'] = $usuario['nivel'];

        header('Location: /dashboard');
        exit;
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        header('Location: /login');
        exit;
    }
}