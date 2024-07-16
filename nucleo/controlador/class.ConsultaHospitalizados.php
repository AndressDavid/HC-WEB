<?php
namespace NUCLEO;

require_once __DIR__ .'/class.Db.php';
use NUCLEO\Db;


class ConsultaHospitalizados
{
	protected $oDb;
	protected $bEdadConFechaIngreso = false;

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}

	/*
	 *	Consulta los pacientes en urgencias
	 */
	public function consultaPacientes($tnIngreso=0, $tcSeccion='', $tcEspecialidad='', $tcMedicoTratante='', $tcPlan='')
	{
		$laPacientes = $laWhere = [];
		$lcWhere = trim($this->oDb->obtenerTabMae1('DE2TMA', 'EVOLUC', 'CL1TMA=\'FILCENS\' AND ESTTMA=\'\'', null, 'A.INGHAB>0 AND A.IDDHAB=\'0\' AND A.ESTHAB<>\'6\' AND A.SECHAB<>\'TU\''));
		$lcWhere = str_replace('A.', 'H.', $lcWhere);

		// Condiciones
		if ($tnIngreso) $laWhere['H.INGHAB'] = $tnIngreso;
		if ($tcSeccion) $laWhere['H.SECHAB'] = $tcSeccion;
		if ($tcEspecialidad) $laWhere['D.DPTINT'] = $tcEspecialidad;
		if ($tcMedicoTratante) $laWhere['F.REGMED'] = $tcMedicoTratante;
		if ($tcPlan) $laWhere['I.PLAING'] = $tcPlan;
		if (!empty($laWhere)) $this->oDb->where($laWhere);

		$laTabla = $this->oDb
			->select('H.INGHAB INGRESO, H.SECHAB SECCION, H.NUMHAB HABITACION, H.ESTHAB ESTADO, I.TIDING TIPO_DOC, I.NIDING NUM_DOC')
			->select('I.FEIING FECHA_ING, I.VIAING CODVIA, IFNULL(TRIM(V.DESVIA), \'\') AS DESVIA, P.FNAPAC AS FECHA_NAC')
			->select('P.SEXPAC AS CODGENERO, IFNULL((SELECT TRIM(DE2TMA) FROM TABMAE WHERE TIPTMA=\'SEXPAC\' AND CL1TMA=P.SEXPAC), \'\') AS GENERO')
			->select('TRIM(P.NM1PAC) || \' \' || TRIM(P.NM2PAC) || \' \' || TRIM(P.AP1PAC) || \' \' || TRIM(P.AP2PAC) AS PACIENTE')
			->select('IFNULL(TRIM(D.DPTINT), \'\') AS COD_ESP, IFNULL(TRIM(E.DESESP), \'\') AS ESPECIALIDAD')
			->select('IFNULL(D.MEDINT, 0) AS REG_MEDICO, IFNULL(UPPER(TRIM(M.NOMMED) || \' \' || TRIM(M.NNOMED)), \'\') AS MEDICO')
			->select('IFNULL(I.ENTING, 0) AS ENTIDAD, IFNULL(TRIM(I.PLAING), \'\') AS COD_PLAN, IFNULL(TRIM(DSCCON), \'\') AS PLAN')
			->select('H1.TP1PAL, H1.CP1PAL')
			->select('IFNULL((SELECT TRIM(OP2TMA) FROM TABMAE WHERE TIPTMA=\'SECHAB\' AND CL1TMA=H.SECHAB), \'\') AS TIPO_HABITACION')
			->from('FACHAB H')
			->innerJoin('RIAING I', 'H.INGHAB=I.NIGING', null)
			->innerJoin('RIAPAC P', 'I.TIDING=P.TIDPAC AND I.NIDING=P.NIDPAC', null)
			->leftJoin('RIAINGT D', 'H.INGHAB=D.NIGINT', null)
			->leftJoin('RIAESPE E', 'D.DPTINT=E.CODESP', null)
			->leftJoin('RIAVIA  V', 'I.VIAING=V.CODVIA', null)
			->leftJoin('RIARGMN M', 'DIGITS(D.MEDINT)=M.REGMED', null)
			->leftJoin('FACPLNC F', 'I.PLAING=F.PLNCON', null)
			->leftJoin('PACALT H1', 'I.TIDING=H1.TIDPAL AND I.NIDING=H1.NIDPAL')
			->where($lcWhere)
			->orderBy('H.SECHAB, H.NUMHAB')
			->getAll('array');
		if (is_array($laTabla)) {
			if (count($laTabla)>0) {
				$ldFechaActual = date_create(date('Y-m-d'));
				foreach($laTabla as $laFila){
					if ($this->bEdadConFechaIngreso) {
						$laFila['EDAD'] = (date_diff(date_create($laFila['FECHA_NAC']), date_create($laFila['FECHA_ING'])))->format('%y-%m-%d');
					} else {
						$laFila['EDAD'] = (date_diff(date_create($laFila['FECHA_NAC']), $ldFechaActual))->format('%y-%m-%d');
					}
					$laPacientes[] = $laFila;
				}
			} else {
				if ($tnIngreso>0) {
					$laPacientes = $this->consultaPacienteSinHab($tnIngreso);
				}
			}
			unset($laTabla);
		}

		return $laPacientes;
	}

	/*
	 *	Paciente sin habitación
	 */
	public function consultaPacienteSinHab($tnIngreso)
	{
		$laPacientes = [];

		$laTabla = $this->oDb
			->select('I.NIGING INGRESO, I.FEIING FECHA_ING, I.TIDING TIPO_DOC, I.NIDING NUM_DOC, I.VIAING CODVIA')
			->select('IFNULL(TRIM(V.DESVIA),\'\') DESVIA, P.FNAPAC FECHA_NAC, P.SEXPAC CODGENERO, IFNULL(TRIM(G.DE2TMA), \'\') GENERO')
			->select('TRIM(P.NM1PAC) || \' \' || TRIM(P.NM2PAC) || \' \' || TRIM(P.AP1PAC) || \' \' || TRIM(P.AP2PAC) PACIENTE')
			->select('IFNULL(D.DPTINT, \'\') COD_ESP, IFNULL(TRIM(SUBSTR(TRIM(E.DESESP), 1, 60)), \'\') ESPECIALIDAD')
			->select('IFNULL(D.MEDINT, 0) REG_MEDICO, IFNULL(UPPER(TRIM(M.NOMMED) || \' \' || TRIM(M.NNOMED)), \'\') MEDICO')
			->select('IFNULL(I.ENTING, 0) ENTIDAD, IFNULL(TRIM(I.PLAING), \'\') COD_PLAN, IFNULL(TRIM(F.DSCCON), \'\') PLAN')
			->select('H1.TP1PAL, H1.CP1PAL')
			->from('RIAING I')
			->innerJoin('RIAPAC P', 'I.TIDING=P.TIDPAC AND I.NIDING=P.NIDPAC', null)
			->leftJoin('RIAINGT D', 'I.NIGING=D.NIGINT', null)
			->leftJoin('RIAESPE E', 'D.DPTINT=E.CODESP', null)
			->leftJoin('RIARGMN M', 'DIGITS(D.MEDINT)=M.REGMED', null)
			->leftJoin('RIAVIA  V', 'I.VIAING=V.CODVIA', null)
			->leftJoin('TABMAE  G', 'P.SEXPAC=G.CL1TMA AND G.TIPTMA=\'SEXPAC\'', null)
			->leftJoin('FACPLNC F', 'I.PLAING=F.PLNCON', null)
			->leftJoin('PACALT  H1', 'I.TIDING=H1.TIDPAL AND I.NIDING=H1.NIDPAL')
			->where(['I.NIGING'=>$tnIngreso])
			->getAll('array');
		if (is_array($laTabla)){
			if (count($laTabla)>0){
				$laFila = $laTabla[0];
				$ldFechaActual = date_create(date('Y-m-d'));
				if ($this->bEdadConFechaIngreso) {
					$laFila['EDAD'] = (date_diff(date_create($laFila['FECHA_NAC']), date_create($laFila['FECHA_ING'])))->format('%y-%m-%d');
				} else {
					$laFila['EDAD'] = (date_diff(date_create($laFila['FECHA_NAC']), $ldFechaActual))->format('%y-%m-%d');
				}
				$laPacientes[] = $laFila+['SECCION'=>'','HABITACION'=>''];

				unset($laTabla);
			}
		}

		return $laPacientes;
	}

	/*
	 *	Valida si al paciente se le puede hacer nueva consulta
	 */
	public function validarNuevaConsulta($tnIngreso=0, $tcCodEsp='', $tnFecRea=0)
	{
		$laReturn=['valido'=>false, 'mensaje'=>'Error inesperado'];
		if ($tnIngreso==0) {
			return ['valido'=>false, 'mensaje'=>'Número de Ingreso NO Pude Ser Cero'];
		}

		// La especialidad del médico debe ser la misma de la consulta
		if ($tcCodEsp!==$_SESSION[HCW_NAME]->oUsuario->getEspecialidad()) {
			return ['valido'=>false, 'mensaje'=>'La Especialidad del médico que va responder es diferente a la Especiliadad de la Cita, revise por favor'];
		}

		// Valida Nueva consulta por Ingreso
		$laTabla = $this->oDb
			->count('*', 'CUENTA')
			->from('RIACHC')
			->where([
				'NINCHC'=>$tnIngreso,
				'RMECHC'=>$_SESSION[HCW_NAME]->oUsuario->getRegistro(),
				'FCOCHC'=>$tnFecRea,
			])
			->getAll('array');

		if (is_array($laTabla)) {
			if ($laTabla[0]['CUENTA']==0) {
				return ['valido'=>true, 'mensaje'=>''];
			} else {
				$lnConCon = $this->verificaEspecialidadHC($tnIngreso, $tnFecRea);
				if ($lnConCon==0) {
					return ['valido'=>true, 'mensaje'=>''];
				} else {
					$lnConCita = $this->consultarConsecutivoCita($tnIngreso, $lnConCon);
					return ['valido'=>false, 'mensaje'=>'Paciente YA tiene consulta', 'concon'=>$lnConCon, 'concita'=>$lnConCita];
				}
			}

		} else {
			return ['valido'=>false, 'mensaje'=>'Error al consultar atenciones del paciente. Intente de nuevo en unos minutos.'];
		}

		return $laReturn;
	}

	/*
	 *	Verifica especialidad para la historia, retorna consecutivo consulta si existe
	 */
	public function verificaEspecialidadHC($tnIngreso=0, $tnFecRea=0)
	{
		$lnConsec = 0;
		$laTabla = $this->oDb
			->select('DESCRI, CONCON')
			->from('RIAHIS')
			->where([
				'NROING'=>$tnIngreso,
				'FECHIS'=>$tnFecRea,
				'INDICE'=>85,
			])
			->getAll('array');
		if (is_array($laTabla)){
			$lcEspMed = $_SESSION[HCW_NAME]->oUsuario->getEspecialidad();
			$lnConsec = 0;
			foreach($laTabla as $laFila){
				$lnConsec = ($lcEspMed==substr(trim($laFila['DESCRI']), 37))? intval($laFila['CONCON']): $lnConsec;
			}
		}
		return $lnConsec;
	}

	/*
	 *	Obtener consecutivo de cita
	 */
	public function consultarConsecutivoCita($tnIngreso=0, $tnConCon=0)
	{
		$laTabla = $this->oDb
			->select('CCIORD')
			->from('RIAORD')
			->where([
				'NINORD'=>$tnIngreso,
				'CCOORD'=>$tnConCon,
			])
			->get('array');

		return $laTabla['CCIORD']??0;
	}

	/*
	 *	Paciente es teleconsulta?, retorna S o N. Si hay error retorna vacío
	 */
	public function esTeleconsulta($tnIngreso=0, $tcTipoId='', $tnNumId=0, $tnCita=0)
	{
		$laTabla = $this->oDb
			->select('CONCIT')
			->from('RIACIT')
			->where([
				'NINCIT'=>$tnIngreso,
				'TIDCIT'=>$tcTipoId,
				'NIDCIT'=>$tnNumId,
				'CCICIT'=>$tnCita,
			])
			->get('array');

		return $laTabla['CONCIT']??'';
	}

}
