<?php
    require_once('../public/core/tcpdf/config/lang/spa.php');
	require_once('../public/core/tcpdf/tcpdf.php');
    ini_set('error_reporting',0);

Class PDF extends TCPDF{

    private $titulo = "";
    private $inicio = "";
    private $fin = "";
    private $prov = "";

    public function setTitulo($tittle, $inicio, $fin, $prov){
        $this->titulo=$tittle;
        $this->inicio=$inicio;
        $this->fin=$fin;
        $this->prov=$prov;
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
		$this->setX(15);
        $this->Cell(88, 0, '', 'B', 0, 'L');
		$this->Cell(10, 0, '', 0, 0, 'L');
		$this->Cell(88, 0, '', 'B', 1, 'L');
		$this->Ln(1);
		$this->SetFont(PDF_FONT_NAME_MAIN, 'B', 10);
		$this->Cell(45, 5, 'DONADOR', 0, 0, 'C');
		$this->Cell(32, 5, 'PESO TOTAL', 0, 0, 'L');
        $this->Cell(25, 5, 'IMPORTE', 0, 0, 'L');
		$this->Cell(10, 5, '', 0, 0, 'L');
		$this->Cell(30, 5, 'DONADOR', 0, 0, 'C');
		$this->Cell(25, 5, 'PESO TOTAL', 0, 0, 'C');
        $this->Cell(28, 5, 'IMPORTE', 0, 1, 'C');
        $this->setX(15);
		$this->Cell(88, 0, '', 'T', 0, 'L');
		$this->Cell(10, 0, '', 0, 0, 'L');
		$this->Cell(88, 0, '', 'T', 1, 'L');
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
    $miReporte->setTitulo($vista, $inicio, $fin, $total);
    $miReporte->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $miReporte->SetDisplayMode('real');
    $miReporte->SetAuthor('DDS.media'); 
    $miReporte->SetCreator('www.ddsmedia.net'); 
    $miReporte->SetTitle($vista); 
    $miReporte->SetSubject($vista);
    $miReporte->SetFillColor(240, 240, 240); 
    $miReporte->AddPage();
    $miReporte->SetFont(PDF_FONT_NAME_MAIN, '', 8);
    $miReporte->SetXY(15,60);
    $miReporte->SetMargins(15, 60); 
    $contador = 2;

    $cve = '';
    $peso = 0;
    $pesoTot = 0;
    $importe = 0;
    $importeTot = 0;
    $arrCve = array('AA' => 'AMBA ABARROTES', 'AP' => 'AMBA PERECEDEROS', 
                    'BA' => 'BANCOS ABARROTES', 'BP' => 'BANCOS PERECEDEROS', 
                    'LA' => 'LOCALES ABARROTES', 'LP' => 'LOCALES PERECEDEROS', 
                    'CO' => 'COMPRAS', 'BACEH' => 'BACEH', 
                    'C'  => 'FOLIO CANCELADO', 'MM' => 'MERMA', 
                    'PR' => 'PRODUCCION', 'WA' => 'WALMART');

    $arrCves = array();
    $arrAct = array();

    foreach($registros as $prod){

        if ($cve != $prod->clave){
            if(count($arrAct) > 0){
                $arrCves[] = $arrAct;
                $arrAct = array();
            }
            $cve = $prod->clave;
        }
        $arrAct[] = $prod;
    }

    $arrCves[] = $arrAct;

    $arrFinal = array(
				array('clavei' => 'titulo', 
					  'provei' => $arrCve[$arrCves[0][0]->clave],
                      'proveii' => $arrCve[$arrCves[0][0]->clave],
					  'pesoti' => '',
                      'importeti' => ''
					  )
			);

    $lnIzq = count($arrCves[0]);
    $l = 1;
    $pesot = 0;
    $importet = 0;

    foreach ($arrCves[0] as $reg) {
        $regFinal = array('clavei' => $reg->clave, 
                          'provei' => $reg->proveedor, 
                          'proveii' => $reg->proveedor, 
                          'pesoti' => number_format($reg->peso_total,3),
                          'importeti' => number_format($reg->importe_total,3));
        $arrFinal[] = $regFinal;
        $pesot = $pesot + str_replace(',', '', $reg->peso_total);
        $importet = $importet + str_replace(',', '', $reg->importe_total);
        $l = $l + 1;
    }

    $arrFinal[] = array('clavei' => 'total', 
                        'provei' => number_format($pesot,3), 
                        'proveii' => number_format($importet,3), 
                        'pesoti' => '', 
                        'importeti' => '', 
                        'claved' => '', 
                        'proved' => '', 
                        'provedd' => '', 
                        'pesotd' => '',
                        'importetd' => ''
                        );

    $l = $l + 1;
    $lnIzq = $lnIzq + 2;

    $pesot = 0;
    $importet = 0;

    if (count($arrCves) > 1) {

        $arrFinal[0]['claved'] = 'titulo';
        $arrFinal[0]['proved'] = $arrCve[$arrCves[1][0]->clave];
        $arrFinal[0]['provedd'] = $arrCve[$arrCves[1][0]->clave];
        $arrFinal[0]['pesotd'] = '';
        $arrFinal[0]['importetd'] = '';
    
        $lnDer = count($arrCves[1]);
        $i = 1;
        foreach ($arrCves[1] as $reg) {
            $arrFinal[$i]['claved'] = $reg->clave;
            $arrFinal[$i]['proved'] = $reg->proveedor;
            $arrFinal[$i]['provedd'] = $reg->proveedor;
            $arrFinal[$i]['pesotd'] = number_format($reg->peso_total,3);
            $arrFinal[$i]['importetd'] = number_format($reg->importe_total,3);
            $i = $i + 1;
            $pesot = $pesot + str_replace(',', '', $reg->peso_total);
            $importet = $importet + str_replace(',', '', $reg->importe_total);
        }
        $arrFinal[$i]['claved'] = 'total';
        $arrFinal[$i]['proved'] = number_format($pesot,3);
        $arrFinal[$i]['provedd'] = number_format($importet,3);
        $arrFinal[$i]['pesotd'] = '';
        $arrFinal[$i]['importetd'] = '';
        $i = $i + 1;
        $pesot = 0;
        $importet = 0;
        $lnDer = $lnDer + 2;
    }

    for ($j=2; $j<count($arrCves) ; $j++) { 
        $pesot = 0;
        $importet = 0;
        if ($lnIzq < $lnDer) {
            $lnIzq = $lnIzq + count($arrCves[$j]) + 2;
    
            $arrFinal[$l]['clavei'] = 'titulo';
            $arrFinal[$l]['provei'] = $arrCve[$arrCves[$j][0]->clave];
            $arrFinal[$l]['proveii'] = $arrCve[$arrCves[$j][0]->clave];
            $arrFinal[$l]['pesoti'] = '';
            $arrFinal[$l]['importeti'] = '';
            $l = $l + 1;
            
            foreach ($arrCves[$j] as $reg) {
                $arrFinal[$l]['clavei'] = $reg->clave;
                $arrFinal[$l]['provei'] = $reg->proveedor;
                $arrFinal[$l]['proveii'] = $reg->proveedor;
                $arrFinal[$l]['pesoti'] = number_format($reg->peso_total,3);
                $arrFinal[$l]['importeti'] = number_format($reg->importe_total,3);
                $l = $l + 1;
                $pesot = $pesot + str_replace(',', '', $reg->peso_total);
                $importet = $importet + str_replace(',', '', $reg->importe_total);
            }
            $arrFinal[$l]['clavei'] = 'total';
            $arrFinal[$l]['provei'] = number_format($pesot,3);
            $arrFinal[$l]['proveii'] = number_format($importet,3);
            $arrFinal[$l]['pesoti'] = '';
            $arrFinal[$l]['importeti'] = '';
            $l = $l + 1;
        }else{
            $lnDer = $lnDer + count($arrCves[$j]);
    
            $arrFinal[$i]['claved'] = 'titulo';
            $arrFinal[$i]['proved'] = $arrCve[$arrCves[$j][0]->clave];
            $arrFinal[$i]['provedd'] = $arrCve[$arrCves[$j][0]->clave];
            $arrFinal[$i]['pesotd'] = '';
            $arrFinal[$i]['importetd'] = '';
            $i = $i + 1;
            
            foreach ($arrCves[$j] as $reg) {
                $arrFinal[$i]['claved'] = $reg->clave;
                $arrFinal[$i]['proved'] = $reg->proveedor;
                $arrFinal[$i]['provedd'] = $reg->proveedor;
                $arrFinal[$i]['pesotd'] = number_format($reg->peso_total,3);
                $arrFinal[$i]['importetd'] = number_format($reg->importe_total,3);
                $i = $i + 1;
                $pesot = $pesot + str_replace(',', '', $reg->peso_total);
                $importet = $importet + str_replace(',', '', $reg->importe_total);
            }
            $arrFinal[$i]['claved'] = 'total';
            $arrFinal[$i]['proved'] = number_format($pesot,3);
            $arrFinal[$i]['provedd'] = number_format($importet,3);
            $arrFinal[$i]['pesotd'] = '';
            $arrFinal[$i]['importetd'] = '';
            $i = $i + 1;
            $lnDer = $lnDer + 2;
        }
    }

    foreach ($arrFinal as $line) {
        $relleno = $contador % 2;
    
        $miReporte->SetFont(PDF_FONT_NAME_MAIN, 'B', 9);
        if(isset($line['clavei'])){
            if ($line['clavei'] == 'titulo') {
                $miReporte->Cell(88, 5, $line['provei'], 0, 0, 'L', $relleno);
            }else if ($line['clavei'] == 'total') {
                $miReporte->Cell(32, 5, '', 0, 0, 'L', $relleno);
                $miReporte->Cell(28, 5, $line['provei'], 0, 0, 'R', $relleno);
                $miReporte->Cell(28, 5, $line['proveii'], 0, 0, 'R', $relleno); /////////// total negrita de importe izquierda
                $pesoTot = $pesoTot + str_replace(',', '', $line['provei']);
                $importeTot = $importeTot + str_replace(',', '', $line['proveii']);
            }else{
                $miReporte->SetFont(PDF_FONT_NAME_MAIN, '', 6.5);
                $miReporte->Cell(32, 5, $line['provei'], 0, 0, 'L', $relleno);
                $miReporte->SetFont(PDF_FONT_NAME_MAIN, '', 9);
                $miReporte->Cell(28, 5, $line['pesoti'], 0, 0, 'R', $relleno);
                $miReporte->Cell(28, 5, $line['importeti'], 0, 0, 'R', $relleno); ///////////// izquierda importe normal
            }
        }else{
            $miReporte->Cell(88, 5, '', 0, 0, 'L', $relleno);
        }
    
        $miReporte->Cell(10, 5, '', 0, 0, 'L');
    
        $miReporte->SetFont(PDF_FONT_NAME_MAIN, 'B', 9);
        if(isset($line['claved'])){
            if ($line['claved'] == 'titulo') {
                $miReporte->Cell(88, 5, $line['proved'], 0, 1, 'L', $relleno);
            }else if ($line['claved'] == 'total') {
                $miReporte->Cell(30, 5, '', 0, 0, 'L', $relleno);
                $miReporte->Cell(30, 5, $line['proved'], 0, 0, 'R', $relleno);
                $miReporte->Cell(28, 5, $line['provedd'], 0, 1, 'R', $relleno); /////////////////// derecha total negrita de importe
                $pesoTot = $pesoTot + str_replace(',', '', $line['proved']);
                $importeTot = $importeTot + str_replace(',', '', $line['provedd']);
            }else{
                $miReporte->SetFont(PDF_FONT_NAME_MAIN, '', 6.5);
                $miReporte->Cell(32, 5, $line['proved'], 0, 0, 'L', $relleno);
                $miReporte->SetFont(PDF_FONT_NAME_MAIN, '', 9);
                $miReporte->Cell(28, 5, $line['pesotd'], 0, 0, 'R', $relleno);
                $miReporte->Cell(28, 5, $line['importetd'], 0, 1, 'R', $relleno); ///////// derecha importe normal
            }
        }else{
            $miReporte->Cell(88, 5, '', 0, 1, 'L', $relleno);
        }
    
        $contador = $contador + 1;
    }

    $miReporte->Cell(88, 0, '', 'T', 0, 'L');
    $miReporte->Cell(10, 0, '', 0, 0, 'L');
    $miReporte->Cell(88, 0, '', 'T', 1, 'L');
    
    $miReporte->Ln(5);
    $miReporte->SetFont(PDF_FONT_NAME_MAIN, '', 13);
    $miReporte->Cell(88, 5, 'TOTAL SALIDAS:', 0, 0, 'R');
    $miReporte->Cell(10, 5, '', 0, 0, 'C');
    $miReporte->SetFont(PDF_FONT_NAME_MAIN, 'B', 13);
    $miReporte->Cell(88, 5, number_format($pesoTot,3), 0, 1, 'L');

    $miReporte->SetFont(PDF_FONT_NAME_MAIN, '', 13);
    $miReporte->Cell(88, 5, 'IMPORTE TOTAL:', 0, 0, 'R');
    $miReporte->Cell(10, 5, '', 0, 0, 'C');
    $miReporte->SetFont(PDF_FONT_NAME_MAIN, 'B', 13);
    $miReporte->Cell(88, 5, number_format($importeTot,3), 0, 1, 'L'); /////////// importe total
    $miReporte->Output();
exit();
?>