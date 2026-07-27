<?php

namespace App\Templates;

/**
 * Gera a arte de Aniversariantes do mês (1080x1080) via GD: painel translúcido
 * com um grid de até 6 pessoas (foto circular, nome, cargo, cidade, data),
 * balões decorativos nas laterais e mensagem de parabéns no rodapé.
 *
 * Uso:
 *   $template = new AniversariantesTemplate();
 *   $template->gerar([
 *       'mes' => 7,
 *       'ano' => 2026,
 *       'pessoas' => [
 *           ['nome' => 'Iverson Aleixo', 'foto' => '/caminho/foto.jpg', 'cargo' => 'Técnico em Fibra Óptica', 'cidade' => 'Telêmaco Borba/PR', 'dia_mes' => '21/07'],
 *           // ... até 6 pessoas
 *       ],
 *   ], '/caminho/destino/aniversariantes_julho_pagina1.png');
 */
class AniversariantesTemplate
{
    private const AZUL    = [0, 69, 151];
    private const LARANJA = [255, 147, 8];
    private const BRANCO  = [255, 255, 255];
    private const CINZA_TEXTO = [100, 100, 110];
    private const CINZA_PILL  = [232, 234, 238];

    private const AZUL_CLARO_FUNDO    = [178, 205, 226];
    private const LARANJA_CLARO_FUNDO = [255, 212, 168];

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
        $this->desenharBaloes($canvas);
        $this->desenharPainel($canvas);
        $this->desenharCabecalho($canvas, (int) $dados['mes'], (int) $dados['ano']);
        $this->desenharGrid($canvas, array_slice($dados['pessoas'], 0, 6));
        $this->desenharMensagem($canvas);
        $this->desenharLogoRodape($canvas);

        $pastaDestino = dirname($caminhoDestino);
        if (!is_dir($pastaDestino)) {
            mkdir($pastaDestino, 0755, true);
        }

        imagepng($canvas, $caminhoDestino, 9);
        imagedestroy($canvas);

        return $caminhoDestino;
    }

    // ------------------------------------------------------------------
    // FUNDO: gradiente diagonal + marca d'água grande
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

        $corPalavra = imagecolorallocatealpha($canvas, 255, 255, 255, 110);
        imagettftext($canvas, 230, 8, -60, 190, $corPalavra, $fonteBold, 'VIVER');
        imagettftext($canvas, 180, 8, 520, 90, $corPalavra, $fonteBold, 'FESTA');
    }

    // ------------------------------------------------------------------
    // BALÕES decorativos nas laterais
    // ------------------------------------------------------------------
    private function desenharBaloes($canvas): void
    {
        $this->desenharBalao($canvas, 90, 560, 90, 115);
        $this->desenharBalao($canvas, 990, 300, 80, 105);
    }

    private function desenharBalao($canvas, float $centroX, float $centroY, float $largura, float $altura): void
    {
        $azulBalao = imagecolorallocate($canvas, 60, 110, 200);
        $azulClaro = imagecolorallocatealpha($canvas, 255, 255, 255, 70);

        imagefilledellipse($canvas, (int) $centroX, (int) $centroY, (int) $largura, (int) $altura, $azulBalao);
        // brilho
        imagefilledellipse($canvas, (int) ($centroX - $largura * 0.22), (int) ($centroY - $altura * 0.22), (int) ($largura * 0.3), (int) ($altura * 0.22), $azulClaro);

        // nó
        $noY = $centroY + ($altura / 2);
        $pontosNo = [
            $centroX - 6, $noY,
            $centroX + 6, $noY,
            $centroX,     $noY + 10,
        ];
        imagefilledpolygon($canvas, $pontosNo, $azulBalao);

        // barbante ondulado
        imagesetthickness($canvas, 2);
        $corBarbante = imagecolorallocate($canvas, 120, 150, 210);
        $y1 = $noY + 10;
        $pontosAnteriores = null;
        for ($passo = 0; $passo <= 100; $passo += 10) {
            $x = $centroX + sin($passo / 12) * 12;
            $y = $y1 + $passo;
            if ($pontosAnteriores !== null) {
                imageline($canvas, (int) $pontosAnteriores[0], (int) $pontosAnteriores[1], (int) $x, (int) $y, $corBarbante);
            }
            $pontosAnteriores = [$x, $y];
        }
        imagesetthickness($canvas, 1);
    }

    // ------------------------------------------------------------------
    // PAINEL translúcido de fundo pro conteúdo
    // ------------------------------------------------------------------
    private function desenharPainel($canvas): void
    {
        $branco = imagecolorallocatealpha($canvas, 255, 255, 255, 55);
        $this->retanguloArredondado($canvas, 50, 50, self::LARGURA - 50, self::ALTURA - 50, 60, $branco);
    }

    // ------------------------------------------------------------------
    // CABEÇALHO: título + pill de mês/ano
    // ------------------------------------------------------------------
    private function desenharCabecalho($canvas, int $mes, int $ano): void
    {
        $azul = imagecolorallocate($canvas, ...self::AZUL);
        $fonteBold = $this->pastaFontes . 'Poppins-Bold.ttf';
        $fonteMedium = $this->pastaFontes . 'Poppins-Medium.ttf';

        $this->textoCentralizado($canvas, 'Aniversariantes', $fonteBold, 56, self::LARGURA / 2, 155, $azul);

        $meses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];
        $textoMes = ($meses[$mes] ?? '') . ' ' . $ano;

        $tamanhoFonte = 24;
        $caixa = imagettfbbox($tamanhoFonte, 0, $fonteMedium, $textoMes);
        $larguraTexto = abs($caixa[2] - $caixa[0]);
        $paddingH = 30;
        $larguraPill = $larguraTexto + ($paddingH * 2);
        $centroX = self::LARGURA / 2;
        $x1 = $centroX - ($larguraPill / 2);
        $x2 = $centroX + ($larguraPill / 2);
        $y1 = 190;
        $y2 = 240;

        $cinza = imagecolorallocate($canvas, ...self::CINZA_PILL);
        $this->retanguloArredondado($canvas, $x1, $y1, $x2, $y2, 24, $cinza);
        $this->textoCentralizado($canvas, $textoMes, $fonteMedium, $tamanhoFonte, $centroX, $y1 + 34, $azul);
    }

    // ------------------------------------------------------------------
    // GRID adaptativo: 1 a 6 pessoas, sempre preenchendo bem a área disponível
    // (em vez de deixar cards pequenos "perdidos" quando tem pouca gente).
    // ------------------------------------------------------------------

    /**
     * Define quantas pessoas por linha, pra cada quantidade total (1 a 6).
     * O número de colunas "de referência" (pra calcular o tamanho do card)
     * é sempre o maior valor entre as linhas, e linhas mais curtas ficam centralizadas.
     */
    private function layoutPorQuantidade(int $quantidade): array
    {
        return match ($quantidade) {
            1 => [1],
            2 => [2],
            3 => [3],
            4 => [2, 2],
            5 => [3, 2],
            default => [3, 3], // 6
        };
    }

    private function desenharGrid($canvas, array $pessoas): void
    {
        $quantidade = count($pessoas);
        if ($quantidade === 0) {
            return;
        }

        $linhasConfig = $this->layoutPorQuantidade($quantidade);
        $numeroLinhas = count($linhasConfig);
        $maxColunas = max($linhasConfig);

        $gapX = 20;
        $gapY = 20;

        // Área disponível pro grid (fixa, independente da quantidade de pessoas)
        $areaX1 = 100;
        $areaX2 = self::LARGURA - 100;
        $areaY1 = 280;
        $areaY2 = 860;
        $larguraDisponivel = $areaX2 - $areaX1;
        $alturaDisponivel = $areaY2 - $areaY1;

        $larguraCelula = ($larguraDisponivel - (($maxColunas - 1) * $gapX)) / $maxColunas;
        $alturaCelula = ($alturaDisponivel - (($numeroLinhas - 1) * $gapY)) / $numeroLinhas;

        // Cards muito grandes (poucas pessoas) ficariam com texto minúsculo se
        // usássemos sempre o mesmo tamanho de fonte fixo — escala proporcional
        // ao tamanho do card em relação ao card "padrão" (grade cheia de 6).
        $escalaFonte = min(1.6, max(0.9, $alturaCelula / 260));

        $pessoasRestantes = $pessoas;
        foreach ($linhasConfig as $linha => $quantidadeNaLinha) {
            $pessoasDaLinha = array_splice($pessoasRestantes, 0, $quantidadeNaLinha);
            $larguraLinha = ($quantidadeNaLinha * $larguraCelula) + (($quantidadeNaLinha - 1) * $gapX);
            $xInicialLinha = $areaX1 + (($larguraDisponivel - $larguraLinha) / 2);
            $y = $areaY1 + ($linha * ($alturaCelula + $gapY));

            foreach ($pessoasDaLinha as $coluna => $pessoa) {
                $x = $xInicialLinha + ($coluna * ($larguraCelula + $gapX));
                $this->desenharCard($canvas, $x, $y, $larguraCelula, $alturaCelula, $pessoa, $escalaFonte);
            }
        }
    }

    private function desenharCard($canvas, float $x, float $y, float $largura, float $altura, array $pessoa, float $escalaFonte = 1.0): void
    {
        $branco = imagecolorallocate($canvas, ...self::BRANCO);
        $laranja = imagecolorallocate($canvas, ...self::LARANJA);
        $azul = imagecolorallocate($canvas, ...self::AZUL);
        $cinzaTexto = imagecolorallocate($canvas, ...self::CINZA_TEXTO);
        $cinzaPill = imagecolorallocate($canvas, ...self::CINZA_PILL);
        $fonteBold = $this->pastaFontes . 'Poppins-Bold.ttf';
        $fonteMedium = $this->pastaFontes . 'Poppins-Medium.ttf';

        $this->retanguloArredondado($canvas, $x, $y, $x + $largura, $y + $altura, 24, $branco);

        $centroX = $x + ($largura / 2);
        $diametroFoto = (int) round(100 * $escalaFonte);
        // não deixa a foto maior que a largura do card com folga
        $diametroFoto = (int) min($diametroFoto, $largura - 40);
        $centroFotoY = $y + 16 + ($diametroFoto / 2);

        $this->desenharFotoCircular($canvas, $pessoa['foto'] ?? '', $centroX, $centroFotoY, $diametroFoto, $laranja);

        $larguraTexto = $largura - 24;

        $nome = mb_strtoupper($pessoa['nome'] ?? '', 'UTF-8');
        $tamanhoNome = $this->ajustarTamanhoFonte($nome, $fonteBold, (int) round(17 * $escalaFonte), $larguraTexto);
        $yNome = $centroFotoY + ($diametroFoto / 2) + round(26 * $escalaFonte);
        $this->textoCentralizado($canvas, $nome, $fonteBold, $tamanhoNome, $centroX, $yNome, $laranja);

        $cargo = $pessoa['cargo'] ?? '';
        $tamanhoCargo = $this->ajustarTamanhoFonte($cargo, $fonteMedium, (int) round(13 * $escalaFonte), $larguraTexto);
        $yCargo = $yNome + round(20 * $escalaFonte);
        $this->textoCentralizado($canvas, $cargo, $fonteMedium, $tamanhoCargo, $centroX, $yCargo, $azul);

        $cidade = $pessoa['cidade'] ?? '';
        $tamanhoCidade = $this->ajustarTamanhoFonte($cidade, $fonteMedium, (int) round(13 * $escalaFonte), $larguraTexto);
        $yCidade = $yCargo + round(18 * $escalaFonte);
        $this->textoCentralizado($canvas, $cidade, $fonteMedium, $tamanhoCidade, $centroX, $yCidade, $azul);

        // pill com a data
        $dataTexto = $pessoa['dia_mes'] ?? '';
        if ($dataTexto !== '') {
            $tamanhoData = (int) round(14 * $escalaFonte);
            $caixa = imagettfbbox($tamanhoData, 0, $fonteBold, $dataTexto);
            $larguraData = abs($caixa[2] - $caixa[0]);
            $larguraPillData = $larguraData + 30;
            $yPill1 = $yCidade + round(12 * $escalaFonte);
            $yPill2 = $yPill1 + round(28 * $escalaFonte);
            $xPill1 = $centroX - ($larguraPillData / 2);
            $xPill2 = $centroX + ($larguraPillData / 2);
            $this->retanguloArredondado($canvas, $xPill1, $yPill1, $xPill2, $yPill2, 14, $cinzaPill);
            $this->textoCentralizado($canvas, $dataTexto, $fonteBold, $tamanhoData, $centroX, $yPill1 + round(19 * $escalaFonte), $cinzaTexto);
        }
    }

    private function desenharFotoCircular($canvas, string $caminhoFoto, float $centroX, float $centroY, int $diametro, int $corAnel): void
    {
        $raio = $diametro / 2;

        $origem = $this->carregarImagem($caminhoFoto);
        if ($origem === null) {
            $cinza = imagecolorallocate($canvas, 220, 220, 224);
            imagefilledellipse($canvas, (int) $centroX, (int) $centroY, $diametro, $diametro, $cinza);
        } else {
            $largOrig = imagesx($origem);
            $altOrig = imagesy($origem);
            $lado = min($largOrig, $altOrig);
            $offsetX = (int) (($largOrig - $lado) / 2);
            $offsetY = (int) (($altOrig - $lado) * 0.15);

            $quadrado = imagecreatetruecolor($diametro, $diametro);
            imagecopyresampled($quadrado, $origem, 0, 0, $offsetX, $offsetY, $diametro, $diametro, $lado, $lado);
            imagedestroy($origem);

            // Máscara circular
            for ($px = 0; $px < $diametro; $px++) {
                for ($py = 0; $py < $diametro; $py++) {
                    $dx = $px - $raio;
                    $dy = $py - $raio;
                    if (($dx * $dx + $dy * $dy) > ($raio * $raio)) {
                        continue;
                    }
                    $cor = imagecolorat($quadrado, $px, $py);
                    imagesetpixel($canvas, (int) ($centroX - $raio + $px), (int) ($centroY - $raio + $py), $cor);
                }
            }
            imagedestroy($quadrado);
        }

        imagesetthickness($canvas, 4);
        imageellipse($canvas, (int) $centroX, (int) $centroY, $diametro + 4, $diametro + 4, $corAnel);
        imagesetthickness($canvas, 1);
    }

    // ------------------------------------------------------------------
    // MENSAGEM de parabéns
    // ------------------------------------------------------------------
    private function desenharMensagem($canvas): void
    {
        $azul = imagecolorallocate($canvas, ...self::AZUL);
        $fonteMedium = $this->pastaFontes . 'Poppins-Medium.ttf';

        $linhas = [
            'A Mottanet parabeniza seus colaboradores aniversariantes do mês.',
            'Que este novo ciclo venha com boas conquistas, prosperidade,',
            'crescimento profissional e muitas vitórias.',
        ];

        $y = 900;
        foreach ($linhas as $linha) {
            $this->textoCentralizado($canvas, $linha, $fonteMedium, 19, self::LARGURA / 2, $y, $azul);
            $y += 28;
        }
    }

    // ------------------------------------------------------------------
    // LOGO no rodapé
    // ------------------------------------------------------------------
    private function desenharLogoRodape($canvas): void
    {
        $logo = $this->carregarImagem($this->pastaImagens . 'logo.png');
        if ($logo === null) {
            return;
        }

        $larguraLogo = 140;
        $alturaOriginal = imagesy($logo);
        $larguraOriginal = imagesx($logo);
        $alturaLogo = (int) ($larguraLogo * ($alturaOriginal / $larguraOriginal));

        $x = self::LARGURA - 70 - $larguraLogo;
        $y = self::ALTURA - 70 - $alturaLogo;

        imagesavealpha($canvas, true);
        imagecopyresampled($canvas, $logo, $x, $y, 0, 0, $larguraLogo, $alturaLogo, $larguraOriginal, $alturaOriginal);
        imagedestroy($logo);
    }

    // ------------------------------------------------------------------
    // HELPERS
    // ------------------------------------------------------------------

    private function ajustarTamanhoFonte(string $texto, string $fonte, int $tamanhoInicial, float $larguraMaxima): int
    {
        $tamanho = $tamanhoInicial;
        while ($tamanho > 9) {
            $caixa = imagettfbbox($tamanho, 0, $fonte, $texto);
            $largura = abs($caixa[2] - $caixa[0]);
            if ($largura <= $larguraMaxima) {
                break;
            }
            $tamanho -= 1;
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
        if ($caminho === '' || !file_exists($caminho)) {
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