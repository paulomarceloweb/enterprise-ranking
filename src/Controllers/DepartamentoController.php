<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Departamento;

class DepartamentoController
{
    public function index(): void
    {
        Auth::exigirSuperAdmin();
        $departamentos = Departamento::listar();
        $titulo = 'Departamentos';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/departamentos/index.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    public function edit(): void
    {
        Auth::exigirSuperAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $departamento = Departamento::buscarPorId($id);
        if (!$departamento) {
            header('Location: /departamentos');
            exit;
        }
        $titulo = 'Editar Departamento';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/departamentos/editar.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    public function store(): void
    {
        Auth::exigirSuperAdmin();
        $nome = trim($_POST['nome'] ?? '');
        if ($nome !== '') {
            Departamento::criar($nome);
        }
        header('Location: /departamentos');
        exit;
    }

    public function update(): void
    {
        Auth::exigirSuperAdmin();
        $id = (int)($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        if ($id && $nome !== '') {
            Departamento::atualizar($id, $nome);
        }
        header('Location: /departamentos');
        exit;
    }

    public function destroy(): void
    {
        Auth::exigirSuperAdmin();
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            Departamento::excluir($id);
        }
        header('Location: /departamentos');
        exit;
    }
}