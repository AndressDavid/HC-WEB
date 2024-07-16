<?php
namespace NUCLEO;

require_once __DIR__ .'/class.Db.php';
require_once __DIR__ .'/class.AplicacionFunciones.php';

use NUCLEO\Db;
use NUCLEO\AplicacionFunciones;


class ConsultaExterna
{
	protected $oDb;

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
    }


	/*
	 *	Consulta citas por atender
	 */
	public function consultaPacientes($tnIngreso=0, $tcTipoId='', $tnNumId=0, $tnFechaIni=0, $tnFechaFin=0, $tcCodVia='', $tcCup='', $tcCodEsp='', $tcRegMed='', $tcEstado='', $tcSeccion='', $tcProg='RIA100ORD',$tsOrigSol='')
	{
		$laPacientes = $laWhere = [];
		// Condiciones
		if ($tnIngreso) $laWhere['O.NINORD'] = $tnIngreso;
		if ($tcTipoId && $tnNumId){
			$laWhere['O.TIDORD'] = $tcTipoId;
			$laWhere['O.NIDORD'] = $tnNumId;
		}
		if ($tnFechaIni && $tnFechaFin) {
			$this->oDb->between('O.FRLORD', $tnFechaIni, $tnFechaFin);
		} elseif ($tnFechaIni && empty($tnFechaFin)) {
			$laWhere['O.FRLORD'] = $tnFechaIni;
		}
		if ($tcCodVia) $laWhere['V.CODVIA'] = $tcCodVia;
		if ($tcCup) $laWhere['C.CODCUP'] = $tcCup;
		if ($tcCodEsp) $laWhere['O.CODORD'] = str_pad($tcCodEsp, 3, '0', STR_PAD_LEFT);
		if ($tcEstado) $laWhere['O.ESTORD'] = $tcEstado;
		if ($tcRegMed) $laWhere['O.RMRORD'] = $tcRegMed;
		if ($tcSeccion) $laWhere['H.SECHAB'] = $tcSeccion;
		if ($tcProg) $laWhere['C.PGRCUP'] = $tcProg;
		if ($tsOrigSol=='int'){
			$this->oDb
				->select('IFNULL(TRIM(INT.DESINT),\'\') PRIORIDAD ')
				->leftJoin('INTCON INT','O.NINORD=INT.INGINT AND O.CCIORD=INT.CORINT AND INT.SORINT=\'S\' AND INT.CNLINT=600 ' )
				->in('O.VIAORD',['01','05'])
				->like('O.COAORD','890%')
				->where("PGMORD <> 'GRAC01'");
		}
		$this->oDb
			->select('O.NINORD, O.TIDORD, O.NIDORD, O.CCOORD, O.CCIORD, O.EVOORD, O.CODORD, O.TPRORD')
			->select('O.ESTORD, O.ENTORD, O.FCOORD, O.FRLORD, O.HOCORD, O.RMRORD, O.CD2ORD, O.RMEORD')
			->select('O.PRTORD, O.FERORD, O.HRLORD, O.ESCORD, O.USRORD, O.PGMORD, O.FECORD, O.HORORD')
			->select('I.TIUING, I.FEIING, I.HORING, I.FEEING, I.HREING, I.PLAING, I.NCAING')
			->select('C.CODCUP, C.DESCUP, C.PGRCUP, V.CODVIA, V.DESVIA')
			->select('P.NM1PAC, P.NM2PAC, P.AP1PAC, P.AP2PAC, P.FNAPAC, P.SEXPAC GENERO')
			->select('T.DINGNT, E.DESESP, H.SECHAB, H.NUMHAB, H.ESTHAB')
			->select("UPPER(M.NOMMED) NOMMED, UPPER(M.NNOMED) NNOMED")
			->select('O.COAORD,COL.OP4TMA AS COLORFOX')
			->orderBy('O.FRLORD, O.HOCORD');
			if($tsOrigSol=='proest'){
				if ($tcProg) $laWhere['C.CODCUP'] = $tcProg;
				unset($laWhere['C.PGRCUP']);
				$this->oDb
				->from('RIAORD O')
				->leftJoin('RIAING  I', 'O.NINORD=I.NIGING')
				->leftJoin('RIAPAC  P', 'O.TIDORD=P.TIDPAC AND O.NIDORD=P.NIDPAC')
				->leftJoin('RIAINGT T', 'O.NINORD=T.NIGINT')
				->leftJoin('RIARES EE', 'O.NINORD=EE.NIGRES')
				->leftJoin('RIACUP  C', 'O.COAORD=C.CODCUP')
				->leftJoin('INTCON GG', 'O.NINORD=GG.INGINT AND O.EVOORD=GG.CONINT AND O.CCIORD=GG.CORINT AND O.COAORD=GG.CUPINT AND GG.CNLINT=600 ')
				->leftJoin('RIAVIA  V', 'I.VIAING=V.CODVIA') // via
				->leftJoin('FACHAB  H', 'O.TIDORD=H.TIDHAB AND O.NIDORD=H.NIDHAB AND H.ESTHAB<>\'8\'') // cama
				->leftJoin('RIAESPE E', 'O.CODORD=E.CODESP') // espe
				->leftJoin('TABMAE  COL', 'O.ESTORD=COL.CL1TMA AND TIPTMA=\'ESTPRORD\'')
				->leftJoin('RIARGMN M', 'O.RMEORD=M.REGMED') // USER
				->where($laWhere)
				->orderBy('O.FRLORD, O.HOCORD');
			}else{
				$this->oDb
				->select('G.TP1PAL, G.CP1PAL, IFNULL(A.CONCIT,\'\') CONCIT')
				->select("L.DSCCON,D.DESRIP, R.DESPRO")
				->from('RIAORD O')
				->leftJoin('RIAING  I', 'O.NINORD=I.NIGING')
				->leftJoin('RIAVIA  V', 'I.VIAING=V.CODVIA')
				->leftJoin('RIACUP  C', 'O.COAORD=C.CODCUP')
				->leftJoin('FACHAB  H', 'O.TIDORD=H.TIDHAB AND O.NIDORD=H.NIDHAB AND H.ESTHAB<>\'8\'') 
				->leftJoin('RIAESPE E', 'O.CODORD=E.CODESP')
				->leftJoin('RIAPAC  P', 'O.TIDORD=P.TIDPAC AND O.NIDORD=P.NIDPAC')
				->leftJoin('RIARGMN M', 'O.RMRORD=M.REGMED')
				->leftJoin('FACPLNC L', 'O.PLAORD=L.PLNCON')
				->leftJoin('RIAINGT T', 'O.NINORD=T.NIGINT')
				->leftJoin('RIACIE  D', 'T.DINGNT=D.ENFRIP')
				->leftJoin('ORDPRO  R', 'O.NINORD=R.INGPRO AND O.CCIORD=R.CORPRO AND O.EVOORD=R.CONPRO AND O.COAORD=R.CUPPRO')
				->leftJoin('RIACIT  A', 'O.NINORD=A.NINCIT AND O.TIDORD=A.TIDCIT AND O.NIDORD=A.NIDCIT AND O.CCIORD=A.CCICIT', null)
				->leftJoin('PACALT  G', 'O.TIDORD=G.TIDPAL AND O.NIDORD=G.NIDPAL')
				->leftJoin('TABMAE  COL', 'O.ESTORD=COL.CL1TMA AND TIPTMA=\'ESTPRORD\'')
				->where($laWhere);
			}

		//	$this->fnEscribirLog('Sentencia: '. $this->oDb->getStatement(). PHP_EOL . 'BindValues: '.var_export($this->oDb->getBindValue(),true));
		$laTabla = $this->oDb->getAll('array');
		if (is_array($laTabla)){
			foreach($laTabla as $laFila){
				if ($tsOrigSol=='int'){
					if (($laFila['PRIORIDAD']==1) && ($laFila['ESTORD']!=3) ){
						$laFila['ESTORD']=81;
						$laFila['COLORFOX']=4227072;
					}
				} // adecua color de 'Interconsulta Urgente'
				$laPacientes[] = array_map('trim',$laFila);
			}
			unset($laTabla);
		}
		return $laPacientes;
	}

	/*
	 *	Consultas ralizadas por pacientes
	 */
	public function consultasPorPaciente($tcTipoId='', $tnNumId=0)
	{
		$laConsultas = [];

		$laTabla = $this->oDb->distinct()
			->select('H.TIDCHC, H.NIDCHC, H.FCOCHC, H.NINCHC, H.CCOCHC, H.CCUCHC, H.RMECHC')
			->select('I.ENTING, I.FEIING, I.HORING, V.CODVIA, V.DESVIA, C.DESCUP, M.NOMMED')
			->select('E.DESESP, D.CCIORD, IFNULL(O.CORORA,0) AS CORORA, D.SCAORD, D.NCAORD')
			->from('RIACHC H')
			->leftJoin('RIAING  I', 'H.NINCHC=I.NIGING')
			->leftJoin('RIAVIA  V', 'I.VIAING=V.CODVIA')
			->leftJoin('RIACUP  C', 'H.CCUCHC=C.CODCUP')
			->leftJoin('RIARGMD M', 'H.RMECHC=M.REGMED')
			->leftJoin('RIAESPE E', 'M.CODRGM=E.CODESP')
			->leftJoin('RIAORD  D', 'H.TIDCHC=D.TIDORD AND H.NIDCHC=D.NIDORD AND H.NINCHC=D.NINORD AND H.CCOCHC=D.CCOORD')
			->leftJoin('ORDAMB  O', 'H.NINCHC=O.INGORA AND H.CCOCHC=O.CCOORA AND D.CCIORD=O.CCIORA')
			->where(['H.TIDCHC'=>$tcTipoId, 'H.NIDCHC'=>$tnNumId, ])
			->orderBy('H.CCOCHC DESC')
			->getAll('array');

		if (is_array($laTabla)){
			foreach($laTabla as $laFila){
				$laConsultas[] = array_map('trim',$laFila);
			}
			unset($laTabla);
		}

		return $laConsultas;
	}


	/*
	 *	Valida si al paciente se le puede hacer nueva consulta
	 */
	public function validarNuevaConsulta($tnIngreso=0, $tcCodEsp='', $tnFecRea=0)
	{
		$laReturn=['valido'=>false, 'mensaje'=>'Error inesperado'];
		if ($tnIngreso==0) {
			return ['valido'=>false, 'mensaje'=>'Número de Ingreso NO puede ser cero'];
		}

		$lcEspUsuario = $_SESSION[HCW_NAME]->oUsuario->getEspecialidad();
		// La especialidad del médico debe ser la misma de la consulta
		if ($tcCodEsp!==$lcEspUsuario) {
			$llRealiza = false;
			$oTabmae = $this->oDb->ObtenerTabMae('DE1TMA', 'PARHCWEB', ['CL1TMA'=>'ESPECIAL', 'CL2TMA'=>$lcEspUsuario, 'ESTTMA'=>'']);
			$laDatosEsp =  explode(',',trim(AplicacionFunciones::getValue($oTabmae, 'DE1TMA', '')));
			if (is_array($laDatosEsp)){
				foreach($laDatosEsp as $laDato){
					if($tcCodEsp==$laDato){
						$llRealiza = true;
					}
				}
			}

			if($llRealiza==false){
				return ['valido'=>false, 'mensaje'=>'La Especialidad del médico que va responder es diferente a la Especiliadad de la Cita, revise por favor'];
			}
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

	/*
	 *	Consulta los estados de procedimientos ordenados
	 */
	public function estadosProcedimientos()
	{
		$laEstados = [];
		$laTabla = $this->oDb
			->select('CL1TMA, DE1TMA, OP4TMA')
			->from('TABMAE')
			->where('TIPTMA=\'ESTPRORD\' AND ESTTMA=\'\' AND OP7TMA BETWEEN 1 AND 20')
			->orderBy('OP7TMA')
			->getAll('array');
		if (is_array($laTabla)){
			foreach($laTabla as $laFila){
				$laEstados[intval($laFila['CL1TMA'])] = [
					'DESCR'=>trim($laFila['DE1TMA']),
					'COLOR'=>AplicacionFunciones::colorFoxToRGB($laFila['OP4TMA']),
				];
			}
		}
		return $laEstados;
	}

}