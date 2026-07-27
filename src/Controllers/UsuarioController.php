<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Usuario;

class UsuarioController
{
    // Só Super Admin gerencia usuários — permissão sensível, poderia criar
    // outro super admin ou desativar todo mundo.
    public function index(): void
    {
        Auth::exigirSuperAdmin();
        $usuarios = Usuario::listar();
        $titulo = 'Usuários';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/usuarios/index.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    public function create(): void
    {
        Auth::exigirSuperAdmin();
        $titulo = 'Novo Usuário';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/usuarios/novo.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    public function store(): void
    {
        Auth::exigirSuperAdmin();

        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = (string) ($_POST['senha'] ?? '');
        $nivel = ($_POST['nivel'] ?? '') === 'super_admin' ? 'super_admin' : 'usuario';

        if ($nome === '' || $email === '' || strlen($senha) < 6) {
            $_SESSION['erro_usuario'] = 'Preencha nome, e-mail e uma senha com pelo menos 6 caracteres.';
            header('Location: /usuarios/novo');
            exit;
        }

        if (Usuario::emailJaExiste($email)) {
            $_SESSION['erro_usuario'] = 'Já existe um usuário com esse e-mail.';
            header('Location: /usuarios/novo');
            exit;
        }

        Usuario::criar($nome, $email, $senha, $nivel);

        header('Location: /usuarios');
        exit;
    }

    public function edit(): void
    {
        Auth::exigirSuperAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $usuario = Usuario::buscarPorId($id);
        if (!$usuario) {
            header('Location: /usuarios');
            exit;
        }
        $titulo = 'Editar Usuário';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/usuarios/editar.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    public function update(): void
    {
        Auth::exigirSuperAdmin();

        $id = (int) ($_POST['id'] ?? 0);
        $usuario = Usuario::buscarPorId($id);
        if (!$usuario) {
            header('Location: /usuarios');
            exit;
        }

        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $nivel = ($_POST['nivel'] ?? '') === 'super_admin' ? 'super_admin' : 'usuario';
        $ativo = !empty($_POST['ativo']);

        if ($nome === '' || $email === '') {
            $_SESSION['erro_usuario'] = 'Nome e e-mail são obrigatórios.';
            header('Location: /usuarios/editar?id=' . $id);
            exit;
        }

        if (Usuario::emailJaExiste($email, $id)) {
            $_SESSION['erro_usuario'] = 'Já existe outro usuário com esse e-mail.';
            header('Location: /usuarios/editar?id=' . $id);
            exit;
        }

        // Não deixa o usuário se autodesativar ou se rebaixar — evita
        // ficar sem nenhum super admin ativo por engano.
        if ($id === Auth::id() && (!$ativo || $nivel !== 'super_admin')) {
            $_SESSION['erro_usuario'] = 'Você não pode desativar ou rebaixar a própria conta enquanto estiver logado nela.';
            header('Location: /usuarios/editar?id=' . $id);
            exit;
        }

        Usuario::atualizar($id, $nome, $email, $nivel, $ativo);

        $novaSenha = (string) ($_POST['nova_senha'] ?? '');
        if ($novaSenha !== '') {
            if (strlen($novaSenha) < 6) {
                $_SESSION['erro_usuario'] = 'Dados salvos, mas a nova senha precisa ter pelo menos 6 caracteres — não foi alterada.';
                header('Location: /usuarios/editar?id=' . $id);
                exit;
            }
            Usuario::atualizarSenha($id, $novaSenha);
        }

        header('Location: /usuarios');
        exit;
    }

    public function destroy(): void
    {
        Auth::exigirSuperAdmin();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id === Auth::id()) {
            $_SESSION['erro_usuario'] = 'Você não pode excluir a própria conta enquanto estiver logado nela.';
            header('Location: /usuarios');
            exit;
        }

        Usuario::excluir($id);

        header('Location: /usuarios');
        exit;
    }
}