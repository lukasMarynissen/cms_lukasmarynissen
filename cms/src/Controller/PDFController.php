<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Dompdf\Dompdf;
use Dompdf\Options;

class PDFController extends AbstractController
{
    /**
     * @Route("/pdf", name="pdf")
     */
    public function index()
    {

        $pdfOptions = new Options();
        $dompdf = new Dompdf($pdfOptions);

        //$html = $this->renderView('pdf/index.html.twig');
        $html = "<html><body><div><p>what</p></div></body></html>";

        $dompdf->loadHtml($html);
        $dompdf->render();

        $dompdf->stream("test.pdf", [
            "Attachment" => true
        ]);

    }
}
