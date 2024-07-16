<?php

namespace NUCLEO;
require_once __DIR__ .'/class.Db.php';
require_once __DIR__ . '/class.Consecutivos.php';

use NUCLEO\Db;

class TrasladosPacientes{

	protected $oDb = null;
	protected $cUsuCre='';
	protected $cPrgCre='';
	protected $cFecCre='';
	protected $cHorCre='';

	public $aError = [
		'Mensaje' => "",
		'Objeto' => "",
		'Valido' => true,
	];

	public function __construct(){
		global $goDb;
		$this->oDb = $goDb;
	}

	function verificarTraslado($taDatos=[])
	{
		$this->aError = $this->validacion($taDatos);
		return $this->aError;
	}
	
	public function consultaRegistrosTraslado($tnIngreso=0)
	{
		$lnIngreso=isset($tnIngreso) ? intval($tnIngreso):0;
		$lcReturn = '';

		$laDatosTraslado = $this->oDb
			->select('A.CONTRA CONSECUTIVO, A.CLNTRA LINEA, TRIM(A.DESTRA) DESCRIPCION, A.USRTRA USUARIO, A.FECTRA FECHA, A.HORTRA HORA')
			->select('TRIM(B.NNOMED)||\' \'||TRIM(B.NOMMED) NOMBREMEDICO')
			->select('(SELECT TRIM(C.DE1TMA) FROM TABMAE AS C WHERE C.TIPTMA=\'CNDSGR\' AND C.CL1TMA=A.ARTTRA AND C.ESTTMA=\' \' ) AS AREA')
			->select('(SELECT TRIM(D.DESESP) FROM RIAESPE AS D WHERE D.CODESP=A.ESPTRA) AS ESPECIALIDAD')
			->select('(SELECT UPPER(TRIM(E.NOMMED)||\' \'||TRIM(E.NNOMED)) FROM RIARGMN AS E WHERE E.REGMED=A.MRETRA) AS MEDICO_RECIBE')
			->select('A.SIFTRA INFFAMILIAR, A.STFTRA TRASFAMILIAR, A.SIGTRA SIGNOS, A.SECTRA HABITACION')
			->from('TRAPAC AS A')
			->leftJoin('RIARGMN AS B', "TRIM(A.USRTRA)=TRIM(B.USUARI)", null)
			->where('A.INGTRA', '=', $lnIngreso)
			->orderBy('A.CONTRA DESC, A.CLNTRA')
			->getAll('array');
		if($this->oDb->numRows()>0){
			$laConsecutivo = $laDatosTraslado[0]['CONSECUTIVO'];
			$lnNumConsec=-1;
			foreach($laDatosTraslado as $laClave=>$laTraslado)
			{
				$laDatosSignos=explode('~', $laTraslado['SIGNOS']);
				$lcDescEscalaDolor=$lcDescNivelConciencia='';
				$lcNivelConciencia=trim($laDatosSignos[6]);
				$laDescNivelConciencia = $this->oDb->select('trim(DESENF) DESCRIPCION')->from('TABENF')
				->where('TIPENF', '=', '4')->where('VARENF', '=', '5')->where('ACTENF', '<>', '1')->where('REFENF', '=', $lcNivelConciencia)->get('array');
				$lcDescNivelConciencia=isset($laDescNivelConciencia['DESCRIPCION'])?trim($laDescNivelConciencia['DESCRIPCION']):'';
				$lcPuntajeNews=!empty($laDatosSignos[8])?('Puntaje NEWS: ' .(trim($laDatosSignos[8])).' puntos'):'';
				$lcCodigoEscalaDolor=trim($laDatosSignos[9]);
				
				if (!empty($lcCodigoEscalaDolor)){
					$laDescEscalaDolor = $this->oDb->select('trim(DESENF) DESCRIPCION')->from('TABENF')
					->where('TIPENF', '=', '14')->where('VARENF', '=', '1')->where('REFENF', '=', $lcCodigoEscalaDolor)->get('array');
					$lcDescEscalaDolor=isset($laDescEscalaDolor['DESCRIPCION'])?trim($laDescEscalaDolor['DESCRIPCION']):'';				
				}
				$lcDesc = $laTraslado['CONSECUTIVO'] .'- ' .$laTraslado['NOMBREMEDICO'].'  -  '
						.AplicacionFunciones::formatFechaHora('fechahora12', $laTraslado['FECHA'].' '.$laTraslado['HORA'])
						.'  -  '.'Habitación: ' .$laTraslado['HABITACION'] 
						.PHP_EOL.'Área a trasladar: '.trim($laTraslado['AREA'])
						.PHP_EOL.'Especialidad a trasladar: '.trim($laTraslado['ESPECIALIDAD'])
						.PHP_EOL.'Médico recibe: '.trim($laTraslado['MEDICO_RECIBE'])
						.PHP_EOL.'Se informa a familiar de traslado: '.($laTraslado['INFFAMILIAR']=='S'?'Si':'No')
						.PHP_EOL.'Se traslada en compañia de familiar: '.($laTraslado['TRASFAMILIAR']=='S'?'Si':'No')
						.PHP_EOL.'Signos vitales: ' 
						.'FR: '.$laDatosSignos[0]
						.'  /  SO2: '.$laDatosSignos[1]
						.'  /  T: '.$laDatosSignos[2]
						.'  /  TAS: '.$laDatosSignos[3]
						.'  /  TAD: '.$laDatosSignos[4]
						.'  /  FC: '.$laDatosSignos[5]
						.PHP_EOL.'Nivel de conciencia: '.$lcDescNivelConciencia
						.PHP_EOL.'Necesita O2 suplementario: '.($laDatosSignos[7]=='1'?'Si':'No')
						.(!empty($lcPuntajeNews)?(PHP_EOL.$lcPuntajeNews):'')
						.(!empty($lcDescEscalaDolor)?(PHP_EOL.'Escala dolor: '.$lcDescEscalaDolor):'')
						.PHP_EOL.'Justificación de traslado: '.trim($laTraslado['DESCRIPCION']);
				if ($lnNumConsec==$laTraslado['CONSECUTIVO']){
					$lcReturn .= trim($lcDesc, '');
				} else{
					$lcReturn = empty($lcReturn)?'':trim($lcReturn,' ').PHP_EOL.PHP_EOL;
					$lcReturn .= $lcDesc;
				}
			}
			$lcReturn = trim($lcReturn);
		}
		return $lcReturn ;
	}
	
	public function validacion($taDatos=[])
	{
		$lbRevisar = true;
		if ($taDatos!='' && is_array($taDatos)){
			$lnIngreso=isset($taDatos['ingreso']) ? $taDatos['ingreso']:'';
			$laDatos=isset($taDatos['datos']) ? $taDatos['datos']:'';
			$lcAreaTrasladar=isset($laDatos['AreaTrasladarTP']) ? $laDatos['AreaTrasladarTP']:'';
			$lcEspecialidadTrasladar=isset($laDatos['EspecialidadTrasladarTP']) ? $laDatos['EspecialidadTrasladarTP']:'';
			$lcMedicoTrasladar=isset($laDatos['MedicoTrasladaTP']) ? $laDatos['MedicoTrasladaTP']:'';
			$lcSeInformaFamiliar=isset($laDatos['SeInformaFamiliarTP']) ? $laDatos['SeInformaFamiliarTP']:'';
			$lcSeTrasladaFamiliar=isset($laDatos['SetrasladaFamiliarTP']) ? $laDatos['SetrasladaFamiliarTP']:'';
			$lcFrecuenciaRespitaria=isset($laDatos['fr']) ? $laDatos['fr']:'';
			$lcSaturacion=isset($laDatos['so2']) ? $laDatos['so2']:'';
			$lcTemperatura=isset($laDatos['t']) ? $laDatos['t']:'';
			$lcPresionArterialSistolica=isset($laDatos['tas']) ? $laDatos['tas']:'';
			$lcPresionArterialDiastolica=isset($laDatos['tad']) ? $laDatos['tad']:'';
			$lcFrecuenciaCardiaca=isset($laDatos['fc']) ? $laDatos['fc']:'';
			$lcNivelConciencia=isset($laDatos['nc']) ? trim($laDatos['nc']):'';
			$lcNecesitaOxigeno=isset($laDatos['o2sp']) ? $laDatos['o2sp']:'';
			$lcEscalaDolor=isset($laDatos['EscalaDolorTr']) ? trim($laDatos['EscalaDolorTr']):'';
			$lcDescripcion=isset($laDatos['RegistrarTraslado']) ? $laDatos['RegistrarTraslado']:'';
			
			if ($lbRevisar){
				if (empty($lnIngreso)){
					$this->aError = [
						'Mensaje' =>'No existe inreso paciente.',
						'Objeto'  => 'selAreaTrasladarTP',
						'Valido'=>false,
					];
					$lbRevisar = false;
				}
			}
			
			if ($lbRevisar){
				if (empty($lcAreaTrasladar)){
					$this->aError = [
						'Mensaje' =>'No existe área trasladar.',
						'Objeto'  => 'selAreaTrasladarTP',
						'Valido'=>false,
					];
					$lbRevisar = false;
				}
			}
			
			if ($lbRevisar){
				if (!empty($lcAreaTrasladar)){
					$lcValidaAreaTrasladar=trim($this->oDb->obtenerTabmae1('DE1TMA', 'CNDSGR', "CL1TMA='$lcAreaTrasladar' AND ESTTMA=''", null, ''));
					if (empty($lcValidaAreaTrasladar)) {
						$this->aError = [
							'Mensaje' =>'No existe descripción área trasladar',
							'Objeto'  => 'selAreaTrasladarTP',
							'Valido'=>false,
						];
						$lbRevisar = false;
					}	
				}
			}
			
			if ($lbRevisar){
				if (empty($lcEspecialidadTrasladar)){
					$this->aError = [
						'Mensaje' =>'No existe especialidad trasladar',
						'Objeto'  => 'selEspecialidadTrasladarTP',
						'Valido'=>false,
					];
					$lbRevisar = false;
				}
			}
			
			if ($lbRevisar){
				if (!empty($lcEspecialidadTrasladar)){
					$laTemp = $this->oDb
						->from('RIAESPE')
						->where(['CODESP'=>$lcEspecialidadTrasladar])
						->get('array');
					if ($this->oDb->numRows()==0){					
						$this->aError = [
							'Mensaje' =>'No existe descripción especialidad trasladar',
							'Objeto'  => 'selEspecialidadTrasladarTP',
							'Valido'=>false,
						];
						$lbRevisar = false;
					}
					unset($laTemp);
				}
			}
			
			if ($lbRevisar){
				if (empty($lcMedicoTrasladar)){
					$this->aError = [
						'Mensaje' =>'No existe médico quien recibe',
						'Objeto'  => 'selMedicoTrasladaTP',
						'Valido'=>false,
					];
					$lbRevisar = false;
				}
			}
			
			if ($lbRevisar){
				if (!empty($lcMedicoTrasladar)){
					$laTemp = $this->oDb
						->from('RIARGMN')
						->where(['REGMED'=>$lcMedicoTrasladar])
						->where(['ESTRGM'=>1])
						->get('array');
					if ($this->oDb->numRows()==0){					
						$this->aError = [
							'Mensaje' =>'No existe descripción médico quien recibe',
							'Objeto'  => 'selMedicoTrasladaTP',
							'Valido'=>false,
						];
						$lbRevisar = false;
					}
					unset($laTemp);
				}
			}
			
			if ($lbRevisar){
				if (empty($lcSeInformaFamiliar)){
					$this->aError = [
						'Mensaje' =>'No existe se informa familiar',
						'Objeto'  => 'selSeInformaFamiliarTP',
						'Valido'=>false,
					];
					$lbRevisar = false;
				}
			}
			
			if ($lbRevisar){
				if (!empty($lcSeInformaFamiliar)){
					if ($lcSeInformaFamiliar!='S' && $lcSeInformaFamiliar!='N'){
						$this->aError = [
							'Mensaje' =>'No existe Se informa a familiar de traslado (Si/No).',
							'Objeto'  => 'SelSetrasladaFamiliarTP',
							'Valido'=>false,
						];
						$lbRevisar = false;
					}	
				}
			}
			
			if ($lbRevisar){
				if (empty($lcSeTrasladaFamiliar)){
					$this->aError = [
						'Mensaje' =>'No existe se traslada en compañia de familiar',
						'Objeto'  => 'SelSetrasladaFamiliarTP',
						'Valido'=>false,
					];
					$lbRevisar = false;
				}
			}
			
			if ($lbRevisar){
				if (!empty($lcSeTrasladaFamiliar)){
					if ($lcSeTrasladaFamiliar!='S' && $lcSeTrasladaFamiliar!='N'){
						$this->aError = [
							'Mensaje' =>'No existe se traslada en compañia de familiar (Si/No).',
							'Objeto'  => 'SelSetrasladaFamiliarTP',
							'Valido'=>false,
						];
						$lbRevisar = false;
					}	
				}
			}
			
			if ($lbRevisar){
				if (empty($lcFrecuenciaRespitaria)){
					$this->aError = [
						'Mensaje' =>'No existe frecuencia respiratoria',
						'Objeto'  => 'fr',
						'Valido'=>false,
					];
					$lbRevisar = false;
				}
			}

			if ($lbRevisar){
				if (empty($lcSaturacion)){
					$this->aError = [
						'Mensaje' =>'No existe saturación',
						'Objeto'  => 'so2',
						'Valido'=>false,
					];
					$lbRevisar = false;
				}
			}
			
			if ($lbRevisar){
				if (empty($lcTemperatura)){
					$this->aError = [
						'Mensaje' =>'No existe temperatura',
						'Objeto'  => 't',
						'Valido'=>false,
					];
					$lbRevisar = false;
				}
			}
			
			if ($lbRevisar){
				if (empty($lcTemperatura)){
					$this->aError = [
						'Mensaje' =>'No existe temperatura',
						'Objeto'  => 't',
						'Valido'=>false,
					];
					$lbRevisar = false;
				}
			}
			
			if ($lbRevisar){
				if (empty($lcPresionArterialSistolica)){
					$this->aError = [
						'Mensaje' =>'No existe presión arterial sistolica',
						'Objeto'  => 'tas',
						'Valido'=>false,
					];
					$lbRevisar = false;
				}
			}

			if ($lbRevisar){
				if (empty($lcPresionArterialDiastolica)){
					$this->aError = [
						'Mensaje' =>'No existe presión arterial distolica',
						'Objeto'  => 'tad',
						'Valido'=>false,
					];
					$lbRevisar = false;
				}
			}
			
			if ($lbRevisar){
				if (empty($lcFrecuenciaCardiaca)){
					$this->aError = [
						'Mensaje' =>'No existe frecuencia cardiaca',
						'Objeto'  => 'fc',
						'Valido'=>false,
					];
					$lbRevisar = false;
				}
			}
			
			if ($lbRevisar){
				if ($lcNivelConciencia===''){
					$this->aError = [
						'Mensaje' =>'No existe nivel de conciencia',
						'Objeto'  => 'nc',
						'Valido'=>false,
					];
					$lbRevisar = false;
				}
			}

			if ($lbRevisar){
				if ($lcNecesitaOxigeno!='0' && $lcNecesitaOxigeno!='1'){	
					$this->aError = [
						'Mensaje' =>'No existe necesita oxigeno suplementario',
						'Objeto'  => 'o2sp',
						'Valido'=>false,
					];
					$lbRevisar = false;
				}
			}
			
			if ($lbRevisar){
				if ($lcEscalaDolor===''){
					$this->aError = [
						'Mensaje' =>'No existe escala dolor',
						'Objeto'  => 'EscalaDolorTr',
						'Valido'=>false,
					];
					$lbRevisar = false;
				}
			}
			
			if ($lbRevisar){
				if (empty($lcDescripcion)){
					$this->aError = [
						'Mensaje' =>'No existe datos a registrar',
						'Objeto'  => 'edtRegistrarTraslado',
						'Valido'=>false,
					];
					$lbRevisar = false;
				}
			}
		}else{
			$this->aError = [
				'Mensaje' =>'No existen datos',
				'Objeto'  => 'edtRegistrarTraslado',
				'Valido'=>false,
			];
			$lbRevisar = false;
		}
		return $this->aError;
	}

	function IniciaDatosAuditoria()
	{
		$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
		$this->cUsuCre = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getUsuario():'');
		$this->cPrgCre = 'TRASWEB';
		$this->cFecCre = $ltAhora->format('Ymd');
		$this->cHorCre = $ltAhora->format('His');
		$this->cChrEnter = chr(13);
	}

	public function guardarDatos($taDatos=[])
	{
		$this->IniciaDatosAuditoria();
		$laRetorno = [
			'Mensaje'=>'',
			'Valido'=>true,
		];
		
		$lnIngreso=isset($taDatos['ingreso']) ? intval($taDatos['ingreso']):0;
		$lnValorNews=isset($taDatos['valornews']) ? $taDatos['valornews']:'';
		$lcHabitacion=isset($taDatos['habitacion']) ? trim($taDatos['habitacion']):'';
		$laDatos=isset($taDatos['datos']) ? $taDatos['datos']:'';
		$lcAreaTrasladar=isset($laDatos['AreaTrasladarTP']) ? trim($laDatos['AreaTrasladarTP']):'';
		$lcEspecialidadTrasladar=isset($laDatos['EspecialidadTrasladarTP']) ? trim($laDatos['EspecialidadTrasladarTP']):'';
		$lcMedicoTrasladar=isset($laDatos['MedicoTrasladaTP']) ? trim($laDatos['MedicoTrasladaTP']):'';
		$lcSeInformaFamiliar=isset($laDatos['SeInformaFamiliarTP']) ? trim($laDatos['SeInformaFamiliarTP']):'';
		$lcSeTrasladaFamiliar=isset($laDatos['SetrasladaFamiliarTP']) ? trim($laDatos['SetrasladaFamiliarTP']):'';
		$lcSignosFrecuencia=trim($laDatos['fr']).'~'.trim($laDatos['so2']).'~'.trim($laDatos['t']).'~'.trim($laDatos['tas']).'~'
							.trim($laDatos['tad']).'~'.trim($laDatos['fc']).'~'.trim($laDatos['nc']).'~'.trim($laDatos['o2sp'])
							.'~'.$lnValorNews.'~'.trim($laDatos['EscalaDolorTr']);
		$lcDatosregistro=isset($laDatos['RegistrarTraslado']) ? trim($laDatos['RegistrarTraslado']):'';							
		
		if (is_array($taDatos) && count($taDatos)>0){
			if ($lnIngreso>0){
				if (!empty($lcDatosregistro)){
					$this->IniciaDatosAuditoria();
					$lnConsecutivo=Consecutivos::fCalcularTrasladoPacientes($lnIngreso);
					
					$lnLinea = 1;
					$lcTabla = 'TRAPAC';
					$lnLongitud = 500;
					$laTextos = AplicacionFunciones::mb_str_split(trim($lcDatosregistro), $lnLongitud);
					if (is_array($laTextos) && !empty($laTextos)) {
						foreach($laTextos as $lcTexto) {
							$laDatosIns = [
								'INGTRA'=>$lnIngreso,
								'CONTRA'=>$lnConsecutivo,
								'SECTRA'=>$lcHabitacion,
								'ARTTRA'=>$lcAreaTrasladar,
								'ESPTRA'=>$lcEspecialidadTrasladar,
								'MRETRA'=>$lcMedicoTrasladar,
								'SIFTRA'=>$lcSeInformaFamiliar,
								'STFTRA'=>$lcSeTrasladaFamiliar,
								'SIGTRA'=>$lcSignosFrecuencia,
								'CLNTRA'=>$lnLinea++,
								'DESTRA'=>$lcTexto,
								'USRTRA'=>$this->cUsuCre,
								'PGMTRA'=>$this->cPrgCre,
								'FECTRA'=>$this->cFecCre,
								'HORTRA'=>$this->cHorCre
							];
							$this->oDb->tabla($lcTabla)->insertar($laDatosIns);
						}
					}
				}else{
					$laRetorno = [
						'Mensaje'=>'No existen datos a registrar.',
						'Valido'=>false,
					];
				}
			}else{
				$laRetorno = [
					'Mensaje'=>'No existe ingreso.',
					'Valido'=>false,
				];
			}
		}else{
			$laRetorno = [
				'Mensaje'=>'No existe información.',
				'Valido'=>false,
			];
		}
		return $laRetorno;
	}
	
					
	function fnEscribirlog($tcMensaje, $tbEcho=false)
	{
		$lcRuta = __DIR__ . '/../logs/log_' . date('Ym');
		if (!is_dir($lcRuta)) { mkDir($lcRuta, 0777, true); }
		$lcFilelog = $lcRuta . '/logAccion_' . date('Ymd') . '.txt';
		$lcMensaje = date('y-m-d h:i:s') . ' | ' . $tcMensaje . "\n";

		$lnFile = fOpen($lcFilelog, 'a');
		fPuts($lnFile, $lcMensaje);
		fClose($lnFile);
		if ($tbEcho) { echo $lcMensaje; }
	}

	
}