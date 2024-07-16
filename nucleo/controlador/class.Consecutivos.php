<?php
namespace NUCLEO;

class Consecutivos
{
	protected $cFecCre = '';
	protected $cHorCre = '';
	protected $cUsuCre = '';
	protected $cRegMed = '';
	protected $cEspecialidad = '';

	public static function fCalcularConsecutivoConsulta($taIngreso=[], $tcPrgCre='')
	{
		global $goDb;
		$lcTipId  = $taIngreso['cTipId'];
		$lnNumId  = $taIngreso['nNumId'];
		$lnIngreso= $taIngreso['nIngreso'];
		$lnConsec = 0;

		$laDatIni = self::IniciaDatos();

		$laConsecutivo = $goDb->max('CCOPAC', 'MAXIMO')->from('RIAPACL02')->where(['TIDPAC'=>$lcTipId,'NIDPAC'=>$lnNumId,])->get('array');

		if(is_array($laConsecutivo)){
			if(count($laConsecutivo)>0){
				$lnConsec = $laConsecutivo['MAXIMO']; settype($lnConsec,'integer');
			}
		}
		unset($laConsecutivo);
		$lnConsec = $lnConsec+1;

		//ACTULIZA EL CONSECUTIVO DE CONSULTA EN LA TABLA RIAPAC
		$lcTabla = 'RIAPACL02';
		$laDatos = [
			'CCOPAC'=>$lnConsec,
			'UMOPAC'=>$laDatIni['cUsuCre'],
			'PMOPAC'=>$tcPrgCre,
			'FMOPAC'=>$laDatIni['cFecCre'],
			'HMOPAC'=>$laDatIni['cHorCre'],
		];
		$llResultado = $goDb->tabla($lcTabla)->where(['TIDPAC'=>$lcTipId,'NIDPAC'=>$lnNumId,])->actualizar($laDatos);

		return $lnConsec;
	}


	/*
	 *	Obtiene y retorna el consecutivo de cita para el paciente
	 *	@param array $taIngreso: arreglo con los elementos cTipId y nNumId, tipo y número de documento del paciente correspondientemente
	 *	@param string $tcPrgCre: programa que crea o mofica
	 *	@param array $taDatIni: arreglo con los elementos cUsuCre, cFecCre y cHorCre
	 *	@return integer número de consecutivo de cita
	 */
	public static function fCalcularConsecutivoCita($taIngreso=[], $tcPrgCre='', $taDatIni=null)
	{
		global $goDb;
		$laDatIni = $taDatIni ?? self::IniciaDatos();

		$lcTipId  = $taIngreso['cTipId'];
		$lnNumId  = $taIngreso['nNumId'];
		$laWherePac = [
			'TIDPAC'=>$lcTipId,
			'NIDPAC'=>$lnNumId,
		];
		$lnConsec = 0;
		$lnExpression = 1;

		while ($lnExpression==1){
			//	Busca consecutivo cita en archivo maestro del paciente
			$laConsecutivo = $goDb
				->select('CCIPAC')
				->from('RIAPAC')
				->where($laWherePac)
				->get('array');
			$lnConsec = $laConsecutivo['CCIPAC'] ?? '0'; settype($lnConsec,'integer');
			$lnConsec = $lnConsec+1;

			if(is_array($laConsecutivo)){
				if(count($laConsecutivo)>0){
					// Actualiza el consecutivo de cita en la tabla RIAPAC
					$laDatos = [
						'CCIPAC'=>$lnConsec,
						'UMOPAC'=>$laDatIni['cUsuCre'],
						'PMOPAC'=>$tcPrgCre,
						'FMOPAC'=>$laDatIni['cFecCre'],
						'HMOPAC'=>$laDatIni['cHorCre'],
					];
					$llResultado = $goDb->from('RIAPAC')->where($laWherePac)->actualizar($laDatos);
				} else {
					// Inserta el registro en la tabla RIAPAC
					$laDatos = array_merge($laWherePac, [
						'CCIPAC'=>$lnConsec,
						'USRPAC'=>$laDatIni['cUsuCre'],
						'PGMPAC'=>$tcPrgCre,
						'FECPAC'=>$laDatIni['cFecCre'],
						'HORPAC'=>$laDatIni['cHorCre'],
					]);
					$llResultado = $goDb->from('RIAPAC')->insertar($laDatos);
				}
			}

			$laRiaCit = $goDb
				->select('CCICIT')
				->from('RIACIT')
				->where([
					'TIDCIT'=>$lcTipId,
					'NIDCIT'=>$lnNumId,
					'CCICIT'=>$lnConsec,
				])
				->getAll('array');
			if($goDb->numRows()==0){
				$laRiaOrd = $goDb
					->select('CCIORD')
					->from('RIAORD')
					->where([
						'TIDORD'=>$lcTipId,
						'NIDORD'=>$lnNumId,
						'CCIORD'=>$lnConsec,
					])
					->getAll('array');
				if($goDb->numRows()==0){
					$lnExpression = 0;
				}
			}
		}
		unset($laConsecutivo,$laRiaCit,$laRiaOrd);
		return $lnConsec;
	}

	public static function fCalcularConsecutivoEPI($tnIngreso=0, $tlEstado=false)
	{
		global $goDb;
		$lnConsec = 0;
		$laCondiciones = ['NINEPH'=>$tnIngreso,];
		$laLista=['EPIPPAL','EPIPPALWEB'];
		if($tlEstado){
			$laCondiciones['ESTEPH']=3;
		}

		$laConsecutivo = $goDb->max('CCNEPH', 'MAXIMO')->from('RIAEPH')->where($laCondiciones)->in('PGMEPH',$laLista)->get('array');
		if(is_array($laConsecutivo)){
			if(count($laConsecutivo)>0){
				$lnConsec = $laConsecutivo['MAXIMO']; settype($lnConsec,'integer');
			}
		}
		unset($laConsecutivo);
		return $lnConsec;
	}
	
	public static function fCalcularConsecutivoCieHap($tnIngreso=0)
	{
		global $goDb;
		$lnConsec=0;
		$laConsecutivo = $goDb->max('CONHAP', 'MAXIMO')->from('CIEHAP')->get('array');

		if(is_array($laConsecutivo)){
			if(count($laConsecutivo)>0){
				$lnConsec=$laConsecutivo['MAXIMO']+1; 
				settype($lnConsec,'integer');
			}else{
				$lnConsec=1;
			}
		}else{
			$lnConsec=1;
		}
		unset($laConsecutivo);
		return $lnConsec;
	}

	public static function fCalcularConsecutivoOxigeno($tnIngreso=0)
	{
		global $goDb;
		$lnConsecutivo = 0;
		$laDatIni = self::IniciaDatos();
		$laCondiciones = ['INGFAR'=>$tnIngreso,];
		$laConsecutivo = $goDb->select('CDNFAR, FECFAR')->from('RIAFARM')->where($laCondiciones)->orderBy('CDNFAR DESC')->get('array');

		if(is_array($laConsecutivo)){
			if(count($laConsecutivo)>0){
				if ($laConsecutivo['FECFAR'] == $laDatIni['cFecCre']){
					$lnConsecutivo = $laConsecutivo['CDNFAR'];
				}else{
					$lnConsecutivo = $laConsecutivo['CDNFAR'] + 1;
				}
			}else{
				$lnConsecutivo = 1;
			}
		}
		unset($laConsecutivo);
		return $lnConsecutivo;
	}

	public static function fCalcularConsecutivoMipres($tnIngreso=0)
	{
		global $goDb;
		$lnConsecutivo = 0;
		$laCondiciones = ['INGNME'=>$tnIngreso,];

		$laConsecutivo = $goDb->max('CNSMNE', 'MAXIMO')->from('NPSMPEP')->where($laCondiciones)->get('array');

		if(is_array($laConsecutivo)){
			if(count($laConsecutivo)>0){
				$lnConsecutivo = $laConsecutivo['MAXIMO']; settype($lnConsec,'integer');
			}
		}
		$lnConsecutivo = $lnConsecutivo+1;
		unset($laConsecutivo);
		return $lnConsecutivo;
	}

	public static function fCalcularConsecutivoEstudiante($tnIngreso=0)
	{
		global $goDb;
		$lnConsecutivo = 0;
		$laCondiciones = ['INGRIC'=>$tnIngreso,];

		$laConsecutivo = $goDb->max('CONRIC', 'MAXIMO')->from('REINCA')->where($laCondiciones)->get('array');

		if(is_array($laConsecutivo)){
			if(count($laConsecutivo)>0){
				$lnConsecutivo = $laConsecutivo['MAXIMO']; settype($lnConsec,'integer');
			}
		}
		$lnConsecutivo = $lnConsecutivo+1;
		unset($laConsecutivo);
		return $lnConsecutivo;
	}

	public static function fCalcularConsecutivoAgendaSalas()
	{
		global $goDb;
		$lnConsecutivo = 0;
		$laConsecutivo = $goDb->max('CONSAL', 'MAXIMO')->from('AGESAL')->get('array');

		if(is_array($laConsecutivo)){
			if(count($laConsecutivo)>0){
				$lnConsecutivo = $laConsecutivo['MAXIMO']; settype($lnConsecutivo,'integer');
			}
		}
		$lnConsecutivo = $lnConsecutivo+1;
		unset($laConsecutivo);
		return $lnConsecutivo;
	}

	public static function fCalcularConsecutivoReagendarSalas($tnConsecutivo=0,$tnIndice=0)
	{
		global $goDb;
		$lnConsecutivo = 0;
		$laCondiciones = ['CONSAD'=>$tnConsecutivo,'INDSAD'=>$tnIndice,];
		$laConsecutivo = $goDb->max('LINSAD', 'MAXIMO')->from('AGESAD')->where($laCondiciones)->get('array');

		if(is_array($laConsecutivo)){
			if(count($laConsecutivo)>0){
				$lnConsecutivo = $laConsecutivo['MAXIMO']; settype($lnConsecutivo,'integer');
			}
		}
		$lnConsecutivo = $lnConsecutivo+1;
		unset($laConsecutivo);
		return $lnConsecutivo;
	}

	public static function fCalcularConsecutivoAnestesiologoSalas($tnConsecutivo=0,$tnIndice=0)
	{
		global $goDb;
		$lnConsecutivo = 0;
		$laCondiciones = ['CONSAD'=>$tnConsecutivo,'INDSAD'=>$tnIndice,];
		$laConsecutivo = $goDb->max('LINSAD', 'MAXIMO')->from('AGESAD')->where($laCondiciones)->get('array');

		if(is_array($laConsecutivo)){
			if(count($laConsecutivo)>0){
				$lnConsecutivo = $laConsecutivo['MAXIMO']; settype($lnConsecutivo,'integer');
			}
		}
		$lnConsecutivo = $lnConsecutivo+1;
		unset($laConsecutivo);
		return $lnConsecutivo;
	}

	public static function fCalcularConsecutivoCirujanoSalas($tnConsecutivo=0,$tnIndice=0)
	{
		global $goDb;
		$lnConsecutivo = 0;
		$laCondiciones = ['CONSAD'=>$tnConsecutivo,'INDSAD'=>$tnIndice,];
		$laConsecutivo = $goDb->max('LINSAD', 'MAXIMO')->from('AGESAD')->where($laCondiciones)->get('array');

		if(is_array($laConsecutivo)){
			if(count($laConsecutivo)>0){
				$lnConsecutivo = $laConsecutivo['MAXIMO']; settype($lnConsecutivo,'integer');
			}
		}
		$lnConsecutivo = $lnConsecutivo+1;
		unset($laConsecutivo);
		return $lnConsecutivo;
	}

	public static function fCalcularConsecutivoOrden($tcTipoide='', $tnNroidenti=0)
	{
		global $goDb;
		$lcTipId  = $tcTipoide ;
		$lnNumId  = $tnNroidenti ;
		$lnConsec = 0 ;
		$laConsecutivo =$goDb->max('CORORA', 'MAXIMO')->from('ORDAMB')->where(['TIDORA'=>$lcTipId,'NIDORA'=>$lnNumId,])->get("array");

		if(is_array($laConsecutivo)){
			if(count($laConsecutivo)>0){
				$lnConsec = $laConsecutivo['MAXIMO']; settype($lnConsec,'integer');
			}
		}
		unset($laConsecutivo);
		$lnConsec = $lnConsec+1;
		return $lnConsec;
	}

	/*
	 *	Llama al procedimiento almacenado FACGA010CP para obtener el consecutivo de evolución para un ingreso
	 *	@param array $taData: datos para cobro. Array con los siguientes elementos:
 	 *		ingreso		Integer - Número de ingreso del paciente
 	 *		seccion		String - Sección del paciente, 2 caracteres
 	 *		cama		String - Cama del paciente, 3 caracteres
 	 *		usuario		String - Usuario que llama
 	 *		programa	String - Programa que llama
 	 *		estado		Integer - Estado
 	 *	@return el siguiente consecutivo de evolución. Si no puede recuperarlo retorna false.
	 */
	public function obtenerConsecEvolucion($taData=[])
	{
		global $goDb;
		// parámetros entrada
		$laParamEntrada = [
			'ingreso'	=> [intval($taData['ingreso']), \PDO::PARAM_INT],
			'seccion'	=> [$taData['seccion'], \PDO::PARAM_STR],
			'cama'		=> [$taData['cama'], \PDO::PARAM_STR],
			'usuario'	=> [substr($taData['usuario'],0,10), \PDO::PARAM_STR],
			'programa'	=> [substr($taData['programa'],0,10), \PDO::PARAM_STR],
			'estado'	=> [intval($taData['estado']??'8'), \PDO::PARAM_INT],
		];
		// parámetros salida
		$laParamSalida = [
			'cnsEvo'	=> [\PDO::PARAM_STR, 9],
		];

		$lnNum=0;
		$lnNumMax=$goDb->obtenerTabMae1('OP3TMA','EVOLUC',['CL1TMA'=>'CNSINT'],null,10);
		do {
			$laRetorno = $goDb->storedProcedure('FACGA010CP', $laParamEntrada, $laParamSalida);
			$lnNum++;
		}while(!($laRetorno['cnsEvo']>0) && $lnNum<$lnNumMax);

		return $laRetorno['cnsEvo']>0 ? $laRetorno['cnsEvo']: false;
	}

	public static function fConsecutivoUltimaFormula($tnIngreso=0)
	{
		global $goDb;
		$lnIngreso= $tnIngreso;
		$lnConsec = 0;
		$laConsecutivo = $goDb->max('CEVFRD', 'MAXIMO')->from('RIAFARD')->where(['NINFRD'=>$lnIngreso])->get('array');

		if(is_array($laConsecutivo)){
			if(count($laConsecutivo)>0){
				$lnConsec = $laConsecutivo['MAXIMO']; settype($lnConsec,'integer');
			}
		}
		unset($laConsecutivo);
		return $lnConsec ;
	}

	public static function IniciaDatos()
	{
		global $goDb;
		$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
		return [
			'cFecCre' => $ltAhora->format('Ymd'),
			'cHorCre' => $ltAhora->format('His'),
			'cUsuCre' => (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getUsuario():''),
			'cEspecialidad' => (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getEspecialidad():''),
			'cRegMed' => (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getRegistro():''),
		];
	}

	public static function fCalcularConsecutivoAntibiotico($tnIngreso=0)
	{
		global $goDb;
		$lnConsec = 0;
		$laCondiciones = ['INGANT'=>$tnIngreso,];

		$laConsecutivo = $goDb->max('CNOANT', 'MAXIMO')->from('USOANT')->where($laCondiciones)->get('array');

		if(is_array($laConsecutivo)){
			if(count($laConsecutivo)>0){
				$lnConsec = $laConsecutivo['MAXIMO']; settype($lnConsec,'integer');
			}
		}
		unset($laConsecutivo);
		$lnConsec = $lnConsec+1;
		return $lnConsec;
	}
	
	public static function fCalcularCensoUrgencias($tnIngreso=0,$tnIndice=0)
	{
		global $goDb;
		$lnConsecutivo = 0;
		$laCondiciones = ['INGURD'=>$tnIngreso,'IN1URD'=>$tnIndice,];

		$laConsecutivo = $goDb->max('CONURD', 'MAXIMO')->from('CENURD')->where($laCondiciones)->get('array');

		if(is_array($laConsecutivo)){
			if(count($laConsecutivo)>0){
				$lnConsecutivo = $laConsecutivo['MAXIMO']; settype($lnConsec,'integer');
			}
		}
		$lnConsecutivo = $lnConsecutivo+1;
		unset($laConsecutivo);
		return $lnConsecutivo;
	}
	
	public static function fCalcularTrasladoPacientes($tnIngreso=0)
	{
		global $goDb;
		$lnConsecutivo = 0;
		$laCondiciones = ['INGTRA'=>$tnIngreso];

		$laConsecutivo = $goDb->max('CONTRA', 'MAXIMO')->from('TRAPAC')->where($laCondiciones)->get('array');

		if(is_array($laConsecutivo)){
			if(count($laConsecutivo)>0){
				$lnConsecutivo = $laConsecutivo['MAXIMO']; settype($lnConsec,'integer');
			}
		}
		$lnConsecutivo = $lnConsecutivo+1;
		unset($laConsecutivo);
		return $lnConsecutivo;
	}
	
	public static function fCalcularRipsResolucion()
	{
		global $goDb;
		$lnConsecutivo = 0;
		$laConsecutivo = $goDb->max('IDERIP', 'MAXIMO')->from('RIPRES')->get('array');

		if(is_array($laConsecutivo)){
			if(count($laConsecutivo)>0){
				$lnConsecutivo = $laConsecutivo['MAXIMO']; settype($lnConsec,'integer');
			}
		}
		$lnConsecutivo = $lnConsecutivo+1;
		unset($laConsecutivo);
		return $lnConsecutivo;
	}

	public static function fCalcularRipsCabecera()
	{
		global $goDb;
		$lnConsecutivo = 0;
		$laConsecutivo = $goDb->max('IDERIC', 'MAXIMO')->from('RIPREC')->get('array');

		if(is_array($laConsecutivo)){
			if(count($laConsecutivo)>0){
				$lnConsecutivo = $laConsecutivo['MAXIMO']; settype($lnConsec,'integer');
			}
		}
		$lnConsecutivo = $lnConsecutivo+1;
		unset($laConsecutivo);
		return $lnConsecutivo;
	}

	public static function fCalcularSeguimientoInterconsulta($tnIngreso=0,$tnConsecCita=0)
	{
		global $goDb;				
		$lnConsecutivo = 0;
		$laWhere = [
			'INGINT'=>$tnIngreso,
			'CCIINT'=>$tnConsecCita
		];
		$laConsecutivo = $goDb->max('CONINT', 'MAXIMO')->from('INTSEGL01')->where($laWhere)->get('array');
		if(is_array($laConsecutivo)){
			if(count($laConsecutivo)>0){
				$lnConsecutivo = $laConsecutivo['MAXIMO']; settype($lnConsecutivo,'integer');
			}
		}
		$lnConsecutivo=$lnConsecutivo+1;
		unset($laConsecutivo);
		return $lnConsecutivo;
	}

	public static function fCalcularInfectologia($paIngreso){
		global $goDb;
		$lnConsecutivo = 0;


		$laConsecutivo = $goDb->max('CONINC', 'MAXIMO')->from('INFECAL01')->where(['INGINC'=>$paIngreso])->get('array');
		if(is_array($laConsecutivo)){
			if(count($laConsecutivo)>0){
				$lnConsecutivo = $laConsecutivo['MAXIMO']; settype($lnConsec,'integer');
				$lnConsecutivo ++;
			}
		}
		unset($laConsecutivo);

		return $lnConsecutivo;
	}
	
	public static function fCalcularGrabarProcedimentos()
	{
		global $goDb;
		$lnConsecutivo = 0;
		$laConsecutivo = $goDb->max('CONCGR', 'MAXIMO')->from('CUPGRA')->get('array');

		if(is_array($laConsecutivo)){
			if(count($laConsecutivo)>0){
				$lnConsecutivo = $laConsecutivo['MAXIMO']; settype($lnConsec,'integer');
			}
		}
		$lnConsecutivo = $lnConsecutivo+1;
		unset($laConsecutivo);
		return $lnConsecutivo;
	}

	public static function fCalcularConsecutivoOrdenProcedimientos($tnCodigoConsecutivo=0,$tcProgramaCreacion='')
	{
		$llAsignado = false;
		$lnConsecutivo = 0;

		global $goDb;
		if(isset($goDb)){
			while($llAsignado==false){
				$lnConsecutivo = $goDb->obtenerConsecRiacon($tnCodigoConsecutivo, $tcProgramaCreacion);
				$llAsignado = ($lnConsecutivo>0);
			}
		}
		return $lnConsecutivo;
	}
	
}
