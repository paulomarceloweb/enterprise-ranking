<?php

namespace App\Templates;

/**
 * Gera a arte de Promoção (1080x1080) via GD, seguindo o layout de referência:
 * badge "NOVO DESAFIO" no canto superior esquerdo da foto, logo Mottanet no
 * canto superior direito, foto central arredondada, cartão com nome + chip de
 * departamento/regional, e por fim cargo anterior -> cargo novo com seta laranja.
 *
 * Uso:
 *   $template = new PromocaoTemplate();
 *   $template->gerar([
 *       'nome'           => 'Ketlin Poliana',
 *       'foto'           => '/caminho/para/foto.jpg',
 *       'departamento'   => 'Comercial',
 *       'regional'       => 'Regional 2',
 *       'cargo_anterior' => 'Vendedora',
 *       'cargo_novo'     => 'Supervisora Comercial',
 *   ], '/caminho/destino/promocao_123.png');
 */
class PromocaoTemplate
{
    private const AZUL    = [0, 69, 151];
    private const LARANJA = [255, 147, 8];
    private const BRANCO  = [255, 255, 255];
    private const CINZA_TEXTO = [110, 110, 118];

    private const AZUL_CLARO_FUNDO    = [214, 227, 236];
    private const LARANJA_CLARO_FUNDO = [255, 228, 205];

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
        $this->desenharBadgeNovoDesafio($canvas);
        $this->desenharLogoCanto($canvas);
        $this->desenharCartaoNome($canvas, $dados['nome'], $dados['departamento'], $dados['regional'] ?? '');
        $this->desenharTransicaoCargo($canvas, $dados['cargo_anterior'], $dados['cargo_novo']);

        $pastaDestino = dirname($caminhoDestino);
        if (!is_dir($pastaDestino)) {
            mkdir($pastaDestino, 0755, true);
        }

        imagepng($canvas, $caminhoDestino, 9);
        imagedestroy($canvas);

        return $caminhoDestino;
    }

    // ------------------------------------------------------------------
    // FUNDO: gradiente diagonal claro + marca d'água grande
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

        $corPalavra = imagecolorallocatealpha($canvas, 255, 255, 255, 105);
        $angulo = 12;
        $palavras = [
            ['texto' => 'NOVO',     'x' => -140, 'y' => 200,  'tamanho' => 230],
            ['texto' => 'DESAFIO',  'x' => 260,  'y' => 60,   'tamanho' => 190],
            ['texto' => 'CRESCER',  'x' => -220, 'y' => 620,  'tamanho' => 210],
            ['texto' => 'PROMOÇÃO', 'x' => 300,  'y' => 900,  'tamanho' => 160],
            ['texto' => 'TIME',     'x' => -160, 'y' => 1040, 'tamanho' => 230],
        ];
        foreach ($palavras as $palavra) {
            imagettftext($canvas, $palavra['tamanho'], $angulo, $palavra['x'], $palavra['y'], $corPalavra, $fonteBold, $palavra['texto']);
        }
    }

    // ------------------------------------------------------------------
    // FOTO: retângulo arredondado, centralizado
    // ------------------------------------------------------------------
    private const FOTO_LARGURA = 640;
    private const FOTO_ALTURA  = 520;
    private const FOTO_X = (self::LARGURA - self::FOTO_LARGURA) / 2;
    private const FOTO_Y = 130;
    private const FOTO_RAIO = 50;

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
    // BADGE "NOVO DESAFIO": pílula laranja com estrela, canto superior esquerdo
    // ------------------------------------------------------------------
    private function desenharBadgeNovoDesafio($canvas): void
    {
        $laranja = imagecolorallocate($canvas, ...self::LARANJA);
        $branco = imagecolorallocate($canvas, ...self::BRANCO);
        $fonteBold = $this->pastaFontes . 'Poppins-Bold.ttf';

        $texto = 'NOVO DESAFIO';
        $tamanhoFonte = 22;
        $caixa = imagettfbbox($tamanhoFonte, 0, $fonteBold, $texto);
        $larguraTexto = abs($caixa[2] - $caixa[0]);

        $x1 = self::FOTO_X - 20;
        $y1 = self::FOTO_Y - 30;
        $alturaPill = 60;
        $diametroEstrela = $alturaPill;
        $paddingDireita = 24;
        $larguraPill = $diametroEstrela + 14 + $larguraTexto + $paddingDireita;
        $x2 = $x1 + $larguraPill;
        $y2 = $y1 + $alturaPill;

        $this->retanguloArredondado($canvas, $x1, $y1, $x2, $y2, (int) ($alturaPill / 2), $laranja);

        // Estrela simplificada dentro de um círculo branco
        $centroEstrelaX = $x1 + ($diametroEstrela / 2);
        $centroEstrelaY = $y1 + ($alturaPill / 2);
        imagefilledellipse($canvas, (int) $centroEstrelaX, (int) $centroEstrelaY, $diametroEstrela - 12, $diametroEstrela - 12, $branco);
        $this->desenharEstrela($canvas, $centroEstrelaX, $centroEstrelaY, 15, $laranja);

        $xTexto = $x1 + $diametroEstrela + 14;
        $yTexto = $y1 + ($alturaPill / 2) + 8;
        imagettftext($canvas, $tamanhoFonte, 0, (int) $xTexto, (int) $yTexto, $branco, $fonteBold, $texto);
    }

    private function desenharEstrela($canvas, float $centroX, float $centroY, float $raio, int $cor): void
    {
        $pontos = [];
        for ($i = 0; $i < 10; $i++) {
            $anguloGraus = -90 + ($i * 36);
            $anguloRad = deg2rad($anguloGraus);
            $r = ($i % 2 === 0) ? $raio : $raio * 0.45;
            $pontos[] = $centroX + $r * cos($anguloRad);
            $pontos[] = $centroY + $r * sin($anguloRad);
        }
        imagefilledpolygon($canvas, $pontos, $cor);
    }

    // ------------------------------------------------------------------
    // LOGO: canto superior direito, sobre o fundo
    // ------------------------------------------------------------------
    private function desenharLogoCanto($canvas): void
    {
        $logo = $this->carregarImagem($this->pastaImagens . 'logo.png');
        if ($logo === null) {
            return;
        }

        $larguraLogo = 190;
        $alturaOriginal = imagesy($logo);
        $larguraOriginal = imagesx($logo);
        $alturaLogo = (int) ($larguraLogo * ($alturaOriginal / $larguraOriginal));

        $x = self::LARGURA - 60 - $larguraLogo;
        $y = 46;

        imagesavealpha($canvas, true);
        imagecopyresampled($canvas, $logo, $x, $y, 0, 0, $larguraLogo, $alturaLogo, $larguraOriginal, $alturaOriginal);
        imagedestroy($logo);
    }

    // ------------------------------------------------------------------
    // CARTÃO: nome (esquerda) + chip departamento/regional (direita)
    // ------------------------------------------------------------------
    private function desenharCartaoNome($canvas, string $nome, string $departamento, string $regional): void
    {
        $branco = imagecolorallocate($canvas, ...self::BRANCO);
        $azul = imagecolorallocate($canvas, ...self::AZUL);
        $laranja = imagecolorallocate($canvas, ...self::LARANJA);
        $fonteBold = $this->pastaFontes . 'Poppins-Bold.ttf';
        $fonteMedium = $this->pastaFontes . 'Poppins-Medium.ttf';

        $margem = 60;
        $y1 = self::FOTO_Y + self::FOTO_ALTURA + 20;
        $alturaCartao = 110;
        $y2 = $y1 + $alturaCartao;

        $x2 = self::LARGURA - $margem;
        $larguraChip = 260;
        $x1Chip = $x2 - $larguraChip;
        $x1Nome = $margem;
        $x2Nome = $x1Chip - 20;

        // Cartão do nome
        $this->retanguloArredondado($canvas, $x1Nome, $y1, $x2Nome, $y2, 26, $branco);
        $nomeMaiusculo = mb_strtoupper(trim($nome), 'UTF-8');
        $tamanhoNome = $this->ajustarTamanhoFonte($nomeMaiusculo, $fonteBold, 30, ($x2Nome - $x1Nome) - 60);
        $this->textoCentralizado($canvas, $nomeMaiusculo, $fonteBold, $tamanhoNome, $x1Nome + (($x2Nome - $x1Nome) / 2), $y1 + ($alturaCartao / 2) + 10, $azul);

        // Chip departamento + regional
        $this->retanguloArredondado($canvas, $x1Chip, $y1, $x2, $y2, 26, $branco);

        $iconeX = $x1Chip + 20;
        $iconeY = $y1 + ($alturaCartao / 2) - 14;
        imagefilledrectangle($canvas, (int) $iconeX, (int) ($iconeY + 6), (int) ($iconeX + 26), (int) ($iconeY + 24), $laranja);
        imagesetthickness($canvas, 2);
        imagearc($canvas, (int) ($iconeX + 13), (int) ($iconeY + 4), 12, 10, 180, 360, $laranja);
        imagesetthickness($canvas, 1);

        $xTexto = $iconeX + 36;
        $larguraTexto = $x2 - $xTexto - 16;

        $tamanhoDep = $this->ajustarTamanhoFonte($departamento, $fonteBold, 20, $larguraTexto);
        imagettftext($canvas, $tamanhoDep, 0, (int) $xTexto, (int) ($y1 + $alturaCartao / 2 - 6), $azul, $fonteBold, $departamento);

        if ($regional !== '') {
            $tamanhoReg = $this->ajustarTamanhoFonte($regional, $fonteMedium, 17, $larguraTexto);
            imagettftext($canvas, $tamanhoReg, 0, (int) $xTexto, (int) ($y1 + $alturaCartao / 2 + 22), $azul, $fonteMedium, $regional);
        }
    }

    // ------------------------------------------------------------------
    // TRANSIÇÃO: cargo anterior -> cargo novo
    // ------------------------------------------------------------------
    private function desenharTransicaoCargo($canvas, string $cargoAnterior, string $cargoNovo): void
    {
        $branco = imagecolorallocate($canvas, ...self::BRANCO);
        $azul = imagecolorallocate($canvas, ...self::AZUL);
        $laranja = imagecolorallocate($canvas, ...self::LARANJA);
        $fonteBold = $this->pastaFontes . 'Poppins-Bold.ttf';

        $margem = 60;
        $y1 = self::FOTO_Y + self::FOTO_ALTURA + 20 + 110 + 20;
        $alturaLinha = 120;
        $y2 = $y1 + $alturaLinha;

        $centroX = self::LARGURA / 2;
        $larguraSeta = 100;

        $x1Anterior = $margem;
        $x2Anterior = $centroX - ($larguraSeta / 2) - 10;
        $x1Novo = $centroX + ($larguraSeta / 2) + 10;
        $x2Novo = self::LARGURA - $margem;

        $this->retanguloArredondado($canvas, $x1Anterior, $y1, $x2Anterior, $y2, 26, $branco);
        $this->retanguloArredondado($canvas, $x1Novo, $y1, $x2Novo, $y2, 26, $branco);

        $tamanhoAnterior = $this->ajustarTamanhoFonte($cargoAnterior, $fonteBold, 28, ($x2Anterior - $x1Anterior) - 50);
        $this->textoCentralizado($canvas, $cargoAnterior, $fonteBold, $tamanhoAnterior, $x1Anterior + (($x2Anterior - $x1Anterior) / 2), $y1 + ($alturaLinha / 2) + 10, $azul);

        // Cargo novo em 2 linhas se necessário (é o destaque, geralmente o texto mais longo)
        $centroXNovo = $x1Novo + (($x2Novo - $x1Novo) / 2);
        $larguraMaximaNovo = ($x2Novo - $x1Novo) - 50;
        $caixaUmaLinha = imagettfbbox(28, 0, $fonteBold, $cargoNovo);
        $larguraUmaLinha = abs($caixaUmaLinha[2] - $caixaUmaLinha[0]);

        if ($larguraUmaLinha <= $larguraMaximaNovo) {
            $this->textoCentralizado($canvas, $cargoNovo, $fonteBold, 28, $centroXNovo, $y1 + ($alturaLinha / 2) + 10, $azul);
        } else {
            $partes = explode(' ', trim($cargoNovo), 2);
            $linha1 = $partes[0] ?? '';
            $linha2 = $partes[1] ?? '';
            $tamanho1 = $this->ajustarTamanhoFonte($linha1, $fonteBold, 24, $larguraMaximaNovo);
            $tamanho2 = $linha2 !== '' ? $this->ajustarTamanhoFonte($linha2, $fonteBold, 24, $larguraMaximaNovo) : 24;
            $this->textoCentralizado($canvas, $linha1, $fonteBold, $tamanho1, $centroXNovo, $y1 + ($alturaLinha / 2) - 6, $azul);
            if ($linha2 !== '') {
                $this->textoCentralizado($canvas, $linha2, $fonteBold, $tamanho2, $centroXNovo, $y1 + ($alturaLinha / 2) + 26, $azul);
            }
        }

        // Seta laranja no meio
        $centroYSeta = $y1 + ($alturaLinha / 2);
        imagesetthickness($canvas, 8);
        imageline($canvas, (int) ($centroX - $larguraSeta / 2), (int) $centroYSeta, (int) ($centroX + $larguraSeta / 2 - 16), (int) $centroYSeta, $laranja);
        imagesetthickness($canvas, 1);
        $pontaSeta = [
            $centroX + $larguraSeta / 2 - 16, $centroYSeta - 20,
            $centroX + $larguraSeta / 2 + 14, $centroYSeta,
            $centroX + $larguraSeta / 2 - 16, $centroYSeta + 20,
        ];
        imagefilledpolygon($canvas, $pontaSeta, $laranja);
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