<?php
	namespace App\Model;

	use PDOException;
	use App\Lib\Response;

	class SalidaProduccionModel {
		private $db;
		private $table = 'salida_produccion';
        private $tableC = 'cat_cliente';
        private $tableU = 'cat_usuario';
		private $tableDSP = 'det_salida_produccion';
        private $tableP = 'cat_proveedor';
		private $response;
		
		public function __CONSTRUCT($db) {
			$this->db = $db;
			$this->response = new Response();
		}

		// Obtener todas las salidas_produccion
		public function getAll($pagina, $limite, $busqueda) {
			$inicial = $pagina * $limite;
			$busqueda = $busqueda==null ? "_" : $busqueda;
			$this->response->result = $this->db
				->from($this->table)
				->select(null)->select("$this->table.id_salida, $this->table.destino as cliente, $this->table.fecha, $this->table.peso_total, CONCAT_WS(' ', CONCAT_WS(' ', DATE_FORMAT($this->table.fecha, '%d-%m-%Y')), SUBSTRING($this->table.fecha, 12, 20)) as fechaf")
                ->where("CONCAT_WS(' ', $this->table.id_salida, $this->table.destino, $this->table.fecha, $this->table.peso_total) LIKE '%$busqueda%'")
                ->where("$this->table.estado", 1)
				->limit("$inicial, $limite")
				->orderBy("$this->table.fecha DESC")
				->fetchAll();
			$this->response->total = $this->db
				->from($this->table)
				->select(null)->select('COUNT(*) Total')
				->where("CONCAT_WS(' ', $this->table.id_salida, $this->table.destino, $this->table.fecha, $this->table.peso_total) LIKE '%$busqueda%'")
				->where("$this->table.estado", 1)
				->limit("$inicial, $limite")
				->fetch()
				->Total;
			return $this->response->SetResponse(true);
		}

        // Obtener salidas_produccion por fecha
        public function getByDate($inicio, $fin) {
			$this->response->result = $this->db
				->from($this->table)
				->select(null)->select("$this->table.id_salida, $this->table.destino as cliente, $this->table.fecha, $this->table.peso_total, CONCAT_WS(' ', CONCAT_WS(' ', DATE_FORMAT($this->table.fecha, '%d-%m-%Y')), SUBSTRING($this->table.fecha, 12, 20)) as fechaf")
                ->where("DATE_FORMAT($this->table.fecha, '%Y-%m-%d') BETWEEN DATE_FORMAT('$inicio', '%Y-%m-%d') AND DATE_FORMAT('$fin', '%Y-%m-%d')")
				->where("$this->table.estado", 1)
				->orderBy("$this->table.fecha DESC")
				->fetchAll();
			$this->response->total = $this->db
				->from($this->table)
				->select(null)->select('COUNT(*) Total')
				->where("DATE_FORMAT($this->table.fecha, '%Y-%m-%d') BETWEEN DATE_FORMAT('$inicio', '%Y-%m-%d') AND DATE_FORMAT('$fin', '%Y-%m-%d')")
				->where("$this->table.estado", 1)
				->fetch()
				->Total;
			return $this->response->SetResponse(true);
		}

        // Obtener salida_produccion por id
		public function get($id) {
			$salida = $this->db
				->from($this->table)
				->select(null)->select("$this->table.id_salida, $this->table.destino as cliente, $this->table.fecha, $this->table.peso_total, CONCAT_WS(' ', CONCAT_WS(' ', DATE_FORMAT($this->table.fecha, '%d-%m-%Y')), SUBSTRING($this->table.fecha, 12, 20)) as fechaf")
				->where('id_salida', $id)
				->fetch();
			if($salida) {
				$this->response->result = $salida;
				$this->response->SetResponse(true);
			}
			else	$this->response->SetResponse(false, 'No existe el registro');
			return $this->response;
		}

        // Eliminar salida_produccion
		public function del($id){
			$set = array('estado' => 2,'fecha_modificacion' => date("Y-m-d H:i:s"));
			$this->response->result = $this->db
				->update($this->table)
				->set($set)
				->where('id_salida', $id)
				->execute();
			if($id!=0){
				return $this->response->SetResponse(true, "Id baja: $id");
			}else{
				return $this->response->SetResponse(true, "Id incorrecto");
			}
		}

        // Agregar salida_produccion
		public function add($data) {
            $data['fecha'] = date('Y-m-d H:i:s');
			$data['fecha_modificacion'] = date('Y-m-d H:i:s');
			try{
				$this->response->result = $this->db
					->insertInto($this->table, $data)
					->execute();
				if($this->response->result) { $this->response->SetResponse(true); }
				else	$this->response->SetResponse(false, 'No se agrego el registro');
			} catch(\PDOException $ex) {
				$this->response->errors = $ex;
				$this->response->SetResponse(false, "catch: Add model salida_produccion $ex");
			}
			return $this->response;
		}

        // Editar salida_produccion
		public function edit($data, $id) {
			$data['fecha_modificacion'] = date('Y-m-d H:i:s');
			try{
				$this->response->result = $this->db
					->update($this->table, $data)
					->where('id_salida', $id)
					->execute();
				if($this->response->result){ 
					return $this->response->SetResponse(true); 
				}else{
					return $this->response->SetResponse(false, 'No se actualizo el registro salida_produccion');
				}
			} catch(\PDOException $ex) {
				$this->response->errors = $ex;
				$this->response->SetResponse(false, "catch: Edit model salida_produccion $ex");
			}
		}

        // Buscar salida_produccion
		public function find($busqueda) {
			$salidas = $this->db
				->from($this->table)
				->select(null)->select("$this->table.id_salida, $this->table.destino as cliente, $this->table.fecha, $this->table.peso_total, CONCAT_WS(' ', CONCAT_WS(' ', DATE_FORMAT($this->table.fecha, '%d-%m-%Y')), SUBSTRING($this->table.fecha, 12, 20)) as fechaf")
				->where("CONCAT_WS(' ', $this->table.id_salida, $this->table.destino, $this->table.fecha, $this->table.peso_total) LIKE '%$busqueda%'")
				->where("$this->table.estado", 1)
				->fetchAll();
			$this->response->result = $salidas;
			$this->response->total = $this->db
				->from($this->table)
				->select(null)->select("COUNT(*) AS total")
				->where("CONCAT_WS(' ', $this->table.id_salida, $this->table.destino, $this->table.fecha, $this->table.peso_total) LIKE '%$busqueda%'")
				->where("estado", 1)
				->fetch()
				->total;
			return $this->response->SetResponse(true);
		} 
/* 
        

        

        

        

        // Obtener reporte por clave de proveedor entre fechas
        public function fk_entrada($terminacion, $field, $fieldAlmacen, $_peso_total, $value) {
            if($field!=''){
                $data['fk_cajero'] = $value;
            }
            if($fieldAlmacen!=''){
                $data['fk_proveedor'] = '429';
                $data['nota_entrada'] = 'COMUNIDAD';
                $data['tipo'] = '1';
                $data['valor'] = '';
            }
			$data['fecha'] = date('Y-m-d H:i:s');
			$data['fecha_modificacion'] = date('Y-m-d H:i:s');
			$data['peso_total'] = $_peso_total;
			try{
				$this->response->result = $this->db
					->insertInto($terminacion, $data)
					->execute();
				if($this->response->result) { 
					$this->response->SetResponse(true); 
				}else{
					$this->response->SetResponse(false, 'No se agrego el registro');
				}
			}catch(\PDOException $ex){
				$this->response->errors = $ex;
				$this->response->SetResponse(false, "catch: Add model entrada $ex");
			}
			return $this->response;
		}

        // Obtener stock_comunidad por kardex, producto, fecha, condicion
        public function stockByKardex($terminacion, $_fk_producto, $fecha, $condicion, $_cajero) {
			$kardexBy = $terminacion=='entrada_tiendita'
				?	'kardex_tiendita'
				:	($terminacion=='entrada_produccion'
					?	'kardex_produccion'
					:	($terminacion=='entrada'
							?	'kardex'
							:	'kardex'));
			return $this->response->result = $this->db->getPdo()->query(
				"SELECT * FROM $kardexBy WHERE fecha='$fecha' AND fk_producto=$_fk_producto$condicion;"
			)->fetchAll();
			
		}

        

		
*/

		// find by field = value
		public function findBy($field, $value){
			$this->response->result = $this->db
				->from($this->table)
				->where($field, $value)
				->where('estado', 1)
				->fetchAll();
			return $this->response->SetResponse(true);
		}
	}
?>
