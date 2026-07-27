<?php

namespace App\Core;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * Helper genérico pra exportar qualquer listagem/relatório do sistema como
 * .xlsx, sem repetir o boilerplate do PhpSpreadsheet em cada controller.
 *
 * Uso:
 *   ExportadorExcel::baixar('colaboradores.xlsx', ['Nome', 'Cidade'], [
 *       ['Ana Souza', 'Curitiba/PR'],
 *       ['Bruno Lima', 'Ponta Grossa/PR'],
 *   ]);
 */
class ExportadorExcel
{
    public static function baixar(string $nomeArquivo, array $cabecalhos, array $linhas): void
    {
        $planilha = new Spreadsheet();
        $aba = $planilha->getActiveSheet();

        foreach ($cabecalhos as $indice => $titulo) {
            $coluna = Coordinate::stringFromColumnIndex($indice + 1);
            $aba->setCellValue($coluna . '1', $titulo);
            $aba->getStyle($coluna . '1')->getFont()->setBold(true);
        }

        $numeroLinha = 2;
        foreach ($linhas as $linha) {
            foreach (array_values($linha) as $indice => $valor) {
                $coluna = Coordinate::stringFromColumnIndex($indice + 1);
                $aba->setCellValue($coluna . $numeroLinha, $valor);
            }
            $numeroLinha++;
        }

        $ultimaColuna = Coordinate::stringFromColumnIndex(count($cabecalhos));
        foreach (Coordinate::extractAllCellReferencesInRange('A1:' . $ultimaColuna . '1') as $referencia) {
            $letra = Coordinate::coordinateFromString($referencia)[0];
            $aba->getColumnDimension($letra)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($planilha);
        $writer->save('php://output');
        exit;
    }
}