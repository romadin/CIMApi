<?php

namespace App\Models\Tcpdf;

use App\Models\Organisation\Organisation;
use TCPDF;

class MyPdf extends TCPDF
{
    /**
     * @var Organisation | null
     */
    private $organisation = null;

    /**
     * @var string;
     */
    private $headerLogo;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false, $organisation = null)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        $this->organisation = $organisation;

        // set header logo
        $this->setHeaderLogo($this->organisation->getLogo() ? $this->getTempImagePathBlob($this->organisation->getLogo()) : storage_path('image/blank-profile.png'));

        // set document information
        $this->SetCreator('PDF generator');
        $this->SetAuthor('PDF generator');
        $this->SetTitle('BIM uitvoeringsplan');
        $this->SetSubject('BIM uitvoeringsplan');
        $this->SetKeywords('BIM, PDF, uitvoeringsplan');

        // set default Footer data
        $this->setFooterData(array(0,64,0), array(0,64,128));

        // set header and footer fonts
        $this->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $this->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $this->SetAutoPageBreak(TRUE, 15);

        // set image scale factor
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set default font subsetting mode
        $this->setFontSubsetting(true);

        // Set font
        // helvetica or times to reduce file size.
        $this->SetFont('helvetica', '', 11, '', true);

        // set display mode. Fixes vertical scrolling
        $this->SetDisplayMode('default','OneColumn');
    }

    //Page header
    public function Header() {
        // Logo
        $this->Image($this->getHeaderLogo(), 10, 10, 20, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
//        // Set font
//        $this->SetFont('helvetica', 'B', 20);
//        // Title
//        $this->Cell(0, 12, 'BIM uitvoeringsplan', 0, false, 'C', 0, '', 0, false, 'M', 'B');
//
//        $this->SetLineStyle(array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
//        $this->SetY((2.835 / $this->k) + max($this->getImageRBY(), $this->y));
//        if ($this->rtl) {
//            $this->SetX($this->original_rMargin);
//        } else {
//            $this->SetX($this->original_lMargin);
//        }
//        $this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, '', 'T', 0, 'C');
    }

    public function getHeaderLogo(): string {
        return $this->headerLogo;
    }

    public function setHeaderLogo(string $logo) {
        $this->headerLogo = $logo;
    }

    private function getTempImagePathBlob($blob) {
        $tempPath = tempnam(sys_get_temp_dir(), 'prefix');
        file_put_contents ($tempPath, $blob);
        return $tempPath;
    }
    private function getTempImagePathBase64($base64) {
        $imageContent = file_get_contents($base64);
        $tempPath = tempnam(sys_get_temp_dir(), 'prefix');
        file_put_contents ($tempPath, $imageContent);
        return $tempPath;
    }

}
