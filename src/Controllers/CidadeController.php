<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Cidade;
use App\Models\Regional;

class CidadeController
{
    public function index(): void
    {
        Auth::exigirSuperAdmin();
        $cidades = Cidade::listar();
        $regionais = Regional::listar();
        $titulo = 'Cidades';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/cidades/index.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    public function edit(): void
    {
        Auth::exigirSuperAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $cidade = Cidade::buscarPorId($id);
        if (!$cidade) {
            header('Location: /cidades');
            exit;
        }
        $regionais = Regional::listar();
        $titulo = 'Editar Cidade';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/cidades/editar.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    public function store(): void
    {
        Auth::exigirSuperAdmin();
        $nome = trim($_POST['nome'] ?? '');
        $uf = strtoupper(trim($_POST['uf'] ?? ''));
        $regionalId = ($_POST['regional_id'] ?? '') !== '' ? (int)$_POST['regional_id'] : null;
        if ($nome !== '' && $uf !== '') {
            Cidade::criar($nome, $uf, $regionalId);
        }
        header('Location: /cidades');
        exit;
    }

    public function update(): void
    {
        Auth::exigirSuperAdmin();
        $id = (int)($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $uf = strtoupper(trim($_POST['uf'] ?? ''));
        $regionalId = ($_POST['regional_id'] ?? '') !== '' ? (int)$_POST['regional_id'] : null;
        if ($id && $nome !== '' && $uf !== '') {
            Cidade::atualizar($id, $nome, $uf, $regionalId);
        }
        header('Location: /cidades');
        exit;
    }

    public function destroy(): void
    {
        Auth::exigirSuperAdmin();
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            Cidade::excluir($id);
        }
        header('Location: /cidades');
        exit;
    }
}