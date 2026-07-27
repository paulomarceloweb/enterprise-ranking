<?php

namespace App\Templates;

/**
 * Gera a arte de Boas-vindas (1080x1080) via GD, seguindo o layout de referência:
 * fundo com gradiente diagonal + marca d'água grande de fundo, foto centralizada,
 * duas pílulas lado a lado ("Seja Bem-Vindo(a)!" / Nome) com selo circular da marca
 * na costura, e uma linha inferior com 3 colunas: logo Mottanet, Ocupação, Localidade.
 *
 * Uso:
 *   $template = new BoasVindasTemplate();
 *   $template->gerar([
 *       'nome'   => 'Edenilson Palhano',
 *       'sexo'   => 'masculino', // ou 'feminino' -> muda "Bem-Vindo"/"Bem-Vinda"
 *       'foto'   => '/caminho/para/foto.jpg',
 *       'cargo'  => 'Aux. Técnico',
 *       'cidade' => 'Carambeí/PR',
 *   ], '/caminho/destino/boas_vindas_123.png');
 */
class BoasVindasTemplate
{
    private const AZUL    = [0, 69, 151];
    private const LARANJA = [255, 147, 8];
    private const BRANCO  = [255, 255, 255];
    private const CINZA_TEXTO = [110, 110, 118];
    private const PILL_CLARA  = [244, 236, 227]; // pêssego bem claro pra pílulas de label

    private const AZUL_CLARO_FUNDO   = [206, 222, 236];
    private const LARANJA_CLARO_FUNDO = [255, 226, 198];

    private const LARGURA = 1080;
    private const ALTURA  = 1080;

    private string $pastaFontes;
    private string $pastaImagens;

    public function __construct()
    {
        $this->pastaFontes  = __DIR__ . '/../../resources/fonts/';
        $this->pastaImagens = __DIR__ . '/../../resources/images/';
    }

    public function gerar(array $dados, string $caminhoDestino): string
    {
        $canvas = imagecreatetruecolor(self::LARGURA, self::ALTURA);
        imagesavealpha($canvas, true);
        imagealphablending($canvas, true);

        $this->desenharFundo($canvas);
        $this->desenharFoto($canvas, $dados['foto']);
        $this->desenharPilulasSuperiores($canvas, $dados['nome'], $dados['sexo'] ?? 'masculino');
        $this->desenharSeloCentral($canvas);
        $this->desenharLinhaInferior($canvas, $dados['cargo'], $dados['cidade']);

        $pastaDestino = dirname($caminhoDestino);
        if (!is_dir($pastaDestino)) {
            mkdir($pastaDestino, 0755, true);
        }

        imagepng($canvas, $caminhoDestino, 9);
        imagedestroy($canvas);

        return $caminhoDestino;
    }

    // ------------------------------------------------------------------
    // FUNDO: gradiente diagonal azul claro -> pêssego + palavras grandes de marca d'água
    // ------------------------------------------------------------------
    private function desenharFundo($canvas): void
    {
        [$r1, $g1, $b1] = self::AZUL_CLARO_FUNDO;
        [$r2, $g2, $b2] = self::LARANJA_CLARO_FUNDO;
        $diagonalMaxima = self::LARGURA + self::ALTURA;

        for ($y = 0; $y < self::ALTURA; $y++) {
            for ($xBloco = 0; $xBloco < self::LARGURA; $xBloco += 4) {
                $t = ($xBloco + $y) / $diagonalMaxima;
                $r = (int) ($r1 + ($r2 - $r1) * $t);
                $g = (int) ($g1 + ($g2 - $g1) * $t);
                $b = (int) ($b1 + ($b2 - $b1) * $t);
                $cor = imagecolorallocate($canvas, $r, $g, $b);
                imagefilledrectangle($canvas, $xBloco, $y, $xBloco + 4, $y, $cor);
            }
        }

        $fonteBold = $this->pastaFontes . 'Poppins-Bold.ttf';
        if (!file_exists($fonteBold)) {
            return;
        }

        // Palavras enormes cobrindo boa parte do canvas na diagonal, bem sutis,
        // pra lembrar o efeito de "texto de fundo gigante" da referência
        $corPalavra = imagecolorallocatealpha($canvas, 255, 255, 255, 105);
        $angulo = 12;
        $palavras = [
            ['texto' => 'NOVO',      'x' => -140, 'y' => 200, 'tamanho' => 230],
            ['texto' => 'COLABO-',   'x' => 420,  'y' => 40,  'tamanho' => 230],
            ['texto' => 'RADOR',     'x' => -220, 'y' => 560, 'tamanho' => 230],
            ['texto' => 'BEM-VINDO', 'x' => 260,  'y' => 760, 'tamanho' => 150],
            ['texto' => 'TIME',      'x' => -160, 'y' => 1040, 'tamanho' => 230],
        ];
        foreach ($palavras as $palavra) {
            imagettftext($canvas, $palavra['tamanho'], $angulo, $palavra['x'], $palavra['y'], $corPalavra, $fonteBold, $palavra['texto']);
        }
    }

    // ------------------------------------------------------------------
    // FOTO: retângulo bem arredondado, centralizado no topo
    // ------------------------------------------------------------------
    private const FOTO_LARGURA = 620;
    private const FOTO_ALTURA  = 560;
    private const FOTO_X = (self::LARGURA - self::FOTO_LARGURA) / 2;
    private const FOTO_Y = 60;
    private const FOTO_RAIO = 60;

    private function desenharFoto($canvas, string $caminhoFoto): void
    {
        $x = self::FOTO_X;
        $y = self::FOTO_Y;
        $largura = self::FOTO_LARGURA;
        $altura = self::FOTO_ALTURA;

        $origem = $this->carregarImagem($caminhoFoto);
        if ($origem === null) {
            $cinza = imagecolorallocate($canvas, 220, 220, 224);
            $this->retanguloArredondado($canvas, $x, $y, $x + $largura, $y + $altura, self::FOTO_RAIO, $cinza);
            return;
        }

        $largOrig = imagesx($origem);
        $altOrig = imagesy($origem);
        $proporcaoDestino = $largura / $altura;
        $proporcaoOrigem = $largOrig / $altOrig;

        if ($proporcaoOrigem > $proporcaoDestino) {
            $larguraRecorte = (int) ($altOrig * $proporcaoDestino);
            $alturaRecorte = $altOrig;
            $offsetX = (int) (($largOrig - $larguraRecorte) / 2);
            $offsetY = 0;
        } else {
            $larguraRecorte = $largOrig;
            $alturaRecorte = (int) ($largOrig / $proporcaoDestino);
            $offsetX = 0;
            $offsetY = (int) (($altOrig - $alturaRecorte) * 0.12);
        }

        $recortada = imagecreatetruecolor($largura, $altura);
        imagesavealpha($recortada, true);
        imagealphablending($recortada, false);
        $transparenteBase = imagecolorallocatealpha($recortada, 0, 0, 0, 127);
        imagefilledrectangle($recortada, 0, 0, $largura, $altura, $transparenteBase);
        imagealphablending($recortada, true);
        imagecopyresampled($recortada, $origem, 0, 0, $offsetX, $offsetY, $largura, $altura, $larguraRecorte, $alturaRecorte);
        imagedestroy($origem);

        $mascara = imagecreatetruecolor($largura, $altura);
        $preto = imagecolorallocate($mascara, 0, 0, 0);
        $branco = imagecolorallocate($mascara, 255, 255, 255);
        imagefilledrectangle($mascara, 0, 0, $largura, $altura, $preto);
        $this->retanguloArredondado($mascara, 0, 0, $largura, $altura, self::FOTO_RAIO, $branco);

        imagealphablending($recortada, false);
        for ($px = 0; $px < $largura; $px++) {
            for ($py = 0; $py < $altura; $py++) {
                $corMascara = imagecolorat($mascara, $px, $py) & 0xFF;
                if ($corMascara < 128) {
                    $corAtual = imagecolorat($recortada, $px, $py);
                    $r = ($corAtual >> 16) & 0xFF;
                    $g = ($corAtual >> 8) & 0xFF;
                    $b = $corAtual & 0xFF;
                    $transparente = imagecolorallocatealpha($recortada, $r, $g, $b, 127);
                    imagesetpixel($recortada, $px, $py, $transparente);
                }
            }
        }
        imagedestroy($mascara);

        imagealphablending($canvas, true);
        imagesavealpha($canvas, true);
        imagecopy($canvas, $recortada, (int) $x, (int) $y, 0, 0, $largura, $altura);
        imagedestroy($recortada);
    }

    // ------------------------------------------------------------------
    // DUAS PÍLULAS: "Seja Bem-Vindo(a)!" (esquerda) + Nome (direita)
    // ------------------------------------------------------------------
    private const PILULAS_Y1 = 650;
    private const PILULAS_Y2 = 762;

    private function desenharPilulasSuperiores($canvas, string $nome, string $sexo): void
    {
        $branco = imagecolorallocate($canvas, ...self::BRANCO);
        $azul = imagecolorallocate($canvas, ...self::AZUL);
        $fonteBold = $this->pastaFontes . 'Poppins-Bold.ttf';

        $margem = 60;
        $meio = self::LARGURA / 2;
        $gap = 8;

        $x1Esquerda = $margem;
        $x2Esquerda = $meio - $gap;
        $x1Direita = $meio + $gap;
        $x2Direita = self::LARGURA - $margem;

        $this->retanguloArredondado($canvas, $x1Esquerda, self::PILULAS_Y1, $x2Esquerda, self::PILULAS_Y2, 36, $branco);
        $this->retanguloArredondado($canvas, $x1Direita, self::PILULAS_Y1, $x2Direita, self::PILULAS_Y2, 36, $branco);

        // Ícone de seta diagonal (↗) antes do texto "Seja Bem-Vindo(a)!"
        $centroYPilula = self::PILULAS_Y1 + (self::PILULAS_Y2 - self::PILULAS_Y1) / 2;
        $setaX = $x1Esquerda + 44;
        imagesetthickness($canvas, 5);
        imageline($canvas, (int) ($setaX - 10), (int) ($centroYPilula + 10), (int) ($setaX + 10), (int) ($centroYPilula - 10), $azul);
        $pontaSeta = [
            $setaX + 10, $centroYPilula - 10,
            $setaX - 2,  $centroYPilula - 10,
            $setaX + 10, $centroYPilula + 2,
        ];
        imagesetthickness($canvas, 1);
        imagefilledpolygon($canvas, $pontaSeta, $azul);

        $textoSaudacao1 = 'Seja';
        $textoSaudacao2 = ($sexo === 'feminino') ? 'Bem-Vinda!' : 'Bem-Vindo!';
        $xTextoSaudacao = $setaX + 26;
        imagettftext($canvas, 26, 0, (int) $xTextoSaudacao, (int) ($centroYPilula - 6), $azul, $fonteBold, $textoSaudacao1);
        imagettftext($canvas, 26, 0, (int) $xTextoSaudacao, (int) ($centroYPilula + 30), $azul, $fonteBold, $textoSaudacao2);

        // Nome em 2 linhas, centralizado na pílula direita
        $partes = explode(' ', trim($nome), 2);
        $linha1 = $partes[0] ?? '';
        $linha2 = $partes[1] ?? '';
        $centroXDireita = $x1Direita + (($x2Direita - $x1Direita) / 2);
        $larguraMaxima = ($x2Direita - $x1Direita) - 60;

        $tamanho1 = $this->ajustarTamanhoFonte($linha1, $fonteBold, 26, $larguraMaxima);
        $tamanho2 = $linha2 !== '' ? $this->ajustarTamanhoFonte($linha2, $fonteBold, 26, $larguraMaxima) : 26;

        $this->textoCentralizado($canvas, $linha1, $fonteBold, $tamanho1, $centroXDireita, $centroYPilula - 6, $azul);
        if ($linha2 !== '') {
            $this->textoCentralizado($canvas, $linha2, $fonteBold, $tamanho2, $centroXDireita, $centroYPilula + 30, $azul);
        }
    }

    // ------------------------------------------------------------------
    // SELO CIRCULAR: marca Mottanet sobre a costura das duas pílulas
    // ------------------------------------------------------------------
    private function desenharSeloCentral($canvas): void
    {
        $branco = imagecolorallocate($canvas, ...self::BRANCO);

        $centroX = self::LARGURA / 2;
        $centroY = self::PILULAS_Y1 + (self::PILULAS_Y2 - self::PILULAS_Y1) / 2;
        $diametro = 76;

        // Disco branco por baixo, pra separar o ícone das duas pílulas
        imagefilledellipse($canvas, (int) $centroX, (int) $centroY, $diametro, $diametro, $branco);

        $icone = $this->carregarImagem($this->pastaImagens . 'icone.png');
        if ($icone === null) {
            return;
        }

        $tamanhoIcone = $diametro - 10;
        $larguraOriginal = imagesx($icone);
        $alturaOriginal = imagesy($icone);

        $x = (int) ($centroX - $tamanhoIcone / 2);
        $y = (int) ($centroY - $tamanhoIcone / 2);

        imagesavealpha($canvas, true);
        imagealphablending($canvas, true);
        imagecopyresampled($canvas, $icone, $x, $y, 0, 0, $tamanhoIcone, $tamanhoIcone, $larguraOriginal, $alturaOriginal);
        imagedestroy($icone);
    }

    // ------------------------------------------------------------------
    // LINHA INFERIOR: logo Mottanet | Ocupação | Localidade
    // ------------------------------------------------------------------
    private function desenharLinhaInferior($canvas, string $cargo, string $cidade): void
    {
        $margem = 60;
        $y1 = 800;
        $y2 = 940;

        // Coluna 1: cartão com o logo completo
        $logoX1 = $margem;
        $logoX2 = 300;
        $branco = imagecolorallocate($canvas, ...self::BRANCO);
        $this->retanguloArredondado($canvas, $logoX1, $y1, $logoX2, $y2, 30, $branco);
        $this->desenharLogoDentroDoCartao($canvas, $logoX1, $y1, $logoX2, $y2);

        // Colunas 2 e 3: label (pequena, clara) + valor (pílula branca, bold azul)
        $larguraRestante = self::LARGURA - $margem - $logoX2 - 20 - $margem;
        $larguraColuna = ($larguraRestante - 20) / 2;

        $col2X1 = $logoX2 + 20;
        $col2X2 = $col2X1 + $larguraColuna;
        $col3X1 = $col2X2 + 20;
        $col3X2 = $col3X1 + $larguraColuna;

        $this->desenharColunaLabelValor($canvas, $col2X1, $col2X2, $y1, $y2, 'bi-pessoa', 'Ocupação', $cargo);
        $this->desenharColunaLabelValor($canvas, $col3X1, $col3X2, $y1, $y2, 'bi-pin', 'Localidade', $cidade);
    }

    private function desenharLogoDentroDoCartao($canvas, float $x1, float $y1, float $x2, float $y2): void
    {
        $caminhoLogo = $this->pastaImagens . 'logo.png';
        $logo = $this->carregarImagem($caminhoLogo);
        if ($logo === null) {
            return;
        }

        $larguraDisponivel = ($x2 - $x1) - 30;
        $alturaOriginal = imagesy($logo);
        $larguraOriginal = imagesx($logo);
        $alturaLogo = (int) ($larguraDisponivel * ($alturaOriginal / $larguraOriginal));

        if ($alturaLogo > ($y2 - $y1) - 30) {
            $alturaLogo = (int) (($y2 - $y1) - 30);
            $larguraDisponivel = (int) ($alturaLogo * ($larguraOriginal / $alturaOriginal));
        }

        $x = (int) ($x1 + (($x2 - $x1) - $larguraDisponivel) / 2);
        $y = (int) ($y1 + (($y2 - $y1) - $alturaLogo) / 2);

        imagesavealpha($canvas, true);
        imagecopyresampled($canvas, $logo, $x, $y, 0, 0, (int) $larguraDisponivel, $alturaLogo, $larguraOriginal, $alturaOriginal);
        imagedestroy($logo);
    }

    private function desenharColunaLabelValor($canvas, float $x1, float $x2, float $y1, float $y2, string $icone, string $label, string $valor): void
    {
        $branco = imagecolorallocate($canvas, ...self::BRANCO);
        $pillClara = imagecolorallocate($canvas, ...self::PILL_CLARA);
        $azul = imagecolorallocate($canvas, ...self::AZUL);
        $cinza = imagecolorallocate($canvas, ...self::CINZA_TEXTO);
        $fonteBold = $this->pastaFontes . 'Poppins-Bold.ttf';
        $fonteMedium = $this->pastaFontes . 'Poppins-Medium.ttf';

        $alturaTotal = $y2 - $y1;
        $alturaLabel = $alturaTotal * 0.42;
        $alturaValor = $alturaTotal * 0.50;
        $gapMeio = $alturaTotal - $alturaLabel - $alturaValor;

        $labelY1 = $y1;
        $labelY2 = $y1 + $alturaLabel;
        $valorY1 = $labelY2 + $gapMeio;
        $valorY2 = $valorY1 + $alturaValor;

        $this->retanguloArredondado($canvas, $x1, $labelY1, $x2, $labelY2, 22, $pillClara);
        $this->retanguloArredondado($canvas, $x1, $valorY1, $x2, $valorY2, 22, $branco);

        $centroX = $x1 + (($x2 - $x1) / 2);

        $this->textoCentralizado($canvas, $label, $fonteMedium, 17, $centroX, $labelY1 + ($alturaLabel / 2) + 6, $cinza);

        $tamanhoValor = $this->ajustarTamanhoFonte($valor, $fonteBold, 22, ($x2 - $x1) - 30);
        $this->textoCentralizado($canvas, $valor, $fonteBold, $tamanhoValor, $centroX, $valorY1 + ($alturaValor / 2) + 8, $azul);
    }

    // ------------------------------------------------------------------
    // HELPERS
    // ------------------------------------------------------------------

    private function ajustarTamanhoFonte(string $texto, string $fonte, int $tamanhoInicial, float $larguraMaxima): int
    {
        $tamanho = $tamanhoInicial;
        while ($tamanho > 12) {
            $caixa = imagettfbbox($tamanho, 0, $fonte, $texto);
            $largura = abs($caixa[2] - $caixa[0]);
            if ($largura <= $larguraMaxima) {
                break;
            }
            $tamanho -= 2;
        }
        return $tamanho;
    }

    private function textoCentralizado($canvas, string $texto, string $fonte, int $tamanho, float $centroX, float $y, int $cor): void
    {
        $caixa = imagettfbbox($tamanho, 0, $fonte, $texto);
        $largura = abs($caixa[2] - $caixa[0]);
        $x = $centroX - ($largura / 2);
        imagettftext($canvas, $tamanho, 0, (int) $x, (int) $y, $cor, $fonte, $texto);
    }

    private function carregarImagem(string $caminho)
    {
        if (!file_exists($caminho)) {
            return null;
        }
        $tipo = strtolower(pathinfo($caminho, PATHINFO_EXTENSION));
        return match ($tipo) {
            'png' => imagecreatefrompng($caminho),
            'jpg', 'jpeg' => imagecreatefromjpeg($caminho),
            default => null,
        };
    }

    private function retanguloArredondado($canvas, float $x1, float $y1, float $x2, float $y2, int $raio, int $cor): void
    {
        imagefilledrectangle($canvas, (int) ($x1 + $raio), (int) $y1, (int) ($x2 - $raio), (int) $y2, $cor);
        imagefilledrectangle($canvas, (int) $x1, (int) ($y1 + $raio), (int) $x2, (int) ($y2 - $raio), $cor);

        imagefilledellipse($canvas, (int) ($x1 + $raio), (int) ($y1 + $raio), $raio * 2, $raio * 2, $cor);
        imagefilledellipse($canvas, (int) ($x2 - $raio), (int) ($y1 + $raio), $raio * 2, $raio * 2, $cor);
        imagefilledellipse($canvas, (int) ($x1 + $raio), (int) ($y2 - $raio), $raio * 2, $raio * 2, $cor);
        imagefilledellipse($canvas, (int) ($x2 - $raio), (int) ($y2 - $raio), $raio * 2, $raio * 2, $cor);
    }
}