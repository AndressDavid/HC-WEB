<?php
namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';


class ImportarAS400
{
	protected $oDb;
	protected $aConfig=[];
	public $aErr=[];
	public $cErr='';


	public function __construct($tcCodigo='')
	{
		global $goDb;
		$this->oDb=$goDb;
		if(!empty($tcCodigo)){
			$this->cargarConfig($tcCodigo);
		}
	}

	/*
	 *	Retorna la lista de importaciones activas para el usuario actual
	 */
	public function listaConsultas()
	{
		$laReturn=[];
		$lcUsuario=isset($_SESSION[HCW_NAME]) ? $_SESSION[HCW_NAME]->oUsuario->getUsuario() : '';
		$lbEsAdmin=false;
		$lcPermisos='';

		// Archivos que puede acceder el usuario
		$laPermisos=$this->oDb
			->select('CL3TMA AS LINEA, DE2TMA || OP5TMA AS PERMISOS, OP2TMA AS ESADMIN')
			->from('TABMAE')
			->where("TIPTMA='IMPAS400' AND CL1TMA='USUARIOS' AND CL2TMA='{$lcUsuario}' AND CL3TMA<>'' AND ESTTMA=''")
			->orderBy('CL3TMA')
			->getAll('array');
		if(is_array($laPermisos)){
			if(count($laPermisos)>0){
				$lbEsAdmin=trim($laPermisos[0]['ESADMIN'])=='ADMIN';
				foreach($laPermisos as $laPermiso){
					$lcPermisos .= $laPermiso['PERMISOS'];
				}
				$lcPermisos=trim($lcPermisos);
			}
		}

		if($lbEsAdmin || !empty($lcPermisos)){
			$this->oDb->distinct()
				->select('TRIM(CL2TMA) AS CODIGO, TRIM(DE1TMA) AS TITULO')
				->from('TABMAE')
				->WHERE("TIPTMA='IMPAS400' AND CL1TMA='CONFIG' AND CL3TMA='' AND ESTTMA=''");
			if (!$lbEsAdmin){
				$this->oDb->in('CL2TMA', explode(',',$lcPermisos));
			}
			$laLista=$this->oDb
				->orderBy('TRIM(DE1TMA)')
				->getAll('array');

			if(is_array($laLista)){
				foreach($laLista as $laElemento){
					$laReturn[$laElemento['CODIGO']]=$laElemento['TITULO'];
				}
			}
		}

		return $laReturn;
	}

	/*
	 *	Obtener la configuración almacenada para el tipo de importación
	 */
	public function cargarConfig($tcCodigo='')
	{
		$this->aConfig=[];
		if(!empty($tcCodigo)){
			$laConfig=$this->oDb
				->select('TRIM(OP2TMA) TITULO, CL3TMA LINEA, DE1TMA || DE2TMA || OP5TMA CONFIG')
				->from('TABMAE')
				->where("TIPTMA='IMPAS400' AND CL1TMA='CONFIG' AND CL3TMA<>'' AND ESTTMA=''")
				->where(['CL2TMA'=>$tcCodigo])
				->orderBy('CL3TMA')
				->getAll('array');
			if($this->oDb->numRows()>0){
				$lcTitulo=$lcConfig='';
				foreach($laConfig as $laCon){
					if($lcTitulo!==$laCon['TITULO']){
						$lcConfig=trim($lcConfig);
						if(!empty($lcConfig) && !empty($lcTitulo)){
							$this->aConfig[$lcTitulo]=json_decode($lcConfig,true);
						}
						$lcTitulo=$laCon['TITULO'];
						$lcConfig='';
					}
					$lcConfig.=$laCon['CONFIG'];
				}
				$lcConfig=trim($lcConfig);
				if(!empty($lcConfig) && !empty($lcTitulo)){
					$this->aConfig[$lcTitulo]=json_decode($lcConfig,true);
				}
			}
		}
	}

	/*
	 *	Retorna la configuración consultada
	 */
	public function aConfig()
	{
		return $this->aConfig;
	}

	/*
	 *	Validar los datos
	 */
	public function validar($tcCodigo, $taDatos)
	{
		$this->cErr='';
		$this->aErr=[];
		$this->cargarConfig($tcCodigo);
		$laCnf=$this->aConfig['origen']['campos'];
		$laVal=$this->aConfig['valida'];
		$lcSep=' | ';
		$lcErr='';
		$lbReturn=true;


		// VALIDACIÓN DE ESTRUCTURA DE DATOS

		$lnFila=0;
		foreach($taDatos as $lnKey=>$laDato){
			$this->aErr[$lnFila]='';
			foreach($laCnf as $lcCampo=>$laCampo){
				if(isset($laCampo['regexp'])){
					if(!preg_match('/'.$laCampo['regexp'].'/',$laDato[$lcCampo])){
						$this->aErr[$lnFila].=(empty($this->aErr[$lnFila])?'':$lcSep)."Error estructura en $lcCampo";
						$lbReturn=false;
					}
				}
				if(isset($laCampo['largo'])){
					if(mb_strlen($laDato[$lcCampo])!==$laCampo['largo']){
						$this->aErr[$lnFila].=(empty($this->aErr[$lnFila])?'':$lcSep)."$lcCampo debe tener {$laCampo['largo']} caracteres";
						$lbReturn=false;
					}
				}
				if(isset($laCampo['min'])){
					$lnMin=floatval($laCampo['min']);
					$lnVal=floatval($laDato[$lcCampo]);
					if($lnVal<$lnMin){
						$this->aErr[$lnFila].=(empty($this->aErr[$lnFila])?'':$lcSep)."$lcCampo NO debe ser menor a {$laCampo['min']}";
						$lbReturn=false;
					}
				}
				if(isset($laCampo['max'])){
					$lnMax=floatval($laCampo['max']);
					$lnVal=floatval($laDato[$lcCampo]);
					if($lnVal>$lnMax){
						$this->aErr[$lnFila].=(empty($this->aErr[$lnFila])?'':$lcSep)."$lcCampo NO debe ser mayor que {$laCampo['max']}";
						$lbReturn=false;
					}
				}
			}
			$lnFila++;
		}



		// VALIDACIÓN EN LA BASE DE DATOS

		// Obtiene los datos del primer registro
		$laPrimer = $taDatos[0];

		// Valida sentencias SQL a nivel general usando $laPrimer
		if(isset($laVal['sql'])){
			foreach($laVal['sql'] as $lasql){
				if(!$this->validasql($lasql['sql'], $lasql['msg'], $laPrimer)){
					$this->aErr[0].=(empty($this->aErr[$lnFila])?'':$lcSep).$this->cErr;
					$this->cErr='';
					$lbReturn=false;
				}
			}
		}

		// Campos que deben ser iguales en todos los registros
		$lbIgual = count($laVal['igual'] ?? []) > 0;

		// Validaciones a nivel de línea
		$lnFila=0;
		foreach($taDatos as $lnKey=>$laDato){

			// Validar campos que deben ser iguales en todos los registros
			if($lbIgual){
				foreach($laVal['igual'] as $lcCampo){
					if($laPrimer[$lcCampo]!==$laDato[$lcCampo]){
						$this->aErr[$lnFila].=(empty($this->aErr[$lnFila])?'':$lcSep)."Valor en $lcCampo diferente";
						$lbReturn=false;
					}
				}
			}
			// Valida sentencias SQL
			if(isset($laVal['sqlf'])){
				foreach($laVal['sqlf'] as $lasql){
					if(!$this->validasql($lasql['sql'], $lasql['msg'], $laDato)){
						$this->aErr[$lnFila].=(empty($this->aErr[$lnFila])?'':$lcSep).$this->cErr;
						$this->cErr='';
						$lbReturn=false;
					}
				}
			}
			$lnFila++;
		}

		return $lbReturn;
	}

	/*
	 *	Valida que sentencia sql retorne uno o más registros
	 */
	public function validasql($tcSql, $tcMsg, $taDatos)
	{
		$laBindValues = [];
		foreach($taDatos as $lcCampo=>$lcValor){
			if(!(mb_strpos($tcSql, ':'.$lcCampo)===false)){
				$laBindValues[':'.$lcCampo] = $lcValor;
			}
			$tcMsg = str_replace("<<$lcCampo>>", $lcValor, $tcMsg);
		}
		$laRta = $this->oDb->query($tcSql, $laBindValues, true);
		if($this->oDb->numRows()==0){
			$this->cErr = $tcMsg;
			return false;
		} else {
			return true;
		}
	}

	/*
	 *	Importar los datos
	 */
	public function importar($taDatos)
	{
		$laReturn = '';
		if (count($this->aConfig)==0) {
			$this->cErr = 'No se cargó la configuración.';
			return $laReturn;
		}

		$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
		$laLog = [
			'USU' => $_SESSION[HCW_NAME]->oUsuario->getUsuario(),
			'PRG' => 'IMPAS400',
			'FEC' => $ltAhora->format('Ymd'),
			'HOR' => $ltAhora->format('His'),
		];
		$lcTabla = $this->aConfig['destino']['tabla'];
		$lnRiaCon = $this->aConfig['destino']['riacon'];
		$lnConsec = $this->oDb->obtenerConsecRiacon($lnRiaCon, $laLog['PRG'], 20, $laLog['PRG']);

		// Insertar datos en la tabla destino
		$lnProceso = count($taDatos);
		$lnImporta = 0;
		foreach($taDatos as $lnKey=>$laDato){
			$laDatIns = [];
			$laInsert = [];
			foreach($this->aConfig['destino']['campos'] as $lcCampo=>$laCampo){
				$luValor = '';
				switch($laCampo['tipo']){
					case 'RIACON':
						$laDatIns[$lcCampo] = $lnConsec;
						break;
					case 'DATA':
						$laDatIns[$lcCampo] = trim($laDato[$laCampo['valor']]);
						break;
					case 'VALOR':
						$laDatIns[$lcCampo] = $laCampo['valor'];
						break;
					case 'LOG':
						$laDatIns[$lcCampo] = $laLog[$laCampo['valor']];
						break;
				}
			}
			$lbRta = $this->oDb->from($lcTabla)->insertar($laDatIns);
			if($lbRta){ $lnImporta++; }
		}

		if($lnProceso==$lnImporta){

			// Ejecutar store procedure
			if (isset($this->aConfig['proceso'])) {
				$lcStProc = $this->aConfig['proceso']['nombre'];
				$laParamIn = $laParamOut = [];
				$lbParamIn = $lbParamOut = false;

				// Parámetros de entrada
				if (isset($this->aConfig['proceso']['param_in'])) {
					foreach($this->aConfig['proceso']['param_in'] as $lcPar=>$lcCampo){
						$laParamIn[$lcPar] = [$laDatIns[$lcCampo], \PDO::PARAM_STR];
					}
				}
				// Parámetros de salida
				if(count($laParamIn)==0) {$laParamIn=null;}
				if (isset($this->aConfig['proceso']['param_out'])) {
					foreach($this->aConfig['proceso']['param_out'] as $lcPar=>$luValor){
						$laParamOut[$lcPar] = [\PDO::PARAM_STR, $luValor];
					}
				}
				if(count($laParamOut)==0) {$laParamOut=null;}

				// Llamada al procedimiento
				$lbRta = $this->oDb->storedProcedure($lcStProc, $laParamIn, $laParamOut);

				// Evaluación de la respuesta
				if ($lbRta) {
					if (isset($this->aConfig['proceso']['exito'])) {
						$lbExito = true;
						foreach($this->aConfig['proceso']['exito'] as $lcPar=>$luValor){
							$lbExito = $lbExito && $lbRta[$lcPar]==$luValor;
						}
						if($lbExito){
							$laReturn = "Proceso realizado con éxito.<br>Se procesaron $lnImporta registros.";
						}else{
							$laReturn = 'Proceso NO fue exitoso';
							$this->cErr = $laReturn;
						}
					} else {
						$laReturn = 'Proceso realizado';
					}
				} else {
					$laReturn = 'Proceso NO realizado';
					$this->cErr = $laReturn;
				}

			} else {
				$laReturn = "Proceso realizado con éxito.<br>Se procesaron $lnImporta registros.";
			}

		}else{
			$laReturn = "Solo se pudieron insertar $lnImporta registros en la tabla $lcTabla.";
			if (isset($this->aConfig['proceso'])) { $laReturn.='<br>No se finalizó el proceso.'; }
			$this->cErr = $laReturn;
		}

		return $laReturn;
	}

}