<?php
namespace NUCLEO;

require_once __DIR__ . '/class.Persona.php';
require_once __DIR__ . '/class.Historia_Clinica_Ingreso.php';
require_once __DIR__ . '/class.Consecutivos.php';
require_once __DIR__ . '/class.Ingreso.php';
require_once __DIR__ . '/class.AntecedentesConsulta.php';

use NUCLEO\Persona;
use NUCLEO\Historia_Clinica_Ingreso;
use NUCLEO\AntecedentesConsulta;

class ConsultaUrgencias
{
	protected $oDb;
	protected $cUsuCre='';
	protected $cPrgCre='';
	protected $cFecCre='';
	protected $cHorCre='';
	protected $cChrEnter='';
	private $oIngreso = null;

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
    }

	function IniciaDatosAuditoria()
	{
		$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
		$this->cUsuCre = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getUsuario():'');
		$this->cPrgCre = 'CENSOWEB';
		$this->cFecCre = $ltAhora->format('Ymd');
		$this->cHorCre = $ltAhora->format('His');
		$this->cChrEnter = chr(13);
	}

	/*
	 *	Consulta los pacientes en urgencias
	 */
	public function consultaPacientes($tnFecha=0, $tnEstado=8, $tnIngreso=0, $tcTipoId='', $tnNumId=0, $tcSeccion='')
	{
		$laPacientes = $laWhere = [];

		// Condiciones
		$tnFecha = intval($tnFecha);
		if (!empty($tnFecha)) {
			$laWhere['J.FERORD'] = $tnFecha;
			$laWhere['W.ESTING'] = '2';
		}
		$tnEstado = intval($tnEstado);
		$laWhere['J.ESTORD'] = empty($tnEstado)? 8: $tnEstado;
		if ($tnIngreso) $laWhere['J.NINORD'] = $tnIngreso;
		if ($tcTipoId && $tnNumId){
			$laWhere['J.TIDORD'] = $tcTipoId;
			$laWhere['J.NIDORD'] = $tnNumId;
		}
		if ($tcSeccion) $laWhere['J.SECHAB'] = $tcSeccion;

		$laTabla = $this->oDb
			->select('J.TIDORD TIPO_DOC, J.NIDORD NUM_DOC, J.CCOORD, J.CCIORD, J.EVOORD, J.ESTORD, J.COAORD, J.FERORD, J.HRLORD')
			->select('W.FEIING FECHA_ING, W.NIGING INGRESO, W.PLAING, W.VIAING CODVIA, Z.ESTHAB ESTADO, P.FNAPAC FECHA_NAC, D.DOCUME')
			->select("P.SEXPAC AS CODGENERO, IFNULL((SELECT TRIM(DE2TMA) FROM TABMAE WHERE TIPTMA='SEXPAC' AND CL1TMA=P.SEXPAC), '') AS GENERO")
			->select('TRIM(P.NM1PAC)||\' \'||TRIM(P.NM2PAC)||\' \'||TRIM(P.AP1PAC)||\' \'||TRIM(P.AP2PAC) AS PACIENTE')
			->select('T.FETTRI, T.HRTTRI, T.CLMTRI, T.OP6TRI, TRIM(Z.SECHAB) SECCION, TRIM(Z.NUMHAB) HABITACION')
			->select('IFNULL(L.DSCCON,\'\') DSCCON, IFNULL(L.IN4CON,0) IN4CON')
			->select('G.TP1PAL, G.CP1PAL')
			->from('RIAORD J')
			->leftJoin('RIAING  W', 'J.NINORD=W.NIGING', null)
			->leftJoin('FACHAB  Z', 'W.NIGING=Z.INGHAB', null)
			->innerJoin('RIAPAC P', 'J.TIDORD=P.TIDPAC AND J.NIDORD=P.NIDPAC', null)
			->leftJoin('TRIAGU  T', 'W.NIGING=T.NIGTRI', null)
			->leftJoin('FACPLNC L', 'W.PLAING=L.PLNCON', null)
			->leftJoin('RIATI   D', 'J.TIDORD=D.TIPDOC', null)
			->leftJoin('PACALT  G', 'J.TIDORD=G.TIDPAL AND J.NIDORD=G.NIDPAL')
			->where('J.COAORD IN (\'890701\', \'890702\')')
			->where($laWhere)
			->orderBy('T.CLMTRI, Z.ESTHAB, J.FERORD, J.HRLORD')
			->getAll('array');

		if (is_array($laTabla)){
			$lnEdadPediatrica = (new Persona())->edadPediatrica();

			foreach($laTabla as $laFila){
				$laFila = array_map('trim', $laFila);
				$laFila['CODUBI'] = 0;
				$laFila['DSCUBI'] = '';

				// Si el ingreso ya está no lo agrega nuevamente
				if (isset($laPacientes[$laFila['INGRESO']])) {
					if ($laPacientes[$laFila['INGRESO']]['ESTADO']=='6' && $laFila['ESTADO']!='6') {
						$laPacientes[$laFila['INGRESO']]['CAMA'] = $laFila['CAMA'];
						$laPacientes[$laFila['INGRESO']]['ESTADO'] = $laFila['ESTADO'];
					}
					continue;
				}

				if ($laFila['ESTADO']=='6') $laFila['CAMA']='-';

				$laFila['EDAD_A'] = 0;
				if(is_numeric($laFila['FECHA_NAC']??'x') && is_numeric($laFila['FECHA_ING']??'x')){
					if($laFila['FECHA_NAC']>18000000 && $laFila['FECHA_ING']>18000000){
						$toDiferencia = date_diff(date_create($laFila['FECHA_NAC']), date_create($laFila['FECHA_ING']));
						$laFila['EDAD_A'] = intval($toDiferencia->format('%y'));
						if ($laFila['EDAD_A']<=$lnEdadPediatrica) {
							$laFila['CODUBI'] = 4;
							$laFila['DSCUBI'] = 'PEDIATRICO';
						}
					}
				}
				if ($laFila['CODUBI']==0) {
					if (!empty($laFila['PLAING'])) {
						$laFila['CODUBI'] = $laFila['IN4CON']==1? '2': ($laFila['IN4CON']==2? '1': '');
						$laFila['DSCUBI'] = $laFila['IN4CON']==1? 'M.P.P.': ($laFila['IN4CON']==2? 'P.O.S.': '');
					}
				}

				//Enfermedad actual
				$laFila['ENFACT'] = '';
				$laEnfAct = $this->oDb
					->select('DESHTR')
					->from('HISTRI')
					->where([
						'TIDHTR' => $laFila['TIPO_DOC'],
						'NIDHTR' => $laFila['NUM_DOC'],
						'INGHTR' => $laFila['INGRESO'],
						'INDHTR' => 10,
					])
					->getAll('array');
				if (is_array($laEnfAct)) {
					foreach ($laEnfAct as $laItem) {
						$laFila['ENFACT'] .= $laItem['DESHTR'];
					}
				}

				if ($laFila['FETTRI']!=='0') {
					$laFila['FECHA_RT'] = $laFila['FETTRI'];
					$laFila['HORA_RT'] = $laFila['HRTTRI'];
				} else {
					$laFila['FECHA_RT'] = $laFila['FERORD'];
					$laFila['HORA_RT'] = $laFila['HRLORD'];
				}

				$laPacientes[$laFila['INGRESO']] = $laFila;
			}
		}
		unset($laTabla);
		$laReturn = [];
		foreach ($laPacientes as $laPaciente) {
			$laReturn[] = $laPaciente;
		}

		return $laReturn;
	}

	/*
	 *	Consulta los estados de la consulta de urgencias
	 */
	public function estadosConsulta()
	{
		$laEstados = [];
		$laTabla = $this->oDb
			->select('CDSTAB, DSLTAB')
			->from('RIATAB')
			->where('CDTTAB=5 AND CDRTAB=1 AND CDSTAB<>0')
			->orderBy('DSLTAB')
			->getAll('array');
		if (is_array($laTabla)){
			foreach($laTabla as $laFila){
				$laEstados[$laFila['CDSTAB']] = trim($laFila['DSLTAB']);
			}
		}
		return $laEstados;
	}

	/*
	 *	Consulta tipos de triage
	 */
	public function tiposTriage()
	{
		$laTriage = [];
		$laTabla = $this->oDb
			->select('SUBSTR(DE1TMA,1,40) AS DESCRIPCION, SUBSTR(CL2TMA,1,2) AS CODIGO, OP3TMA AS TIEMPO, OP4TMA AS COLOR')
			->from('TABMAE')
			->where('TIPTMA=\'DATING\' AND CL1TMA=\'7\' AND ESTTMA=\'\'')
			->orderBy('DE1TMA')
			->getAll('array');
		if (is_array($laTabla)){
			foreach($laTabla as $laFila){
				$laFila = array_map('trim', $laFila);
				$laTriage[$laFila['CODIGO']] = $laFila;
			}
		}
		return $laTriage;
	}

	/*
	 *	valida tipo de usuario
	 */
	public function tipoUsuarioValido()
	{
		$lcTipoUsuario = $_SESSION[HCW_NAME]->oUsuario->getTipoUsuario();
		$lcAutorizado = trim($this->oDb->obtenerTabMae1('DE2TMA', 'USUHCL', "CL1TMA='$lcTipoUsuario' AND CL2TMA='A' AND ESTTMA=''", null, ''));
		if (empty($lcAutorizado)) {
			$laRetorna = [
				'error' => 'USUARIO NO TIENE AUTORIZACION',
				'valido'=> false,
			];
		} else {
			$laRetorna = [
				'error' => '',
				'valido'=> true,
			];
		}
		return $laRetorna;
	}

	/*
	 *	valida si el paciente no está siendo atendido y lo bloquea
	 */
	public function bloquearIngreso($tnIngreso)
	{
		$laRetorna['error'] = '';
		$laIng = $this->oDb->count('CREING','CUENTA')->from('RIAING')->where(['NIGING'=>$tnIngreso,'CREING'=>0])->get('array');

		if (is_array($laIng)) {
			if ($laIng['CUENTA']>0) {

				// Bloquea el ingreso
				try {
					$ltAhora = new \DateTime($this->oDb->fechaHoraSistema());
					$this->oDb
						->from('RIAING')
						->where(['NIGING'=>$tnIngreso])
						->actualizar([
							'CREING'=>1,
							'UMOING'=>$_SESSION[HCW_NAME]->oUsuario->getUsuario(),
							'PMOING'=>'CNSURGWEB',
							'FMOING'=>$ltAhora->format('Ymd'),
							'HMOING'=>$ltAhora->format('His'),
						]);
				} catch (Exception $e) {
					$laRetorna['error'] = 'Ocurrió un error al bloquear el ingreso. Intente de nuevo por favor';
				}
			} else {
				$laRetorna['error'] = 'Paciente esta siendo atendido por otro médico, seleccione otro paciente por favor';
			}
		} else {
			$laRetorna['error'] = 'No se pudo consultar el ingreso. Intente de nuevo por favor';
		}
		return $laRetorna;
	}

	/*
	 *	liberar bloqueo de un paciente
	 */
	public function desbloquearIngreso($tnIngreso)
	{
		$laRetorna['error'] = '';
		try {
			$ltAhora = new \DateTime($this->oDb->fechaHoraSistema());
			$this->oDb
				->from('RIAING')
				->where(['NIGING'=>$tnIngreso])
				->actualizar([
					'CREING'=>0,
					'UMOING'=>$_SESSION[HCW_NAME]->oUsuario->getUsuario(),
					'PMOING'=>'CNSURGWEB',
					'FMOING'=>$ltAhora->format('Ymd'),
					'HMOING'=>$ltAhora->format('His'),
				]);
		} catch (Exception $e) {
			$laRetorna['error'] = 'Ocurrió un error al desbloquear el ingreso. Intente de nuevo por favor';
		}
		return $laRetorna;
	}

	/*
	 *	Mensaje para mostrar al entrar a HC
	 */
	public function atendidoReingreso($tnIngreso)
	{
		$lcTexto = '';
		$laTabla = $this->oDb
			->select('IFNULL((SELECT TRIM(SUBSTR(DE2TMA, 1, 30)) FROM TABMAE WHERE TIPTMA=\'DATING\' AND CL1TMA=\'PACATEN\' AND CL2TMA=TRIM(SUBSTR(A.OP2TRI, 1, 2))), \'\') DESCRIPCION')
			->select('IFNULL((SELECT TRIM(SUBSTR(DE2TMA, 1, 20)) FROM TABMAE WHERE TIPTMA=\'DATING\' AND CL1TMA=\'ATEINST\' AND CL2TMA=TRIM(SUBSTR(A.OP2TRI, 4, 1))), \'\') INSTITUCION')
			->from('TRIAGU A')
			->where(['A.NIGTRI'=>$tnIngreso])
			->where('A.OP2TRI<>\'\'')
			->get('array');
		if (is_array($laTabla)){
			if (count($laTabla)>0){
				$lcTexto = trim($this->oDb->obtenerTabMae1('DE2TMA', 'DATING', 'CL1TMA=\'MSGATEN\' AND ESTTMA=\'\'', null, 'Atendido en la clínica u otra institución:'));
				$lcTexto .= ' '.$laTabla['DESCRIPCION'].(empty($laTabla['INSTITUCION'])? '': ' en '.$laTabla['INSTITUCION']);
			}
		}
		return $lcTexto;
	}

	/*
	 *	Mensaje Covid-19
	 */
	public function mensajeCovid19($tnIngreso)
	{
		$laTabla = $this->oDb
			->select('TRIM(SUBSTR(DESHTR, 4, 215)) AS DESCRIPCION')
			->from('HISTRI')
			->where(['INGHTR'=>$tnIngreso])
			->where('INDHTR=59')
			->get('array');
		return trim($laTabla['DESCRIPCION']??'');
	}

	public function esPacienteUrgencias($tnIngreso=0, $tcVia='', $tcSección='')
	{
		$llEnUrgencias = false;

		if(empty($tcVia)){
			$laTemp = $this->oDb
				->select('VIAING')
				->from('RIAING')
				->where('NIGING', '=', $tnIngreso)
				->get('array');

			if(is_array($laTemp)){
				if(count($laTemp)>0){
					$tcVia = $laTemp['VIAING'];
				}
			}
		}

		if(empty($tcSección)){
			$laTemp = $this->oDb
				->select('SECHAB')
				->from('FACHABL3')
				->where('INGHAB', '=', $tnIngreso)
				->get('array');

			if(is_array($laTemp)){
				if(count($laTemp)>0){
					$tcSección = $laTemp['SECHAB'];
				}
			}
		}

		if($tcSección=='TU'){
			$laTemp = $this->oDb
				->select('INGIUR')
				->from('INGURGTL01')
				->where('INGIUR', '=', $tnIngreso)
				->get('array');

			if(is_array($laTemp)){
				if(count($laTemp)>0){
					$llEnUrgencias = true;
				}
			}
		}

		// Verifica vía de ingresos
		$llEnUrgencias = ($tcVia=='01' || $tcSección=='TU' || $llEnUrgencias);
		// Verifica para via de ingreso Hospitalizacion y sección TU
		if(!$llEnUrgencias){
			if($tcVia=='05'){
				$laTemp = $this->oDb
					->select('SECHAB')
					->from('FACHAB')
					->where([
							 'IDDHAB'=>'0',
							 'INGHAB'=>$tnIngreso,
							])
					->where("(LIQHAB='U' OR SECHAB='TU')")
					->in('ESTHAB', ['1','2'])
					->get('array');

				if(is_array($laTemp)){
					if(count($laTemp)>0){
						$llEnUrgencias = true;
					}
				}
			}
		}
		unset($laTemp);
		return $llEnUrgencias;
	}

	public function PuedeEvolucionarUrg($tnIngreso=0, $tcVia='')
	{
		$laRetorno = [
			'Mensaje'=>'',
			'Valido'=>true,
		];
		$laTemp = $this->oDb
			->select('VIAING')
			->from('RIAING')
			->where('NIGING', '=', $tnIngreso)
			->get('array');

		if(is_array($laTemp)){
			if(count($laTemp)>0){
				$tcVia = $laTemp['VIAING'];
			}
		}

		$loObjCU = new Historia_Clinica_Ingreso();
		$laRetorno = $loObjCU->validaExisteHC($tnIngreso, $tcVia);
		if ($laRetorno['Valido']==true){
			$llValidarVia = $this->oDb->obtenerTabMae1('OP3TMA','EVOLUC',['CL1TMA'=>'EVOSINHC','CL2TMA'=>'VIAING','ESTTMA'=>''],null,'')!==0;
			if($llValidarVia){
				// verifica que sea paciente de urgencias
				$llEsPacienteUrg = $this->esPacienteUrgencias($tnIngreso, $tcVia);
				if(!$llEsPacienteUrg){
					$laRetorno = [
						'Mensaje'=>'Vía no es urgencias. El paciente no tiene Historia Clínica',
						'Valido'=>false,
					];
					return $laRetorno ;
				}
			}
		}else{
			return $laRetorno ;
		}

		// Obtiene el tiempo desde la primer evolución
		$laTemp = $this->oDb
			->select('FECEVL, HOREVL')
			->from('EVOLUC')
			->where('NINEVL', '=', $tnIngreso)
			->orderBy('FECEVL,HOREVL')
			->get('array');

		if(!is_array($laTemp)){$laTemp=[];}
		if(count($laTemp)==0){
			$laRetorno = [
				'Mensaje'=>'',
				'Valido'=>true,
			];
			return $laRetorno;
		}else{
			$lnHorasLimite = $this->oDb->obtenerTabMae1('OP3TMA', 'EVOLUC', "CL1TMA='EVOSINHC' AND CL2TMA='TIMEEVO' AND ESTTMA=''", null, 0);
			if($lnHorasLimite==0){
				$laRetorno = [
					'Mensaje'=>'',
					'Valido'=>true,
				];
				return $laRetorno;
			}else{
				$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema());
				$ltFechoraEvolucion = new \DateTime(AplicacionFunciones::formatFechaHora('fechahora', $laTemp['FECEVL'].' '.$laTemp['HOREVL']));
				$laHoras = $ltAhora->diff($ltFechoraEvolucion);
				$lnHorasDif = $laHoras->format('%h') - $lnHorasLimite;
				if($lnHorasDif > 0 || ($lnHorasDif==0 && $laHoras->format('%i')>0) ){
					$laRetorno = [
						'Mensaje'=>'Horas límite excedidas. El paciente no tiene Historia Clínica.',
						'Valido'=>false,
					];
				}else{
					$laRetorno = [
						'Mensaje'=>'',
						'Valido'=>true,
					];
				}
			}
		}
		return $laRetorno ;
	}

	/* 	Consulta censo urgencias  */
	public function consultaCensoUrgencias($tnFechaInicio=0, $tnFechaFinal=0, $tnIngreso=0, $tcSeccion='', $tcTipoCenso='',
											$tcUbicacionMed='', $tcSinHabitacion='', $tcGenero='', $tcPacienteTipo='')
	{
		$laReturn = [];
		if (strlen($tnIngreso)<=9){
			$laPacientes=$laWhere=[];
			$ltFechaHoraSistema = new \DateTime( $this->oDb->fechaHoraSistema());
			$ldFechaSistema = new \DateTime(substr($this->oDb->fechaHoraSistema(),0,10));
			$lcHorasDiasMaximas=trim($this->oDb->obtenerTabmae1('de2tma', 'CENPAC', "CL1TMA='DIAHOR' AND ESTTMA=''", null, ''));
			$lnHorasMaximaUrgencias=explode('~', $lcHorasDiasMaximas)[0];
			$lnDiasMaximoHospitalizados=explode('~', $lcHorasDiasMaximas)[1];
			$loAntecdente = new AntecedentesConsulta();

			if ($tnFechaInicio && $tnFechaFinal) {
				$this->oDb->between('J.FHOURC', $tnFechaInicio, $tnFechaFinal);
			} elseif ($tnFechaInicio && empty($tnFechaFinal)) {
				$laWhere['J.FHOURC'] = $tnFechaInicio;
			}
			if ($tcTipoCenso) $laWhere['J.UBPURC'] = $tcTipoCenso;
			if ($tnIngreso) { $laWhere['J.INGURC'] = $tnIngreso; }else{ $this->oDb->where('J.ESTURC', '<>', 99); }
			if ($tcSeccion) $laWhere['Z.SECHAB'] = $tcSeccion;
			if ($tcUbicacionMed) $laWhere['J.UMEURC'] = $tcUbicacionMed;
			if ($tcGenero) $laWhere['P.SEXPAC'] = $tcGenero;

			if ($tcPacienteTipo=='90'){ $laWhere['J.EXAURC'] = '';
			}else{ if ($tcPacienteTipo) $laWhere['J.EXAURC'] = $tcPacienteTipo; }

			$laTabla = $this->oDb
				->select('W.TIDING, W.NIDING, TRIM(J.UBPURC) UBPURC, TRIM(J.ALTURC) ALTURC, TRIM(J.SPRURC) SPRURC, TRIM(J.MEDURC) MEDURC')
				->select('TRIM(J.UMEURC) UMEURC, TRIM(J.EXAURC) EXAURC')
				->select('W.FEIING, W.NIGING, W.PLAING, W.VIAING, W.ENTING,Z.ESTHAB, P.FNAPAC, P.SEXPAC, D.DOCUME')
				->select('TRIM(P.NM1PAC)||\' \'||TRIM(P.NM2PAC)||\' \'||TRIM(P.AP1PAC)||\' \'||TRIM(P.AP2PAC) AS PACIENTE')
				->select('TRIM(Z.SECHAB)||\'-\'||TRIM(Z.NUMHAB) AS CAMA')
				->select('TRIM(IFNULL(L.DSCCON,\'\')) DSCCON, IFNULL(L.IN4CON,0) IN4CON')
				->select('G.TP1PAL, G.CP1PAL, J.FHOURC, J.HHOURC, J.ESTURC')
				->select('(SELECT TRIM(DESURD) FROM CENURD WHERE INGURD=J.INGURC AND IN1URD=1 ORDER BY CONURD DESC FETCH FIRST 1 ROWS ONLY) AS DESCDIAG')
				->select('(SELECT TRIM(DESURD) FROM CENURD WHERE INGURD=J.INGURC AND IN1URD=2 ORDER BY CONURD DESC FETCH FIRST 1 ROWS ONLY) AS PLANMANEJO')
				->select('(SELECT TRIM(DESURD) FROM CENURD WHERE INGURD=J.INGURC AND IN1URD=3 ORDER BY CONURD DESC FETCH FIRST 1 ROWS ONLY) AS DIETAPAC')
				->select('(SELECT TRIM(DESURD) FROM CENURD WHERE INGURD=J.INGURC AND IN1URD=4 ORDER BY CONURD DESC FETCH FIRST 1 ROWS ONLY) AS ADMINISTRATIVO')
				->select('(SELECT TRIM(DESURD) FROM CENURD WHERE INGURD=J.INGURC AND IN1URD=6 ORDER BY CONURD DESC FETCH FIRST 1 ROWS ONLY) AS ENFERMERIA')
				->select('(SELECT TRIM(DESURD) FROM CENURD WHERE INGURD=J.INGURC AND IN1URD=7 ORDER BY CONURD DESC FETCH FIRST 1 ROWS ONLY) AS RECOMENDACIONES')
				->select('(SELECT TRIM(DESURD) FROM CENURD WHERE INGURD=J.INGURC AND IN1URD=9 ORDER BY CONURD DESC FETCH FIRST 1 ROWS ONLY) AS PENDIENTESREVISTATARDE')
				->select('M.MEDINT REGISTRO_MEDICO, UPPER(TRIM(N.NOMMED)||\' \'||TRIM(N.NNOMED)) NOMBRE_MEDICO, TRIM(Q.DESESP) ESPECIALIDAD')
				->select('UPPER(TRIM(O.NOMMED)||\' \'||TRIM(O.NNOMED)) NOMBRE_MEDICO_URGENCIAS, TRIM(R.DESESP) ESPECIALIDAD_URGENCIAS')
				->from('CENURC J')
				->leftJoin('RIAING  W', 'J.INGURC=W.NIGING', null)
				->leftJoin('FACHAB  Z', 'W.NIGING=Z.INGHAB', null)
				->innerJoin('RIAPAC P', 'W.TIDING=P.TIDPAC AND W.NIDING=P.NIDPAC', null)
				->leftJoin('FACPLNC L', 'W.PLAING=L.PLNCON', null)
				->leftJoin('RIATI   D', 'W.TIDING=D.TIPDOC', null)
				->leftJoin('PACALT  G', 'W.TIDING=G.TIDPAL AND W.NIDING=G.NIDPAL')
				->leftJoin('RIAINGT M', 'J.INGURC=M.NIGINT AND M.MEDINT>0', null)
				->leftJoin('RIARGMN N', 'M.MEDINT=N.NIDRGM', null)
				->leftJoin('RIAESPE Q', 'N.CODRGM=Q.CODESP', null)
				->leftJoin('RIARGMN O', 'J.MEDURC=O.REGMED', null)
				->leftJoin('RIAESPE R', 'O.CODRGM=R.CODESP', null)
				->where($laWhere)
				->orderBy('J.FHOURC, J.HHOURC')
				->getAll('array');
			if (is_array($laTabla)){
				$lnEdadPediatrica = (new Persona())->edadPediatrica();
				$lnNoRegistra='-';

				foreach($laTabla as $laFila){
					$lcAntPatologicos='';
					$lnDiferenciaHoras=0;
					$laFila = array_map('trim', $laFila);
					$laFila['CODUBI'] = 0;
					$laFila['DSCUBI'] = '';

					// Si el ingreso ya está no lo agrega nuevamente
					if (isset($laPacientes[$laFila['NIGING']])) {
						if ($laPacientes[$laFila['NIGING']]['ESTHAB']=='6' && $laFila['ESTHAB']!='6') {
							$laPacientes[$laFila['NIGING']]['CAMA']	  = $laFila['CAMA'];
							$laPacientes[$laFila['NIGING']]['ESTHAB'] = $laFila['ESTHAB'];
						}
						continue;
					}

					$laFila['EDAD_A'] = 0;
					$laFila['EDAD_PAC'] = '';
					if(is_numeric($laFila['FNAPAC']??'x') && is_numeric($laFila['FEIING']??'x')){
						if($laFila['FNAPAC']>18000000 && $laFila['FEIING']>18000000){
							$toDiferencia = date_diff(date_create($laFila['FNAPAC']), date_create($laFila['FEIING']));
							$laFila['EDAD_A'] = $toDiferencia->format('%y').'A ' .$toDiferencia->format('%m').'M ' .$toDiferencia->format('%d').'D';
							$laFila['EDAD_PAC'] = $toDiferencia->format('%y').' años, ' .$toDiferencia->format('%m').' meses, ' .$toDiferencia->format('%d').' días ';
							if ($laFila['EDAD_A']<=$lnEdadPediatrica) {
								$laFila['CODUBI'] = 4;
								$laFila['DSCUBI'] = 'PEDIATRICO';
							}
						}
					}
					if ($laFila['CODUBI']==0) {
						if (!empty($laFila['PLAING'])) {
							$laFila['CODUBI'] = $laFila['IN4CON']==1? '2': ($laFila['IN4CON']==2? '1': '');
							$laFila['DSCUBI'] = $laFila['IN4CON']==1? 'Prepagada': ($laFila['IN4CON']==2? 'POS': '');
						}
					}

					if (intval($laFila['FHOURC'])>0){
						$llFechaHoraIngreso = $laFila['FHOURC'].str_pad($laFila['HHOURC'], 6, '0', STR_PAD_LEFT);
						$llFechaHoraResultado = date_create_from_format('YmdHis', $llFechaHoraIngreso);
						$llFechaFormato = new \DateTime(date_format($llFechaHoraResultado, 'Y-m-d H:i:s'));
						$lcIntervalo = $ltFechaHoraSistema->diff($llFechaFormato);
						$lnDiferenciaHoras = intval(($lcIntervalo->y * 365.25 + $lcIntervalo->m * 30 + $lcIntervalo->d) * 24 + $lcIntervalo->h + $lcIntervalo->i/60);
						$ldFechaHospitaliza = new \DateTime(AplicacionFunciones::formatFechaHora('fecha', $laFila['FHOURC']));
						$loIntervalo = date_diff($ldFechaSistema, $ldFechaHospitaliza);
						$lnDiferenciaDias = $loIntervalo->days;
					}
					$lcAntPatologicos=$loAntecdente->ultimoAntecedenteIngreso($laFila['TIDING'],$laFila['NIDING'],$laFila['NIGING']);
					$lcAntPatologicos=$lcAntPatologicos['15']['1'];

					$laFila['ANTECED']=$lnNoRegistra;
					$laFila['DIAGNOS']=!empty(trim($laFila['DESCDIAG'])) ? trim($laFila['DESCDIAG']) : $lnNoRegistra;
					$laFila['PLANM']=!empty(trim($laFila['PLANMANEJO'])) ? trim($laFila['PLANMANEJO']) : $lnNoRegistra;
					$laFila['DIETA']=!empty(trim($laFila['DIETAPAC'])) ? trim($laFila['DIETAPAC']) : $lnNoRegistra;
					$laFila['ADMIN']=!empty(trim($laFila['ADMINISTRATIVO'])) ? trim($laFila['ADMINISTRATIVO']) : $lnNoRegistra;
					$laFila['ENFERM']=!empty(trim($laFila['ENFERMERIA'])) ? trim($laFila['ENFERMERIA']) : $lnNoRegistra;
					$laFila['UBICAMED']=!empty(trim($laFila['UMEURC'])) ? trim($laFila['UMEURC']) : $lnNoRegistra;
					$laFila['PENDREVI']=!empty(trim($laFila['PENDIENTESREVISTATARDE'])) ? trim($laFila['PENDIENTESREVISTATARDE']) : $lnNoRegistra;
					$laFila['RECTRA']=!empty(trim($laFila['RECOMENDACIONES'])) ? trim($laFila['RECOMENDACIONES']) : $lnNoRegistra;
					$laFila['ALTATEMP']=$laFila['ALTURC'];
					$laFila['SALIDAPROV']=$laFila['SPRURC'];
					$laFila['ANTPATOL']=$lcAntPatologicos;
					$lcSeccionHab=substr($laFila['CAMA'], 0, 2);
					$laFila['TIPO_HABITACION']=trim($this->oDb->obtenerTabMae1('OP2TMA', 'SECHAB', "CL1TMA='$lcSeccionHab' AND ESTTMA=''", null, ''));

					if ($laFila['UBPURC']=='U'){
						$laFila['DIFEREN'] = $lnDiferenciaHoras;
						$laFila['MARCADIF'] = $lnDiferenciaHoras>=$lnHorasMaximaUrgencias ? 'S' : 'N';
						$laFila['COLOR']=($laFila['SPRURC']=='S' || $laFila['ESTURC']==9 || $laFila['ESTURC']==99) ? $laFila['ESTURC'] : (($lnDiferenciaHoras>=$lnHorasMaximaUrgencias) ? 2 : $laFila['ESTURC']);
					}else{
						$laFila['DIFEREN'] = $lnDiferenciaDias;
						$laFila['MARCADIF'] = $lnDiferenciaDias>=$lnDiasMaximoHospitalizados ? 'S' : 'N';
						$laFila['COLOR']=$lnDiferenciaDias==$lnDiasMaximoHospitalizados ? 4 : ($lnDiferenciaDias>=$lnDiasMaximoHospitalizados ? 2 : $laFila['ESTURC']);
					}
					$laFila['FECHORAINGRESO'] = AplicacionFunciones::formatFechaHora('fechahora12', $laFila['FHOURC'].' '.$laFila['HHOURC']);

					if ($tcSinHabitacion=='S'){
						if ($laFila['CAMA']==''){
							$laPacientes[$laFila['NIGING']] = $laFila;
						}
					}else{
						$laPacientes[$laFila['NIGING']] = $laFila;
					}
				}
			}
			unset($laTabla);
			$laReturn = [];

			foreach ($laPacientes as $laPaciente) {
				$laReturn[] = $laPaciente;
			}
		}
		return $laReturn;
	}

	public function excluirSalidaCenso($taDatosCenso=[])
	{
		$this->IniciaDatosAuditoria();
		$lnIngreso=isset($taDatosCenso['ingreso']) ? intval($taDatosCenso['ingreso']):0;
		$lnTiporegistro=isset($taDatosCenso['tiporegistro']) ? intval($taDatosCenso['tiporegistro']):0;
		$lcDatosregistro=isset($taDatosCenso['datosregistro']) ? $taDatosCenso['datosregistro'] : '';
		$lcAltaTemprana=isset($taDatosCenso['altatemprana']) ? $taDatosCenso['altatemprana'] : '';
		$lcQuitarSalida=isset($taDatosCenso['quitasalida']) ? $taDatosCenso['quitasalida'] : '';
		$lcUbicacionMedico=isset($taDatosCenso['ubicacionmedico']) ? $taDatosCenso['ubicacionmedico'] : '';
		$lnEstado=$lcQuitarSalida=='Q' ? 0 : $lnTiporegistro;
		$lcSalidaProvisional=$lnTiporegistro==8 ? 'S' : '';
		$laRetorno = [ 'Mensaje'=>'', 'Valido'=>true, ];

		$laParametros=[];
		$lcTabla='CENURC';
		$laDatosCenso = $this->oDb
			->select('ESTURC,SPRURC,UMEURC')
			->from('CENURC')
			->where('INGURC', '=', $lnIngreso)
			->get('array');
		if($this->oDb->numRows()>0){
			$lnEstado=$lnTiporegistro==20 ? $laDatosCenso['ESTURC'] : $lnEstado;
			$lcSalidaProvisional=$lnTiporegistro==20 ? $laDatosCenso['SPRURC'] : $lcSalidaProvisional;
			$lcUbicacionMedico=$lnTiporegistro==20 ? $lcUbicacionMedico : $laDatosCenso['UMEURC'];

			$laDatosUpd = [
				'ESTURC'=>$lnEstado,
				'SPRURC'=>$lcSalidaProvisional,
				'UMEURC'=>$lcUbicacionMedico,
				'UMOURC'=>$this->cUsuCre, 'PMOURC'=>$this->cPrgCre, 'FMOURC'=>$this->cFecCre, 'HMOURC'=>$this->cHorCre,
			];
			$llResultado = $this->oDb->tabla($lcTabla)->where('INGURC', '=', $lnIngreso)->actualizar($laDatosUpd);

			$laParametros=[
				'ingreso'=>$lnIngreso,
				'tiporegistro'=>$lnTiporegistro,
				'datosregistro'=>$lcDatosregistro,
				'altatemprana'=>$lcAltaTemprana,
				'programaguarda'=>$this->cPrgCre,

			];
			$this->registrarInformacion($laParametros);
		}else{
			$laRetorno = [
			'Mensaje'=>'Ingreso no existe en el censo.',
			'Valido'=>false,
			];
		}
		unset($laDatosCenso);
		return $laRetorno ;
	}

	public function cambiarUbicacionPaciente($taDatosCenso=[])
	{
		$this->IniciaDatosAuditoria();
		$lnIngreso=isset($taDatosCenso['ingreso']) ? intval($taDatosCenso['ingreso']):0;
		$lnTiporegistro=isset($taDatosCenso['tiporegistro']) ? intval($taDatosCenso['tiporegistro']):0;
		$lcDatosregistro=isset($taDatosCenso['datosregistro']) ? $taDatosCenso['datosregistro'] : '';
		$lcDatoRegistro=isset($taDatosCenso['datosregistro']) ? $taDatosCenso['datosregistro'] : '';
		$laRetorno = [ 'Mensaje'=>'', 'Valido'=>true, ];

		$laParametros=[];
		$lcTabla='CENURC';
		$laDatosCenso = $this->oDb
			->select('INGURC')
			->from('CENURC')
			->where('INGURC', '=', $lnIngreso)
			->get('array');
		if($this->oDb->numRows()>0){
			$lcDatoRegistro=$lcDatoRegistro=='90'?'':$lcDatoRegistro;
			$laWhere=['INGURC' => $lnIngreso];
			if ($lnTiporegistro==25){ $laData=[ 'UBPURC'=>$lcDatoRegistro, ]; }
			if ($lnTiporegistro==30){ $laData=[ 'EXAURC'=>$lcDatoRegistro, ]; }

			$laDataU=[
				'UMOURC'=>$this->cUsuCre,
				'PMOURC'=>$this->cPrgCre,
				'FMOURC'=>$this->cFecCre,
				'HMOURC'=>$this->cHorCre,
			];
			$this->oDb->from($lcTabla)->where($laWhere)->actualizar(array_merge($laData,$laDataU));

			$laParametros=[
				'ingreso'=>$lnIngreso,
				'tiporegistro'=>$lnTiporegistro,
				'datosregistro'=>$lcDatosregistro,
				'altatemprana'=>'',
				'programaguarda'=>$this->cPrgCre,

			];
			$this->registrarInformacion($laParametros);
		}else{
			$laRetorno = [
			'Mensaje'=>'Ingreso no existe en el censo.',
			'Valido'=>false,
			];
		}
		unset($laDatosCenso);
		return $laRetorno ;
	}


	public function verificarDatos($validacionDatos)
	{
		$laRetornar = [
			'Mensaje'=>'',
			'Objeto'=>'',
			'Valido'=>true,
		];
		$lbRevisar = true;
		return $laRetornar;
	}

	public function registrarInformacion($taDatosCenso=[])
	{
		$this->IniciaDatosAuditoria();
		$laRetorno = [
			'Mensaje'=>'',
			'Valido'=>true,
		];
		$lnIngreso=isset($taDatosCenso['ingreso']) ? intval($taDatosCenso['ingreso']):0;
		$lnTiporegistro=isset($taDatosCenso['tiporegistro']) ? intval($taDatosCenso['tiporegistro']):0;
		$lcDatosregistro=isset($taDatosCenso['datosregistro']) ? $taDatosCenso['datosregistro'] : '';
		$lcAltaTemprana=isset($taDatosCenso['altatemprana']) ? $taDatosCenso['altatemprana'] : '';
		$lcProgramaGuarda=isset($taDatosCenso['programaguarda']) ? $taDatosCenso['programaguarda'] : $this->cPrgCre;

		if (!empty($taDatosCenso)){
			if ($lnIngreso>0){
				if (!empty($lcDatosregistro)){
					$this->IniciaDatosAuditoria();
					$lnConsecutivoCenso=Consecutivos::fCalcularCensoUrgencias($lnIngreso,$lnTiporegistro);
					$lnLinea = 1;
					$lcTabla = 'CENURD';
					$lnLongitud = 500;
					$laTextos = AplicacionFunciones::mb_str_split(trim($lcDatosregistro), $lnLongitud);
					if (is_array($laTextos) && !empty($laTextos)) {
						foreach($laTextos as $lcTexto) {
							$laDatosIns = [
								'INGURD'=>$lnIngreso,
								'CONURD'=>$lnConsecutivoCenso,
								'IN1URD'=>$lnTiporegistro,
								'CLNURD'=>$lnLinea++,
								'DESURD'=>$lcTexto,
								'USRURD'=>$this->cUsuCre,
								'PGMURD'=>$lcProgramaGuarda,
								'FECURD'=>$this->cFecCre,
								'HORURD'=>$this->cHorCre
							];
							$this->oDb->tabla($lcTabla)->insertar($laDatosIns);
						}
					}
					if (!empty($lcAltaTemprana)){
						$lcTabla='CENURC';
						$laDatosUpd = [
							'ALTURC'=>$lcAltaTemprana, 'UMOURC'=>$this->cUsuCre, 'PMOURC'=>$this->cPrgCre, 'FMOURC'=>$this->cFecCre, 'HMOURC'=>$this->cHorCre,
						];
						$llResultado = $this->oDb->tabla($lcTabla)->where('INGURC', '=', $lnIngreso)->actualizar($laDatosUpd);
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

	public function crearBitacora($taDatosCenso=[])
	{
		$this->IniciaDatosAuditoria();
		$laRetorno = [
			'Mensaje'=>'',
			'Valido'=>true,
		];

		$lnIngreso=isset($taDatosCenso['ingreso']) ? intval($taDatosCenso['ingreso']):0;
		$lcTipoIde=isset($taDatosCenso['tipoide']) ? $taDatosCenso['tipoide'] : '';
		$lnIdentificacion=isset($taDatosCenso['identificacion']) ? intval($taDatosCenso['identificacion']):0;
		$lcRegistroGuarda=isset($taDatosCenso['registroguarda']) ? $taDatosCenso['registroguarda'] : '';
		$lcTipoAlta=isset($taDatosCenso['datosregistro']['tipo']) ? $taDatosCenso['datosregistro']['tipo'] : '';
		$lcObservacionesAlta=isset($taDatosCenso['datosregistro']['observacion']) ? $taDatosCenso['datosregistro']['observacion'] : '';
		$lcProgramaGuarda=isset($taDatosCenso['programaguarda']) ? $taDatosCenso['programaguarda'] : $this->cPrgCre;
		$lnEstado=8;
		$loIngreso = new Historia_Clinica_Ingreso();
		$this->oIngreso = $loIngreso->datosIngreso($lnIngreso);
		$lcTipoIde=$this->oIngreso['cTipId'];
		$lnIdentificacion=$this->oIngreso['nNumId'];

		$lcSeccion=$this->oIngreso['cSeccion'];
		$lcHabitacion=$this->oIngreso['cHabita'];
		$lcNitEntidad=str_pad($this->oIngreso['nEntidad'], 13, '0', STR_PAD_LEFT);
		$lnFechaEgreso=$this->oIngreso['nEgresoFecha'];
		$lnHoraEgreso=$this->oIngreso['nEgresoHora'];

		$lcTabla='BITCAB';
		$lnConsecutivoCabecera = $this->oDb->secuencia('SEQ_BITCAB');
		$laDatos = ['CONCAB'=>$lnConsecutivoCabecera,
					'TIPBIT'=>'ALTTEMP',
					'NIGING'=>$lnIngreso,
					'TIDING'=>$lcTipoIde,
					'NIDING'=>$lnIdentificacion,
					'ESTADO'=>$lnEstado,
					'INIFEC'=>$this->cFecCre,
					'INIHOR'=>$this->cHorCre,
					'FINFEC'=>$this->cFecCre,
					'FINHOR'=>$this->cHorCre,
					'USCCAB'=>$this->cUsuCre,
					'PGCCAB'=>$lcProgramaGuarda,
					'FECCAB'=>$this->cFecCre,
					'HOCCAB'=>$this->cHorCre
					];
		$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatos);

		$lcTabla='BITDET';
		$lnConsecutivoDetalle = $this->oDb->secuencia('SEQ_BITDET');
		$laDatos = ['CONDET'=>$lnConsecutivoDetalle,
					'CONCAB'=>$lnConsecutivoCabecera,
					'NIGING'=>$lnIngreso,
					'ESTADO'=>0,
					'ENTDET'=>$lcNitEntidad,
					'PROVEE'=>'',
					'TRAMIT'=>$lcTipoAlta,
					'SECCIN'=>$lcSeccion,
					'HABITA'=>$lcHabitacion,
					'INIFEC'=>$this->cFecCre,
					'INIHOR'=>$this->cHorCre,
					'FINFEC'=>0,
					'FINHOR'=>0,
					'EGRFEC'=>$lnFechaEgreso,
					'EGRHOR'=>$lnHoraEgreso,
					'OBSERV'=>trim(substr($lcObservacionesAlta,0,510)),
					'USCDET'=>$this->cUsuCre,
					'PGCDET'=>$lcProgramaGuarda,
					'FECDET'=>$this->cFecCre,
					'HOCDET'=>$this->cHorCre
					];
		$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatos);

		if ($lcObservacionesAlta!=''){
			$lcTabla='BITOBS';
			$lnConsecutivoObservacion = $this->oDb->secuencia('SEQ_BITOBS');
			$laDatos = ['CONOBS'=>$lnConsecutivoObservacion,
						'CONCAB'=>$lnConsecutivoCabecera,
						'CONDET'=>$lnConsecutivoDetalle,
						'OBSERV'=>trim(substr($lcObservacionesAlta,0,510)),
						'USCOBS'=>$this->cUsuCre,
						'PGCOBS'=>$lcProgramaGuarda,
						'FECOBS'=>$this->cFecCre,
						'HOCOBS'=>$this->cHorCre
						];
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatos);
		}
		return $laRetorno;
	}

	public function crearRegistroCenso($taDatosCenso=[])
	{
		$this->IniciaDatosAuditoria();
		$laRetorno = [
			'Mensaje'=>'',
			'Valido'=>true,
		];
		$lcEstadoExamen='';
		$lcEstadoEnfermeria=trim($this->oDb->obtenerTabMae1('CL2TMA', 'CENPAC', "CL1TMA='ESTADO' AND CL3TMA='T' AND OP1TMA='E' AND ESTTMA=''", null, ''));
		$lnIngreso=isset($taDatosCenso['ingreso']) ? intval($taDatosCenso['ingreso']):0;
		$lcRegistroGuarda=isset($taDatosCenso['registroguarda']) ? $taDatosCenso['registroguarda'] : '';
		$lcProgramaGuarda=isset($taDatosCenso['programaguarda']) ? $taDatosCenso['programaguarda'] : $this->cPrgCre;
		$lcUbicacion=isset($taDatosCenso['ubicacion']) ? $taDatosCenso['ubicacion'] : 'U';
		$lnEstado=isset($taDatosCenso['estadocenso']) ? intval($taDatosCenso['estadocenso']):0;
		$lcEstadoExamen=isset($taDatosCenso['estadoenfermeria']) ? ($lcUbicacion=='U' && !empty($taDatosCenso['estadoenfermeria'])?$lcEstadoEnfermeria:'') : '';

		$lcTabla = 'CENURC';
		$laDatos = [
			'INGURC'=>$lnIngreso,
			'UBPURC'=>$lcUbicacion,
			'ESTURC'=>$lnEstado,
			'FHOURC'=>$this->cFecCre,
			'HHOURC'=>$this->cHorCre,
			'MEDURC'=>$lcRegistroGuarda,
			'EXAURC'=>$lcEstadoExamen,
			'USRURC'=>$this->cUsuCre,
			'PGMURC'=>$lcProgramaGuarda,
			'FECURC'=>$this->cFecCre,
			'HORURC'=>$this->cHorCre,
		];
		$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatos);

	}

	public function ubicacionCensoPacientes($tnIngreso=0)
	{
		$lcUbicacion='U';
		if ($tnIngreso>0){
			$laHabitacion = $this->oDb
				->select('A.INGHAB INGRESO, A.SECHAB SECCION')
				->from('FACHAB A')
				->leftJoin("TABMAE B", "A.SECHAB=B.CL1TMA AND B.TIPTMA='SECHAB'", null)
				->where('A.INGHAB', '=', $tnIngreso)
				->where('A.IDDHAB', '=', '0')
				->in('A.ESTHAB', ['1','9'])
				->in('B.CL4TMA', ['PISOS', 'UNID.', 'PED.'])
			->getAll('array');
			if ($this->oDb->numRows()>0){
				$lcUbicacion='H';
			}
		}
		unset($laHabitacion);
		return $lcUbicacion;
	}

	public function consultaRegistroCenso($taDatosCenso=[])
	{
		$lnIngreso=intval($taDatosCenso['ingreso']);
		$lnTiporegistro=intval($taDatosCenso['tiporegistro']);
		$lcReturn = '';

		$laDatosCenso = $this->oDb
			->select('A.CONURD CONSECUTIVO, A.CLNURD LINEA, TRIM(A.DESURD) DESCRIPCION, A.USRURD USUARIO, A.FECURD FECHA, A.HORURD HORA')
			->select('TRIM(B.NNOMED)||\' \'||TRIM(B.NOMMED) NOMBREMEDICO')
			->from('CENURD AS A')
			->leftJoin('RIARGMN AS B', "TRIM(A.USRURD)=TRIM(B.USUARI)", null)
			->where('A.INGURD', '=', $lnIngreso)
			->where('A.IN1URD', '=', $lnTiporegistro)
			->orderBy('A.CONURD DESC, A.CLNURD')
			->getAll('array');
		if($this->oDb->numRows()>0){
			$laConsecutivo = $laDatosCenso[0]['CONSECUTIVO'];
			$lnNumConsec=-1;
			foreach($laDatosCenso as $laClave=>$laEvolucion)
			{
				$lcDesc = $laEvolucion['CONSECUTIVO'] .'- ' .$laEvolucion['NOMBREMEDICO'].' / '
						.AplicacionFunciones::formatFechaHora('fechahora12', $laEvolucion['FECHA'].' '.$laEvolucion['HORA'])
						.PHP_EOL.trim($laEvolucion['DESCRIPCION']);
				if ($lnNumConsec==$laEvolucion['CONSECUTIVO']){
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

	public function consultaAltasTempranas()
	{
		$laParametros = [];
		if(isset($this->oDb)){
			$laParametros = $this->oDb
				->select('trim(CL3TMA) AS CODIGO, trim(DE1TMA) AS DESCRIPCION')
				->from('TABMAE')
				->where('TIPTMA=\'BITPAR\' AND CL1TMA=\'ALTTEMP\' AND CL2TMA=\'01010103\' AND ESTTMA=\' \'')
				->orderBy('DE1TMA')
				->getAll('array');
		}
		return $laParametros;
	}

	public function consultaAutorizacionUsuarioEspecifico($taDatosPermiso=[])
	{
		$tsPermiso=isset($taDatosPermiso['tsPermiso']) ? $taDatosPermiso['tsPermiso']:'';
		$laReturn=[];
		if(isset($this->oDb)){
			switch ($tsPermiso) {
				case 'usuarioJefeUrgencias':
					$laParametros = $this->oDb
					->select('CL2TMA')
					->from('TABMAE')
					->where('TIPTMA=\'CENPAC\' AND CL1TMA=\'USJEFURG\' AND CL2TMA=\''.$_SESSION[HCW_NAME]->oUsuario->getUsuario().'\' ')
					->get('array');
				$laReturn['usuarioJefeUrgencias'][] = ($this->oDb->numRows()>0  ? true : false);
				break;
			}
		}
		unset($laParametros);		
		return $laReturn;
	}	

	public function consultarAutorizacionUsuarios()
	{
		$aParametros = [];
		if(isset($this->oDb)){
			$laParametros = $this->oDb
				->select('trim(OP1TMA) OP1TMA, trim(DE2TMA) DESCRIPCION')
				->from('TABMAE')
				->where('TIPTMA=\'CENPAC\' AND CL1TMA=\'TUSUREG\' AND ESTTMA=\' \'')
				->orderBy('CL2TMA')
				->get('array');
			$lcDatosAutorizacion=explode('~', $laParametros['DESCRIPCION']);
			$aParametros=[
				'tipousuadm'=>explode(',', $lcDatosAutorizacion[0]),
				'tipousumed'=>explode(',', $lcDatosAutorizacion[1]),
				'tiporegadm'=>explode(',', $lcDatosAutorizacion[2]),
				'tiporegmed'=>explode(',', $lcDatosAutorizacion[3]),
				'tipoexportar'=>explode(',', $lcDatosAutorizacion[4]),
				'tipousuenfer'=>explode(',', $lcDatosAutorizacion[5]),
				'tiporegenfer'=>explode(',', $lcDatosAutorizacion[6]),
				'activacambio'=>$laParametros['OP1TMA'],
			];
			$laParametros = $this->oDb
				->select('DE1TMA')
				->from('TABMAE')
				->where('TIPTMA=\'CENPAC\' AND CL1TMA=\'TUSUADMR\' AND ESTTMA=\' \' AND CL2TMA= \''.$_SESSION[HCW_NAME]->oUsuario->getUsuario().'\'')
				->get('array');
				if($this->oDb->numRows()>0){
					$aParametros['tipousuadm'][] =	trim($_SESSION[HCW_NAME]->oUsuario->getTipoUsuario());
				}
		}
		unset($laParametros);
		return $aParametros;
	}

	public function consultarparametros()
	{
		$lcDatoUsuario='';
		$lcUsuario = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getUsuario():'');
		if(isset($this->oDb)){
			$laParametros = $this->oDb
				->select('trim(CL2TMA) CLASIFICACION2')
				->from('TABMAE')
				->where('TIPTMA', '=', 'CENPAC')
				->where('CL1TMA', '=', 'USUARIO')
				->where('CL2TMA', '=', $lcUsuario)
				->get('array');
			if($this->oDb->numRows()>0){
				$lcDatoUsuario=$laParametros['CLASIFICACION2'];
			}
		}
		return $lcDatoUsuario;
	}


	public function consultaDatosAltasTempranas($taDatosCenso=[])
	{
		$laDatosAltas = [];
		$lcChrRec=chr(24);
		$lcChrAlta=chr(25);

		$lnIngreso=intval($taDatosCenso['ingreso']);
		$lnTiporegistro=intval($taDatosCenso['tiporegistro']);
		if(isset($this->oDb)){
			$laParametros = $this->oDb
			->select('A.CONURD CONSECUTIVO, A.CLNURD LINEA, TRIM(A.DESURD) DESCRIPCION, TRIM(A.USRURD) USUARIOCREA, A.FECURD FECHACREA, A.HORURD HORACREA')
			->select('(SELECT UPPER(TRIM(NNOMED)||\' \'||TRIM(NOMMED)) FROM RIARGMN WHERE USUARI=A.USRURD) AS NOMBRE_USUARIO_CREA')
			->from('CENURD AS A')
			->where('A.INGURD', '=', $lnIngreso)
			->where('A.IN1URD', '=', $lnTiporegistro)
			->where('CONURD=(SELECT MAX(M.CONURD) FROM CENURD M WHERE M.INGURD='.$lnIngreso.' AND M.IN1URD='.$lnTiporegistro.')')
			->orderBy('A.CONURD DESC, A.CLNURD')
			->getAll('array');
			if($this->oDb->numRows()>0){
				$lcDescripcion='';
				$lcDatosAuditoria=$laParametros[0]['NOMBRE_USUARIO_CREA'].' - '.AplicacionFunciones::formatFechaHora('fechahora12', $laParametros[0]['FECHACREA'].' '.$laParametros[0]['HORACREA']);

				foreach($laParametros as $laRegistro){
					$lcDescripcion .= $laRegistro['DESCRIPCION'];
				}
				$laLineas = explode($lcChrAlta,$lcDescripcion);
				foreach($laLineas as $lcAltas){
					if ($lcAltas!=''){
						$lcDescripcionAlta='';
						$laAltaTemprana = explode($lcChrRec,$lcAltas);
						$lcCodigoAlta=$laAltaTemprana[0];
						$lcObservacionesAlta=$laAltaTemprana[1];

						if (!empty($lcCodigoAlta)){
							$lcDescripcionAlta = $this->oDb->obtenerTabmae1('DE1TMA', 'BITPAR', "CL1TMA='ALTTEMP' AND CL2TMA='01010103' AND CL3TMA='$lcCodigoAlta'", null, '');
						}
						$laDatosAltas[] = [
							'CODIGO' =>$lcCodigoAlta,
							'DESCRIPCION' =>trim($lcDescripcionAlta),
							'OBSERVACIONES' =>$lcObservacionesAlta,
							'DATOSAUDITORIA' =>$lcDatosAuditoria,
						];
					}
				}
			}
		}
		return $laDatosAltas;
	}

	public function estadosCenso($taDatosCenso=[])
	{
		$taTipoCenso=$taDatosCenso['taTipoCenso'];
		$laEstados = [];		
		$laTabla = $this->oDb
			->select('CL2TMA, CL3TMA, DE2TMA, OP4TMA, OP2TMA')
			->from('TABMAE')
			->where('TIPTMA=\'CENPAC\' AND CL1TMA=\'ESTADO\' AND ESTTMA=\'\' AND LOCATE(\''.$taTipoCenso.'\', OP2TMA) > 0')
			->orderBy('OP7TMA')
			->getAll('array');
		if (is_array($laTabla)){
			foreach($laTabla as $laFila){
				$laEstados[intval($laFila['CL2TMA'])] = [
					'TIPO'=>trim($laFila['CL3TMA']),
					'DESCR'=>trim($laFila['DE2TMA']),
					'COLOR'=>AplicacionFunciones::colorFoxToRGB($laFila['OP4TMA']),
				];
			}
		}
		return $laEstados;
	}

	public function triagePendientesIngresoAdmin()
	{
		$ltAhora = new \DateTime($this->oDb->fechaHoraSistema());
		$lcFecha = $ltAhora->format('Ymd');
		$lcFechaHora = $ltAhora->format('Y-m-d H:i:s');

		$laTabla = $this->oDb
			->select('TRIM(PRCTRI) AS TRIAGE, COUNT(*) AS REGISTROS')
			->from('TRIAGU')
			->where("ESTTRI IN ('2','3','8','30') AND FETTRI >={$lcFecha}")
			->groupBy('PRCTRI')->orderBy('PRCTRI')
			->getAll('array');

		return [
			'triage'=>($this->oDb->numRows()>0 ? $laTabla : []),
			'fechahora'=>$lcFechaHora
		];
	}
}
