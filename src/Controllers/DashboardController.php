<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\ArteGerada;
use App\Models\Ocorrencia;
use App\Models\Ranking;
use App\Models\Relatorio;

class DashboardController
{
    public function index(): void
    {
        Auth::exigirLogin();

        $demografico = Relatorio::demografico();
        $aniversariantesMes = Relatorio::aniversariantesFuturos(30);
        $artesRecentes = ArteGerada::listarRecentes(8);
        $ocorrenciasRecentes = Ocorrencia::listarRecentes(5);

        // Ranking do mês atual (se já foi gerado)
        $rankings = Ranking::listar();
        $rankingAtual = null;
        foreach ($rankings as $ranking) {
            if ((int) $ranking['mes'] === (int) date('n') && (int) $ranking['ano'] === (int) date('Y')) {
                $rankingAtual = $ranking;
                break;
            }
        }

        $titulo = 'Dashboard';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/dashboard/index.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }
}