<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Colaborador;
use App\Models\ArteGerada;
use App\Templates\BoasVindasTemplate;

class BoasVindasController
{
    private string $pastaArtes;

    public function __construct()
    {
        $this->pastaArtes = __DIR__ . '/../../public/assets/artes/boas-vindas/';
    }

    /**
     * Gera a arte de boas-vindas pra UM colaborador (botão manual na ficha dele).
     * Não é automático no cadastro de propósito: evita gerar "boas-vindas" pra
     * gente que já trabalha há anos na empresa e só está sendo cadastrada agora.
     */
    public function store(): void
    {
        Auth::exigirLogin();

        $colaboradorId = (int) ($_POST['colaborador_id'] ?? 0);
        $colaborador = Colaborador::buscarPorId($colaboradorId);
        if (!$colaborador) {
            $_SESSION['erro_colaborador'] = 'Colaborador não encontrado.';
            header('Location: /colaboradores');
            exit;
        }

        $caminhoFoto = __DIR__ . '/../../public/' . $colaborador['foto'];
        $nomeArquivo = 'BoasVindas_' . $this->slug($colaborador['nome']) . '_' . uniqid() . '.png';
        $caminhoDestino = $this->pastaArtes . $nomeArquivo;

        try {
            $template = new BoasVindasTemplate();
            $template->gerar([
                'nome'   => $colaborador['nome'],
                'sexo'   => $colaborador['sexo'],
                'foto'   => $caminhoFoto,
                'cargo'  => $colaborador['cargo'],
                'cidade' => trim(($colaborador['cidade_nome'] ?? '') . '/' . ($colaborador['cidade_uf'] ?? ''), '/'),
            ], $caminhoDestino);
        } catch (\Throwable $e) {
            $_SESSION['erro_colaborador'] = 'Erro ao gerar a arte de boas-vindas: ' . $e->getMessage();
            header('Location: /colaboradores/editar?id=' . $colaboradorId);
            exit;
        }

        $arteId = ArteGerada::registrar([
            'ranking_id'     => null,
            'colaborador_id' => $colaboradorId,
            'tipo'           => 'boas_vindas',
            'colocacao'      => null,
            'mes'            => (int) date('n'),
            'ano'            => (int) date('Y'),
            'caminho_imagem' => 'assets/artes/boas-vindas/' . $nomeArquivo,
            'gerado_por'     => Auth::id(),
        ]);

        header('Location: /boas-vindas/detalhe?id=' . $arteId);
        exit;
    }

    public function show(): void
    {
        Auth::exigirLogin();
        $id = (int) ($_GET['id'] ?? 0);
        $arte = ArteGerada::buscarPorId($id);
        if (!$arte || $arte['tipo'] !== 'boas_vindas') {
            header('Location: /colaboradores');
            exit;
        }
        $colaborador = Colaborador::buscarPorId((int) $arte['colaborador_id']);
        $titulo = 'Boas-vindas gerada';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/boas_vindas/detalhe.php';
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