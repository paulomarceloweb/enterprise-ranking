<?php

namespace App\Templates;

/**
 * Gera a arte de Ranking (1080x1080) via GD, seguindo o layout de referência:
 * fundo claro com marca d'água "RANKING" repetida, título + pill de mês/ano lado a lado,
 * foto retangular no canto superior esquerdo com badge de coroa circular sobreposto,
 * barra branca inferior com nome (2 linhas) + chip de setor à direita, logo Mottanet no rodapé.
 *
 * Uso:
 *   $template = new RankingTemplate();
 *   $caminho = $template->gerar([
 *       'nome'      => 'Maria da Silva',
 *       'foto'      => __DIR__ . '/../../public/assets/uploads/colaboradores/xxx.jpg',
 *       'setor'     => 'Vendas',
 *       'cidade'    => 'Sorocaba/SP',
 *       'colocacao' => 1,           // 1, 2, 3, 4, 5...
 *       'mes'       => 7,
 *       'ano'       => 2026,
 *   ], '/caminho/destino/ranking_123.png');
 */
class RankingTemplate
{
    // Cores da marca Mottanet
    private const AZUL      = [0, 69, 151];    // #004597
    private const LARANJA   = [255, 147, 8];   // #FF9308
    private const DOURADO   = [255, 193, 0];   // #FFC100
    private const PRATA     = [176, 176, 184];
    private const BRONZE    = [176, 116, 56];
    private const BRANCO    = [255, 255, 255];
    private const CINZA_TEXTO = [90, 90, 90];
    private const CINZA_FUNDO = [242, 243, 245];
    private const CINZA_MARCA_DAGUA = [225, 228, 233];
    private const CINZA_FOTO_FUNDO = [70, 70, 74];

    private const LARGURA = 1080;
    private const ALTURA  = 1080;

    private string $pastaFontes;
    private string $pastaImagens;

    public function __construct()
    {
        // Ajuste estes caminhos se a estrutura de pastas final for diferente
        $this->pastaFontes  = __DIR__ . '/../../resources/fonts/';
        $this->pastaImagens = __DIR__ . '/../../resources/images/';
    }

    /**
     * Gera a imagem e salva no caminho de destino.
     * Retorna o caminho salvo ou lança Exception em caso de erro grave.
     */
    public function gerar(array $dados, string $caminhoDestino): string
    {
        $canvas = imagecreatetruecolor(self::LARGURA, self::ALTURA);
        imagesavealpha($canvas, true);
        imagealphablending($canvas, true);

        $this->desenharFundo($canvas);
        $this->desenharCabecalho($canvas, (int) $dados['mes'], (int) $dados['ano']);
        $this->desenharFoto($canvas, $dados['foto']);
        $this->desenharCoroa($canvas, (int) $dados['colocacao']);
        $this->desenharBarraInferior($canvas, $dados['nome'], $dados['setor']);
        $this->desenharLogo($canvas);

        $pastaDestino = dirname($caminhoDestino);
        if (!is_dir($pastaDestino)) {
            mkdir($pastaDestino, 0755, true);
        }

        imagepng($canvas, $caminhoDestino, 9);
        imagedestroy($canvas);

        return $caminhoDestino;
    }

    // ------------------------------------------------------------------
    // FUNDO: brilho radial quente (laranja) perto do badge da coroa,
    // esmaecendo pra azul-petróleo bem claro e depois branco nas bordas
    // ------------------------------------------------------------------
    private const GLOW_LARANJA        = [255, 224, 190];
    private const AZUL_PETROLEO_CLARO = [214, 227, 229];

    private function desenharFundo($canvas): void
    {
        [$r1, $g1, $b1] = self::GLOW_LARANJA;
        [$r2, $g2, $b2] = self::AZUL_PETROLEO_CLARO;
        [$r3, $g3, $b3] = self::BRANCO;

        // Centro do brilho: perto do badge da coroa (canto superior esquerdo da foto)
        $centroX = self::FOTO_X + 60;
        $centroY = self::FOTO_Y + 40;
        $raioMaximo = 780.0;

        for ($y = 0; $y < self::ALTURA; $y++) {
            for ($xBloco = 0; $xBloco < self::LARGURA; $xBloco += 4) {
                $dx = $xBloco - $centroX;
                $dy = $y - $centroY;
                $distancia = sqrt($dx * $dx + $dy * $dy);
                $t = min(1.0, $distancia / $raioMaximo);

                if ($t < 0.45) {
                    $t2 = $t / 0.45;
                    $r = (int) ($r1 + ($r2 - $r1) * $t2);
                    $g = (int) ($g1 + ($g2 - $g1) * $t2);
                    $b = (int) ($b1 + ($b2 - $b1) * $t2);
                } else {
                    $t2 = ($t - 0.45) / 0.55;
                    $r = (int) ($r2 + ($r3 - $r2) * $t2);
                    $g = (int) ($g2 + ($g3 - $g2) * $t2);
                    $b = (int) ($b2 + ($b3 - $b2) * $t2);
                }

                $cor = imagecolorallocate($canvas, $r, $g, $b);
                imagefilledrectangle($canvas, $xBloco, $y, $xBloco + 4, $y, $cor);
            }
        }

        $fonteBold = $this->pastaFontes . 'Poppins-Bold.ttf';
        if (!file_exists($fonteBold)) {
            return;
        }

        // Marca d'água bem sutil (alpha alto = mais transparente), grade regular e bem espaçada
        $corMarcaDagua = imagecolorallocatealpha($canvas, 205, 209, 216, 100);
        $tamanho = 46;
        $angulo = -22;

        $passoX = 380;
        $passoY = 300;

        // Cada linha desloca meio passo em X pra dar o efeito "tijolo" da referência, sem sobrepor letras
        $linhaIndice = 0;
        for ($y = -100; $y <= self::ALTURA + 200; $y += $passoY) {
            $deslocamento = ($linhaIndice % 2 === 0) ? 0 : (int) ($passoX / 2);
            for ($x = -300 + $deslocamento; $x <= self::LARGURA + 300; $x += $passoX) {
                imagettftext($canvas, $tamanho, $angulo, $x, $y, $corMarcaDagua, $fonteBold, 'RANKING');
            }
            $linhaIndice++;
        }
    }

    // ------------------------------------------------------------------
    // CABEÇALHO: título "RANKING" (esquerda) + pill com mês/ano (direita, mesma linha)
    // ------------------------------------------------------------------
    private function desenharCabecalho($canvas, int $mes, int $ano): void
    {
        $azul = imagecolorallocate($canvas, ...self::AZUL);
        $fonteBold = $this->pastaFontes . 'Poppins-Bold.ttf';
        $fonteMedium = $this->pastaFontes . 'Poppins-Medium.ttf';

        $margem = 60;
        $yBase = 100;

        // Título "RANKING" alinhado à esquerda
        imagettftext($canvas, 46, 0, $margem, $yBase, $azul, $fonteBold, 'RANKING');

        // Pill com mês/ano, alinhada à direita, mesma altura do título
        $meses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];
        $textoMes = mb_strtoupper(($meses[$mes] ?? '') . ' ' . $ano, 'UTF-8');

        $fonteTamanho = 22;
        $caixa = imagettfbbox($fonteTamanho, 0, $fonteMedium, $textoMes);
        $larguraTexto = abs($caixa[2] - $caixa[0]);

        $paddingH = 30;
        $larguraPill = $larguraTexto + ($paddingH * 2);
        $alturaPill = 56;
        $xPill2 = self::LARGURA - $margem;
        $xPill1 = $xPill2 - $larguraPill;
        $yPill1 = $yBase - $alturaPill + 12;
        $yPill2 = $yBase + 12;

        $cinzaPill = imagecolorallocate($canvas, 226, 229, 234);
        $this->retanguloArredondado($canvas, $xPill1, $yPill1, $xPill2, $yPill2, 28, $cinzaPill);

        $xTexto = $xPill1 + $paddingH;
        $yTexto = $yPill1 + ($alturaPill / 2) + 8;
        imagettftext($canvas, $fonteTamanho, 0, (int) $xTexto, (int) $yTexto, $azul, $fonteMedium, $textoMes);
    }

    // ------------------------------------------------------------------
    // FOTO: retângulo arredondado, centralizado horizontalmente no canvas
    // ------------------------------------------------------------------
    private const FOTO_LARGURA = 600;
    private const FOTO_ALTURA = 480;
    private const FOTO_X = (self::LARGURA - self::FOTO_LARGURA) / 2;
    private const FOTO_Y = 190;
    private const FOTO_RAIO = 36;

    private function desenharFoto($canvas, string $caminhoFoto): void
    {
        $x = self::FOTO_X;
        $y = self::FOTO_Y;
        $largura = self::FOTO_LARGURA;
        $altura = self::FOTO_ALTURA;

        $origem = $this->carregarImagem($caminhoFoto);
        if ($origem === null) {
            $cinzaFundo = imagecolorallocate($canvas, ...self::CINZA_FOTO_FUNDO);
            $this->retanguloArredondado($canvas, $x, $y, $x + $largura, $y + $altura, self::FOTO_RAIO, $cinzaFundo);
            return;
        }

        // Recorte central mantendo a proporção do retângulo de destino
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
            // Viés pra cima: em vez de cortar 50/50 (que corta a cabeça em fotos tipo retrato),
            // deixa mais espaço no topo intacto e corta mais de baixo (ombros/peito).
            $offsetY = (int) (($altOrig - $alturaRecorte) * 0.12);
        }

        // A foto recortada PRECISA ter savealpha ligado ANTES de qualquer pixel com alpha
        // ser desenhado nela, senão o canal de transparência não é preservado no imagecopy
        // final — foi isso que deixava os cantos "sujos"/quadrados em vez de arredondados.
        $recortada = imagecreatetruecolor($largura, $altura);
        imagesavealpha($recortada, true);
        imagealphablending($recortada, false);
        $transparenteBase = imagecolorallocatealpha($recortada, 0, 0, 0, 127);
        imagefilledrectangle($recortada, 0, 0, $largura, $altura, $transparenteBase);
        imagealphablending($recortada, true);
        imagecopyresampled($recortada, $origem, 0, 0, $offsetX, $offsetY, $largura, $altura, $larguraRecorte, $alturaRecorte);
        imagedestroy($origem);

        // Máscara com cantos arredondados (branco = mantém a foto, preto = vira transparente)
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
                    // fora do arredondamento: torna o pixel totalmente transparente
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
    // COROA: badge circular sobreposto ao canto superior esquerdo da foto
    // ------------------------------------------------------------------
    private function desenharCoroa($canvas, int $colocacao): void
    {
        $cor = $this->corPorColocacao($colocacao);
        $corBadge = imagecolorallocate($canvas, ...$cor);
        $branco = imagecolorallocate($canvas, ...self::BRANCO);

        $diametro = 100;
        $centroX = self::FOTO_X + 10;
        $centroY = self::FOTO_Y + 10;

        imagefilledellipse($canvas, (int) $centroX, (int) $centroY, $diametro, $diametro, $corBadge);
        // anel branco fino ao redor do badge para destacar da foto
        imagesetthickness($canvas, 4);
        imageellipse($canvas, (int) $centroX, (int) $centroY, $diametro, $diametro, $branco);
        imagesetthickness($canvas, 1);

        if ($colocacao <= 3) {
            // Coroa simplificada (3 pontas) dentro do círculo
            $largCoroa = 46;
            $altCoroa = 26;
            $topo = $centroY - 8;
            $pontos = [
                $centroX - $largCoroa / 2, $topo + $altCoroa,
                $centroX - $largCoroa / 2, $topo + 6,
                $centroX - $largCoroa / 4, $topo + 16,
                $centroX,                  $topo,
                $centroX + $largCoroa / 4, $topo + 16,
                $centroX + $largCoroa / 2, $topo + 6,
                $centroX + $largCoroa / 2, $topo + $altCoroa,
            ];
            imagefilledpolygon($canvas, $pontos, $branco);
            imagefilledrectangle(
                $canvas,
                (int) ($centroX - $largCoroa / 2),
                (int) ($topo + $altCoroa - 4),
                (int) ($centroX + $largCoroa / 2),
                (int) ($topo + $altCoroa + 4),
                $branco
            );
        } else {
            // Colocações 4+: número dentro do círculo em vez da coroa
            $fonteBold = $this->pastaFontes . 'Poppins-Bold.ttf';
            $texto = $colocacao . 'º';
            $tamanho = 24;
            $caixa = imagettfbbox($tamanho, 0, $fonteBold, $texto);
            $largura = abs($caixa[2] - $caixa[0]);
            imagettftext($canvas, $tamanho, 0, (int) ($centroX - $largura / 2), (int) ($centroY + 9), $branco, $fonteBold, $texto);
        }
    }

    private function corPorColocacao(int $colocacao): array
    {
        return match ($colocacao) {
            1 => self::DOURADO,
            2 => self::PRATA,
            3 => self::BRONZE,
            default => self::AZUL,
        };
    }

    // ------------------------------------------------------------------
    // BARRA INFERIOR BRANCA: nome (2 linhas, esquerda) + chip de setor (direita)
    // ------------------------------------------------------------------
    private function desenharBarraInferior($canvas, string $nome, string $setor): void
    {
        $branco = imagecolorallocate($canvas, ...self::BRANCO);
        $azul = imagecolorallocate($canvas, ...self::AZUL);
        $cinzaChip = imagecolorallocate($canvas, 236, 238, 241);

        $margem = 60;
        $yBarra = 720;
        $alturaBarra = 210;
        $x1 = $margem;
        $x2 = self::LARGURA - $margem;

        $this->retanguloArredondado($canvas, $x1, $yBarra, $x2, $yBarra + $alturaBarra, 32, $branco);

        $fonteBold = $this->pastaFontes . 'Poppins-Bold.ttf';
        $fonteMedium = $this->pastaFontes . 'Poppins-Medium.ttf';

        // Divide o nome em até 2 linhas: 1ª palavra na linha 1, restante na linha 2
        $partes = explode(' ', trim($nome), 2);
        $linha1 = mb_strtoupper($partes[0] ?? '', 'UTF-8');
        $linha2 = mb_strtoupper($partes[1] ?? '', 'UTF-8');

        $paddingEsquerda = 40;
        $larguraDisponivelNome = ($x2 - $x1) * 0.55; // reserva ~45% pro chip de setor

        $tamanhoFonteNome = 40;
        $tamanhoLinha1 = $this->ajustarTamanhoFonte($linha1, $fonteBold, $tamanhoFonteNome, $larguraDisponivelNome - $paddingEsquerda);
        $tamanhoLinha2 = $linha2 !== '' ? $this->ajustarTamanhoFonte($linha2, $fonteBold, $tamanhoFonteNome, $larguraDisponivelNome - $paddingEsquerda) : $tamanhoFonteNome;

        $xNome = $x1 + $paddingEsquerda;
        imagettftext($canvas, $tamanhoLinha1, 0, (int) $xNome, (int) ($yBarra + 90), $azul, $fonteBold, $linha1);
        if ($linha2 !== '') {
            imagettftext($canvas, $tamanhoLinha2, 0, (int) $xNome, (int) ($yBarra + 140), $azul, $fonteBold, $linha2);
        }

        // Chip do setor, à direita dentro da barra
        $larguraChip = ($x2 - $x1) * 0.38;
        $alturaChip = 130;
        $xChip1 = $x2 - 30 - $larguraChip;
        $xChip2 = $x2 - 30;
        $yChip1 = $yBarra + (($alturaBarra - $alturaChip) / 2);
        $yChip2 = $yChip1 + $alturaChip;

        $this->retanguloArredondado($canvas, $xChip1, $yChip1, $xChip2, $yChip2, 20, $cinzaChip);

        // Ícone de maleta simplificado (retângulo + alça), na cor azul da marca
        $iconeX = $xChip1 + 24;
        $iconeY = $yChip1 + ($alturaChip / 2) - 16;
        $azulSolido = imagecolorallocate($canvas, ...self::AZUL);
        imagefilledrectangle($canvas, (int) $iconeX, (int) ($iconeY + 8), (int) ($iconeX + 32), (int) ($iconeY + 30), $azulSolido);
        imagesetthickness($canvas, 3);
        imagearc($canvas, (int) ($iconeX + 16), (int) ($iconeY + 6), 16, 14, 180, 360, $azulSolido);
        imagesetthickness($canvas, 1);

        // Textos do chip: "Setor" (pequeno) + nome do setor (bold)
        $xTextoChip = $iconeX + 44;
        $larguraTextoChip = $xChip2 - $xTextoChip - 16;

        imagettftext($canvas, 16, 0, (int) $xTextoChip, (int) ($yChip1 + 52), imagecolorallocate($canvas, ...self::CINZA_TEXTO), $fonteMedium, 'Setor');

        $tamanhoFonteSetor = $this->ajustarTamanhoFonte($setor, $fonteBold, 22, $larguraTextoChip);
        imagettftext($canvas, $tamanhoFonteSetor, 0, (int) $xTextoChip, (int) ($yChip1 + 82), $azul, $fonteBold, $setor);
    }

    /**
     * Reduz o tamanho da fonte até que o texto caiba na largura máxima.
     */
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

    // ------------------------------------------------------------------
    // LOGO: Mottanet centralizado no rodapé
    // ------------------------------------------------------------------
    private function desenharLogo($canvas): void
    {
        $caminhoLogo = $this->pastaImagens . 'logo.png';
        $logo = $this->carregarImagem($caminhoLogo);
        if ($logo === null) {
            return;
        }

        $larguraLogo = 280;
        $alturaOriginal = imagesy($logo);
        $larguraOriginal = imagesx($logo);
        $alturaLogo = (int) ($larguraLogo * ($alturaOriginal / $larguraOriginal));

        $x = (int) ((self::LARGURA - $larguraLogo) / 2);
        $y = self::ALTURA - $alturaLogo - 30;

        imagesavealpha($canvas, true);
        imagecopyresampled($canvas, $logo, $x, $y, 0, 0, $larguraLogo, $alturaLogo, $larguraOriginal, $alturaOriginal);
        imagedestroy($logo);
    }

    // ------------------------------------------------------------------
    // HELPERS GERAIS
    // ------------------------------------------------------------------

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

    /**
     * Desenha um retângulo com cantos arredondados preenchido, via
     * retângulo central + 4 elipses preenchidas (técnica compatível com GD puro).
     */
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