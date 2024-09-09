<?php
    require_once('../public/core/tcpdf/config/lang/spa.php');
	require_once('../public/core/tcpdf/tcpdf.php');
    ini_set('error_reporting',0);

Class PDF extends TCPDF{

    private $titulo = "";
    private $inicio = "";
    private $fin = "";
    private $cliente = "";

    public function setTitulo($tittle, $inicio, $fin, $cliente){
        $this->titulo=$tittle;
        $this->inicio=$inicio;
        $this->fin=$fin;
        $this->cliente=$cliente;
    }

	function Header(){

        $arrMes = array('','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
        $arrFe = explode('-', $this->inicio);
        $subtitulo = "Del ".$arrFe[2]." de ".$arrMes[(int)$arrFe[1]];
        if(utf8_decode($this->inicio) != utf8_decode($this->fin)){
            $arrFe = explode('-', $this->fin);
	        $subtitulo .= " al ".$arrFe[2]." de ".$arrMes[(int)$arrFe[1]]." del ".$arrFe[0];
        }else{
            $subtitulo .= " del ".$arrFe[0];
        }

        $this->Image('../public/assets/images/logo.png', 15, 5, 55, 40);
		$this->Ln(20);
		$this->SetFont(PDF_FONT_NAME_MAIN, 'B', 13);
		$this->Cell(0, 10, utf8_decode($this->titulo), 0, 1, 'R');
		$this->Cell(0, 10, $subtitulo, 0, 1, 'R');
		$this->Ln(5);
		$this->setX(5);
        $this->Cell(205, 0, '', 'B', 1, 'L');
        $this->Ln(1);
        $this->SetFont(PDF_FONT_NAME_MAIN, 'B', 10);
        $this->setX(5);
        $this->Cell(20, 5, 'FECHA', 0, 0, 'C');
        $this->Cell(45, 5, 'PRODUCTO', 0, 0, 'C');
        $this->Cell(25, 5, 'PESO UNIT', 0, 0, 'C');
        $this->Cell(20, 5, 'CANTIDAD', 0, 0, 'C');
        $this->Cell(25, 5, 'PESO TOTAL', 0, 0, 'C');
        $this->Cell(32, 5, 'IMPORTE TOTAL', 0, 0, 'C');
        $this->Cell(37, 5, 'PROVEEDOR', 0, 1, 'C');
        $this->setX(5);
        $this->Cell(205, 0, '', 'T', 1, 'L');
	}

	function Footer(){
		$paginas = "Página " . $this->PageNo() . ' de ' . $this->getAliasNbPages();
		$this->SetY(-15);
		$this->SetTextColor(77, 73, 72);
	    $this->Ln(4);
	    $this->SetFont(PDF_FONT_NAME_MAIN, 'B', 7);
	    $this->Cell(0, 3, $paginas, 0, 0, 'C');
	}
}

    $miReporte = new PDF('P', 'mm', 'letter');
    $miReporte->setTitulo($vista, $inicio, $fin, $cliente);
    $miReporte->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $miReporte->SetDisplayMode('real');
    $miReporte->SetAuthor('DDS.media'); 
    $miReporte->SetCreator('www.ddsmedia.net'); 
    $miReporte->SetTitle($vista); 
    $miReporte->SetSubject($subtitulo);
    $miReporte->SetFillColor(240, 240, 240); 
    $miReporte->AddPage();
    $miReporte->SetFont(PDF_FONT_NAME_MAIN, '', 8);
    $miReporte->SetXY(5,60);
    $miReporte->SetMargins(5, 60); 
    $contador = 2;

    $cliente = '';
    $peso = 0;	
    $importe = 0;
    $prov = '';
    $pesop = 0;
    $importep = 0;
    $provc = 0;

    foreach($registros as $prod){
        $relleno = $contador % 2;
        if ($cliente != $prod->cliente){
            $miReporte->SetFont(PDF_FONT_NAME_MAIN, 'B', 9);
            if($provc > 1 && $pesop > 0){
                $miReporte->Cell(105, 5, 'PESO TOTAL PROVEEDOR', 0, 0, 'R', $relleno);
                $miReporte->Cell(25, 5, number_format($pesop,3), 0, 0, 'R', $relleno);
                $miReporte->Cell(25, 5, number_format($importep,3), 0, 0, 'R', $relleno);
                $miReporte->Cell(51, 5, '', 0, 1, 'L', $relleno);
                $contador = $contador + 1;
                $pesop = 0;
                $importep = 0;
            }
            $relleno = $contador % 2;
            if($peso > 0){
                $miReporte->Cell(105, 5, 'PESO TOTAL CLIENTE', 0, 0, 'R', $relleno);
                $miReporte->Cell(25, 5, number_format($peso,3), 0, 0, 'R', $relleno);
                $miReporte->Cell(25, 5, number_format($importe,3), 0, 0, 'R', $relleno);
                $miReporte->Cell(51, 5, '', 0, 1, 'L', $relleno);
                $contador = $contador + 1;
                $peso = 0;
                $importe = 0;
            }
            $relleno = $contador % 2;
            $miReporte->Cell(206, 5, $prod->cliente, 0, 1, 'L', $relleno);
            $contador = $contador + 1;
            $miReporte->SetFont(PDF_FONT_NAME_MAIN, '', 8);
            $cliente = $prod->cliente;
            $relleno = $contador % 2;
    
            $provc = 0;	$pesop = 0; $importep = 0;	$prov = '';
        }
    
        if ($prov != $prod->proveedor){
            $miReporte->SetFont(PDF_FONT_NAME_MAIN, 'B', 9);
            $provc++;
            if($pesop > 0){
                $miReporte->Cell(105, 5, 'PESO TOTAL PROVEEDOR', 0, 0, 'R', $relleno);
                $miReporte->Cell(25, 5, number_format($pesop,3), 0, 0, 'R', $relleno);
                $miReporte->Cell(25, 5, number_format($importep,3), 0, 0, 'R', $relleno);
                $miReporte->Cell(51, 5, '', 0, 1, 'L', $relleno);
                $contador = $contador + 1;
                $pesop = 0;
                $importep = 0;
            }
            $prov = $prod->proveedor;
            $miReporte->SetFont(PDF_FONT_NAME_MAIN, '', 8);
            $relleno = $contador % 2;
        }
        $pesop = $pesop + $prod->peso;
        $importep = $importep + $prod->importe;
	
        $miReporte->Cell(18, 5, $prod->fecha, 0, 0, 'C', $relleno);
        $miReporte->Cell(50, 5, $prod->producto, 0, 0, 'L', $relleno);
        $miReporte->Cell(23, 5, number_format($prod->pesou,3), 0, 0, 'C', $relleno);
        $miReporte->Cell(20, 5, $prod->cantidad, 0, 0, 'C', $relleno);
        $miReporte->Cell(25, 5, number_format($prod->peso,3), 0, 0, 'C', $relleno);
        $miReporte->Cell(25, 5, number_format($prod->importe,3), 0, 0, 'C', $relleno);
        $miReporte->Cell(45, 5, $prod->proveedor, 0, 1, 'C', $relleno);
        $peso = $peso + $prod->peso;
        $importe = $importe + $prod->importe;
        $contador = $contador + 1;

    }
    $relleno = $contador % 2;
    $miReporte->SetFont(PDF_FONT_NAME_MAIN, 'B', 9);
    $miReporte->Cell(111, 5, '', 0, 0, 'C', $relleno);
    $miReporte->Cell(25, 5, number_format($peso,3), 0, 0, 'C', $relleno);
    $miReporte->Cell(25, 5, number_format($importe,3), 0, 0, 'C', $relleno);
    $miReporte->Cell(45, 5, '', 0, 1, 'L', $relleno);
    $miReporte->SetFont(PDF_FONT_NAME_MAIN, '', 8);

    $miReporte->Cell(0, 0, '', 'T', 1, 'L');
    
    $miReporte->Output();
exit();
?>