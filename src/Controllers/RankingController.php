<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Colaborador;
use App\Models\Departamento;
use App\Models\Ranking;
use App\Models\ArteGerada;
use App\Models\PlanilhaRanking;
use App\Templates\RankingTemplate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class RankingController
{
    private string $pastaArtes;
    private string $pastaTemp;
    private string $pastaPlanilhas;

    public function __construct()
    {
        $this->pastaArtes = __DIR__ . '/../../public/assets/artes/ranking/';
        // Fora de public/ de propósito: arquivo de planilha enviado não precisa (e não deve) ser acessível via URL
        $this->pastaTemp = __DIR__ . '/../../storage/temp/';
        // Planilhas já processadas ficam retidas aqui (histórico), também fora de public/
        $this->pastaPlanilhas = __DIR__ . '/../../storage/planilhas/';
    }

    public function index(): void
    {
        Auth::exigirLogin();
        $rankings = Ranking::listar();
        $titulo = 'Ranking';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/ranking/index.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    public function create(): void
    {
        Auth::exigirLogin();
        $colaboradores = Colaborador::listar();
        $titulo = 'Gerar Ranking - Individual';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/ranking/novo.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    /**
     * Gera a arte de ranking para UM colaborador (fluxo individual).
     */
    public function store(): void
    {
        Auth::exigirLogin();

        $colaboradorId = (int) ($_POST['colaborador_id'] ?? 0);
        $colocacao = (int) ($_POST['colocacao'] ?? 0);
        $mes = (int) ($_POST['mes'] ?? 0);
        $ano = (int) ($_POST['ano'] ?? 0);

        if (!$colaboradorId || !$colocacao || !$mes || !$ano) {
            $_SESSION['erro_ranking'] = 'Preencha colaborador, colocação, mês e ano.';
            header('Location: /ranking/novo');
            exit;
        }

        $colaborador = Colaborador::buscarPorId($colaboradorId);
        if (!$colaborador) {
            $_SESSION['erro_ranking'] = 'Colaborador não encontrado.';
            header('Location: /ranking/novo');
            exit;
        }

        $rankingId = Ranking::localizarOuCriar($mes, $ano, Auth::id());

        $caminhoFoto = __DIR__ . '/../../public/' . $colaborador['foto'];

        $setorParaArquivo = $colaborador['departamento_nome'] ?? $colaborador['cargo'];
        $nomeArquivo = $this->gerarNomeArquivo($colaborador['nome'], $mes, $ano, $colocacao, $setorParaArquivo);
        $caminhoDestino = $this->pastaArtes . $nomeArquivo;

        try {
            $template = new RankingTemplate();
            $template->gerar([
                'nome'      => $colaborador['nome'],
                'foto'      => $caminhoFoto,
                'setor'     => $colaborador['departamento_nome'] ?? $colaborador['cargo'],
                'cidade'    => trim(($colaborador['cidade_nome'] ?? '') . '/' . ($colaborador['cidade_uf'] ?? ''), '/'),
                'colocacao' => $colocacao,
                'mes'       => $mes,
                'ano'       => $ano,
            ], $caminhoDestino);
        } catch (\Throwable $e) {
            $_SESSION['erro_ranking'] = 'Erro ao gerar a imagem: ' . $e->getMessage();
            header('Location: /ranking/novo');
            exit;
        }

        ArteGerada::registrar([
            'ranking_id'      => $rankingId,
            'colaborador_id'  => $colaboradorId,
            'tipo'            => 'ranking',
            'colocacao'       => $colocacao,
            'setor'           => $setorParaArquivo,
            'mes'             => $mes,
            'ano'             => $ano,
            'caminho_imagem'  => 'assets/artes/ranking/' . $nomeArquivo,
            'gerado_por'      => Auth::id(),
        ]);

        header('Location: /ranking/detalhe?id=' . $rankingId);
        exit;
    }

    public function show(): void
    {
        Auth::exigirLogin();
        $id = (int) ($_GET['id'] ?? 0);
        $ranking = Ranking::buscarPorId($id);
        if (!$ranking) {
            header('Location: /ranking');
            exit;
        }
        $artes = ArteGerada::listarPorRanking($id);
        $titulo = 'Ranking ' . $ranking['mes'] . '/' . $ranking['ano'];
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/ranking/detalhe.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    /**
     * Baixa todas as artes de um ranking num único .zip, organizadas em
     * subpastas por setor — pra ficar fácil pro R.H. separar/enviar por grupo.
     */
    public function baixarZip(): void
    {
        Auth::exigirLogin();

        $id = (int) ($_GET['id'] ?? 0);
        $ranking = Ranking::buscarPorId($id);
        if (!$ranking) {
            header('Location: /ranking');
            exit;
        }

        $artes = ArteGerada::listarPorRankingAgrupadoPorSetor($id);
        if (empty($artes)) {
            header('Location: /ranking/detalhe?id=' . $id);
            exit;
        }

        if (!class_exists('ZipArchive')) {
            $_SESSION['erro_ranking'] = 'A extensão ZipArchive do PHP não está habilitada no servidor — sem ela não dá pra gerar o .zip.';
            header('Location: /ranking/detalhe?id=' . $id);
            exit;
        }

        $nomeZip = 'Ranking_' . $ranking['ano'] . '-' . str_pad((string) $ranking['mes'], 2, '0', STR_PAD_LEFT) . '.zip';
        $caminhoZipTemp = $this->pastaTemp . 'zip_' . uniqid() . '.zip';

        if (!is_dir($this->pastaTemp)) {
            mkdir($this->pastaTemp, 0755, true);
        }

        $zip = new \ZipArchive();
        $zip->open($caminhoZipTemp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($artes as $arte) {
            $caminhoFisico = __DIR__ . '/../../public/' . $arte['caminho_imagem'];
            if (!is_file($caminhoFisico)) {
                continue;
            }
            $pastaSetor = $this->slug($arte['setor'] ?: 'sem-setor');
            $nomeArquivoNoZip = $pastaSetor . '/' . basename($arte['caminho_imagem']);
            $zip->addFile($caminhoFisico, $nomeArquivoNoZip);
        }

        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $nomeZip . '"');
        header('Content-Length: ' . filesize($caminhoZipTemp));
        readfile($caminhoZipTemp);
        @unlink($caminhoZipTemp);
        exit;
    }

    /**
     * Regenera UMA arte específica sem mexer no resto do lote — útil quando
     * a foto do colaborador foi trocada depois, ou o template mudou, e não
     * vale a pena reprocessar a planilha inteira de novo. Sobrescreve o
     * mesmo arquivo de imagem e mantém o registro em artes_geradas.
     */
    public function regenerarArte(): void
    {
        Auth::exigirLogin();

        $id = (int) ($_POST['id'] ?? 0);
        $arte = ArteGerada::buscarPorId($id);

        if (!$arte || $arte['tipo'] !== 'ranking') {
            header('Location: /ranking');
            exit;
        }

        $colaborador = Colaborador::buscarPorId((int) $arte['colaborador_id']);
        if (!$colaborador) {
            $_SESSION['erro_ranking'] = 'Colaborador não encontrado — não dá pra regenerar essa arte.';
            header('Location: /ranking/detalhe?id=' . $arte['ranking_id']);
            exit;
        }

        $caminhoFoto = __DIR__ . '/../../public/' . $colaborador['foto'];
        $caminhoDestino = __DIR__ . '/../../public/' . $arte['caminho_imagem'];

        try {
            $template = new RankingTemplate();
            $template->gerar([
                'nome'      => $colaborador['nome'],
                'foto'      => $caminhoFoto,
                'setor'     => $arte['setor'] ?: ($colaborador['departamento_nome'] ?? $colaborador['cargo']),
                'cidade'    => trim(($colaborador['cidade_nome'] ?? '') . '/' . ($colaborador['cidade_uf'] ?? ''), '/'),
                'colocacao' => (int) $arte['colocacao'],
                'mes'       => (int) $arte['mes'],
                'ano'       => (int) $arte['ano'],
            ], $caminhoDestino);
        } catch (\Throwable $e) {
            $_SESSION['erro_ranking'] = 'Erro ao regenerar a arte: ' . $e->getMessage();
        }

        header('Location: /ranking/detalhe?id=' . $arte['ranking_id']);
        exit;
    }

    /**
     * Marca/desmarca o ranking do mês como já enviado ao time.
     */
    public function marcarEnviado(): void
    {
        Auth::exigirLogin();
        $id = (int) ($_POST['id'] ?? 0);
        Ranking::marcarComoEnviado($id);
        header('Location: /ranking/detalhe?id=' . $id);
        exit;
    }

    public function desmarcarEnviado(): void
    {
        Auth::exigirLogin();
        $id = (int) ($_POST['id'] ?? 0);
        Ranking::desmarcarEnviado($id);
        header('Location: /ranking/detalhe?id=' . $id);
        exit;
    }

    /**
     * Exporta o ranking gerado (não importa se veio de planilha, manual ou
     * individual) de volta pro formato .xlsx — mesmo modelo aceito no
     * upload: uma linha por setor, colunas "1° Lugar", "2° Lugar"...
     * Isso fecha a via de mão dupla: sobe planilha OU monta pelo sistema,
     * e sempre dá pra baixar a planilha equivalente depois.
     */
    public function exportarXlsx(): void
    {
        Auth::exigirLogin();

        $id = (int) ($_GET['id'] ?? 0);
        $ranking = Ranking::buscarPorId($id);
        if (!$ranking) {
            header('Location: /ranking');
            exit;
        }

        $artes = ArteGerada::listarPorRankingAgrupadoPorSetor($id);
        if (empty($artes)) {
            header('Location: /ranking/detalhe?id=' . $id);
            exit;
        }

        // Agrupa: setor => colocacao => [nomes] (lista porque pode ter empate)
        $porSetor = [];
        $colocacaoMaxima = 1;
        foreach ($artes as $arte) {
            $setor = $arte['setor'] ?: 'Sem setor';
            $colocacao = (int) $arte['colocacao'];
            $colocacaoMaxima = max($colocacaoMaxima, $colocacao);
            $porSetor[$setor][$colocacao][] = $arte['colaborador_nome'];
        }

        $planilha = new Spreadsheet();
        $aba = $planilha->getActiveSheet();
        $aba->setTitle(substr($ranking['ano'] . '-' . str_pad((string) $ranking['mes'], 2, '0', STR_PAD_LEFT), 0, 31));

        $aba->setCellValue('A1', 'Departamento');
        $aba->setCellValue('B1', 'Setor');
        for ($colocacao = 1; $colocacao <= $colocacaoMaxima; $colocacao++) {
            $coluna = Coordinate::stringFromColumnIndex(2 + $colocacao);
            $aba->setCellValue($coluna . '1', $colocacao . '° Lugar');
        }

        $linha = 2;
        foreach ($porSetor as $setor => $colocacoes) {
            $aba->setCellValue('B' . $linha, $setor);
            for ($colocacao = 1; $colocacao <= $colocacaoMaxima; $colocacao++) {
                $coluna = Coordinate::stringFromColumnIndex(2 + $colocacao);
                $nomes = $colocacoes[$colocacao] ?? [];
                $aba->setCellValue($coluna . $linha, implode(', ', $nomes));
            }
            $linha++;
        }

        $ultimaColuna = Coordinate::stringFromColumnIndex(2 + $colocacaoMaxima);
        foreach (Coordinate::extractAllCellReferencesInRange('A1:' . $ultimaColuna . '1') as $referencia) {
            $letra = Coordinate::coordinateFromString($referencia)[0];
            $aba->getColumnDimension($letra)->setAutoSize(true);
        }

        $nomeArquivo = 'Ranking_' . $ranking['ano'] . '-' . str_pad((string) $ranking['mes'], 2, '0', STR_PAD_LEFT) . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($planilha);
        $writer->save('php://output');
        exit;
    }

    /**
     * Exclui um ranking inteiro: apaga os registros de artes_geradas,
     * os arquivos de imagem físicos e por fim o registro do ranking.
     * Só o Super Admin pode fazer isso.
     */
    public function destroy(): void
    {
        Auth::exigirSuperAdmin();

        $id = (int) ($_POST['id'] ?? 0);
        $ranking = Ranking::buscarPorId($id);
        if (!$ranking) {
            header('Location: /ranking');
            exit;
        }

        $artes = ArteGerada::listarPorRanking($id);
        foreach ($artes as $arte) {
            $caminhoFisico = __DIR__ . '/../../public/' . $arte['caminho_imagem'];
            if (is_file($caminhoFisico)) {
                @unlink($caminhoFisico);
            }
        }

        ArteGerada::excluirPorRanking($id);
        Ranking::excluir($id);

        header('Location: /ranking');
        exit;
    }

    // ------------------------------------------------------------------
    // CADASTRO MANUAL (monta o ranking do mês inteiro direto pelo sistema,
    // linha a linha, sem precisar de planilha)
    // ------------------------------------------------------------------

    public function manualForm(): void
    {
        Auth::exigirLogin();
        $colaboradores = Colaborador::listar();
        $departamentos = Departamento::listar();
        $mesSugerido = (int) date('n');
        $anoSugerido = (int) date('Y');
        $titulo = 'Gerar Ranking - Cadastro Manual';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/ranking/manual.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    /**
     * Recebe as linhas montadas manualmente (arrays paralelos setor[],
     * colocacao[], colaborador_id[]) e gera uma arte pra cada uma — mesmo
     * motor de geração usado no lote via planilha. Reaproveita a tela de
     * resultado do lote pra mostrar o que foi gerado.
     */
    public function manualGerar(): void
    {
        Auth::exigirLogin();

        $mes = (int) ($_POST['mes'] ?? 0);
        $ano = (int) ($_POST['ano'] ?? 0);
        $setores = $_POST['setor'] ?? [];
        $colocacoes = $_POST['colocacao'] ?? [];
        $colaboradorIds = $_POST['colaborador_id'] ?? [];

        if (!$mes || !$ano || empty($colaboradorIds)) {
            $_SESSION['erro_lote'] = 'Preencha mês, ano e adicione pelo menos uma linha com colaborador.';
            header('Location: /ranking/manual/novo');
            exit;
        }

        $rankingId = Ranking::localizarOuCriar($mes, $ano, Auth::id());
        $template = new RankingTemplate();

        $gerados = 0;
        $naoEncontrados = [];
        $totalLinhas = count($colaboradorIds);

        for ($i = 0; $i < $totalLinhas; $i++) {
            $colaboradorId = (int) ($colaboradorIds[$i] ?? 0);
            $colocacao = (int) ($colocacoes[$i] ?? 0);
            $setor = trim((string) ($setores[$i] ?? ''));

            if (!$colaboradorId || !$colocacao) {
                continue; // linha em branco, ignora
            }

            $colaborador = Colaborador::buscarPorId($colaboradorId);
            if (!$colaborador) {
                $naoEncontrados[] = 'Linha ' . ($i + 1) . ': colaborador não encontrado.';
                continue;
            }

            if ($setor === '') {
                $setor = $colaborador['departamento_nome'] ?? $colaborador['cargo'];
            }

            $caminhoFoto = __DIR__ . '/../../public/' . $colaborador['foto'];
            $nomeArquivo = $this->gerarNomeArquivo($colaborador['nome'], $mes, $ano, $colocacao, $setor);
            $caminhoDestino = $this->pastaArtes . $nomeArquivo;

            try {
                $template->gerar([
                    'nome'      => $colaborador['nome'],
                    'foto'      => $caminhoFoto,
                    'setor'     => $setor,
                    'cidade'    => trim(($colaborador['cidade_nome'] ?? '') . '/' . ($colaborador['cidade_uf'] ?? ''), '/'),
                    'colocacao' => $colocacao,
                    'mes'       => $mes,
                    'ano'       => $ano,
                ], $caminhoDestino);
            } catch (\Throwable $e) {
                $naoEncontrados[] = $colaborador['nome'] . ' (erro ao gerar: ' . $e->getMessage() . ')';
                continue;
            }

            ArteGerada::registrar([
                'ranking_id'     => $rankingId,
                'colaborador_id' => $colaborador['id'],
                'tipo'           => 'ranking',
                'colocacao'      => $colocacao,
                'setor'          => $setor,
                'mes'            => $mes,
                'ano'            => $ano,
                'caminho_imagem' => 'assets/artes/ranking/' . $nomeArquivo,
                'gerado_por'     => Auth::id(),
            ]);

            $gerados++;
        }

        $_SESSION['resultado_lote'] = [
            'total'           => $gerados + count($naoEncontrados),
            'gerados'         => $gerados,
            'nao_encontrados' => $naoEncontrados,
            'ranking_id'      => $rankingId,
            'aba'             => 'Cadastro manual',
        ];

        header('Location: /ranking/lote/resultado');
        exit;
    }

    // ------------------------------------------------------------------
    // GERAÇÃO EM LOTE (planilha .xlsx com uma aba por mês, uma linha por
    // setor e colunas "1° Lugar", "2° Lugar"... com o nome de quem ficou
    // em cada posição naquele setor)
    // ------------------------------------------------------------------

    public function loteForm(): void
    {
        Auth::exigirLogin();
        $titulo = 'Gerar Ranking - Em Lote';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/ranking/lote.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    /**
     * Passo 1: recebe o .xlsx, salva numa pasta temporária (fora de public/)
     * e mostra as abas disponíveis pra R.H. escolher qual mês processar.
     */
    public function loteUpload(): void
    {
        Auth::exigirLogin();

        if (empty($_FILES['planilha']['name']) || $_FILES['planilha']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['erro_lote'] = 'Selecione um arquivo .xlsx válido.';
            header('Location: /ranking/lote');
            exit;
        }

        $extensao = strtolower(pathinfo($_FILES['planilha']['name'], PATHINFO_EXTENSION));
        if ($extensao !== 'xlsx') {
            $_SESSION['erro_lote'] = 'O arquivo precisa ser .xlsx (Excel).';
            header('Location: /ranking/lote');
            exit;
        }

        if ($_FILES['planilha']['size'] > 10 * 1024 * 1024) {
            $_SESSION['erro_lote'] = 'A planilha está maior que 10MB — confira se não é o arquivo errado.';
            header('Location: /ranking/lote');
            exit;
        }

        if (!is_dir($this->pastaTemp)) {
            mkdir($this->pastaTemp, 0755, true);
        }

        // Guarda uma versão "slugificada" do nome original dentro do próprio
        // nome do arquivo temporário (separado por "__"), assim recuperamos
        // um nome legível pro histórico sem precisar mexer nas views de confirmação.
        $nomeOriginalSlug = $this->slug(pathinfo($_FILES['planilha']['name'], PATHINFO_FILENAME));
        $nomeTemp = 'lote_' . uniqid() . '__' . $nomeOriginalSlug . '.xlsx';
        if (!move_uploaded_file($_FILES['planilha']['tmp_name'], $this->pastaTemp . $nomeTemp)) {
            $_SESSION['erro_lote'] = 'Não consegui salvar o arquivo enviado.';
            header('Location: /ranking/lote');
            exit;
        }

        try {
            $planilha = IOFactory::load($this->pastaTemp . $nomeTemp);
            $abas = $planilha->getSheetNames();
        } catch (\Throwable $e) {
            @unlink($this->pastaTemp . $nomeTemp);
            $_SESSION['erro_lote'] = 'Não consegui abrir a planilha: ' . $e->getMessage();
            header('Location: /ranking/lote');
            exit;
        }

        // Sugere a última aba da lista (normalmente é o mês mais recente)
        $abaSugerida = end($abas);

        $titulo = 'Gerar Ranking - Confirmar aba';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/ranking/lote_confirmar.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    /**
     * Passo 2: lê a aba escolhida, casa cada nome com um colaborador
     * cadastrado e gera a arte de quem foi encontrado.
     */
    public function loteProcessar(): void
    {
        Auth::exigirLogin();

        $nomeTemp = basename((string) ($_POST['arquivo_temp'] ?? ''));
        $aba = (string) ($_POST['aba'] ?? '');
        $mes = (int) ($_POST['mes'] ?? 0);
        $ano = (int) ($_POST['ano'] ?? 0);

        $caminhoArquivo = $this->pastaTemp . $nomeTemp;

        if (!$mes || !$ano || $aba === '' || !file_exists($caminhoArquivo)) {
            $_SESSION['erro_lote'] = 'Dados incompletos pra processar o lote. Envie a planilha de novo.';
            header('Location: /ranking/lote');
            exit;
        }

        try {
            $planilha = IOFactory::load($caminhoArquivo);
            if (!$planilha->sheetNameExists($aba)) {
                throw new \RuntimeException('Aba "' . $aba . '" não existe nesse arquivo.');
            }
            $linhas = $planilha->getSheetByName($aba)->toArray(null, true, true, false);
        } catch (\Throwable $e) {
            @unlink($caminhoArquivo);
            $_SESSION['erro_lote'] = 'Erro ao ler a planilha: ' . $e->getMessage();
            header('Location: /ranking/lote');
            exit;
        }

        // Confere se o cabeçalho da aba bate com o modelo esperado antes de
        // processar qualquer coisa — evita gerar artes erradas silenciosamente
        // se alguém subir uma planilha fora do formato.
        $errosModelo = $this->validarCabecalho($linhas[0] ?? []);
        if (!empty($errosModelo)) {
            @unlink($caminhoArquivo);
            $_SESSION['erro_lote'] = 'Essa planilha não bate com o modelo esperado: ' . implode(' ', $errosModelo);
            header('Location: /ranking/lote');
            exit;
        }

        $entradas = $this->extrairEntradasDaAba($linhas);

        if (empty($entradas)) {
            @unlink($caminhoArquivo);
            $_SESSION['erro_lote'] = 'Não encontrei nenhum nome preenchido nessa aba. Confira se escolheu a aba certa.';
            header('Location: /ranking/lote');
            exit;
        }

        $rankingId = Ranking::localizarOuCriar($mes, $ano, Auth::id());
        $template = new RankingTemplate();

        $gerados = 0;
        $naoEncontrados = [];

        foreach ($entradas as $entrada) {
            $colaborador = Colaborador::buscarPorNome($entrada['nome']);
            if (!$colaborador) {
                $naoEncontrados[] = $entrada['nome'] . ' (' . $entrada['colocacao'] . 'º - ' . $entrada['setor'] . ')';
                continue;
            }

            $caminhoFoto = __DIR__ . '/../../public/' . $colaborador['foto'];
            $nomeArquivo = $this->gerarNomeArquivo($colaborador['nome'], $mes, $ano, $entrada['colocacao'], $entrada['setor']);
            $caminhoDestino = $this->pastaArtes . $nomeArquivo;

            try {
                $template->gerar([
                    'nome'      => $colaborador['nome'],
                    'foto'      => $caminhoFoto,
                    'setor'     => $entrada['setor'],
                    'cidade'    => trim(($colaborador['cidade_nome'] ?? '') . '/' . ($colaborador['cidade_uf'] ?? ''), '/'),
                    'colocacao' => $entrada['colocacao'],
                    'mes'       => $mes,
                    'ano'       => $ano,
                ], $caminhoDestino);
            } catch (\Throwable $e) {
                $naoEncontrados[] = $entrada['nome'] . ' (erro ao gerar: ' . $e->getMessage() . ')';
                continue;
            }

            ArteGerada::registrar([
                'ranking_id'     => $rankingId,
                'colaborador_id' => $colaborador['id'],
                'tipo'           => 'ranking',
                'colocacao'      => $entrada['colocacao'],
                'setor'          => $entrada['setor'],
                'mes'            => $mes,
                'ano'            => $ano,
                'caminho_imagem' => 'assets/artes/ranking/' . $nomeArquivo,
                'gerado_por'     => Auth::id(),
            ]);

            $gerados++;
        }

        // Em vez de apagar, guarda a planilha permanentemente pra histórico —
        // fora de public/, então não fica acessível por URL direta.
        if (!is_dir($this->pastaPlanilhas)) {
            mkdir($this->pastaPlanilhas, 0755, true);
        }
        $nomePermanente = date('Y-m-d_His') . '_' . $nomeTemp;
        if (@rename($caminhoArquivo, $this->pastaPlanilhas . $nomePermanente)) {
            $partesNome = explode('__', pathinfo($nomeTemp, PATHINFO_FILENAME), 2);
            $nomeOriginalRecuperado = ($partesNome[1] ?? 'planilha') . '.xlsx';

            PlanilhaRanking::registrar([
                'ranking_id'      => $rankingId,
                'nome_original'   => $nomeOriginalRecuperado,
                'caminho_arquivo' => $nomePermanente,
                'aba_processada'  => $aba,
                'mes'             => $mes,
                'ano'             => $ano,
                'total_entradas'  => count($entradas),
                'total_gerados'   => $gerados,
                'usuario_id'      => Auth::id(),
            ]);
        } else {
            @unlink($caminhoArquivo);
        }

        $_SESSION['resultado_lote'] = [
            'total'           => count($entradas),
            'gerados'         => $gerados,
            'nao_encontrados' => $naoEncontrados,
            'ranking_id'      => $rankingId,
            'aba'             => $aba,
        ];

        header('Location: /ranking/lote/resultado');
        exit;
    }

    public function loteResultado(): void
    {
        Auth::exigirLogin();
        $resultado = $_SESSION['resultado_lote'] ?? null;
        unset($_SESSION['resultado_lote']);

        if (!$resultado) {
            header('Location: /ranking/lote');
            exit;
        }

        $titulo = 'Resultado do Lote';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/ranking/lote_resultado.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    /**
     * Confere se o cabeçalho da aba bate com o modelo esperado:
     * coluna B precisa se chamar "Setor", e precisa existir pelo menos
     * uma coluna de colocação (ex: "1° Lugar") a partir da coluna C.
     * Retorna uma lista de mensagens de erro (vazia = tudo certo).
     */
    private function validarCabecalho(array $cabecalho): array
    {
        $erros = [];

        if (empty($cabecalho)) {
            $erros[] = 'A aba escolhida está vazia.';
            return $erros;
        }

        $colunaSetor = trim((string) ($cabecalho[1] ?? ''));
        if (stripos($colunaSetor, 'setor') === false) {
            $erros[] = 'A coluna B (2ª coluna) deveria se chamar "Setor", mas veio "' . $colunaSetor . '".';
        }

        $temColunaDeColocacao = false;
        foreach ($cabecalho as $indice => $valor) {
            if ($indice < 2) {
                continue;
            }
            if (preg_match('/\d+.{0,3}lugar/ui', (string) $valor)) {
                $temColunaDeColocacao = true;
                break;
            }
        }
        if (!$temColunaDeColocacao) {
            $erros[] = 'Não encontrei nenhuma coluna de colocação (ex: "1° Lugar", "2° Lugar"...) a partir da coluna C.';
        }

        return $erros;
    }

    /**
     * Histórico de planilhas de ranking já enviadas e processadas.
     */
    public function planilhasIndex(): void
    {
        Auth::exigirLogin();
        $planilhas = PlanilhaRanking::listar();
        $titulo = 'Planilhas Enviadas';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/ranking/planilhas.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    /**
     * Baixa de volta uma planilha já processada (fica fora de public/,
     * então precisa passar por aqui em vez de link direto).
     */
    public function baixarPlanilha(): void
    {
        Auth::exigirLogin();
        $id = (int) ($_GET['id'] ?? 0);
        $planilha = PlanilhaRanking::buscarPorId($id);
        if (!$planilha) {
            header('Location: /ranking/planilhas');
            exit;
        }

        $caminhoFisico = $this->pastaPlanilhas . $planilha['caminho_arquivo'];
        if (!is_file($caminhoFisico)) {
            $_SESSION['erro_lote'] = 'O arquivo dessa planilha não está mais disponível no servidor.';
            header('Location: /ranking/planilhas');
            exit;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $planilha['nome_original'] . '"');
        header('Content-Length: ' . filesize($caminhoFisico));
        readfile($caminhoFisico);
        exit;
    }

    /**
     * Transforma a matriz bruta da aba (uma linha por setor, colunas "1° Lugar"...)
     * numa lista simples de entradas [nome, colocacao, setor].
     *
     * Suporta empate: se a célula de uma colocação tiver mais de um nome
     * (separados por vírgula, ponto-e-vírgula, ou quebra de linha dentro da
     * célula — Alt+Enter no Excel), cada nome vira uma entrada própria com a
     * MESMA colocação. Ex: célula "1° Lugar" = "Ana Souza, Bruno Lima" gera
     * duas artes de 1º lugar, uma pra cada.
     */
    private function extrairEntradasDaAba(array $linhas): array
    {
        $cabecalho = $linhas[0] ?? [];

        // Descobre em quais colunas ficam as colocações (1° Lugar, 2° Lugar...)
        $colunasColocacao = [];
        foreach ($cabecalho as $indice => $valor) {
            if ($indice < 2) {
                continue; // colunas 0 e 1 são Departamento e Setor
            }
            if (preg_match('/(\d+)/u', (string) $valor, $m)) {
                $colunasColocacao[$indice] = (int) $m[1];
            }
        }

        $entradas = [];
        $totalLinhas = count($linhas);
        for ($i = 1; $i < $totalLinhas; $i++) {
            $linha = $linhas[$i];
            $setor = trim((string) ($linha[1] ?? ''));
            if ($setor === '') {
                continue;
            }

            foreach ($colunasColocacao as $indiceColuna => $colocacao) {
                $conteudoCelula = trim((string) ($linha[$indiceColuna] ?? ''));
                if ($conteudoCelula === '' || $conteudoCelula === '-') {
                    continue;
                }

                // Divide a célula em múltiplos nomes se houver empate
                $nomes = preg_split('/[,;\r\n]+/u', $conteudoCelula) ?: [$conteudoCelula];

                foreach ($nomes as $nome) {
                    $nome = trim($nome);
                    if ($nome === '') {
                        continue;
                    }
                    $entradas[] = [
                        'nome'      => $nome,
                        'colocacao' => $colocacao,
                        'setor'     => $setor,
                    ];
                }
            }
        }

        return $entradas;
    }

    /**
     * Monta um nome de arquivo legível: Ranking_2026-07_1o-lugar_Marketing_Paulo-Marcelo.png
     * Isso já deixa o sistema pronto pra quando tivermos 50+ pessoas/mês e formos gerar tudo em lote/zip.
     */
    private function gerarNomeArquivo(string $nome, int $mes, int $ano, int $colocacao, string $setor): string
    {
        $mesFormatado = str_pad((string) $mes, 2, '0', STR_PAD_LEFT);
        // Zero à esquerda na colocação: sem isso, "10º" viria ordenado antes de
        // "2º" no explorador de arquivos (ordem de texto, não numérica).
        $colocacaoFormatada = str_pad((string) $colocacao, 2, '0', STR_PAD_LEFT);
        $nomeSlug = $this->slug($nome);
        $setorSlug = $this->slug($setor);

        return sprintf(
            'Ranking_%d-%s_%so-lugar_%s_%s.png',
            $ano,
            $mesFormatado,
            $colocacaoFormatada,
            $setorSlug,
            $nomeSlug
        );
    }

    private function slug(string $texto): string
    {
        $texto = trim($texto);

        if (function_exists('transliterator_transliterate')) {
            $texto = transliterator_transliterate('Any-Latin; Latin-ASCII;', $texto) ?: $texto;
        } else {
            // Fallback sem extensão intl: remove acentos manualmente
            $comAcento = ['á','à','â','ã','ä','é','è','ê','ë','í','ì','î','ï','ó','ò','ô','õ','ö','ú','ù','û','ü','ç','ñ','Á','À','Â','Ã','Ä','É','È','Ê','Ë','Í','Ì','Î','Ï','Ó','Ò','Ô','Õ','Ö','Ú','Ù','Û','Ü','Ç','Ñ'];
            $semAcento  = ['a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','c','n','A','A','A','A','A','E','E','E','E','I','I','I','I','O','O','O','O','O','U','U','U','U','C','N'];
            $texto = str_replace($comAcento, $semAcento, $texto);
        }

        $texto = preg_replace('/[^A-Za-z0-9]+/', '-', $texto);
        $texto = trim((string) $texto, '-');
        return $texto === '' ? 'sem-nome' : $texto;
    }
}