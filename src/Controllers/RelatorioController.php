<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\ExportadorExcel;
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

        if ($this->pediuExportacao()) {
            $linhas = [];
            foreach ($dados['por_sexo'] as $linha) {
                $linhas[] = ['Sexo', ucfirst($linha['sexo']), (int) $linha['total']];
            }
            foreach ($dados['por_estado_civil'] as $linha) {
                $linhas[] = ['Estado civil', $linha['estado_civil'], (int) $linha['total']];
            }
            foreach ($dados['por_filhos'] as $linha) {
                $linhas[] = ['Filhos', $linha['grupo'], (int) $linha['total']];
            }
            foreach ($dados['por_faixa_etaria'] as $linha) {
                $linhas[] = ['Faixa etária', $linha['faixa'], (int) $linha['total']];
            }
            foreach ($dados['por_departamento'] as $linha) {
                $linhas[] = ['Departamento', $linha['departamento'] ?? '—', (int) $linha['total']];
            }
            foreach ($dados['por_cidade'] as $linha) {
                $linhas[] = ['Cidade', $linha['cidade'] ?? '—', (int) $linha['total']];
            }
            ExportadorExcel::baixar('Relatorio_Demografico_' . date('Y-m-d') . '.xlsx', ['Categoria', 'Grupo', 'Total'], $linhas);
            return;
        }

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

        if ($this->pediuExportacao()) {
            $linhas = [];
            foreach ($colaboradores as $colaborador) {
                $linhas[] = [
                    $colaborador['nome'],
                    date('d/m', strtotime($colaborador['data_nascimento'])),
                    date('d/m/Y', strtotime($colaborador['proximo_aniversario'])),
                    $colaborador['departamento_nome'] ?? '',
                ];
            }
            ExportadorExcel::baixar('Aniversariantes_' . date('Y-m-d') . '.xlsx', ['Nome', 'Nascimento', 'Próximo aniversário', 'Departamento'], $linhas);
            return;
        }

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

        if ($this->pediuExportacao()) {
            $linhas = [];
            foreach ($eventos as $evento) {
                $linhas[] = [
                    date('d/m/Y', strtotime($evento['data_evento'])),
                    $evento['colaborador_nome'] ?? '',
                    $evento['cargo_anterior'] ?? '',
                    $evento['cargo_novo'] ?? '',
                    $evento['detalhes'] ?? '',
                ];
            }
            ExportadorExcel::baixar('Promocoes_' . date('Y-m-d') . '.xlsx', ['Data', 'Colaborador', 'Cargo anterior', 'Cargo novo', 'Detalhes'], $linhas);
            return;
        }

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

        if ($this->pediuExportacao()) {
            $linhas = [];
            foreach ($ocorrencias as $ocorrencia) {
                $linhas[] = [
                    date('d/m/Y', strtotime($ocorrencia['data_evento'])),
                    $ocorrencia['colaborador_nome'] ?? '',
                    $ocorrencia['tipo_nome'] ?? '',
                    $ocorrencia['descricao'] ?? '',
                ];
            }
            ExportadorExcel::baixar('Ocorrencias_' . date('Y-m-d') . '.xlsx', ['Data', 'Colaborador', 'Tipo', 'Descrição'], $linhas);
            return;
        }

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

        if ($this->pediuExportacao()) {
            $nomesMeses = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
            $linhas = [];
            foreach ($meses as $numeroMes => $dadosMes) {
                $linhas[] = [
                    $nomesMeses[$numeroMes],
                    $dadosMes['admissoes'],
                    $dadosMes['desligamentos'],
                    $dadosMes['admissoes'] - $dadosMes['desligamentos'],
                ];
            }
            ExportadorExcel::baixar('Turnover_' . $ano . '.xlsx', ['Mês', 'Admissões', 'Desligamentos', 'Saldo'], $linhas);
            return;
        }

        $titulo = 'Turnover';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/relatorios/turnover.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    public function ranking(): void
    {
        Auth::exigirLogin();
        $ano = (int) ($_GET['ano'] ?? date('Y'));
        $linhas_dados = Relatorio::rankingPeriodo($ano);

        if ($this->pediuExportacao()) {
            $linhas = [];
            foreach ($linhas_dados as $linha) {
                $linhas[] = [
                    $linha['colaborador_nome'] ?? '',
                    (int) $linha['primeiros'],
                    (int) $linha['segundos'],
                    (int) $linha['terceiros'],
                    (int) $linha['total_aparicoes'],
                ];
            }
            ExportadorExcel::baixar('Ranking_' . $ano . '.xlsx', ['Colaborador', '1º lugares', '2º lugares', '3º lugares', 'Total de aparições'], $linhas);
            return;
        }

        $linhas = $linhas_dados;
        $titulo = 'Ranking por Período';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/relatorios/ranking.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    private function pediuExportacao(): bool
    {
        return isset($_GET['exportar']) && $_GET['exportar'] === '1';
    }
}