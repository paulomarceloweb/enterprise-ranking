<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Colaborador;
use App\Models\Ocorrencia;
use App\Models\TipoOcorrencia;

class OcorrenciaController
{
    private string $pastaAnexos;

    // Extensões e limite de tamanho aceitos pros anexos de ocorrência
    private const EXTENSOES_PERMITIDAS = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
    private const TAMANHO_MAXIMO = 10 * 1024 * 1024; // 10MB

    public function __construct()
    {
        // Fora de public/ de propósito: documentos de R.H. (PMO, advertências etc)
        // são sensíveis e não podem ficar acessíveis por URL adivinhada.
        $this->pastaAnexos = __DIR__ . '/../../storage/ocorrencias/';
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
        $tipos = TipoOcorrencia::listar();
        $titulo = 'Registrar Ocorrência';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/ocorrencias/nova.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    public function store(): void
    {
        Auth::exigirLogin();

        $colaboradorId = (int) ($_POST['colaborador_id'] ?? 0);
        $tipoOcorrenciaId = (int) ($_POST['tipo_ocorrencia_id'] ?? 0);
        $dataOcorrencia = $_POST['data_ocorrencia'] ?? ''; // vira a coluna data_evento no banco
        $descricao = trim($_POST['descricao'] ?? '') ?: null;

        if (!$colaboradorId || !$tipoOcorrenciaId || !$dataOcorrencia) {
            $_SESSION['erro_ocorrencia'] = 'Preencha colaborador, tipo e data da ocorrência.';
            header('Location: /ocorrencias/nova?colaborador_id=' . $colaboradorId);
            exit;
        }

        $arquivoSalvo = null;
        $nomeArquivoOriginal = null;

        if (!empty($_FILES['anexo']['name'])) {
            if ($_FILES['anexo']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['erro_ocorrencia'] = 'Erro ao enviar o anexo. Tente novamente.';
                header('Location: /ocorrencias/nova?colaborador_id=' . $colaboradorId);
                exit;
            }

            $extensao = strtolower(pathinfo($_FILES['anexo']['name'], PATHINFO_EXTENSION));
            if (!in_array($extensao, self::EXTENSOES_PERMITIDAS, true)) {
                $_SESSION['erro_ocorrencia'] = 'Formato de anexo não permitido. Use PDF, DOC, DOCX, XLS ou XLSX.';
                header('Location: /ocorrencias/nova?colaborador_id=' . $colaboradorId);
                exit;
            }

            if ($_FILES['anexo']['size'] > self::TAMANHO_MAXIMO) {
                $_SESSION['erro_ocorrencia'] = 'O anexo precisa ter no máximo 10MB.';
                header('Location: /ocorrencias/nova?colaborador_id=' . $colaboradorId);
                exit;
            }

            if (!is_dir($this->pastaAnexos)) {
                mkdir($this->pastaAnexos, 0755, true);
            }

            $nomeArquivoOriginal = $_FILES['anexo']['name'];
            $arquivoSalvo = uniqid('ocorrencia_', true) . '.' . $extensao;
            if (!move_uploaded_file($_FILES['anexo']['tmp_name'], $this->pastaAnexos . $arquivoSalvo)) {
                $_SESSION['erro_ocorrencia'] = 'Não consegui salvar o anexo no servidor.';
                header('Location: /ocorrencias/nova?colaborador_id=' . $colaboradorId);
                exit;
            }
        }

        Ocorrencia::registrar([
            'colaborador_id'        => $colaboradorId,
            'tipo_ocorrencia_id'    => $tipoOcorrenciaId,
            'data_evento'           => $dataOcorrencia,
            'descricao'             => $descricao,
            'arquivo'               => $arquivoSalvo,
            'nome_arquivo_original' => $nomeArquivoOriginal,
            'criado_por'            => Auth::id(),
        ]);

        header('Location: /colaboradores/editar?id=' . $colaboradorId);
        exit;
    }

    /**
     * Serve o anexo de volta — o arquivo fica fora de public/, então
     * precisa passar por aqui (e por Auth::exigirLogin()) em vez de link direto.
     */
    public function baixarAnexo(): void
    {
        Auth::exigirLogin();

        $id = (int) ($_GET['id'] ?? 0);
        $ocorrencia = Ocorrencia::buscarPorId($id);
        if (!$ocorrencia || empty($ocorrencia['arquivo'])) {
            header('Location: /colaboradores');
            exit;
        }

        $caminhoFisico = $this->pastaAnexos . $ocorrencia['arquivo'];
        if (!is_file($caminhoFisico)) {
            $_SESSION['erro_ocorrencia'] = 'Esse anexo não está mais disponível no servidor.';
            header('Location: /colaboradores/editar?id=' . $ocorrencia['colaborador_id']);
            exit;
        }

        $nomeParaDownload = $ocorrencia['nome_arquivo_original'] ?: $ocorrencia['arquivo'];

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $nomeParaDownload . '"');
        header('Content-Length: ' . filesize($caminhoFisico));
        readfile($caminhoFisico);
        exit;
    }

    public function destroy(): void
    {
        Auth::exigirLogin();

        $id = (int) ($_POST['id'] ?? 0);
        $ocorrencia = Ocorrencia::buscarPorId($id);
        if (!$ocorrencia) {
            header('Location: /colaboradores');
            exit;
        }

        if (!empty($ocorrencia['arquivo'])) {
            $caminhoFisico = $this->pastaAnexos . $ocorrencia['arquivo'];
            if (is_file($caminhoFisico)) {
                @unlink($caminhoFisico);
            }
        }

        Ocorrencia::excluir($id);

        header('Location: /colaboradores/editar?id=' . $ocorrencia['colaborador_id']);
        exit;
    }
}