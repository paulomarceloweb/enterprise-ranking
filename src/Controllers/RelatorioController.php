<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Relatorio;
use App\Models\TipoOcorrencia;

class RelatorioController
{
    public function index(): void
    {
        Auth::exigirLogin();
        $titulo = 'Relatórios';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/relatorios/index.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    public function demografico(): void
    {
        Auth::exigirLogin();
        $dados = Relatorio::demografico();
        $titulo = 'Relatório Demográfico';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/relatorios/demografico.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    public function aniversariantes(): void
    {
        Auth::exigirLogin();
        $dias = (int) ($_GET['dias'] ?? 30);
        if ($dias <= 0) {
            $dias = 30;
        }
        $colaboradores = Relatorio::aniversariantesFuturos($dias);
        $titulo = 'Aniversariantes Futuros';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/relatorios/aniversariantes.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    public function promocoes(): void
    {
        Auth::exigirLogin();
        $inicio = (string) ($_GET['inicio'] ?? '');
        $fim = (string) ($_GET['fim'] ?? '');
        $eventos = Relatorio::historicoPromocoes($inicio ?: null, $fim ?: null);
        $titulo = 'Histórico de Promoções';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/relatorios/promocoes.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    public function ocorrencias(): void
    {
        Auth::exigirLogin();
        $inicio = (string) ($_GET['inicio'] ?? '');
        $fim = (string) ($_GET['fim'] ?? '');
        $tipoOcorrenciaId = (int) ($_GET['tipo_ocorrencia_id'] ?? 0);
        $ocorrencias = Relatorio::historicoOcorrencias($inicio ?: null, $fim ?: null, $tipoOcorrenciaId ?: null);
        $tipos = TipoOcorrencia::listar();
        $titulo = 'Histórico de Ocorrências';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/relatorios/ocorrencias.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    public function turnover(): void
    {
        Auth::exigirLogin();
        $ano = (int) ($_GET['ano'] ?? date('Y'));
        $meses = Relatorio::turnover($ano);
        $titulo = 'Turnover';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/relatorios/turnover.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    public function ranking(): void
    {
        Auth::exigirLogin();
        $ano = (int) ($_GET['ano'] ?? date('Y'));
        $linhas = Relatorio::rankingPeriodo($ano);
        $titulo = 'Ranking por Período';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/relatorios/ranking.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }
}