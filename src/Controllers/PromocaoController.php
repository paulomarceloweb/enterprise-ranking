<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Colaborador;
use App\Models\HistoricoColaborador;
use App\Models\ArteGerada;
use App\Templates\PromocaoTemplate;

class PromocaoController
{
    private string $pastaArtes;

    public function __construct()
    {
        $this->pastaArtes = __DIR__ . '/../../public/assets/artes/promocao/';
    }

    public function form(): void
    {
        Auth::exigirLogin();
        $colaboradorId = (int) ($_GET['colaborador_id'] ?? 0);
        $colaborador = Colaborador::buscarPorId($colaboradorId);
        if (!$colaborador) {
            header('Location: /colaboradores');
            exit;
        }
        $titulo = 'Registrar Promoção';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/promocoes/novo.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    /**
     * Registra o evento de promoção no histórico, atualiza o cargo atual
     * do colaborador e já gera a arte (temos tudo que precisa nesse momento).
     */
    public function store(): void
    {
        Auth::exigirLogin();

        $colaboradorId = (int) ($_POST['colaborador_id'] ?? 0);
        $cargoNovo = trim($_POST['cargo_novo'] ?? '');
        $dataPromocao = $_POST['data_promocao'] ?? date('Y-m-d');
        $observacao = trim($_POST['observacao'] ?? '') ?: null;

        $colaborador = Colaborador::buscarPorId($colaboradorId);
        if (!$colaborador || $cargoNovo === '') {
            $_SESSION['erro_colaborador'] = 'Preencha o cargo novo pra registrar a promoção.';
            header('Location: /promocoes/nova?colaborador_id=' . $colaboradorId);
            exit;
        }

        $cargoAnterior = $colaborador['cargo'];

        Colaborador::atualizar($colaboradorId, [
            'nome' => $colaborador['nome'],
            'sexo' => $colaborador['sexo'],
            'estado_civil' => $colaborador['estado_civil'],
            'quantidade_filhos' => $colaborador['quantidade_filhos'],
            'cidade_id' => $colaborador['cidade_id'],
            'departamento_id' => $colaborador['departamento_id'],
            'cargo' => $cargoNovo,
            'data_admissao' => $colaborador['data_admissao'],
            'data_nascimento' => $colaborador['data_nascimento'],
            'status' => $colaborador['status'],
            'data_desligamento' => $colaborador['data_desligamento'],
            'telefone' => $colaborador['telefone'],
            'email' => $colaborador['email'],
            'instagram' => $colaborador['instagram'],
            'facebook' => $colaborador['facebook'],
            'observacoes' => $colaborador['observacoes'],
        ]);

        HistoricoColaborador::registrar(
            $colaboradorId,
            'promocao',
            $dataPromocao,
            $observacao,
            Auth::id(),
            $cargoAnterior,
            $cargoNovo
        );

        $caminhoFoto = __DIR__ . '/../../public/' . $colaborador['foto'];
        $nomeArquivo = 'Promocao_' . $this->slug($colaborador['nome']) . '_' . uniqid() . '.png';
        $caminhoDestino = $this->pastaArtes . $nomeArquivo;

        try {
            $template = new PromocaoTemplate();
            $template->gerar([
                'nome'           => $colaborador['nome'],
                'foto'           => $caminhoFoto,
                'departamento'   => $colaborador['departamento_nome'] ?? '',
                'regional'       => trim(($colaborador['cidade_nome'] ?? '') . '/' . ($colaborador['cidade_uf'] ?? ''), '/'),
                'cargo_anterior' => $cargoAnterior,
                'cargo_novo'     => $cargoNovo,
            ], $caminhoDestino);
        } catch (\Throwable $e) {
            $_SESSION['erro_colaborador'] = 'Promoção registrada, mas houve erro ao gerar a imagem: ' . $e->getMessage();
            header('Location: /colaboradores/editar?id=' . $colaboradorId);
            exit;
        }

        $arteId = ArteGerada::registrar([
            'ranking_id'     => null,
            'colaborador_id' => $colaboradorId,
            'tipo'           => 'promocao',
            'colocacao'      => null,
            'mes'            => (int) date('n', strtotime($dataPromocao)),
            'ano'            => (int) date('Y', strtotime($dataPromocao)),
            'caminho_imagem' => 'assets/artes/promocao/' . $nomeArquivo,
            'gerado_por'     => Auth::id(),
        ]);

        header('Location: /promocoes/detalhe?id=' . $arteId);
        exit;
    }

    public function show(): void
    {
        Auth::exigirLogin();
        $id = (int) ($_GET['id'] ?? 0);
        $arte = ArteGerada::buscarPorId($id);
        if (!$arte || $arte['tipo'] !== 'promocao') {
            header('Location: /colaboradores');
            exit;
        }
        $colaborador = Colaborador::buscarPorId((int) $arte['colaborador_id']);
        $titulo = 'Promoção gerada';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/promocoes/detalhe.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    private function slug(string $texto): string
    {
        $texto = trim($texto);
        if (function_exists('transliterator_transliterate')) {
            $texto = transliterator_transliterate('Any-Latin; Latin-ASCII;', $texto) ?: $texto;
        }
        $texto = preg_replace('/[^A-Za-z0-9]+/', '-', $texto);
        $texto = trim((string) $texto, '-');
        return $texto === '' ? 'sem-nome' : $texto;
    }
}