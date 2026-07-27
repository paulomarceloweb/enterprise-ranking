<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Regional;

class RegionalController
{
    public function index(): void
    {
        Auth::exigirSuperAdmin();
        $regionais = Regional::listar();
        $titulo = 'Regionais';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/regionais/index.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    public function edit(): void
    {
        Auth::exigirSuperAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $regional = Regional::buscarPorId($id);
        if (!$regional) {
            header('Location: /regionais');
            exit;
        }
        $titulo = 'Editar Regional';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/regionais/editar.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    public function store(): void
    {
        Auth::exigirSuperAdmin();
        $nome = trim($_POST['nome'] ?? '');
        if ($nome !== '') {
            Regional::criar($nome);
        }
        header('Location: /regionais');
        exit;
    }

    public function update(): void
    {
        Auth::exigirSuperAdmin();
        $id = (int)($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        if ($id && $nome !== '') {
            Regional::atualizar($id, $nome);
        }
        header('Location: /regionais');
        exit;
    }

    public function destroy(): void
    {
        Auth::exigirSuperAdmin();
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            Regional::excluir($id);
        }
        header('Location: /regionais');
        exit;
    }
}