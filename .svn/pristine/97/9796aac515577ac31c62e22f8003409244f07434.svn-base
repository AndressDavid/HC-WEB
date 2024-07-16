<?php

namespace NUCLEO;

require_once('class.Db.php');
require_once __DIR__ . '/class.Consecutivos.php';
require_once __DIR__ . '/class.Ingreso.php';
require_once __DIR__ . '/class.FormulacionParametros.php';
require_once __DIR__ . '/class.NoPosFunciones.php';

use NUCLEO\Db;
use NUCLEO\Ingreso;
use NUCLEO\FormulacionParametros;
use NUCLEO\NoPosFunciones;

class DatosAmbulatorios
{
	protected $aPrioridadInterconsulta = [];
	protected $aTablaInterconsulta = [];
	protected $aTablaProcedimientos = [];
	protected $aTablaDietas = [];
	protected $aTablaMotivoIncapacidad = [];
	protected $aTablaInsumos = [];
	protected $aUltimaFormula = [];
	protected $aMedicamentosAnteriores = [];
	protected $aListaOrdenesAmbulatorias = [];
	protected $aUltimoIngresoAmb = [];
	protected $nConsecConsulta = 0;
	protected $nConsecOrden = 0;
	protected $nConsecCita = 0;
	protected $nConsecEvol = 0;
	protected $nIngreso = 0;
	protected $nEntidad = 0;
	protected $cTipoIden = '';
	protected $nNroIden = 0;
	protected $ccodigoViaIngreso = '';
	protected $cPlanIngreso = '';
	protected $cRegMed = '';
	protected $cEspecialidad = '';
	protected $cFecCre = '';
	protected $cHorCre = '';
	protected $cUsuCre = '';
	protected $cPrgCre = '';
	protected $aIngreso = [];
	protected $oAmbIng = null;
	protected $oDb = null;
	protected $cDxPrincipal = '';
	protected $laDatosGuardarQr = [];


	protected $nNroHistoria = 0;
	protected $aDatosMedicamento = [
		'Unidaddosis' => '',
		'Unidad' => '',
		'Presentacion' => '',
		'Concentracion' => '',
	];

	protected $aError = [
		'Mensaje' => "",
		'Objeto' => "",
		'Valido' => true,
	];

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
		$this->oAmbIng = new Ingreso();
	}

	public function consultarTextoPaquetes()
	{
		$lcTextoPaquetes = '';
		if (isset($this->oDb)) {
			$laDatos = explode('~', $this->oDb->obtenerTabMae1("TRIM(DE2TMA) || '~' || TRIM(OP5TMA)", 'FORMEDIC', "CL1TMA='TEXTPAQ' AND ESTTMA=''", null, ''));
			$lcPaquetes = $laDatos[0] ?? '';
			$lcInterconsultas = $laDatos[1] ?? '';
			$lcTextoPaquetes = $lcPaquetes . '~' . $lcInterconsultas;
		}
		return $lcTextoPaquetes;
	}

	public function consultarMipresIntranet()
	{
		$aParametros = [];
		$lcRutaMipres = $lcDirigir = $lcRutaIntranet = '';

		if (isset($this->oDb)) {
			$laParametros = $this->oDb
				->select('trim(OP1TMA) DIRIGIR, trim(DE2TMA) RUTAINTRANET, trim(OP5TMA) RUTAMIPRES')
				->from('TABMAE')
				->where('TIPTMA=\'WSNOPOS\' AND CL1TMA=\'MIPINTR\' AND ESTTMA=\' \'')
				->get('array');

			if (is_array($laParametros) && count($laParametros) > 0) {
				$lcRutaMipres = $laParametros['RUTAMIPRES'];
				$lcDirigir = $laParametros['DIRIGIR'];
				$lcRutaIntranet = $laParametros['RUTAINTRANET'];
			}
		}
		$aParametros = [
			'rutamipres' => $lcRutaMipres,
			'rutaintranet' => $lcRutaIntranet,
			'irmipresintranet' => $lcDirigir,
		];
		return $aParametros;
	}

	public function PrioridadInterconsulta()
	{
		if (isset($this->oDb)) {
			$laParams = $this->oDb
				->select('trim(TABCOD) AS CODIGO, trim(TABDSC) AS DESCRIPCION')
				->from('PRMTAB')
				->where('TABTIP=\'PAT\' AND TABCOD<>\' \'')
				->orderBy('TABDSC')
				->getAll('array');
			if (is_array($laParams) == true) {
				$this->aPrioridadInterconsulta = $laParams;
			}
		}
		return $this->aPrioridadInterconsulta;
	}

	public function TablaInterconsultas($tcTipoFiltro = '')
	{
		if ($tcTipoFiltro == 'T') {
			$laCondiciones = 'CODESP<>\' \'';
		} else {
			$laCondiciones = ['PGCESP' => $tcTipoFiltro,];
		}

		$loTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'FORMEDIC', ['CL1TMA' => 'CUPSINTC', 'ESTTMA' => ' ']);
		$lcCodInterconsulta = trim(AplicacionFunciones::getValue($loTabmae, 'DE2TMA', ''));
		$lcCodInterconsulta = empty(trim($lcCodInterconsulta)) ? '890402' : $lcCodInterconsulta;

		if (isset($this->oDb)) {
			$laParams = $this->oDb
				->select('trim(CODESP) AS CODIGO, trim(DESESP) AS DESCRIPCION')
				->from('RIAESPE')
				->where($laCondiciones)
				->orderBy('DESESP')
				->getAll('array');
		}

		if (is_array($laParams) == true) {
			foreach ($laParams as $laInterconsultas) {
				$lcCodidoEspecialidad = $laInterconsultas['CODIGO'];
				$lcCodigoCups = $lcCupsCambia = '';
				$lcCupsCambia = trim($this->oDb->obtenerTabmae1('DE2TMA', 'FORMEDIC', "CL1TMA='INTERCAL' AND CL2TMA='$lcCodidoEspecialidad'", null, ''));

				$laTablaCups = $this->oDb
					->select('CODCUP')
					->from('RIACUP')
					->where('IDDCUP', '=', '0')
					->where(['ESPCUP' => $lcCodidoEspecialidad])
					->where("(CODCUP LIKE '8904%')")
					->get('array');
				if ($this->oDb->numRows() > 0) {
					$lcCodigoCups = trim($laTablaCups['CODCUP'] ?? '');
				}
				$lcCodigoCups = empty($lcCodigoCups) ? $lcCodInterconsulta : $lcCodigoCups;
				$lcCodigoCups = !empty($lcCupsCambia) ? $lcCupsCambia : $lcCodigoCups;

				$this->aTablaInterconsulta[] = [
					'CODIGO' => $lcCodidoEspecialidad,
					'DESCRIPCION' => $laInterconsultas['DESCRIPCION'],
					'CUPS' => $lcCodigoCups,
				];
			}
		}
		return $this->aTablaInterconsulta;
	}

	public function TablaProcedimientos($tcFiltro = '', $tcGenero = '')
	{
		$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'DATCUP', ['CL1TMA' => 'GENCUP', 'CL2TMA' => $tcGenero, 'ESTTMA' => ' ']);
		$lcFiltroSexo = trim(AplicacionFunciones::getValue($oTabmae, 'DE2TMA', ''));

		if ($tcFiltro == '') {
			$laCondiciones = 'A.IDDCUP=\'0\' AND A.RIPCUP<>\'P\' AND A.PGRCUP<>\'PAQLAB\'';
		} else {
			$laCondiciones = 'A.IDDCUP=\'0\' AND A.RIPCUP<>\'P\'';
		}
		$laCondiciones = (!empty($tcGenero) ? $laCondiciones . " AND (TRIM(IFNULL(B.GENCUA, '')) IN($lcFiltroSexo))" : $laCondiciones);

		if ($tcFiltro == '') {
			$laCondiciones = $laCondiciones . " AND A.CODCUP NOT LIKE '8904%'";
		}

		if (isset($this->oDb)) {
			$laParams = $this->oDb
				->select('trim(A.CODCUP) CODIGO, trim(A.DESCUP) DESCRIPCION, trim(A.RF1CUP) CLASIFICACION1, trim(A.RF5CUP) POSNOPOS')
				->from('RIACUP AS A')
				->leftJoin('RIACUPA B', 'trim(A.CODCUP)=trim(B.CODCUA)', null)
				->where($laCondiciones)
				->orderBy('A.DESCUP')
				->getAll('array');
			if (is_array($laParams) == true) {
				$this->aTablaProcedimientos = $laParams;
			}
		}
		return $this->aTablaProcedimientos;
	}

	public function TablaInsumos()
	{
		if (isset($this->oDb)) {
			$laParams = $this->oDb
				->select('trim(REFDES) AS CODIGO, trim(DESDES) AS DESCRIPCION')
				->from('INVDESL3')
				->where('REFDES<>\' \'')
				->where('STSDES<>\'1\'')
				->where('TINDES<>\'500\'')
				->orderBy('DESDES')
				->getAll('array');
			if (is_array($laParams) == true) {
				$this->aTablaInsumos = $laParams;
			}
		}
		return $this->aTablaInsumos;
	}

	public function TablaDietas($tcTipo = 'D')
	{
		$tcTipo = !is_string($tcTipo) ? 'N' : trim(strtoupper($tcTipo));
		if (isset($this->oDb)) {
			$laParams = $this->oDb
				->select('TRIM(SUBSTR(TRIM(DE1TMA), 1, 15)) AS CODIGO, TRIM(SUBSTR(TRIM(DE2TMA), 1, 40)) AS DESCRIPCION')
				->from('TABMAE')
				->where("TIPTMA='DIESHA' AND ESTTMA='' AND OP2TMA LIKE '%{$tcTipo}%'")
				->orderBy('DE2TMA')
				->getAll('array');
			if (is_array($laParams) == true) {
				$this->aTablaDietas = $laParams;
			}
		}
		return $this->aTablaDietas;
	}

	public function TablaMotivoIncapacidad()
	{
		if (isset($this->oDb)) {
			$laParams = $this->oDb
				->select('trim(TABCOD) AS CODIGO, trim(TABDSC) AS DESCRIPCION')
				->from('PRMTAB')
				->where('TABTIP=\'IN2\' AND TABCOD<>\' \'')
				->orderBy('TABDSC')
				->getAll('array');
			if (is_array($laParams) == true) {
				$this->aTablaMotivoIncapacidad = $laParams;
			}
		}
		return $this->aTablaMotivoIncapacidad;
	}

	/*
	 *	Retorna parámetros para incapacidades
	 */
	public function obtenerParametrosIncapacidad($taParam = [], $tcCodigo = '')
	{
		if (count($taParam) == 0) {
			$laPrm = [
				'INCORIG' => 'ORIGEN',
				'INCCAUS' => 'CAUSA',
				'INCRTRO' => 'RETROACTIVA',
			];
		} else {
			$laPrm = $taParam;
		}
		if (strlen($tcCodigo) > 0) {
			$this->oDb->where("CL2TMA='$tcCodigo'");
		}
		$laPrmInc = [];
		$laParams = $this->oDb
			->select('TRIM(CL1TMA) TIPOPRM, TRIM(CL2TMA) CODIGO, TRIM(DE2TMA) DESCRIPCION')
			->from('TABMAE')
			->where("TIPTMA='GENRIPS' AND ESTTMA=''")
			->in('CL1TMA', array_keys($laPrm))
			->orderBy('CL1TMA,CL2TMA')
			->getAll('array');
		if (is_array($laParams)) {
			foreach ($laParams as $laPar) {
				$laPrmInc[$laPrm[$laPar['TIPOPRM']]][$laPar['CODIGO']] = $laPar['DESCRIPCION'];
			}
		}
		return $laPrmInc;
	}

	/*
	 *	Retorna parámetro para incapacidades
	 */
	public function obtenerParametroIncapacidad($taParam = [], $tcCodigo = '')
	{
		if (count($taParam) > 0 && strlen($tcCodigo) > 0) {
			$laModPres = $this->obtenerParametrosIncapacidad($taParam, $tcCodigo);
			$lcKey = (array_values($taParam))[0];
			return isset($laModPres[$lcKey]) ? ($laModPres[$lcKey][$tcCodigo] ?? '') : '';
		} else {
			return '';
		}
	}

	/*
	 *	Retorna Modalidades de la Prestación
	 */
	public function obtenerModalidadesPrestacion($tcCodigo = '')
	{
		if (strlen($tcCodigo) > 0) {
			$this->oDb->where("CL2TMA='$tcCodigo'");
		}
		$laPrm = [];
		$laParams = $this->oDb
			->select('TRIM(CL2TMA) CODIGO, TRIM(DE2TMA) DESCRIPCION')
			->from('TABMAE')
			->where("TIPTMA='GENRIPS' AND CL1TMA='MODATEN' AND ESTTMA=''")
			->orderBy('CL2TMA')
			->getAll('array');
		if (is_array($laParams)) {
			foreach ($laParams as $laPar) {
				$laPrm[$laPar['CODIGO']] = $laPar['DESCRIPCION'];
			}
		}
		return $laPrm;
	}
	/*
	 *	Retorna descripción de la Modalidad de la Prestación con el código enviado
	 */
	public function obtenerModalidadPrestacion($tcCodigo = '')
	{
		if (strlen($tcCodigo) > 0) {
			$laModPres = $this->obtenerModalidadesPrestacion($tcCodigo);
			return $laModPres[$tcCodigo] ?? '';
		} else {
			return '';
		}
	}

	/*
	 *	Retorna grupo de servicio para una vía
	 */
	public function obtenerGrupoServicioVia($tcVia = '')
	{
		$laGrupo = [];
		$laGrupos = $this->oDb
			->select('TRIM(CL2TMA) CODGRUPO, TRIM(DE2TMA) GRUPO')
			->from('TABMAE')
			->where("TIPTMA='GENRIPS' AND CL1TMA='GRUSERV' AND CL3TMA='$tcVia' AND ESTTMA=''")
			->orderBy('CL2TMA')
			->get('array');
		if ($this->oDb->numRows() > 0) {
			$laGrupo[$laGrupos['CODGRUPO']] = $laGrupos['GRUPO'];
		}
		return $laGrupo;
	}

	public function datosMedicamentosSeleccionado($tcCodigoConsumoRec)
	{
		if (isset($this->oDb)) {
			$lcUnidadDosis = "7";
			$lcUnidad = '';
			$lcPresentacion = '';
			$lcConcentracion = '';
			$laCodigoConsumo = explode('-', $tcCodigoConsumoRec);
			$tcCodigoConsumo = $laCodigoConsumo[0];

			if (!empty($tcCodigoConsumo)) {
				$laObtieneUnidad = $this->oDb
					->select('UNIDAD VALOR_UNIDAD')
					->from('INVMEDU')
					->where('CODIGO', '=', $tcCodigoConsumo)
					->getAll('array');
				if (is_array($laObtieneUnidad)) {
					if (count($laObtieneUnidad) > 0) {
						foreach ($laObtieneUnidad as $laUnidad) {
							$lcUnidadDosis = $laUnidad['VALOR_UNIDAD'];
						}
					}
				}
				unset($laObtieneUnidad);

				$laObtienePresentacion = $this->oDb
					->select('UPPER(TRIM(A.UNCDES)) UNIDAD, TRIM(B.PRESE) PRESENTACION, TRIM(B.CONCE) CONCENTRACION')
					->from('INVDES AS A')
					->leftJoin('INVMEDA AS B', "TRIM(A.REFDES)=TRIM(B.CODIGO)", null)
					->where('A.REFDES', '=', $tcCodigoConsumo)
					->get('array');

				if (is_array($laObtienePresentacion)) {
					if (count($laObtienePresentacion) > 0) {
						$lcUnidad = $laObtienePresentacion['UNIDAD'];
						$lcPresentacion = $laObtienePresentacion['PRESENTACION'];
						$lcConcentracion = $laObtienePresentacion['CONCENTRACION'];
					}
				}
				unset($laObtienePresentacion);
			}
			$this->aDatosMedicamento = [
				'Unidaddosis' => $lcUnidadDosis,
				'Unidad' => $lcUnidad,
				'Presentacion' => $lcPresentacion,
				'Concentracion' => $lcConcentracion,
			];
		}
		return $this->aDatosMedicamento;
	}

	public function consultaUltimosMedicamentos($tnIngreso = 0)
	{
		$tnIngreso = intval($tnIngreso);
		$laConsecutivo = $this->oDb->max('CEVFRD', 'MAXIMO')->from('RIAFARD')->where(['NINFRD' => $tnIngreso,])->get("array");

		if (is_array($laConsecutivo)) {
			if (count($laConsecutivo) > 0) {
				$lnConsec = $laConsecutivo['MAXIMO'];
				settype($lnConsec, 'integer');
			}
		}
		unset($laConsecutivo);

		if ($lnConsec > 0) {
			$laObtieneFormula = $this->oDb
				->select('TRIM(A.MEDFRD) CODIGO, TRIM(B.DESDES) DESCRIPCION, A.DOSFRD DOSIS, TRIM(A.UDOFRD) UNIDAD_DOSIS, int(A.FRCFRD) FRECUENCIA, TRIM(A.UFRFRD) UNIDAD_FRECUENCIA')
				->from('RIAFARD AS A')
				->leftJoin('INVDES AS B', "TRIM(A.MEDFRD)=TRIM(B.REFDES)", null)
				->where('A.NINFRD', '=', $tnIngreso)
				->where('A.CEVFRD', '=', $lnConsec)
				->in('ESTFRD', [11, 12, 13, 15, 16])
				->orderBy('A.FEFFRD DESC, A.HMFFRD ASC')
				->getAll('array');
			$this->aUltimaFormula = $laObtieneFormula;
			unset($laObtieneFormula);
		}
	}

	public function consultarUltimaFormula()
	{
		return $this->aUltimaFormula;
	}

	public function consultaMedicamentosAnteriores($tcTipoIden = '', $tnNroIden = 0)
	{
		$lcChrRec = chr(24);
		$lcChrItm = chr(25);
		$laMedicamentos = [];
		$tnNroIden = intval($tnNroIden);
		$lnConsMaximo = $lnConsMinimo = 0;
		$lnCorAnterior = $this->oDb->obtenerTabmae1('trim(DE2TMA)', 'FORMEDIC', "CL1TMA='ORDMEDAN' AND ESTTMA=''", null, '');
		$lnCorAnterior = intval(trim($lnCorAnterior));
		$laConsecutivo = $this->oDb->max('CORORA', 'MAXIMO')->from('ORDAMB')->where(['TIDORA' => $tcTipoIden, 'NIDORA' => $tnNroIden,])->get("array");

		if (is_array($laConsecutivo)) {
			if (count($laConsecutivo) > 0) {
				$lnConsMaximo = $laConsecutivo['MAXIMO'];
				settype($lnConsMaximo, 'integer');
			}
		}
		unset($laConsecutivo);

		if ($lnConsMaximo > 0) {
			$lnConsMinimo = $lnConsMaximo - $lnCorAnterior;
			$laObtieneFormula = $this->oDb
				->select('CORORA, DESORA DESCRIPCION')
				->from('ORDAMB')
				->where([
					'TIDORA' => $tcTipoIden,
					'NIDORA' => $tnNroIden,
					'INDORA' => 8,
				])
				->where('CORORA', '>', $lnConsMinimo)
				->orderBy('CORORA DESC,CLNORA')
				->getAll('array');
			if (is_array($laObtieneFormula)) {
				if (count($laObtieneFormula) > 0) {
					$lcCnsOrden = $laObtieneFormula[0]['CORORA'];
					$lcDescripcion = '';
					foreach ($laObtieneFormula as $laRegistro) {
						if ($lcCnsOrden <> $laRegistro['CORORA']) {
							$lcDescripcion = trim($lcDescripcion) . $lcChrRec;
							$lcCnsOrden = $laRegistro['CORORA'];
						}
						$lcDescripcion .= $laRegistro['DESCRIPCION'];
					}
					$laLineas = explode($lcChrRec, $lcDescripcion);
					foreach ($laLineas as $lcMedica) {
						$lcPresentacion = '';
						$laMedica = explode($lcChrItm, $lcMedica);
						$lcCodigoMed = trim($laMedica[1]);

						$laPresentacion = $this->oDb->select(trim('PRESE'))->from('INVMEDA')->where(['CODIGO' => $lcCodigoMed,])->get("array");
						if (is_array($laPresentacion)) if (count($laPresentacion) > 0);
						$lcPresentacion = (isset($laPresentacion['PRESE']) && $laPresentacion['PRESE'] != '') ? trim($laPresentacion['PRESE']) : '';
						$laDescripcionMedicamento = $this->oDb->select('DESDES, RF4DES')->from('INVDES')->where(['REFDES' => $lcCodigoMed])->get("array");

						if (is_array($laDescripcionMedicamento)) if (count($laDescripcionMedicamento) > 0);
						$lcDescripcionMedicamento = (isset($laDescripcionMedicamento['DESDES']) && $laDescripcionMedicamento['DESDES'] != '') ? trim($laDescripcionMedicamento['DESDES']) : '';
						$lcPosNopos = (isset($laDescripcionMedicamento['RF4DES']) && $laDescripcionMedicamento['RF4DES'] != '') ? trim($laDescripcionMedicamento['RF4DES']) : '';

						if (mb_substr($lcCodigoMed, 0, 2) == 'NC' || !empty($lcDescripcionMedicamento)) {
							$lnEncuentra = array_search($lcCodigoMed, array_column($laMedicamentos, 'CODIGO'));
							if ($lnEncuentra === false) {
								$lcTipoDosis = trim($laMedica[4]);
								$lcDescripTipoDosis = $this->oDb->obtenerTabmae1('DE1TMA', 'MEDDOS', "CL2TMA='$lcTipoDosis'", null, '');
								$lcTipoDosisDiaria = trim($laMedica[8]);
								$lcDescripTipoDosisDiaria = $this->oDb->obtenerTabmae1('DE1TMA', 'MEDDOS', "CL2TMA='$lcTipoDosisDiaria'", null, '');
								$lcTipoFrecuencia = trim($laMedica[6]);
								$lcDescripTipoFrecuencia = $this->oDb->obtenerTabmae1('DE1TMA', 'MEDFRE', "CL2TMA='$lcTipoFrecuencia'", null, '');
								$lcViaAdministracion = trim(isset($laMedica[49]) ? $laMedica[49] : '');
								$lcDescripViaAdministracion = $this->oDb->obtenerTabmae1('DE1TMA', 'MEDVAD', "CL1TMA='$lcViaAdministracion'", null, '');

								$laMedicamentos[] = [
									'CODIGO' => trim($laMedica[1]),
									'DESCRIP' => trim(!empty($lcDescripcionMedicamento) ? $lcDescripcionMedicamento : $laMedica[2]),
									'DOSIS' => trim($laMedica[3]),
									'TIPODCOD' => $lcTipoDosis,
									'DESTIPODOSIS' => trim($lcDescripTipoDosis),
									'FRECUENCIA' => trim($laMedica[5]),
									'TIPOCODF' => $lcTipoFrecuencia,
									'DESTIPOFRECUENCIA' => trim($lcDescripTipoFrecuencia),
									'DOSISDIA' => trim($laMedica[7]),
									'TIPODCODDIA' => $lcTipoDosisDiaria,
									'DESTIPODOSISDIARIA' => $lcDescripTipoDosisDiaria,
									'TIEMPOTRATA' => trim($laMedica[9]),
									'TIPOTIEMTRAT' => trim($laMedica[10]),
									'CANTID' => trim($laMedica[11]),
									'CANTIDADTRAT' => trim(isset($laMedica[51]) ? $laMedica[51] : trim($lcPresentacion)),
									'OBSERVA' => trim($laMedica[12]),
									'PRESENTANP' => trim(isset($laMedica[13]) ? $laMedica[13] : ''),
									'CONCENTRANP' => trim(isset($laMedica[14]) ? $laMedica[14] : ''),
									'UNIDADNP' => trim(isset($laMedica[15]) ? $laMedica[15] : ''),
									'GRUPOTNP' => trim(isset($laMedica[16]) ? $laMedica[16] : ''),
									'TIEMPOTNP' => trim(isset($laMedica[17]) ? $laMedica[17] : ''),
									'INVIMANP' => trim(isset($laMedica[18]) ? $laMedica[18] : ''),
									'EFECTO' => trim(isset($laMedica[19]) ? $laMedica[19] : ''),
									'EFECTOS' => trim(isset($laMedica[20]) ? $laMedica[20] : ''),
									'PACIENTEINF' => trim(isset($laMedica[21]) ? $laMedica[21] : ''),
									'BIBLIOGRAFIA' => trim(isset($laMedica[22]) ? $laMedica[22] : ''),
									'CODIGOP' => trim(isset($laMedica[23]) ? $laMedica[23] : ''),
									'MEDICAP' => trim(isset($laMedica[24]) ? $laMedica[24] : ''),
									'DOSISP' => trim(isset($laMedica[25]) ? $laMedica[25] : ''),
									'TIPODOSISP' => trim(isset($laMedica[26]) ? $laMedica[26] : ''),
									'FRECUENCIAP' => trim(isset($laMedica[27]) ? $laMedica[27] : ''),
									'TFRECUENCIAP' => trim(isset($laMedica[28]) ? $laMedica[28] : ''),
									'DOSISDIAP' => trim(isset($laMedica[29]) ? $laMedica[29] : ''),
									'TIPODOSISDIAP' => trim(isset($laMedica[30]) ? $laMedica[30] : ''),
									'TRATAMIENTOP' => trim(isset($laMedica[31]) ? $laMedica[31] : ''),
									'TIPOTRATAMP' => trim(isset($laMedica[32]) ? $laMedica[32] : ''),
									'CANTIDADP' => trim(isset($laMedica[33]) ? $laMedica[33] : ''),
									'PRESENTAP' => trim(isset($laMedica[35]) ? $laMedica[35] : ''),
									'CONCENTRAP' => trim(isset($laMedica[36]) ? $laMedica[36] : ''),
									'UNIDADP' => trim(isset($laMedica[37]) ? $laMedica[37] : ''),
									'RESUMENNP' => trim(isset($laMedica[45]) ? $laMedica[45] : ''),
									'NOPOS' => trim(isset($laMedica[46]) ? $laMedica[46] : ''),
									'RIESGOINP' => trim(isset($laMedica[48]) ? $laMedica[48] : ''),
									'VIACOD' => $lcViaAdministracion,
									'DECRIPCIONVIACOD' => $lcDescripViaAdministracion,
									'VIAP' => trim(isset($laMedica[50]) ? $laMedica[50] : ''),
								];
							}
						}
					}
				}
			}
			unset($laMedicamentoActivo);
		}
		$this->aMedicamentosAnteriores = $laMedicamentos;
	}

	public function ordenesAmbulatoriasPaciente($tcTipoIden = '', $tnNroIden = 0)
	{
		$laordenesAmbulatorias = $this->oDb
			->select('A.TIDORA TIPOIDE, A.NIDORA NROIDE, A.INGORA INGRESO, A.CORORA ORDEN, SUBSTR(A.DESORA, 0, 9) FECHA, SUBSTR(A.DESORA, 9, 6) HORA, TRIM(SUBSTR(A.DESORA, 34, 4)) CIE, TRIM(B.DESRIP) DESCRIPCION_CIE')
			->select('TRIM(SUBSTR(A.DESORA, 44, 8)) PLAN, TRIM(C.DSCCON) DESCRIPCION_PLAN, A.CCIORA CONSEC_CITA, A.CCOORA CONSEC_CONSULTA')
			->from('ORDAMB A')
			->leftJoin('RIACIE B', 'TRIM(SUBSTR(A.DESORA, 34, 4))=TRIM(B.ENFRIP)', null)
			->leftJoin('FACPLNC C', 'TRIM(SUBSTR(A.DESORA, 44, 8))=TRIM(C.PLNCON)', null)
			->where([
				'TIDORA' => $tcTipoIden,
				'NIDORA' => $tnNroIden,
			])
			->where('INDORA', '=', 1)
			->orderBy('CORORA DESC')
			->getAll('array');
		if (is_array($laordenesAmbulatorias)) {
			if (count($laordenesAmbulatorias) > 0) {
				$this->aListaOrdenesAmbulatorias = $laordenesAmbulatorias;
			}
		}
		unset($laordenesAmbulatorias);
	}

	public function ingresoUltimoAmbulatorio($tcTipoIden = '', $tnNroIden = 0)
	{
		$laDatosUltimoIngreso = [];
		$lcDiasMaximo = $this->oDb->obtenerTabmae1('trim(DE2TMA)', 'FORMEDIC', "CL1TMA='ORDMEDMX' AND ESTTMA=''", null, '');
		$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'FORMEDIC', ['CL1TMA' => 'ORDMEDVI', 'ESTTMA' => ' ']);
		$lcListaViasIngreso = trim(AplicacionFunciones::getValue($oTabmae, 'DE2TMA', ''));
		$lcListaViasIngreso = empty($lcListaViasIngreso) ? "'02'" : $lcListaViasIngreso;
		$laViasIngreso = explode(',', str_replace("'", '', $lcListaViasIngreso));

		$laIngresoAmbulatorias = $this->oDb
			->max('FEIING', 'FECHAINGRESO')
			->from('RIAING')
			->where([
				'TIDING' => $tcTipoIden,
				'NIDING' => $tnNroIden,
			])
			->in('VIAING', $laViasIngreso)
			->get('array');
		if (is_array($laIngresoAmbulatorias)) {
			if (count($laIngresoAmbulatorias) > 0) {
				$this->aUltimoIngresoAmb = $laIngresoAmbulatorias['FECHAINGRESO'] . ' - ' . $lcDiasMaximo;
			}
		}
		unset($laIngresoAmbulatorias);
	}

	public function cantidadCaracteresObservacioneMed()
	{
		$lcDatos = $this->oDb->obtenerTabmae1('trim(DE2TMA)', 'FORMEDIC', "CL1TMA='CANTOBS' AND ESTTMA=''", null, '');
		$laReturn = trim($lcDatos);
		return $laReturn;
	}

	public function consultaTituloPlan()
	{
		$lcDatos = $this->oDb->obtenerTabmae1('trim(DE2TMA)', 'FORMEDIC', "CL1TMA='TITPLANW' AND ESTTMA=''", null, '');
		$laReturn = trim($lcDatos);
		return $laReturn;
	}

	public function fnMedicamentosAnteriores()
	{
		return $this->aMedicamentosAnteriores;
	}

	public function consultarNopos($taCups = null, $taMedicamentos = null)
	{
		require_once __DIR__ . '/class.NoPosFunciones.php';
		$loNoPos = new NoPosFunciones();
		$laListaNoPos = $loNoPos->consultarNopos(['cup' => $taCups, 'med' => $taMedicamentos]);
		return $loNoPos->obtenerTextoNopos($laListaNoPos);
	}

	function IniciaDatosIngreso($tnIngreso = 0)
	{
		$this->aIngreso = $this->DatosIngreso($tnIngreso);
		$ltAhora = new \DateTime($this->oDb->fechaHoraSistema());
		$this->cFecCre = $ltAhora->format('Ymd');
		$this->cHorCre = $ltAhora->format('His');
		$this->cUsuCre = (isset($_SESSION[HCW_NAME]) ? $_SESSION[HCW_NAME]->oUsuario->getUsuario() : '');
		$this->cPrgCre = 'ORDA01AWEB';
		$this->cEspecialidad  = (isset($_SESSION[HCW_NAME]) ? $_SESSION[HCW_NAME]->oUsuario->getEspecialidad() : '');
		$this->cRegMed  = (isset($_SESSION[HCW_NAME]) ? $_SESSION[HCW_NAME]->oUsuario->getRegistro() : '');
	}

	function DatosIngreso($tnIngreso = 0)
	{
		$this->oAmbIng->cargarIngreso($tnIngreso);
		return [
			'nIngreso' => $this->oAmbIng->nIngreso,
			'cTipId' => $this->oAmbIng->cId,
			'nNumId' => $this->oAmbIng->nId,
			'cNombre' => $this->oAmbIng->oPaciente->getNombreCompleto(),
			'cSexo' => $this->oAmbIng->oPaciente->cSexo,
			'aEdad' => $this->oAmbIng->aEdad,
			'cCodVia' => $this->oAmbIng->cVia,
			'cDesVia' => $this->oAmbIng->cDescVia,
			'nEntidad' => $this->oAmbIng->nEntidad,
			'cPlan' => $this->oAmbIng->cPlan,
			'cPlanDsc' => $this->oAmbIng->obtenerDescripcionPlan(),
			'cSeccion' => $this->oAmbIng->oHabitacion->cSeccion,
			'cHabita' => $this->oAmbIng->oHabitacion->cHabitacion,
			'nIngresoFecha' => $this->oAmbIng->nIngresoFecha,
			'nHistoria' => $this->oAmbIng->oPaciente->nNumHistoria,
		];
	}

	function verificarOA($taDatos = [])
	{
		$this->IniciaDatosIngreso($taDatos['Ingreso']);
		$taDatos['Diagnostico'] = strtoupper(trim($taDatos['Diagnostico']));
		$this->cDxPrincipal = $taDatos['Diagnostico'];
		if (strlen($taDatos['Diagnostico']) == 0) {
			$this->aError = [
				'Valido' => false,
				'Mensaje' => 'Diagnóstico Principal es obligatorio',
				'Objeto' => 'buscarProcedimiento',
			];
		} else {
			$this->aError = $this->validacion($taDatos['Ambulatorio']);
		}
		return $this->aError;
	}

	public function validacion($validacionDatos)
	{
		$laRetornar = [
			'Valido' => true,
			'Mensaje' => '',
			'Objeto' => 'buscarProcedimiento',
		];
		$lbRevisar = true;

		if ($lbRevisar && !empty($validacionDatos['MedicamentosAmb'])) {
			if (count($validacionDatos['MedicamentosAmb']) > 0) {
				$loObjC = new FormulacionParametros();
				$loObjC->obtenerParametrosTodos();

				foreach ($validacionDatos['MedicamentosAmb'] as $lnKey => $laMedicaEgreso) {
					$lcValidarDescripion = $laMedicaEgreso['MEDICA'];
					$lcObjeto = "AdicionarMedAmb";

					//Valida que el medicamento codificado exista
					if (mb_substr($laMedicaEgreso['CODIGO'], 0, 2) != 'NC') {
						$laResultado = $loObjC->BuscarMedicamento(trim($laMedicaEgreso['CODIGO']));
						if (empty($laResultado)) {
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "No existe en la base de datos el medicamento codificado: " . trim($laMedicaEgreso['MEDICA']);
							$laRetornar['Objeto'] = $lcObjeto;
							$lbRevisar = false;
							break;
						}
					}

					if (empty(trim($lcValidarDescripion))) {
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = "No existe medicamento: " . trim($laMedicaEgreso['MEDICA']);
						$laRetornar['Objeto'] = $lcObjeto;
						$lbRevisar = false;
					}

					if (empty(trim($laMedicaEgreso['DOSIS']))) {
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = "Dosis obligatoria para el medicamento: " . trim($laMedicaEgreso['MEDICA']);
						$laRetornar['Objeto'] = $lcObjeto;
						$lbRevisar = false;
						break;
					}

					$laResultado = $loObjC->unidadDosis($laMedicaEgreso['TIPODCOD']);
					if (empty($laResultado)) {
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = "No existe el tipo de dosis digitado para el medicamento: " . trim($laMedicaEgreso['MEDICA']);
						$laRetornar['Objeto'] = $lcObjeto;
						$lbRevisar = false;
						break;
					}

					if (empty(trim($laMedicaEgreso['FRECUENCIA']))) {
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = "Frecuencia obligatoria para el medicamento: " . trim($laMedicaEgreso['MEDICA']);
						$laRetornar['Objeto'] = $lcObjeto;
						$lbRevisar = false;
						break;
					}

					$laResultado = $loObjC->Frecuencia($laMedicaEgreso['TIPOCODF']);
					if (empty($laResultado)) {
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = "No existe el tipo de Frecuencia digitado para el medicamento: " . trim($laMedicaEgreso['MEDICA']);
						$laRetornar['Objeto'] = $lcObjeto;
						$lbRevisar = false;
						break;
					}

					if (empty(trim($laMedicaEgreso['DOSISDIA']))) {
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = "Dosis diaria obligatoria para el medicamento: " . trim($laMedicaEgreso['MEDICA']);
						$laRetornar['Objeto'] = $lcObjeto;
						$lbRevisar = false;
						break;
					}

					$laResultado = $loObjC->unidadDosis($laMedicaEgreso['TIPODCODDIA']);
					if (empty($laResultado)) {
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = "No existe el tipo de dosis diaria digitado para el medicamento: " . trim($laMedicaEgreso['MEDICA']);
						$laRetornar['Objeto'] = $lcObjeto;
						$lbRevisar = false;
						break;
					}

					if (empty(trim($laMedicaEgreso['TIEMPOTRATA']))) {
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = "Tiempo de Tratamiento obligatoria para el medicamento: " . trim($laMedicaEgreso['MEDICA']);
						$laRetornar['Objeto'] = $lcObjeto;
						$lbRevisar = false;
						break;
					}

					if ($laMedicaEgreso['TIPOCODTIEMTRAT'] != 'DIAS') {
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = "No existe el tipo de tratamiento digitado para el medicamento: " . trim($laMedicaEgreso['MEDICA']);
						$laRetornar['Objeto'] = $lcObjeto;
						$lbRevisar = false;
						break;
					}

					$laResultado = $loObjC->viaAdmin($laMedicaEgreso['VIACOD']);
					if (empty($laResultado)) {
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = "No existe el tipo de vía administración para el medicamento: " . trim($laMedicaEgreso['MEDICA']);
						$laRetornar['Objeto'] = $lcObjeto;
						$lbRevisar = false;
						break;
					}

					//Validación medicamento NO POS con CTC
					$lcPlan = $validacionDatos['PlanCups'];
					$loObjNP = new NoPosFunciones();
					$llTipoNOPOS = substr($loObjNP->entidadMipres($lcPlan), 0, 1);

					if (trim($laMedicaEgreso['NOPOS'] == '1') && $llTipoNOPOS == 'S' && substr($laMedicaEgreso['CODIGO'], 0, 2) != 'NC') {

						$laResultadoCTC = $loObjC->DatosMedicamentoNOPOS(trim($laMedicaEgreso['CODIGO']));

						if (trim($laResultadoCTC['PRESE']) != trim($laMedicaEgreso['PRESENTANP'])) {
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "Error en la presentación del medicamento NO POS para CTC: " . trim($laMedicaEgreso['MEDICA']);
							$laRetornar['Objeto'] = $lcObjeto;
							$lbRevisar = false;
							break;
						}

						if (trim($laResultadoCTC['CONCE']) != trim($laMedicaEgreso['CONCENTRANP'])) {
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "Error en la concentración del medicamento NO POS para CTC: " . trim($laMedicaEgreso['MEDICA']);
							$laRetornar['Objeto'] = $lcObjeto;
							$lbRevisar = false;
							break;
						}

						if (trim($laResultadoCTC['UNIDA']) != trim($laMedicaEgreso['UNIDADNP'])) {
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "Error en la unidad del medicamento NO POS para CTC: " . trim($laMedicaEgreso['MEDICA']);
							$laRetornar['Objeto'] = $lcObjeto;
							$lbRevisar = false;
							break;
						}

						if (trim($laResultadoCTC['GTEJUS'] ?? '') != trim($laMedicaEgreso['GRUPOTNP'])) {
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "Error en el Grupo Terapeútico del medicamento NO POS para CTC: " . trim($laMedicaEgreso['MEDICA']);
							$laRetornar['Objeto'] = $lcObjeto;
							$lbRevisar = false;
							break;
						}

						if (trim($laResultadoCTC['TIMJUS'] ?? '') != trim($laMedicaEgreso['TIEMPOTNP'])) {
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "Error en el Tiempo de Respuesta del medicamento NO POS para CTC: " . trim($laMedicaEgreso['MEDICA']);
							$laRetornar['Objeto'] = $lcObjeto;
							$lbRevisar = false;
							break;
						}

						if (trim($laResultadoCTC['RGIJUS'] ?? '') != trim($laMedicaEgreso['INVIMANP'])) {
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "Error en el Registro Invima del medicamento NO POS para CTC: " . trim($laMedicaEgreso['MEDICA']);
							$laRetornar['Objeto'] = $lcObjeto;
							$lbRevisar = false;
							break;
						}

						if (empty(trim($laMedicaEgreso['RIESGOINP']))) {
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "El dato Riesgo Inminente NO POS es obligatorio para CTC: " . trim($laMedicaEgreso['MEDICA']);
							$laRetornar['Objeto'] = $lcObjeto;
							$lbRevisar = false;
							break;
						}

						$loObjC->ObtenerRiesgoCTC();
						$laResultado = $loObjC->riesgoInminente(trim($laMedicaEgreso['RIESGOINP']));
						if (empty(trim($laResultado))) {
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "Error en el Riesgo Inminente del medicamento NO POS para CTC: " . trim($laMedicaEgreso['MEDICA']);
							$laRetornar['Objeto'] = $lcObjeto;
							$lbRevisar = false;
							break;
						}

						if (empty(trim($laMedicaEgreso['RESUMENNP']))) {
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "El dato para resumen de HC que justifique el medicamento NO POS para CTC: " . trim($laMedicaEgreso['MEDICA']);
							$laRetornar['Objeto'] = $lcObjeto;
							$lbRevisar = false;
							break;
						}

						// VALIDAR MEDICAMENTO POS SI EXISTE
						if (!empty(trim($laMedicaEgreso['CODIGOP']))) {

							$laResultado = $loObjC->BuscarMedicamento(trim($laMedicaEgreso['CODIGOP']));
							if (empty($laResultado)) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "No existe en la base de datos el medicamento POS para CTC: " . trim($laMedicaEgreso['MEDICA']);
								$laRetornar['Objeto'] = $lcObjeto;
								$lbRevisar = false;
								break;
							}

							$laResultado = $loObjC->DatosMedicamentoNOPOS(trim($laMedicaEgreso['CODIGOP']));
							if (trim($laResultado['PRESE']) != trim($laMedicaEgreso['PRESENTAP'])) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "Error en la presentación del medicamento POS para CTC: " . trim($laMedicaEgreso['MEDICA']);
								$laRetornar['Objeto'] = $lcObjeto;
								$lbRevisar = false;
								break;
							}

							if (trim($laResultado['CONCE']) != trim($laMedicaEgreso['CONCENTRAP'])) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "Error en la concentración del medicamento POS para CTC: " . trim($laMedicaEgreso['MEDICA']);
								$laRetornar['Objeto'] = $lcObjeto;
								$lbRevisar = false;
								break;
							}

							if (trim($laResultado['UNIDA']) != trim($laMedicaEgreso['UNIDADP'])) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "Error en la unidad del medicamento POS para CTC: " . trim($laMedicaEgreso['MEDICA']);
								$laRetornar['Objeto'] = $lcObjeto;
								$lbRevisar = false;
								break;
							}

							if (empty(trim($laMedicaEgreso['DOSISP']))) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "Dosis obligatoria para medicamento POS del CTC: " . trim($laMedicaEgreso['MEDICA']);
								$laRetornar['Objeto'] = $lcObjeto;
								$lbRevisar = false;
								break;
							}

							$laResultado = $loObjC->unidadDosis($laMedicaEgreso['TIPODOSISP']);
							if (empty($laResultado)) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "No existe el tipo de dosis digitado para medicamento POS del CTC: " . trim($laMedicaEgreso['MEDICA']);
								$laRetornar['Objeto'] = $lcObjeto;
								$lbRevisar = false;
								break;
							}

							if (empty(trim($laMedicaEgreso['FRECUENCIAP']))) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "Frecuencia obligatoria para medicamento POS del CTC: " . trim($laMedicaEgreso['MEDICA']);
								$laRetornar['Objeto'] = $lcObjeto;
								$lbRevisar = false;
								break;
							}

							$laResultado = $loObjC->Frecuencia($laMedicaEgreso['TFRECUENCIAP']);
							if (empty($laResultado)) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "No existe el tipo de Frecuencia digitado para medicamento POS del CTC: " . trim($laMedicaEgreso['MEDICA']);
								$laRetornar['Objeto'] = $lcObjeto;
								$lbRevisar = false;
								break;
							}

							if (empty(trim($laMedicaEgreso['DOSISDIAP']))) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "Dosis diaria obligatoria para medicamento POS del CTC: " . trim($laMedicaEgreso['MEDICA']);
								$laRetornar['Objeto'] = $lcObjeto;
								$lbRevisar = false;
								break;
							}

							$laResultado = $loObjC->unidadDosis($laMedicaEgreso['TIPODOSISDIAP']);
							if (empty($laResultado)) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "No existe el tipo de dosis diaria digitado para medicamento POS del CTC: " . trim($laMedicaEgreso['MEDICA']);
								$laRetornar['Objeto'] = $lcObjeto;
								$lbRevisar = false;
								break;
							}

							if (empty(trim($laMedicaEgreso['TRATAMIENTOP']))) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "Tiempo de Tratamiento obligatoria para medicamento POS del CTC: " . trim($laMedicaEgreso['MEDICA']);
								$laRetornar['Objeto'] = $lcObjeto;
								$lbRevisar = false;
								break;
							}

							if ($laMedicaEgreso['TIPOTRATAMP'] != 'DIAS') {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "No existe el tipo de tratamiento digitado para medicamento POS del CTC: " . trim($laMedicaEgreso['MEDICA']);
								$laRetornar['Objeto'] = $lcObjeto;
								$lbRevisar = false;
								break;
							}

							if (empty(trim($laMedicaEgreso['CANTIDADP']))) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "Cantidad obligatoria para medicamento POS del CTC: " . trim($laMedicaEgreso['MEDICA']);
								$laRetornar['Objeto'] = $lcObjeto;
								$lbRevisar = false;
								break;
							}

							$laResultado = $loObjC->viaAdmin($laMedicaEgreso['VIAP']);
							if (empty($laResultado)) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "No existe el tipo de vía administración para medicamento POS del CTC: " . trim($laMedicaEgreso['MEDICA']);
								$laRetornar['Objeto'] = $lcObjeto;
								$lbRevisar = false;
								break;
							}
						}

						if (trim($laResultadoCTC['EFEJUS'] ?? '') != trim($laMedicaEgreso['EFECTO'])) {
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "Error en el Efecto deseado al tratamiento del medicamento NO POS para CTC: " . trim($laMedicaEgreso['MEDICA']);
							$laRetornar['Objeto'] = $lcObjeto;
							$lbRevisar = false;
							break;
						}

						if (trim($laResultadoCTC['ESRJUS'] ?? '') != trim($laMedicaEgreso['EFECTOS'])) {
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "Error en el Efecto secundario del medicamento NO POS para CTC: " . trim($laMedicaEgreso['MEDICA']);
							$laRetornar['Objeto'] = $lcObjeto;
							$lbRevisar = false;
							break;
						}

						if (empty($laMedicaEgreso['BIBLIOGRAFIA'])) {
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "Error en el Efecto secundario del medicamento NO POS para CTC: " . trim($laMedicaEgreso['MEDICA']);
							$laRetornar['Objeto'] = $lcObjeto;
							$lbRevisar = false;
							break;
						}
					}
				}
			}
		}

		if ($lbRevisar && !empty($validacionDatos['Procedimientos'])) {
			foreach ($validacionDatos['Procedimientos'] as $valProcedimientos) {
				$lcCodigoCups = trim($valProcedimientos['CODIGO']);
				$lcDescripCup = '';
				$lcObjeto = "buscarProcedimiento";
				$llTipoNP = false;

				if (!empty($lcCodigoCups)) {
					$laErrores = [];
					$lcTablaValida = 'RIACUP';
					$laWhere = ['IDDCUP' => '0', 'CODCUP' => $lcCodigoCups,];
					try {
						$lbValidar = false;
						$laReg = $this->oDb->from($lcTablaValida)->where($laWhere)->get('array');
						if (is_array($laReg)) if (count($laReg) > 0) $lbValidar = true;

						if (!$lbValidar) {
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "NO se encontro el código procedimiento " . $lcCodigoCups;
							$laRetornar['Objeto'] = $lcObjeto;
							$lbRevisar = false;
							break;
						} else {
							$lcDescripCup = trim($laReg['DESCUP']) ?? '';
							$llTipoNP = ($laReg['RF5CUP'] == 'NOPB');
						}
					} catch (\Exception $loError) {
						$laErrores[] = $loError->getMessage();
					} catch (\PDOException $loError) {
						$laErrores[] = $loError->getMessage();
					}
				}

				// Validar descripción
				if (trim($valProcedimientos['DESCRIPCION']) !== $lcDescripCup) {
					$laRetornar['Valido'] = false;
					$laRetornar['Mensaje'] = "Error en la descripción del procedimiento " . $lcCodigoCups;
					$laRetornar['Objeto'] = $lcObjeto;
					$lbRevisar = false;
					break;
				}

				// Cantidad de procedimiento
				if (intval($valProcedimientos['CANTIDAD']) < 1) {
					$laRetornar['Valido'] = false;
					$laRetornar['Mensaje'] = "Error en la cantidad del procedimiento " . $lcCodigoCups;
					$laRetornar['Objeto'] = $lcObjeto;
					$lbRevisar = false;
					break;
				}

				if ($llTipoNP) {

					// Valida solicitado por
					require_once __DIR__ . '/class.Procedimientos.php';
					$loObjC = new Procedimientos();
					$loObjC->obtenerUbicacion();
					$laResultado = $loObjC->getUbicacion($valProcedimientos['SOLICITADO']);

					if (empty($laResultado)) {
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = "No existe servicio solicitado del procedimiento NO POS para CTC" . $lcDescripCup;
						$laRetornar['Objeto'] = $lcObjeto;
						$lbRevisar = false;
						break;
					}

					// Valida objetivo
					$loObjC->obtenerObjetivos();
					$laResultado = $loObjC->getObjetivo($valProcedimientos['OBJETIVO']);

					if (empty($laResultado)) {
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = "No existe objetivo para el procedimiento NO POS para CTC" . $lcDescripCup;
						$laRetornar['Objeto'] = $lcObjeto;
						$lbRevisar = false;
						break;
					}

					// Valida Riesgo Inminente
					if (empty(trim($valProcedimientos['RIESGO']))) {
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = "El dato Riesgo Inminente NO POS es obligatorio para CTC: " . $lcDescripCup;
						$laRetornar['Objeto'] = $lcObjeto;
						$lbRevisar = false;
						break;
					}

					$loObjC = new FormulacionParametros();
					$loObjC->ObtenerRiesgoCTC();
					$laResultadoCTC = $loObjC->riesgoInminente(trim($valProcedimientos['RIESGO']));
					if (empty(trim($laResultadoCTC))) {
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = "Error en el Riesgo Inminente del medicamento NO POS para CTC: " . $lcDescripCup;
						$laRetornar['Objeto'] = $lcObjeto;
						$lbRevisar = false;
						break;
					}

					// Valida procedimiento POS si existe
					$lbValidar = false;
					if ($valProcedimientos['EXISTE'] == '1') {
						$laWhere = ['IDDCUP' => '0', 'CODCUP' => $valProcedimientos['CODIGOPOS'],];
						$laReg = $this->oDb->from($lcTablaValida)->where($laWhere)->getAll('array');
						if (is_array($laReg)) if (count($laReg) > 0) $lbValidar = true;

						if ($lbValidar) {
							$llTipoNP = (trim($laReg['RF5CUP']) == 'PB');
							if (!$llTipoNP) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "Error en el procedimiento POS para CTC: " . $lcDescripCup;
								$laRetornar['Objeto'] = $lcObjeto;
								$lbRevisar = false;
								break;
							}
						} else {
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "NO se encontro el código procedimiento POS para el CTC" . $lcDescripCup;
							$laRetornar['Objeto'] = $lcObjeto;
							$lbRevisar = false;
							break;
						}

						// Valida cantidad del procedimiento POS
						if (intval($valProcedimientos['CANTIDADPOS']) < 1) {
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "Error en la cantidad del procedimiento POS del CTC " .  $lcDescripCup;
							$laRetornar['Objeto'] = $lcObjeto;
							$lbRevisar = false;
							break;
						}
					}

					// Valida Resumen de HC CTC
					if (empty(trim($valProcedimientos['RESUMEN']))) {
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = "No existe información de Resumen de HC para procedimiento POS del CTC " .  $lcDescripCup;
						$laRetornar['Objeto'] = $lcObjeto;
						$lbRevisar = false;
						break;
					}

					// Valida Bibliografia CTC
					if (empty(trim($valProcedimientos['BIBLIOGRAFIA']))) {
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = "No existe información de Bibliografia para procedimiento POS del CTC " .  $lcDescripCup;
						$laRetornar['Objeto'] = $lcObjeto;
						$lbRevisar = false;
						break;
					}
				}
			}
		}

		if ($lbRevisar && !empty($validacionDatos['Interconsultas'])) {
			foreach ($validacionDatos['Interconsultas'] as $valInterconsultas) {
				$lcCodigoInterconsulta = trim($valInterconsultas['CODIGO']);

				if (!empty($lcCodigoInterconsulta)) {
					$laErrores = [];
					$lcTablaValida = 'RIAESPE';
					$laWhere = ['CODESP' => $lcCodigoInterconsulta,];
					try {
						$lbValidar = false;
						$laReg = $this->oDb->from($lcTablaValida)->where($laWhere)->getAll('array');
						if (is_array($laReg)) if (count($laReg) > 0) $lbValidar = true;

						if (!$lbValidar) {
							$laRetornar['Valido'] = $lbRevisar = false;
							$laRetornar['Mensaje'] = "NO se encontro el código interconsulta";
							$laRetornar['Objeto'] = "buscarInterconsulta";
							break;
						}
					} catch (\Exception $loError) {
						$laErrores[] = $loError->getMessage();
					} catch (\PDOException $loError) {
						$laErrores[] = $loError->getMessage();
					}
				}
			}
		}

		if ($lbRevisar && !empty($validacionDatos['Dieta'])) {
			$lcCodigoDieta = $validacionDatos['Dieta']['tipoDieta'];

			if (!empty($lcCodigoDieta)) {
				$laErrores = [];
				$lcTablaValida = 'PRMTAB';
				$laWhere = ['TABTIP' => 'TDI', 'TABCOD' => $lcCodigoDieta,];
				try {
					$lbValidar = false;
					$laReg = $this->oDb->from($lcTablaValida)->where($laWhere)->getAll('array');
					if (is_array($laReg)) if (count($laReg) > 0) $lbValidar = true;
					if (!$lbValidar) {
						$laRetornar['Valido'] = $lbRevisar = false;
						$laRetornar['Mensaje'] = "NO se encontro el código tipo dieta";
						$laRetornar['Objeto'] = "seltipoDieta";
					}
				} catch (\Exception $loError) {
					$laErrores[] = $loError->getMessage();
				} catch (\PDOException $loError) {
					$laErrores[] = $loError->getMessage();
				}
			}
		}

		if ($lbRevisar && isset($validacionDatos['Incapacidad'])) {

			$lcTipoIncapacidad = $validacionDatos['Incapacidad']['TipoIncapacidad'];
			$lbExisteIncapacidad = strlen($lcTipoIncapacidad) > 0;
			if ($lbExisteIncapacidad) {
				$lcOrigenIncapacidad = $validacionDatos['Incapacidad']['OrigenIncapacidad'];
				$lcCausaAtencion = $validacionDatos['Incapacidad']['CausaAtencion'];
				if ($lbRevisar && empty($lcOrigenIncapacidad)) {
					$laRetornar['Valido'] = $lbRevisar = false;
					$laRetornar['Mensaje'] = '"Presunto Origen Incapacidad" es obligatorio.';
					$laRetornar['Objeto'] = 'selOrigenIncapacidad';
				}
				if ($lbRevisar && empty($lcCausaAtencion)) {
					$laRetornar['Valido'] = $lbRevisar = false;
					$laRetornar['Mensaje'] = '"Causa que Motiva la Atención" es obligatorio.';
					$laRetornar['Objeto'] = 'selCausaAtencion';
				}
				if ($lbRevisar && !empty($validacionDatos['Incapacidad']['cCodigoCieOrdAmbR'])) {
					if ($this->cDxPrincipal == $validacionDatos['Incapacidad']['cCodigoCieOrdAmbR']) {
						$laRetornar['Valido'] = $lbRevisar = false;
						$laRetornar['Mensaje'] = '"Diagnóstico Relacionado Incapacidad" no puede ser igual al "Diagnóstico Principal".';
						$laRetornar['Objeto'] = 'txtCodigoCieOrdAmbR';
					}
				}
				if ($lbRevisar) {
					switch ($lcTipoIncapacidad) {
						case 'AMB':		// Incapacidad Ambulatoria
						case 'PRO':		// Prórroga de incapacidad
							$lnDiasIncapacidad = intval($validacionDatos['Incapacidad']['DiasIncapacidad']);
							if ($lbRevisar && $lnDiasIncapacidad == 0) {
								$laRetornar['Valido'] = $lbRevisar = false;
								$laRetornar['Mensaje'] = '"Días incapacidad" es obligatorio.';
								$laRetornar['Objeto'] = 'txtDiasIncapacidad';
							}
							if ($lbRevisar && $lnDiasIncapacidad > 30) {
								$laRetornar['Valido'] = $lbRevisar = false;
								$laRetornar['Mensaje'] = '"Días incapacidad" no puede ser mayor que 30.';
								$laRetornar['Objeto'] = 'txtDiasIncapacidad';
							}
							if ($lbRevisar && empty($validacionDatos['Incapacidad']['Prorroga'])) {
								$laRetornar['Valido'] = $lbRevisar = false;
								$laRetornar['Mensaje'] = '"Es prórroga" es obligatorio.';
								$laRetornar['Objeto'] = 'selProrroga';
							}
							if ($lbRevisar) {
								$laReg = $this->oDb->select('CL2TMA')->from('TABMAE')->where(['TIPTMA' => 'GENRIPS', 'CL1TMA' => 'INCORIG', 'CL2TMA' => $lcOrigenIncapacidad,])->getAll('array');
								if ($this->oDb->numRows() == 0) {
									$laRetornar['Valido'] = $lbRevisar = false;
									$laRetornar['Mensaje'] = 'NO se encontro el código origen de incapacidad';
									$laRetornar['Objeto'] = 'selOrigenIncapacidad';
								} else {
									$lnFechaDesdeIncapacidad = intval(str_replace('-', '', $validacionDatos['Incapacidad']['FechaDesde']));
									$lnFechaHastaIncapacidad = intval(str_replace('-', '', $validacionDatos['Incapacidad']['FechaHasta']));

									if ($lnFechaDesdeIncapacidad > $lnFechaHastaIncapacidad) {
										$laRetornar['Valido'] = $lbRevisar = false;
										$laRetornar['Mensaje'] = 'Fecha inicio de incapacidad no puede ser mayor a fecha final de incapacidad.';
										$laRetornar['Objeto'] = 'txtFechaDesde';
									} else {
										// validar que los días de incapacidad correspondan con las fechas
										$ldFechaIni = \DateTime::createFromFormat('Ymd', $lnFechaDesdeIncapacidad);
										$ldFechaFin = \DateTime::createFromFormat('Ymd', $lnFechaHastaIncapacidad);
										$loDifFecha = $ldFechaIni->diff($ldFechaFin);
										$lnDiffDias = $loDifFecha->days + 1;
										if ($lnDiasIncapacidad !== $lnDiffDias) {
											$laRetornar['Valido'] = $lbRevisar = false;
											$laRetornar['Mensaje'] = 'Los días de incapacidad no concuerdan con el rango de fechas indicadas.';
											$laRetornar['Objeto'] = 'txtFechaDesde';
										}
									}
								}
							}
							break;

						case 'RET':		// Incapacidad Retroactiva
							$lcRetroactiva = $validacionDatos['Incapacidad']['IncapacidadRetroactiva'];
							$lcFecIniRetro = $validacionDatos['Incapacidad']['FechaIniRetroactiva'];
							$lcFecFinRetro = $validacionDatos['Incapacidad']['FechaFinRetroactiva'];
							if (strlen($lcRetroactiva) == 0) {
								$laRetornar['Valido'] = $lbRevisar = false;
								$laRetornar['Mensaje'] = 'Debe indicar el tipo de incapacidad retroactiva.';
								$laRetornar['Objeto'] = 'selIncapacidadRetroactiva';
							}
							if ($lbRevisar && strlen($lcFecFinRetro) == 0) {
								$laRetornar['Valido'] = $lbRevisar = false;
								$laRetornar['Mensaje'] = 'Debe indicar la fecha final de la incapacidad retroactiva.';
								$laRetornar['Objeto'] = 'selIncapacidadRetroactiva';
							}
							if ($lbRevisar && strlen($lcFecIniRetro) == 0) {
								$laRetornar['Valido'] = $lbRevisar = false;
								$laRetornar['Mensaje'] = 'Debe indicar la fecha inicial de la incapacidad retroactiva.';
								$laRetornar['Objeto'] = 'selIncapacidadRetroactiva';
							}
							break;

						default:
							$laRetornar['Valido'] = $lbRevisar = false;
							$laRetornar['Mensaje'] = 'Tipo de Incapacidad tiene un valor no permitido.';
							$laRetornar['Objeto'] = 'selTipoIncapacidad';
							break;
					}

					if ($lbRevisar) {
						$lbObligarIncapacidadHosp = !in_array($this->aIngreso['cCodVia'], ['01', '02']);
						$lcIncapacidadHosp = $validacionDatos['Incapacidad']['IncapacidadHospitalaria'];
						if ($lbObligarIncapacidadHosp && strlen($lcIncapacidadHosp) == 0) {
							$laRetornar['Valido'] = $lbRevisar = false;
							$laRetornar['Mensaje'] = 'Debe indicar si el paciente requiere incapacidad hospitalaria';
							$laRetornar['Objeto'] = 'selIncapacidadHospitalaria';
						}
					}
				}
			}
		}
		return $laRetornar;
	}

	public function fGuardarOrdenes($lcDatosGuardar)
	{
		$lnConsecutivoConsulta = Consecutivos::fCalcularConsecutivoConsulta($this->aIngreso, $this->cPrgCre);
		$this->nConsecOrden = Consecutivos::fCalcularConsecutivoOrden($this->aIngreso['cTipId'], $this->aIngreso['nNumId']);
		$lnConsecutivoCita = 0;
		$lcCiePrincipal = strtoupper($lcDatosGuardar['Diagnostico']);
		$lnConsecEvolucion = 0;
		$laDatosIngreso = [];
		$laDatosIngreso['nIngreso'] = $this->aIngreso['nIngreso'];
		$laDatosIngreso['nEntidad'] = $this->aIngreso['nEntidad'];
		$laDatosIngreso['cTipId'] = $this->aIngreso['cTipId'];
		$laDatosIngreso['nNumId'] = $this->aIngreso['nNumId'];
		$laDatosIngreso['cCodVia'] = $this->aIngreso['cCodVia'];
		$laDatosIngreso['nHistoria'] = $this->aIngreso['nHistoria'];

		return $this->GuardarOrdenesAmbulatorias($lcDatosGuardar['Ambulatorio'], $laDatosIngreso, $lnConsecutivoConsulta, $lnConsecutivoCita, $lcCiePrincipal, $lnConsecEvolucion, $this->cUsuCre, $this->cPrgCre, $this->cFecCre, $this->cHorCre, $this->cRegMed);
	}

	public function GuardarOrdenesAmbulatorias($detalleAmbulatorio, $laDatosIngreso, $tnConsecConsulta, $tnConsecCita, $tcCiePrincipal, $tnConsecEvolucion, $tcUsuarioCrea, $tcprogramaCrea, $tnFechaCrea, $tnHoraCrea, $tcRegMed)
	{
		if (!empty($detalleAmbulatorio)) {
			$this->nConsecConsulta = $tnConsecConsulta;
			$this->nConsecCita = $tnConsecCita;
			$this->nConsecEvol = $tnConsecEvolucion;
			$this->nIngreso = $laDatosIngreso['nIngreso'];
			$this->nEntidad = $laDatosIngreso['nEntidad'];
			$this->cTipoIden = $laDatosIngreso['cTipId'];
			$this->nNroIden = $laDatosIngreso['nNumId'];
			$this->ccodigoViaIngreso = $laDatosIngreso['cCodVia'];
			$this->cPlanIngreso = $detalleAmbulatorio['PlanCups'];
			$this->nNroHistoria = $laDatosIngreso['nHistoria'];
			$this->cRegMed = $tcRegMed;
			$this->cUsuCre = $tcUsuarioCrea;
			$this->cPrgCre = $tcprogramaCrea;
			$this->cFecCre = $tnFechaCrea;
			$this->cHorCre = $tnHoraCrea;
			$this->cEspecialidad = !empty($this->cEspecialidad) ? $this->cEspecialidad : (isset($_SESSION[HCW_NAME]) ? $_SESSION[HCW_NAME]->oUsuario->getEspecialidad() : '');
			$lcChrRec = chr(24);
			$lcChrItm = chr(25);
			$lcDatosGuardar = '';
			$this->nConsecOrden = $this->nConsecOrden > 0 ? $this->nConsecOrden : Consecutivos::fCalcularConsecutivoOrden($this->cTipoIden, $this->nNroIden);
			$lcTipoDieta = $lcDiasIncapacidad = $lcTipoPrioridad = $lcFechaDesdeIncapacidad = $lcFechaHastaIncapacidad = $lcOrigenIncapacidad = '';
			$lcIncapacidadHospitalaria = $lcRealizoFormulacion = $lcBrindoInformacion = $lcRiesgoSeleccion = '';

			if (!empty($detalleAmbulatorio['Procedimientos'])) {
				foreach ($detalleAmbulatorio['Procedimientos'] as $valProcedimientos) {
					$lcRiesgoSeleccion = trim($valProcedimientos['RIESGO'] ?? ' ');
				}
			}

			if (!empty($detalleAmbulatorio['Dieta']['tipoDieta'])) {
				$lcTipoDieta = $detalleAmbulatorio['Dieta']['tipoDieta'];
			}

			if (!empty($detalleAmbulatorio['RealizoFormulacion'])) {
				$lcRealizoFormulacion = $detalleAmbulatorio['RealizoFormulacion'];
			}
			if (!empty($detalleAmbulatorio['BrindoInformacion'])) {
				$lcBrindoInformacion = $detalleAmbulatorio['BrindoInformacion'];
			}
			if (!empty($detalleAmbulatorio['Incapacidad'])) {
				$lcDiasIncapacidad = $detalleAmbulatorio['Incapacidad']['DiasIncapacidad'];
				$lcFechaDesdeIncapacidad = mb_substr($detalleAmbulatorio['Incapacidad']['FechaDesde'], 0, 4) . mb_substr($detalleAmbulatorio['Incapacidad']['FechaDesde'], 5, 2) . mb_substr($detalleAmbulatorio['Incapacidad']['FechaDesde'], 8, 2) . '000000';
				$lcFechaHastaIncapacidad = mb_substr($detalleAmbulatorio['Incapacidad']['FechaHasta'], 0, 4) . mb_substr($detalleAmbulatorio['Incapacidad']['FechaHasta'], 5, 2) . mb_substr($detalleAmbulatorio['Incapacidad']['FechaHasta'], 8, 2) . '000000';
				$lcOrigenIncapacidad = str_pad($detalleAmbulatorio['Incapacidad']['OrigenIncapacidad'], 22, ' ', STR_PAD_RIGHT);
				$lcIncapacidadHospitalaria = (empty($detalleAmbulatorio['Incapacidad']['IncapacidadHospitalaria']) ? ' ' : ($detalleAmbulatorio['Incapacidad']['IncapacidadHospitalaria'] == 'S' ? '1' : '2'));
			}
			if (!empty($detalleAmbulatorio['Prioridad']['tipoPrioridad'])) {
				$lcTipoPrioridad = $detalleAmbulatorio['Prioridad']['tipoPrioridad'];
			}
			if (
				!empty(trim($lcTipoDieta)) || !empty(trim($lcDiasIncapacidad))
				|| !empty(trim($lcRealizoFormulacion)) || !empty(trim($lcBrindoInformacion))
				|| !empty($detalleAmbulatorio['Otras']) || !empty($detalleAmbulatorio['MedicamentosAmb'])
				|| !empty($detalleAmbulatorio['Procedimientos']) || !empty($detalleAmbulatorio['Interconsultas'])
				|| !empty(trim($detalleAmbulatorio['Recomendaciones']['RecomendacionGeneral']))
				|| !empty(trim($detalleAmbulatorio['Recomendaciones']['RecomendacionNutricional']))
			) {
				$lnIndice = 1;
				$lcDatosGuardar = $tnFechaCrea . $tnHoraCrea							//	14 - 014
					. str_pad(trim($lcTipoDieta), 15, ' ', STR_PAD_RIGHT)			//	15 - 029
					. str_pad(trim($lcDiasIncapacidad), 4, ' ', STR_PAD_LEFT)		//	04 - 033
					. str_pad(trim($tcCiePrincipal), 12, ' ', STR_PAD_RIGHT)			//	12 - 045
					. str_pad(trim($this->cPlanIngreso), 12, ' ', STR_PAD_RIGHT)		//	12 - 057
					. str_pad(trim($this->ccodigoViaIngreso), 2, ' ', STR_PAD_RIGHT)	//	02 - 059
					. str_pad(trim($lcTipoPrioridad), 5, ' ', STR_PAD_RIGHT)			//	05 - 064
					. str_pad(trim($lcRiesgoSeleccion), 1, ' ', STR_PAD_RIGHT)		//	01 - 065
					. $lcFechaDesdeIncapacidad										//	14 - 079
					. $lcFechaHastaIncapacidad										//	14 - 093
					. $lcOrigenIncapacidad											//	22 - 115
					. $lcIncapacidadHospitalaria										//	01 - 116
					. $lcRealizoFormulacion											//	02 - 118
					. $lcBrindoInformacion											//	02 - 120
					. str_pad(trim($this->cEspecialidad), 5, ' ', STR_PAD_RIGHT);	//	05 - 125
				$this->Guardar(trim($lcDatosGuardar), $lnIndice);
			}
			if (!empty(trim($detalleAmbulatorio['Dieta']['observacionDieta']))) {
				$lnIndice = 4;
				$lcDatosGuardar = $detalleAmbulatorio['Dieta']['observacionDieta'];
				$this->Guardar(trim($lcDatosGuardar), $lnIndice);
			}
			if (!empty($detalleAmbulatorio['Otras'])) {
				$lnIndice = 7;
				$lcDatosGuardar = $detalleAmbulatorio['Otras']['ObservacionesOtras'];
				$this->Guardar(trim($lcDatosGuardar), $lnIndice);
			}
			if (!empty($detalleAmbulatorio['MedicamentosAmb'])) {
				$lnIndice = 8;
				$lcDatosGuardar = $lcMedicamentos = '';
				$lnRegistro = 0;
				$lnCantidadCups = count($detalleAmbulatorio['MedicamentosAmb']);

				foreach ($detalleAmbulatorio['MedicamentosAmb'] as $valMedicamentosAmb) {
					$lnRegistro  = $lnRegistro + 1;
					$lcMedicamentos = $lcMedicamentos . (empty($lcMedicamentos) ? '' : $lcChrItm);
					$lcDescripcionMedicamento = trim($valMedicamentosAmb['CODIGO']) . $lcChrItm . trim($valMedicamentosAmb['MEDICA']);
					$lcDatosGuardar .= $lcMedicamentos . '0' . $lcChrItm . $lcDescripcionMedicamento . $lcChrItm . $valMedicamentosAmb['DOSIS']
						. $lcChrItm . $valMedicamentosAmb['TIPODCOD'] . $lcChrItm . $valMedicamentosAmb['FRECUENCIA'] . $lcChrItm . $valMedicamentosAmb['TIPOCODF']
						. $lcChrItm . $valMedicamentosAmb['DOSISDIA'] . $lcChrItm . $valMedicamentosAmb['TIPODCODDIA'] . $lcChrItm . $valMedicamentosAmb['TIEMPOTRATA']
						. $lcChrItm . $valMedicamentosAmb['TIPOTIEMTRAT'] . $lcChrItm . $valMedicamentosAmb['CANTID'] . $lcChrItm . ' ' . $valMedicamentosAmb['OBSERVA']
						. $lcChrItm . ($valMedicamentosAmb['PRESENTANP'] ?? '') . $lcChrItm . ($valMedicamentosAmb['CONCENTRANP'] ?? '')
						. $lcChrItm . ($valMedicamentosAmb['UNIDADNP'] ?? '') . $lcChrItm . ($valMedicamentosAmb['GRUPOTNP'] ?? '')
						. $lcChrItm . ($valMedicamentosAmb['TIEMPOTNP'] ?? '') . $lcChrItm . ($valMedicamentosAmb['INVIMANP'] ?? '')
						. $lcChrItm . ($valMedicamentosAmb['EFECTO'] ?? '') . $lcChrItm . ($valMedicamentosAmb['EFECTOS'] ?? '')
						. $lcChrItm . ($valMedicamentosAmb['PACIENTEINF'] ?? '') . $lcChrItm . ($valMedicamentosAmb['BIBLIOGRAFIA'] ?? '')
						. $lcChrItm . ($valMedicamentosAmb['CODIGOP'] ?? '') . $lcChrItm . trim(explode('-', empty(trim($valMedicamentosAmb['MEDICAP'] ?? '')) ? '-' : $valMedicamentosAmb['MEDICAP'])[1])
						. $lcChrItm . ($valMedicamentosAmb['DOSISP'] ?? '') . $lcChrItm . ($valMedicamentosAmb['TIPODOSISP'] ?? '')
						. $lcChrItm . ($valMedicamentosAmb['FRECUENCIAP'] ?? '') . $lcChrItm . ($valMedicamentosAmb['TFRECUENCIAP'] ?? '')
						. $lcChrItm . ($valMedicamentosAmb['DOSISDIAP'] ?? '') . $lcChrItm . ($valMedicamentosAmb['TIPODOSISDIAP'] ?? '')
						. $lcChrItm . ($valMedicamentosAmb['TRATAMIENTOP'] ?? '') . $lcChrItm . ($valMedicamentosAmb['TIPOTRATAMP'] ?? '')
						. $lcChrItm . ($valMedicamentosAmb['CANTIDADP'] ?? '') . $lcChrItm . ' ' . $lcChrItm . ($valMedicamentosAmb['PRESENTAP'] ?? '')
						. $lcChrItm . ($valMedicamentosAmb['CONCENTRAP'] ?? '') . $lcChrItm . ($valMedicamentosAmb['UNIDADP'] ?? '')
						. $lcChrItm . ' ' . $lcChrItm . ' ' . $lcChrItm . ' ' . $lcChrItm . ' ' . $lcChrItm . ' '
						. $lcChrItm . ' ' . $lcChrItm . ' ' . $lcChrItm . ($valMedicamentosAmb['RESUMENNP'] ?? '')
						. $lcChrItm . ($valMedicamentosAmb['NOPOS'] ?? '0') . $lcChrItm . ' ' . $lcChrItm . ($valMedicamentosAmb['RIESGOINP'] ?? '')
						. $lcChrItm . ($valMedicamentosAmb['VIACOD'] ?? '') . $lcChrItm . ($valMedicamentosAmb['VIAP'] ?? '')
						. $lcChrItm . ($valMedicamentosAmb['CANTIDADTRAT'] ?? '')
						. ($lnRegistro == $lnCantidadCups ? $lcChrItm : $lcChrRec);
				}

				if ($lnIndice === 8) {
					$this->laDatosGuardarQr['dataAO'] = [
						"nIngreso" => $this->nIngreso,
						"cTipDocPac" => $this->cTipoIden,
						"nNumDocPac" => $this->nNroIden,
						"cRegMedico" => $this->cRegMed,
						"cTipoDocum" => "5000",
						"cTipoProgr" => "ORDA01A",
						"tFechaHora" => date('Y-m-d H:i:s', strtotime($tnFechaCrea . ' ' . $tnHoraCrea)),
						"nConsecCita" => $this->nConsecCita,
						"nConsecCons" => $this->nConsecConsulta,
						"nConsecEvol" => $lnIndice,
						"nConsecDoc" => $this->nConsecOrden,
						"cCUP" => "",
						"cCodVia" => $this->ccodigoViaIngreso,
						"cSecHab" => ""
					];

					$this->GuardarDatosQr($this->generarUUID(), $lnIndice, json_encode($this->laDatosGuardarQr['dataAO']));
				}

				$this->Guardar(trim($lcDatosGuardar), $lnIndice);
			}

			if (!empty($detalleAmbulatorio['Procedimientos'])) {
				$lnIndice = 9;
				$lcDatosGuardar = $lcDatosCups = $lcCodigoCups = $lcDatosInsumos = '';
				$lnRegistro = 0;
				$lnCantidadCups = count($detalleAmbulatorio['Procedimientos']);

				foreach ($detalleAmbulatorio['Procedimientos'] as $valProcedimientos) {
					$lcRegistros = $lcChrItm . trim($valProcedimientos['SOLICITADO'] ?? ' ') . $lcChrItm . trim($valProcedimientos['TIPOR'] ?? '0')
						. $lcChrItm . trim($valProcedimientos['PACIENTE'] ?? '') . $lcChrItm . trim($valProcedimientos['BIBLIOGRAFIA'] ?? ' ')
						. $lcChrItm . trim($valProcedimientos['CODIGOPOS'] ?? ' ') . $lcChrItm . trim($valProcedimientos['PROCEDIMPOS'] ?? ' ')
						. $lcChrItm . ' ' . $lcChrItm . trim($valProcedimientos['CANTIDADPOS'] ?? '0') . $lcChrItm . ' ' . $lcChrItm . '0'
						. $lcChrItm . '1' . $lcChrItm . ' ' . $lcChrItm . trim($valProcedimientos['RESUMEN'] ?? '')
						. $lcChrItm . trim($valProcedimientos['RESPUESTA'] ?? '')
						. $lcChrItm . trim($valProcedimientos['OBJETIVO'] ?? ' ')
						. $lcChrItm . 'P' . $lcChrItm . trim($valProcedimientos['POS'] ?? '0') . $lcChrItm;
					$lnRegistro  = $lnRegistro + 1;
					$lcCodigoCups = $valProcedimientos['CODIGO'];
					$lcDescripcionCups = trim($valProcedimientos['DESCRIPCION']);
					$lcObservacionCups = (empty($valProcedimientos['OBSERVACION']) ? ' ' : trim($valProcedimientos['OBSERVACION']));
					$lcCantidadCups = trim($valProcedimientos['CANTIDAD']);
					$laEspecialidad = $this->oDb->select('ESPCUP')->from('RIACUP')->where(['CODCUP' => $valProcedimientos['CODIGO'],])->get("array");
					$lcEspecialidad = is_array($laEspecialidad) ? ($laEspecialidad['ESPCUP'] ?? '') : '';
					$lcDatosCups .= '0' . $lcChrItm . $lcCodigoCups . $lcChrItm . $lcDescripcionCups . $lcChrItm
						. $lcObservacionCups . $lcChrItm . $lcCantidadCups . $lcRegistros . $lcEspecialidad . $lcChrItm . trim($valProcedimientos['RIESGO'] ?? '') . ($lnRegistro == $lnCantidadCups ? $lcChrItm : $lcChrRec);
				}

				if ($lnIndice === 9) {
					$this->laDatosGuardarQr['dataAO'] = [
						"nIngreso" => $this->nIngreso,
						"cTipDocPac" => $this->cTipoIden,
						"nNumDocPac" => $this->nNroIden,
						"cRegMedico" => $this->cRegMed,
						"cTipoDocum" => "5000",
						"cTipoProgr" => "ORDA01A",
						"tFechaHora" => date('Y-m-d H:i:s', strtotime($tnFechaCrea . ' ' . $tnHoraCrea)),
						"nConsecCita" => $this->nConsecCita,
						"nConsecCons" => $this->nConsecConsulta,
						"nConsecEvol" => $lnIndice,
						"nConsecDoc" => $this->nConsecOrden,
						"cCUP" => "",
						"cCodVia" => $this->ccodigoViaIngreso,
						"cSecHab" => ""
					];

					$this->GuardarDatosQr($this->generarUUID(), $lnIndice, json_encode($this->laDatosGuardarQr['dataAO']));
				}

				$this->Guardar(trim($lcDatosCups), $lnIndice);
				unset($laEspecialidad);
			}

			if (!empty($detalleAmbulatorio['Interconsultas'])) {
				$lnIndice = 10;
				$lcDatosGuardar = '';
				$lnRegistro = 0;
				$lnCantidadRegistros = count($detalleAmbulatorio['Interconsultas']);
				foreach ($detalleAmbulatorio['Interconsultas'] as $valInterconsultas) {
					$lnRegistro  = $lnRegistro + 1;
					$lcCodigoInterconsulta = trim($valInterconsultas['CODIGO']);
					$lcDescripcion = (empty(trim($valInterconsultas['OBSERVACION'])) ? ' ' : trim($valInterconsultas['OBSERVACION']));
					$lcDatosGuardar .= $valInterconsultas['CODIGO'] . $lcChrItm . $lcDescripcion . $lcChrItm . $tcCiePrincipal
						. ($lnRegistro == $lnCantidadRegistros ? '' : $lcChrRec);
				}

				if ($lnIndice === 10) {
					$this->laDatosGuardarQr['dataAO'] = [
						"nIngreso" => $this->nIngreso,
						"cTipDocPac" => $this->cTipoIden,
						"nNumDocPac" => $this->nNroIden,
						"cRegMedico" => $this->cRegMed,
						"cTipoDocum" => "5000",
						"cTipoProgr" => "ORDA01A",
						"tFechaHora" => date('Y-m-d H:i:s', strtotime($tnFechaCrea . ' ' . $tnHoraCrea)),
						"nConsecCita" => $this->nConsecCita,
						"nConsecCons" => $this->nConsecConsulta,
						"nConsecEvol" => $lnIndice,
						"nConsecDoc" => $this->nConsecOrden,
						"cCUP" => "",
						"cCodVia" => $this->ccodigoViaIngreso,
						"cSecHab" => ""
					];

					$this->GuardarDatosQr($this->generarUUID(), $lnIndice, json_encode($this->laDatosGuardarQr['dataAO']));
				}

				$this->Guardar(trim($lcDatosGuardar), $lnIndice);
			}
			if (!empty($detalleAmbulatorio['Recomendaciones'])) {
				if (!empty(trim($detalleAmbulatorio['Recomendaciones']['RecomendacionGeneral']))) {
					$lnIndice = 11;
					$lcDatosGuardar = $detalleAmbulatorio['Recomendaciones']['RecomendacionGeneral'];
					$this->Guardar(trim($lcDatosGuardar), $lnIndice);
				}
				if (!empty(trim($detalleAmbulatorio['Recomendaciones']['RecomendacionNutricional']))) {
					$lnIndice = 12;
					$lcDatosGuardar = $detalleAmbulatorio['Recomendaciones']['RecomendacionNutricional'];
					$this->Guardar(trim($lcDatosGuardar), $lnIndice);
				}
			}
			if (!empty($detalleAmbulatorio['Insumos'])) {
				$lnIndice = 13;
				$lcDatosGuardar = $detalleAmbulatorio['Insumos'];
				$this->Guardar(trim($lcDatosGuardar), $lnIndice);
			}
			if (!empty($detalleAmbulatorio['Incapacidad'])) {
				$lcTipoInc = $detalleAmbulatorio['Incapacidad']['TipoIncapacidad'];
				if (strlen($lcTipoInc) > 0) {
					$lnIndice = 14;
					$lcDatosGuardar = json_encode([
						'tpi' => $lcTipoInc,
						'mdp' => $detalleAmbulatorio['Incapacidad']['ModalidadPrestacion'],
						'dxp' => $tcCiePrincipal,
						'dxr' => $detalleAmbulatorio['Incapacidad']['cCodigoCieOrdAmbR'],
						'fin' => $detalleAmbulatorio['Incapacidad']['FechaDesde'],
						'ffi' => $detalleAmbulatorio['Incapacidad']['FechaHasta'],
						'dia' => $detalleAmbulatorio['Incapacidad']['DiasIncapacidad'],
						'pro' => $detalleAmbulatorio['Incapacidad']['Prorroga'],
						'ori' => $detalleAmbulatorio['Incapacidad']['OrigenIncapacidad'],
						'cau' => $detalleAmbulatorio['Incapacidad']['CausaAtencion'],
						'rtr' => $detalleAmbulatorio['Incapacidad']['IncapacidadRetroactiva'],
						'fir' => $detalleAmbulatorio['Incapacidad']['FechaIniRetroactiva'],
						'ffr' => $detalleAmbulatorio['Incapacidad']['FechaFinRetroactiva'],
						'hos' => $detalleAmbulatorio['Incapacidad']['IncapacidadHospitalaria'],
					]);


					if ($lnIndice === 14) {
						$lnIndice = 0;
						$this->laDatosGuardarQr['dataAO'] = [
							"nIngreso" => $this->nIngreso,
							"cTipDocPac" => $this->cTipoIden,
							"nNumDocPac" => $this->nNroIden,
							"cRegMedico" => $this->cRegMed,
							"cTipoDocum" => "5000",
							"cTipoProgr" => "ORDA01A",
							"tFechaHora" => date('Y-m-d H:i:s', strtotime($tnFechaCrea . ' ' . $tnHoraCrea)),
							"nConsecCita" => $this->nConsecCita,
							"nConsecCons" => $this->nConsecConsulta,
							"nConsecEvol" => $lnIndice,
							"nConsecDoc" => $this->nConsecOrden,
							"cCUP" => "",
							"cCodVia" => $this->ccodigoViaIngreso,
							"cSecHab" => ""
						];

						$this->GuardarDatosQr($this->generarUUID(), $lnIndice, json_encode($this->laDatosGuardarQr['dataAO']));
					}
					$lnIndice = 14;
					$this->Guardar($lcDatosGuardar, $lnIndice);
				}
			}
			if (!empty($detalleAmbulatorio['Incapacidad']['ObservacionesIncapacidad'])) {
				$lnIndice = 6;
				$lcDatosGuardar = $detalleAmbulatorio['Incapacidad']['ObservacionesIncapacidad'];
				$this->Guardar(trim($lcDatosGuardar), $lnIndice);
			}

			$this->aError['dataOA'] = [
				'nIngreso'		=> $this->nIngreso,
				'cTipDocPac'	=> $this->cTipoIden,
				'nNumDocPac'	=> $this->nNroIden,
				'cRegMedico'	=> $this->cRegMed,
				'cTipoDocum'	=> '5000',
				'cTipoProgr'	=> 'ORDA01A',
				'tFechaHora'	=> date('Y-m-d H:i:s', strtotime($tnFechaCrea . $tnHoraCrea)),
				'nConsecCita'	=> $this->nConsecCita,
				'nConsecCons'	=> $this->nConsecConsulta,
				'nConsecEvol'	=> $this->nConsecEvol,
				'nConsecDoc'	=> $this->nConsecOrden,
				'cCUP'			=> '',
				'cCodVia'		=> $this->ccodigoViaIngreso,
				'cSecHab'		=> '',
			];
		}

		return $this->aError;
	}

	public function Guardar($tcDatosGuardar, $tcIndice)
	{
		if (isset($this->oDb)) {
			if (!empty(trim($tcDatosGuardar))) {
				$tnLinea = 0;
				$lnCantidadCaracteres = mb_strlen($tcDatosGuardar);
				$lnLongitud = 220;
				$lnLinea = $tnLinea == 0 ? 1 : $tnLinea;
				$lnInicio = 0;
				$lnLines = ceil($lnCantidadCaracteres / $lnLongitud) == 0 ? 1 : ceil($lnCantidadCaracteres / $lnLongitud);
				$lnLines = intval($lnLines + $lnLinea);
				for ($i = 1; $i < $lnLines; $i++) {
					$lcDescrip = mb_substr($tcDatosGuardar, $lnInicio, $lnLongitud);
					$lnInicio = $lnInicio + $lnLongitud;
					$laErrores = [];
					$lcTablaCie = 'ORDAMB';
					$laData = [
						'TIDORA' => $this->cTipoIden,
						'NIDORA' => $this->nNroIden,
						'INGORA' => $this->nIngreso,
						'CORORA' => $this->nConsecOrden,
						'INDORA' => $tcIndice,
						'SUBORA' => $this->nNroHistoria,
						'CODORA' => 0,
						'SHIORA' => '',
						'PROORA' => '',
						'CLNORA' => $i,
						'CEVORA' => $this->nConsecEvol,
						'PLAORA' => $this->cPlanIngreso,
						'VIAORA' => $this->ccodigoViaIngreso,
						'DESORA' => $lcDescrip,
						'CCIORA' => $this->nConsecCita,
						'CCOORA' => $this->nConsecConsulta,
						'USRORA' => $this->cUsuCre,
						'PGMORA' => $this->cPrgCre,
						'FECORA' => $this->cFecCre,
						'HORORA' => $this->cHorCre,
					];


					try {
						$this->oDb->from($lcTablaCie)->insertar($laData);
					} catch (\Exception $loError) {
						$laErrores[] = $loError->getMessage();
					} catch (\PDOException $loError) {
						$laErrores[] = $loError->getMessage();
					}
				}
			}
		}
	}

	private function generarUUID()
	{
		return uniqid();
	}

	public function GuardarDatosQr($uuid, $nconseEvol, $json)
	{
		if (!empty($this->oDb)) {
			$lcTablaQr = 'CODIGOSVALIDADOC';
			$laErrores = [];

			$tnLinea = 0;
			$lnCantidadCaracteres = mb_strlen($json);
			$lnLongitud = 500;
			$lnLinea = $tnLinea == 0 ? 1 : $tnLinea;
			$lnInicio = 0;
			$lnLines = ceil($lnCantidadCaracteres / $lnLongitud) == 0 ? 1 : ceil($lnCantidadCaracteres / $lnLongitud);
			$lnLines = intval($lnLines + $lnLinea);

			for ($i = 1; $i < $lnLines; $i++) {
				$lcDetalle = mb_substr($json, $lnInicio, $lnLongitud);
				$lnInicio = $lnInicio + $lnLongitud;

				$laDetallesQr = [
					'CODIGO' => $uuid,
					'INGRESO' => $this->nIngreso,
					'CONSECUTIVO_DOC' => $this->nConsecOrden,
					'CONSECUTIVO_EVOL' => $nconseEvol,
					'TIPO_DOC' => $this->cTipoIden,
					'DETALLE' => $lcDetalle,
					'USUARIO_CREA' => $this->cUsuCre,
					'PROGRAMA_CREA' => $this->cPrgCre
				];

				try {
					$this->oDb->from($lcTablaQr)->insertar($laDetallesQr);
				} catch (\Exception $loError) {
					$laErrores[] = $loError->getMessage();
				} catch (\PDOException $loError) {
					$laErrores[] = $loError->getMessage();
				}
			}

			return $laErrores;
		}
	}

	/*
	 *	Retorna array con lista de nombres y lista de índices de órdenes ambulatorias para imprimir cuando se selecciona TODO
	 */
	public function consultaItemsTodo()
	{
		$loTabMae = $this->oDb->obtenerTabMae('DE2TMA,OP5TMA', 'HCPARAM', ['CL1TMA' => 'PRINT', 'CL2TMA' => 'TODO', 'ESTTMA' => '']);
		return [
			'nombres' => trim(AplicacionFunciones::getValue($loTabMae, 'DE2TMA', 'FORMULA,NOPOS,PROCEDIMIENTOS,INSUMOS,INTERCONSULTAS,DIETA,INCAPACIDAD,OTRAS,TODO')),
			'indices' => trim(AplicacionFunciones::getValue($loTabMae, 'OP5TMA', '1,4,6,7,8,9,10,13,91,92')),
		];
	}

	/*
	 *	Consulta que contiene una orden ambulatoria
	 */
	public function consultaContenido($tnIngreso, $tnCnsOrden)
	{
		$laReturn = [
			'MEDIC' => false,
			'CTCMD' => false,
			'PROCE' => false,
			'CTCPR' => false,
			'INSUM' => false,
			'INSUN' => false,
			'INTER' => false,
			'DIETA' => false,
			'INCAP' => false,
			'OTRAS' => false,
			'RECOM' => false,
		];
		$laWhere = [
			'OA.INGORA' => $tnIngreso,
			'OA.CORORA' => $tnCnsOrden,
		];
		$it = chr(25);

		$laDatos = $this->oDb
			->sum('CASE WHEN INDORA=8 THEN 1 ELSE 0 END', 'MEDIC')
			->sum("CASE WHEN INDORA=9 AND DESORA LIKE '%{$it}P{$it}%' THEN 1 ELSE 0 END", 'PROCE')
			->sum("CASE WHEN INDORA=9 AND DESORA LIKE '%{$it}I{$it}%' THEN 1 ELSE 0 END", 'INSUM')
			->sum('CASE WHEN INDORA=10 THEN 1 ELSE 0 END', 'INTER')
			->sum('CASE WHEN INDORA=4 THEN 1 ELSE 0 END', 'DIETA')
			->sum("CASE WHEN (INDORA=14 OR (INDORA=1 AND (TRIM(SUBSTR(DESORA,30,4)) NOT IN ('','0')) )) THEN 1 ELSE 0 END", 'INCAP')
			->sum('CASE WHEN INDORA=7 THEN 1 ELSE 0 END', 'OTRAS')
			->sum('CASE WHEN INDORA IN (11,12) THEN 1 ELSE 0 END', 'RECOM')
			->sum('CASE WHEN INDORA=13 THEN 1 ELSE 0 END', 'INSUN')
			->from('ORDAMB AS OA')
			->where($laWhere)
			->getAll('array');
		if (is_array($laDatos)) {
			foreach ($laReturn as $laClave => $lbValor) {
				$laReturn[$laClave] = ($laDatos[0][$laClave] ?? 0) > 0;
			}
			// CTC Medicamentos
			if ($laReturn['MEDIC']) {
				$laDatos = $this->oDb
					->select('DESORA')
					->from('ORDAMB AS OA')
					->where($laWhere + ['OA.INDORA' => 8])
					->orderBy('CLNORA')
					->getAll('array');
				if (is_array($laDatos)) {
					$lcTexto = '';
					foreach ($laDatos as $laDato) {
						$lcTexto .= $laDato['DESORA'];
					}
					$laElementos = explode(chr(24), trim($lcTexto));
					foreach ($laElementos as $lcElemento) {
						$laCampos = explode(chr(25), $lcElemento);
						if (($laCampos[46] ?? '') == '1' && !empty($laCampos[45] ?? '')) {
							$laReturn['CTCMD'] = true;
							break;
						}
					}
				}
			}
			// CTC Procedimientos
			if ($laReturn['PROCE']) {
				$laDatos = $this->oDb
					->select('DESORA')
					->from('ORDAMB AS OA')
					->where($laWhere + ['OA.INDORA' => 9])
					->orderBy('CLNORA')
					->getAll('array');
				if (is_array($laDatos)) {
					$lcTexto = '';
					foreach ($laDatos as $laDato) {
						$lcTexto .= $laDato['DESORA'];
					}
					$laElementos = explode(chr(24), trim($lcTexto));
					foreach ($laElementos as $lcElemento) {
						if (!empty($lcElemento)) {
							$laCampos = explode(chr(25), $lcElemento);
							if (($laCampos[20] ?? '') == 'P' && ($laCampos[21] ?? '') == '1' && !empty($laCampos[17] ?? '')) {
								$laReturn['CTCPR'] = true;
								break;
							}
						}
					}
				}
			}
		}
		return $laReturn;
	}

	public function ConsultaIncapacidades($tcTipoDoc, $tnNumDoc)
	{
		$laRetorno = [];
		$laDatos = $this->oDb
			->select('INGORA INGRESO, CORORA CONSECUTIVO, INDORA INDICE, TRIM(DESORA) DESCRIPCION, USRORA USUARIO, FECORA FECHA, HORORA HORA')
			->from('ORDAMB')
			// ->where(['TIDORA'=>$tcTipoDoc, 'NIDORA'=>$tnNumDoc])
			->where("TIDORA='$tcTipoDoc' AND NIDORA=$tnNumDoc")
			->where("((INDORA=1 AND (TRIM(SUBSTR(DESORA,30,4)) NOT IN ('','0')) ) OR INDORA=14)")
			->getAll('array');

		if ($this->oDb->numRows() > 0) {
			foreach ($laDatos as $laDato) {
				$lnConsec = $laDato['CONSECUTIVO'];
				if ($laDato['INDICE'] == 1) {
					$lcTipoInc = 'AMB';
					$lnFechaIni = substr($laDato['DESCRIPCION'], 65, 8);
					$lnFechaFin = substr($laDato['DESCRIPCION'], 79, 8);
					$lnDias = substr($laDato['DESCRIPCION'], 29, 4);
				} else {
					$laIncp = json_decode($laDato['DESCRIPCION'], true);
					$lcTipoInc = $laIncp['tpi'];
					if (strlen($laIncp['rtr']) > 0) {
						$lnFechaIni = intval(str_replace('-', '', $laIncp['fir']));
						$lnFechaFin = intval(str_replace('-', '', $laIncp['ffr']));
						$ldFechaIni = strtotime($laIncp['fir']);
						$ldFechaFin = strtotime($laIncp['ffr']);
						$lnDias = intval(round(($ldFechaFin - $ldFechaIni) / 86400, 0, PHP_ROUND_HALF_UP)) + 1;
					} else {
						$lnFechaIni = intval(str_replace('-', '', $laIncp['fin']));
						$lnFechaFin = intval(str_replace('-', '', $laIncp['ffi']));
						$lnDias = intval($laIncp['dia']);
					}
				}
				$laRetorno[$lnConsec] = [
					'ingreso' => $laDato['INGRESO'],
					'cons' => $laDato['CONSECUTIVO'],
					'tipo' => $lcTipoInc,
					'fechaini' => $lnFechaIni,
					'fechafin' => $lnFechaFin,
					'dias' => $lnDias,
				];
			}
		}
		return $laRetorno;
	}


	public function ListadoInterconsultas()
	{
		return $this->aTablaInterconsulta;
	}

	public function ListadoProcedimientos()
	{
		return $this->aTablaProcedimientos;
	}

	public function ListadoInsumos()
	{
		return $this->aTablaInsumos;
	}

	public function ListadoOrdenesAmbulatoriasPaciente()
	{
		return $this->aListaOrdenesAmbulatorias;
	}

	public function ConsultaUltimoIngresoAmb()
	{
		return $this->aUltimoIngresoAmb;
	}

	public function nConsecOrden()
	{
		return $this->nConsecOrden;
	}

	public function setIngreso($taIngreso)
	{
		return $this->aIngreso = $taIngreso;
	}

	public function setDxPrincipal($tcDxPrincipal)
	{
		return $this->cDxPrincipal = $tcDxPrincipal;
	}
}
