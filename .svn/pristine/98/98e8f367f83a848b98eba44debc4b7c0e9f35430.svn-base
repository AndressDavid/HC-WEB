<?php
namespace NUCLEO;

use ApiAutenticacion;

require_once __DIR__ . '/class.Db.php';
require_once __DIR__ . '/class.AplicacionFunciones.php';
require_once __DIR__ . '/class.ApiAutenticacion.php';
require_once __DIR__ . '/class.ApiConsentimientoInformado.php';


class ListaDocumentos
{
	protected $nIngreso = 0;
	protected $cTipoId = '';
	protected $nNumeroId = 0;
	protected $aCamporOrd = ['fecha', 'descrip'];
	protected $aOrden = [SORT_ASC, SORT_ASC];
	protected $aIngresos = [];
	protected $oDb = null;
	protected $nFechaIniHCppal = 0;
	protected $nFechaInicioWSLAB = 0;
	protected $cTipoFechaEnf = '';
	protected $aLstProg = [];
	protected $aCupsGlucGas = [];
	protected $aCupsGlucome = [];
	protected $aCupsHisNutric = [];
	protected $aDescTipos = [];
	protected $aSeccHab = [];
	protected $aVia = [];
	public $bLibro24hr = false;
	protected $nFecHora24 = 0;
	protected $nHoras24 = 24;
	protected $cTodosOrdAmb = '';
	protected $cSinIngreso = 'Sin_Ingreso';
	protected $aUltimoIngreso = [];


	/*	Evitar permisos de usuario para consultar documentos */
	protected $bEvitarPermisos = false;

	/*	Arreglo con los documentos agrupados por Ingreso, ordenados por fecha, descripción */
	protected $aDocumentos = [];

	/*	Arreglo con los tipos de documento agrupados por Ingreso, para tree */
	protected $aIngresosTipos = [];

	/*	Arreglo con las funciones que deben ejecutarse para obtener la lista de documentos */
	protected $aFunciones = [
			'consultarTriage',
			'consultarHistoria',
			'consultarHcUrg',
			'consultarHcHos',
			'consultarHcCex',
			'consultarEvoluciones',
			'consultarEvolucionesFisio',
			'consultarProcedimientos',
			'consultarNotasAnestesia',
			'consultarIngresoUCV',
			'consultarResumenAdm',
			'consultarEpicrisis',
			'consultarTrasladosPacientes',
			'consultarOrdenesAmbulatorias',
			'consultarEscalaNihss',
			'consultarEnfNotas',
			'consultarEnfAdministraMed',
			'consultarEnfBalanceLiq',
			'consultarEnfCtrlNeuro',
			'consultarEnfRiesgoCaida',
			'consultarEnfRiesgoFuga',
			'consultarEnfEscalaNas',
			'consultarEnfEpidemiologia',
			'consultarEnfSensorica',
			'consultarSignosVitales',
			'consultarCambioDatos',
			'consultarAdjuntos',
			'consultarConsentimiento',
			//'consultarEnfCtrlGlucom', deshabilitado el elemento LIBROHC - TIPOCHC - 2940
			//'consultarNotasDocumentos',
		];

	/*	Arreglo con los campos que se retornan */
	protected $aCampos = ['tipoDoc','tipoPrg','codCup','cnsDoc','cnsCita','cnsCons','cnsEvo','fecha','medRegMd','medApell','medNombr','descrip','via'];



	/*
	 *	Constructor de la clase
	 *	Alcance public
	 */
	public function __construct($tbEvitarPermisos=false)
	{
		global $goDb;
		$this->oDb = $goDb;
		$this->bEvitarPermisos = $tbEvitarPermisos;
	}

	/*
	 *	Consulta de datos
	 *	Alcance public
	 *
	 *	@param int $tnIngreso:  Número de ingreso
	 *	@param string $tcTipoId: Tipo de identificación del paciente (opcional)
	 *	@param int $tcNumeroId: Número de identificación del paciente (opcional)
	 *	@param array|string $taCamporOrd: Campos por los que se debe ordenar
	 *	@param array|string $taOrden: Dirección de orden que se debe usar (SORT_ASC o SORT_DESC)
	 *	@param boolean $tbObtenerDocs: si es true obtiene los documentos del ingreso o documento
	 *	@param boolean $tbObtenerIngresos: obtener lista de ingresos del paciente
	 */
	public function cargarDatos($tnIngreso=0, $tcTipoId='', $tcNumeroId=0, $taCamporOrd='', $taOrden='', $tbObtenerDocs=true, $tbObtenerIngresos=true)
	{
		if ($tnIngreso==0 && $tcNumeroId==0) {
			return false;
		}
		$this->nIngreso = $tnIngreso;
		$this->cTipoId = $tcTipoId;
		$this->nNumeroId = $tcNumeroId;

		$this->aOrden = $taOrden=='' ? $this->aOrden : $taOrden;
		$this->aCamporOrd = $taCamporOrd=='' ? $this->aCamporOrd : $taCamporOrd;

		// Obtener documento del paciente
		if ($this->nIngreso > 0) {
			$aDatos = $this->oDb
				->select('TIDING,NIDING,FEIING,HORING')
				->from('RIAING')
				->where(['NIGING'=>$this->nIngreso])
				->get('array');
			$this->cTipoId = $aDatos['TIDING']??'';
			$this->nNumeroId = $aDatos['NIDING']??0;

			if (!$tbObtenerIngresos) {
				$this->aIngresosTipos[ $this->nIngreso ] = [];
				$this->aDocumentos[ $this->nIngreso ] = [];
				$this->aIngresos[ $this->nIngreso ] = [ 'fechaing'=>$aDatos['FEIING']??0, 'horaing'=>$aDatos['HORING']??0, ] ;
			}

		// No hay ingreso y faltan datos del documento
		} elseif ($this->cTipoId=='' || $this->nNumeroId==0) {
			// error indicando datos del paciente
			return false;
		}

		$this->obtenerPropiedades();

		// Lista de ingresos del paciente
		if ($tbObtenerIngresos || $this->nIngreso==0) {
			$this->obtenerIngresos();
		}

		// Consultar documentos
		if ($tbObtenerDocs) {
			if ($this->nIngreso===$this->cSinIngreso) {
				$this->obtenerLista($this->cSinIngreso);
			} elseif ($this->nIngreso>0) {
				$lbObtener = $this->bEvitarPermisos ? true : ( defined('HCW_NAME') ? $this->puedeVerDocsIngreso($this->nIngreso) : false);
				if ($lbObtener) {
					$this->obtenerLista($this->nIngreso);
				} else {
					return false;
				}
			} else {
				$this->obtenerTodos();
			}
		}

		return true;
	}


	/*
	 *	Obtiene la lista de ingresos a partir del tipo y número de identificación
	 *	Alcance private
	 */
	private function obtenerIngresos()
	{
		$laNotas = $this->oDb
			->select('COUNT(*) CUENTA')
			->from('notacl AS n')
			->where(['n.cnlnot'=>1, 'n.tidnot'=>$this->cTipoId, 'n.idenot'=>$this->nNumeroId])
			->getAll('array');
		$lbHayNotas = $this->oDb->numRows()>0;

		$aDatos = $this->oDb
			->select('NIGING,FEIING,HORING')
			->from('RIAING')
			->where(['TIDING'=>$this->cTipoId,'NIDING'=>$this->nNumeroId])
			->orderBy('NIGING')
			->getAll('array');
		if ($lbHayNotas) {
			$aDatos[] = ['NIGING'=>$this->cSinIngreso,'FEIING'=>0,'HORING'=>0];
		}
		$lnIngreso=0;
		foreach ($aDatos as $aDato) {
			$this->aIngresosTipos[$aDato['NIGING']] = [];
			$this->aDocumentos[$aDato['NIGING']] = [];
			$this->aIngresos[$aDato['NIGING']] = ['fechaing'=>$aDato['FEIING'], 'horaing'=>$aDato['HORING']] ;
			if($lnIngreso<$aDato['NIGING'] && $aDato['NIGING']!=='Sin_Ingreso'){
				$this->aUltimoIngreso = ['nIngreso'=>$aDato['NIGING'], 'nIngresoFecha'=>$aDato['FEIING'], 'nIngresoHora'=>$aDato['HORING']] ;
			}
		}
	}


	/*
	 *	Obtiene lista de documentos para todos los ingresos
	 *	Alcance public
	 */
	public function obtenerTodos()
	{
		$laIngresos = array_keys($this->aIngresos);
		foreach($laIngresos as $lnIngreso) {
			$lbObtener = $this->bEvitarPermisos ? true : $this->puedeVerDocsIngreso($lnIngreso);
			if ($lbObtener) {
				$this->obtenerLista($lnIngreso);
			}
		}
		$this->obtenerLista($this->cSinIngreso);
	}


	/*
	 *	Obtener la fecha hora menos 24 horas
	 */
	private function obtenerFecha24()
	{
		$laDatos = $this->oDb
			->select("REPLACE(REPLACE(SUBSTR(CHAR(NOW()-{$this->nHoras24} HOURS),0,20),'.',''),'-','') AS AHORA")
			->from('SYSIBM.SYSDUMMY1')
			->getAll('array');

		return $laDatos[0]['AHORA'];
	}


	/*
	 *	Obtiene lista de documentos para un ingreso
	 *	Alcance private
	 *
	 *	@param int $tnIngreso: Número de ingreso
	 */
	private function obtenerLista($tnIngreso)
	{
		$this->nFecHora24 = $this->bLibro24hr ? $this->obtenerFecha24() : 0;

		if (empty($this->aDocumentos[$tnIngreso])) {
			if ($tnIngreso===$this->cSinIngreso) {
				$this->consultarNotasAclaratorias();

			} else {
				$this->obtenerVia($tnIngreso);
				$this->obtenerHabitaciones($tnIngreso);

				// Consultar los documentos
				foreach ($this->aFunciones as $cFuncion) {
					call_user_func_array([$this, $cFuncion], [$tnIngreso]);
				}
			}
			$this->ordenarDocIngreso($tnIngreso);
		}
	}


	/*
	 *	Ordenar la lista de documentos para un ingreso
	 */
	public function ordenarDocIngreso($tnIngreso)
	{
		if(isset($this->aDocumentos[$tnIngreso])){
			if(is_array($this->aDocumentos[$tnIngreso])){
				// Ordenar la lista
				AplicacionFunciones::ordenarArrayMulti($this->aDocumentos[$tnIngreso], $this->aCamporOrd, $this->aOrden);
				$this->formatoFechaHora($tnIngreso);
			} else {
				unset($this->aDocumentos[$tnIngreso]);
			}
		}
	}


	/*
	 *	Convertir número a fecha hora
	 */
	public function formatoFechaHora($tnIngreso)
	{
		foreach ($this->aDocumentos[$tnIngreso] as $cClave => $aDocumento) {
			if ($aDocumento['fecha']) {
				if (is_numeric($aDocumento['fecha']) && $aDocumento['fecha']>19000000000000) {
					$this->aDocumentos[$tnIngreso][$cClave]['fecha'] = date_format(date_create_from_format('YmdHis', $aDocumento['fecha']),'Y-m-d H:i:s');
				} else {
					$this->aDocumentos[$tnIngreso][$cClave]['fecha'] = '';
				}
			} else {
				$this->aDocumentos[$tnIngreso][$cClave]['fecha'] = '';
			}
		}
	}


	/*
	 *	Obtiene descripciones de tipos de documento
	 *	Alcance private
	 */
	public function obtenerDescripcionTipos()
	{
		$aDatos = $this->oDb
			->select('cl2tma,de1tma,de2tma,op1tma,op2tma,op3tma,op4tma')
			->from('tabmae')
			->where(['tiptma'=>'LIBROHC', 'cl1tma'=>'TIPOCHC'])  //, 'esttma'=>''
			->orderBy('cl2tma')
			->getAll('array');
		$aReturn = [];
		foreach ($aDatos as $aDato) {
			$lcKey = substr($aDato['CL2TMA'],0,4);
			if (isset($aReturn[$lcKey])) {
				$lcKey .= 'a';
			}
			$aReturn[ $lcKey ] = [
				'Descr' => trim($aDato['DE1TMA']),
				'Lista' => trim($aDato['DE2TMA']),
				'NumEvo' => trim($aDato['OP1TMA']),
				'PrgEvo' => trim($aDato['OP2TMA']),
			];
		}
		return $aReturn;
	}


	/*
	 *	Adiciona un documento en el array documentos
	 *	Alcance private
	 *
	 *	@param int $tnIngreso: Número de ingreso
	 *	@param string $tcTipoDoc: Tipo de documento
	 *	@param string $tcTipoPrg: Programa de ejecución
	 *	@param array $taDato: Array con datos del documento
	 */
	private function addDoc($tnIngreso, $tcTipoDoc, $tcTipoPrg, $taDato)
	{
		$this->aDocumentos[$tnIngreso][] = [
			'tipoDoc' => $tcTipoDoc,
			'tipoPrg' => $tcTipoPrg,
		] + $taDato;
	}

	/*
	 *	Consulta propiedades para la clase
	 *	Alcance private
	 */
	private function obtenerPropiedades()
	{
		// Lista descripciones de tipos
		$this->aDescTipos = $this->obtenerDescripcionTipos();

		// Fecha inicio HCPPAL
		$this->nFechaIniHCppal = $this->oDb->obtenerTabMae1('op2tma', 'HCPARAM', ['CL1TMA'=>'FINIHCPP', 'ESTTMA'=>''], null, '20150701');

		// Fecha inicio webservice de laboratorio
		$this->nFechaInicioWSLAB = 20190518;

		// Tipo de manejo de fecha para enfermería
		$this->cTipoFechaEnf = $this->oDb->obtenerTabMae1('op2tma', 'LIBROHC', ['CL1TMA'=>'ENFFECHA', 'ESTTMA'=>''], null, 'MIN');

		// Lista programas consulta externa
		$lcTmp = $this->oDb->obtenerTabMae1('DE2TMA||OP5TMA', 'LIBROHC', ['CL1TMA'=>'PARAM','CL2TMA'=>'PRG_CE','ESTTMA'=>''], null, "'RIA100ORD','HC0007AN','HC0007U','HC0007','HCPPAL','HCPPALWEB'");
		$this->aLstProg = explode(',', str_replace('\'', '', trim($lcTmp)));

		// Cups de glucometría de laboratorio
		$lcTmp = $this->oDb->obtenerTabMae1('DE2TMA||OP5TMA', 'FORMEDIC', ['CL1TMA'=>'GLUCGAS','ESTTMA'=>''], null, '903839,M19275,903883,911015,911003,911005,911009,911013,911017,911021,911023,911025,911027,911028,911029,911030,906232,912001,912002,912003,912004,912005');
		$this->aCupsGlucGas = explode(',', str_replace('\'', '', trim($lcTmp)));

		// Cups de glucometría de laboratorio
		$lcTmp = $this->oDb->obtenerTabMae1('de2tma||op5tma', 'FORMEDIC', ['cl1tma'=>'GLUCOME','esttma'=>''], null, 'M19275,903883');
		$this->aCupsGlucome = explode(',', str_replace('\'', '', trim($lcTmp)));

		// Lista de índices de órdenes ambulatorias para imprimir
		$this->cTodosOrdAmb = trim($this->oDb->obtenerTabMae1('OP5TMA', 'HCPARAM', ['CL1TMA'=>'PRINT', 'CL2TMA'=>'TODO', 'ESTTMA'=>''], null, '1,4,6,7,8,9,10,91,92'));

		// Cups de Nutrición
		$lcTmp = trim($this->oDb->obtenerTabMae1('de2tma||op5tma', 'FORMEDIC', ['CL1TMA'=>'CUPSNUTR','ESTTMA'=>''], null, '933600,933601'));
		$this->aCupsHisNutric = explode(',', $lcTmp);
	}


	/*
	 *	Consulta de habitaciones para un ingreso
	 *	Alcance private
	 */
	public function obtenerHabitaciones($tnIngreso)
	{
		$this->aSeccHab = [];
		$aDatos = $this->oDb
			->select('strepc AS Seccion, ntrepc AS Habita, fteepc AS Tipo, feiepc * 1000000 + hoiepc AS FecHora')
			->from('riaepc')
			->where(['ingepc'=>$tnIngreso])
			->notWhere('strepc=\'TU\'')
			->orderBy('fecepc,horepc')
			->getAll('array');

		$nNumReg = $this->oDb->numRows();
		$lnFecHoraIni = $lnFecHoraFin = 0;
		$lcSecc = $lcHab = $lcTipo = '';

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim',$aDato);
			$lnFecHoraFin= $aDato['FECHORA'];
			$this->aSeccHab[] = [
				'SECCION'	=> $lcSecc,
				'HABITA'	=> $lcHab,
				'TIPO'		=> $lcTipo,
				'FECHORAINI'=> $lnFecHoraIni,
				'FECHORAFIN'=> $lnFecHoraFin,
				];
			$lcSecc			= $aDato['SECCION'];
			$lcHab			= $aDato['HABITA'];
			$lcTipo			= $aDato['TIPO'];
			$lnFecHoraIni	= $aDato['FECHORA'];
		}
		$lnFecHoraFin = 70001231235959;
		$this->aSeccHab[] = [
			'SECCION'	=> $lcSecc,
			'HABITA'	=> $lcHab,
			'TIPO'		=> $lcTipo,
			'FECHORAINI'=> $lnFecHoraIni,
			'FECHORAFIN'=> $lnFecHoraFin,
			];
	}

	/*
	 *	Consulta de las vías de un ingreso
	 *	Alcance private
	 */
	public function obtenerVia($tnIngreso)
	{
		$nFecIniDef = 0; $nFecFinDef = 70001231235959;
		$this->aVia = [];

		$aVias = $this->oDb->distinct()
			->select('viaind AS via')
			->from('riaingd')
			->where(['nigind'=>$tnIngreso])
			->getAll('array');
		$nNumReg = $this->oDb->numRows();

		// El paciente tiene solo una vía
		if ($nNumReg == 1) {
			$this->aVia[] = [
				'VIA' => $aVias[0]['VIA'],
				'FHINI' => $nFecIniDef,
				'FHFIN' => $nFecFinDef
				];
		}

		// El paciente tiene más de una vía
		else {
			$aVias = $this->oDb
				->select('viaind AS via, fecind*1000000+horind AS fechor')
				->from('riaingd')
				->where(['nigind'=>$tnIngreso])
				->orderBy('fecind,horind')
				->getAll('array');
			$nNumReg = $this->oDb->numRows();

			$nFechoraIni = 0; $nFechoraFin = 0;
			$nItem = 0;
			$cVia = '';
			foreach ($aVias as $aDato) {
				$nItem++;
				if ($nItem > 1) {
					if ($cVia == $aDato['VIA'] && $nItem !== $nNumReg) { continue; }
					$this->aVia[] = [
						'VIA' => $cVia,
						'FHINI' => $nFechoraIni,
						'FHFIN' => intval($aDato['FECHOR'])
						];
					$nFechoraIni = intval($aDato['FECHOR']);
				} else {
					$nFechoraIni = $nFecIniDef;
				}
				$cVia = $aDato['VIA'];

				if ($nItem == $nNumReg) {
					$nNum = count($this->aVia);
					if ($this->aVia[$nNum-1]['VIA'] == $cVia) {
						$this->aVia[$nNum-1]['FHFIN'] = $nFecFinDef;
					} else {
						$this->aVia[] = [
							'VIA' => $cVia,
							'FHINI' => $nFechoraIni,
							'FHFIN' => $nFecFinDef
							];
					}
				}
			}
		}
	}

	/*
	 *	Obtener la vía registrada a una fecha y hora
	 *	Precaución: se trabaja sobre el array actual, que corresponde al ingreso que se está consultando
	 *	Alcance private
	 */
	private function consultaViaFecha($tnFecha=0, $tnHora=0)
	{
		$lcVia = '';
		if (!empty($tnFecha) && !empty($tnHora)) {
			// si solo hay una retorna esa vía
			if (count($this->aVia)==1) {
				$lcVia = $this->aVia[0]['VIA'];
			} else {
				// fecha y hora que se está consultando
				$lnFH = $tnFecha*1000000+$tnHora;
				// recorre el arreglo buscando coincidencia
				foreach ($this->aVia as $laVia) {
					// queda el último si nunguno coincide
					$lcVia = $laVia['VIA'];
					// si coincide detiene el ciclo
					if ($lnFH>=$laVia['FHINI'] && $lnFH<=$laVia['FHFIN']) break;
				}
			}
		}
		return $lcVia;
	}

	/*
	 *	Obtener la habitación registrada a una fecha y hora
	 *	Precaución: se trabaja sobre el array actual,  que corresponde al ingreso que se está consultando
	 *	Alcance private
	 */
	private function consultaHabFecha($tnFecha=0, $tnHora=0)
	{
		$lcSecHab = '';
		if (!empty($tnFecha) && !empty($tnHora)) {
			// si solo hay una retorna esa habitación
			if (count($this->aSeccHab)==1) {
				$lcSec = $this->aSeccHab[0]['SECCION'];
				$lcHab = $this->aSeccHab[0]['HABITA'];
			} else {
				// fecha y hora que se está consultando
				$lnFH = $tnFecha*1000000+$tnHora;
				// recorre el arreglo buscando coincidencia
				foreach ($this->aSeccHab as $laSecHab) {
					// queda el último si nunguno coincide
					$lcSec = $laSecHab['SECCION'];
					$lcHab = $laSecHab['HABITA'];
					// si coincide detiene el ciclo
					if ($lnFH>=$laSecHab['FECHORAINI'] && $lnFH<=$laSecHab['FECHORAFIN']) break;
				}
			}
			$lcSecHab = $lcSec.((!empty($lcSec) && !empty($lcHab)) ? '-' : '').$lcHab;
		}
		return $lcSecHab;
	}


// ****************************************************************** //
// ********************  CONSULTA DE DOCUMENTOS  ******************** //
// ****************************************************************** //


	/*
	 *	obtener Notas Aclaratorias, queda con número de ingreso=0
	 */
	public function consultarNotasAclaratorias()
	{
		$cTipoDoc = '4900';
		$cTipoPrg = 'EPI009';

		if ($this->bLibro24hr){
			$this->oDb->where("n.fecnot*1000000+n.hornot>{$this->nFecHora24}");
		}
		$aDatos = $this->oDb
			->select('n.connot, n.fecnot, n.hornot')
			->select('IFNULL(m.regmed,\'\') AS regmed, IFNULL(m.nommed,\'\') AS nommed, IFNULL(m.nnomed,\'\') AS nnomed')
			->from('notacl AS n')
			->leftJoin('riargmn AS m', 'n.usrnot = m.usuari')
			->where(['n.cnlnot'=>1, 'n.tidnot'=>$this->cTipoId, 'n.idenot'=>$this->nNumeroId])
			->orderBy('n.connot')
			->getAll('array');

		if ($this->oDb->numRows() > 0) {
			//$this->aIngresosTipos[0][] = [$cTipoDoc => [
			$this->aIngresosTipos[$this->cSinIngreso][] = [$cTipoDoc => [
				'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
				'tipoPrg' => $cTipoPrg,
				'numRows' => $this->oDb->numRows(),
			]];
		}

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			$lnIngreso=$this->cSinIngreso;
			$this->addDoc($lnIngreso, $cTipoDoc, $cTipoPrg, [
				'codCup'	=> '',
				'cnsDoc'	=> $aDato['CONNOT'],
				'cnsCita'	=> '',
				'cnsCons'	=> '',
				'cnsEvo'	=> '',
				'fecha'		=> $aDato['FECNOT']*1000000 + $aDato['HORNOT'],
				'medRegMd'	=> $aDato['REGMED'],
				'medApell'	=> $aDato['NOMMED'],
				'medNombr'	=> $aDato['NNOMED'],
				'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'],
				'codvia'	=> '',
				'sechab'	=> '',
			]);
		}
	}


	/*
	 *	Obtener Triages
	 */
	public function consultarTriage($tnIngreso)
	{
		if ($tnIngreso <= 1695954) return;

		$cTipoDoc = '2800';
		$cTipoPrg = 'TRI010';

		if ($this->bLibro24hr){
			$this->oDb->where("t.fectri*1000000+t.hortri>{$this->nFecHora24}");
		}
		$aDatos = $this->oDb
			->select('ROW_NUMBER() OVER (ORDER BY t.cnstri) AS CnsTriage')
			->select('t.cnstri, t.prctri AS Clasif, t.fectri, t.hortri')
			->select('IFNULL(m.regmed,\'\') AS regmed, IFNULL(m.nommed,\'\') AS nommed, IFNULL(m.nnomed,\'\') AS nnomed')
			->from('triagu AS t')
			->leftJoin('riargmn AS m', 't.usrtri = m.usuari')
			->where(['t.nigtri'=>$tnIngreso, 't.pgmtri'=>'TRI010'])
			->orderBy('t.cnstri')
			->getAll('array');

		if ($this->oDb->numRows() > 0)
			$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
				'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
				'tipoPrg' => $cTipoPrg,
				'numRows' => $this->oDb->numRows(),
				];

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
				'codCup'	=> '',
				'cnsDoc'	=> $aDato['CNSTRI'],
				'cnsCita'	=> '',
				'cnsCons'	=> '',
				'cnsEvo'	=> $aDato['CNSTRIAGE'],
				'fecha'		=> $aDato['FECTRI']*1000000 + $aDato['HORTRI'],
				'medRegMd'	=> $aDato['REGMED'],
				'medApell'	=> $aDato['NOMMED'],
				'medNombr'	=> $aDato['NNOMED'],
				'descrip'	=> 'Triage Clasif.' . $aDato['CLASIF'] . ' (Valoración No.' . $aDato['CNSTRIAGE'] . ')',
				'codvia'	=> '01',
				'sechab'	=> '',
			]);
		}
	}


	/*
	 *	Obtener Historias realizadas con el formato HCPPAL
	 */
	public function consultarHistoria($tnIngreso, $taCondiciones=[])
	{
		// Nueva HC
		if (isset($this->aIngresos[$tnIngreso]) && $this->aIngresos[$tnIngreso]['fechaing'] >= $this->nFechaIniHCppal) {
			$cTipoDoc = '2300';
			$cTipoPrg = 'HCPPAL';

			$this->oDb
				->where(['h.nroing'=>$tnIngreso])
				->where('h.indice=2 AND h.subind=1 AND h.FILLE1=0')
				->where('SUBSTR(h.descri,1,2) IN (\'01\',\'04\',\'05\',\'06\')');
			if ($this->bLibro24hr){
				$this->oDb->where("h.fechis*1000000+h.horhis>{$this->nFecHora24}");
			}
			if ((is_array($taCondiciones) && count($taCondiciones)>0) || (is_string($taCondiciones) && strlen($taCondiciones)>0)) {
				$this->oDb->where($taCondiciones);
			}

			$aDatos = $this->oDb
				->select('h.concon, h.fechis, h.horhis, SUBSTR(h.descri,1,2) AS viahis')
				->select('IFNULL(m.regmed,\'\') AS regmed, IFNULL(m.nommed,\'\') AS nommed, IFNULL(m.nnomed,\'\') AS nnomed')
				->from('riahis AS h')
				->leftJoin('riargmn AS m', 'h.usrhis = m.usuari')
				->getAll('array');

			if ($this->oDb->numRows() > 0)
				$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
					'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
					'tipoPrg' => $cTipoPrg,
					'numRows' => $this->oDb->numRows(),
					];

			foreach ($aDatos as $aDato) {
				//$cFecha = date_create_from_format('YmdHis', $aDato['FECHIS'].str_pad($aDato['HORHIS'],6,"0",STR_PAD_LEFT));
				$aDato = array_map('trim', $aDato);
				$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
					'codCup'	=> '',
					'cnsDoc'	=> $aDato['CONCON'],
					'cnsCita'	=> '',
					'cnsCons'	=> $aDato['CONCON'],
					'cnsEvo'	=> '',
					'fecha'		=> $aDato['FECHIS']*1000000 + $aDato['HORHIS'],
					'medRegMd'	=> $aDato['REGMED'],
					'medApell'	=> $aDato['NOMMED'],
					'medNombr'	=> $aDato['NNOMED'],
					'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'],
					'codvia'	=> $aDato['VIAHIS'],
					'sechab'	=> $this->consultaHabFecha($aDato['FECHIS'], $aDato['HORHIS']),
				]);
			}
		}
	}


	/*
	 *	Obtener Historias de Urgencias
	 */
	public function consultarHcUrg($tnIngreso)
	{
		if (isset($this->aIngresos[$tnIngreso]) && $this->aIngresos[$tnIngreso]['fechaing'] <= $this->nFechaIniHCppal) {
			$cTipoDoc = '2000';
			$cTipoPrg = 'HCPPAL'; //$cTipoPrg = 'HC0100';

			if ($this->bLibro24hr){
				$this->oDb->where("h.fechcl*1000000+h.horhcl>{$this->nFecHora24}");
			}
			$aDatos = $this->oDb
				->select('h.ccohcl, h.fechcl, h.horhcl')
				->select('IFNULL(m.regmed,\'\') AS regmed, IFNULL(m.nommed,\'\') AS nommed, IFNULL(m.nnomed,\'\') AS nnomed')
				->from('hiscli AS h')
				->leftJoin('riargmn AS m', 'h.usrhcl = m.usuari')
				->where(['h.inghcl'=>$tnIngreso])
				->where('h.indhcl = 5 AND h.subhcl = 5')
				->getAll('array');

			if ($this->oDb->numRows() > 0)
				$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
					'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
					'tipoPrg' => $cTipoPrg,
					'numRows' => $this->oDb->numRows(),
					];

			foreach ($aDatos as $aDato) {
				$aDato = array_map('trim', $aDato);
				$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
					'codCup'	=> '',
					'cnsDoc'	=> $aDato['CCOHCL'],
					'cnsCita'	=> '',
					'cnsCons'	=> $aDato['CCOHCL'],
					'cnsEvo'	=> '',
					'fecha'		=> $aDato['FECHCL']*1000000 + $aDato['HORHCL'],
					'medRegMd'	=> $aDato['REGMED'],
					'medApell'	=> $aDato['NOMMED'],
					'medNombr'	=> $aDato['NNOMED'],
					'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'],
					'codvia'	=> '01',
					'sechab'	=> $this->consultaHabFecha($aDato['FECHCL'], $aDato['HORHCL']),
				]);
			}
		}
	}


	/*
	 *	Obtener Historias de Hospitalización
	 */
	public function consultarHcHos($tnIngreso)
	{
		if (isset($this->aIngresos[$tnIngreso]) && $this->aIngresos[$tnIngreso]['fechaing'] <= $this->nFechaIniHCppal) {
			$cTipoDoc = '2100';
			$cTipoPrg = 'HCPPAL'; //$cTipoPrg = 'HC0007U';

			if ($this->bLibro24hr){
				$this->oDb->where("h.fechos*1000000+h.horhos>{$this->nFecHora24}");
			}
			$aDatos = $this->oDb
				->select('h.ccohos, h.fechos, h.horhos')
				->select('IFNULL(m.regmed,\'\') AS regmed, IFNULL(m.nommed,\'\') AS nommed, IFNULL(m.nnomed,\'\') AS nnomed')
				->from('hishos AS h')
				->leftJoin('riargmn AS m', 'h.usrhos = m.usuari')
				->where(['h.inghos'=>$tnIngreso])
				->where('h.indhos = 5 AND h.subhos = 5')
				->getAll('array');

			if ($this->oDb->numRows() > 0)
				$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
					'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
					'tipoPrg' => $cTipoPrg,
					'numRows' => $this->oDb->numRows(),
					];

			foreach ($aDatos as $aDato) {
				$aDato = array_map('trim', $aDato);
				$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
					'codCup'	=> '',
					'cnsDoc'	=> $aDato['CCOHOS'],
					'cnsCita'	=> '',
					'cnsCons'	=> $aDato['CCOHOS'],
					'cnsEvo'	=> '',
					'fecha'		=> $aDato['FECHOS']*1000000 + $aDato['HORHOS'],
					'medRegMd'	=> $aDato['REGMED'],
					'medApell'	=> $aDato['NOMMED'],
					'medNombr'	=> $aDato['NNOMED'],
					'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'],
					'codvia'	=> '05',
					'sechab'	=> $this->consultaHabFecha($aDato['FECHOS'], $aDato['HORHOS']),
				]);
			}
		}
	}


	/*
	 *	Obtener Historias de Consulta Externa
	 */
	public function consultarHcCex($tnIngreso)
	{
		$cTipoDoc = '2200';
		$cTipoPrg = 'HCPPAL'; //$cTipoPrg = 'HC0007AN';

		if ($this->bLibro24hr){
			$this->oDb->where("h.fcochc*1000000+h.horchc>{$this->nFecHora24}");
		}
		$aDatos = $this->oDb->distinct()
			->select('D.CCIORD, H.CCOCHC, H.FCOCHC, H.HORCHC, H.CCUCHC, C.DESCUP, IFNULL(O.CORORA,0) AS CORORA')
			->select('IFNULL(M.REGMED,\'\') AS REGMED, IFNULL(M.NOMMED,\'\') AS NOMMED, IFNULL(M.NNOMED,\'\') AS NNOMED')
			->from('RIACHC AS H')
			->leftJoin('RIAORD AS D', 'H.CCOCHC=D.CCOORD AND H.NINCHC=D.NINORD AND H.CCUCHC=D.COAORD')
			->leftJoin('RIACUP AS C', 'H.CCUCHC=C.CODCUP')
			->leftJoin('ORDAMB AS O', 'H.NINCHC=O.INGORA AND H.CCOCHC=O.CCOORA AND D.CCIORD=O.CCIORA')
			->leftJoin('RIARGMN AS M', 'H.USRCHC = M.USUARI')
			->where(['H.NINCHC'=>$tnIngreso,'H.HMACHC'=>0])
			->in('H.PGMCHC', $this->aLstProg)
			->getAll('array');

		if ($this->oDb->numRows() > 0)
			$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
				'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
				'tipoPrg' => $cTipoPrg,
				'numRows' => $this->oDb->numRows(),
			];

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
				'codCup'	=> $aDato['CCUCHC'],
				'cnsDoc'	=> $aDato['CCOCHC'],
				'cnsCita'	=> $aDato['CCIORD'],
				'cnsCons'	=> $aDato['CCOCHC'],
				'cnsEvo'	=> $aDato['CORORA'],
				'fecha'		=> $aDato['FCOCHC']*1000000 + $aDato['HORCHC'],
				'medRegMd'	=> $aDato['REGMED'],
				'medApell'	=> $aDato['NOMMED'],
				'medNombr'	=> $aDato['NNOMED'],
				'descrip'	=> $aDato['DESCUP'],
				'codvia'	=> '02',
				'sechab'	=> $this->consultaHabFecha($aDato['FCOCHC'], $aDato['HORCHC']),
			]);
		}
	}


	/*
	 *	Obtener Evoluciones, Órdenes Médicas hospitalarias, etc
	 */
	public function consultarEvoluciones($tnIngreso, $taCondiciones=[])
	{
		$this->oDb->where(['e.ninevl'=>$tnIngreso]);
		if ($this->bLibro24hr){
			$this->oDb->where("e.fecevl*1000000+e.horevl>{$this->nFecHora24}");
		}
		if ((is_array($taCondiciones) && count($taCondiciones)>0) || (is_string($taCondiciones) && strlen($taCondiciones)>0)) {
			$this->oDb->where($taCondiciones);
		}
		$aDatos = $this->oDb
			->select('e.conevl, e.pgmevl, e.fecevl, e.horevl')
			->select('IFNULL(m.regmed,\'\') AS regmed, IFNULL(m.nommed,\'\') AS nommed, IFNULL(m.nnomed,\'\') AS nnomed')
			->select('(SELECT count(ox.ingrdo) FROM riafardo AS ox WHERE e.ninevl=ox.ingrdo AND e.conevl=ox.cevrdo AND ox.cuprdo<>\'\') AS NumOxigen')
			->select('(SELECT count(o.ingpro) FROM ordpro AS o WHERE e.ninevl=o.ingpro AND e.conevl=o.conpro) AS NumProced')
			->select('(SELECT count(m.ninfmd) FROM formed AS m WHERE e.ninevl=m.ninfmd AND e.conevl=m.cevfmd) AS NumMedic')
			->select('(SELECT count(i.ninord) FROM riaordl24 AS i WHERE e.ninevl=i.ninord AND e.conevl=i.evoord AND (i.coaord LIKE \'8904%\')) AS NumIntCon')
			->select('(SELECT count(d.ninevl) FROM evoluco AS d WHERE e.ninevl=d.ninevl AND e.conevl=d.conevl AND e.conevl=d.ccievl AND d.cnlevl BETWEEN 1700 AND 1749) AS NumDieta')
			->select('(SELECT count(f.ninevl) FROM evoluco AS f WHERE e.ninevl=f.ninevl AND e.conevl=f.conevl AND e.conevl=f.ccievl AND f.cnlevl BETWEEN 1751 AND 1799) AS NumOrdEnf')
			->select('(SELECT count(a.ingaep) FROM anaepi AS a WHERE e.ninevl=a.ingaep AND e.conevl=a.cevaep AND a.tipaep=\'EP\') AS NumAnaEpi')
			->from('evoluc AS e')
			->leftJoin('riargmn AS m', 'e.usrevl = m.usuari')
			->groupBy('e.ninevl,e.conevl,e.usrevl,e.pgmevl,e.fecevl,e.horevl,m.regmed, m.nommed, m.nnomed')
			->getAll('array');

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			$aDato['NUMANAEPI'] = $aDato['NUMANAEPI']>0 ? 1 : 0;
			$cTipoPrg = $aDato['PGMEVL'];
			$cTipoDoc = '';
			foreach ($this->aDescTipos as $cKey=>$aTipo) {
				if ($cTipoPrg==$aTipo['PrgEvo'] && $aDato['NUMANAEPI']==$aTipo['NumEvo']) {
					$cTipoDoc = substr($cKey, 0, 4);
					break;
				}
			}

			if ($cTipoDoc !== '') {
				$cOrdenes = ''
					.($aDato['NUMOXIGEN']>0 ? ' - Oxígeno' : '')
					.($aDato['NUMPROCED']>0 ? ' - Procedimientos' : '')
					.($aDato['NUMMEDIC' ]>0 ? ' - Medicamentos' : '')
					.($aDato['NUMINTCON']>0 ? ' - Interconsultas' : '')
					.($aDato['NUMDIETA' ]>0 ? ' - Dieta' : '')
					.($aDato['NUMORDENF']>0 ? ' - Ord Enfermeria' : '');

				if (array_key_exists($cTipoDoc, $this->aIngresosTipos[$tnIngreso])) {
					$this->aIngresosTipos[$tnIngreso][$cTipoDoc]['numRows']++;
				} else {
					$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
						'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
						'tipoPrg' => $cTipoPrg,
						'numRows' => 1,
					];
				}

				$this->addDoc($tnIngreso, $cTipoDoc, 'EV0018E', [
					'codCup'	=> '',
					'cnsDoc'	=> $aDato['PGMEVL'],
					'cnsCita'	=> '',
					'cnsCons'	=> '',
					'cnsEvo'	=> $aDato['CONEVL'],
					'fecha'		=> $aDato['FECEVL']*1000000 + $aDato['HOREVL'],
					'medRegMd'	=> $aDato['REGMED'],
					'medApell'	=> $aDato['NOMMED'],
					'medNombr'	=> $aDato['NNOMED'],
					'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'] . $cOrdenes,
					'codvia'	=> $this->consultaViaFecha($aDato['FECEVL'], $aDato['HOREVL']),
					'sechab'	=> $this->consultaHabFecha($aDato['FECEVL'], $aDato['HOREVL']),
				]);
			}
		}
	}


	/*
	 *	Obtener Todas las Evoluciones, sin buscar detalle de orden médica
	 */
	public function consultarEvolucionesSinDetalleOM($tnIngreso)
	{
		if ($this->bLibro24hr){
			$this->oDb->where("e.fecevl*1000000+e.horevl>{$this->nFecHora24}");
		}
		$cTipoDoc = '3000';
		$cTipoPrg = 'EV0018E';
		$aLstNumEvo = [];
		$aDatos = $this->oDb
			->select('E.CONEVL, E.PGMEVL, E.FECEVL, E.HOREVL')
			->select('IFNULL(M.REGMED,\'\') AS REGMED, IFNULL(M.NOMMED,\'\') AS NOMMED, IFNULL(M.NNOMED,\'\') AS NNOMED')
			->from('EVOLUC AS E')
			->leftJoin('RIARGMN AS M', 'E.USREVL = M.USUARI')
			->where(['E.NINEVL'=>$tnIngreso])
			->groupBy('E.NINEVL,E.CONEVL,E.USREVL,E.PGMEVL,E.FECEVL,E.HOREVL,M.REGMED, M.NOMMED, M.NNOMED')
			->getAll('array');
		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			if (in_array($aDato['CONEVL'], $aLstNumEvo)) {
				continue;
			}
			$aLstNumEvo[] = $aDato['CONEVL'];
			if (isset($this->aIngresosTipos[$tnIngreso]) && is_array($this->aIngresosTipos[$tnIngreso]) && array_key_exists($cTipoDoc, $this->aIngresosTipos[$tnIngreso])) {
				$this->aIngresosTipos[$tnIngreso][$cTipoDoc]['numRows']++;
			} else {
				if (!isset($this->aIngresosTipos[$tnIngreso])) $this->aIngresosTipos[$tnIngreso] = [];
				$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
					'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
					'tipoPrg' => $cTipoPrg,
					'numRows' => 1,
				];
			}
			$this->addDoc($tnIngreso, $cTipoDoc, 'EV0018E', [
				'codCup'	=> '',
				'cnsDoc'	=> $aDato['PGMEVL'],
				'cnsCita'	=> '',
				'cnsCons'	=> '',
				'cnsEvo'	=> $aDato['CONEVL'],
				'fecha'		=> $aDato['FECEVL']*1000000 + $aDato['HOREVL'],
				'medRegMd'	=> $aDato['REGMED'],
				'medApell'	=> $aDato['NOMMED'],
				'medNombr'	=> $aDato['NNOMED'],
				'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'],
				'codvia'	=> $this->consultaViaFecha($aDato['FECEVL'], $aDato['HOREVL']),
				'sechab'	=> $this->consultaHabFecha($aDato['FECEVL'], $aDato['HOREVL']),
			]);
		}
	}


	/*
	 *	Obtener Procedimientos, interconsultas y laboratorios
	 */
	public function consultarProcedimientos($tnIngreso, $taCondiciones=[])
	{
		$laProgramasEvoluciones=[];
		$this->oDb
			->where("O.NINORD=:cpIngreso AND O.ESTORD IN (3,59) AND O.COAORD NOT IN ('890701','890702')")
			->addBindValue([':cpIngreso'=>$tnIngreso]);
		if ($this->bLibro24hr){
			$this->oDb->where("O.FERORD*1000000+O.HRLORD>{$this->nFecHora24}");
		}
		//$cTipoDoc = 'PROCEDIMIENTO';
		if ( (is_array($taCondiciones) && count($taCondiciones)>0) || (is_string($taCondiciones) && !empty($taCondiciones)) ) {
			$this->oDb->where($taCondiciones);
		}
		$aDatos = $this->oDb
			->select('O.CCOORD, O.CCIORD, O.EVOORD, O.CODORD, O.VIAORD')
			->select('O.FRLORD, O.HOCORD, O.FERORD, O.HRLORD, O.PGMORD')
			->select('O.RMEORD, IFNULL(MO.NOMMED,\'\') AS NOMEDO, IFNULL(MO.NNOMED,\'\') AS APMEDO')
			->select('O.RMRORD, IFNULL(MR.NOMMED,\'\') AS NOMMED, IFNULL(MR.NNOMED,\'\') AS NNOMED')
			->select('O.COAORD AS CODCUP, C.DESCUP, C.ESPCUP, C.PGRCUP')
			->select("IFNULL((SELECT PGMDET FROM RIADET WHERE INGDET=O.NINORD AND CUPDET=O.COAORD AND CCIDET=O.CCIORD AND ESTORD=3 ORDER BY FECDET DESC,HORDET DESC FETCH FIRST 1 ROWS ONLY), C.PGRCUP) PGMDET")
			->select('(SELECT MIN(M.CCIORD) FROM RIAORD M WHERE M.CCOORD=O.CCOORD AND M.NINORD=O.NINORD) AS MINCCI')
			->select('(SELECT MAX(M.CCIORD) FROM RIAORD M WHERE M.CCOORD=O.CCOORD AND M.NINORD=O.NINORD) AS MAXCCI')
			->from('RIAORD AS O')
			->leftJoin('RIARGMN AS MR', 'O.RMRORD = MR.REGMED')
			->leftJoin('RIARGMN AS MO', 'O.RMEORD = MO.REGMED')
			->leftJoin('RIACUP  AS C', 'O.COAORD = C.CODCUP')
			->getAll('array');

		// Especialidades de Neurointervencionismo
		$loEspNeuroInter = trim($this->oDb->ObtenerTabMae1('DE2TMA', 'EVOLUC', ['CL1TMA'=>'ESPNEURO','ESTTMA'=>''], null, '446'));

		// Programas en RIADET que no cambian RIACUP.PGRCUP
		$lcProgCup = trim($this->oDb->ObtenerTabMae1('DE2TMA||OP5TMA', 'LIBROHC', ['CL1TMA'=>'PGRCUP','CL2TMA'=>'CAMBIO','ESTTMA'=>''], null, ''));
		$laProgCup = explode(',', str_replace('\'', '', $lcProgCup));
		$lbProgCup = !empty($lcProgCup);
		
		$laModulosEvoluciones = $this->oDb->select('trim(CL1TMA) PROGRAMA')->from('TABMAE')->where('TIPTMA=\'TIPPROG\' AND OP1TMA=\'C\' AND ESTTMA=\' \'')->getAll('array');
		if ($this->oDb->numRows()>0){				
			foreach ($laModulosEvoluciones as $laDatos){
				$laProgramasEvoluciones[]=$laDatos['PROGRAMA'];
			}
		}

		foreach ($aDatos as $aDato) {
			// Evitar historias clínicas
			$lcProgramaOrdena=trim($aDato['PGMORD']);
			
			if (trim($aDato['PGRCUP'])=='RIA100ORD' && (!in_array($lcProgramaOrdena, $laProgramasEvoluciones))) {
				continue; 
			}

			$cTipoDoc = '1000';
			$aDato = array_map('trim', $aDato);

			// Cambia programa por el que está registrado en RIADET
			if ($lbProgCup) {
				if (!in_array($aDato['PGMDET'], $laProgCup)) {
					$aDato['PGRCUP'] = $aDato['PGMDET'];
				}
			}

			if (trim($aDato['PGRCUP'])=='RIA100ORD' && (in_array($lcProgramaOrdena, $laProgramasEvoluciones))) {
				$aDato['PGRCUP']='RIA022';
			}
			
			$cPrgAnestesia = $this->oDb->ObtenerTabmae1('CL4TMA', 'ANEOPC', ['CL1TMA'=>'1','CL3TMA'=>$aDato['CODORD'],'OP2TMA'=>$aDato['CODCUP'],'ESTTMA'=>''], null, '');
			//Correcciones a programa en procedimientos

			// Anestesia - Ecoperioperatorio
			if ($aDato['PGMORD']==='RIA133A')
			{
				$aDato['PGRCUP']=$aDato['PGMORD'];
			}

			// Neuroradiología
			elseif ($aDato['PGMORD']==='RIA133E')
			{
				// Solo el menor número de cita por consulta
				if ($aDato['CCIORD'] = $aDato['MINCCI']){
					$aDato['PGRCUP'] = $aDato['PGMORD'];
				}
			}

			// Radiología
			elseif ($aDato['CODORD']==='602' && in_array($aDato['CODCUP'],['385620','877121','385401','874133','877141','395200','878401']) )
			{
				$aDato['PGRCUP']='RAD002';
			}

			elseif ( ( $aDato['CODCUP']==='933600' && $aDato['VIAORD']==='02' ) ||
				( $aDato['CODCUP']==='938300' && $aDato['FRLORD']<=20101011 ) ||
				( $aDato['CODCUP']==='937000' && $aDato['FRLORD']<=20101128 ) ||
				( in_array($aDato['CODCUP'],['931000','931001']) && $aDato['FRLORD']<=20101201 ) )
			{
				$aDato['PGRCUP']='RIA022';
			}

			elseif($aDato['CODCUP']==='933601'){
				$laPrograma = $this->oDb
					->select('TRIM(PGMDET) PGMDET')
					->from("RIADET")
					->where([
						'INGDET'=>$tnIngreso,
						'CCIDET'=>$aDato['CCIORD'],
						'ESTDET'=>3
					])
					->get("array");
				switch($laPrograma['PGMDET']??''){
					case 'RIA022W':
						$aDato['PGRCUP']='RIA022';
						break;
					case 'NUT001':
						$aDato['PGRCUP']='NUT001';
						break;
				}
			}

			elseif ($aDato['CODCUP']==='937000' && $aDato['FRLORD']>=20101129 && $aDato['FRLORD']<=20101221 )
			{
				$aDato['PGRCUP']='FIS012';
			}

			elseif ($tnIngreso<962431 && $aDato['CODORD']==='130' && $aDato['PGMORD']==='RIA133' )
			{
				$aDato['PGRCUP']='RIA132';
			}

			// Interconsultas
			elseif (substr($aDato['CODCUP'],0,4)==='8904')
			{
				// Consultar si existe texto en la respuesta
				$aDatos = $this->oDb
					->select('INGINT')
					->from('INTCON')
					->where(['INGINT'=>$tnIngreso, 'CORINT'=>$aDato['CCIORD']])
					->where("DESINT<>'' AND SORINT='R'")
					->get('array');
				if($this->oDb->numRows()>0){
					$aDato['PGRCUP']='HIS001';
					$cTipoDoc = '1900';
				}else{
					continue;
				}
			}

			// Descripciones quirúrgicas
			elseif ($aDato['CODCUP']==='22')
			{
				$cTipoDoc = '1800';
			}

			// Saltar Juntas Médicas repetidas
			// elseif ( in_array($aDato['CODCUP'],['890502','890503']) ) {
			elseif ($aDato['PGRCUP']==='RIA050')
			{
				// Solo el mayor número de cita por consulta
				if ($aDato['CCIORD']==$aDato['MAXCCI']) {
					$cTipoDoc = '1700';
					// Fecha de realizado será la registrada como cita
					$aDato['FERORD'] = $aDato['FRLORD'];
					$aDato['HRLORD'] = $aDato['HOCORD'];
				}else{
					continue;
				}
			}

			// Glucometrías
			elseif (in_array($aDato['CODCUP'], $this->aCupsGlucome))
			{
				$aGluc = $this->oDb
					->select('COUNT(*) AS CTAGLUC')
					->from('ENGLUCOL01')
					->where(['INGGLU'=>$tnIngreso, 'CNTGLU'=>$aDato['CCIORD']])
					->in('PGMGLU',['GLU001','INS_ORU'])
					->get();
				if (intval($aGluc->CTAGLUC) > 0) {
					$aDato['PGRCUP'] = 'GLU001';
				}
			}

			// Laboratorios
			elseif ($aDato['CODORD']==='353' && $aDato['PGRCUP']==='RIA022')
			{
				$aDatos = $this->oDb
					->select('INGLAB')
					->from('RESLAB')
					->where(['INGLAB'=>$tnIngreso, 'CONLAB'=>$aDato['CCIORD']])
					->get('array');
				if($this->oDb->numRows()>0){
					$cTipoDoc = '1100';
					if ($aDato['FERORD'] > $this->nFechaInicioWSLAB) {
						$aDato['PGRCUP'] = 'LAB001';
					}
				}
			}

			elseif ($aDato['CODORD']=='353' && !in_array($aDato['CODCUP'], $this->aCupsGlucGas))
			{
				$cTipoDoc = '1100';
				if ($aDato['FERORD'] > $this->nFechaInicioWSLAB) {
					$aDato['PGRCUP'] = 'LAB001';
				}
			}

			// Preanestesia
			elseif ($cPrgAnestesia !== '')
			{
				$aDato['PGRCUP'] = $cPrgAnestesia;
			}

			// Cambios en Cardiología no invasiva
			elseif ($aDato['CODCUP']==='881236' && $aDato['FERORD']>20141101)
			{
				$aCni = $this->oDb
					->count('*', 'CtaCni')
					->from('arcagfal04')
					->where(['ingagf'=>$tnIngreso, 'cciagf'=>$aDato['CCIORD'], 'tipagf'=>'ORU^R01^OR', 'txtagf'=>'Satisfactorio.'])
					->where('anuagf', '>', 0)
					->get();
				if (intval($aCni->CTACNI) > 0) {
					$aCni = $this->oDb
						->sum('CASE WHEN indeco IN (13,14,15) THEN 1 ELSE 0 END','Cta021')
						->sum('CASE WHEN indeco IN (21,22,23,24,25) THEN 1 ELSE 0 END','Cta022')
						->from('ecos')
						->where(['ingeco'=>$tnIngreso, 'coneco'=>$aDato['CCIORD']])
						->get();
					if (intval($aCni->CTA022) > 0) {
						$aDato['CD2ORD'] = 0;
						$aDato['PGRCUP'] = 'CNI022';
					}
					elseif (intval($aCni->CTA021) > 0) {
						$aDato['CD2ORD'] = 1;
						$aDato['PGRCUP'] = 'CNI021';
					}
				}
			}

			// Marca Historia Nutricional
			elseif (in_array($aDato['CODCUP'], $this->aCupsHisNutric))
			{
				$aHcNut = $this->oDb
					->count('*','CtaNut')
					->from('InfNutL01')
					->where(['ingNut'=>$tnIngreso, 'cciNut'=>$aDato['CCIORD']])
					->get();
				if (intval($aHcNut->CtaNut ?? '') > 0) {
					$aDato['PGRCUP'] = 'NUT001';
				}
			}

			// Saltar Cuidado (manejo) intrahospitalario por medicina especializada, sin respuesta
			elseif ($aDato['CODCUP']==='890602')
			{
				$aTemp = $this->oDb
					->count('*','CtaReg')
					->from('riahis')
					->where(['nroing'=>$tnIngreso, 'suborg'=>$aDato['CODCUP'], 'concon'=>$aDato['CCIORD']])
					->get();
				if ($aTemp->CTAREG == 0) {
					continue;
				}
			}

			elseif ($aDato['CODORD']!=='' && $aDato['ESPCUP']!=='' && $aDato['CODORD']!==$aDato['ESPCUP'])
			{
				$cPrgUrg = trim($this->oDb->ObtenerTabmae1('DE2TMA', 'EVOLUC', ['cl1tma'=>'CUPIMUR','cl2tma'=>$aDato['CODCUP'],'cl3tma'=>'D','esttma'=>''], null, ''));
				if ( $cPrgUrg !== '' ) {
					$aDato['PGRCUP'] = $cPrgUrg;
				}
			}

			elseif ($aDato['PGRCUP']==='FIS011')
			{
				$cTipoDoc = '1000';
				$aDatos = $this->oDb
					->select('substr(trim(DESTOC),1,8) FECHA_REALIZA, substr(trim(DESTOC),9,6) HORA_REALIZA')
					->from('FisOcu')
					->where(['INGTOC'=>$tnIngreso, 'CCITOC'=>$aDato['CCIORD'], 'INDTOC'=>'1'])
					->get('array');

				// Fecha de realizado será la registrada como cita
				$aDato['FERORD'] = $aDatos['FECHA_REALIZA']??'0';
				$aDato['HRLORD'] = $aDatos['HORA_REALIZA']??'0';
			}

			elseif (substr($aDato['PGRCUP'],0,5)=='FIS01')
			{
				$aDato['PGRCUP'] = substr($aDato['PGRCUP'],0,6);
			}

			if (array_key_exists($cTipoDoc, $this->aIngresosTipos[$tnIngreso])) {
				$this->aIngresosTipos[$tnIngreso][$cTipoDoc]['numRows']++;
			} else {
				$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
					'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
					'tipoPrg' => $aDato['PGRCUP'],
					'numRows' => 1,
				];
			}

			$lcFechaOrd = $aDato['FRLORD']>0 ? (date_format(date_create_from_format('YmdHis', $aDato['FRLORD']*1000000 + $aDato['HOCORD']),'Y-m-d H:i:s')) : '';
			$this->addDoc($tnIngreso, $cTipoDoc, $aDato['PGRCUP'], [
				'codCup'	=> $aDato['CODCUP'],
				'cnsDoc'	=> '',
				'cnsCita'	=> $aDato['CCIORD'],
				'cnsCons'	=> $aDato['CCOORD'],
				'cnsEvo'	=> $aDato['EVOORD'],
				'fecha'		=> $aDato['FERORD']*1000000 + $aDato['HRLORD'],
				'medRegMd'	=> $aDato['RMRORD'],
				'medApell'	=> $aDato['NOMMED'],
				'medNombr'	=> $aDato['NNOMED'],
				'medOrdAp'	=> $aDato['NOMEDO'],
				'medOrdNm'	=> $aDato['APMEDO'],
				'fechaOrd'	=> $lcFechaOrd,
				'descrip'	=> $aDato['DESCUP'],
				'codvia'	=> $aDato['VIAORD'],
				'sechab'	=> $this->consultaHabFecha($aDato['FERORD'], $aDato['HRLORD']),
			]);
		}
	}


	/*
	 *	Obtener Documentos de Anestesia
	 */
	public function consultarNotasAnestesia($tnIngreso)
	{
		$cTipoDoc = '1200';
		$cTipoPrg = 'EV0053';

		if ($this->bLibro24hr){
			$this->oDb->where("A.FECRAN*1000000+A.HORRAN>{$this->nFecHora24}");
		}
		$aDatos = $this->oDb->distinct()
			->select('A.TIPRAN,A.CONRAN,A.USRRAN,A.PGMRAN,A.FECRAN,A.HORRAN,T.DE1TMA')
			->select('IFNULL(M.REGMED,\'\') AS REGMED, IFNULL(M.NOMMED,\'\') AS NOMMED, IFNULL(M.NNOMED,\'\') AS NNOMED')
			->from('REGANEL01 AS A')
			->leftJoin('RIARGMN AS M', 'A.USRRAN=M.USUARI')
			->leftJoin('TABMAE AS T', "T.TIPTMA='ANSIA' AND T.CL1TMA='CNFNOTAS' AND A.TIPRAN=T.OP2TMA")
			->where(['A.INGRAN'=>$tnIngreso])
			->getAll('array');

		if ($this->oDb->numRows() > 0)
			$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
				'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
				'tipoPrg' => $cTipoPrg,
				'numRows' => $this->oDb->numRows(),
				];

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
				'codCup'	=> '',
				'cnsDoc'	=> $aDato['TIPRAN'].'-'.$aDato['CONRAN'],
				'cnsCita'	=> '',
				'cnsCons'	=> '',
				'cnsEvo'	=> '',
				'fecha'		=> $aDato['FECRAN']*1000000 + $aDato['HORRAN'],
				'medRegMd'	=> $aDato['REGMED'],
				'medApell'	=> $aDato['NOMMED'],
				'medNombr'	=> $aDato['NNOMED'],
				'descrip'	=> $aDato['DE1TMA'],
				'codvia'	=> $this->consultaViaFecha($aDato['FECRAN'], $aDato['HORRAN']),
				'sechab'	=> $this->consultaHabFecha($aDato['FECRAN'], $aDato['HORRAN']),
			]);
		}
	}


	/*
	 *	Obtener Epicrisis (Resumen Médico Final)
	 */
	public function consultarEpicrisis($tnIngreso)
	{
		$cTipoDoc = '4100';
		$cTipoPrg = 'EPI002';

		if ($this->bLibro24hr){
			$this->oDb->where("e.feceph*1000000+e.horeph>{$this->nFecHora24}");
		}
		$aDatos = $this->oDb->distinct()
			->select('e.ccneph, e.coneph, e.feceph, e.horeph')
			->select('IFNULL(m.regmed,\'\') AS regmed, IFNULL(m.nommed,\'\') AS nommed, IFNULL(m.nnomed,\'\') AS nnomed')
			->from('riaeph AS e')
			->leftJoin('riargmn AS m', 'e.usreph = m.usuari')
			->where(['e.nineph'=>$tnIngreso])
			->in('e.pgmeph', ['EPI002','EPIPPAL','EPIPPALWEB'])
			->getAll('array');

		if ($this->oDb->numRows() > 0)
			$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
				'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
				'tipoPrg' => $cTipoPrg,
				'numRows' => $this->oDb->numRows(),
				];

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
				'codCup'	=> '',
				'cnsDoc'	=> $aDato['CCNEPH'],
				'cnsCita'	=> '',
				'cnsCons'	=> $aDato['CONEPH'],
				'cnsEvo'	=> '',
				'fecha'		=> $aDato['FECEPH']*1000000 + $aDato['HOREPH'],
				'medRegMd'	=> $aDato['REGMED'],
				'medApell'	=> $aDato['NOMMED'],
				'medNombr'	=> $aDato['NNOMED'],
				'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'],
				'codvia'	=> $this->consultaViaFecha($aDato['FECEPH'], $aDato['HOREPH']),
				'sechab'	=> $this->consultaHabFecha($aDato['FECEPH'], $aDato['HOREPH']),
			]);
		}
	}

	/*
	 *	Obtener TRASLADOS PACIENTES
	 */
	public function consultarTrasladosPacientes($tnIngreso)
	{
		$cTipoDoc = '3800';
		$cTipoPrg = 'TRASWEB';

		if ($this->bLibro24hr){
			$this->oDb->where("e.FECTRA*1000000+e.HORTRA>{$this->nFecHora24}");
		}
		$aDatos = $this->oDb->distinct()
			->select('e.CONTRA, e.FECTRA, e.HORTRA')
			->select('IFNULL(m.regmed,\'\') AS regmed, IFNULL(m.nommed,\'\') AS nommed, IFNULL(m.nnomed,\'\') AS nnomed')
			->from('TRAPAC AS e')
			->leftJoin('riargmn AS m', 'e.USRTRA = m.usuari')
			->where(['e.INGTRA'=>$tnIngreso])
			->getAll('array');

		if ($this->oDb->numRows() > 0)
			$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
				'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
				'tipoPrg' => $cTipoPrg,
				'numRows' => $this->oDb->numRows(),
				];

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
				'codCup'	=> '',
				'cnsDoc'	=> $aDato['CONTRA'],
				'cnsCita'	=> '',
				'cnsCons'	=> '',
				'cnsEvo'	=> '',
				'fecha'		=> $aDato['FECTRA']*1000000 + $aDato['HORTRA'],
				'medRegMd'	=> $aDato['REGMED'],
				'medApell'	=> $aDato['NOMMED'],
				'medNombr'	=> $aDato['NNOMED'],
				'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'],
				'codvia'	=> $this->consultaViaFecha($aDato['FECTRA'], $aDato['HORTRA']),
				'sechab'	=> $this->consultaHabFecha($aDato['FECTRA'], $aDato['HORTRA']),
			]);
		}
	}


	/*
	 *	Obtener Resumen Médico Administrativo
	 */
	public function consultarResumenAdm($tnIngreso)
	{
		$cTipoDoc = '4000';
		$cTipoPrg = 'EPI003';

		if ($this->bLibro24hr){
			$this->oDb->where("e.feceph*1000000+e.horeph>{$this->nFecHora24}");
		}
		$aDatos = $this->oDb->distinct()
			->select('e.ccneph, e.coneph, e.feceph, e.horeph')
			->select('IFNULL(m.regmed,\'\') AS regmed, IFNULL(m.nommed,\'\') AS nommed, IFNULL(m.nnomed,\'\') AS nnomed')
			->from('riaeph AS e')
			->leftJoin('riargmn AS m', 'e.usreph = m.usuari')
			->where(['e.nineph'=>$tnIngreso, 'e.pgmeph'=>'EPI003'])
			->getAll('array');

		if ($this->oDb->numRows() > 0)
			$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
				'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
				'tipoPrg' => $cTipoPrg,
				'numRows' => $this->oDb->numRows(),
				];

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
				'codCup'	=> '',
				'cnsDoc'	=> $aDato['CCNEPH'],
				'cnsCita'	=> '',
				'cnsCons'	=> $aDato['CONEPH'],
				'cnsEvo'	=> '',
				'fecha'		=> $aDato['FECEPH']*1000000 + $aDato['HOREPH'],
				'medRegMd'	=> $aDato['REGMED'],
				'medApell'	=> $aDato['NOMMED'],
				'medNombr'	=> $aDato['NNOMED'],
				'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'],
				'codvia'	=> $this->consultaViaFecha($aDato['FECEPH'], $aDato['HOREPH']),
				'sechab'	=> $this->consultaHabFecha($aDato['FECEPH'], $aDato['HOREPH']),
			]);
		}
	}


	/*
	 *	Obtener Ordenes Médicas Ambulatorias
	 */
	public function consultarOrdenesAmbulatorias($tnIngreso)
	{
		$cTipoDoc = '5000';
		$cTipoPrg = 'ORDA01A';

		if ($this->bLibro24hr){
			$this->oDb->where("oa.fecora*1000000+oa.horora>{$this->nFecHora24}");
		}
		$it = chr(25);
		$aDatos = $this->oDb
			->select('oa.corora, oa.viaora, oa.cciora, oa.ccoora')
			->select('oa.fecora, oa.horora')
			->select('IFNULL(m.regmed,\'\') AS regmed, IFNULL(m.nommed,\'\') AS nommed, IFNULL(m.nnomed,\'\') AS nnomed')
			->sum('CASE WHEN INDORA=4 THEN 1 ELSE 0 END', 'NUMDIETA')
			->sum("CASE WHEN (INDORA=14 OR (INDORA=1 AND (TRIM(SUBSTR(DESORA,30,4)) NOT IN ('','0')) )) THEN 1 ELSE 0 END", 'NUMINCAP')
			->sum('CASE WHEN INDORA=7 THEN 1 ELSE 0 END', 'NUMOTROS')
			->sum('CASE WHEN INDORA=8 THEN 1 ELSE 0 END', 'NUMMEDIC')
			->sum('CASE WHEN INDORA=9 AND DESORA LIKE \'%'.$it.'P'.$it.'%\' THEN 1 ELSE 0 END', 'NUMPROCE')
			->sum('CASE WHEN INDORA=9 AND DESORA LIKE \'%'.$it.'I'.$it.'%\' THEN 1 ELSE 0 END', 'NUMINSUM')
			->sum('CASE WHEN INDORA=10 THEN 1 ELSE 0 END', 'NUMINTER')
			->sum('CASE WHEN INDORA=11 THEN 1 ELSE 0 END', 'NUMRECGEN')
			->sum('CASE WHEN INDORA=12 THEN 1 ELSE 0 END', 'NUMRECNUTRIC')
			->sum('CASE WHEN INDORA=13 THEN 1 ELSE 0 END', 'NUMINSUMOS')
			->from('ordamb AS oa')
			->leftJoin('riargmn AS m', 'oa.usrora = m.usuari')
			->where(['oa.ingora'=>$tnIngreso])
			->groupBy('oa.tidora, oa.nidora, oa.corora, oa.plaora, oa.viaora,oa.cciora, oa.ccoora, oa.fecora, oa.horora, m.regmed, m.nommed, m.nnomed')
			->orderBy('oa.corora')
			->getAll('array');

		if ($this->oDb->numRows() > 0) {

			$lbAddTipo = false;
			foreach ($aDatos as $aDato) {
				$aDato = array_map('trim', $aDato);
				$cOrdenes = ''
					.($aDato['NUMMEDIC']>0 ? ' - Medicamentos' : '')
					.($aDato['NUMPROCE']>0 ? ' - Procedimientos' : '')
					.($aDato['NUMINSUM']>0 || $aDato['NUMINSUMOS']>0 ? ' - Insumos' : '')
					.($aDato['NUMINTER']>0 ? ' - Interconsultas' : '')
					.($aDato['NUMDIETA']>0 ? ' - Dieta' : '')
					.($aDato['NUMINCAP']>0 ? ' - Incapacidad' : '')
					.(($aDato['NUMRECGEN']>0 || $aDato['NUMRECNUTRIC']>0)? ' - Recomendaciones' : '')
					.($aDato['NUMOTROS']>0 ? ' - Otras' : '');

				if (strlen($cOrdenes)>0) {
					$lbAddTipo = true;
					$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
						'codCup'	=> '',
						'cnsDoc'	=> $aDato['CORORA'],
						'cnsCita'	=> $aDato['CCIORA'],
						'cnsCons'	=> $aDato['CCOORA'],
						'cnsEvo'	=> $this->cTodosOrdAmb,
						'fecha'		=> $aDato['FECORA']*1000000 + $aDato['HORORA'],
						'medRegMd'	=> $aDato['REGMED'],
						'medApell'	=> $aDato['NOMMED'],
						'medNombr'	=> $aDato['NNOMED'],
						'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'] . $cOrdenes,
						'codvia'	=> $aDato['VIAORA'],
						'sechab'	=> '',
					]);
				}
			}

			if ($lbAddTipo) {
				$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
					'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
					'tipoPrg' => $cTipoPrg,
					'numRows' => $this->oDb->numRows(),
				];
			}
		}
	}


	/*
	 *	Obtener Escala Nihss
	 */
	public function consultarEscalaNihss($tnIngreso)
	{
		$cTipoDoc = '3900';
		$cTipoPrg = 'ESCNIHSS';

		if ($this->bLibro24hr){
			$this->oDb->where("e.feceni*1000000+e.horeni>{$this->nFecHora24}");
		}
		$aDatos = $this->oDb->distinct()
			->select('E.TIPENI,E.CCOENI,E.FECENI,E.HORENI')
			->select('IFNULL(M.REGMED,\'\') AS REGMED, IFNULL(M.NOMMED,\'\') AS NOMMED, IFNULL(M.NNOMED,\'\') AS NNOMED')
			->from('ESCNIH AS E')
			->leftJoin('RIARGMN AS M', 'E.USRENI = M.USUARI')
			->where(['E.INGENI'=>$tnIngreso])
			->getAll('array');

		if ($this->oDb->numRows() > 0)
			$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
				'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
				'tipoPrg' => $cTipoPrg,
				'numRows' => $this->oDb->numRows(),
				];

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
				'codCup'	=> '',
				'cnsDoc'	=> $aDato['TIPENI'].'-'.$aDato['CCOENI'],
				'cnsCita'	=> '',
				'cnsCons'	=> '',
				'cnsEvo'	=> '',
				'fecha'		=> $aDato['FECENI']*1000000 + $aDato['HORENI'],
				'medRegMd'	=> $aDato['REGMED'],
				'medApell'	=> $aDato['NOMMED'],
				'medNombr'	=> $aDato['NNOMED'],
				'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'],
				'codvia'	=> $this->consultaViaFecha($aDato['FECENI'], $aDato['HORENI']),
				'sechab'	=> '',
			]);
		}
	}


	/*
	 *	Obtener Notas de Enfermería
	 */
	public function consultarEnfNotas($tnIngreso)
	{
		$cTipoDoc = '2910';
		$cTipoPrg = 'EF0017';

		if ($this->bLibro24hr){
			$this->oDb->where("n.fecnot*1000000+n.hornot>{$this->nFecHora24}");
		}
		$aDatos = $this->oDb
			->select('n.connot, n.fecnot, n.hornot, n.scanot, n.ncanot')
			->select('IFNULL(m.regmed,\'\') AS regmed, IFNULL(m.nommed,\'\') AS nommed, IFNULL(m.nnomed,\'\') AS nnomed')
			->from('ncsnot AS n')
			->leftJoin('riargmn AS m', 'n.usrnot = m.usuari')
			->where(['n.ingnot'=>$tnIngreso])
			->where('n.connot > 0 AND n.fecnot > 0')
			->where('( n.pgmnot<>\'ING001\' OR ( n.pgmnot=\'ING001\' AND n.ntanot=\'S\' ) )')
/*
			->grupo(function() use($db) {
					$db
					->grupo(function() use($db) {
						$db->where('n.pgmnot', '<>', 'ING001');
					})
					->grupo(function() use($db) {
						$db->orWhere(['n.pgmnot'=>'ING001','n.ntanot'=>'S']);
					});
				})
*/
			->getAll('array');

		if ($this->oDb->numRows() > 0)
			$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
				'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
				'tipoPrg' => $cTipoPrg,
				'numRows' => $this->oDb->numRows(),
				];

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
				'codCup'	=> '',
				'cnsDoc'	=> $aDato['CONNOT'],
				'cnsCita'	=> '',
				'cnsCons'	=> '',
				'cnsEvo'	=> '',
				'fecha'		=> $aDato['FECNOT']*1000000 + $aDato['HORNOT'],
				'medRegMd'	=> $aDato['REGMED'],
				'medApell'	=> $aDato['NOMMED'],
				'medNombr'	=> $aDato['NNOMED'],
				'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'],
				'codvia'	=> $this->consultaViaFecha($aDato['FECNOT'], $aDato['HORNOT']),
				'sechab'	=> $aDato['SCANOT'].'-'.$aDato['NCANOT'],
			]);
		}
	}


	/*
	 *	Obtener registros de Administración de Medicamentos
	 */
	public function consultarEnfAdministraMed($tnIngreso)
	{
		$cTipoDoc = '2920';
		$cTipoPrg = 'EFADMMED';

		if ($this->bLibro24hr){
			$this->oDb->where("fepadm*1000000+hdpadm>{$this->nFecHora24}");
		}
		if ($this->cTipoFechaEnf=='MAX') {
			$this->oDb->max('fepadm*1000000+hdpadm','mfechr');
		} else {
			$this->oDb->min('fepadm*1000000+hdpadm','mfechr');
		}
		$aDatos = $this->oDb
			->select('CASE WHEN hdpadm<070000 THEN TO_DATE(CHAR(fepadm),\'YYYYMMDD\')-1 DAYS ELSE TO_DATE(CHAR(fepadm),\'YYYYMMDD\') END AS fechaM')
			->from('enadmmd')
			->where(['ingadm'=>$tnIngreso])
			->where('feaadm', '>', 0)
			->groupBy('CASE WHEN hdpadm<070000 THEN TO_DATE(CHAR(fepadm),\'YYYYMMDD\')-1 DAYS ELSE TO_DATE(CHAR(fepadm),\'YYYYMMDD\') END')
			->getAll('array');

		if ($this->oDb->numRows() > 0)
			$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
				'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
				'tipoPrg' => $cTipoPrg,
				'numRows' => $this->oDb->numRows(),
				];

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			$lnFecha = substr($aDato['MFECHR'],0,8);
			$lnHora = substr($aDato['MFECHR'],8,6);
			$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
				'codCup'	=> '',
				'cnsDoc'	=> str_replace('-','',substr($aDato['FECHAM'],0,10)),
				'cnsCita'	=> '',
				'cnsCons'	=> '',
				'cnsEvo'	=> '',
				'fecha'		=> $aDato['MFECHR'],
				'medRegMd'	=> '',
				'medApell'	=> '',
				'medNombr'	=> '',
				'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'],
				'codvia'	=> $this->consultaViaFecha($lnFecha, $lnHora),
				'sechab'	=> $this->consultaHabFecha($lnFecha, $lnHora),
			]);
		}
	}


	/*
	 *	Obtener registros de Balance de Líquidos
	 */
	public function consultarEnfBalanceLiq($tnIngreso)
	{
		$cTipoDoc = '2930';
		$cTipoPrg = 'EFBALLIQ';

		if ($this->bLibro24hr){
			$this->oDb->where("fdibaq*1000000+hdibaq>{$this->nFecHora24}");
		}
		if ($this->cTipoFechaEnf=='MAX') {
			$this->oDb->max('fdibaq*1000000+hdibaq','mfechr');
		} else {
			$this->oDb->min('fdibaq*1000000+hdibaq','mfechr');
		}
		$aDatos = $this->oDb
			->select('CASE WHEN hdibaq<070000 THEN TO_DATE(CHAR(fdibaq),\'YYYYMMDD\')-1 DAYS ELSE TO_DATE(CHAR(fdibaq),\'YYYYMMDD\') END AS fechaM')
			->from('enbalq')
			->where(['ingbaq'=>$tnIngreso])->where('fdibaq>0')
			->groupBy('ingbaq, CASE WHEN hdibaq<070000 THEN TO_DATE(CHAR(fdibaq),\'YYYYMMDD\')-1 DAYS ELSE TO_DATE(CHAR(fdibaq),\'YYYYMMDD\') END')
			->getAll('array');

		if ($this->oDb->numRows() > 0)
			$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
				'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
				'tipoPrg' => $cTipoPrg,
				'numRows' => $this->oDb->numRows(),
				];

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			$lnFecha = substr($aDato['MFECHR'],0,8);
			$lnHora = substr($aDato['MFECHR'],8,6);
			$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
				'codCup'	=> '',
				'cnsDoc'	=> str_replace('-','',substr($aDato['FECHAM'],0,10)),
				'cnsCita'	=> '',
				'cnsCons'	=> '',
				'cnsEvo'	=> '',
				'fecha'		=> $aDato['MFECHR'],
				'medRegMd'	=> '',
				'medApell'	=> '',
				'medNombr'	=> '',
				'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'],
				'codvia'	=> $this->consultaViaFecha($lnFecha, $lnHora),
				'sechab'	=> $this->consultaHabFecha($lnFecha, $lnHora),
			]);
		}
	}


	/*
	 *	Obtener registros de Control de Glucometrías
	 */
	public function consultarEnfCtrlGlucom($tnIngreso)
	{
		$cTipoDoc = '2940';
		$cTipoPrg = 'EFCTRGLU';

		if ($this->bLibro24hr){
			$this->oDb->where("fdiglu*1000000+hdiglu>{$this->nFecHora24}");
		}
		if ($this->cTipoFechaEnf=='MAX') {
			$this->oDb->max('fdiglu*1000000+hdiglu','mfechr');
		} else {
			$this->oDb->min('fdiglu*1000000+hdiglu','mfechr');
		}
		$aDatos = $this->oDb
			->select('CASE WHEN hdiglu<070000 THEN TO_DATE(CHAR(fdiglu),\'YYYYMMDD\')-1 DAYS ELSE TO_DATE(CHAR(fdiglu),\'YYYYMMDD\') END AS fechaM')
			->from('engluco')
			->where(['ingglu'=>$tnIngreso])
			->groupBy('CASE WHEN hdiglu<070000 THEN TO_DATE(CHAR(fdiglu),\'YYYYMMDD\')-1 DAYS ELSE TO_DATE(CHAR(fdiglu),\'YYYYMMDD\') END')
			->getAll('array');

		if ($this->oDb->numRows() > 0)
			$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
				'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
				'tipoPrg' => $cTipoPrg,
				'numRows' => $this->oDb->numRows(),
				];

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			$lnFecha = substr($aDato['MFECHR'],0,8);
			$lnHora = substr($aDato['MFECHR'],8,6);
			$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
				'codCup'	=> '',
				'cnsDoc'	=> str_replace('-','',substr($aDato['FECHAM'],0,10)),
				'cnsCita'	=> '',
				'cnsCons'	=> '',
				'cnsEvo'	=> '',
				'fecha'		=> $aDato['MFECHR'],
				'medRegMd'	=> '',
				'medApell'	=> '',
				'medNombr'	=> '',
				'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'],
				'codvia'	=> $this->consultaViaFecha($lnFecha, $lnHora),
				'sechab'	=> $this->consultaHabFecha($lnFecha, $lnHora),
			]);
		}
	}


	/*
	 *	Obtener registros de Control Neurológico
	 */
	public function consultarEnfCtrlNeuro($tnIngreso)
	{
		$cTipoDoc = '2950';
		$cTipoPrg = 'EFCTRNEU';

		if ($this->bLibro24hr){
			$this->oDb->where("fcrnec*1000000+hrrnec>{$this->nFecHora24}");
		}
		if ($this->cTipoFechaEnf=='MAX') {
			$this->oDb->max('fcrnec*1000000+hrrnec','mfechr');
		} else {
			$this->oDb->min('fcrnec*1000000+hrrnec','mfechr');
		}
		$aDatos = $this->oDb
			->select('CASE WHEN hrrnec<070000 THEN TO_DATE(CHAR(fcrnec),\'YYYYMMDD\')-1 DAYS ELSE TO_DATE(CHAR(fcrnec),\'YYYYMMDD\') END AS fechaM')
			->from('enneurc')
			->where(['ingnec'=>$tnIngreso, 'clnnec'=>1])
			->groupBy('CASE WHEN hrrnec<070000 THEN TO_DATE(CHAR(fcrnec),\'YYYYMMDD\')-1 DAYS ELSE TO_DATE(CHAR(fcrnec),\'YYYYMMDD\') END')
			->getAll('array');

		if ($this->oDb->numRows() > 0)
			$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
				'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
				'tipoPrg' => $cTipoPrg,
				'numRows' => $this->oDb->numRows(),
				];

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			$lnFecha = substr($aDato['MFECHR'],0,8);
			$lnHora = substr($aDato['MFECHR'],8,6);
			$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
				'codCup'	=> '',
				'cnsDoc'	=> str_replace('-','',substr($aDato['FECHAM'],0,10)),
				'cnsCita'	=> '',
				'cnsCons'	=> '',
				'cnsEvo'	=> '',
				'fecha'		=> $aDato['MFECHR'],
				'medRegMd'	=> '',
				'medApell'	=> '',
				'medNombr'	=> '',
				'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'],
				'codvia'	=> $this->consultaViaFecha($lnFecha, $lnHora),
				'sechab'	=> $this->consultaHabFecha($lnFecha, $lnHora),
			]);
		}
	}


	/*
	 *	Obtener registros de Riesgo de Caida
	 */
	public function consultarEnfRiesgoCaida($tnIngreso)
	{
		$cTipoDoc = '2960';
		$cTipoPrg = 'EFRIECAI';

		if ($this->bLibro24hr){
			$this->oDb->where("fdicai*1000000+hdicai>{$this->nFecHora24}");
		}
		if ($this->cTipoFechaEnf=='MAX') {
			$this->oDb->max('fdicai*1000000+hdicai','mfechr');
		} else {
			$this->oDb->min('fdicai*1000000+hdicai','mfechr');
		}
/*
		$aDatos = $this->oDb
			->select('CASE WHEN hdicai<070000 THEN TO_DATE(CHAR(fdicai),\'YYYYMMDD\')-1 DAYS ELSE TO_DATE(CHAR(fdicai),\'YYYYMMDD\') END AS fechaM')
			->from('encaida')
			->where(['ingcai'=>$tnIngreso])
			->groupBy('CASE WHEN hdicai<070000 THEN TO_DATE(CHAR(fdicai),\'YYYYMMDD\')-1 DAYS ELSE TO_DATE(CHAR(fdicai),\'YYYYMMDD\') END')
			->getAll('array');
*/
		$aDatos = $this->oDb
			->select('fdicai AS fechaM')
			->from('encaida')
			->where(['ingcai'=>$tnIngreso])
			->groupBy('fdicai')
			->getAll('array');

		if ($this->oDb->numRows() > 0)
			$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
				'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
				'tipoPrg' => $cTipoPrg,
				'numRows' => $this->oDb->numRows(),
				];

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			$lnFecha = substr($aDato['MFECHR'],0,8);
			$lnHora = substr($aDato['MFECHR'],8,6);
			$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
				'codCup'	=> '',
				'cnsDoc'	=> str_replace('-','',substr($aDato['FECHAM'],0,10)),
				'cnsCita'	=> '',
				'cnsCons'	=> '',
				'cnsEvo'	=> '',
				'fecha'		=> $aDato['MFECHR'],
				'medRegMd'	=> '',
				'medApell'	=> '',
				'medNombr'	=> '',
				'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'],
				'codvia'	=> $this->consultaViaFecha($lnFecha, $lnHora),
				'sechab'	=> $this->consultaHabFecha($lnFecha, $lnHora),
			]);
		}
	}



	/*
	 *	Obtener registros de Riesgo de Fuga
	 */
	public function consultarEnfRiesgoFuga($tnIngreso)
	{
		$cTipoDoc = '2965';
		$cTipoPrg = 'FRMFUGA';

		if ($this->bLibro24hr){
			$this->oDb->where("fdifug*1000000+hdifug>{$this->nFecHora24}");
		}
		if ($this->cTipoFechaEnf=='MAX') {
			$this->oDb->max('fdifug*1000000+hdifug','mfechr');
		} else {
			$this->oDb->min('fdifug*1000000+hdifug','mfechr');
		}

		$aDatos = $this->oDb
			->select('fdifug AS fechaM')
			->from('enffuga')
			->where(['ingfug'=>$tnIngreso])
			->groupBy('fdifug')
			->getAll('array');

		if ($this->oDb->numRows() > 0)
			$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
				'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
				'tipoPrg' => $cTipoPrg,
				'numRows' => $this->oDb->numRows(),
				];

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			$lnFecha = substr($aDato['MFECHR'],0,8);
			$lnHora = substr($aDato['MFECHR'],8,6);
			$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
				'codCup'	=> '',
				'cnsDoc'	=> str_replace('-','',substr($aDato['FECHAM'],0,10)),
				'cnsCita'	=> '',
				'cnsCons'	=> '',
				'cnsEvo'	=> '',
				'fecha'		=> $aDato['MFECHR'],
				'medRegMd'	=> '',
				'medApell'	=> '',
				'medNombr'	=> '',
				'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'],
				'codvia'	=> $this->consultaViaFecha($lnFecha, $lnHora),
				'sechab'	=> $this->consultaHabFecha($lnFecha, $lnHora),
			]);
		}
	}


	/*
	 *	Obtener registros de Escala Nas
	 */
	public function consultarEnfEscalaNas($tnIngreso)
	{
		$cTipoDoc = '2970';
		$cTipoPrg = 'NAS002';

		if ($this->bLibro24hr){
			$this->oDb->where("e.fecnas*1000000+e.hornas>{$this->nFecHora24}");
		}
		$aDatos = $this->oDb
			->select('e.connas, e.regnas, e.fecnas, e.hornas')
			->select('IFNULL(m.regmed,\'\') AS regmed, IFNULL(m.nommed,\'\') AS nommed, IFNULL(m.nnomed,\'\') AS nnomed')
			->from('enfnas AS e')
			->leftJoin('riargmn AS m', 'e.usunas = m.usuari')
			->where(['ingnas'=>$tnIngreso])
			->orderBy('fecnas, hornas')
			->getAll('array');

		if ($this->oDb->numRows() > 0)
			$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
				'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
				'tipoPrg' => $cTipoPrg,
				'numRows' => $this->oDb->numRows(),
				];

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
				'codCup'	=> '',
				'cnsDoc'	=> $aDato['REGNAS'],
				'cnsCita'	=> $aDato['CONNAS'],
				'cnsCons'	=> '',
				'cnsEvo'	=> '',
				'fecha'		=> $aDato['FECNAS']*1000000 + $aDato['HORNAS'],
				'medRegMd'	=> $aDato['REGMED'],
				'medApell'	=> $aDato['NOMMED'],
				'medNombr'	=> $aDato['NNOMED'],
				'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'],
				'codvia'	=> $this->consultaViaFecha($aDato['FECNAS'], $aDato['HORNAS']),
				'sechab'	=> $this->consultaHabFecha($aDato['FECNAS'], $aDato['HORNAS']),
			]);
		}
	}


	/*
	 *	Obtener registros de Epidemiología
	 */
	public function consultarEnfEpidemiologia($tnIngreso)
	{
		$cTipoDoc = '2980';
		$cTipoPrg = 'EPIDE001';

		if ($this->bLibro24hr){
			$this->oDb->where("fecepi*1000000+horepi>{$this->nFecHora24}");
		}
		$aDatos = $this->oDb->distinct()
			->select('conepi, fecepi, horepi')
			->from('epirgs')
			->where(['ingepi'=>$tnIngreso])
			->orderBy('fecepi, horepi')
			->getAll('array');

		if ($this->oDb->numRows() > 0)
			$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
				'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
				'tipoPrg' => $cTipoPrg,
				'numRows' => $this->oDb->numRows(),
				];

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
				'codCup'	=> '',
				'cnsDoc'	=> $aDato['CONEPI'],
				'cnsCita'	=> '',
				'cnsCons'	=> '',
				'cnsEvo'	=> '',
				'fecha'		=> $aDato['FECEPI']*1000000 + $aDato['HOREPI'],
				'medRegMd'	=> '',
				'medApell'	=> '',
				'medNombr'	=> '',
				'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'],
				'codvia'	=> $this->consultaViaFecha($aDato['FECEPI'], $aDato['HOREPI']),
				'sechab'	=> $this->consultaHabFecha($aDato['FECEPI'], $aDato['HOREPI']),
			]);
		}
	}


	/*
	 *	Obtener registros de enfermería sensórica
	 */
	public function consultarEnfSensorica($tnIngreso)
	{
		$cTipoDoc = '2990';
		$cTipoPrg = 'ENFSENSO';

		if ($this->bLibro24hr){
			$this->oDb->where("E.FECSNR*1000000+E.HORSNR>{$this->nFecHora24}");
		}
		//	if ($this->cTipoFechaEnf=='MAX') {
		//		$this->oDb->max('E.FECSNR*1000000+E.HORSNR','mfechr');
		//	} else {
		//		$this->oDb->min('E.FECSNR*1000000+E.HORSNR','mfechr');
		//	}

		$aDatos = $this->oDb
			->select('E.NOTSNR AS NUMNOTA, E.USCSNR USUARIO, M.REGMED, M.NNOMED, M.NOMMED')
			->min('E.FECSNR*1000000+E.HORSNR','MFECHR')
			->from('ENFSNSR E')
			->leftJoin('RIARGMN AS M', 'E.USCSNR = M.USUARI')
			->where(['E.INGSNR'=>$tnIngreso,'E.TIPSNR'=>'NOTDE'])
			->groupBy('E.NOTSNR, E.USCSNR, M.REGMED, M.NNOMED, M.NOMMED')
			->getAll('array');

		if ($this->oDb->numRows() > 0)
			$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
				'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
				'tipoPrg' => $cTipoPrg,
				'numRows' => $this->oDb->numRows(),
				];

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			$lnFecha = substr($aDato['MFECHR'],0,8);
			$lnHora = substr($aDato['MFECHR'],8,6);
			$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
				'codCup'	=> '',
				'cnsDoc'	=> $aDato['NUMNOTA'],
				'cnsCita'	=> '',
				'cnsCons'	=> '',
				'cnsEvo'	=> '',
				'fecha'		=> $aDato['MFECHR'],
				'medRegMd'	=> $aDato['REGMED'],
				'medApell'	=> $aDato['NOMMED'],
				'medNombr'	=> $aDato['NNOMED'],
				'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'],
				'codvia'	=> $this->consultaViaFecha($lnFecha, $lnHora),
				'sechab'	=> $this->consultaHabFecha($lnFecha, $lnHora),
			]);
		}
	}


	/*
	 *	Obtener Evoluciones de Fisioterapia
	 */
	public function consultarEvolucionesFisio($tnIngreso)
	{
		$cTipoDoc = '4200';
		$cTipoPrg = 'FRMFISEV';

		if ($this->bLibro24hr){
			$this->oDb->where("e.fecefi*1000000+e.horefi>{$this->nFecHora24}");
		}
		$aDatos = $this->oDb
			->select('e.conefi, e.fecefi, e.horefi')
			->select('IFNULL(m.regmed,\'\') AS regmed, IFNULL(m.nommed,\'\') AS nommed, IFNULL(m.nnomed,\'\') AS nnomed')
			->from('evofis AS e')
			->leftJoin('riargmn AS m', 'e.usrefi = m.usuari')
			->where(['e.ingefi'=>$tnIngreso, 'e.cnlefi'=>1])
			->getAll('array');

		if ($this->oDb->numRows() > 0)
			$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
				'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
				'tipoPrg' => $cTipoPrg,
				'numRows' => $this->oDb->numRows(),
				];

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
				'codCup'	=> '',
				'cnsDoc'	=> $aDato['CONEFI'],
				'cnsCita'	=> '',
				'cnsCons'	=> '',
				'cnsEvo'	=> '',
				'fecha'		=> $aDato['FECEFI']*1000000 + $aDato['HOREFI'],
				'medRegMd'	=> $aDato['REGMED'],
				'medApell'	=> $aDato['NOMMED'],
				'medNombr'	=> $aDato['NNOMED'],
				'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'],
				'codvia'	=> $this->consultaViaFecha($aDato['FECEFI'], $aDato['HOREFI']),
				'sechab'	=> $this->consultaHabFecha($aDato['FECEFI'], $aDato['HOREFI']),
			]);
		}
	}


	/*
	 *	Obtener registro de Ingreso a la UCV
	 */
	public function consultarIngresoUCV($tnIngreso)
	{
		$cTipoDoc = '4300';
		$cTipoPrg = 'FRMNOTAV';

		if ($this->bLibro24hr){
			$this->oDb->where("h.fecvcv*1000000+h.horvcv>{$this->nFecHora24}");
		}
		$aDatos = $this->oDb
			->select('h.convcv, h.fecvcv, h.horvcv')
			->select('substr(h.desvcv, 7, 2) as seccion, substr(h.desvcv, 10, 4) as habita')
			->select('IFNULL(m.regmed,\'\') AS regmed, IFNULL(m.nommed,\'\') AS nommed, IFNULL(m.nnomed,\'\') AS nnomed')
			->from('hisucv AS h')
			->leftJoin('riargmn AS m', 'h.usrvcv = m.usuari')
			->where(['h.ingvcv'=>$tnIngreso, 'h.indvcv'=>2000, 'h.cnlvcv'=>1])
			->getAll('array');

		if ($this->oDb->numRows() > 0)
			$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
				'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
				'tipoPrg' => $cTipoPrg,
				'numRows' => $this->oDb->numRows(),
				];

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
				'codCup'	=> '',
				'cnsDoc'	=> $aDato['CONVCV'],
				'cnsCita'	=> '',
				'cnsCons'	=> '',
				'cnsEvo'	=> '',
				'fecha'		=> $aDato['FECVCV']*1000000 + $aDato['HORVCV'],
				'medRegMd'	=> $aDato['REGMED'],
				'medApell'	=> $aDato['NOMMED'],
				'medNombr'	=> $aDato['NNOMED'],
				'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'],
				'codvia'	=> '05',
				'sechab'	=> $aDato['SECCION'].'-'.$aDato['HABITA'],
			]);
		}
	}


	/*
	 *	Obtener registro de Toma de Signos Vitales / Alertas Tempranas
	 */
	public function consultarSignosVitales($tnIngreso)
	{
		$cTipoDoc = '6000';
		$cTipoPrg = 'ALETEMP';

		if ($this->bLibro24hr){
			$this->oDb->where("A.FECALE*1000000+A.HOCALE>{$this->nFecHora24}");
		}
		$aDatos = $this->oDb
			->select('A.CONALE,A.SECCIN,A.HABITA,A.FECALE,A.HOCALE')
			->SELECT('IFNULL(M.REGMED,\'\') AS REGMED, IFNULL(M.NOMMED,\'\') AS NOMMED, IFNULL(M.NNOMED,\'\') AS NNOMED')
			->from('ALETEMP AS A')
			->leftJoin('RIARGMN AS M', 'A.USCALE = M.USUARI')
			->where(['A.NIGING'=>$tnIngreso])
			->getAll('array');

		if ($this->oDb->numRows() > 0)
			$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
				'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
				'tipoPrg' => $cTipoPrg,
				'numRows' => $this->oDb->numRows(),
				];

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
				'codCup'	=> '',
				'cnsDoc'	=> $aDato['CONALE'],
				'cnsCita'	=> '',
				'cnsCons'	=> '',
				'cnsEvo'	=> '',
				'fecha'		=> $aDato['FECALE']*1000000 + $aDato['HOCALE'],
				'medRegMd'	=> $aDato['REGMED'],
				'medApell'	=> $aDato['NOMMED'],
				'medNombr'	=> $aDato['NNOMED'],
				'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'],
				'codvia'	=> '05',
				'sechab'	=> $aDato['SECCIN'].'-'.$aDato['HABITA'],
			]);
		}
	}


	/*
	 *	Obtener Notas Aclaratorias de Documentos
	 */
	public function consultarNotasDocumentos($tnIngreso)
	{
		$cTipoDoc = '4800';
		$cTipoPrg = 'EPI010';

		if ($this->bLibro24hr){
			$this->oDb->where("n.fecnim*1000000+n.hornim>{$this->nFecHora24}");
		}
		$aDatos = $this->oDb
			->select('n.cnonim, n.ccinim, n.pronim, n.op2nim, n.fecnim, n.hornim')
			->select('IFNULL(m.regmed,\'\') AS regmed, IFNULL(m.nommed,\'\') AS nommed, IFNULL(m.nnomed,\'\') AS nnomed')
			->from('notima AS n')
			->leftJoin('riargmn AS m', 'n.usrnim = m.usuari')
			->where(['n.ingnim'=>$tnIngreso, 'n.cnlnim'=>1, 'n.op1nim'=>''])
			->getAll('array');

		if ($this->oDb->numRows() > 0)
			$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
				'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
				'tipoPrg' => $cTipoPrg,
				'numRows' => $this->oDb->numRows(),
				];

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
				'codCup'	=> '',
				'cnsDoc'	=> $aDato['CNONIM'],
				'cnsCita'	=> $aDato['CCINIM'],
				'cnsCons'	=> $aDato['PRONIM'],
				'cnsEvo'	=> $aDato['OP2NIM'],
				'fecha'		=> $aDato['FECNIM']*1000000 + $aDato['HORNIM'],
				'medRegMd'	=> $aDato['REGMED'],
				'medApell'	=> $aDato['NOMMED'],
				'medNombr'	=> $aDato['NNOMED'],
				'descrip'	=> $this->aDescTipos[$cTipoDoc]['Descr'],
				'codvia'	=> '',
				'sechab'	=> '',
			]);
		}
	}


	/*
	 *	Obtener documentos Adjuntos
	 */
	public function consultarAdjuntos($tnIngreso, $taCondiciones=[])
	{
		$cTipoDoc = '9000';
		$cTipoPrg = 'ADJUNTOS';

		$this->oDb
			->where(['d.nigadj'=>$tnIngreso])
			->where('d.estdoc', '<>', 'I');
		if ($this->bLibro24hr){
			$this->oDb->where("d.fecadj*1000000+d.horadj>{$this->nFecHora24}");
		}
		if ((is_array($taCondiciones) && count($taCondiciones)>0) || (is_string($taCondiciones) && strlen($taCondiciones)>0)) {
			$this->oDb->where($taCondiciones);
		}

		$aDatos = $this->oDb
			->select('ROW_NUMBER() OVER (ORDER BY d.fecadj, d.horadj) AS Consec')
			->select('t.de1tma AS DesTipo, d.srcdoc AS FNombre, d.outdoc AS FRuta, t.op3tma AS TipAdj')
			->select('d.fecadj AS Fecha, d.horadj AS Hora')
			->select('d.regmed, IFNULL(m.nommed,\'\') AS nommed, IFNULL(m.nnomed,\'\') AS nnomed')
			->from('HcAdjuntos AS d')
			->leftJoin('riargmn AS m', 'd.regmed=m.regmed')
			->innerJoin('tabmae AS t', 't.tiptma=\'HCADJUN\' AND t.cl1tma=\'TIPOS\' AND d.tipadj=t.cl2tma')
			->orderBy('CASE WHEN d.fecadj=0 THEN 99999999 ELSE d.fecadj END')
			->orderBy('CASE WHEN d.horadj=0 THEN 999999 ELSE d.horadj END')
			->getAll('array');

		foreach ($aDatos as $aDato) {
			$cTipoDoc = $aDato['TIPADJ'].'';
			if (array_key_exists($cTipoDoc, $this->aIngresosTipos[$tnIngreso])) {
				$this->aIngresosTipos[$tnIngreso][$cTipoDoc]['numRows']++;
			} else {
				$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
					'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
					'tipoPrg' => $cTipoPrg,
					'numRows' => 1,
					];
			}

			$aDato = array_map('trim', $aDato);
			$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
				'codCup'	=> '',
				'cnsDoc'	=> $aDato['CONSEC'],
				'cnsCita'	=> '',
				'cnsCons'	=> $aDato['FRUTA'],
				'cnsEvo'	=> $aDato['FNOMBRE'],
				'fecha'		=> $aDato['FECHA']*1000000 + $aDato['HORA'],
				'medRegMd'	=> $aDato['REGMED'],
				'medApell'	=> $aDato['NOMMED'],
				'medNombr'	=> $aDato['NNOMED'],
				'descrip'	=> $aDato['DESTIPO'],
				'codvia'	=> $this->consultaViaFecha($aDato['FECHA'], $aDato['HORA']),
				'sechab'	=> $this->consultaHabFecha($aDato['FECHA'], $aDato['HORA']),
			]);
		}
	}


	/*
	 *	Obtener registros de Cambio de Datos de paciente
	 */
	public function consultarCambioDatos($tnIngreso)
	{
		$cTipoDoc = '8800';
		$cTipoPrg = 'CAMB_DAT';

		if ($this->bLibro24hr){
			$this->oDb->where("d.fcrbit*1000000+d.hcrbit>{$this->nFecHora24}");
		}
		$aDatos = $this->oDb
			->select('ROW_NUMBER() OVER (ORDER BY d.fcrbit, d.hcrbit) AS Consec')
			->select('d.tipreg AS CodTipo, d.fcrbit AS Fecha, d.hcrbit AS Hora')
			->select('d.tipreg AS TipoReg, t.de1tma AS DscTipoReg')
			->select('IFNULL(m.regmed,\'\') AS regmed, IFNULL(m.nommed,\'\') AS nommed, IFNULL(m.nnomed,\'\') AS nnomed')
			->from('IdPacBit AS d')
			->leftJoin('riargmn AS m', 'd.ucrbit = m.usuari')
			->innerJoin('tabmae AS t', 't.tiptma=\'DATING\' AND t.cl1tma=\'REGISTRO\' AND SUBSTR(t.cl2tma,1,6)=\'010101\'
							AND t.cl3tma=\'\' AND t.op1tma=\'1\' AND d.tipreg = t.de2tma')
			->where(['d.niging'=>$tnIngreso])
			->orderBy('d.fcrbit, d.hcrbit')
			->getAll('array');

		if ($this->oDb->numRows() > 0)
			$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
				'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
				'tipoPrg' => $cTipoPrg,
				'numRows' => $this->oDb->numRows(),
			];

		foreach ($aDatos as $aDato) {
			$aDato = array_map('trim', $aDato);
			$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
				'codCup'	=> '',
				'cnsDoc'	=> $aDato['CONSEC'],
				'cnsCita'	=> '',
				'cnsCons'	=> $aDato['CODTIPO'],
				'cnsEvo'	=> $aDato['TIPOREG'],
				'fecha'		=> $aDato['FECHA']*1000000 + $aDato['HORA'],
				'medRegMd'	=> $aDato['REGMED'],
				'medApell'	=> $aDato['NOMMED'],
				'medNombr'	=> $aDato['NNOMED'],
				'descrip'	=> $aDato['DSCTIPOREG'],
				'codvia'	=> '',
				'sechab'	=> '',
			]);
		}
	}

	public function consultarConsentimiento($tnIngreso)
	{
		$loConsentimiento = new \ApiConsentimientoInformado();

		$cTipoDoc = '9100';
		$cTipoPrg = 'CON_INF';

		$laResponse = $loConsentimiento->consultarConsentimientosPaciente($tnIngreso);

		if (count($laResponse) > 0)
		$this->aIngresosTipos[$tnIngreso][$cTipoDoc] = [
			'descDoc' => $this->aDescTipos[$cTipoDoc]['Descr'],
			'tipoPrg' => $cTipoPrg,
			'numRows' => count($laResponse),
		];
		if(!empty($laResponse)){
			foreach($laResponse as $aDato){
				$nFecha = (int) str_replace(":", "", str_replace(" ", "", str_replace("-", "",$aDato['fecha']))) ;
				$cNomMed = explode(' ', $aDato['nombreMedico']);
				$this->addDoc($tnIngreso, $cTipoDoc, $cTipoPrg, [
					'codCup'	=> '',
					'cnsDoc'	=> $aDato['idDocumento'],
					'cnsCita'	=> '',
					'cnsCons'	=> $aDato['tipoDocumento'],
					'cnsEvo'	=> $aDato['nombrePlantilla'],
					'fecha'		=> $nFecha,
					'medRegMd'	=> $aDato['rmMedico'],
					'medApell'	=> '',
					'medNombr'	=> $aDato['nombreMedico'],
					'descrip'	=> $aDato['nombrePlantilla'],
					'codvia'	=> '',
					'sechab'	=> '',
				]);
			}
		}
	}


// ****************************************************************** //
// ******************* FIN CONSULTA DE DOCUMENTOS ******************* //
// ****************************************************************** //


	/*
	 *	Consulta y Retorna los items que puede contener el árbol de un libro
	 */
	public function obtenerItemsTree()
	{
		$laReturn = [];
		$laTree = $this->oDb
			->select('TRIM(CL3TMA) AS CODIGO, TRIM(CL4TMA) AS PADRE, TRIM(DE1TMA) AS DESCRIP, TRIM(DE2TMA) AS FILTRO, TRIM(OP5TMA) AS ICON')
			->from('TABMAE')
			->where(['TIPTMA'=>'LIBROHC','CL1TMA'=>'LISTATRV','CL2TMA'=>'ITEM','ESTTMA'=>''])
			->where('CL3TMA', '<>', '')
			->orderBy('CL4TMA, CL3TMA')
			->getAll('array');

		return $laTree;
	}


	/*
	 *	Retorna los items que puede contener el árbol de un libro organizados
	 */
	public function obtenerItemsTreeNE()
	{
		$laTree = [];
		$laTipos = $this->obtenerItemsTree();
		$lnNumPadre = 0;
		$laPadres = [];
		foreach ($laTipos as $laTipo) {
			$laTemp = [
				'descripcion' => $laTipo['DESCRIP'],
				'filtro' => str_replace("'", '', $laTipo['FILTRO']),
				'icono' => $laTipo['ICON'],
			];
			if (strlen($laTipo['PADRE'])==0) {
				$laTree[$lnNumPadre] = $laTemp;
				$laPadres[$laTipo['CODIGO']] = $lnNumPadre;
				$lnNumPadre++;
			} else {
				$lnNum = $laPadres[$laTipo['PADRE']];
				$laTree[$lnNum]['hijos'][] = $laTemp;
			}
		}

		return $laTree;
	}


	/*
	 *	Validar si el usuario puede consultar un ingreso (por entidades)
	 */
	public function puedeVerDocsIngreso($tnIngreso=0)
	{
		// Validación parametrizada
		$lbConsulta = false;
		$lcCmd='$lbConsulta='.trim($this->oDb->obtenerTabMae1('DE2TMA || OP5TMA', 'LIBROHC', ['CL1TMA'=>'WEB','CL2TMA'=>'CONSULTA','ESTTMA'=>''], null, 'true')).';';
		eval($lcCmd);
		if ($lbConsulta===false) {
			return true;
		}
		$laEntidades=$_SESSION[HCW_NAME]->oUsuario->getEntidadesConsultaLibroHc();

		if ($tnIngreso!==$this->cSinIngreso && $tnIngreso!==0 && count($laEntidades)!==0) {
			// lista NITs de entidades autorizadas
			$laLstNitEnt = [];
			foreach ($laEntidades as $taEntidad) {
				// validar si el usuario puede consultar todas las entidades
				if (trim($laEntidades[0]['PLAN'])=='*') {
					return true;
				}
				$laLstNitEnt[] = $taEntidad['NIT'];
			}

			// valida entidades facturadas
			$laValida = $this->oDb
				->count('INGCAB', 'CUENTA')
				->from('FACCABF')
				->where(['INGCAB'=>$tnIngreso])
				->notWhere(['MA1CAB'=>'A'])
				->in('NITCAB', $laLstNitEnt)
				->getAll('array');
			if (is_array($laValida)) {
				if ($laValida[0]['CUENTA']>0) {
					return true;
				}
			}

			// valida entidades del ingreso
			$laValida = $this->oDb
				->count('NIGEPP', 'CUENTA')
				->from('RIAEPPHIS')
				->where(['NIGEPP'=>$tnIngreso])
				->in('ENTEPP', $laLstNitEnt)
				->getAll('array');
			if (is_array($laValida)) {
				if ($laValida[0]['CUENTA']>0) {
					return true;
				}
			}

			// valida entidad del ingreso
			$laValida = $this->oDb
				->count('ENTING', 'CUENTA')
				->from('RIAING')
				->where(['NIGING'=>$tnIngreso])
				->in('ENTING', $laLstNitEnt)
				->getAll('array');
			if (is_array($laValida)) {
				if ($laValida[0]['CUENTA']>0) {
					return true;
				}
			}
		}
		return false;
	}


	/*
	 *	Retorna lista de documentos para un ingreso / documento
	 */
	public function listarDocumentos($tnIngreso=0, $tcTipId='', $tnNumId=0, $tbNuevaEstructura=false)
	{
		$laRetorna['error'] = '';
		$lbContinuar = true;
		if ($tnIngreso!==0) {
			if ($tnIngreso>500000 && $tnIngreso<99999999) {
				// Si viene un número de ingreso valida que se pueda consultar
				$lbConsulta=false;
				$lcCmd='$lbConsulta='.trim($this->oDb->obtenerTabMae1('DE2TMA || OP5TMA', 'LIBROHC', ['CL1TMA'=>'WEB','CL2TMA'=>'CONSULTA','ESTTMA'=>''], null, 'true')).';';
				eval($lcCmd);
				if ($lbConsulta) {
					if (! $this->puedeVerDocsIngreso($tnIngreso)) {
						$laRetorna['error'] = "Entidad del ingreso $tnIngreso no permitida para el usuario.";
						$lbContinuar = false;
					}
				}
			} else {
				$laRetorna['error'] = "Número de ingreso $tnIngreso incorrecto.";
				$lbContinuar = false;
			}
		}

		if ($lbContinuar) {
			$this->cargarDatos($tnIngreso, $tcTipId, $tnNumId, ['fecha', 'descrip'], [SORT_DESC, SORT_ASC]);
			$laLista = $tbNuevaEstructura ? $this->obtenerDocumentosNE() : $this->obtenerDocumentos();

			if (count($laLista) > 0) {
				$laRetorna['documentos'] = $laLista;
				$laRetorna['tree'] = $tbNuevaEstructura ? $this->getIngresosTiposNE() : $this->getIngresosTipos();
				$laRetorna['ultimoIng'] = $this->getUltimoIngreso();
				$laRetorna['itemstree'] = $this->obtenerItemsTreeNE();
			} else {
				$laRetorna['error'] = 'No se encontraron documentos';
			}
		}

		return $laRetorna;
	}


	/*
	 *	Retorna lista de documentos en formato para consultar el contenido
	 */
	public function ultimasHoras($tnIngreso, $tcTipId, $tnNumId)
	{
		$laRetorna = ['error'=>'', 'datos'=>[]];

		// Valida que se pueda consultar el ingreso
		if ( $this->puedeVerDocsIngreso($tnIngreso) ) {
			$this->bLibro24hr = true;
			$this->cargarDatos($tnIngreso, '', 0, 'fecha', SORT_DESC, $lbObtenerDocs=true, $lbObtenerIngresos=false);
			$laListaDocs = $this->obtenerDocumentos();

			if (count($laListaDocs[$tnIngreso]) > 0) {
				$laDatosDoc = [];
				foreach($laListaDocs[$tnIngreso] as $laDoc){
					$laDatosDoc[] = [
						'nIngreso'		=> $tnIngreso,
						'cTipDocPac'	=> $tcTipId,
						'nNumDocPac'	=> $tnNumId,
						'cRegMedico'	=> $laDoc['medRegMd'],
						'cTipoDocum'	=> $laDoc['tipoDoc'],
						'cTipoProgr'	=> $laDoc['tipoPrg'],
						'tFechaHora'	=> $laDoc['fecha'],
						'nConsecCita'	=> $laDoc['cnsCita'],
						'nConsecCons'	=> $laDoc['cnsCons'],
						'nConsecEvol'	=> $laDoc['cnsEvo'],
						'nConsecDoc'	=> $laDoc['cnsDoc'],
						'cCUP'			=> $laDoc['codCup'],
						'cCodVia'		=> $laDoc['codvia'],
						'cSecHab'		=> $laDoc['sechab'],
					];
				}
				unset($laListaDocs);
				$laRetorna['datos'] = $laDatosDoc;

			} else {
				$laRetorna['error'] = 'No se encontraron documentos de las últimas 24hr.';
				$laRetorna['tipoerror'] = 'warning';
			}
		} else {
			$laRetorna['error'] = 'Entidad del ingreso no permitida para el usuario.';
			$laRetorna['tipoerror'] = 'danger';
		}

		return $laRetorna;
	}


	/*
	 *	Retorna lista de documentos en formato para consultar el contenido
	 */
	public function organizarDocumentos()
	{
		$laRta = [];
		foreach ($this->aDocumentos as $lnIngreso => $laDatos) {
			foreach ($laDatos as $taDato) {
				$laRta[] = [
					'nIngreso'		=> $lnIngreso ?? '0',
					'cTipDocPac'	=> $this->cTipoId,
					'nNumDocPac'	=> $this->nNumeroId,
					'cRegMedico'	=> $taDato['medRegMd']	?? '',
					'cTipoDocum'	=> $taDato['tipoDoc']	?? '',
					'cTipoProgr'	=> $taDato['tipoPrg']	?? '',
					'tFechaHora'	=> AplicacionFunciones::formatFechaHora('fechahora', $taDato['fecha']??'0'),
					'nConsecCita'	=> $taDato['cnsCita']	?? '0',
					'nConsecCons'	=> $taDato['cnsCons']	?? '0',
					'nConsecEvol'	=> $taDato['cnsEvo']	?? '0',
					'nConsecDoc'	=> $taDato['cnsDoc']	?? '0',
					'cCUP'			=> $taDato['codCup']	?? '',
					'cCodVia'		=> $taDato['codvia']	?? '',
					'cSecHab'		=> $taDato['sechab']	?? '',
				];
			}
		}
		return $laRta;
	}


	/*
	 *	Limpiar lista de documentos
	 */
	public function limpiarDocumentos()
	{
		$this->aDocumentos = [];
	}

	/*
	 *	Retorna los documentos del paciente agrupados por ingreso
	 */
	public function obtenerDocumentos()
	{
		return $this->aDocumentos;
	}

	/*
	 *	Retorna los documentos del paciente agrupados por ingreso
	 */
	public function obtenerDocumentosNE()
	{
		$laReturn = [];
		foreach ($this->aDocumentos as $lnIngreso => $laDocumentos) {
			$laReturn[] = [
				'ingreso' => $lnIngreso,
				'documentos' => $laDocumentos,
			];
		}

		return $laReturn;
	}

	/*
	 *	Retorna arreglo con tipos agrupados por ingreso
	 */
	public function getIngresosTipos()
	{
		return $this->aIngresosTipos;
	}

	/*
	 *	Retorna arreglo con tipos agrupados por ingreso
	 */
	public function getIngresosTiposNE()
	{
		$laReturn = [];
		foreach ($this->aIngresosTipos as $lnIngreso => $laTiposDoc) {
			$laTemp = [
				'ingreso' => $lnIngreso,
				'tiposDocumento' => [],
			];
			foreach ($laTiposDoc as $lnCodTipo => $laTipoDoc) {
				$laTemp['tiposDocumento'][] = array_merge(['codigo'=>$lnCodTipo], $laTipoDoc);
			}
			$laReturn[] = $laTemp;
		}
		return $laReturn;
	}

	/*
	 *	Retorna tipo de documento del paciente
	 */
	public function cTipoId()
	{
		return $this->cTipoId;
	}

	/*
	 *	Retorna número de documento del paciente
	 */
	public function nNumeroId()
	{
		return $this->nNumeroId;
	}

	/*
	 *	Retorna datos del último ingreso del paciente
	 */
	public function getUltimoIngreso()
	{
		return $this->aUltimoIngreso;
	}

	/*
	 *	Retorna vías del ingreso
	 */
	public function getViaIngreso()
	{
		return $this->aVia;
	}

}