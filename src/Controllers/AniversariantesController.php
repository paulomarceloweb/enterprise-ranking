<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Models\ArteGerada;
use App\Templates\AniversariantesTemplate;

class AniversariantesController
{
    private string $pastaArtes;

    public function __construct()
    {
        $this->pastaArtes = __DIR__ . '/../../public/assets/artes/aniversariantes/';
    }

    public function form(): void
    {
        Auth::exigirLogin();
        $titulo = 'Gerar Aniversariantes do Mês';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/aniversariantes/novo.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    /**
     * Passo intermediário: mostra quem faz aniversário no mês escolhido antes
     * de gerar de fato, pra R.H. conferir a lista (e não ter surpresa com
     * quantas imagens vão ser criadas).
     */
    public function pesquisar(): void
    {
        Auth::exigirLogin();

        $mes = (int) ($_GET['mes'] ?? 0);
        $ano = (int) ($_GET['ano'] ?? date('Y'));

        if (!$mes) {
            header('Location: /aniversariantes/novo');
            exit;
        }

        $colaboradores = $this->buscarAniversariantes($mes);
        $totalPaginas = (int) ceil(count($colaboradores) / 6);

        $titulo = 'Conferir Aniversariantes';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/aniversariantes/preview.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    private function buscarAniversariantes(int $mes): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT colaboradores.*, cidades.nome AS cidade_nome, cidades.uf AS cidade_uf
            FROM colaboradores
            LEFT JOIN cidades ON cidades.id = colaboradores.cidade_id
            WHERE MONTH(colaboradores.data_nascimento) = :mes
              AND colaboradores.status = "ativo"
            ORDER BY DAY(colaboradores.data_nascimento) ASC
        ');
        $stmt->execute(['mes' => $mes]);
        return $stmt->fetchAll();
    }

    /**
     * Busca os colaboradores ativos que fazem aniversário no mês escolhido
     * (independente do ano de nascimento), ordena por dia, e gera uma imagem
     * de grupo pra cada 6 pessoas (pagina automaticamente se passar disso).
     * Chamado depois que o R.H. já conferiu a lista na tela de preview.
     */
    public function store(): void
    {
        Auth::exigirLogin();

        $mes = (int) ($_POST['mes'] ?? 0);
        $ano = (int) ($_POST['ano'] ?? date('Y'));

        if (!$mes) {
            $_SESSION['erro_aniversariantes'] = 'Selecione o mês.';
            header('Location: /aniversariantes/novo');
            exit;
        }

        $colaboradores = $this->buscarAniversariantes($mes);

        if (empty($colaboradores)) {
            $_SESSION['erro_aniversariantes'] = 'Nenhum colaborador ativo faz aniversário nesse mês.';
            header('Location: /aniversariantes/novo');
            exit;
        }

        $paginas = array_chunk($colaboradores, 6);
        $template = new AniversariantesTemplate();
        $arteIds = [];

        foreach ($paginas as $indice => $pagina) {
            $pessoas = array_map(function ($colaborador) {
                return [
                    'nome'    => $colaborador['nome'],
                    'foto'    => __DIR__ . '/../../public/' . $colaborador['foto'],
                    'cargo'   => $colaborador['cargo'],
                    'cidade'  => trim(($colaborador['cidade_nome'] ?? '') . '/' . ($colaborador['cidade_uf'] ?? ''), '/'),
                    'dia_mes' => date('d/m', strtotime($colaborador['data_nascimento'])),
                ];
            }, $pagina);

            $numeroPagina = $indice + 1;
            $nomeArquivo = 'Aniversariantes_' . $ano . '-' . str_pad((string) $mes, 2, '0', STR_PAD_LEFT) . '_pagina' . $numeroPagina . '_' . uniqid() . '.png';
            $caminhoDestino = $this->pastaArtes . $nomeArquivo;

            try {
                $template->gerar([
                    'mes'     => $mes,
                    'ano'     => $ano,
                    'pessoas' => $pessoas,
                ], $caminhoDestino);
            } catch (\Throwable $e) {
                $_SESSION['erro_aniversariantes'] = 'Erro ao gerar a página ' . $numeroPagina . ': ' . $e->getMessage();
                header('Location: /aniversariantes/novo');
                exit;
            }

            $arteIds[] = ArteGerada::registrar([
                'ranking_id'     => null,
                'colaborador_id' => null,
                'tipo'           => 'aniversario',
                'colocacao'      => null,
                'mes'            => $mes,
                'ano'            => $ano,
                'caminho_imagem' => 'assets/artes/aniversariantes/' . $nomeArquivo,
                'gerado_por'     => Auth::id(),
            ]);
        }

        $_SESSION['aniversariantes_gerados'] = $arteIds;
        header('Location: /aniversariantes/resultado');
        exit;
    }

    public function resultado(): void
    {
        Auth::exigirLogin();
        $arteIds = $_SESSION['aniversariantes_gerados'] ?? [];
        unset($_SESSION['aniversariantes_gerados']);

        if (empty($arteIds)) {
            header('Location: /aniversariantes/novo');
            exit;
        }

        $artes = array_map(fn ($id) => ArteGerada::buscarPorId((int) $id), $arteIds);
        $titulo = 'Aniversariantes gerados';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/aniversariantes/resultado.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }
}