<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Configuracao;

class ConfiguracaoController
{
    private string $pastaUploads;

    public function __construct()
    {
        $this->pastaUploads = __DIR__ . '/../../public/assets/uploads/sistema/';
    }

    public function form(): void
    {
        Auth::exigirSuperAdmin();
        $config = Configuracao::obter();
        $titulo = 'Personalização';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/configuracoes/form.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    public function update(): void
    {
        Auth::exigirSuperAdmin();

        $configAtual = Configuracao::obter();

        $nomeSistema = trim($_POST['nome_sistema'] ?? '') ?: 'Enterprise Ranking';
        $corPrimaria = $this->normalizarHex($_POST['cor_primaria'] ?? '', $configAtual['cor_primaria']);
        $corBarraLateral = $this->normalizarHex($_POST['cor_barra_lateral'] ?? '', $configAtual['cor_barra_lateral']);

        $logo = $configAtual['logo'];
        $erroUpload = $this->salvarImagemSeEnviada('logo', $logo);
        if ($erroUpload) {
            $_SESSION['erro_configuracao'] = $erroUpload;
            header('Location: /configuracoes');
            exit;
        }

        $favicon = $configAtual['favicon'];
        $erroUpload = $this->salvarImagemSeEnviada('favicon', $favicon);
        if ($erroUpload) {
            $_SESSION['erro_configuracao'] = $erroUpload;
            header('Location: /configuracoes');
            exit;
        }

        Configuracao::atualizar([
            'nome_sistema'      => $nomeSistema,
            'logo'              => $logo,
            'favicon'           => $favicon,
            'cor_primaria'      => $corPrimaria,
            'cor_barra_lateral' => $corBarraLateral,
            'atualizado_por'    => Auth::id(),
        ]);

        $_SESSION['sucesso_configuracao'] = 'Personalização salva com sucesso.';
        header('Location: /configuracoes');
        exit;
    }

    /**
     * Se um arquivo novo foi enviado no campo $nomeCampo, valida (só imagem,
     * até 2MB), salva e atualiza $caminhoAtual por referência. Se nada foi
     * enviado, não mexe em $caminhoAtual (mantém a imagem anterior).
     * Retorna uma mensagem de erro, ou null se deu tudo certo.
     */
    private function salvarImagemSeEnviada(string $nomeCampo, ?string &$caminhoAtual): ?string
    {
        if (empty($_FILES[$nomeCampo]['name'])) {
            return null;
        }
        if ($_FILES[$nomeCampo]['error'] !== UPLOAD_ERR_OK) {
            return 'Erro ao enviar o arquivo de ' . $nomeCampo . '.';
        }

        $extensoesPermitidas = ['png', 'jpg', 'jpeg', 'svg', 'ico'];
        $extensao = strtolower(pathinfo($_FILES[$nomeCampo]['name'], PATHINFO_EXTENSION));
        if (!in_array($extensao, $extensoesPermitidas, true)) {
            return 'Formato não permitido pra ' . $nomeCampo . '. Use PNG, JPG, SVG ou ICO.';
        }

        if ($_FILES[$nomeCampo]['size'] > 2 * 1024 * 1024) {
            return 'O arquivo de ' . $nomeCampo . ' precisa ter no máximo 2MB.';
        }

        if (!is_dir($this->pastaUploads)) {
            mkdir($this->pastaUploads, 0755, true);
        }

        $nomeArquivo = $nomeCampo . '_' . uniqid() . '.' . $extensao;
        if (!move_uploaded_file($_FILES[$nomeCampo]['tmp_name'], $this->pastaUploads . $nomeArquivo)) {
            return 'Não consegui salvar o arquivo de ' . $nomeCampo . '.';
        }

        $caminhoAtual = 'assets/uploads/sistema/' . $nomeArquivo;
        return null;
    }

    private function normalizarHex(string $cor, string $padrao): string
    {
        $cor = trim($cor);
        return preg_match('/^#[0-9a-fA-F]{6}$/', $cor) ? $cor : $padrao;
    }
}