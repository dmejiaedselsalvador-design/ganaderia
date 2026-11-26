<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocumento;

/** Genera los PDF con el diseño común (resources/views/pdf/layout) y número de página. */
class DocumentoPdf
{
    public static function crear(string $vista, array $datos = [], string $orientacion = 'portrait'): PdfDocumento
    {
        $pdf = Pdf::loadView($vista, $datos)->setPaper('letter', $orientacion);
        $pdf->render();

        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();
        $fuente = $dompdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
        $canvas->page_text($canvas->get_width() - 100, $canvas->get_height() - 36, 'Página {PAGE_NUM} de {PAGE_COUNT}', $fuente, 7, [0.61, 0.64, 0.69]);

        return $pdf;
    }
}
