<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\ExportadorExcel;
use App\Models\Colaborador;
use App\Models\Cidade;
use App\Models\Departamento;
use App\Models\Regional;
use App\Models\HistoricoColaborador;
use App\Models\Ocorrencia;

class ColaboradorController
{
    private string $pastaUploads;

    public function __construct()
    {
        $this->pastaUploads = __DIR__ . '/../../public/assets/uploads/colaboradores/';
    }

    public function index(): void
    {
        Auth::exigirLogin();

        $filtros = $this->filtrosDaQuerystring();
        $colaboradores = Colaborador::listar($filtros);
        $cidades = Cidade::listar();
        $departamentos = Departamento::listar();
        $regionais = Regional::listar();
        $titulo = 'Colaboradores';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/colaboradores/index.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    /**
     * Mesma listagem da tela (com os mesmos filtros aplicados via
     * querystring), só que exportada em .xlsx em vez de renderizada em HTML.
     */
    public function exportar(): void
    {
        Auth::exigirLogin();

        $filtros = $this->filtrosDaQuerystring();
        $colaboradores = Colaborador::listar($filtros);

        $rotulosEstadoCivil = ['solteiro' => 'Solteiro(a)', 'casado' => 'Casado(a)', 'uniao_estavel' => 'União estável', 'divorciado' => 'Divorciado(a)', 'viuvo' => 'Viúvo(a)'];

        $linhas = [];
        foreach ($colaboradores as $colaborador) {
            $linhas[] = [
                $colaborador['nome'],
                $colaborador['sexo'] === 'masculino' ? 'Masculino' : 'Feminino',
                $rotulosEstadoCivil[$colaborador['estado_civil']] ?? '',
                $colaborador['quantidade_filhos'] ?? 0,
                trim(($colaborador['cidade_nome'] ?? '') . '/' . ($colaborador['cidade_uf'] ?? ''), '/'),
                $colaborador['departamento_nome'] ?? '',
                $colaborador['cargo'],
                $colaborador['status'] === 'ativo' ? 'Ativo' : 'Desligado',
                !empty($colaborador['data_admissao']) ? date('d/m/Y', strtotime($colaborador['data_admissao'])) : '',
                !empty($colaborador['data_nascimento']) ? date('d/m/Y', strtotime($colaborador['data_nascimento'])) : '',
                $colaborador['telefone'] ?? '',
                $colaborador['email'] ?? '',
            ];
        }

        ExportadorExcel::baixar('Colaboradores_' . date('Y-m-d') . '.xlsx', [
            'Nome', 'Sexo', 'Estado civil', 'Filhos', 'Cidade', 'Departamento', 'Cargo', 'Status', 'Admissão', 'Nascimento', 'Telefone', 'E-mail',
        ], $linhas);
    }

    /**
     * Lê os filtros da tela de Colaboradores a partir da querystring —
     * usado tanto na listagem normal quanto na exportação, pra manter
     * os dois sempre em sincronia.
     */
    private function filtrosDaQuerystring(): array
    {
        $filtros = [
            'busca'                => trim((string) ($_GET['busca'] ?? '')),
            'status'                => (string) ($_GET['status'] ?? ''),
            'sexo'                  => (string) ($_GET['sexo'] ?? ''),
            'estado_civil'          => (string) ($_GET['estado_civil'] ?? ''),
            'quantidade_filhos'     => (string) ($_GET['quantidade_filhos'] ?? ''),
            'cidade_id'             => (string) ($_GET['cidade_id'] ?? ''),
            'departamento_id'       => (string) ($_GET['departamento_id'] ?? ''),
            'regional_id'           => (string) ($_GET['regional_id'] ?? ''),
            'aniversariantes_mes'   => (string) ($_GET['aniversariantes_mes'] ?? ''),
            'admitidos_desde'       => (string) ($_GET['admitidos_desde'] ?? ''),
        ];
        // Remove os vazios pra não sujar a query nem a URL
        return array_filter($filtros, fn ($valor) => $valor !== '');
    }

    public function create(): void
    {
        Auth::exigirLogin();
        $cidades = Cidade::listar();
        $departamentos = Departamento::listar();
        $titulo = 'Novo Colaborador';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/colaboradores/novo.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    public function edit(): void
    {
        Auth::exigirLogin();
        $id = (int) ($_GET['id'] ?? 0);
        $colaborador = Colaborador::buscarPorId($id);
        if (!$colaborador) {
            header('Location: /colaboradores');
            exit;
        }
        $cidades = Cidade::listar();
        $departamentos = Departamento::listar();
        $historico = HistoricoColaborador::listarPorColaborador($id);
        $ocorrencias = Ocorrencia::listarPorColaborador($id);
        $titulo = 'Editar Colaborador';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/colaboradores/editar.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    public function store(): void
    {
        Auth::exigirLogin();

        $foto = $this->salvarFoto();
        if ($foto === null) {
            $_SESSION['erro_colaborador'] = 'Erro ao enviar a foto. Verifique se selecionou um arquivo JPG/PNG de até 5MB.';
            header('Location: /colaboradores/novo');
            exit;
        }

        $dados = $this->dadosDoFormulario();
        $dados['foto'] = $foto;

        $id = Colaborador::criar($dados);

        HistoricoColaborador::registrar($id, 'admissao', $dados['data_admissao'], 'Cadastro inicial no sistema', Auth::id());

        header('Location: /colaboradores');
        exit;
    }

    public function update(): void
    {
        Auth::exigirLogin();
        $id = (int) ($_POST['id'] ?? 0);
        if (!$id) {
            header('Location: /colaboradores');
            exit;
        }

        $colaboradorAntes = Colaborador::buscarPorId($id);
        $dados = $this->dadosDoFormulario();
        Colaborador::atualizar($id, $dados);

        // Se o status mudou de/para desligado, registra o evento no histórico automaticamente
        if ($colaboradorAntes && $colaboradorAntes['status'] !== $dados['status']) {
            if ($dados['status'] === 'desligado') {
                HistoricoColaborador::registrar(
                    $id,
                    'desligamento',
                    $dados['data_desligamento'] ?? date('Y-m-d'),
                    'Status alterado para desligado',
                    Auth::id()
                );
            } elseif ($colaboradorAntes['status'] === 'desligado' && $dados['status'] === 'ativo') {
                HistoricoColaborador::registrar($id, 'readmissao', date('Y-m-d'), 'Status alterado de volta para ativo', Auth::id());
            }
        }

        if (!empty($_FILES['foto']['name'])) {
            $foto = $this->salvarFoto();
            if ($foto !== null) {
                Colaborador::atualizarFoto($id, $foto);
            }
        }

        header('Location: /colaboradores/editar?id=' . $id);
        exit;
    }

    public function destroy(): void
    {
        Auth::exigirLogin();
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            Colaborador::excluir($id);
        }
        header('Location: /colaboradores');
        exit;
    }

    private function dadosDoFormulario(): array
    {
        $quantidadeFilhos = $_POST['quantidade_filhos'] ?? '';

        return [
            'nome' => trim($_POST['nome'] ?? ''),
            'sexo' => $_POST['sexo'] ?? 'masculino',
            'estado_civil' => ($_POST['estado_civil'] ?? '') !== '' ? $_POST['estado_civil'] : null,
            'quantidade_filhos' => $quantidadeFilhos !== '' ? (int) $quantidadeFilhos : null,
            'cidade_id' => (int) ($_POST['cidade_id'] ?? 0),
            'departamento_id' => (int) ($_POST['departamento_id'] ?? 0),
            'cargo' => trim($_POST['cargo'] ?? ''),
            'data_admissao' => $_POST['data_admissao'] ?? null,
            'data_nascimento' => $_POST['data_nascimento'] ?? null,
            'status' => ($_POST['status'] ?? 'ativo') === 'desligado' ? 'desligado' : 'ativo',
            'data_desligamento' => trim($_POST['data_desligamento'] ?? '') ?: null,
            'telefone' => trim($_POST['telefone'] ?? '') ?: null,
            'email' => trim($_POST['email'] ?? '') ?: null,
            'instagram' => trim($_POST['instagram'] ?? '') ?: null,
            'facebook' => trim($_POST['facebook'] ?? '') ?: null,
            'observacoes' => trim($_POST['observacoes'] ?? '') ?: null,
        ];
    }

    /**
     * Salva a foto do colaborador já comprimida: redimensiona pra no máximo
     * 1200px de largura e reencoda sempre como JPEG qualidade 82%, não importa
     * se o original era PNG. Isso evita que o banco/storage encha com fotos
     * de celular de 4-8MB cada — o arquivo final normalmente fica bem abaixo de 300KB.
     */
    private function salvarFoto(): ?string
    {
        if (empty($_FILES['foto']['name'])) {
            return null;
        }

        if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $extensoesPermitidas = ['jpg', 'jpeg', 'png'];
        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (!in_array($extensao, $extensoesPermitidas, true)) {
            return null;
        }

        // Limite generoso de upload (antes de comprimir) — depois de reencodada
        // a imagem final fica bem menor que isso.
        if ($_FILES['foto']['size'] > 15 * 1024 * 1024) {
            return null;
        }

        $infoImagem = @getimagesize($_FILES['foto']['tmp_name']);
        if ($infoImagem === false) {
            return null;
        }

        $origem = match ($infoImagem[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($_FILES['foto']['tmp_name']),
            IMAGETYPE_PNG => @imagecreatefrompng($_FILES['foto']['tmp_name']),
            default => null,
        };
        if ($origem === null || $origem === false) {
            return null;
        }

        $larguraOriginal = imagesx($origem);
        $alturaOriginal = imagesy($origem);
        $larguraMaxima = 1200;

        if ($larguraOriginal > $larguraMaxima) {
            $alturaFinal = (int) round($alturaOriginal * ($larguraMaxima / $larguraOriginal));
            $redimensionada = imagecreatetruecolor($larguraMaxima, $alturaFinal);
        } else {
            $larguraMaxima = $larguraOriginal;
            $alturaFinal = $alturaOriginal;
            $redimensionada = imagecreatetruecolor($larguraOriginal, $alturaOriginal);
        }

        // Fundo branco (PNG com transparência não pode virar JPEG direto,
        // senão a área transparente vira preto)
        $branco = imagecolorallocate($redimensionada, 255, 255, 255);
        imagefilledrectangle($redimensionada, 0, 0, $larguraMaxima, $alturaFinal, $branco);
        imagecopyresampled($redimensionada, $origem, 0, 0, 0, 0, $larguraMaxima, $alturaFinal, $larguraOriginal, $alturaOriginal);
        imagedestroy($origem);

        if (!is_dir($this->pastaUploads)) {
            mkdir($this->pastaUploads, 0755, true);
        }

        $nomeArquivo = uniqid('colab_', true) . '.jpg';
        $destino = $this->pastaUploads . $nomeArquivo;

        imagejpeg($redimensionada, $destino, 82);
        imagedestroy($redimensionada);

        return 'assets/uploads/colaboradores/' . $nomeArquivo;
    }
}