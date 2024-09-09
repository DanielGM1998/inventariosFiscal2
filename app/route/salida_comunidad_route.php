<?php
	use App\Lib\Response;
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Csv;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

	$app->group('/salida_comunidad/', function () {
		$this->get('', function($request, $response, $arguments) {
			return $response->withHeader('Content-type', 'text/html')->write('Soy ruta de salida_comunidad');			
		});

        // Obtener todas las salida_comunidad
		$this->get('getAll/{pagina}/{limite}[/{busqueda}]', function($request, $response, $arguments) {
			$arguments['busqueda'] = isset($arguments['busqueda'])? $arguments['busqueda']: null;
			return $response->withJson($this->model->salida_comunidad->getAll($arguments['pagina'], $arguments['limite'], $arguments['busqueda']));
		});

        // Obtener salida_comunidad por fecha
		$this->get('getByDate/{inicio}/{final}', function($request, $response, $arguments) {
			$resultado = $this->model->salida_comunidad->getByDate($arguments['inicio'], $arguments['final']);
			foreach ($resultado->result as $sal) {
				$sal->total = number_format($sal->total,2);
			}
			return $response->withJson($resultado);
		});

        // Cancelar salida_comunidad
		$this->put('cancel/{salida}', function($request, $response, $arguments) {
			$this->model->transaction->iniciaTransaccion();
			$infoSalida = $this->model->salida_comunidad->get($arguments['salida'])->result->fecha;
            $fechaSalida = substr($infoSalida, 0,10);
            $detSalid = $this->model->det_salida_comunidad->getBySalida($arguments['salida'])->result;
            $count=count($detSalid);
            for($x=0;$x<$count;$x++){
                $cant = $detSalid[$x]->cantidad;
                $prod = $detSalid[$x]->fk_producto;
                $stockcomunidadsum = $this->model->producto->stockComunidadSum($cant, $prod);
				if(!$stockcomunidadsum->response){
					$this->model->transaction->regresaTransaccion();
					return $response->withJson($stockcomunidadsum); 
				}
                $salidasrest = $this->model->kardex_comunidad->salidasRest($cant, $prod, $fechaSalida);
				if(!$salidasrest->response){
					$this->model->transaction->regresaTransaccion();
					return $response->withJson($salidasrest); 
				}
				$this->model->kardex_comunidad->arreglaKardex($prod, $fechaSalida);
            }
            $CancelaSalida=$this->model->salida_comunidad->del($arguments['salida']);
			if($CancelaSalida){
				$seg_log = $this->model->seg_log->add('Cancelar salida_comunidad',$arguments['salida'], 'salida_comunidad'); 
						if(!$seg_log->response) {
							$this->model->transaction->regresaTransaccion(); 
							return $response->withJson($seg_log);
						}
			}
			$this->model->transaction->confirmaTransaccion();
			return $response->withJson($CancelaSalida);
		});

        // Agregar salida_comunidad
		$this->post('add/', function($request, $response, $arguments) {
			$this->response = new Response();
			$this->response->state = $this->model->transaction->iniciaTransaccion();
			$parsedBody = $request->getParsedBody();
			$fecha=date("Y-m-d");
            $_fk_cliente = $parsedBody['fk_cliente'];
            $_peso_total = $parsedBody['peso_total'];
            $_folio = $parsedBody['folio'];
            $_total = $parsedBody['total'];
            $_cajero = 35;
            $data = [
				'comunidad'=>$_fk_cliente,
				'peso_total'=>$_peso_total,
                'folio'=>$_folio,
                'total'=>$_total
			];
			$resSalida =  $this->model->salida_comunidad->add($data);
            $_fk_salida = $resSalida->result;
			if($_fk_salida){
				$seg_log = $this->model->seg_log->add('Agregar salida_comunidad',$_fk_salida, 'salida_comunidad'); 
						if(!$seg_log->response) {
							$this->model->transaction->regresaTransaccion(); 
							return $response->withJson($seg_log);
						}
			}
			if(!$_fk_salida){
				$this->model->transaction->regresaTransaccion();
				return $response->withJson($_fk_salida);
			}
            $terminacion = $_fk_cliente=="TIENDITA"
                ? 'entrada_tiendita'
                : ($_fk_cliente=="PRODUCCION"
                    ? 'entrada_produccion'
                    : ($_fk_cliente=="ALMACEN"
                        ? 'entrada'
                        : ''));
            $_fk_entrada = null;
            $field = $terminacion=='entrada_tiendita'
				? "fk_cajero"
				: "";
            $value = $terminacion=='entrada_tiendita'
                ? "$_cajero"
                : "";
            $kardex_terminacion = null;
            $fieldAlmacen = $terminacion=='entrada'
				? 'fk_proveedor, nota_entrada, tipo, valor'
				: '';
            if(strlen($terminacion)>0){
                $_fk_entrada = $this->model->salida_comunidad->fk_entrada($terminacion, $field, $fieldAlmacen, $_peso_total, $value);
            }
			$detalles = $parsedBody['detalles'];
			$cont=1;
			foreach($detalles as $detalle) {
				$_fk_producto = $detalle['id_producto'];
				if(intval($_fk_producto) <= 0){
					$respuesta=new Response();
					$respuesta->state=$this->model->transaction->regresaTransaccion(); 
					$respuesta->SetResponse(false,"Producto $cont incorrecto");
					return $response->withJson($respuesta);
				}
				$cont++;
                $_cantidad = $detalle['cantidad'];
                $_peso = $detalle['peso'];
                $_importe = $detalle['importe'];
				$produc = $this->model->producto->get($_fk_producto);
				if($produc->result->stock_comunidad < $_cantidad){
					$this->model->transaction->regresaTransaccion();
					$produc->setResponse(false,'No hay suficiente stock para '.$produc->result->descripcion);
					unset($produc->result);
					return $response->withJson($produc);
				}
				$stock = $this->model->kardex_comunidad->kardexByDate($fecha, $_fk_producto);
				if($stock->Total<=0){
                    $_inicial = '0';
					$_final = '0';
					$kardexinicial = $this->model->kardex_comunidad->kardexInicial($fecha, $_fk_producto);
                    if($kardexinicial!='0'){
						$_inicial = $kardexinicial;
					}
					$kardexfinal = $this->model->kardex_comunidad->kardexFinal($fecha, $_fk_producto);
					if($kardexfinal!='0'){
						$_final = $kardexfinal;
					}
                    if($_final == '0') { $_final = $_inicial; }
                    $dataKardex = [
                        'fk_producto'=>$_fk_producto,
                        'inicial'=>$_inicial,
                        'entradas'=>'0',
                        'salidas'=>'0',
                        'final'=>$_final
                    ];
                    $new_kardex = $this->model->kardex_comunidad->add($dataKardex);					
                    if(!$new_kardex->response) {
                        $this->model->transaction->regresaTransaccion();
                        return $response->withJson($new_kardex); 
                    }
                }
				$condicion = $_fk_cliente == "TIENDITA"
					? " AND fk_cajero=$_cajero"
					: "";
				if(strlen($terminacion)>0) {
					$stock2 = $this->model->salida_comunidad->stockByKardex($terminacion, $_fk_producto, $fecha, $condicion, $_cajero);
					if($stock2==null){
						$_inicial2 = '0';
						$_final2 = '0';
                        $kardexinicialbykardex = $this->model->kardex_comunidad->kardexInicialByKardex($terminacion, $fecha, $_fk_producto, $condicion);
						if($kardexinicialbykardex){
							$_inicial2 = ($kardexinicialbykardex)[0]->final;
						}
						$kardexfinalbykardex = $this->model->kardex_comunidad->kardexFinalByKardex($terminacion, $fecha, $_fk_producto, $condicion, $_cajero);
						if($kardexfinalbykardex){
							$_final2 = ($kardexfinalbykardex)[0]->inicial;
						}
						if($_final2 == '0') { $_final2 = $_inicial2; }
                        $dataKard = [
							'fk_producto'=>$_fk_producto,
							'inicial'=>$_inicial2,
							'entradas'=>'0',
							'salidas'=>'0',
							'final'=>$_final2
						];
						$new_kardex = $this->model->kardex_comunidad->addByKardex($dataKard, $value, $terminacion);
						if(!$new_kardex->response) {
							$this->model->transaction->regresaTransaccion();
							return $response->withJson($new_kardex); 
						}
					}
				}
				if(strlen($terminacion)>0){
					$this->model->producto->stockSumByStock($terminacion, $_cantidad, $_fk_producto);
				}
				$edit_producto = $this->model->producto->stockComunidadRest($_cantidad, $_fk_producto);
				if($edit_producto->response) {
					$edit_kardex = $this->model->kardex_comunidad->salidasSum($_cantidad, $_fk_producto, $fecha);
					if($edit_kardex->response) {
						if(strlen($terminacion)>0) {
							$edit_kardex_terminacion = $this->model->kardex_comunidad->editByKardex($terminacion, $fecha, $_fk_producto, $_cantidad, $condicion, $_cajero);
							if($edit_kardex_terminacion->response) {
								$edit_next_kardex_terminacion = $this->model->kardex_comunidad->inicialfinalSumByFechaCajero($terminacion, $fecha, $_fk_producto, $_cantidad, $condicion, $_cajero);
								//if($edit_next_kardex_terminacion->response) {
									switch($terminacion) {
										case 'entrada_tiendita': $this->model->kardex_tiendita->arreglaKardex($_fk_producto, $fecha, $_cajero); break;
										case 'entrada_produccion': $this->model->kardex_produccion->arreglaKardex($_fk_producto, $fecha); break;
										case 'entrada': $this->model->kardex->arreglaKardex($_fk_producto, $fecha); break;
									}
									$dataDetEntr = [
										'fk_entrada'=>$_fk_entrada,
										'fk_producto'=>$_fk_producto,
										'cantidad'=>$_cantidad,
										'peso'=>$_peso
									];
									$add_detalle_terminacion = $this->model->det_entrada->addTipo($terminacion, $dataDetEntr);
									if($add_detalle_terminacion->response) {
									}else{
										$this->model->transaction->regresaTransaccion();
										return $response->withJson($add_detalle_terminacion); 
									}
								//}
							}else{
								$this->model->transaction->regresaTransaccion();
								return $response->withJson($edit_kardex_terminacion); 
							}
						}
						$dataDetSali = [
							'fk_salida'=>$_fk_salida,
							'fk_producto'=>$_fk_producto,
							'cantidad'=>$_cantidad,
							'peso'=>$_peso,
							'importe'=>$_importe
						];
						$add_detalle = $this->model->det_salida_comunidad->add($dataDetSali);
						if(!$add_detalle->response) {                             
							$this->model->transaction->regresaTransaccion();
							return $response->withJson($add_detalle);
						}else{
							$edit_next_kardex = $this->model->kardex_comunidad->inicialfinalRest($_cantidad, $_fk_producto, $fecha);
							if($edit_next_kardex->response) {
								$this->model->kardex_comunidad->arreglaKardex($_fk_producto, $fecha);
							}
						}
					}else{
                        $this->model->transaction->regresaTransaccion();
						return $response->withJson($edit_kardex);
                    }
				}else{
                    $this->model->transaction->regresaTransaccion();
					return $response->withJson($edit_producto);
                }
			}
			$this->model->transaction->confirmaTransaccion();
			return $response->withJson($resSalida);
		});

        // Editar salida_comunidad
		$this->post('edit/{id}', function($request, $response, $arguments) {
            require_once './core/defines.php';
			$this->model->transaction->iniciaTransaccion();
			$parsedBody = $request->getParsedBody();
			$salida_id = $arguments['id'];
            $_fk_cliente = $parsedBody['fk_cliente'];
            $_peso_total = $parsedBody['peso_total'];
            $_folio = $parsedBody['folio'];
            $_total = $parsedBody['total'];
            $dataSalida = [
				'comunidad'=>$_fk_cliente,
				'peso_total'=>$_peso_total,
                'folio'=>$_folio,
                'total'=>$_total
			];
            $salida = $this->model->salida_comunidad->edit($dataSalida, $salida_id);
            $detalles = $parsedBody['detalles'];
            foreach($detalles as $detalle) {
				$dataDetSalida = [
					'fk_producto'=>$detalle['id_producto'],
					'cantidad'=>$detalle['cantidad'], 
					'peso'=>$detalle['peso'],
                    'importe'=>$detalle['importe']
				];
                $salida2 = $this->model->det_salida_comunidad->editBySalida($dataDetSalida, $salida_id, $detalle['id_producto']);
				if(!$salida2->response) {
					$this->model->transaction->regresaTransaccion();
					return $response->withJson($salida2); 
				}
			}
            if($salida->response) {
				$seg_log = $this->model->seg_log->add('Actualización información salida_comunidad', $salida_id, 'salida_comunidad', 1);
				if(!$seg_log->response) {
					$seg_log->state = $this->model->transaction->regresaTransaccion();
					return $response->withJson($seg_log);
				}
				$salida->SetResponse(true, 'Salida_comunidad actualizada');
			}else{
				$salida->state = $this->model->transaction->regresaTransaccion(); 
				return $response->withJson($salida); 
			}
			$this->model->transaction->confirmaTransaccion();
			return $response->withJson($salida);
		});

        // Editar estado de salida_comunidad
		$this->post('editEstado/{id}', function($request, $response, $arguments) {
            require_once './core/defines.php';
			$this->model->transaction->iniciaTransaccion();
			$parsedBody = $request->getParsedBody();
			$salida_id = $arguments['id'];
			$dataSalida = [
				'estado'=>$parsedBody['estado']
			];
			$salida = $this->model->salida_comunidad->edit($dataSalida, $salida_id);
			if($salida->response) {
				$seg_log = $this->model->seg_log->add('Actualización información salida_comunidad', $salida_id, 'salida_comunidad', 1);
				if(!$seg_log->response) {
					$seg_log->state = $this->model->transaction->regresaTransaccion(); 
					return $response->withJson($seg_log);
				}
				$salida->SetResponse(true, 'Salida_comunidad actualizada');
			}else{
				$salida->state = $this->model->transaction->regresaTransaccion(); 
				return $response->withJson($salida); 
			}
			$salida->state = $this->model->transaction->confirmaTransaccion();
			return $response->withJson($salida);
		});

        // Obtener salida_comunidad por id
		$this->get('get/{id}', function($request, $response, $arguments) {
			$resultado = $this->model->salida_comunidad->get($arguments['id']);
			$resultado->detalles = $this->model->det_salida_comunidad->getBySalida($arguments['id'])->result;
			return $response->withJson($resultado);
		});

        // Eliminar salida_comunidad
		$this->put('del/{id}', function($request, $response, $arguments) {
			$this->model->transaction->iniciaTransaccion();
			$salida_id = $arguments['id'];
			$del_salida = $this->model->salida_comunidad->del($salida_id);
			if($del_salida->response) {
				$seg_log = $this->model->seg_log->add('Baja de salida_comunidad', $salida_id, 'salida_comunidad'); 
				if(!$seg_log->response) {
					$seg_log->state = $this->model->transaction->regresaTransaccion(); 
					return $response->withJson($seg_log);
				}
			}else{ $del_salida->state = $this->model->transaction->regresaTransaccion();
				return $response->withJson($del_salida);
			}
			$del_salida->state = $this->model->transaction->confirmaTransaccion();
			return $response->withJson($del_salida);
		});

        // Buscar salida_comunidad
		$this->get('find/{busqueda}', function($request, $response, $arguments) {
			return $response->withJson($this->model->salida_comunidad->find($arguments['busqueda']));
		});

        // Agregar detalle de salida_comunidad
		$this->post('addDetalle/', function($request, $response, $arguments) {
            $parsedBody = $request->getParsedBody();
			$this->model->transaction->iniciaTransaccion();
            $dataDetalles = [
				'fk_salida'=>$parsedBody['fk_salida'],
				'fk_producto'=>$parsedBody['fk_producto'], 
				'cantidad'=>$parsedBody['cantidad'], 
				'peso'=>$parsedBody['peso']
			];
			$add = $this->model->det_salida_comunidad->add($dataDetalles);
			if($add){
				$seg_log = $this->model->seg_log->add('Agregar detalle salida_comunidad',$parsedBody['fk_salida'], 'det_salida_comunidad'); 
						if(!$seg_log->response) {
							$this->model->transaction->regresaTransaccion(); 
							return $response->withJson($seg_log);
						}
			}
			$this->model->transaction->confirmaTransaccion();
			return $response->withJson($add);
		});

        // Obtener det_salida_comunidad por salida_comunidad
		$this->get('getDetalles/{salida}', function($request, $response, $arguments) {
			return $response->withJson($this->model->det_salida_comunidad->getBySalida($arguments['salida']));
		});

        // Eliminar salida_comunidad
		$this->put('delDetalleSalida/{id}', function($request, $response, $arguments) {
			require_once './core/defines.php';
			$this->model->transaction->iniciaTransaccion();
			$del_det_salida = $this->model->det_salida_comunidad->getById($arguments['id']);
			$cant = $del_det_salida->cantidad;
			$fecha = substr($del_det_salida->fecha,0,10);
			$salida = $del_det_salida->fk_salida;
			$prod = $del_det_salida->fk_producto;
			$del_detsalida = $this->model->det_salida_comunidad->del($arguments['id']);
            if($del_detsalida->response){
                $stockcomunidadsum = $this->model->producto->stockComunidadSum($cant, $prod);
				if(!$stockcomunidadsum->response){
					$this->model->transaction->regresaTransaccion();
					return $response->withJson($stockcomunidadsum); 
				}
                $salidasrest = $this->model->kardex_comunidad->salidasRest($cant, $prod, $fecha);
				if(!$salidasrest->response){
					$this->model->transaction->regresaTransaccion();
					return $response->withJson($salidasrest); 
				}
				$this->model->kardex_comunidad->arreglaKardex($prod, $fecha);
                $PesoTotal = $this->model->det_salida_comunidad->getPesoTotal($salida)->peso_total;
                $Total = $this->model->det_salida_comunidad->getTotal($salida)->total;
                $data = [
					'peso_total'=>$PesoTotal,
					'total'=>$Total
				];
                $edit = $this->model->salida_comunidad->edit($data, $salida); 
                if($edit->response) {
					$seg_log = $this->model->seg_log->add('Baja de det_salida_comunidad', $arguments['id'], 'det_salida_comunidad'); 
					if(!$seg_log->response) {
						$seg_log->state = $this->model->transaction->regresaTransaccion(); 
						return $response->withJson($seg_log);
					}
				}else{
					$this->model->transaction->regresaTransaccion(); 
					return $response->withJson($edit); 
				}
            }else{
				$this->model->transaction->regresaTransaccion(); 
				return $response->withJson($del_detsalida); 
			}	
			$del_detsalida->result = null; 
			$this->model->transaction->confirmaTransaccion();
			return $response->withJson($del_detsalida);
		});

        // Obtener reporte entre fechas
		$this->get('rpt/{inicio}/{fin}', function($request, $response, $arguments) {
			return $response->withJson($this->model->det_salida_comunidad->getRpt($arguments['inicio'], $arguments['fin']));
		});

		// Obtener reporte entre fechas (pdf)
		$this->get('rpt/print/{inicio}/{fin}', function($request, $response, $arguments) {
			$salidas = $this->model->det_salida_comunidad->getRpt($arguments['inicio'], $arguments['fin']);
			$titulo = "REPORTE DE SALIDAS [Comunidad]";
			$params = array('vista' => $titulo);
        	$params['registros'] = $salidas;
			$params['inicio'] = $arguments['inicio'];
			$params['fin'] = $arguments['fin'];
			return $this->view->render($response, 'rptSalidasCom.php', $params);
		});

		// Obtener reporte entre fechas (xlsx)
		$this->get('rpt/xlsx/{inicio}/{fin}', function($request, $response, $arguments){
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();

			$arrMes = array('','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
			$arrFe = explode('-', $arguments['inicio']);
			$subtitulo = "del ".$arrFe[2]." de ".$arrMes[(int)$arrFe[1]]." del ".$arrFe[0];
			if($arguments['inicio'] != $arguments['fin']){
				$arrFe = explode('-', $arguments['fin']);
				$subtitulo .= " al ".$arrFe[2]." de ".$arrMes[(int)$arrFe[1]]." del ".$arrFe[0];
			}

			$sheet->setCellValue("A1", "REPORTE DE SALIDAS [Comunidad]");
			$sheet->setCellValue("E1", $subtitulo);
			$sheet->setCellValue("A2", 'Folio');
			$sheet->setCellValue("B2", 'Fecha');
			$sheet->setCellValue("C2", "Comunidad");
			$sheet->setCellValue("D2", 'Proveedor');
			$sheet->setCellValue("E2", 'Producto');
			$sheet->setCellValue("F2", 'Cantidad');
			$sheet->setCellValue("G2", 'Peso');
			$sheet->setCellValue("H2", 'Importe');

			$resultado = $this->model->det_salida_comunidad->getRpt($arguments['inicio'], $arguments['fin']);

			//echo json_encode($resultado); exit;

    		$comu = ''; 
			$totalc = 0; 
			$totalcp = 0; 
			$contador = 2;

			$total = 0;
			$total2 = 0;

			$fila = 3;
			foreach($resultado as $res){
				if($comu!=$res->cliente && $contador>2) {
					$sheet->setCellValue("G".$fila, number_format($totalcp, 3,'.', ','));
					$sheet->setCellValue("H".$fila, number_format($totalc, 2,'.', ','));
					$fila++;
					
					$contador = $contador + 1;
					$comu = $res->cliente;
					$totalcp = 0;
					$totalc = 0;
				}

				$total += $res->peso;
        		$total2 += $res->importe;

				$sheet->setCellValue("A".$fila, $res->folio);
				$sheet->setCellValue("B".$fila, $res->fechaf);
				$sheet->setCellValue("C".$fila, $res->cliente);
				$sheet->setCellValue("D".$fila, $res->proveedor);
				$sheet->setCellValue("E".$fila, $res->descripcion);
				$sheet->setCellValue("F".$fila, $res->cantidad);
				$sheet->setCellValue("G".$fila, $res->peso);
				$sheet->setCellValue("H".$fila, number_format($res->importe, 2,'.', ','));

				$contador = $contador + 1;
				$totalcp += $res->peso;
				$totalc += $res->importe;

				$fila++;
			}
			$sheet->setCellValue("G".$fila, number_format($totalcp, 3,'.', ','));
			$sheet->setCellValue("H".$fila, number_format($totalc, 2,'.', ','));
			$fila++;
			$sheet->setCellValue("D".$fila, 'TOTALES');
			$sheet->setCellValue("F".$fila, '$ '.number_format($total, 2,'.', ','));
			$sheet->setCellValue("H".$fila, '$ '.number_format($total2, 2,'.', ','));
			/* $writer = new Csv($spreadsheet);
			$writer->setUseBOM(true);
			header('Content-Type: text/csv');
			header("Content-Disposition: attachment; filename=\"Ventascajero_".date('YmdHi').".csv\"");
			$writer->save('php://output'); */
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header("Content-Disposition: attachment; filename=\"salidas_comunidad_".date('YmdHi').".xlsx\"");
			$writer->save('php://output');
		});

		// Obtener reporte por proveedor entre fechas
		$this->get('rptProv/{inicio}/{fin}/{prov}', function($request, $response, $arguments) {
			return $response->withJson($this->model->det_salida_comunidad->getRptProv($arguments['inicio'], $arguments['fin'], $arguments['prov']));
		});

		// Obtener reporte por cliente entre fechas
		$this->get('rptCli/{inicio}/{fin}/{cli}', function($request, $response, $arguments) {
			return $response->withJson($this->model->det_salida_comunidad->getRptCli($arguments['inicio'], $arguments['fin'], $arguments['cli']));
		});

		// Ruta para buscar
		$this->get('findBy/{f}/{v}', function ($req, $res, $args) {
			return $res->withHeader('Content-type', 'application/json')
				->write(json_encode($this->model->salida_comunidad->findBy($args['f'], $args['v'])));			
		});

	});
?>