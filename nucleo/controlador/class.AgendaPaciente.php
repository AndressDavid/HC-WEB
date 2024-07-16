<?php
namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';
require_once __DIR__ . '/class.AplicacionFunciones.php';
require_once __DIR__ . '/class.Consecutivos.php';
require_once __DIR__ . '/class.Persona.php';
require_once __DIR__ . '/class.Citas.php';
require_once __DIR__ . '/class.MailEnviar.php';


class AgendaPaciente
{
	protected $oDb = null;
	protected $aClaves = [
		'paciente' => [
			'TIPOID'	=> 'tipoId',
			'NUMEROID'	=> 'numeroId',
			'NOMBRE1'	=> 'nombre1',
			'NOMBRE2'	=> 'nombre2',
			'APELLIDO1'	=> 'apellido1',
			'APELLIDO2'	=> 'apellido2',
			'FECHANAC'	=> 'fechaNac',
			'GENERO'	=> 'genero',
			'CORREO'	=> 'correo',
			'TELEFONO'	=> 'telefono',
			'TELEFONO2'	=> 'telefono2',
			'CELULAR'	=> 'celular',
			'CELULAR2'	=> 'celular2',
			'DIRECCION'	=> 'direccion',
			'BARRAS'	=> 'desdeDoc',
		],
	];
	private $aCitaElectro = [
		'1'=>'S. Jude',
		'2'=>'Guidant',
		'3'=>'Metronic',
		'4'=>'Sorin',
		'5'=>'Telectronic',
		'6'=>'Intermedics',
	];
	private $aAgendaMed = [];
	private $aConsultorios = [];
	private $cMensajePacienteNoEncontrado = 'Paciente no encontrado';

	private $nDobutamina = 0;


	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}


	/*
	 *	Datos de un paciente por documento
	 *	@param string $tcTipoId: Tipo de documento de identidad del paciente
	 *	@param decimal $tnNumId: Número de documento de identidad del paciente
	 *	@return array con los elementos: success (true o false), mensaje (en caso de success false), datos (array con datos del paciente TIPOID, NUMEROID, NOMBRE1, NOMBRE2, APELLIDO1, APELLIDO2, FECHANAC, GENERO, CORREO, TELEFONO, TELEFONO2, CELULAR, CELULAR2, DIRECCION)
	 */
	public function consultarPacientePorDocumento($tcTipoId, $tnNumId)
	{
		$laRta = $this->validarId($tcTipoId, $tnNumId);
		if ($laRta['success']) {
			$laDatos = $this->oDb
				->select('P.TIDPAC TIPOID, P.NIDPAC NUMEROID, IFNULL(A.NM1PAL,P.NM1PAC) NOMBRE1, IFNULL(A.NM2PAL,P.NM2PAC) NOMBRE2, IFNULL(A.AP1PAL,P.AP1PAC) APELLIDO1, IFNULL(A.AP2PAL,P.AP2PAC) APELLIDO2')
				->select('SUBSTR(CHAR(TO_DATE(CHAR(P.FNAPAC),\'YYYYMMDD\')),1,10) FECHANAC')
				->select('IFNULL(A.SEXPAL,P.SEXPAC) GENERO, IFNULL(A.MAIPAL,P.MAIPAC) CORREO, IFNULL(A.DIRPAL,P.DR1PAC) DIRECCION')
				->select('IFNULL(A.TP1PAL,P.TELPAC) TELEFONO, IFNULL(A.TP2PAL,\'\') TELEFONO2, IFNULL(A.CP1PAL,P.FA2PAC) CELULAR, IFNULL(A.CP2PAL,\'\') CELULAR2')
				->select('(SELECT COUNT(*) FROM IDPACBIT WHERE IDTIP1=P.TIDPAC AND IDNUM1=P.NIDPAC AND TIPREG=\'CAPTURA\' AND ESTADO=\'A\' AND TIPCAP=\'BARRAS\') BARRAS')
				->from('RIAPAC P')
				->leftJoin('PACALT A', 'P.TIDPAC=A.TIDPAL AND P.NIDPAC=A.NIDPAL', null)
				->where([
					'P.TIDPAC'=>$tcTipoId,
					'P.NIDPAC'=>$tnNumId,
				])
				->get('array');
			if($this->oDb->numRows()>0){
				$laDatos = $this->modificaClaves('paciente', array_map('trim',$laDatos));
				$laDatos['desdeDoc']=$laDatos['desdeDoc']>0? 'true': 'false';
				$laRta = ['success'=>true, 'datos'=>array_map('trim',$laDatos)];

			} else {
				$laRta = ['success'=>false, 'message'=>$this->cMensajePacienteNoEncontrado];
			}
		}

		return array_merge(['fechahora'=>date('Y-m-d H:i:s')], $laRta);
	}


	/*
	 *	Insertar paciente
	 *	@param array $taDatos: arreglo con los siguientes elementos: tipoId, numeroId, nombre1, nombre2, apellido1, apellido2, fechaNac, genero, correo, telefono, telefono2, celular, celular2, direccion
	 *	@param string $tcUser: Usuario que inserta el paciente
	 *	@param string $tcProg: Programa que inserta el paciente
	 *	@return array con los elementos: success (true o false), mensaje (en caso de success false), datos (array con datos del paciente insertado)
	 */
	public function insertarPaciente($taDatos, $tcUser='AGENDAWEB', $tcProg='INSERT_PAC')
	{
		$lcFechaNac = str_replace('-','',$taDatos['fechaNac']);

		$ltAhora = new \DateTime($this->oDb->fechaHoraSistema());
		$lcFecha = $ltAhora->format('Ymd');
		$lcHora  = $ltAhora->format('His');

		$lcTabla = 'RIAPAC';
		$laData = [
			'TIDPAC' => $taDatos['tipoId'],
			'NIDPAC' => $taDatos['numeroId'],
			'NM1PAC' => mb_substr($taDatos['nombre1'],0,15,'UTF-8'),
			'NM2PAC' => mb_substr($taDatos['nombre2'],0,15,'UTF-8'),
			'AP1PAC' => mb_substr($taDatos['apellido1'],0,15,'UTF-8'),
			'AP2PAC' => mb_substr($taDatos['apellido2'],0,15,'UTF-8'),
			'FNAPAC' => $lcFechaNac,
			'SEXPAC' => $taDatos['genero'],
			'MAIPAC' => mb_substr($taDatos['correo'],0,60,'UTF-8'),
			'DR1PAC' => mb_substr($taDatos['direccion'],0,30,'UTF-8'),
			'TELPAC' => $taDatos['telefono'],
			'FA2PAC' => mb_substr(trim($taDatos['celular']),0,20,'UTF-8'),
			'USRPAC' => $tcUser,
			'PGMPAC' => $tcProg,
			'FECPAC' => $lcFecha,
			'HORPAC' => $lcHora,
		];
		if (isset($taDatos['cita'])) {
			$laData['CCIPAC'] = $taDatos['cita'];
		}
		$this->oDb->from($lcTabla)->insertar($laData);

		$lcTabla = 'PACALT';
		$laData = [
			'TIDPAL' => $taDatos['tipoId'],
			'NIDPAL' => $taDatos['numeroId'],
			'NM1PAL' => mb_substr($taDatos['nombre1'],0,40,'UTF-8'),
			'NM2PAL' => mb_substr($taDatos['nombre2'],0,40,'UTF-8'),
			'AP1PAL' => mb_substr($taDatos['apellido1'],0,40,'UTF-8'),
			'AP2PAL' => mb_substr($taDatos['apellido2'],0,40,'UTF-8'),
			'FNAPAL' => $lcFechaNac,
			'SEXPAL' => $taDatos['genero'],
			'MAIPAL' => $taDatos['correo'],
			'DIRPAL' => $taDatos['direccion'],
			'TP1PAL' => $taDatos['telefono'],
			'TP2PAL' => $taDatos['telefono2'],
			'CP1PAL' => $taDatos['celular'],
			'OP5PAL' => $taDatos['numeroIdAlt']??'',
			'CP2PAL' => $taDatos['celular2'],
			'USRPAL' => $tcUser,
			'PGMPAL' => $tcProg,
			'FECPAL' => $lcFecha,
			'HORPAL' => $lcHora,
		];
		$this->oDb->from($lcTabla)->insertar($laData);

		$lcTabla = 'PACDET';
		$laData = [
			'TIDPAD' => $taDatos['tipoId'],
			'NIDPAD' => $taDatos['numeroId'],
			'NM1PAD' => mb_substr($taDatos['nombre1'],0,40,'UTF-8'),
			'NM2PAD' => mb_substr($taDatos['nombre2'],0,40,'UTF-8'),
			'AP1PAD' => mb_substr($taDatos['apellido1'],0,40,'UTF-8'),
			'AP2PAD' => mb_substr($taDatos['apellido2'],0,40,'UTF-8'),
			'FNAPAD' => $lcFechaNac,
			'SEXPAD' => $taDatos['genero'],
			'MAIPAD' => $taDatos['correo'],
			'DR1PAD' => $taDatos['direccion'],
			'TP1PAD' => $taDatos['telefono'],
			'TP2PAD' => $taDatos['telefono2'],
			'CP1PAD' => $taDatos['celular'],
			'CP2PAD' => $taDatos['celular2'],
			'USRPAD' => $tcUser,
			'PGMPAD' => $tcProg,
			'FECPAD' => $lcFecha,
			'HORPAD' => $lcHora,
		];
		if (isset($taDatos['cita'])) {
			$laData['CCIPAD'] = $taDatos['cita'];
		}
		$this->oDb->from($lcTabla)->insertar($laData);
		$laRta = ['success'=>true, 'message'=>'Paciente creado.'];

		return array_merge(['fechahora'=>date('Y-m-d H:i:s')], $laRta);
	}


	/*
	 *	Actualizar paciente
	 *	@param array $taDatos: arreglo con los siguientes elementos: nombre1, nombre2, apellido1, apellido2, fechaNac, genero, correo, telefono, telefono2, celular, celular2, direccion
	 *	@param string $tcUser: Usuario que inserta el paciente
	 *	@param string $tcProg: Programa que inserta el paciente
	 *	@param boolean $tbNoActualizarDatos: Si es true no actualiza nombres, fecha de nacimiento, sexo
	 *	@return array con los elementos: success (true o false), mensaje (en caso de success false), datos (array con datos del paciente insertado)
	 */
	public function actualizarPaciente($taDatos, $tcUser='AGENDAWEB', $tcProg='ACTUAL_PAC', $tbNoActualizarDatos=false)
	{
		$tcTipoId = $taDatos['tipoId'];
		$tnNumId  = $taDatos['numeroId'];
		$laWhere = [
			'TIDPAC'=>$tcTipoId,
			'NIDPAC'=>$tnNumId,
		];

		$laDatos = $this->oDb
			->select('P.TIDPAC TIPOID, P.NIDPAC NUMEROID, P.NHCPAC HISTORIA, P.FNAPAC FECHANAC, IFNULL(A.SEXPAL,P.SEXPAC) GENERO, IFNULL(A.MAIPAL,P.MAIPAC) CORREO, IFNULL(A.DIRPAL,P.DR1PAC) DIRECCION')
			->select('IFNULL(A.TP1PAL,P.TELPAC) TELEFONO, IFNULL(A.TP2PAL,\'\') TELEFONO2, IFNULL(A.CP1PAL,P.FA2PAC) CELULAR, IFNULL(A.CP2PAL,\'\') CELULAR2')
			->select('IFNULL(A.NM1PAL,P.NM1PAC) NOMBRE1, IFNULL(A.NM2PAL,P.NM2PAC) NOMBRE2, IFNULL(A.AP1PAL,P.AP1PAC) APELLIDO1, IFNULL(A.AP2PAL,P.AP2PAC) APELLIDO2')
			->select('(SELECT COUNT(*) FROM IDPACBIT WHERE IDTIP1=P.TIDPAC AND IDNUM1=P.NIDPAC AND TIPREG=\'CAPTURA\' AND ESTADO=\'A\' AND TIPCAP=\'BARRAS\') BARRAS')
			->from('RIAPAC P')
			->leftJoin('PACALT A', 'P.TIDPAC=A.TIDPAL AND P.NIDPAC=A.NIDPAL', null)
			->where($laWhere)
			->get('array');
		if ($this->oDb->numRows()==0) {
			$laRta = ['success'=>false, 'message'=>'Ocurrió un error al consultar datos del paciente.'];

		// Actualiza el paciente
		} else {

			$lbDesdeDoc = $tbNoActualizarDatos ? $tbNoActualizarDatos : $laDatos['BARRAS']>0;
			$lcFechaNac = str_replace('-','',$taDatos['fechaNac']);

			$ltAhora = new \DateTime($this->oDb->fechaHoraSistema());
			$lcFecha = $ltAhora->format('Ymd');
			$lcHora  = $ltAhora->format('His');

			foreach($this->aClaves['paciente'] as $laClave=>$lcCampo){
				if (!isset($taDatos[$lcCampo]))
					$taDatos[$lcCampo] = $laDatos[$laClave];
			}

			$lcTabla = 'RIAPAC';
			$laData = array_merge(
			($lbDesdeDoc ? [] : [
				'NM1PAC' => mb_substr($taDatos['nombre1'],0,15,'UTF-8'),
				'NM2PAC' => mb_substr($taDatos['nombre2'],0,15,'UTF-8'),
				'AP1PAC' => mb_substr($taDatos['apellido1'],0,15,'UTF-8'),
				'AP2PAC' => mb_substr($taDatos['apellido2'],0,15,'UTF-8'),
				'FNAPAC' => $lcFechaNac,
				'SEXPAC' => $taDatos['genero'],
			]), [
				'UMOPAC' => $tcUser,
				'PMOPAC' => $tcProg,
				'FMOPAC' => $lcFecha,
				'HMOPAC' => $lcHora,
			]);
			$lcCorreo = mb_substr($taDatos['correo'],0,60,'UTF-8');
			$lcDirecc = mb_substr($taDatos['direccion'],0,30,'UTF-8');
			$lcCelular = mb_substr(trim($taDatos['celular']),0,20,'UTF-8');
			if (!empty($lcCorreo))				$laData['MAIPAC'] = $lcCorreo;
			if (!empty($lcDirecc))				$laData['DR1PAC'] = $lcDirecc;
			if (!empty($taDatos['telefono']))	$laData['TELPAC'] = $taDatos['telefono'];
			if (!empty($lcCelular))				$laData['FA2PAC'] = $lcCelular;
			if (isset($taDatos['cita']))		$laData['CCIPAC'] = $taDatos['cita'];
			$this->oDb->from($lcTabla)->where($laWhere)->actualizar($laData);

			$lcTabla = 'PACALT';
			$laWhere = [
				'TIDPAL'=>$tcTipoId,
				'NIDPAL'=>$tnNumId,
			];
			$laPacAlt = $this->oDb->from($lcTabla)->where($laWhere)->get('array');
			if ($this->oDb->numRows()==0) {
				// Si no existe en la tabla PACALT se inserta
				$laData = [
					'TIDPAL' => $taDatos['tipoId'],
					'NIDPAL' => $taDatos['numeroId'],
					'NM1PAL' => mb_substr($taDatos['nombre1'],0,40,'UTF-8'),
					'NM2PAL' => mb_substr($taDatos['nombre2'],0,40,'UTF-8'),
					'AP1PAL' => mb_substr($taDatos['apellido1'],0,40,'UTF-8'),
					'AP2PAL' => mb_substr($taDatos['apellido2'],0,40,'UTF-8'),
					'FNAPAL' => $lcFechaNac,
					'SEXPAL' => $taDatos['genero'],
					'MAIPAL' => $taDatos['correo'],
					'DIRPAL' => $taDatos['direccion'],
					'TP1PAL' => $taDatos['telefono'],
					'TP2PAL' => $taDatos['telefono2'],
					'CP1PAL' => $taDatos['celular'],
					'OP5PAL' => $taDatos['numeroIdAlt']??'',
					'CP2PAL' => $taDatos['celular2'],
					'USRPAL' => $tcUser,
					'PGMPAL' => $tcProg,
					'FECPAL' => $lcFecha,
					'HORPAL' => $lcHora,
				];
				$this->oDb->from($lcTabla)->insertar($laData);
			} else {
				$laData = array_merge(
					($lbDesdeDoc ? [] : [
						'NM1PAL' => mb_substr($taDatos['nombre1'],0,40,'UTF-8'),
						'NM2PAL' => mb_substr($taDatos['nombre2'],0,40,'UTF-8'),
						'AP1PAL' => mb_substr($taDatos['apellido1'],0,40,'UTF-8'),
						'AP2PAL' => mb_substr($taDatos['apellido2'],0,40,'UTF-8'),
						'FNAPAL' => $lcFechaNac,
						'SEXPAL' => $taDatos['genero'],
					]), [
						'UMOPAL' => $tcUser,
						'PMOPAL' => $tcProg,
						'FMOPAL' => $lcFecha,
						'HMOPAL' => $lcHora,
				]);
				if (!empty($taDatos['correo']))		$laData['MAIPAL'] = $taDatos['correo'];
				if (!empty($taDatos['direccion']))	$laData['DIRPAL'] = $taDatos['direccion'];
				if (!empty($taDatos['telefono']))	$laData['TP1PAL'] = $taDatos['telefono'];
				if (!empty($taDatos['telefono2']))	$laData['TP2PAL'] = $taDatos['telefono2'];
				if (!empty($taDatos['celular']))	$laData['CP1PAL'] = $taDatos['celular'];
				if (!empty($taDatos['celular2']))	$laData['CP2PAL'] = $taDatos['celular2'];
				$this->oDb->from($lcTabla)->where($laWhere)->actualizar($laData);
			}

			$lcTabla = 'PACDET';
			$laData = array_merge(
			($lbDesdeDoc ? [
				'NM1PAD' => $laDatos['NOMBRE1'],
				'NM2PAD' => $laDatos['NOMBRE2'],
				'AP1PAD' => $laDatos['APELLIDO1'],
				'AP2PAD' => $laDatos['APELLIDO2'],
				'FNAPAD' => $laDatos['FECHANAC'],
				'SEXPAD' => $laDatos['GENERO'],
			] : [
				'NM1PAD' => mb_substr($taDatos['nombre1'],0,40,'UTF-8'),
				'NM2PAD' => mb_substr($taDatos['nombre2'],0,40,'UTF-8'),
				'AP1PAD' => mb_substr($taDatos['apellido1'],0,40,'UTF-8'),
				'AP2PAD' => mb_substr($taDatos['apellido2'],0,40,'UTF-8'),
				'FNAPAD' => $lcFechaNac,
				'SEXPAD' => $taDatos['genero'],
			]), [
				'NHCPAD' => $laDatos['HISTORIA'],
				'TIDPAD' => $tcTipoId,
				'NIDPAD' => $tnNumId,
				'MAIPAD' => $taDatos['correo'],
				'DR1PAD' => $taDatos['direccion'],
				'TP1PAD' => $taDatos['telefono'],
				'TP2PAD' => $taDatos['telefono2'],
				'CP1PAD' => $taDatos['celular'],
				'CP2PAD' => $taDatos['celular2'],
				'USRPAD' => $tcUser,
				'PGMPAD' => $tcProg,
				'FECPAD' => $lcFecha,
				'HORPAD' => $lcHora,
			]);
			if (isset($taDatos['cita'])) $laData['CCIPAD'] = $taDatos['cita'];
			$this->oDb->from($lcTabla)->insertar($laData);

			$laRta = ['success'=>true, 'message'=>'Paciente actualizado.'.($lbDesdeDoc?' Solo se actualizaron datos de localización.':'')];
		}

		return array_merge(['fechahora'=>date('Y-m-d H:i:s')], $laRta);
	}


	/*
	 *	Valida si el paciente existe
	 *	@param string $tcTipoId: Tipo de documento de identidad del paciente
	 *	@param decimal $tnNumId: Número de documento de identidad del paciente
	 *	@return boolean que indica si exite o no el paciente
	 */
	public function existePaciente($tcTipoId, $tnNumId)
	{
		$laDatos = $this->oDb
			->select('TIDPAC TIPODOC, NIDPAC NUMDOC')
			->from('RIAPAC')
			->where([
				'TIDPAC'=>$tcTipoId,
				'NIDPAC'=>$tnNumId,
			])
			->get('array');
		if ($this->oDb->numRows()>0) {
			$laReturn = ['success'=>true, 'data'=>$laDatos];
		} else {
			if ($tcTipoId=='P') {
				$laDatos = $this->oDb
					->select('TIDPAL TIPODOC, NIDPAL NUMDOC, OP5PAL NUMDOCA')
					->from('PACALT')
					->where([
						'TIDPAL'=>$tcTipoId,
						'OP5PAL'=>$tnNumId,
					])
					->get('array');
				if ($this->oDb->numRows()>0) {
					$laReturn = ['success'=>true, 'data'=>$laDatos];
				} else {
					$laReturn = ['success'=>false, 'message'=>'Pasaporte no encontrado'];
				}
			} else {
				$laReturn = ['success'=>false, 'message'=>'Documento no encontrado'];
			}
		}

		return $laReturn;
	}


	/*
	 *	Consulta citas para un paciente
	 *	@param string $tcTipoId: Tipo de documento de identidad del paciente
	 *	@param integer $tnNumId: Número de documento de identidad del paciente
	 *	@param integer $tnFechaIni: Fecha inicial de consulta
	 *	@param integer $tnFechaFin: Fecha final de consulta
	 *	@return array con los elementos: success (true o false), mensaje (en caso de success false), datos (array con datos de las citas del paciente)
	 */
	public function consultarCitaPorDocumento($tcTipoId, $tnNumId, $tnFechaIni, $tnFechaFin)
	{
		$laRta = $this->validarId($tcTipoId, $tnNumId);
		if (!$laRta['success']) return $laRta;

		$laRta = [];
		$laDatos = $this->oDb
			->select([
				'O.NINORD INGRESO',
				'O.CCIORD CITA',
				'O.EVOORD CUPO',
				'O.ESTORD ESTADO',
				'O.FRLORD FECHA_CITA',
				'O.HOCORD HORA_CITA',
				'O.FERORD FECHA_REALIZADO',
				'O.HRLORD HORA_REALIZADO',
				"IFNULL(O.COAORD,'') CUP",
				'O.VIAORD COD_VIA',
				"CASE O.VIAORD WHEN '02' THEN 'CE' WHEN '01' THEN 'UR' WHEN '05' THEN 'HO' WHEN '06' THEN 'AM' ELSE '' END VIA",
				'O.CODORD ESPECIALIDAD',
				'O.RMRORD REGMED_REALIZA',
				'O.RMEORD REGMED_ORDENA',
				'O.PLAORD COD_PLAN',
				"IFNULL(PL.DSCCON,'') PLAN",
				'C.ESCCIT UNIDAD_AGENDA',
				"CASE WHEN CUP.RF3CUP='MED.NU' THEN 1 ELSE 0 END MCA",
				"CASE C.NSACIT WHEN 'S' THEN 1 WHEN 'C' THEN 2 WHEN 'P' THEN 3 ELSE 0 END C3D",
				//"CASE WHEN C.NSACIT='S' THEN 'D' ELSE '' END COM",
				'IFNULL(EX.HINEXE,-1) HORA_INI_EXC',
				'IFNULL(EX.HFIEXE,-1) HORA_FIN_EXC',
			])
			->from('RIAORD O')
			->innerJoin('RIACIT C',  'O.TIDORD=C.TIDCIT AND O.NIDORD=C.NIDCIT AND O.CCIORD=C.CCICIT')
			->leftJoin('UNIEXE EX',  "C.ESCCIT=EX.CODEXE AND O.RMRORD=EX.REGEXE AND O.FRLORD=EX.FEXEXE AND EX.ESTEXE='B'")
			->leftJoin('RIACUP CUP', 'O.COAORD=CUP.CODCUP')
			->leftJoin('FACPLNC PL', "O.PLAORD=PL.PLNCON AND PL.PLNCON<>''")
			->where([
				'O.TIDORD'=>$tcTipoId,
				'O.NIDORD'=>$tnNumId,
			])
			->in('O.ESTORD', [2,3,8])
			->between('O.FRLORD', $tnFechaIni, $tnFechaFin)
			->orderBy('O.FRLORD, O.HOCORD')
			->getAll('array');
		if($this->oDb->numRows()>0){
			$laCitas = [];
			foreach($laDatos as &$laDato){
				$laDato = array_map('trim',$laDato);
				if(($laDato['HORA_CITA']>=$laDato['HORA_INI_EXC'] && $laDato['HORA_CITA']<=$laDato['HORA_FIN_EXC']) || ($laDato['HORA_INI_EXC']=0 && $laDato['HORA_FIN_EXC']==0)) {
					$laDato['ESTADO']=9;
				}
				unset($laDato['HORA_INI_EXC'],$laDato['HORA_FIN_EXC']);
			}
			//$laRta = ['success'=>true, 'datos'=>$laDatos];
			$laRta = ['success'=>true, 'datos'=>array_map('array_change_key_case',$laDatos)];
		} else {
			$laRta = ['success'=>false, 'message'=>'Citas no encontradas'];
		}

		return array_merge(['fechahora'=>date('Y-m-d H:i:s')], $laRta);
	}


	/*
	 *	Valida estructura del documento del paciente
	 *	@param string $tcTipoId: Tipo de documento de identidad del paciente
	 *	@param decimal $tnNumId: Número de documento de identidad del paciente
	 *	@return array con los elementos: success (true o false) y mensaje (en caso de success false)
	 */
	public function validarId($tcTipoId, $tnNumeroId)
	{
		$lcMsg = $lcSep = '';
		$laData = $this->oDb->select('TIPDOC')->from('RIATI')->where(['TIPDOC'=>$tcTipoId])->get('array');
		if ($this->oDb->numRows()==0) {
			$lcMsg  = 'Tipo de documento no permitido.';
			$lcSep = "/n";
			// return ['success'=>false, 'message'=>'Tipo de documento no permitido.'];
		}
		if (in_array($tcTipoId, ['C','E','T','R','V'])) {
			if ($tnNumeroId<0 || strlen($tnNumeroId)>13) {
				$lcMsg .= $lcSep.'Número de documento incorrecto.';
				// return ['success'=>false, 'message'=>'Número de documento incorrecto.'];
			}
		}
		if (empty($lcMsg)) {
			return ['success'=>true];
		} else {
			return ['success'=>false, 'message'=>$lcMsg];
		}
		// return ['success'=>true];
	}


	/*
	 *	Valida los datos del paciente
	 *	@param array $taDatos: arreglo con los datos de paciente (tipoId, numeroId, nombre1, nombre2, apellido1, apellido2, fechaNac, genero, correo, telefono, telefono2, celular, celular2, direccion)
	 *	@return array con los elementos: success (true o false) y mensaje (en caso de success false)
	 */
	public function validarDatosPac($taDatos)
	{
		$laRta = [];
		$lcMsg = $lcSep = '';

		if (isset($taDatos['tipoId']) && isset($taDatos['numeroId'])) {
			$laRta = $this->validarId($taDatos['tipoId'], $taDatos['numeroId']);
			if (!$laRta['success']) {
				$lcMsg = $laRta['message'];
				$lcSep = "/n";
			}
		}
		$lnMinLen = 2;
		if (isset($taDatos['nombre1'])) {
			if (strlen($taDatos['nombre1'])<$lnMinLen || strlen($taDatos['apellido1'])<$lnMinLen) {
				$lcMsg .= $lcSep."Nombre1 y/o apellido1 deben tener al menos $lnMinLen caracteres.";
				$lcSep = "/n";
			}
		}
		$laFecha = explode('-',$taDatos['fechaNac']);
		if (count($laFecha)==3) {
			if (checkdate($laFecha[1],$laFecha[2],$laFecha[0])) {
				if (strtotime($taDatos['fechaNac'])>strtotime(date('Y-m-d'))) {
					$lcMsg .= $lcSep.'Fecha de nacimiento no puede ser mayor a la fecha actual.';
					$lcSep = "/n";
				}
				if (strtotime($taDatos['fechaNac'])<strtotime(date('Y-m-d').' - 150 year')) {
					$lcMsg .= $lcSep.'Fecha de nacimiento no puede ser inferior a la fecha actual menos 150 años.';
					$lcSep = "/n";
				}
			} else {
				$lcMsg .= $lcSep.'Fecha de nacimiento incorrecta, debe estar en formato YYYY-MM-DD.';
				$lcSep = "/n";
			}
		} else {
			$lcMsg .= $lcSep.'Fecha de nacimiento debe estar en formato YYYY-MM-DD.';
			$lcSep = "/n";
		}
		if (!in_array($taDatos['genero'],['M','F'])) {
			$lcMsg .= $lcSep.'Género debe ser F o M.';
			$lcSep = "/n";
		}
		if (!empty($taDatos['correo'])) {
			if (!boolval(filter_var($taDatos['correo'], FILTER_VALIDATE_EMAIL))) {
				$lcMsg .= $lcSep.'Correo electrónico no válido.';
				$lcSep = "/n";
			}
		}
		$laRta = empty($lcMsg) ? ['success'=>true] : ['success'=>false, 'message'=>$lcMsg];

		return array_merge(['fechahora'=>date('Y-m-d H:i:s')], $laRta);
	}


	/*
	 *	Genera consulta de Agenda entre dos fechas
	 *	@param integer $tcUnidadAgenda: código de la unidad de agendamiento
	 *	@param integer $tnFechaIni: fecha inicial de consulta
	 *	@param integer $tnFechaFin: fecha final de consulta
	 *	@param string $tcCupsPaciente: código CUP del procedimiento
	 *	@param string $tcRegMedico: registro médico (13 caracteres)
	 *	@return array con los datos de los cupos
	 */
	public function agendaConsultar($tcUnidadAgenda, $tnFechaIni, $tnFechaFin, $tcCupsPaciente='', $tcRegMedico='')
	{
		$this->aAgendaMed = [];

		$laReturn = $this->agendaCrearHorarios($tnFechaIni, $tnFechaFin, $tcUnidadAgenda, $tcCupsPaciente, $tcRegMedico);
		if(!$laReturn['success']) return $laReturn;

		$laReturn = $this->agendaCitasExtra();
		if(!$laReturn['success']) return $laReturn;

		$laReturn = $this->agendaCitasbloqueadas($tnFechaIni, $tnFechaFin, $tcUnidadAgenda, $tcRegMedico='');
		if(!$laReturn['success']) return $laReturn;

		$laReturn = $this->agendaDatosDia();
		if(!$laReturn['success']) return $laReturn;

		$laReturn = $this->agendaFueraDeHorario($tnFechaIni, $tnFechaFin, $tcUnidadAgenda, $tcRegMedico);
		if(!$laReturn['success']) return $laReturn;

		if(in_array($tcUnidadAgenda, ['29','30'])){		// SCANNER, RESONANCIA
			$laReturn = $this->agendaValidarHora();
			if(!$laReturn['success']) return $laReturn;
		}
		if($tcUnidadAgenda=='22'){						// TERAPIA FISICA
			$laReturn = $this->agendaTerapiaFisica();
			if(!$laReturn['success']) return $laReturn;
		}

		// Ordenar por fecha hora
		AplicacionFunciones::ordenarArrayMulti($this->aAgendaMed, ['fecha','hora'], SORT_ASC, SORT_NUMERIC);

		// Formato fecha hora
		foreach ($this->aAgendaMed as &$laAgeMed) {
			$laAgeMed['fecha']=$this->fecha_NumToStr($laAgeMed['fecha']);
			$laAgeMed['hora']=$this->horaMin_NumToStr($laAgeMed['hora']);
			$laAgeMed['horai']=$this->horaMin_NumToStr($laAgeMed['horai']);
			$laAgeMed['horaf']=$this->horaMin_NumToStr($laAgeMed['horaf']);
			$laAgeMed['horaic']=$this->horaMin_NumToStr($laAgeMed['horaic']);
			$laAgeMed['horafc']=$this->horaMin_NumToStr($laAgeMed['horafc']);
		}
		unset($laAgeMed);

		return array_merge($laReturn, ['datos'=>$this->aAgendaMed]);
	}
	private function fecha_NumToStr($tnFecha){
		return substr($tnFecha,0,4).'-'.substr($tnFecha,4,2).'-'.substr($tnFecha,6,2);
	}
	private function horaMin_NumToStr($tnHoraMinuto){
		$lcHoraMin=str_pad($tnHoraMinuto,4,'0',STR_PAD_LEFT);
		return substr($lcHoraMin,0,2).':'.substr($lcHoraMin,2,2);
	}


	public function agendaCrearHorarios($tnFechaIni, $tnFechaFin, $tcUnidadAgenda, $tcCupsPaciente='', $tcRegMedico='')
	{
		$lcEstadoDesbloquea = 'D';	//	ACTIVO - DESBLOQUEADO

		$ldFechaIni = \DateTime::createFromFormat('Ymd', $tnFechaIni);
		$ldFechaFin = \DateTime::createFromFormat('Ymd', $tnFechaFin);
		$loDifFecha = $ldFechaIni->diff($ldFechaFin);
		$lnDiffDias = $loDifFecha->days;

		// Validar el número máximo de días a consultar
		$lnDiasMax = 15;
		if ($lnDiffDias>$lnDiasMax) {
			return [
				'success'=>false,
				'fechahora'=>date('Y-m-d H:i:s'),
				'mensaje'=>'El rango de fechas no puede exceder 15 días',
			];
		}

		$lnUnidadAgenda = intval($tcUnidadAgenda);
		$tcCupsPaciente	= trim($tcCupsPaciente);
		$lcCupsCita		= $tcCupsPaciente;
		$tcRegMedico	= trim($tcRegMedico);

		// Especialidad Unidad de Agenda
		$laEspUni = $this->oDb->select('ESPUNA')->from('UNIAGEL01')->where(['CODUNA'=>$lnUnidadAgenda])->get('array');
		if ($this->oDb->numRows()==0) return ['success'=>false, 'message'=>'Unidad de agenda no encontrada.'];
		$lcCodigoEspec = trim($laEspUni['ESPUNA']);

		// Citas por especialidad
		$laCitasEsp = $this->oDb
			->select('C.TIDCIT, C.NIDCIT, C.CCICIT, C.EVOCIT, C.NINCIT, C.ESTCIT, C.SSACIT, C.NSACIT, C.COACIT, C.INSCIT, C.PGMCIT, C.FRLCIT, C.HOCCIT, C.RMRCIT, C.ESCCIT')
			->select("IFNULL(TRIM(P.NM1PAC)||' '||TRIM(P.NM2PAC)||' '||TRIM(P.AP1PAC)||' '||TRIM(P.AP2PAC),'') AS PACIENTE")
			->from('RIACIT C')
			->leftJoin('RIAPAC P', 'C.TIDCIT=P.TIDPAC AND C.NIDCIT=P.NIDPAC')
			->where(['C.CODCIT'=>$lcCodigoEspec])
			->in('C.ESTCIT', [2, 3, 8, 30])		// RIACITL14
			->in('C.VIACIT', ['02', '06'])	// RIACITL14
			->between('C.FRLCIT', $tnFechaIni, $tnFechaFin)
			->getAll('array');
		if (!is_array($laCitasEsp)) $laCitasEsp=[];
		foreach($laCitasEsp as &$laCitEsp){
			$laCitEsp=array_map('trim',$laCitEsp);
		}
		unset($laCitEsp);

		// Días festivos
		$laFestivos = [];
		$laFest = $this->oDb->select('DFEDFE')->from('UNIFEC')->between('DFEDFE', $tnFechaIni, $tnFechaFin)->getAll('array');
		if (is_array($laFest)) {
			foreach($laFest as $laFes){
				$laFestivos[] = $laFes['DFEDFE'];
			}
		}
		unset($laFest);

		$ldFechaCont = \DateTime::createFromFormat('Ymd', $tnFechaIni);
		for($lnNumDia=0; $lnNumDia<=$lnDiffDias; $lnNumDia++){

			if ($lnNumDia>0) { $ldFechaCont->add(new \DateInterval('P1D')); }
			$lcDiaCalendario = $ldFechaCont->format('Ymd');
			$lnDiaCalendario = intval($lcDiaCalendario);
			$lnDiaSemana = $this->diaSemana($lnDiaCalendario);

			// Verifica días festivos
			if(in_array($lnDiaCalendario, $laFestivos)){ continue; }

			$laWhere = [
				'U.CODPME'=>$lnUnidadAgenda,
				'U.DIAPME'=>$lnDiaSemana,
				'U.ESTPME'=>' ', // UNIPMEL04
			];
			if(!empty($tcRegMedico)){$laWhere['U.REGPME']=$tcRegMedico;}
			$laHorarios = $this->oDb
				->select('U.HINPME, U.HFIPME, U.LAPPME, U.CANPME, U.REGPME, U.PROPME, U.OP1PME, U.OP2PME, U.OP4PME, U.OP7PME')
				->select("IFNULL(TRIM(M.NOMMED)||' '||TRIM(M.NNOMED),'') MEDICO")
				->from('UNIPME U')
				->leftJoin('RIARGMN M','U.REGPME=M.REGMED')
				->where($laWhere)
				->where("U.ESTPME='' AND $lnDiaCalendario BETWEEN U.FDEPME AND U.FHAPME")
				->orderBy('U.DIAPME, U.HINPME')
				->getAll('array');
			if(!is_array($laHorarios)){$laHorarios=[];}

			foreach($laHorarios as $laHorario){
				$laHorario=array_map('trim',$laHorario);

				$lcNombreMedico=$lcProgramaCrea='';
				$lnEjecuta=$lnNroEvolucion=$lnHoraCitaFinal=$lcHoraInicial5=$lcHoraInicial6=$lcCampoNacit=0;

				$lcHoraInicial1 		= substr(str_pad($laHorario['HINPME'],4,'0',STR_PAD_LEFT),0,2);
				$lcHoraInicial2 		= substr(str_pad($laHorario['HINPME'],4,'0',STR_PAD_LEFT),2,2);
				$lcHoraInicial3 		= $lcHoraInicial1;
				$lcHoraInicial4 		= $lcHoraInicial2;
				$lcHoraFinal1 			= substr(str_pad($laHorario['HFIPME'],4,'0',STR_PAD_LEFT),0,2);
				$lcHoraFinal2 			= substr(str_pad($laHorario['HFIPME'],4,'0',STR_PAD_LEFT),2,2);
				$lnLapsoMinutos			= intval($laHorario['LAPPME']);
				$lnCantidadHora 		= $laHorario['CANPME'];
				$lcMedicoAtiende 		= $laHorario['REGPME'];
				$lcNombreMedico			= $laHorario['MEDICO'];
				$lcCups 				= $laHorario['PROPME'];
				$lcOpcional1 			= $laHorario['OP1PME'];
				$lnHoraInicioContraste 	= $laHorario['OP4PME'];
				$lnHoraFinalContraste 	= $laHorario['OP7PME'];
				$lcCodigoConsultorio 	= $laHorario['OP2PME'];

				for($lnI=1; $lnI<=$lnCantidadHora; $lnI++){
					$lcHoraInicial1 = $lcHoraInicial3;
					$lcHoraInicial2 = $lcHoraInicial4;
					$lnNroEvolucion = $lnNroEvolucion + 1;

					$lbEjecuta = true;
					while($lbEjecuta){
						$lnNroIngreso=$lnConsecCita=$lnNroIdentif=$lnConsecOrden=$lnEstadoCitaInicial=0;
						$lcTipoIdentif=$lcNombrePaciente=$lcCampoSacit=$lcCupsCita='';
						$lnEstadoCita 		= 1;
						$lnHoraCitaFinal 	= intval($lcHoraInicial1 . $lcHoraInicial2);
						$lcHoraInicial5 	= intval($lcHoraInicial3 . $lcHoraInicial4);
						$lcHoraInicial6 	= intval($lcHoraFinal1 . $lcHoraFinal2);
						$lnHoraCita 		= $lnHoraCitaFinal * 100;

						foreach($laCitasEsp as $laCitEsp){
							if(
								$laCitEsp['EVOCIT']==$lnNroEvolucion &&
								$laCitEsp['FRLCIT']==$lnDiaCalendario &&
								$laCitEsp['HOCCIT']==$lnHoraCita &&
								$laCitEsp['RMRCIT']==$lcMedicoAtiende &&
								$laCitEsp['ESCCIT']==$tcUnidadAgenda
							){
								$lcTipoIdentif 		= $laCitEsp['TIDCIT'];
								$lnNroIdentif 		= $laCitEsp['NIDCIT'];
								$lnConsecCita 		= $laCitEsp['CCICIT'];
								$lnNroIngreso 		= $laCitEsp['NINCIT'];
								$lnEstadoCita 		= $laCitEsp['ESTCIT'];
								$lcCampoSacit 		= $laCitEsp['SSACIT']=='S'?'*':'';
								$lcCampoNacit 		= $laCitEsp['NSACIT']=='S'?'°':'';
								$lcNombrePaciente	= trim($lcCampoSacit.' '.$lcCampoNacit.' '.$laCitEsp['PACIENTE']);
								$lcCupsCita 		= $laCitEsp['COACIT'];
								$lnConsecOrden 		= intval($laCitEsp['INSCIT']);
								$lnEstadoCitaInicial= $laCitEsp['ESTCIT'];
								$lcProgramaCrea 	= $laCitEsp['PGMCIT'];

								if($lnEstadoCita==30){$lnEstadoCita=2;}
								if($lnEstadoCita!=3 && in_array($lcProgramaCrea,['CIT014H','CIT014O'])){$lnEstadoCita=5;}

								break;
							}
						}
						$this->aAgendaMed[] = [
							'estado'	=>$lnEstadoCita,
							'unidad'	=>$lnUnidadAgenda,
							'fecha'		=>$lnDiaCalendario,
							'hora'		=>$lnHoraCitaFinal,
							'numdia'	=>$lnDiaSemana,
							'ingreso'	=>$lnNroIngreso,
							'cita'		=>$lnConsecCita,
							'tipoid'	=>$lcTipoIdentif,
							'numid'		=>$lnNroIdentif,
							'paciente'	=>$lcNombrePaciente,
							'cnscant'	=>$lnNroEvolucion,
							'cup'		=>$lcCupsCita,
							'codesp'	=>$lcCodigoEspec,
							'regmed'	=>$lcMedicoAtiende,
							'medico'	=>$lcNombreMedico,
							'lapso'		=>$lnLapsoMinutos,
							'horai'		=>$lcHoraInicial5,
							'horaf'		=>$lcHoraInicial6,
							'consult'	=>$lcCodigoConsultorio,
							'soloat'	=>$lcOpcional1,
							'extra'		=>0,
							'cnsord'	=>$lnConsecOrden,
							'estadoi'	=>$lnEstadoCitaInicial,
							'horaic'	=>$lnHoraInicioContraste,
							'horafc'	=>$lnHoraFinalContraste,
						];

						$lcHoraInicial2 = intval($lcHoraInicial2) + $lnLapsoMinutos;
						while($lcHoraInicial2 >= 60){
							$lcHoraInicial1 = intval($lcHoraInicial1) + 1;
							$lcHoraInicial2 = intval($lcHoraInicial2) - 60;
						}
						$lcHoraInicial2 = str_pad($lcHoraInicial2,2,'0',STR_PAD_LEFT);

						if(intval($lcHoraInicial1 . $lcHoraInicial2) > intval($lcHoraFinal1 . $lcHoraFinal2)){
							$lbEjecuta = false;
						}
					}
				}
			}
			// ***** FIN CUPO NORMAL *****



			// ***** INICIO DIA ADICIONAL *****
			$laWhere = [
				'U.CODEXE'=>$lnUnidadAgenda,
				'U.FEXEXE'=>$lnDiaCalendario,
				'U.DIAEXE'=>$lnDiaSemana,
				'U.ESTEXE'=>$lcEstadoDesbloquea,
				'U.PROEXE'=>$tcCupsPaciente,
				'U.ESAEXE'=>' ',
			];
			if(!empty($tcRegMedico)){$laWhere['REGEXE']=$tcRegMedico;}
			$laExcepciones = $this->oDb
				->select('U.HINEXE, U.HFIEXE, U.LAPEXE, U.CANEXE, U.REGEXE, U.PROEXE, U.OP2EXE')
				->select("IFNULL(TRIM(M.NOMMED)||' '||TRIM(M.NNOMED),'') MEDICO, IFNULL(C.DESCUP,'') DESCUP")
				->from('UNIEXE U')
				->leftJoin('RIARGMN M','U.REGEXE=M.REGMED')
				->leftJoin('RIACUP C','U.PROEXE=C.CODCUP')
				->where($laWhere)
				->orderBy('U.DIAEXE, U.HINEXE')
				->getAll('array');
			if(!is_array($laExcepciones)){$laExcepciones=[];}

			foreach($laExcepciones as $laExcepcion){
				$laExcepcion=array_map('trim',$laExcepcion);

				$lcHoraUnoDosExtra=$lcNombreMedExtra=$lcDescCupsExtra=$lcOpcional1='';
				$lnEvolucionExtra=$lnHoraUnoDosExtra=$lcHoraInicial5=$lcHoraInicial6=0;

				$lcHoraIniExec1 	= substr(str_pad($laExcepcion['HINEXE'],4,'0',STR_PAD_LEFT),0,2);
				$lcHoraIniExec2 	= substr(str_pad($laExcepcion['HINEXE'],4,'0',STR_PAD_LEFT),2,2);
				$lcHoraIniExec3 	= $lcHoraIniExec1;
				$lcHoraIniExec4 	= $lcHoraIniExec2;
				$lcHoraFinalExec1 	= substr(str_pad($laExcepcion['HFIEXE'],4,'0',STR_PAD_LEFT),0,2);
				$lcHoraFinalExec2 	= substr(str_pad($laExcepcion['HFIEXE'],4,'0',STR_PAD_LEFT),2,2);
				$lnLapsoExepcion 	= intval(trim($laExcepcion['LAPEXE']));
				$lnCantidadHoraExtra= $laExcepcion['CANEXE'];
				$lcRegMedExtra 		= $laExcepcion['REGEXE'];
				$lcNombreMedExtra	= $laExcepcion['MEDICO'];
				$lcCupsExtra 		= $laExcepcion['PROEXE'];
				$lcDescCupsExtra	= $laExcepcion['DESCUP'];
				$lcCodigoConsultorio= $laExcepcion['OP2EXE'];

				for($lnI=1; $lnI<=$lnCantidadHoraExtra; $lnI++){
					$lcHoraIniExec1 = trim($lcHoraIniExec3);
					$lcHoraIniExec2 = trim($lcHoraIniExec4);
					$lnEvolucionExtra = $lnEvolucionExtra + 1;

					$lbEjecuta = true;
					while($lbEjecuta){
						$lnNroIngreso=$lnConsecCita=$lnNroIdentif=$lnConsecOrden=$lnEstadoCitaInicial=0;
						$lcTipoIdentif=$lcNombrePaciente=$lcCampoSacit=$lcCupsCita=$lcOpcional1='';
						$lnEstadoCita 		= 1;
						$lcHoraUnoDosExtra 	= $lcHoraIniExec1.$lcHoraIniExec2;
						$lnHoraUnoDosExtra 	= intval($lcHoraUnoDosExtra);
						$lcHoraInicial5 	= intval($lcHoraIniExec3.$lcHoraIniExec4);
						$lcHoraInicial6 	= intval($lcHoraFinalExec1.$lcHoraFinalExec2);
						$lnHoraCita 		= intval($lnHoraUnoDosExtra.'00');

						foreach($laCitasEsp as $laCitEsp){
							if(
								$laCitEsp['EVOCIT']==$lnEvolucionExtra &&
								$laCitEsp['FRLCIT']==$lnDiaCalendario &&
								$laCitEsp['HOCCIT']==$lnHoraCita &&
								$laCitEsp['RMRCIT']==$lcRegMedExtra &&
								$laCitEsp['ESCCIT']==$tcUnidadAgenda
							){
								$lcTipoIdentif 		= $laCitEsp['TIDCIT'];
								$lnNroIdentif 		= $laCitEsp['NIDCIT'];
								$lnConsecCita 		= $laCitEsp['CCICIT'];
								$lnNroIngreso 		= $laCitEsp['NINCIT'];
								$lnEstadoCita 		= $laCitEsp['ESTCIT'];
								$lcCampoSacit 		= $laCitEsp['SSACIT']=='S'?'*':'';
								$lcNombrePaciente	= trim($lcCampoSacit.' '.$laCitEsp['PACIENTE']);
								$lcCupsCita 		= $laCitEsp['COACIT'];
								$lnConsecOrden 		= intval($laCitEsp['INSCIT']);
								$lnEstadoCitaInicial= $laCitEsp['ESTCIT'];
								$lcProgramaCrea 	= $laCitEsp['PGMCIT'];

								if($lnEstadoCita==30){$lnEstadoCita=2;}
								if($lnEstadoCita!=3 && in_array($lcProgramaCrea,['CIT014H','CIT014O'])){$lnEstadoCita=5;}

								break;
							}
						}

						$this->aAgendaMed[] = [
							'estado'	=>$lnEstadoCita,
							'unidad'	=>$lnUnidadAgenda,
							'fecha'		=>$lnDiaCalendario,
							'hora'		=>$lnHoraUnoDosExtra,
							'numdia'	=>$lnDiaSemana,
							'ingreso'	=>$lnNroIngreso,
							'cita'		=>$lnConsecCita,
							'tipoid'	=>$lcTipoIdentif,
							'numid'		=>$lnNroIdentif,
							'paciente'	=>$lcNombrePaciente,
							'cnscant'	=>$lnEvolucionExtra,
							'cup'		=>$lcCupsCita,
							'codesp'	=>$lcCodigoEspec,
							'regmed'	=>$lcRegMedExtra,
							'medico'	=>$lcNombreMedExtra,
							'lapso'		=>$lnLapsoExepcion,
							'horai'		=>$lcHoraInicial5,
							'horaf'		=>$lcHoraInicial6,
							'consult'	=>$lcCodigoConsultorio,
							'soloat'	=>$lcOpcional1,
							'extra'		=>0,
							'cnsord'	=>$lnConsecOrden,
							'estadoi'	=>$lnEstadoCitaInicial,
							'horaic'	=>0,
							'horafc'	=>0,
						];

						$lcHoraIniExec2 = intval($lcHoraIniExec2) + $lnLapsoExepcion;
						if($lcHoraIniExec2=60){
							$lcHoraIniExec1 = strval(intval($lcHoraIniExec1) + 1);
							$lcHoraIniExec2 = '00';
						}
						if(intval($lcHoraIniExec2) > 60){
							$lcHoraIniExec1 = intval($lcHoraIniExec1) + 1;
							$lcHoraIniExec2 = str_pad(intval($lcHoraIniExec2) - 60,2,'0',STR_PAD_LEFT);
						}
						if(intval($lcHoraIniExec1 . $lcHoraIniExec2) > intval($lcHoraFinalExec1 . $lcHoraFinalExec2)){
							$lbEjecuta = false;
						}
					}
				}
			}
			// ***** FIN DIA ADICIONAL *****
		}

		return ['fechahora'=>date('Y-m-d H:i:s'), 'success'=>true];
	}


	public function agendaCitasExtra()
	{
		$lcFiltroCursor='';

		$laCitasExt=$laCiEx=[];
		foreach ($this->aAgendaMed as $laAgeMed) {
			$lcKey=$laAgeMed['unidad'].'-'.$laAgeMed['fecha'].'-'.$laAgeMed['regmed'];
			if(in_array($lcKey, $laCiEx)) continue;
			$laCitasExt[]=[
				'unidad'=>$laAgeMed['unidad'],
				'fecha' =>$laAgeMed['fecha'],
				'regmed'=>$laAgeMed['regmed'],
			];
			$laCiEx[]=$lcKey;
		}
		unset($laCiEx);

		foreach ($laCitasExt as $laCitaExt) {
			$lnConsecCita=$lnNroIdent=$lnConsCantidad=$lnHoraCita=0;
			$lcTipoIdent=$lcNombreMedico=$lcConsultorio=$lcEstadoExtra='';
			$lnUnidadAgenda	= $laCitaExt['unidad'];
			$lnFechaAgenda	= $laCitaExt['fecha'];
			$lcRegMedico	= $laCitaExt['regmed'];
			$ldFechaAgenda	= \DateTime::createFromFormat('Ymd',$lnFechaAgenda);
			$lcDiaSemana	= strval($ldFechaAgenda->format('N')+1);
			if($lcDiaSemana=='8') $lcDiaSemana='1';

			if(empty($lnUnidadAgenda)) continue;

			//	Filtro por unidad de agenda y fecha
			$laWhere = [
				'UNIEXT'=>$lnUnidadAgenda,
				'FEEEXT'=>$lnFechaAgenda,
				'ESTEXT'=>$lcEstadoExtra,
			];
			//	Filtro por medico
			if(!empty($lcRegMedico)){
				$laWhere['REGEXT'] = $lcRegMedico;
			}

			$laUniExt = $this->oDb
				->select('U.TIDEXT, U.NIDEXT, U.CCIEXT, U.HOEEXT, U.CUPEXT, U.PROEXT, U.REGEXT')
				->select("IFNULL(TRIM(M.NOMMED)||' '||TRIM(M.NNOMED),'') MEDICO, IFNULL(M.CODRGM,'') CODESP")
				->select("IFNULL(TRIM(P.NM1PAC)||' '||TRIM(P.NM2PAC)||' '||TRIM(P.AP1PAC)||' '||TRIM(P.AP2PAC),'') PACIENTE")
				->from('UNIEXT U')
				->leftJoin('RIARGMN M','U.REGEXT=M.REGMED')
				->leftJoin('RIAPAC P','U.TIDEXT=P.TIDPAC AND U.NIDEXT=P.NIDPAC')
				->where($laWhere)
				->orderBy('U.FEEEXT')
				->getAll('array');
			if(!is_array($laUniExt)) $laUniExt=[];

			foreach ($laUniExt as $laUniE) {
				$laUniE=array_map('trim',$laUniE);
				$lnIngreso=$lnInstitucion=$lnEstadoCita=0;
				$lcProgramaCrea	='';
				$lnEstadoAgenda	= 2;
				$lcTipoIdent	= $laUniE['TIDEXT'];
				$lnNroIdent		= $laUniE['NIDEXT'];
				$lcPaciente		= $laUniE['PACIENTE'];
				$lnConsecCita	= $laUniE['CCIEXT'];
				$lnHoraCita		= intval(substr(str_pad($laUniE['HOEEXT'],6,'0',STR_PAD_LEFT),0,4));
				$lnConsCantidad	= $laUniE['CUPEXT'];
				$lcCups			= $laUniE['PROEXT'];
				$lcRegistroMed	= $laUniE['REGEXT'];
				$lcNombreMedico	= $laUniE['MEDICO'];
				$lcCodigoEspec	= $laUniE['CODESP'];

				// Buscar datos cita
				$laWhere=[
					'TIDCIT'=>$lcTipoIdent,
					'NIDCIT'=>$lnNroIdent,
					'CCICIT'=>$lnConsecCita,
					'RMRCIT'=>$lcRegistroMed,
					'ESCCIT'=>$lnUnidadAgenda,
				];
				$laCitaPac=$this->oDb
					->select('NINCIT,ESTCIT,INSCIT,CODCIT,PGMCIT')
					->from('RIACIT')
					->where($laWhere)
					->get('array');

				if($this->oDb->numRows()>0){
					$laCitaPac=array_map('trim',$laCitaPac);
					$lnIngreso		= $laCitaPac['NINCIT'];
					$lnEstadoAgenda	= $laCitaPac['ESTCIT']=='30' ? 2 : intval($laCitaPac['ESTCIT']);
					$lnInstitucion	= intval($laCitaPac['INSCIT']);
					$lnEstadoCita	= $laCitaPac['ESTCIT'];
					$lcProgramaCrea	= $laCitaPac['PGMCIT'];
					if(empty($lcCodigoEspec)) $lcCodigoEspec=$laCitaPac['CODCIT'];
				}
				if($lnEstadoAgenda!==3 && in_array($lcProgramaCrea,['CIT014H','CIT014O'])) $lnEstadoAgenda=5;

				// Consultorio
				$laCons=$this->oDb
					->select('OP2PME')
					->from('UNIPME')
					->where([
						'CODPME'=>$lnUnidadAgenda,
						'REGPME'=>$lcRegistroMed,
						'DIAPME'=>$lcDiaSemana,
						'ESTPME'=>' ', // UNIPMEL02
					])
					->get('array');
				if($this->oDb->numRows()>0){
					$lcConsultorio=trim($laCons['OP2PME']);
				}
				$this->aAgendaMed[] = [
					'estado'	=>$lnEstadoAgenda,
					'unidad'	=>$lnUnidadAgenda,
					'fecha'		=>$lnFechaAgenda,
					'hora'		=>$lnHoraCita,
					'numdia'	=>$lcDiaSemana,
					'ingreso'	=>$lnIngreso,
					'cita'		=>$lnConsecCita,
					'tipoid'	=>$lcTipoIdent,
					'numid'		=>$lnNroIdent,
					'paciente'	=>$lcPaciente,
					'cnscant'	=>$lnConsCantidad,
					'cup'		=>$lcCups,
					'codesp'	=>$lcCodigoEspec,
					'regmed'	=>$lcRegistroMed,
					'medico'	=>$lcNombreMedico,
					'lapso'		=>0,
					'horai'		=>0,
					'horaf'		=>0,
					'consult'	=>$lcConsultorio,
					'soloat'	=>'',
					'extra'		=>1,
					'cnsord'	=>$lnInstitucion,
					'estadoi'	=>$lnEstadoCita,
					'horaic'	=>0,
					'horafc'	=>0,
				];
			}
		}

		return ['fechahora'=>date('Y-m-d H:i:s'), 'success'=>true];
	}


	public function agendaCitasbloqueadas($tnFechaIni, $tnFechaFin, $tcUnidadAgenda, $tcRegMedico='')
	{
		$lnLapsoHorario=$lnHoraInicial2=$lnHoraFinal2=$lnConsOrden=0;
		$lcTipoUnidad=$lcCodConsultorio='';

		// Tipo Unidad Agenda
		$laUniAge=$this->oDb->select('COPUNA')->from('UNIAGE')->where(['CODUNA'=>$tcUnidadAgenda])->get('array');
		if($this->oDb->numRows()>0){
			$lcTipoUnidad=trim($laUniAge['COPUNA']);
		}


		// ***   PARTE I   ***
		//	HORARIOS INACTIVOS

		$laWhere=['CODPME'=>$tcUnidadAgenda];
		if(!empty($tcRegMedico)){
			$laWhere['REGPME']=$tcRegMedico;
		}
		$laHorarios=$this->oDb->select('REGPME')->from('UNIPME')->where($laWhere)->groupBy('REGPME')->orderBy('REGPME')->getAll('array');
		if(!is_array($laHorarios)) $laHorarios=[];

		foreach ($laHorarios as $laHorario) {
			$lcRegMedico = trim($laHorario['REGPME']);
			$laUnipmes=$this->oDb
				->select('DIAPME,HINPME,HFIPME,FDEPME,FHAPME,LAPPME,OP2PME')
				->from('UNIPME')
				->where([
					'CODPME'=>$tcUnidadAgenda,
					'REGPME'=>$lcRegMedico
				])
				->where("ESTPME<>''")
				->getAll('array');
			if(!is_array($laUnipmes)) $laUnipmes=[];

			foreach ($laUnipmes as $laUnipme) {
				$laUnipme=array_map('trim',$laUnipme);
				$lcDiaSemana 		= $laUnipme['DIAPME'];
				$lnHoraInicial 		= intval($laUnipme['HINPME'].'00');
				$lnHoraFinal 		= intval($laUnipme['HFIPME'].'00');
				$lnHoraInicial2 	= intval($laUnipme['HINPME']);
				$lnHoraFinal2 		= intval($laUnipme['HFIPME']);
				$lnFechaDesde 		= intval($laUnipme['FDEPME']);
				$lnFechaHasta 		= intval($laUnipme['FHAPME']);
				$lnLapsoHorario 	= intval($laUnipme['LAPPME']);
				$lcCodConsultorio	= $laUnipme['OP2PME'];

				// Datos de la cita
				$laCitas=$this->oDb
					->select('TIDCIT,NIDCIT,NINCIT,CCICIT,EVOCIT,COACIT,INSCIT,FRLCIT,HOCCIT')
					->select("IFNULL(TRIM(NOMMED)||' '||TRIM(NNOMED),'') MEDICO, IFNULL(CODRGM,'') CODRGM")
					->from('RIACIT')
					->leftJoin('RIARGMN','REGMED=RMRCIT')
					->between('FRLCIT',$tnFechaIni,$tnFechaFin)
					->where([
						'RMRCIT'=>$lcRegMedico,
						'ESCCIT'=>$tcUnidadAgenda
					])
					->in('ESTCIT', [2, 8, 30])
					->getAll('array');
				if(!is_array($laCitas)) $laCitas=[];

				foreach ($laCitas as $laCita) {
					$laCita=array_map('trim',$laCita);
					$lcNombreMedico=$lcCodEspec='';
					$ldFechaCita = \DateTime::createFromFormat('Ymd', $laCita['FRLCIT']);
					$lnDiaCitaSemana= $ldFechaCita->format('N')+1;
					if($lnDiaCitaSemana==8) $lnDiaCitaSemana=1;
					$lcTipIdentif	= $laCita['TIDCIT'];
					$lnNroIdentif	= $laCita['NIDCIT'];
					$lnConsecCita	= $laCita['CCICIT'];
					$lnConsecEvoluc	= $laCita['EVOCIT'];
					$lnNroIngreso	= $laCita['NINCIT'];
					$lnFechaCita	= intval($laCita['FRLCIT']);
					$lnHoraCita		= $laCita['HOCCIT'];
					$lcCodCups		= $laCita['COACIT'];
					$lnConsOrden	= intval($laCita['INSCIT']);
					$lnHoraCitaLarga= intval(substr(str_pad($laCita['HOCCIT'],6,'0',STR_PAD_LEFT),0,4));
					$lcNombreMedico	= $laCita['MEDICO'];
					$lcCodEspec		= $laCita['CODRGM'];

					if($lnFechaCita>=$lnFechaDesde && $lnFechaCita<=$lnFechaHasta){
						if(intval($lcDiaSemana)==$lnDiaCitaSemana){
							if($lnHoraCita>=$lnHoraInicial && $lnHoraCita<=$lnHoraFinal){

								foreach ($this->aAgendaMed as &$laAgeMed) {
									if($laAgeMed['numid']==$lnNroIdentif && $laAgeMed['fecha']==$lnFechaCita && $laAgeMed['hora']==$lnHoraCita){
										$laAgeMed=array_merge($laAgeMed, [
											'estado'	=> 9,
											'unidad'	=> $tcUnidadAgenda,
											'fecha'		=> $lnFechaCita,
											'hora'		=> $lnHoraCitaLarga,
											'numdia'	=> $lnDiaCitaSemana,
											'ingreso'	=> $lnNroIngreso,
											'cita'		=> $lnConsecCita,
											'tipoid'	=> $lcTipIdentif,
											'numid'		=> $lnNroIdentif,
											'cnscant'	=> $lnConsecEvoluc==0 ? 1 : $lnConsecEvoluc,
											'cup'		=> $lcCodCups,
											'codesp'	=> $lcCodEspec,
											'regmed'	=> $lcRegMedico,
											'medico'	=> $lcNombreMedico,
											'lapso'		=> $lnLapsoHorario,
											'horai'		=> $lnHoraInicial2,
											'horaf'		=> $lnHoraFinal2,
											'consult'	=> $lcCodConsultorio,
											'cnsord'	=> $lnConsOrden,
										]);
										break;
									}
								}
								unset($laAgeMed);
							}
						}
					}
				}
			}
		}


		if ($lcTipoUnidad!=='C'){
			return ['fechahora'=>date('Y-m-d H:i:s'), 'success'=>true];
		}


		// ***   PARTE II   ***
		//	PACIENTES CON CITA Y YA NO ESTAN DENTRO DEL HORARIO DE LA UNIDAD DE AGENDA

		foreach ($laHorarios as $laHorario) {
			$lcRegMedico = trim($laHorario['REGPME']);

			// Datos de la cita
			$laCitas=$this->oDb
				->select('C.TIDCIT,C.NIDCIT,C.NINCIT,C.CCICIT,C.EVOCIT,C.COACIT,C.INSCIT,C.FRLCIT,C.HOCCIT')
				->select("IFNULL(TRIM(NOMMED)||' '||TRIM(NNOMED),'') MEDICO, IFNULL(CODRGM,'') CODRGM")
				->select("IFNULL(TRIM(P.NM1PAC)||' '||TRIM(P.NM2PAC)||' '||TRIM(P.AP1PAC)||' '||TRIM(P.AP2PAC),'') AS PACIENTE")
				->from('RIACIT C')
				->leftJoin('RIAPAC P', 'C.TIDCIT=P.TIDPAC AND C.NIDCIT=P.NIDPAC')
				->leftJoin('RIARGMN M','M.REGMED=C.RMRCIT')
				->between('C.FRLCIT',$tnFechaIni,$tnFechaFin)
				->where([
					'C.RMRCIT'=>$lcRegMedico,
					'C.ESCCIT'=>$tcUnidadAgenda
				])
				->in('C.ESTCIT',[2, 8, 30])
				->orderBy('C.FRLCIT, C.RMRCIT')
				->getAll('array');
			if(!is_array($laCitas)) $laCitas=[];

			foreach ($laCitas as $laCita) {
				$laCita=array_map('trim',$laCita);
				$lnHoraParamFinal=$lnIndicadorCita=0;
				$lcTipIdentif 	= $laCita['TIDCIT'];
				$lnNroIdentif 	= $laCita['NIDCIT'];
				$lcPaciente 	= $laCita['PACIENTE'];
				$lnConsecCita 	= $laCita['CCICIT'];
				$lnNroIngreso 	= $laCita['NINCIT'];
				$lnConsecEvoluc = $laCita['EVOCIT'];
				$lnFechaCita 	= $laCita['FRLCIT'];
				$lnHoraCita 	= $laCita['HOCCIT'];
				$lcCodCups 		= $laCita['COACIT'];
				$lnConsOrden 	= intval($laCita['INSCIT']);
				$lnHoraCitaLarga= intval(substr(str_pad($laCita['HOCCIT'],6,'0',STR_PAD_LEFT),0,4));
				$ldFechaCita 	= \DateTime::createFromFormat('Ymd', $lnFechaCita);
				$lcDiaSemana	= $ldFechaCita->format('N')+1;
				if($lcDiaSemana==8) $lcDiaSemana=1;

				//	PARAMETROS HORARIOS
				$laParams=$this->oDb
					->select('HINPME,HFIPME,LAPPME,OP2PME')
					->from('UNIPME')
					->where([
						'CODPME'=>$tcUnidadAgenda,
						'REGPME'=>$lcRegMedico,
						'DIAPME'=>$lcDiaSemana,
						'ESTPME'=>' ',
					])
					->where("$lnFechaCita BETWEEN FDEPME AND FHAPME")
					->getAll('array');
				if(!is_array($laParams)) $laParams=[];

				foreach ($laParams as $laParam) {
					$laParam = array_map('trim',$laParam);
					$lnHoraInicial2 	= intval($laParam['HINPME']);
					$lnHoraFinal2 		= intval($laParam['HFIPME']);
					$lnHoraParamInicial = $lnHoraInicial2*100;
					$lnHoraParamFinal 	= $lnHoraFinal2*100;
					$lnLapsoHorario 	= intval($laParam['LAPPME']);
					$lcCodConsultorio 	= $laParam['OP2PME'];

					if($lnIndicadorCita==0){
						if($lnHoraCita>=$lnHoraParamInicial && $lnHoraCita<=$lnHoraParamFinal){
							$lnIndicadorCita=1;
							break;
						}
					}
				}
				if($lnIndicadorCita==1) continue;

				// Verifica si es cita extra
				$laCitaExt=$this->oDb->select('TIDEXT')->from('UNIEXT')->where(['TIDEXT'=>$lcTipIdentif,'NIDEXT'=>$lnNroIdentif,'CCIEXT'=>$lnConsecCita])->getAll('array');
				if($this->oDb->numRows()>0) continue;

				// Verifica si es desbloqueo
				$laDesbl=$this->oDb->select('CODEXE')->from('UNIEXE')->where(['REGEXE'=>$lcRegMedico,'FEXEXE'=>$lnFechaCita,'ESTEXE'=>'D'])->getAll('array');
				if($this->oDb->numRows()>0) continue;

				//if($lnIndicadorCita==0 && $lcTipoUnidad=='C'){
					$lcNombreMedico		= $laCita['MEDICO'];
					$lcCodEspec			= $laCita['CODRGM'];

					$lbFound=false;
					foreach ($this->aAgendaMed as &$laAgeMed) {
						if($laAgeMed['numid']==$lnNroIdentif && $laAgeMed['fecha']==$lnFechaCita && $laAgeMed['hora']==$lnHoraCita){
							$laAgeMed=array_merge($laAgeMed, [
								'estado'	=> 9,
								'unidad'	=> $tcUnidadAgenda,
								'fecha'		=> $lnFechaCita,
								'hora'		=> $lnHoraCitaLarga,
								'numdia'	=> intval($lcDiaSemana),
								'ingreso'	=> $lnNroIngreso,
								'cita'		=> $lnConsecCita,
								'tipoid'	=> $lcTipIdentif,
								'numid'		=> $lnNroIdentif,
								'paciente'	=> $lcPaciente,
								'cnscant'	=> $lnConsecEvoluc==0 ? 1 : $lnConsecEvoluc,
								'cup'		=> $lcCodCups,
								'codesp'	=> $lcCodEspec,
								'regmed'	=> $lcRegMedico,
								'medico'	=> $lcNombreMedico,
								'lapso'		=> $lnLapsoHorario,
								'horai'		=> $lnHoraInicial2,
								'horaf'		=> $lnHoraFinal2,
								'consult'	=> $lcCodConsultorio,
								'cnsord'	=> $lnConsOrden,
							]);
							$lbFound=true;
							break;
						}
					}
					unset($laAgeMed);

					if(!$lbFound){
						$laAgeMedNF=[
							'estado'	=> 9,
							'unidad'	=> $tcUnidadAgenda,
							'fecha'		=> $lnFechaCita,
							'hora'		=> $lnHoraCitaLarga,
							'numdia'	=> intval($lcDiaSemana),
							'ingreso'	=> $lnNroIngreso,
							'cita'		=> $lnConsecCita,
							'tipoid'	=> $lcTipIdentif,
							'numid'		=> $lnNroIdentif,
							'paciente'	=> $lcPaciente,
							'cnscant'	=> $lnConsecEvoluc==0 ? 1 : $lnConsecEvoluc,
							'cup'		=> $lcCodCups,
							'codesp'	=> $lcCodEspec,
							'regmed'	=> $lcRegMedico,
							'medico'	=> $lcNombreMedico,
							'lapso'		=> $lnLapsoHorario,
							'horai'		=> $lnHoraInicial2,
							'horaf'		=> $lnHoraFinal2,
							'consult'	=> $lcCodConsultorio,
							'soloat'	=> '',
							'extra'		=> 0,
							'cnsord'	=> $lnConsOrden,
							'estadoi'	=> 0,
							'horaic'	=> 0,
							'horafc'	=> 0,
						];
						$this->aAgendaMed[]=$laAgeMedNF;
					}
				//}
			}
		}

		return ['fechahora'=>date('Y-m-d H:i:s'), 'success'=>true];
	}


	public function agendaDatosDia()
	{
		$laDiasSem = ($this->consultarParametros('diassemana'))['datos'];
		foreach ($this->aAgendaMed as &$laAgeMed) {

			$lnHoraIniBloqueo=$lnHoraFinBloqueo=0;
			$lcDescDiaSemana=$lcNombrePaciente=$lclcDescEspecialidad=$lcDescCups='';
			$lcBloqueado			= 'B';
			$lnEstadoCita			= $laAgeMed['estado'];
			$lnUnidadAgenda			= $laAgeMed['unidad'];
			$lnFechaExepcion		= $laAgeMed['fecha'];
			$lnHoraCita				= intval($laAgeMed['hora']);
			$lcRegistroMedico		= $laAgeMed['regmed'];

			// Excepción
			$laExcep=$this->oDb
				->select('HINEXE,HFIEXE')
				->from('UNIEXE')
				->where([
					'CODEXE'=>$lnUnidadAgenda,
					'REGEXE'=>$lcRegistroMedico,
					'FEXEXE'=>$lnFechaExepcion,
					'ESTEXE'=>$lcBloqueado,
					'ESAEXE'=>' ',
				])
				->get('array');
			if($this->oDb->numRows()>0){
				$lnHoraIniBloqueo = intval($laExcep['HINEXE']);
				$lnHoraFinBloqueo = intval($laExcep['HFIEXE']);
				if($lnHoraIniBloqueo>0 && $lnHoraFinBloqueo>0){
					if($lnHoraCita>=$lnHoraIniBloqueo && $lnHoraCita<=$lnHoraFinBloqueo){
						$laAgeMed['estado']=9;
					}
				}else{
					$laAgeMed['estado']=9;
				}
			}

			// Nombres y apellidos de paciente y médico ya se consultaron en las otras funciones
			// NO retornan descripciones de CUP, Especialidad, día semana, consultorio
		}
		unset($laAgeMed);

		return ['fechahora'=>date('Y-m-d H:i:s'), 'success'=>true];
	}


	public function agendaFueraDeHorario($tnFechaIni, $tnFechaFin, $tcUnidadAgenda, $tcRegMedico)
	{
		$lnEstadoCita=9;
		$laWhere['C.ESCCIT']=$tcUnidadAgenda;
		if(strlen($tcRegMedico)>0){
			$laWhere['C.RMRCIT']=$tcRegMedico;
		}

		// Datos de la cita
		$laCitas=$this->oDb
			->select('C.TIDCIT,C.NIDCIT,C.NINCIT,C.CCICIT,C.FRLCIT,C.HOCCIT,C.COACIT,C.CODCIT,C.RMRCIT,C.INSCIT,C.ESTCIT,C.EVOCIT')
			->select("IFNULL(TRIM(P.NM1PAC)||' '||TRIM(P.NM2PAC)||' '||TRIM(P.AP1PAC)||' '||TRIM(P.AP2PAC), '') PACIENTE")
			->select("IFNULL(TRIM(M.NOMMED)||' '||TRIM(M.NNOMED), '') MEDICO")
			->from('RIACIT C')
			->leftJoin('RIAPAC  P', 'P.TIDPAC=C.TIDCIT AND P.NIDPAC=C.NIDCIT')
			->leftJoin('RIARGMN M', 'M.REGMED=C.RMRCIT')
			->between('FRLCIT',$tnFechaIni,$tnFechaFin)
			->where($laWhere)
			->in('C.ESTCIT',[2, 8, 30])
			->orderBy('C.FRLCIT, C.HOCCIT')
			->getAll('array');
		if(!is_array($laCitas)) $laCitas=[];

		foreach ($laCitas as $laCita) {
			$laCita=array_map('trim',$laCita);
			$lcTipoIdentif 		= $laCita['TIDCIT'];
			$lnNroIdentif 		= $laCita['NIDCIT'];
			$lnConsecCita 		= $laCita['CCICIT'];

			$lbFound=false;
			foreach ($this->aAgendaMed as $laAgeMed) {
				if($laAgeMed['tipoid']=$lcTipoIdentif && $laAgeMed['numid']==$lnNroIdentif && $laAgeMed['cita']==$lnConsecCita){
					$lbFound=true;
					break;
				}
			}
			if(!$lbFound){
				$lcCodigoConsultorio=$lcOpcional1='';
				$lnHoraInicio=$lnHoraFinal=$lnLapsoHorario=0;
				$lnConsecEvolucion 	= $laCita['EVOCIT'];
				$lnFechaCita 		= $laCita['FRLCIT'];
				$lnHoraCita 		= intval($laCita['HOCCIT']/100);
				$lnNroIngreso 		= $laCita['NINCIT'];
				$lcCodigoCups 		= $laCita['COACIT'];
				$lcCodigoEspec 		= $laCita['CODCIT'];
				$lcRegMedico 		= $laCita['RMRCIT'];
				$lnConsecOrden 		= $laCita['INSCIT'];
				$lnEstadoCitaActual = $laCita['ESTCIT'];
				$lnDiaSemana		= $this->diaSemana($lnFechaCita);
				$lcNombrePaciente	= $laCita['PACIENTE'];
				$lcNombreMedico 	= $laCita['MEDICO'];

				$laHorario=$this->oDb
					->select('LAPPME,HINPME,HFIPME,OP1PME,OP2PME')
					->from('UNIPME')
					->where([
						'CODPME'=>$tcUnidadAgenda,
						'REGPME'=>$lcRegMedico,
						'DIAPME'=>$lnDiaSemana,
					])
					->get('array');
				if($this->oDb->numRows()>0){
					$laHorario=array_map('trim', $laHorario);
					$lnLapsoHorario		= intval($laHorario['LAPPME']);
					$lnHoraInicio		= intval($laHorario['HINPME']);
					$lnHoraFinal		= intval($laHorario['HFIPME']);
					$lcCodigoConsultorio= $laHorario['OP2PME'];
					$lcOpcional1		= $laHorario['OP1PME'];
				}

				$this->aAgendaMed[]=[
					'estado'	=> $lnEstadoCita,
					'unidad'	=> $tcUnidadAgenda,
					'fecha'		=> $lnFechaCita,
					'hora'		=> $lnHoraCita,
					'numdia'	=> $lnDiaSemana,
					'ingreso'	=> $lnNroIngreso,
					'cita'		=> $lnConsecCita,
					'tipoid'	=> $lcTipoIdentif,
					'numid'		=> $lnNroIdentif,
					'paciente'	=> $lcNombrePaciente,
					'cnscant'	=> $lnConsecEvolucion,
					'cup'		=> $lcCodigoCups,
					'codesp'	=> $lcCodigoEspec,
					'regmed'	=> $lcRegMedico,
					'medico'	=> $lcNombreMedico,
					'lapso'		=> $lnLapsoHorario,
					'horai'		=> $lnHoraInicio,
					'horaf'		=> $lnHoraFinal,
					'consult'	=> $lcCodigoConsultorio,
					'soloat'	=> $lcOpcional1,
					'extra'		=> 0,
					'cnsord'	=> $lnConsecOrden,
					'estadoi'	=> $lnEstadoCitaActual,
					'horaic'	=> 0,
					'horafc'	=> 0,
				];
			}
		}

		return ['fechahora'=>date('Y-m-d H:i:s'), 'success'=>true];
	}


	public function agendaValidarHora()
	{
		foreach ($this->aAgendaMed as &$laAgeMed) {
			if ($laAgeMed['estado']==1 && $laAgeMed['horaic']>0) {
				if ($laAgeMed['hora']>=$laAgeMed['horaic'] && $laAgeMed['hora']<=$laAgeMed['horafc']) {
					$laAgeMed['estado']=4;
				}
			}
		}
		unset($laAgeMed);

		return ['fechahora'=>date('Y-m-d H:i:s'), 'success'=>true];
	}


	public function agendaTerapiaFisica()
	{
		foreach ($this->aAgendaMed as &$laAgeMed) {
			if($laAgeMed['estado']=='1'){
				$laTemp=$this->oDb
					->select('NINCIT')
					->from('RIACIT')
					->where("ESTCIT IN (2,3,8,30) AND SSACIT='S' AND VIACIT='02'")	// RIACITL12
					->where([
						'FRLCIT'=>$laAgeMed['fecha'],
						'HOCCIT'=>$laAgeMed['hora']*100,
						'RMRCIT'=>$laAgeMed['regmed'],
					])
					->getAll();
				if($this->oDb->numRows()>0){
					$laAgeMed['paciente']='*';
				}
			}
		}
		unset($laAgeMed);

		return ['fechahora'=>date('Y-m-d H:i:s'), 'success'=>true];
	}


	/*
	 *	Consulta de parámetros de agendamiento
	 *	@param string $tcTipo: tipo de parámetro a consultar
	 *	@param string $tcParam: dato acidional del parámetro, requerido solo en algunas consultas
	 *	@return array con los datos del tipo de parámetro solicitado
	 */
	public function consultarParametros($tcTipo, $tcParam='')
	{
		$laRta = [];
		$lcMsg = '';
		switch($tcTipo){

			// Tipos de documento paciente
			case 'tiposid':
				$lcMsg='No se pudieron consultar los tipos de documento.';
				$laDatos = $this->oDb
					->select('TIPDOC TIPO_ID, TRIM(DOCUME) TIPO_DOC, TRIM(DESDOC) DESCRIPCION')
					->from('RIATI')
					->getAll('array');
				break;

			// Unidades de agenda
			case 'unidades_agenda':
				$lcMsg='No se pudieron consultar las unidades de agenda.';
				$laDatos = $this->oDb
					->select("CODUNA CODIGO, TRIM(COPUNA) TIPO, TRIM(DESUNA) UNIDAD, TRIM(ESPUNA) ESPECIALIDAD, IFNULL(CL2TMA,'NO') PESO")
					->from('UNIAGE')
					->leftJoin('TABMAE', "TIPTMA='UNIFNA' AND CL1TMA='CITFNA' AND CL2TMA=CODUNA AND ESTTMA=''")
					->where(['ESTUNA'=>''])
					->orderBy('DESUNA')
					->getAll('array');
				if (is_array($laDatos)) {
					foreach ($laDatos as &$laDato) {
						if ($laDato['PESO']!=='NO') $laDato['PESO']='SI';
					}
				}
				break;

			// Planes
			case 'planes':
				$lcMsg='No se pudieron consultar los planes.';
				$laDatos = $this->oDb
					->select('TRIM(PLNCON) CODIGO, TRIM(DSCCON) PLAN, TENCON TIPO, NI1CON ENTIDAD')
					->from('FACPLNC')
					->where("DSCCON<>'' AND PLNCON<>'' AND ESTCON='A'")
					->orderBy('DSCCON')
					->getAll('array');
				break;

			// Procedimientos
			case 'procedimientos':
				$lcMsg='No se pudieron consultar los procedimientos.';
				$laDatos = $this->oDb
					->select('TRIM(R.CODCUP) CUP, TRIM(R.DESCUP) DESCRIPCION, CASE WHEN R.RF5CUP=\'NOPB\' THEN \'N\' ELSE \'P\' END POS')
					->from('RIACUP R')
					->innerJoin('UNICON U', 'R.CODCUP=U.PROUCO')
					->where("R.IDDCUP='0' AND R.RIPCUP<>'P'")
					->orderBy('R.DESCUP')
					->getAll('array');
				break;

			// Procedimientos por especialidad
			case 'procedimientosxespecialidad':
				$lcMsg='No se pudieron consultar los procedimientos por especialidad.';
				$laDatos = $this->oDb
					->select('TRIM(CODCUP) CUP, TRIM(DESCUP) DESCRIPCION')
					->from('RIACUP')
					->where("IDDCUP='0' AND RIPCUP<>'P' AND CODCUP LIKE '8903%' AND ESPCUP='{$tcParam}'")
					->orderBy('DESCUP')
					->getAll('array');
				break;

			// Procedimientos por unidad de agenda
			case 'procedimientosxagenda':
				$lcMsg='No se pudieron consultar los procedimientos por unidad de agenda.';
				$laDatos = $this->oDb->distinct()
					->select("U.REGUCO REGMEDICO, TRIM(M.NOMMED)||' '||TRIM(M.NNOMED) MEDICO, TRIM(U.PROUCO) CUP, TRIM(C.DESCUP) DESCRIPCION")
					->from('UNICON U')
					->innerJoin('RIACUP  C', 'U.PROUCO=C.CODCUP')
					->innerJoin('RIARGMN M', 'U.REGUCO=M.REGMED')
					->where("U.CODUCO='{$tcParam}' AND U.ESTUCO=''")
					->orderBy('1,3')
					->getAll('array');
				break;

			// Preparación por Procedimientos
			case 'preparacion':
				$lcMsg='No se pudo consultar la preparación del procedimiento.';
				$laData = $this->oDb
					->select("D.CODAVI, D.DESAVI")
					->from('UNIAVI D')
					->innerJoin('UNIAPR C', 'C.CODAPR=D.CODAVI')
					->where("C.CUPAPR='{$tcParam}' AND C.ESTAPR=0")
					->orderBy('D.CODAVI, D.CLIAVI')
					->getAll('array');
				if ($this->oDb->numRows()>0) {
					$laDatos = [
						'cup' => $tcParam,
						'preparacion' => [],
					];
					foreach ($laData as $laFila) {
						if (!isset($laDatos['preparacion'][$laFila['CODAVI']])) {
							$laDatos['preparacion'][$laFila['CODAVI']] = '';
						}
						$laDatos['preparacion'][$laFila['CODAVI']] .= $laFila['DESAVI'];
					}
					$laDatos['preparacion'] = array_map('trim', $laDatos['preparacion']);
				}
				break;

			// Unidades de Agenda por Procedimientos
			case 'agendaproc':
				$lcMsg='No se pudieron consultar las agendas del procedimiento.';
				$laDatos = $this->oDb->distinct()
					->select('A.CODUNA CODIGO, TRIM(A.DESUNA) DESCRIPCION, TRIM(C.OP2UCO) ENTREGA, C.OP3UCO DURACION')
					->from('UNICON C')
					->innerJoin('UNIAGE A', 'C.CODUCO=A.CODUNA')
					->where(['C.PROUCO'=>$tcParam])
					->where('A.ESTUNA=\'\' AND C.ESTUCO=\'\' ')
					->getAll('array');
				break;

			// Consultorios
			case 'consultorios':
				$lcMsg='No se pudieron obtener los consultorios.';
				$laDatos = $this->oDb
					->select('TRIM(TABCOD) CODIGO, TRIM(TABDSC) DESCRIPCION')
					->from('PRMTAB04')
					->where("TABTIP='CON' AND TABCOD<>''")
					->orderBy('INT(TABCOD)')
					->getAll('array');
				break;

			// Estados Citas
			case 'estados':
				$lcMsg='No se pudieron obtener los estados de las citas.';
				$laDatos = $this->oDb
					->select('TRIM(TABCOD) CODIGO, TRIM(TABDSC) DESCRIPCION')
					->from('PRMTAB02')
					->where("TABTIP='FCI' AND TABCOD<>''")
					->orderBy('TABDSC')
					->getAll('array');
				break;

			// Tipos de Consulta
			case 'tiposConsulta':
				$lcMsg='No se pudieron obtener los tipos de consulta.';
				$laDatos = $this->oDb
					->select('TRIM(TABCOD) CODIGO, TRIM(TABDSC) DESCRIPCION')
					->from('PRMTAB02')
					->where("TABTIP='TIC' AND TABCOD<>''")
					->orderBy('TABDSC')
					->getAll('array');
				break;

			// Motivos Cancelación de Citas
			case 'motivosCancela':
				$lcMsg='No se pudieron obtener los motivos de cancelación de citas.';
				$laMotivos = $this->oDb
					->select('TRIM(TABCOD) CODTIPO, TRIM(TABDSC) DSCTIPO, TRIM(CODMCC) CODIGO, TRIM(DESMCC) DESCRIPCION')
					->from('MOTCCI')
					->innerJoin('PRMTAB02', "TABTIP='TMC' AND TABCOD=TCAMCC")
					->where('ESTMCC=1')
					->orderBy('TABDSC, DESMCC')
					->getAll('array');
				$lcTipoMotivo = ''; $lnIndex = -1;
				foreach ($laMotivos as $laMotivo) {
					if ($lcTipoMotivo!==$laMotivo['CODTIPO']) {
						$lnIndex++;
						$lcTipoMotivo = $laMotivo['CODTIPO'];
						$laDatos[$lnIndex] = [
							'codigotipo' => $laMotivo['CODTIPO'],
							'descripciontipo' => $laMotivo['DSCTIPO'],
							'motivos' => [],
						];
					}
					$laDatos[$lnIndex]['motivos'][] = [
						'codigo' => $laMotivo['CODIGO'],
						'descripcion' => $laMotivo['DESCRIPCION'],
					];
				}
				break;

			// Días semana
			case 'diassemana':
				$lcMsg='No se pudieron obtener los nombres de los días.';
				$laDatos = $this->oDb
					->select('TRIM(TABCOD) CODIGO, TRIM(TABDSC) DESCRIPCION')
					->from('PRMTAB')
					->where("TABTIP='SEM' AND TABCOD<>''")
					->orderBy('TABCOD')
					->getAll('array');
				break;
		}

		if(empty($lcMsg)){
			$laRta = ['success'=>false, 'message'=>'Opción no encontrada'];
		}else{
			if(is_array($laDatos ?? '')){
				if($this->oDb->numRows()>0){
					if (!in_array($tcTipo, ['preparacion'])) {
						$laDatos = array_map('array_change_key_case', $laDatos);
					}
					$laRta = ['success'=>true, 'datos'=>$laDatos];
				}else{
					$laRta = ['success'=>false, 'message'=>'La consulta no retornó datos'];
				}
			} else {
				$laRta = ['success'=>false, 'message'=>$lcMsg];
			}
		}
		return array_merge(['fechahora'=>date('Y-m-d H:i:s')], $laRta);
	}


	private function modificaClaves($tcTipo, $tcDatos, $tnCambioRev=false)
	{
		$lcResult=[];
		if ($tnCambioRev){
			foreach($this->aClaves[$tcTipo] as $lcClaveFin => $lcClaveIni){
				$lcResult[$lcClaveFin]=$tcDatos[$lcClaveIni]??'';
			}
		}else{
			foreach($this->aClaves[$tcTipo] as $lcClaveIni => $lcClaveFin){
				$lcResult[$lcClaveFin]=$tcDatos[$lcClaveIni]??'';
			}
		}
		return $lcResult;
	}


	/*
	 *	Insertar cita
	 *	@param array $taDatos: arrreglo con los datos de la cita
	 *	@param string $tcUser: Usuario que inserta la cita
	 *	@param string $tcProg: Programa que inserta la cita
	 *	@return array con los elementos: success (true o false), mensaje (en caso de success false), datos (array con datos de la cita insertada)
	 */
	public function insertarCita($taDatos, $tcUser='AGENDAWEB', $tcProg='INSERT_CIT')
	{
		$taDatos['fecha'] = str_replace('-','',$taDatos['fecha']);
		$taDatos['hora'] = str_replace(':','',$taDatos['hora']);
		$taDatos['fechaDesea'] = str_replace('-','',$taDatos['fechaDesea']);
		$taDatos['horaDesea'] = str_replace(':','',$taDatos['horaDesea']);
		$taDatos['regMedico'] = str_pad($taDatos['regMedico'],13,'0',STR_PAD_LEFT);

		$taDatos['diaSemana'] = $this->diaSemana($taDatos['fecha']);
		if ($taDatos['cup']=='X00106') {
			$taDatos['especialidad'] = '121';
		} else {
			if (empty($taDatos['especialidad'])) {
				$taDatos['especialidad'] = $this->obtenerEspecialidad($taDatos['regMedico'], $taDatos['cup']);
			}
		}

		if ($taDatos['citaExtra']=='S' || in_array($taDatos['tipoConsulta'], ['C','P'])) {
			$laAgendaWeb = $this->agendamientoSalasWeb($taDatos);
			$taDatos['cnsAgeWeb'] = $laAgendaWeb['ConsecutivoAgendaWeb'];
		}

		if ($taDatos['citaExtra']=='S') {
			$laRta = $this->insertarExtra($taDatos, $tcUser, $tcProg.'E');
		} else {
			/*
			Tipos de consulta
				C	CONSULTA
				P	PROCEDIMIENTO
				H	POST-HOSPITALIZADO
				O	POST-OPERATORIO
			*/
			switch ($taDatos['tipoConsulta']) {
				case 'C': case 'P':
					$laRta = $this->insertarCitaCP($taDatos, $tcUser, $tcProg);
					if ($laRta['success'] && $taDatos['cup']=='920407') {
						$laDatos = array_merge($taDatos, ['cup'=>'894102','especialidad'=>'124']);
						$laRta = $this->insertarCitaCP($laDatos, $tcUser, $tcProg);
						unset($laDatos);
					}
					break;
				case 'H': case 'O':
					$laRta = $this->insertarCitaPost($taDatos, $tcUser, $tcProg, $taDatos['tipoConsulta']);
					break;
			}
			if ($laRta['success']) {
				$taDatos['preparacion'] = ($this->preparacionCup($taDatos['cup']))??[];
				$taDatos['tiempos'] = $this->obtenerTiempoEntrega($taDatos['unidadAgenda'], $taDatos['regMedico'], $taDatos['cup']);
				$laRtaMail = $this->enviarEmail($taDatos);
				$laRta = ['success'=>true, 'datos'=>$taDatos, 'message'=>'Cita agendada. '.$laRtaMail['message']];
			}
		}

		return $laRta;
	}


	/*
	 *	Guarda el paciente y retorna el consecutivo de cita
	 *	@param array $taDatos: arrreglo con los datos del paciente y la cita
	 *	@param string $tcUser: Usuario que inserta la cita
	 *	@param string $tcProg: Programa que inserta la cita
	 *	@return array con el consecutivo de cita, mensaje, fecha y hora de creación
	 */
	public function pacienteCita($taDatos, $tcUser, $tcProg)
	{
		// Fecha y hora para Log
		$ltAhora = new \DateTime($this->oDb->fechaHoraSistema());
		$lnFechaCrea = $ltAhora->format('Ymd');
		$lnHoraCrea  = $ltAhora->format('His');

		$laExistePac = $this->existePaciente($taDatos['tipoId'], $taDatos['numeroId']);
		if ($laExistePac['success']) {
			// obtener consecutivo de cita
			$laDocPac = ['cTipId'=>$taDatos['tipoId'], 'nNumId'=>$taDatos['numeroId']];
			$laDataLog = ['cUsuCre'=>$tcUser, 'cFecCre'=>$lnFechaCrea, 'cHorCre'=>$lnHoraCrea];
			$taDatos['cita'] = Consecutivos::fCalcularConsecutivoCita($laDocPac, $tcProg, $laDataLog);

			if ($taDatos['cita']==0) {

				// FALTA HACER MANEJO DE ERRORES PARA PACIENTE CITA
				// VOLVER A INSERTAR CITA PARA TERMINAR

			}
			$laRta = $this->actualizarPaciente($taDatos, $tcUser, $tcProg);
			$lcMsgPac = 'Paciente actualizado';

		} else {
			$taDatos['cita'] = 1;
			$laRta = $this->insertarPaciente($taDatos, $tcUser, $tcProg);
			$lcMsgPac = 'Paciente insertado';
		}

		return [
			'cita'	=> $taDatos['cita'],
			'msg'	=> $lcMsgPac,
			'fecha'	=> $lnFechaCrea,
			'hora'	=> $lnHoraCrea,
		];
	}


	/*
	 *	Insertar cita Consultas - Control - Procedimientos
	 *	@param array $taDatos: arrreglo con los datos del paciente y la cita
	 *	@param string $tcUser: Usuario que inserta la cita
	 *	@param string $tcProg: Programa que inserta la cita
	 *	@return array con los elementos: success (true o false), mensaje (en caso de success false), datos (array con datos de la cita insertada)
	 */
	public function insertarCitaCP($taDatos, $tcUser, $tcProg)
	{
		$laRta = ['success'=>false, 'message'=>'Ocurrió un error al insertar la cita'];
		$laCitasMasivas = explode(',', $this->oDb->obtenerTabMae1('DE1TMA', 'PROACT', "CL1TMA='CITMAS' AND ESTTMA=''", null, ''));

		// Por parametrizacion y aplique en cualquier momento a otras unidades de agenda, en este caso fisiotera ocupacional
		//	Marca para NO INVASIVOS o ELECTROFISIOLOGIA
		if (in_array($taDatos['cup'], ['894102', '881236'])) {
			$taDatos['dobutamina'] = $this->nDobutamina;
		}

		$lnConsecLinea		= 1;
		$lnCantRegistros	= 1;
		$lcNoInvasivos		= $taDatos['iniciaFisio'].$taDatos['conContraste'];
		$lnHoraCita			= intval(trim($taDatos['hora']) + '00');
		$taDatos['estadoCita'] = 2;
		$taDatos['viaIng']	= '02';

		// No se hace manejo de citas masivas

		//	Verifica si se debe cambiar vía de ingreso
		$laTemp = $this->oDb->select('TABDSC')->from('PRMTAB02')->where(['TABTIP'=>'VIA','TABCOD'=>$taDatos['unidadAgenda']])->get('array');
		if ($this->oDb->numRows()>0) {
			$taDatos['viaIng'] = trim($laTemp['TABDSC']);
		}

		// Consecutivo orden cita
		$taDatos['cnsOrden'] = $this->oDb->obtenerConsecRiacon(920, $tcProg, 20, $tcUser);

		// Actualizar paciente y obtener Consecutivo de cita
		$laLogCita = $this->pacienteCita($taDatos, $tcUser, $tcProg);
		$taDatos['cnsCita']		= $laLogCita['cita'];
		$taDatos['fechaCrea']	= $laLogCita['fecha'];
		$taDatos['horaCrea']	= $laLogCita['hora'];

		// Inserta en RIACIT
		$laData = [
			'TIDCIT' => $taDatos['tipoId'],
			'NIDCIT' => $taDatos['numeroId'],
			'CCICIT' => $taDatos['cnsCita'],
			'EVOCIT' => $taDatos['cnsCant'],
			'CODCIT' => $taDatos['especialidad'],
			'CD2CIT' => $taDatos['dobutamina'],
			'COACIT' => $taDatos['cup'],
			'FCOCIT' => $taDatos['fechaCrea'],
			'FRLCIT' => $taDatos['fecha'],
			'HOCCIT' => $lnHoraCita,
			'RMRCIT' => $taDatos['regMedico'],
			'FERCIT' => $taDatos['fecha'],
			'HRLCIT' => $lnHoraCita,
			'CONCIT' => $taDatos['teleconsulta'],
			'ESTCIT' => $taDatos['estadoCita'],
			'ESCCIT' => $taDatos['unidadAgenda'],
			'INSCIT' => $taDatos['cnsOrden'],
			'VIACIT' => $taDatos['viaIng'],
			'SSACIT' => $lcNoInvasivos,
			'NSACIT' => $taDatos['con3D'],
			'USRCIT' => $tcUser,
			'PGMCIT' => $tcProg,
			'FECCIT' => $taDatos['fechaCrea'],
			'HORCIT' => $taDatos['horaCrea'],
		];
		if ($this->oDb->from('RIACIT')->insertar($laData)) {
			$laRta = ['success'=>true, 'datos'=>$taDatos];

			// Teleconsulta
			if ($taDatos['teleconsulta']=='S') {
				$this->insertarCitaTeleConsulta($taDatos, $tcUser, $tcProg);
				$taDatos['cnsAgeWeb'] = 1;
			}

			// Inserta en RIAORD
			$laData = [
				'TIDORD' => $taDatos['tipoId'],
				'NIDORD' => $taDatos['numeroId'],
				'EVOORD' => $taDatos['cnsCant'],
				'CCIORD' => $taDatos['cnsCita'],
				'CODORD' => $taDatos['especialidad'],
				'CD2ORD' => $taDatos['dobutamina'],
				'COAORD' => $taDatos['cup'],
				'FCOORD' => $taDatos['fechaCrea'],
				'FRLORD' => $taDatos['fecha'],
				'HOCORD' => $lnHoraCita,
				'RMRORD' => $taDatos['regMedico'],
				'FERORD' => $taDatos['fecha'],
				'HRLORD' => $lnHoraCita,
				'CICORD' => $taDatos['dosisCovid'],
				'ESTORD' => $taDatos['estadoCita'],
				'VIAORD' => $taDatos['viaIng'],
				'CATORD' => $taDatos['cnsAgeWeb'],
				'PLAORD' => $taDatos['codPlan'],
				'USRORD' => $tcUser,
				'PGMORD' => $tcProg,
				'FECORD' => $taDatos['fechaCrea'],
				'HORORD' => $taDatos['horaCrea'],
			];
			if ($this->oDb->from('RIAORD')->insertar($laData)) {
				// Insertar en RIACID
				$laData = [
					'TIDCID' => $taDatos['tipoId'],
					'NIDCID' => $taDatos['numeroId'],
					'UNICID' => $taDatos['unidadAgenda'],
					'CCICID' => $taDatos['cnsCita'],
					'CCUCID' => $taDatos['cnsCant'],
					'ORDCID' => $taDatos['cnsOrden'],
					'FGRCID' => $taDatos['fechaCrea'],
					'HGRCID' => $taDatos['horaCrea'],
					'CLICID' => $lnConsecLinea,
					'CUPCID' => $taDatos['cup'],
					'ESTCID' => $taDatos['estadoCita'],
					'CODCID' => $taDatos['especialidad'],
					'CD2CID' => $taDatos['dobutamina'],
					'RMECID' => $taDatos['regMedico'],
					'FRLCID' => $taDatos['fecha'],
					'HOCCID' => $lnHoraCita,
					'PLACID' => $taDatos['codPlan'],
					'VIACID' => $taDatos['viaIng'],
					'OP1CID' => $taDatos['tipoConsulta'],
					'OP2CID' => $lcNoInvasivos,
					'OP3CID' => $taDatos['horaDesea'],
					'OP4CID' => $taDatos['peso'],
					'OP5CID' => str_repeat(' ',50).$taDatos['fechaDesea'].'='.$taDatos['horaDesea'],
					'OP7CID' => $taDatos['fechaDesea'],
					'USRCID' => $tcUser,
					'PGMCID' => $tcProg,
					'FECCID' => $taDatos['fechaCrea'],
					'HORCID' => $taDatos['horaCrea'],
				];
				if ($this->oDb->from('RIACID')->insertar($laData)) {
					// Se guardó correctamente la cita
				} else {
					$laRta = ['success'=>false, 'message'=>'Ocurrió un error al registrar la cita en el archivo de detalle de citas.'];
				}
			} else {
				$laRta = ['success'=>false, 'message'=>'Ocurrió un error al registrar la cita en el archivo de órdenes.'];
			}

		} else {
			$laRta = ['success'=>false, 'message'=>'Ocurrió un error al registrar la cita.'];
		}

		return $laRta;
	}


	/*
	 *	Insertar cita post operatorio - post hospitalizado
	 *	@param array $taDatos: arrreglo con los datos del paciente y la cita
	 *	@param integer $tnNroIngreso: Número de ingreso
	 *	@param string $tcUser: Usuario que inserta la cita
	 *	@param string $tcProg: Programa que inserta la cita
	 *	@param string $tcLetra: O para post operatorio, H para post hospitalizado
	 *	@return array con los elementos: success (true o false), mensaje (en caso de success false), datos (array con datos de la cita insertada)
	 */
	public function insertarCitaPost($taDatos, $tcUser, $tcProg, $tcLetra)
	{
		// **********  VALIDACIÓN POST OPERATORIO  **********

		if ($tcLetra=='O') {
			$lcTipoPost = 'POST-OPERATORIO';

			// Ingreso del paciente
			$lnIndicadorOperatorio = 0;
			if (in_array($taDatos['unidadAgenda'], [37, 38])) {
				$laTemp = $this->oDb->select('NIGING')
					->from('RIAINGL15')
					->where(['TIDING'=>$taDatos['tipoId'],'NIDING'=>$taDatos['numeroId']])
					->orderBy('NIGING','DESC')->get('array');
				if ($this->oDb->numRows()>0) {
					$lnNroIngreso = $laTemp['NIGING'];
					$lnIndicadorOperatorio = 2;
				} else {
					return ['success'=>false, 'message'=>'Ingreso no encontrado. NO puede asignarse cita POST-OPERATORIO.'];
				}
			} else {
				$laTemp = $this->oDb->select('NIGING')
					->from('RIAINGL15')
					->where(['TIDING'=>$taDatos['tipoId'],'NIDING'=>$taDatos['numeroId']])
					->orderBy('NIGING','DESC')->getAll('array');
				if ($this->oDb->numRows()>0) {
					$ltAhora = new \DateTime($this->oDb->fechaHoraSistema());
					$lnFechaCrea = $ltAhora->format('Ymd');
					$lnLimiteOperatorio = (clone $ltAhora)->sub(new \DateInterval('P30D'))->format('Ymd');

					foreach ($laTemp as $laFila) {
						$laTempC = $this->oDb->select('INGEST,FINEST')
							->from('RIAESTM41')
							->where("INGEST={$laFila['NIGING']} AND (FINEST BETWEEN $lnLimiteOperatorio AND $lnFechaCrea) AND (RF1EST='CIRUG.' OR RF3EST='HEMODI')")
							->orderBy('FINEST','DESC')->get('array');
						if ($this->oDb->numRows()>0) {
							$ldFechaConsumo = \DateTime::createFromFormat('Ymd', $laTempC['FINEST']);
							$lnIndicadorOperatorio = 1;
							$lnNroIngreso = $laFila['NIGING'];
							break;
						}
					}
				} else {
					return ['success'=>false, 'message'=>'Ingreso no encontrado. NO puede asignarse cita POST-OPERATORIO.'];
				}
			}
			unset($laTemp,$laTempC);

			if ($lnIndicadorOperatorio==0) {
				return ['success'=>false, 'message'=>'No existen procedimientos QX. NO puede asignarse cita POST-OPERATORIO.'];
			} elseif ($lnIndicadorOperatorio==1) {
				$ldFechaCita = \DateTime::createFromFormat('Ymd', $taDatos['fecha']);
				$loDifFecha = $ldFechaConsumo->diff($ldFechaCita);
				if ($loDifFecha->days > 30) {
					return ['success'=>false, 'message'=>'Fecha Cita Excede el tiempo para asignar POST-OPERATORIO.'];
				}
			}


		// **********  VALIDACIÓN POST HOSPITALIZADOS  **********

		} elseif ($tcLetra=='H') {
			$lcTipoPost = 'POST-HOSPITALIZADO';

			// Solo pacientes COMPENSAR
			$laTemp = $this->oDb->select('NI1CON')->from('FACPLNC')->where(['PLNCON'=>$taDatos['codPlan']])->get('array');
			if ($this->oDb->numRows()>0) {
				if (!in_array($laTemp['NI1CON'], [860066942])) {
					return ['success'=>false, 'message'=>'Paciente POST-HOSPITALIZADO solo entidad COMPENSAR.'];
				}
			} else {
				return ['success'=>false, 'message'=>'Plan no encontrado en la base de datos POST-HOSPITALIZADO.'];
			}

			// VALIDA INGRESO HOSPITALIZACION Y SIN LIMITE DE TIEMPO
			$laTemp = $this->oDb->select('NIGING')->from('RIAINGL6')->where(['TIDING'=>$taDatos['tipoId'], 'NIDING'=>$taDatos['numeroId'], 'VIAING'=>'05'])->get('array');
			if ($this->oDb->numRows()>0) {
				$lnNroIngreso = $laTemp['NIGING'];
			} else {
				return ['success'=>false, 'message'=>'Paciente NO tiene autorización para POST-HOSPITALIZADO.'];
			}
			if (empty($lnNroIngreso)) {
				return ['success'=>false, 'message'=>'Ingreso no encontrado NO puede asignarse cita POST-HOSPITALIZADO.'];
			}

			$laEstadoNoAutoriza = explode(',', trim($this->oDb->obtenerTabMae1("DE2TMA", "CITCAN", "CL1TMA='1' AND ESTTMA=''", null, '1')));

			// VALIDA PARA EL INGRESO NO EXISTA OTRA CITA POR ESPECIALIDAD
			$laTemp = $this->oDb->select('CODCIT')
				->from('RIACITL01')
				->where([
					'TIDCIT'=>$taDatos['tipoId'],
					'NIDCIT'=>$taDatos['numeroId'],
					'NINCIT'=>$lnNroIngreso,
					'CODCIT'=>$taDatos['especialidad'],
					'COACIT'=>$taDatos['cup'],
				])
				->notIn('ESTCIT', $laEstadoNoAutoriza)
				->get('array');
			if ($this->oDb->numRows()>0) {
				return ['success'=>false, 'message'=>'Paciente ya tiene cita POST-HOSPITALIZADO.'];
			}
		}


		// **********  GUARDAR CITA POST OPERATORIO O POST HOSPITALIZADO  **********

		$lnHoraCita				= intval(trim($taDatos['hora']) + '00');
		$taDatos['estadoCita']	= 8;
		$taDatos['viaIng']		= $taDatos['unidadAgenda']==35 ? '06' : '02';
		$lnConsecLinea			= 1;
		$tcProg .= $tcLetra;

		// CONSECUTIVO ORDEN CITA
		$taDatos['cnsOrden'] = $this->oDb->obtenerConsecRiacon(920, $tcProg, 20, $tcUser);

		// Actualizar paciente y obtener Consecutivo de cita
		$laLogCita = $this->pacienteCita($taDatos, $tcUser, $tcProg);
		$taDatos['cnsCita']		= $laLogCita['cita'];
		$taDatos['fechaCrea']	= $laLogCita['fecha'];
		$taDatos['horaCrea']	= $laLogCita['hora'];

		$laRta = ['success'=>true, 'datos'=>$taDatos];

		// Insertar RIAORD
		$laData = [
			'TIDORD' => $taDatos['tipoId'],
			'NIDORD' => $taDatos['numeroId'],
			'EVOORD' => $taDatos['cnsCant'],
			'NINORD' => $lnNroIngreso,
			'CCIORD' => $taDatos['cnsCita'],
			'CODORD' => $taDatos['especialidad'],
			'COAORD' => $taDatos['cup'],
			'FCOORD' => $taDatos['fechaCrea'],
			'FRLORD' => $taDatos['fecha'],
			'HOCORD' => $lnHoraCita,
			'RMRORD' => $taDatos['regMedico'],
			'FERORD' => $taDatos['fecha'],
			'HRLORD' => $lnHoraCita,
			'ESTORD' => $taDatos['estadoCita'],
			'VIAORD' => $taDatos['viaIng'],
			'PLAORD' => $taDatos['codPlan'],
			'USRORD' => $tcUser,
			'PGMORD' => $tcProg,
			'FECORD' => $taDatos['fechaCrea'],
			'HORORD' => $taDatos['horaCrea'],
		];
		if ($this->oDb->from('RIAORD')->insertar($laData)) {
			// Inserta en RIACIT
			$laData = [
				'TIDCIT' => $taDatos['tipoId'],
				'NIDCIT' => $taDatos['numeroId'],
				'CCICIT' => $taDatos['cnsCita'],
				'EVOCIT' => $taDatos['cnsCant'],
				'NINCIT' => $lnNroIngreso,
				'CODCIT' => $taDatos['especialidad'],
				'COACIT' => $taDatos['cup'],
				'FCOCIT' => $taDatos['fechaCrea'],
				'FRLCIT' => $taDatos['fecha'],
				'HOCCIT' => $lnHoraCita,
				'RMRCIT' => $taDatos['regMedico'],
				'FERCIT' => $taDatos['fecha'],
				'HRLCIT' => $lnHoraCita,
				'CONCIT' => $taDatos['teleconsulta'],
				'ESTCIT' => $taDatos['estadoCita'],
				'ESCCIT' => $taDatos['unidadAgenda'],
				'INSCIT' => $taDatos['cnsOrden'],
				'VIACIT' => $taDatos['viaIng'],
				'USRCIT' => $tcUser,
				'PGMCIT' => $tcProg,
				'FECCIT' => $taDatos['fechaCrea'],
				'HORCIT' => $taDatos['horaCrea'],
			];
			if ($this->oDb->from('RIACIT')->insertar($laData)) {
				// Insertar en RIACID
				$laData = [
					'TIDCID' => $taDatos['tipoId'],
					'NIDCID' => $taDatos['numeroId'],
					'UNICID' => $taDatos['unidadAgenda'],
					'CCICID' => $taDatos['cnsCita'],
					'CCUCID' => $taDatos['cnsCant'],
					'ORDCID' => $taDatos['cnsOrden'],
					'FGRCID' => $taDatos['fechaCrea'],
					'HGRCID' => $taDatos['horaCrea'],
					'CLICID' => $lnConsecLinea,
					'CUPCID' => $taDatos['cup'],
					'ESTCID' => $taDatos['estadoCita'],
					'NINCID' => $lnNroIngreso,
					'CODCID' => $taDatos['especialidad'],
					'RMECID' => $taDatos['regMedico'],
					'FRLCID' => $taDatos['fecha'],
					'HOCCID' => $lnHoraCita,
					'PLACID' => $taDatos['codPlan'],
					'VIACID' => $taDatos['viaIng'],
					'OP1CID' => $taDatos['tipoConsulta'],
					'OP3CID' => $taDatos['horaDesea'],
					'OP4CID' => $taDatos['peso'],
					'OP5CID' => str_repeat(' ',50).$taDatos['fechaDesea'].'='.$taDatos['horaDesea'],
					'OP7CID' => $taDatos['fechaDesea'],
					'USRCID' => $tcUser,
					'PGMCID' => $tcProg,
					'FECCID' => $taDatos['fechaCrea'],
					'HORCID' => $taDatos['horaCrea'],
				];
				if ($this->oDb->from('RIACID')->insertar($laData)) {
					// Se guardó correctamente la cita

				} else {
					$laRta = ['success'=>false, 'message'=>"Ocurrió un error al registrar la cita $lcTipoPost en el archivo de detalle de citas."];
				}
			} else {
				$laRta = ['success'=>false, 'message'=>"Ocurrió un error al registrar la cita $lcTipoPost."];
			}
		} else {
			$laRta = ['success'=>false, 'message'=>"Ocurrió un error al registrar $lcTipoPost en el archivo de órdenes."];
		}

		return $laRta;
	}


	/*
	 *	Insertar cita extra
	 *	@param array $taDatos: arrreglo con los datos del paciente y la cita
	 *	@param string $tcUser: Usuario que inserta la cita
	 *	@param string $tcProg: Programa que inserta la cita
	 *	@return array con los elementos: success (true o false), mensaje (en caso de success false), datos (array con datos de la cita insertada)
	 */
	public function insertarExtra($taDatos, $tcUser, $tcProg)
	{
		$laRta = ['success'=>false, 'message'=>'Opción cita extra no habilitada.'];

		//	Marca para NO INVASIVOS o ELECTROFISIOLOGIA
		if (in_array($taDatos['cup'], ['894102', '881236'])) {
			$taDatos['dobutamina'] = $this->nDobutamina;
		}

		$lnNroIngreso		= 0;
		$lnConsecLinea		= 1;
		$lnCantRegistros	= 1;
		$lcNoInvasivos		= $taDatos['iniciaFisio'].$taDatos['conContraste'];
		$lnHoraCita			= intval(trim($taDatos['hora']) + '00');
		$taDatos['estadoCita'] = 2;
		$taDatos['viaIng']	= $taDatos['unidadAgenda']==35 ? '06' : '02';
		$tcProg .= 'E';

		// Consecutivo orden cita
		$taDatos['cnsOrden'] = $this->oDb->obtenerConsecRiacon(920, $tcProg, 20, $tcUser);

		// Actualizar paciente y obtener Consecutivo de cita
		$laLogCita = $this->pacienteCita($taDatos, $tcUser, $tcProg);
		$taDatos['cnsCita']		= $laLogCita['cita'];
		$taDatos['fechaCrea']	= $laLogCita['fecha'];
		$taDatos['horaCrea']	= $laLogCita['hora'];

		$laRta = ['success'=>true, 'datos'=>$taDatos];

		// Insertar RIAORD
		$laData = [
			'TIDORD' => $taDatos['tipoId'],
			'NIDORD' => $taDatos['numeroId'],
			'EVOORD' => $taDatos['cnsCant'],
			'NINORD' => $lnNroIngreso,
			'CCIORD' => $taDatos['cnsCita'],
			'CODORD' => $taDatos['especialidad'],
			'CD2ORD' => $taDatos['dobutamina'],
			'COAORD' => $taDatos['cup'],
			'FCOORD' => $taDatos['fechaCrea'],
			'FRLORD' => $taDatos['fecha'],
			'HOCORD' => $lnHoraCita,
			'RMRORD' => $taDatos['regMedico'],
			'FERORD' => $taDatos['fecha'],
			'HRLORD' => $lnHoraCita,
			'CICORD' => $taDatos['dosisCovid'],
			'ESTORD' => $taDatos['estadoCita'],
			'VIAORD' => $taDatos['viaIng'],
			'CATORD' => $taDatos['cnsAgeWeb'],
			'PLAORD' => $taDatos['codPlan'],
			'USRORD' => $tcUser,
			'PGMORD' => $tcProg,
			'FECORD' => $taDatos['fechaCrea'],
			'HORORD' => $taDatos['horaCrea'],
		];
		if ($this->oDb->from('RIAORD')->insertar($laData)) {
			// Inserta en RIACIT
			$laData = [
				'TIDCIT' => $taDatos['tipoId'],
				'NIDCIT' => $taDatos['numeroId'],
				'CCICIT' => $taDatos['cnsCita'],
				'EVOCIT' => $taDatos['cnsCant'],
				'NINCIT' => $lnNroIngreso,
				'CODCIT' => $taDatos['especialidad'],
				'CD2CIT' => $taDatos['dobutamina'],
				'COACIT' => $taDatos['cup'],
				'FCOCIT' => $taDatos['fechaCrea'],
				'FRLCIT' => $taDatos['fecha'],
				'HOCCIT' => $lnHoraCita,
				'RMRCIT' => $taDatos['regMedico'],
				'FERCIT' => $taDatos['fecha'],
				'HRLCIT' => $lnHoraCita,
				'CONCIT' => $taDatos['teleconsulta'],
				'ESTCIT' => $taDatos['estadoCita'],
				'ESCCIT' => $taDatos['unidadAgenda'],
				'INSCIT' => $taDatos['cnsOrden'],
				'VIACIT' => $taDatos['viaIng'],
				'USRCIT' => $tcUser,
				'PGMCIT' => $tcProg,
				'FECCIT' => $taDatos['fechaCrea'],
				'HORCIT' => $taDatos['horaCrea'],
			];
			if ($this->oDb->from('RIACIT')->insertar($laData)) {
				// Insertar en RIACID
				$laData = [
					'TIDCID' => $taDatos['tipoId'],
					'NIDCID' => $taDatos['numeroId'],
					'UNICID' => $taDatos['unidadAgenda'],
					'CCICID' => $taDatos['cnsCita'],
					'CCUCID' => $taDatos['cnsCant'],
					'ORDCID' => $taDatos['cnsOrden'],
					'FGRCID' => $taDatos['fechaCrea'],
					'HGRCID' => $taDatos['horaCrea'],
					'CLICID' => $lnConsecLinea,
					'CUPCID' => $taDatos['cup'],
					'ESTCID' => $taDatos['estadoCita'],
					'CODCID' => $taDatos['especialidad'],
					'CD2CID' => $taDatos['dobutamina'],
					'RMECID' => $taDatos['regMedico'],
					'FRLCID' => $taDatos['fecha'],
					'HOCCID' => $lnHoraCita,
					'NINCID' => $lnNroIngreso,
					'PLACID' => $taDatos['codPlan'],
					'VIACID' => $taDatos['viaIng'],
					'DESCID' => $taDatos['tipoConsulta'],
					'OP3CID' => $taDatos['horaDesea'],
					'OP4CID' => $taDatos['peso'],
					'OP5CID' => str_repeat(' ',50).$taDatos['fechaDesea'].'='.$taDatos['horaDesea'],
					'OP7CID' => $taDatos['fechaDesea'],
					'USRCID' => $tcUser,
					'PGMCID' => $tcProg,
					'FECCID' => $taDatos['fechaCrea'],
					'HORCID' => $taDatos['horaCrea'],
				];
				if ($this->oDb->from('RIACID')->insertar($laData)) {
					// Se guardó correctamente la cita
				} else {
					$laRta = ['success'=>false, 'message'=>'Ocurrió un error al registrar la cita extra en el archivo de detalle de citas.'];
				}
			} else {
				$laRta = ['success'=>false, 'message'=>'Ocurrió un error al registrar la cita extra.'];
			}
		} else {
			$laRta = ['success'=>false, 'message'=>'Ocurrió un error al registrar la cita extra en el archivo de órdenes.'];
		}

		return $laRta;
	}


	/*
	 *	Insertar cita telemedicina
	 *	@param array $taDatos: arrreglo con los datos del paciente y la cita
	 *	@param string $tcUser: Usuario que inserta la cita
	 *	@param string $tcProg: Programa que inserta la cita
	 *	@return array con los elementos: success (true o false), mensaje (en caso de success false), datos (array con datos de la cita insertada)
	 */
	public function insertarCitaTeleConsulta($taDatos, $tcUser, $tcProg)
	{
		$loCita = new Citas();
		$loCita->cPrograma = $tcProg;

		$lnConsulta = 0;
		$lnHoraCita = str_pad(trim($taDatos['hora']), 6, '0');

		$loCita->crearCitaTelemedicina(
			$tcUser,
			$taDatos['tipoId'],
			$taDatos['numeroId'],
			$taDatos['cnsCita'],
			$lnConsulta,
			$taDatos['cnsCant'],
			$taDatos['ingreso']??0,
			$taDatos['especialidad'],
			$taDatos['cup'],
			$taDatos['fechaCrea'],
			$taDatos['fecha'],
			$lnHoraCita,
			$taDatos['regMedico'],
			$taDatos['fecha'],
			$lnHoraCita,
			$taDatos['teleconsulta'],
			$taDatos['estadoCita'],
			$taDatos['unidadAgenda'],
			$taDatos['cnsOrden'],
			$taDatos['viaIng']
		);
	}


	/*
	 *	Obtener el CUP de control para una especialidad
	 *	@param string $tcEspecialidad: código de la especialidad
	 *	@return string código del procedimiento para cita de control
	 */
	public function cupControl($tcEspecialidad)
	{
		$lcCup = '890302';	// Cup control por defecto
		$laCup = $this->oDb
			->select('CODCUP')
			->from('RIACUP')
			->where("IDDCUP='0' AND CODCUP LIKE '8903%'")
			->where(['ESPCUP'=>$tcEspecialidad])
			->get('array');
		if ($this->oDb->numRows()>0) {
			$lcCup = $laCup['CODCUP'];
		}

		return $lcCup;
	}


	public function validarDatosCita($taDatos, $tbValidarDocPaciente = false)
	{
		if ($tbValidarDocPaciente) {
			$laRta = $this->validarId($taDatos['tipoId'], $taDatos['numeroId']);
			if (!$laReturn['success']) return $laRta;
		}

		$laRta = ['success'=>true, 'fechahora'=>date('Y-m-d H:i:s'), 'datos'=>$taDatos];

		$laFechaCita = $taDatos['fecha'];
		$laHoraCita  = $taDatos['hora'];
		$taDatos['fecha'] = str_replace('-','',$taDatos['fecha']);
		$taDatos['hora'] = str_replace(':','',$taDatos['hora']);
		$taDatos['fechaDesea'] = str_replace('-','',$taDatos['fechaDesea']);
		$taDatos['horaDesea'] = str_replace(':','',$taDatos['horaDesea']);

		$lcFormato = 'Ymd His';
		$lcFechaHora = $taDatos['fecha'].' '.$taDatos['hora'];
		if (AplicacionFunciones::validarFechaHora($lcFechaHora, $lcFormato)) {
			if (strtotime($lcFechaHora)<strtotime(date($lcFormato))) {
				return array_merge($laRta, ['success'=>false, 'message'=>'Fecha hora de cita no puede ser menor a la fecha hora actual.']);
			}
		} else {
			return array_merge($laRta, ['success'=>false, 'message'=>'Fecha hora de cita incorrecta.']);
		}

		$lcFechaHora = $taDatos['fechaDesea'].' '.$taDatos['horaDesea'];
		if (AplicacionFunciones::validarFechaHora($lcFechaHora, $lcFormato)) {
			if (strtotime($lcFechaHora)<strtotime(date($lcFormato))) {
				return array_merge($laRta, ['success'=>false, 'message'=>'Fecha hora deseada no puede ser menor a la fecha hora actual.']);
			}
		} else {
			return array_merge($laRta, ['success'=>false, 'message'=>'Fecha hora deseada incorrecta.']);
		}

		if (empty($taDatos['cup'])) {
			return array_merge($laRta, ['success'=>false, 'message'=>'Código de procedimiento (CUP) obligatorio']);
		}

		if (!in_array($taDatos['tipoConsulta'],['P','C','O','H'])) {
			return array_merge($laRta, ['success'=>false, 'message'=>'Tipo Consulta incorrecto.']);
		}

		foreach (['teleconsulta','citaExtra'] as $lcClave) {
			if (!in_array($taDatos[$lcClave],['S','N'])) {
				return array_merge($laRta, ['success'=>false, 'message'=>$lcClave.' debe ser S o N.']);
			}
		}

		if ($taDatos['unidadAgenda']=='22') {		// FISIOTERAPIA - TERAPIA FISICA
			if (empty($taDatos['iniciaFisio'])) {
				return array_merge($laRta, ['success'=>false, 'message'=>'Inicia Fisioterapia no puede estar vacío']);
			} else {
				if (!in_array($taDatos['iniciaFisio'],['S','N'])) {
					return array_merge($laRta, ['success'=>false, 'message'=>'Inicia Fisio debe ser S o N.']);
				}
			}
		} else {
			$taDatos['iniciaFisio'] = '';
		}

		/*
		// No visible en fox
		if (!empty($taDatos['electro'])) {
			if (!in_array($taDatos['electro'], array_keys($this->aCitaElectro))) {
				return array_merge($laRta, ['success'=>false, 'message'=>'Valor de Electro no permitido.']);
			}
		}

		// Unidades de agenda deshabilitadas
		if ($taDatos['unidadAgenda']=='29') { // IMAGENOLOGIA - SCANNER
			if (empty($taDatos['con3D'])) {
				return array_merge($laRta, ['success'=>false, 'message'=>'con3D no puede estar vacío']);
			} else {
				if (!in_array($taDatos['con3D'],['S','N'])) {
					return array_merge($laRta, ['success'=>false, 'message'=>'con3D debe ser S o N.']);
				}
			}
		} else {
			$taDatos['con3D'] = '';
		}
		if (in_array($taDatos['unidadAgenda'], ['29','30'])) { // IMAGENOLOGIA - SCANNER, RESONANCIA
			if (empty($taDatos['conContraste'])) {
				return array_merge($laRta, ['success'=>false, 'message'=>'conContraste no puede estar vacío']);
			} else {
				if (!in_array($taDatos['conContraste'],['S','N'])) {
					return array_merge($laRta, ['success'=>false, 'message'=>'conContraste debe ser S o N.']);
				}
			}
		} else {
			$taDatos['conContraste'] = '';
		}

		// Cups sin unidad de agenda relacionada
		if (in_array($taDatos['cup'], ['894102','881236'])) { // MÉTODOS NO INVASIVOS
			if (empty($taDatos['dobutamina'])) {
				return array_merge($laRta, ['success'=>false, 'message'=>'dobutamina no puede estar vacío']);
			} else {
				if (!in_array($taDatos['dobutamina'],['S','N'])) {
					return array_merge($laRta, ['success'=>false, 'message'=>'dobutamina debe ser S o N.']);
				}
			}
			$taDatos['dobutamina'] = $taDatos['dobutamina']=='S' ? 1 : 0;
		} else {
			$taDatos['dobutamina'] = 0;
		}
		*/
		$taDatos['con3D'] = $taDatos['conContraste'] = '';
		$taDatos['dobutamina'] = 0;

		$laUndAgendaPesoObliga = explode(',', $this->oDb->obtenerTabmae1('DE2TMA','UNAPESO',"ESTTMA=''",'CL1TMA','5,50,58,59'));
		if (in_array($taDatos['unidadAgenda'], $laUndAgendaPesoObliga)) {
			if (($taDatos['peso']??'0')=='0') {
				return array_merge($laRta, ['success'=>false, 'message'=>"Peso es obligatorio para la unidad de agenda {$taDatos['unidadAgenda']}."]);
			}
		}

		// Se verifica que exista un médico seleccionado
		if (!empty($taDatos['regMedico'])) {
			$taDatos['regMedico'] = str_pad($taDatos['regMedico'],13,'0',STR_PAD_LEFT);
			$laTemp = $this->oDb->select('TABCOD')->from('PRMTAB04')->where("TABTIP='CIV' AND TABCOD='{$taDatos['unidadAgenda']}'")->get('array');
			if ($this->oDb->numRows()>0) {
				// Valida cita no tenga otra cita este dia y esta hora
				$laTemp = $this->oDb
					->select('FRLCIT')
					->from('RIACIT')
					->where("VIACIT='02' AND ESTCIT IN (2, 3, 8, 30)") // RIACITL13
					->where([
						'TIDCIT'=>$taDatos['tipoId'],
						'NIDCIT'=>$taDatos['numeroId'],
						'FRLCIT'=>$taDatos['fecha'],
						'HOCCIT'=>$taDatos['hora'],
						'RMRCIT'=>$taDatos['regMedico'],
					])
					->getAll('array');
				if ($this->oDb->numRows()>0) {
					return array_merge($laRta, ['success'=>false, 'message'=>'Paciente tiene asignada una cita para este médico el mismo día y hora.']);
				}
			}
		}

		$laTemp = $this->oDb
			->select('FRLCIT')
			->from('RIACIT')
			->where("VIACIT='02' AND ESTCIT IN (2, 3, 8, 30)") // RIACITL13
			->where([
				'TIDCIT'=>$taDatos['tipoId'],
				'NIDCIT'=>$taDatos['numeroId'],
				'FRLCIT'=>$taDatos['fecha'],
				'HOCCIT'=>$taDatos['hora'],
			])
			->getAll('array');
		if ($this->oDb->numRows()>0) {
			return array_merge($laRta, ['success'=>false, 'message'=>'Paciente tiene asignada una cita para esta hora.']);
		}

		$taDatos['diaSemana'] = $this->diaSemana($taDatos['fecha']);

		// Pacientes puede atender
		$laTemp = $this->oDb
			->select('CODPME')->from('UNIPMEL01')
			->where([
				'CODPME'=>$taDatos['unidadAgenda'],
				'INDPME'=>1,
				'REGPME'=>$taDatos['regMedico'],
				'DIAPME'=>$taDatos['diaSemana'],
			])
			->getAll('array');
		$uCanc = $this->oDb->numRows()>0 ? $this->oDb->numRows() : 1;

		/*
		// Agendas deshabilitadas
		if (in_array($taDatos['unidadAgenda'], ['31','32'])) {
			$loPersona = new Persona();
			$loPersona->nNacio = $laPaciente['FNAPAC'];
			$laEdad = explode('-', $loPersona->getEdad('', '%y-%m-%d'));
		}
		// Verifica para la unidad de agenda 32-IMAGENOLOGIA - PROC. ESPECIALES (DRA. SANCHEZ) - PEDIATRICO  no se pueden asignar citas a pacientes mayores de 14 años
		if ($taDatos['unidadAgenda']=='32') {
			if (intval($laEdad[0])>14) {
				return array_merge($laRta, ['success'=>false, 'message'=>'Para la Dra. Sanchez no pueden asignarse pacientes mayores a 14 años, deben ser asignados al Dr. Huertas']);
			}
		}
		//	Verifica para la unidad de agenda 31-IMAGENOLOGIA - PROC. ESPECIALES (DR. HUERTAS) no se pueden asignar citas a pacientes menores de 14 años
		if ($taDatos['unidadAgenda']=='31') {
			if (intval($laEdad[0])<14) {
				return array_merge($laRta, ['success'=>false, 'message'=>'Para el Dr. Huertas no pueden asignarse pacientes menores a 14 años, deben ser asignados a la Dra. Sánchez']);
			}
		}

		// Valida Máximo 2 procedimientos iguales a la misma hora fecha para Medicina Nuclear
		if ($taDatos['unidadAgenda']=='5') {
			$lnNumMaxProc = 2;
			$laListaDias = [2,3,4,5,6]; // 2=lunes hasta 6=viernes
			$laValida = $this->MaxProcedPorAgenda($taDatos, $lnNumMaxProc, $laListaDias);
			if (!$laValida['success']) {
				return array_merge($laValida, ['datos'=>$taDatos]);
			}
		}
		*/

		// Si es procedimiento revisa si por tiempo es necesario un doble cupo
		// ??

		// Valida valoración Preanestesia
		$laPreaneastesia = $this->agendamientoSalasWeb($taDatos);
		if (!empty($laPreaneastesia['ValoraciónPreanestesia'])) {
			if ($laPreaneastesia['ConsecutivoAgendaWeb']==0) {
				return array_merge($laRta, ['success'=>false, 'message'=>'Agendamiento Salas WEB']);
			}
		}

		// Vacuna COVID 19
		$lcDosisCovid = $this->oDb->obtenerTabMae1("TRIM(CL2TMA)", "COVID19", "CL1TMA='UNVACCOV' AND CL2TMA='{$taDatos['unidadAgenda']}' AND ESTTMA=''", null, '');
		if (!empty($lcDosisCovid) && empty($taDatos['dosisCovid'])) {
			return array_merge($laRta, ['success'=>false, 'message'=>'Tipo dosis vacuna Covid obligatorio']);
		}

		// Validar fecha hora de la cita para la agenda
		$laAgendas = $this->agendaConsultar($taDatos['unidadAgenda'], $taDatos['fecha'], $taDatos['fecha']);
		$lbAgendaEncontrada = false; $lnKeyAgenda = -1;
		foreach ($laAgendas['datos'] as $lnKey=>$laAgenda) {
			if (substr($laHoraCita,0,5) == $laAgenda['hora'] &&
				$taDatos['regMedico'] == $laAgenda['regmed'] &&
				$taDatos['cnsCant'] == $laAgenda['cnscant'] &&
				(empty($laAgenda['cup']) || $taDatos['cup'] == $laAgenda['cup']) &&
				($laAgenda['estado']==1 || $taDatos['citaExtra']=='S') )
			{
				$lbAgendaEncontrada = true;
				$lnKeyAgenda = $lnKey;
			}
		}
		if (!$lbAgendaEncontrada) {
			return array_merge($laRta, ['success'=>false, 'message'=>'Agenda no disponible']);
		}

		return array_merge(['fechahora'=>date('Y-m-d H:i:s')], $laRta);
	}


	private function obtenerEspecialidad($tcRegMedico, $tcCUP)
	{
		$lcCodEsp = '';
		if (!empty($tcRegMedico)) {
			$laDatos = $this->oDb->select('CODRGM')->from('RIARGMN')->where(['REGMED'=>$tcRegMedico])->get('array');
			if ($this->oDb->numRows()>0) {
				$lcCodEsp = trim($laDatos['CODRGM']);
			}
		}
		if (empty($lcCodEsp)) {
			$laDatos = $this->oDb->select('ESPCUP')->from('RIACUP')->where(['CODCUP'=>$tcCUP])->get('array');
			if ($this->oDb->numRows()>0) {
				$lcCodEsp = trim($laDatos['ESPCUP']);
			}
		}

		return $lcCodEsp;
	}


	public function MaxProcedPorAgenda($taDatos, $tnNumMaxProc, $taListaDias)
	{
		if (in_array($taDatos['diaSemana'], $taListaDias)) {
			$laTemp = $this->oDb
				->select('COACIT')
				->from('RIACIT')
				->where([
					'COACIT'=>$taDatos['cup'],
					'FRLCIT'=>$taDatos['fecha'],
					'HOCCIT'=>$taDatos['hora'],
				])
				->getAll('array');
			if ($this->oDb->numRows()>=$tnNumMaxProc) {
				return ['success'=>false, 'message'=>"El máximo de procedimientos iguales admitidos para el horario es $tnNumMaxProc"];
			}
		}

		return ['success'=>true, 'message'=>''];
	}


	public function agendamientoSalasWeb($taDatos)
	{
		$lnConsecutivoAgendaWeb = 0;

		$laDatosPreanestesia = explode('~', $this->oDb->obtenerTabMae1("TRIM(DE2TMA) || '~' || TRIM(OP2TMA)", 'DATING', "CL1TMA='VALANES' AND CL2TMA='{$taDatos['unidadAgenda']}' AND ESTTMA=''", null, ''));
		$lcValoraciónPreanestesia = $laDatosPreanestesia[0];
		$lnDiasValoracionWeb = intval($laDatosPreanestesia[2]??'');

		if (!empty($lcValoraciónPreanestesia)) {
			$lcFechaSist = substr($this->oDb->fechaHoraSistema(),0,10);
			$ldFechaSist = \DateTime::createFromFormat('Ymd', $lcFechaSist);
			$ldFechaSist->sub(new \DateInterval('P'.$lnDiasValoracionWeb.'D'));
			$lnFechaDesde = $ldFechaSist->format('Ymd');

			$laTemp = $this->oDb
				->select('CONSAL')
				->from('AGESALL01')
				->where([
					'TIDSAL'=>$taDatos['tipoId'],
					'NIDSAL'=>$taDatos['numeroId'],
					'ESTSAL'=>'A',
				])
				->where('FPRSAL','>=',$lnFechaDesde)
				->orderBy('CONSAL')
				->getAll('array');
			$lnConsecutivoAgendaWeb = $this->oDb->numRows()>0 ? $laTemp[0]['CONSAL'] : 0;
		}

		return [
			'ValoraciónPreanestesia'=>$lcValoraciónPreanestesia,
			'ConsecutivoAgendaWeb'=>$lnConsecutivoAgendaWeb,
		];
	}


	public function cancelarCita($taDatos, $tcUser, $tcProg)
	{
		$laRta = ['success'=>true, 'message'=>''];

		$laListasEstados = explode(',', trim($this->oDb->obtenerTabMae1("DE2TMA", "CITCAN", "CL1TMA='IDENTI' AND ESTTMA=''", null, '2,8,9,5')));

		// *** VALIDAR CANCELACIÓN ***

		$taDatos['fecha'] = str_replace('-','',$taDatos['fecha']);
		$taDatos['hora'] = str_replace(':','',$taDatos['hora']);
		$lcFormato = 'Ymd His';
		$lcFechaHora = $taDatos['fecha'].' '.$taDatos['hora'];
		if (AplicacionFunciones::validarFechaHora($lcFechaHora, $lcFormato)) {
			//	if (strtotime($lcFechaHora)<strtotime(date($lcFormato))) {
			//		return array_merge($laRta, ['success'=>false, 'message'=>'Fecha hora de cita no puede ser menor a la fecha hora actual.']);
			//	}
		} else {
			return array_merge($laRta, ['success'=>false, 'message'=>'Fecha hora de cita incorrecta.']);
		}

		// Validar si la cita existe y no está anulada
		$laWhere = [
			'TIDCIT' => $taDatos['tipoId'],
			'NIDCIT' => $taDatos['numeroId'],
			'FRLCIT' => $taDatos['fecha'],
			'HOCCIT' => $taDatos['hora'],
			'CCICIT' => $taDatos['cnsCita'],
			'EVOCIT' => $taDatos['cnsCant'],
			'COACIT' => $taDatos['cup'],
		];
		if (isset($taDatos['unidadAgenda'])) {
			$laWhere['ESCCIT'] = $taDatos['unidadAgenda'];
		}

		$laCita = $this->oDb
			->select('CCOCIT,NINCIT,CODCIT,RMRCIT,ESTCIT,INSCIT,VIACIT,ESCCIT')
			->from('RIACIT')
			->where($laWhere)
			->in('ESTCIT', $laListasEstados)
			->get('array');
		if ($this->oDb->numRows()==0) {
			$laRta = ['success'=>false, 'message'=>'Cita no encontrada.'];
		} else {
			if ($taDatos['codTipo']!==0 && $taDatos['codMotivo']!==0) {
				// Validar tipo y motivo de cancelación
				$laTipoMotivo = $this->oDb
					->select('CODMCC')
					->from('MOTCCI')
					->innerJoin('PRMTAB02', "TABTIP='TMC' AND TABCOD=TCAMCC")
					->where([
						'TABCOD' => $taDatos['codTipo'],
						'CODMCC' => $taDatos['codMotivo'],
					])
					->where('ESTMCC=1')
					->orderBy('TABDSC, DESMCC')
					->get('array');
				if ($this->oDb->numRows()==0) {
					$laRta = ['success'=>false, 'message'=>'Tipo y/o motivo de cancelación no corresponden'];
				}
				unset($laTipoMotivo);
			}
			$taDatos['unidadAgenda'] = trim($laCita['ESCCIT']);
		}


		// *** GUARDAR CANCELACIÓN ***

		if ($laRta['success']) {
			// Fecha y hora para Log
			$ltAhora = new \DateTime($this->oDb->fechaHoraSistema());
			$lnFechaCrea = $ltAhora->format('Ymd');
			$lnHoraCrea	 = $ltAhora->format('His');
			$tcProg = substr($tcProg,0,8) . '_C';

			$lnEstado = 6;
			$lnEstadoCitaCit = 1;
			$lcEstadoExtra = 'A';
			$lcConsecOrden = '';
			$lnOrdenCita = 0;
			$lnConsLinea = 1;
			$lnCnsCancela = 0;

			// Tipo Unidad de Agenda
			if (!empty($taDatos['unidadAgenda'])) {
				$laTemp = $this->oDb->select('COPUNA')->from('UNIAGEL01')->where(['CODUNA'=>$taDatos['unidadAgenda']])->get('array');
				$lcTipoUnidad = $laTemp['COPUNA'] ?? '';
			} else {
				$lcTipoUnidad = '';
				$taDatos['unidadAgenda'] = 0;
			}

			//
			$laTemp = $this->oDb->select('TABTIP')->from('PRMTAB02')->where(['TABTIP'=>'NCO','TABCOD'=>$taDatos['unidadAgenda']])->get('array');
			$lnNoCobra = $this->oDb->numRows()>0 ? 1 : 0;

			if ($lcTipoUnidad=='C' || $lnNoCobra==1) {
				$laTemp = $this->oDb
					->select('ORDCID')
					->from('RIACIDL1')
					->where([
						'TIDCID'=>$taDatos['tipoId'],
						'NIDCID'=>$taDatos['numeroId'],
						'UNICID'=>$taDatos['unidadAgenda'],
						'CCICID'=>$taDatos['cnsCita'],
					])
					->where('ORDCID>0')
					->get('array');
				$lnOrdenCita = $laTemp['ORDCID'] ?? 0;
				// ¿Sería lo mismo $laCita['INSCIT']?
			}

			// Observaciones - CANCIT
			$taDatos['observaciones'] = trim($taDatos['observaciones']);
			if (!empty($taDatos['observaciones'])) {
				// Consecutivo de cancelación
				$laCnsCancela = $this->oDb->max('CONDCC','CONSECUTIVO')->from('CANCIT')->getAll('array');
				$lnCnsCancela = $this->oDb->numRows()>0 ? $laCnsCancela[0]['CONSECUTIVO'] + 1 : 1;

				$laData = [
					'CONDCC' => $lnCnsCancela,
					'USRDCC' => $tcUser,
					'PGMDCC' => $tcProg,
					'FECDCC' => $lnFechaCrea,
					'HORDCC' => $lnHoraCrea,
				];
				$laLineas = AplicacionFunciones::mb_str_split(trim($taDatos['observaciones']), 220);
				if (is_array($laLineas) && count($laLineas)>0) {
					foreach($laLineas as $lnLinea=>$lcLinea){
						$laData['CLIDCC'] = $lnLinea++;
						$laData['DESDCC'] = $lcLinea;
						$this->oDb->from('CANCIT')->insertar($laData);
					}
				}
			}

			// Actualizar RIAORD
			$laData = [
				//'EVOORD' => $taDatos['cnsCant'],
				//'CODORD' => $laCita['CODCIT'],
				//'COAORD' => $taDatos['cup'],
				//'FRLORD' => $taDatos['fecha'],
				//'HOCORD' => $taDatos['hora'],
				//'RMRORD' => $laCita['RMRCIT'],
				//'FERORD' => $taDatos['fecha'],
				//'HRLORD' => $taDatos['hora'],
				'ESTORD' => $lnEstado,
				'UMOORD' => $tcUser,
				'PMOORD' => $tcProg,
				'FMOORD' => $lnFechaCrea,
				'HMOORD' => $lnHoraCrea,
			];
			$laWhere = [
				'TIDORD' => $taDatos['tipoId'],
				'NIDORD' => $taDatos['numeroId'],
				'CCIORD' => $taDatos['cnsCita'],
			];
			$this->oDb->from('RIAORD')->where($laWhere)->actualizar($laData);

			// Actualizar RIACIT
			$laData = [
				//'EVOCIT' => $taDatos['cnsCant'],
				//'CODCIT' => $laCita['CODCIT'],
				//'COACIT' => $taDatos['cup'],
				//'FRLCIT' => $taDatos['fecha'],
				//'HOCCIT' => $taDatos['hora'],
				//'RMRCIT' => $laCita['RMRCIT'],
				//'FERCIT' => $taDatos['fecha'],
				//'HRLCIT' => $taDatos['hora'],
				//'ESCCIT' => $taDatos['unidadAgenda'],
				'ESTCIT' => $lnEstadoCitaCit,
				'UMOCIT' => $tcUser,
				'PMOCIT' => $tcProg,
				'FMOCIT' => $lnFechaCrea,
				'HMOCIT' => $lnHoraCrea,
			];
			$laWhere = [
				'TIDCIT' => $taDatos['tipoId'],
				'NIDCIT' => $taDatos['numeroId'],
				'CCICIT' => $taDatos['cnsCita'],
			];
			$this->oDb->from('RIACIT')->where($laWhere)->actualizar($laData);

			// Actualizar JTMCIT
			$laData = [
				//'EVOCIT' => $taDatos['cnsCant'],
				//'CODCIT' => $laCita['CODCIT'],
				//'COACIT' => $taDatos['cup'],
				//'FRLCIT' => $taDatos['fecha'],
				//'HOCCIT' => $taDatos['hora'],
				//'RMRCIT' => $laCita['RMRCIT'],
				//'FERCIT' => $taDatos['fecha'],
				//'HRLCIT' => $taDatos['hora'],
				//'ESCCIT' => $taDatos['unidadAgenda'],
				'ESTCIT' => $lnEstadoCitaCit,
				'UMOCIT' => $tcUser,
				'PMOCIT' => $tcProg,
				'FMOCIT' => $lnFechaCrea,
				'HMOCIT' => $lnHoraCrea,
			];
			$laWhere = [
				'TIDCIT' => $taDatos['tipoId'],
				'NIDCIT' => $taDatos['numeroId'],
				'CCICIT' => $taDatos['cnsCita'],
			];
			$this->oDb->from('JTMCIT')->where($laWhere)->actualizar($laData);

			// Actualizar CITA EXTRA
			$laWhere = [
				'TIDEXT' => $taDatos['tipoId'],
				'NIDEXT' => $taDatos['numeroId'],
				'CCIEXT' => $taDatos['cnsCita'],
			];
			$laTemp = $this->oDb->select('CCIEXT')->from('UNIEXTL01')->where($laWhere)->get('array');
			if ($this->oDb->numRows()>0) {
				$laData = [
					//'FEEEXT' => $taDatos['fecha'],
					//'HOEEXT' => $taDatos['hora'],
					//'UNIEXT' => $taDatos['unidadAgenda'],
					//'CUPEXT' => $taDatos['cnsCant'],
					//'PROEXT' => $taDatos['cup'],
					//'REGEXT' => $laCita['RMRCIT'],
					'ESTEXT' => $lcEstadoExtra,
					'OP2EXT' => $lcConsecOrden,
					'UMOEXT' => $tcUser,
					'PMOEXT' => $tcProg,
					'FMOEXT' => $lnFechaCrea,
					'HMOEXT' => $lnHoraCrea,
				];
				$laWhere = [
					'TIDEXT' => $taDatos['tipoId'],
					'NIDEXT' => $taDatos['numeroId'],
					'CCIEXT' => $taDatos['cnsCita'],
				];
				$this->oDb->from('UNIEXT')->where($laWhere)->actualizar($laData);
			}

			// Crear RIACID
			$laData = [
				'TIDCID' => $taDatos['tipoId'],
				'NIDCID' => $taDatos['numeroId'],
				'UNICID' => $taDatos['unidadAgenda'],
				'CCICID' => $taDatos['cnsCita'],
				'CCUCID' => $taDatos['cnsCant'],
				'ORDCID' => $lnOrdenCita,
				'FGRCID' => $lnFechaCrea,
				'HGRCID' => $lnHoraCrea,
				'CLICID' => $lnConsLinea,
				'CUPCID' => $taDatos['cup'],
				'ESTCID' => $lnEstado,
				'NINCID' => $laCita['NINCIT'],
				'CODCID' => $laCita['CODCIT'],
				'CD2CID' => $taDatos['codTipo'],
				'RMECID' => $laCita['RMRCIT'],
				'FRLCID' => $taDatos['fecha'],
				'HOCCID' => $taDatos['hora'],
				'VIACID' => $laCita['VIACIT'],
				'DESCID' => $taDatos['codMotivo'],
				'OP7CID' => $lnCnsCancela,
				'USRCID' => $tcUser,
				'PGMCID' => $tcProg,
				'FECCID' => $lnFechaCrea,
				'HORCID' => $lnHoraCrea,
			];
			$this->oDb->from('RIACID')->insertar($laData);

			$laRta = ['success'=>true, 'message'=>'Cita cancelada'];
		}

		return array_merge(['fechahora'=>date('Y-m-d H:i:s')], $laRta);
	}


	public function aCitaElectro()
	{
		return $this->aCitaElectro;
	}


	/*
	 *	Retorna número del día de la semana
	 *	@param int $tdFecha: fecha formato YYYYMMDD
	 *	@return int número del día de la semana (1=domingo, 2=lunes, etc)
	 */
	private function diaSemana($lnFecha, $tcFormato = 'Ymd')
	{
		$ldFecha = \DateTime::createFromFormat($tcFormato, $lnFecha);
		$lnDiaSemana = $ldFecha->format('N')+1;
		if($lnDiaSemana==8) $lnDiaSemana=1;

		return $lnDiaSemana;
	}


	private function obtenerTiempoEntrega($tcUnidadAgenda, $tcRegMedico, $tcCUP)
	{
		$laTemp = $this->oDb
			->select('OP2UCO, OP3UCO')
			->from('UNICON')
			->where([
				'CODUCO'=>$tcUnidadAgenda,
				'REGUCO'=>$tcRegMedico,
				'PROUCO'=>$tcCUP,
			])
			->getAll('array');
		return [
			'tiempoEntrega'  => $laTemp['OP3UCO'] ?? 0,
			'tiempoDuracion' => intval($laTemp['OP2UCO'] ?? '0'),
		];
	}


	/*
	 *	Consulta y retorna datos del médico
	 *	Tipos 1-Medico, 3-Odontólogo, 6-Anestesiologo, 8-Nutricionista, 10-Fisioterapeuta, 91-Enfermera(o) jefe
	 *	@param string $tcRegistro registro médico a consultar (completado a 13 dígitos con ceros a la izquierda)
	 *	@return array con los datos obtenidos
	 */
	public function consultarMedico($tcRegistro)
	{
		$laTemp = $this->oDb
			->from('RIARGMN')
			->where(['REGMED'=>$tcRegistro])
			// ->in('TPMRGM', [1,3,6,8,10,91])
			->get('array');
		return $this->oDb->numRows()>0 ? $laTemp : false;
	}


	/*
	 *	Consulta y retorna datos del cup
	 *	@param string $tcCodCup código del cup
	 *	@return array con los datos obtenidos
	 */
	public function consultarCup($tcCodCup)
	{
		$laTemp = $this->oDb
			->from('RIACUP')
			->where(['CODCUP'=>$tcCodCup])
			->get('array');
		return $this->oDb->numRows()>0 ? $laTemp : false;
	}


	/*
	 *	Consulta y retorna datos de la especialidad
	 *	@param string $tcCodEsp código de la especialidad
	 *	@return array con los datos obtenidos
	 */
	public function consultarEspecialidad($tcCodEsp)
	{
		$laTemp = $this->oDb
			->from('RIAESPE')
			->where(['CODESP'=>$tcCodEsp])
			->get('array');
		return $this->oDb->numRows()>0 ? $laTemp : false;
	}


	/*
	 *	Consulta y retorna la preparación de un cup determinado
	 *	@param string $tcCup CUP a consultar
	 */
	private function preparacionCup($tcCup)
	{
		$lcDatos = $this->consultarParametros('preparacion', $tcCup);
		return $lcDatos['PREPARACION'] ?? '';
	}


	/*
	 *	Envío de correo electrónico al paciente
	 */
	private function enviarEmail($taDatos)
	{
		$loMail = new MailEnviar();
		if (!$loMail->validarEmail($taDatos['correo'])) {
			return ['success'=>false, 'message'=>'Correo no válido'];
		}

		// Plantilla de derechos y deberes
		$loMail->obtenerPlantilla('GENERAL', 'DERECHOS');
		$lcDerechosDeberes = $loMail->cPlantilla;

		// Plantilla para enviar
		$lcTipoPlantilla = $taDatos['teleconsulta']=='S' ? 'SOLTELE' : (empty($taDatos['dosisCovid']) ? 'SOLPRES' : 'SOLVACUN');
		$loMail->obtenerPlantilla('CITAS', $lcTipoPlantilla);
		$lcPlantilla = utf8_encode($loMail->cPlantilla);

		// Configuración para el envío
		$laConfigToda = $loMail->obtenerConfiguracion('CITAS');
		$laConfig = $laConfigToda['config'];

		// Obtener datos para el envío

		// preparación
		$lcPreparacion = '';
		if (is_array($taDatos['preparacion'])) {
			foreach ($taDatos['preparacion'] as $lcPrepara) {
				$lcPreparacion .= "<li>$lcPrepara</li>";
			}
		}
		if (empty($lcPreparacion)) {
			$lcPreparacion = trim($this->oDb->ObtenerTabMae1('DE2TMA', 'DATING', "CL1TMA='SINPRE' AND ESTTMA=''", null, "No se requieren preparaciones previas."));
		} else {
			$lcPreparacion = "<b>Es necesario cumplir con las siguientes preparaciones:</b><ol>$lcPreparacion</ol>";
		}

		// Nombre del médico
		if (empty($taDatos['regMedico'])) {
			$lcMedico = '';
		} else {
			$laTemp = $this->oDb->select('NOMMED,NNOMED')->from('RIARGMN5')->where(['REGMED'=>$taDatos['regMedico']])->get('array');
			if ($this->oDb->numRows()>0) $lcMedico = trim(trim($laTemp['NOMMED']).' '.trim($laTemp['NNOMED']));
		}
		if (!empty($lcMedico)) $lcMedico = ', con el Dr.'.$lcMedico;

		// Descripción procedimiento
		$laTemp = $this->oDb->select('DESCUP')->from('RIACUPL0')->where(['CODCUP'=>$taDatos['cup']])->get('array');
		$lcDscProc = $this->oDb->numRows()>0 ? trim($laTemp['DESCUP']) : '';

		// Area
		$lcArea = $lcAreaEmail = $lcAreaDireccion = $lcAreaTelefono = '';
		$laTemp = $this->oDb->select('DESUNA, OP5UNA')->from('UNIAGEL01')->where(['CODUNA'=>$taDatos['unidadAgenda']])->get('array');
		if ($this->oDb->numRows()>0) {
			$lcArea = trim($laTemp['DESUNA']);
			$lcAreaEmail = trim(substr($laTemp['OP5UNA'], 0, 150));
		}

		// Fecha y hora de la cita
		$ldFechaHoraCita = \DateTime::createFromFormat('YmdHis', $taDatos['fecha'].$taDatos['hora']);
		$lcFechaHoraCita = $ldFechaHoraCita->format('Y/m/d H:i:s');

		// Reemplazar datos en la plantilla
		$laDatos = [
			'[[NombrePaciente]]'	=> $taDatos['nombre1'].' '.$taDatos['nombre2'].' '.$taDatos['apellido1'].' '.$taDatos['apellido2'],
			'[[FechaCita]]'			=> $lcFechaHoraCita,
			'[[MedicoCita]]'		=> $lcMedico,
			'[[ProDescripcion]]'	=> $lcDscProc ?? '',
			'[[Preparaciones]]'		=> $lcPreparacion,
			'[[Area]]'				=> $lcArea,
			'[[AreaEmail]]'			=> $lcAreaEmail,
			'[[AreaDireccion]]'		=> $lcAreaDireccion,
			'[[AreaTelefono]]'		=> $lcAreaTelefono,
			'[[DerechosDeberes]]'	=> $lcDerechosDeberes,
		];
		$lcPlantilla = strtr($lcPlantilla, $laDatos);
		$laConfig['tcTO'] = $taDatos['correo'];
		$laConfig['tcBody'] = $lcPlantilla;

		// Enviar correo
		$lcResult = $loMail->enviar($laConfig);
		if (!empty($lcResult)) {
			$laRta = ['success'=>true, 'message'=>'Correo enviado'];
		} else {
			$laRta = ['success'=>false, 'message'=>'Correo no se puedo enviar'];
		}

		return $laRta;
	}

}
