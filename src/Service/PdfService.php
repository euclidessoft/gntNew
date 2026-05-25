<?php

namespace App\Service;

use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Symfony\Component\HttpKernel\KernelInterface;

class PdfService
{
    public function __construct(private Environment $twig,  private KernelInterface $kernel) {}

    private function createMpdf(): Mpdf
    {
        return new Mpdf([
            'mode'            => 'utf-8',
            'format'          => 'A4',
            'orientation'     => 'P',
            'margin_top'      => 15,
            'margin_bottom'   => 15,
            'margin_left'     => 15,
            'margin_right'    => 15,
            'tempDir'         => sys_get_temp_dir(),
            
        ]);
    }

    public function generatePdf(string $template, array $data = []): string
    {
        
        $mpdf = $this->createMpdf();

         $logoPath = $this->kernel->getProjectDir() . '/public/images/gntpharma.jpeg';
        $logoBase64 = '';

        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));
        } else {
            $logoContent = @file_get_contents('https://gnt-pharma.com/images/gntpharma.jpeg');
            if ($logoContent) {
                $logoBase64 = 'data:image/jpeg;base64,' . base64_encode($logoContent);
            }
        }

        $data['logoBase64'] = $logoBase64;
         $footer = '
        <table style="width:100%; border-top: 1px solid #ccc; padding-top: 5px;">
            <tr>
                <td style="font-size:9px; color:#888; text-align:center;">
                    GNT PHARMA &nbsp;|&nbsp;
                    RCCM RC/YAE/2023/M/105 &nbsp;|&nbsp;
                    NIU M122217881373Q &nbsp;|&nbsp;
                    info@gnt-pharma.com &nbsp;|&nbsp;
                    Page {PAGENO}/{nbpg}
                </td>
            </tr>
        </table>
    ';
    $mpdf->SetHTMLFooter($footer);

        $html = $this->twig->render($template, $data);
        $mpdf->WriteHTML($html);

        // 'S' = retourne le contenu en string
        return $mpdf->Output('', 'S');
    }

    /**
     * Téléchargement du PDF
     */
    public function streamPdf(string $template, array $data = [], string $filename = 'document.pdf'): Response
    {
        return new Response($this->generatePdf($template, $data), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
        ]);
    }

    /**
     * Affichage inline dans le navigateur
     */
    public function inlinePdf(string $template, array $data = [], string $filename = 'document.pdf'): Response
    {
        return new Response($this->generatePdf($template, $data), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => sprintf('inline; filename="%s"', $filename),
        ]);
    }

    /**
     * Sauvegarder le PDF sur le disque
     */
    public function savePdf(string $template, array $data = [], string $filePath): void
    {
        $mpdf = $this->createMpdf();
        $html = $this->twig->render($template, $data);
        $mpdf->WriteHTML($html);

        // 'F' = sauvegarde sur disque
        $mpdf->Output($filePath, 'F');
    }
}