<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\TipoOcorrencia;

class TipoOcorrenciaController
{
    public function index(): void
    {
        Auth::exigirSuperAdmin();
        $tipos = TipoOcorrencia::listar();
        $titulo = 'Tipos de Ocorrência';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/tipos_ocorrencia/index.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    public function edit(): void
    {
        Auth::exigirSuperAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $tipo = TipoOcorrencia::buscarPorId($id);
        if (!$tipo) {
            header('Location: /tipos-ocorrencia');
            exit;
        }
        $titulo = 'Editar Tipo de Ocorrência';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/tipos_ocorrencia/editar.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    public function store(): void
    {
        Auth::exigirSuperAdmin();
        $nome = trim($_POST['nome'] ?? '');
        if ($nome !== '') {
            TipoOcorrencia::criar($nome);
        }
        header('Location: /tipos-ocorrencia');
        exit;
    }

    public function update(): void
    {
        Auth::exigirSuperAdmin();
        $id = (int)($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        if ($id && $nome !== '') {
            TipoOcorrencia::atualizar($id, $nome);
        }
        header('Location: /tipos-ocorrencia');
        exit;
    }

    public function destroy(): void
    {
        Auth::exigirSuperAdmin();
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            TipoOcorrencia::excluir($id);
        }
        header('Location: /tipos-ocorrencia');
        exit;
    }
}