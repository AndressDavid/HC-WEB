<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';
require_once __DIR__ . '/class.Diagnostico.php';
require_once __DIR__ . '/class.DatosAmbulatorios.php';
require_once __DIR__ . '/class.ParametrosConsulta.php';
require_once __DIR__ . '/class.PdfHC.config.php';
require_once __DIR__ . '/class.PeriodosFechas.php';

use NUCLEO\Db;
use NUCLEO\Diagnostico;

class Doc_Ordenes
{
	protected $oDb;
	protected $aDatosB = [];
	protected $aIncapacidad = [];
	protected $aOrdenes = [];
	protected $lnFechaInicio = '';
	protected $lnFechaFin = '';
	protected $lcPlanIngreso = '';
	protected $aIncapacidadH = [];
	protected $aOrdenesDet = [];
	protected $aDosis = [];
	protected $aFrecuencia = [];
	protected $aFrecuen = [];
	protected $aPresenta = [];
	protected $aListaParametros = [];
	protected $aVia = [];
	protected $aPaciente = [];
	protected $aUsuario = [];
	protected $aFirma = [];
	protected $cSL = '';
	protected $cRiesgoInminente = '';
	protected $aPrmTab = [];
	protected $cEnter = [];
	protected $cEncabezadoNopos = '';
	protected $cImagenFirma = '';
	protected $cLogoShaio = '';
	protected $cRegistroMedico = '';
	protected $cNombreMedico = '';
	protected $cPiePaginaNopos = '';
	protected $oDatSrv = [];
	protected $aDetalleCtcCups = [];
	protected $aDetalleCtcMedicamentos = [];
	protected $cPaginaUsuario = '';
	protected $nTipoIncapacidad = 1;
	protected $aReporte = [
		'cTitulo' => '',
		'lMostrarFechaRealizado' => false,
		'lMostrarViaCama' => false,
		'cTxtAntesDeCup' => '',
		'cTituloCup' => '',
		'cTxtLuegoDeCup' => '',
		'aCuerpo' => [],
		'aFirmas' => [],
		'aNotas' => ['notas' => false],
	];


	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;

		$lcSrv = $this->oDb->ObtenerTabMae1('OP5TMA', 'FIRMADIG', ['CL1TMA' => 'RUTA', 'ESTTMA' => ''], null, '');
		$this->oDatSrv = $this->oDb->configServer($lcSrv);
	}

	//	Retornar array con los datos del documento
	public function retornarDocumento($taData = [], $tnTipoOrden = 2)
	{
		if (empty($taData['nConsecDoc'])) {
			$taData['nConsecDoc'] = $this->BuscarConsecutivo($taData['nIngreso'], $taData['nConsecCita'], $taData['nConsecCons']);
		}

		$this->cSL = PHP_EOL;
		$lcGeneroPac = $taData['cSexoPaciente'];
		$this->aPaciente['Plan'] = $taData['cPlan'];
		$this->aPaciente['cTipDocPac'] = $taData['cTipDocPac'];
		$this->aPaciente['cTipoDocum'] = $taData['cTipoDocum'];
		$this->aPaciente['cTipDocDesc'] = $taData['cTipDocDesc'];
		$this->aPaciente['nNumDocPac'] = $taData['nNumDocPac'];
		$this->aPaciente['cNombre'] = $taData['cNombre'];
		$this->aPaciente['cSexoPaciente'] = $this->oDb->obtenerTabmae1('DE2TMA', 'SEXPAC', "CL1TMA='$lcGeneroPac'", null, '');
		$this->aPaciente['cDescripcionViaIngreso'] = $taData['cDescVia'];
		$this->aPaciente['cFechaRealizado'] = $taData['cFechaRealizado'];
		$this->lnFechaInicio = $taData['nFechaIngreso'] ?? 0;
		$this->lnFechaFin = $taData['nFechaEgreso'] ?? 0;
		$laListaImp = !empty($taData['nConsecEvol']) ? explode(',', $taData['nConsecEvol']) : [];


		$this->cargar($taData, $tnTipoOrden, $laListaImp);

		if ($tnTipoOrden == 1) {
			return $this->aOrdenes;
		} else {
			return ['datos' => $this->aOrdenesDet, 'firma' => $this->aUsuario, 'firmaCC' => $this->aFirma];
		}
	}


	private function cargar($taData, $tnTipoOrden = 1, $taListaImp = [])
	{
		// Cosulta Ordenes
		if (!empty($taData['nIngreso']) && !empty($taData['nConsecCons']) && $taData['nConsecDoc'] == 0) {
			$this->oDb->where([
				'INGORA' => $taData['nIngreso'],
				'CCOORA' => $taData['nConsecCons'],
			]);
		}



		// Para interconsulta adiciona el elemento 14
		if (in_array('6', $taListaImp)) $taListaImp[] = '14';

		if (!empty($taListaImp)) {
			$this->oDb->in('INDORA', $taListaImp);
		}
		$laOrdenes = $this->oDb
			->select('INGORA INGRESO, INDORA INDICE, DESORA DESCRIP, USRORA USUARIO, FECORA')
			->from('OrdAmbL02')
			->where([
				'TIDORA' => $taData['cTipDocPac'],
				'NIDORA' => $taData['nNumDocPac'],
				'CORORA' => $taData['nConsecDoc'],
			])
			->getAll('array');
		$lnReg = count($laOrdenes);

		if ($lnReg > 0) {
			$this->aUsuario['usuario'] = trim($laOrdenes[0]['USUARIO']);
			$lcEspecialidadmedico = trim(mb_substr(trim($laOrdenes[0]['DESCRIP']), 120, 3));
			$this->lnFechaFin = $laOrdenes[0]['FECORA'];
			$laUnion = [];
			for ($lnNum = 1; $lnNum < 15; $lnNum++) {
				$laUnion[strval($lnNum)] = '';
			}

			foreach ($laOrdenes as $laData) {
				$laUnion[$laData['INDICE']] .= $laData['DESCRIP'];
			}


			if (empty($lcEspecialidadmedico)) {
				$laEspecialidad = $this->oDb
					->select('DESORA DESCRIP')
					->from('OrdAmbL02')
					->where([
						'TIDORA' => $taData['cTipDocPac'],
						'NIDORA' => $taData['nNumDocPac'],
						'CORORA' => $taData['nConsecDoc'],
						'INDORA' => 1,
					])
					->getAll('array');
				$lcEspecialidadmedico = trim(mb_substr(trim($laEspecialidad[0]['DESCRIP']), 120, 3));
			}
			$laEspecialidad = $this->oDb->select('DESESP')->from('RIAESPE')->where("CODESP='$lcEspecialidadmedico'")->get('array');
			$lcDescrEspecialidad = $laEspecialidad['DESESP'] ?? '';

			// Consulta de datos del médico que ordena
			$laMedico = $this->oDb
				->select("M.REGMED REGISTRO,TRIM(M.NNOMED)||' '||TRIM(M.NOMMED) NOMBRE_MEDICO, TRIM(IFNULL(D.DOCUME, M.TIDRGM)) || ' ' || TRIM(M.NIDRGM) DOCUMENTO")
				->from('RIARGMN M')
				->leftJoin('RIATI D', 'M.TIDRGM=D.TIPDOC')
				->where(['M.USUARI' => $this->aUsuario['usuario'],])
				->get('array');
			if (is_array($laMedico)) {
				$this->cRegistroMedico = $laMedico['REGISTRO'];
				$this->cNombreMedico = $laMedico['NOMBRE_MEDICO'];
				$this->aUsuario = [
					'registro' => $laMedico['REGISTRO'],
					'prenombre' => 'Dr. ',
					'codespecialidad' => $lcEspecialidadmedico,
				];
			}

			$this->fnIniciaDatos();
			foreach ($laUnion as $lnIndice => $laData) {

				if (!empty(trim($laData))) {

					switch ($lnIndice) {

						case '1':
							if (isset($laUnion['14']) && strlen($laUnion['14']) > 0) {
								$this->nTipoIncapacidad = 2;
								$this->aOrdenes['6']['datos'] = $laUnion['14'];
								$this->obtenerDatosIncapacidad($taData);
							}
							$this->fnActualizarDatosBasicos($laData, $tnTipoOrden, $taData['nIngreso'], $taData['nConsecCons'], $taListaImp);
							break;

						case '4': /* dieta */
							$this->aOrdenes['4']['titulo'] = 'DIETA';
							$this->aOrdenes['4']['descripcion'] .= $laData;
							break;

						case '6': /* Incapacidad Médica */
							$this->aOrdenes['6']['titulo'] = 'INCAPACIDAD MÉDICA';
							$this->aOrdenes['6']['descripcion'] .= $laData;
							break;

						case '7': /* Otros */
							$this->aOrdenes['7']['titulo'] = 'OTROS';
							$this->aOrdenes['7']['descripcion'] .= $laData;
							break;

						case '8': /* Medicamentos */
							$this->fnActualizarMedicamentos($laData, $tnTipoOrden, $taListaImp);
							break;

						case '9': /* Procedimientos */
							$this->fnActualizarProcedimientos($laData, $tnTipoOrden, $taListaImp);
							break;

						case '10': /* Interconsultas */
							$this->fnActualizarInterconsultas($laData, $tnTipoOrden);
							break;

						case '11': /* Recomendaciones */
							$this->aOrdenes['11']['titulo'] = 'Recomendaciones Generales' . $this->cSL;
							$this->aOrdenes['11']['descripcion'] .= $laData;
							break;

						case '12':
							$this->aOrdenes['12']['titulo'] = 'RECOMENDACIONES NUTRICIONALES Y DIETARIAS' . $this->cSL;
							$this->aOrdenes['12']['descripcion'] .= $laData;
							break;

						case '13':
							$this->aOrdenes['13']['titulo'] = 'INSUMOS' . $this->cSL;
							$this->aOrdenes['13']['descripcion'] .= $laData;
							break;

						case '14':
							// índice validado en case '1'
							break;
					}
				}
			}

			$this->aFirma = $this->nTipoIncapacidad == 2 ?
				[
					'texto_firma' => "Dr. {$laMedico['NOMBRE_MEDICO']}{$this->cSL}{$laMedico['DOCUMENTO']}{$this->cSL}RME. {$laMedico['REGISTRO']}{$this->cSL}$lcDescrEspecialidad",
					'registro' => $laMedico['REGISTRO'],
				] :
				$this->aUsuario;


			if ($this->nTipoIncapacidad == 2) {
				$lcSubtituloIncapacidad = 'FUNDACIÓN ABOOD SHAIO - NIT 860006656 - Código Prestador 1100106447';
				$lcSubtituloIncapacidad = $this->cSL . $this->oDb->obtenerTabmae1('DE2TMA', 'FORMEDIC', "CL1TMA='ORDAMB' AND CL2TMA='INCAPAC' AND CL3TMA='SUBTIT' AND CL4TMA='0001' AND ESTTMA=''", null, $lcSubtituloIncapacidad);
				$lcDxRel = empty($this->aIncapacidad['DxRel']) ? '' : "<br>Diagnóstico relacionado: <b>{$this->aIncapacidad['DxRel']}</b>";
			} else {
				$lcSubtituloIncapacidad = $lcDxRel = '';
			}

			if ($tnTipoOrden == 2) {

				// INSUMOS TEXTO
				if (!empty($this->aOrdenes['13']['descripcion'])) {
					$this->aOrdenesDet[] = [
						'titulop' => 'INSUMOS',
						'cuerpo' => [['texto10', $this->aOrdenes['13']['descripcion']]],
						'encabezado' => true,
						'firma' => true,
					];
				}

				if (!empty($this->aOrdenes['4']['descripcion'])) {
					$this->aOrdenesDet[] = [
						'titulop' => 'DIETA',
						'cuerpo' => [
							['titulo1', 'DIETA'],
							['titulo2', $this->aOrdenes['4']['subtitulo']],
							['texto10', $this->aOrdenes['4']['descripcion']],
						],
						'encabezado' => true,
						'firma' => true,
					];
				}

				$laEncabezado = [
					['titulo1', 'DATOS HISTORIA CLINICA'],
					['txthtml10', "Diagnóstico principal: <b>{$this->aIncapacidad['Diagnos']}</b>{$lcDxRel}"],
					['titulo1', 'DATOS DE INCAPACIDAD'],
				];
				if ($this->nTipoIncapacidad == 2) {
					$laEncabezado[] = ['txthtml10', "Grupo de servicios &nbsp;&nbsp;: <b>{$this->aIncapacidad['GrupoServ']}</b>"];
					$laEncabezado[] = ['txthtml10', "Modalidad prestación : <b>{$this->aIncapacidad['ModPresta']}</b>"];
					$laEncabezado[] = ['txthtml10', "Presunto Origen &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <b>{$this->aIncapacidad['Origen']}</b>"];
					$laEncabezado[] = ['txthtml10', "Causa Motiva Atención: <b>{$this->aIncapacidad['Causa']}</b>"];
				}
				$laObservaInc = empty($this->aOrdenes['6']['descripcion']) ? [] : [
					['titulo1', 'OBSERVACIONES'],
					['texto10', $this->aOrdenes['6']['descripcion']],
				];
				if (!empty($this->aIncapacidad['Dias'])) {
					$lcIniInc = AplicacionFunciones::formatFechaHora('fecha', $this->aIncapacidad['FecInicio']);
					$lcFinInc = AplicacionFunciones::formatFechaHora('fecha',  $this->aIncapacidad['FecFin']);

					if ($this->nTipoIncapacidad == 2) {
						$laDetalle = [
							['txthtml10', "¿Es prórroga? &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <b>{$this->aIncapacidad['Prorroga']}</b>"],
						];
					} else {
						$laDetalle = [
							['txthtml10', "Tipo de Incapacidad &nbsp;: <b>{$this->aIncapacidad['Tipo']}</b>"],
						];
					}
					$laDetalle[] = ['txthtml10', "Inicio Incapacidad &nbsp;&nbsp;: <b>{$lcIniInc}</b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Fin Incapacidad : <b>{$lcFinInc}</b>"];
					$laDetalle[] = ['txthtml10', "Días Incapacidad &nbsp;&nbsp;&nbsp;&nbsp;: <b>{$this->aIncapacidad['Dias']}</b>"];

					$this->aOrdenesDet[] = [
						'titulop' => 'INCAPACIDAD MEDICA' . $lcSubtituloIncapacidad,
						'cuerpo' => array_merge($laEncabezado, $laDetalle, $laObservaInc),
						'encabezado' => true,
						'firma' => true,
					];
				}

				// Incapacidad Retroactiva
				if (count($this->aIncapacidad['Retroactiva']) > 0) {
					$lcTipoRetro = $this->aIncapacidad['Retroactiva']['tipo'];
					foreach ($this->aIncapacidad['Retroactiva']['periodos'] as $laPeriodo) {
						$this->aOrdenesDet[] = [
							'titulop' => 'INCAPACIDAD MEDICA RETROACTIVA' . $lcSubtituloIncapacidad,
							'cuerpo' => array_merge($laEncabezado, [
								['txthtml10', "Tipo de Incapacidad &nbsp;: <b>RETROACTIVA - $lcTipoRetro</b>"],
								['txthtml10', "Inicio Incapacidad &nbsp;&nbsp;: <b>{$laPeriodo['ini']}</b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Fin Incapacidad &nbsp;&nbsp;: <b>{$laPeriodo['fin']}</b>"],
								['txthtml10', "Días Incapacidad &nbsp;&nbsp;&nbsp;&nbsp;: <b>{$laPeriodo['dias']}</b>"],
							], $laObservaInc),
							'encabezado' => true,
							'firma' => true,
						];
					}
				}

				// Incapacidad Hospitalaria
				$lnReg = count($this->aIncapacidadH);
				if ($lnReg > 0) {
					foreach ($this->aIncapacidadH as $laIncap) {
						$this->aOrdenesDet[] = [
							'titulop' => 'INCAPACIDAD HOSPITALARIA' . $lcSubtituloIncapacidad,
							'cuerpo' => array_merge($laEncabezado, [
								['txthtml10', "Tipo de Incapacidad &nbsp;: <b>HOSPITALARIA</b>"],
								['txthtml10', "Inicio Incapacidad &nbsp;&nbsp;: <b>{$laIncap['FecInicio']}</b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Fin Incapacidad &nbsp;&nbsp;: <b>{$laIncap['FecFin']}</b>"],
								['txthtml10', "Días Incapacidad &nbsp;&nbsp;&nbsp;&nbsp;: <b>{$laIncap['dias']}</b>"],
							]),
							'encabezado' => true,
							'firma' => true,
						];
					}
				}

				// Otros - Instrucciones no farmacologicas / seguridad
				if (!empty($this->aOrdenes['7']['descripcion'])) {
					$this->aOrdenesDet[] = [
						'titulop' => 'INSTRUCCIONES NO FARMACOLOGICAS / SEGURIDAD',
						'cuerpo' => [['texto10', $this->aOrdenes['7']['descripcion']]],
						'encabezado' => true,
						'firma' => true,
					];
				}

				// Recomendaciones
				if (!empty($this->aOrdenes['11']['descripcion']) || !empty($this->aOrdenes['12']['descripcion'])) {
					$laDetalle = [
						['titulo1', 'RECOMENDACIONES GENERALES'],
						['texto10', $this->aOrdenes['11']['descripcion']],
					];
					if (!empty(trim($this->aOrdenes['12']['descripcion']))) {
						$laDetalle[] = ['titulo1', 'RECOMENDACIONES NUTRICIONALES Y DIETARIAS'];
						$laDetalle[] = ['texto10', $this->aOrdenes['12']['descripcion']];
					}
					$this->aOrdenesDet[] = [
						'titulop' => 'RECOMENDACIONES',
						'cuerpo' => $laDetalle,
						'encabezado' => true,
						'firma' => true,
					];
				}
			}

			$lbFormula = isset($this->aOrdenesDet[0]) ? ($tnTipoOrden == 2 && $this->aOrdenesDet[0]['titulop'] == 'FÓRMULA MÉDICA') : false;

			if ($this->aIncapacidad['MedicaP1'] == 'Si') {
				$lcDetalle = '¿Realizó formulación de egreso teniendo en cuenta los medicamentos registrados en la conciliación de medicamentos al ingreso del paciente?    Si';

				if ($tnTipoOrden == 1 && !empty(trim($this->aOrdenes['8']['descripcion']))) {
					$this->aOrdenes['8']['descripcion'] .= $lcDetalle . $this->cSL;
				}
				if ($lbFormula) {
					$this->aOrdenesDet[0]['cuerpo'][] = ['saltol', 5];
					$this->aOrdenesDet[0]['cuerpo'][] = ['texto9', $lcDetalle];
				}
			}

			if ($this->aIncapacidad['MedicaP2'] == 'Si') {
				$lcDetalle = '¿El médico le brindó al paciente información sobre el uso correcto de los medicamentos que deberá tomar en casa? ¿Fue clara y entendida?   Si';

				if ($tnTipoOrden == 1 && !empty(trim($this->aOrdenes['8']['descripcion']))) {
					$this->aOrdenes['8']['descripcion'] .= $lcDetalle . $this->cSL;
				}
				if ($lbFormula) {
					$this->aOrdenesDet[0]['cuerpo'][] = ['saltol', 5];
					$this->aOrdenesDet[0]['cuerpo'][] = ['texto9', $lcDetalle];
				}
			}

			if ($lbFormula) {
				$this->aOrdenesDet[0]['cuerpo'][] = ['saltol', 5];
				$this->aOrdenesDet[0]['cuerpo'][] = ['texto10', 'Cordialmente,'];
			}
		}
	}


	private function fnIniciaDatos()
	{
		// Incapacidad
		$this->aIncapacidad = [
			'TipoInc'	=> '',
			'Dias'		=> '',
			'FecInicio'	=> '',
			'FecFin'	=> '',
			'Tipo'		=> '',
			'Genera'	=> '',
			'Requiere'	=> '',
			'Detalle'	=> '',
			'Diagnos'	=> '',
			'DescDiag'  => '',
			'MedicaP1'  => '',
			'MedicaP2'  => '',
			'DxRel'     => '',
			'GrupoServ' => '',
			'ModPresta' => '',
			'Origen'    => '',
			'Causa'     => '',
			'Prorroga'  => '',
			'Retroactiva' => [],
		];

		// Ordenes
		$this->aOrdenes = [
			'1'	=> ['titulo' => '', 'descripcion' => ''],
			'2'	=> ['titulo' => '', 'descripcion' => ''],
			'3'	=> ['titulo' => '', 'descripcion' => ''],
			'4'	=> ['titulo' => '', 'subtitulo' => '', 'descripcion' => ''],
			'5'	=> ['titulo' => '', 'descripcion' => ''],
			'6'	=> ['titulo' => '', 'subtitulo' => '', 'descripcion' => '', 'incapacidad' => '', 'IncHospital' => '', 'IncRetroactiva' => ''],
			'7'	=> ['titulo' => '', 'descripcion' => ''],
			'8'	=> ['titulo' => '', 'subtitulo' => '', 'descripcion' => ''],
			'9'	=> ['titulo' => '', 'descripcion' => '', 'titulo1' => '', 'descripcion1' => ''],
			'10' => ['titulo' => '', 'descripcion' => ''],
			'11' => ['titulo' => '', 'descripcion' => ''],
			'12' => ['titulo' => '', 'descripcion' => ''],
			'13' => ['titulo' => '', 'descripcion' => ''],
		];
	}


	private function fnActualizarDatosBasicos($taDatos = '', $tnTipo = 1, $tnIngreso = 0, $tnConsulta = 0, $taListaImp)
	{
		$lcSL = $this->cSL;
		$lbTodo = empty($taListaImp);
		$this->cRiesgoInminente = trim(substr($taDatos, 61, 4));

		if ($lbTodo || in_array('4', $taListaImp)) {
			$oPrmTab = $this->oDb->obtenerPrmtab('TABDSC', 'TDI', ['TABCOD' => trim(substr($taDatos, 14, 15))]);
			$this->aOrdenes['4']['subtitulo'] = 'DIETA ' . trim(AplicacionFunciones::getValue($oPrmTab, 'TABDSC', ''));
		}
		if ($lbTodo || in_array('8', $taListaImp)) {
			$this->aIncapacidad['MedicaP1'] = (empty(trim(substr($taDatos, 116, 2))) ? trim(substr($taDatos, 108, 2)) : trim(substr($taDatos, 116, 2)));
			$this->aIncapacidad['MedicaP2'] = (empty(trim(substr($taDatos, 118, 2))) ? trim(substr($taDatos, 110, 2)) : trim(substr($taDatos, 118, 2)));
		}
		$this->aIncapacidad['Diagnos'] = trim(substr($taDatos, 33, 4));
		$loDiagnostico = new Diagnostico($this->aIncapacidad['Diagnos'], $this->lnFechaFin);
		$this->aIncapacidad['DescDiag']	= $loDiagnostico->getTexto();
		if ($lbTodo || in_array('6', $taListaImp)) {
			$this->aIncapacidad['Dias'] = trim(substr($taDatos, 29, 4));
			$this->aIncapacidad['FecInicio'] = trim(substr($taDatos, 65, 14));
			$this->aIncapacidad['FecFin'] =	trim(substr($taDatos, 79, 14));
			$this->aIncapacidad['Tipo'] = trim(substr($taDatos, 93, 15));
			if (!empty($this->aIncapacidad['Tipo'])) {
				$oPrmTab = $this->oDb->obtenerPrmtab('TABDSC', 'IN2', ['TABCOD' => $this->aIncapacidad['Tipo']]);
				if ($this->nTipoIncapacidad == 2) {
					if ($tnTipo == 1) {
						$this->aIncapacidad['Tipo'] = implode($lcSL, [
							"Grupo de servicios   : {$this->aIncapacidad['GrupoServ']}",
							"Modalidad prestación : {$this->aIncapacidad['ModPresta']}",
							"Presunto Origen      : {$this->aIncapacidad['Origen']}",
							"Causa Motiva Atención: {$this->aIncapacidad['Causa']}",
							"¿Es prórroga?        : {$this->aIncapacidad['Prorroga']}",
						]);
					}
				} else {
					$this->aIncapacidad['Tipo'] = ($tnTipo == 1 ? 'Tipo de Incapacidad  : ' : '') . trim(AplicacionFunciones::getValue($oPrmTab, 'TABDSC', ''));
				}
			}

			$this->aIncapacidad['Genera'] =	trim(substr($taDatos, 108, 4));
			$this->aIncapacidad['Requiere'] = trim(substr($taDatos, 112, 4));

			$lcDetalle = '';
			if (!empty($this->aIncapacidad['Dias'])) {
				if (!empty($this->aIncapacidad['Tipo'])) {
					$lnFechaI = intval(trim(substr($this->aIncapacidad['FecInicio'], 0, 8)));
					$lnFechaF = intval(trim(substr($this->aIncapacidad['FecFin'], 0, 8)));
					$lcDetalle .= $this->aIncapacidad['Tipo'] . $lcSL
						. 'INICIO: ' . AplicacionFunciones::formatFechaHora('fecha', $lnFechaI)
						. ' FIN: ' . AplicacionFunciones::formatFechaHora('fecha', $lnFechaF)
						. ' DIAS: ' . $this->aIncapacidad['Dias'];

					if (!empty(trim($lcDetalle))) {
						$this->aOrdenes['6']['subtitulo'] = $lcDetalle;
					}
				}
			}

			// Incapacidad Retroactiva
			if (count($this->aIncapacidad['Retroactiva']) > 0) {
				$lcDetalle = "Tipo de Incapacidad  : {$this->aIncapacidad['Retroactiva']['tipo']}{$lcSL}{$lcSL}";
				foreach ($this->aIncapacidad['Retroactiva']['periodos'] as $laPeriodo) {
					$lcDetalle .= "- Inicio Incapacidad: {$laPeriodo['ini']}          Fin Incapacidad: {$laPeriodo['fin']}{$lcSL}" .
						"  Días Incapacidad: {$laPeriodo['dias']}{$lcSL}{$lcSL}";
				}
				$this->aOrdenes['6']['IncRetroactiva'] = trim($lcDetalle);
			}

			// Incapacidad Hospitalaria
			$lcDetalle = '';
			$lnFechaUnifica = trim($this->oDb->obtenerTabMae1('OP2TMA', 'HCPARAM', ['CL1TMA' => 'FINIHCPP', 'ESTTMA' => '']));

			if (($this->aIncapacidad['Requiere'] == '1') && ($this->aIncapacidad['FecInicio'] >= $lnFechaUnifica)) {

				$lnFechaIniIH = substr(trim($this->oDb->obtenerTabMae1('OP4TMA', 'HCPARAM', ['CL1TMA' => 'FINIINCH', 'ESTTMA' => ''])), 0, 8);
				$ldFechaIniIH = date_create($lnFechaIniIH);
				$lnFechaInicio = $this->validarFechaInicio($tnIngreso, $tnConsulta);
				$lnFechaInicio = $lnFechaInicio == 0 ? $this->lnFechaInicio : $lnFechaInicio;
				$ldFechaInicio = date_create($lnFechaInicio);
				$ldIndFechaFin = date_create($lnFechaInicio);
				$lnFechaFin = $this->lnFechaFin;
				$ldFechaFin = date_create($lnFechaFin);
				$loDias = date_diff($ldFechaFin, $ldFechaInicio);
				$lnDias = $loDias->days + 1;
				$ldFechaSig = date_create($lnFechaInicio);
				date_add($ldFechaSig, date_interval_create_from_date_string('29 days'));

				if ($ldFechaIniIH <= $ldFechaFin) {
					$lnDiasIncap = 0;
					$lnKey = 0;

					for ($lnIndica = 1; $lnIndica <= $lnDias; $lnIndica += 30) {
						$lnKey = $lnKey + 1;
						$loDias = date_diff($ldFechaSig, $ldFechaInicio);
						$lnDiasIncap = $lnDias < 30 ? $lnDias : $loDias->days + 1;

						$this->aIncapacidadH[$lnKey] = [
							'FecInicio' => $ldFechaInicio->format('Y-m-d'),
							'FecFin' => $lnDias < 30 ? $ldFechaFin->format('Y-m-d') : $ldFechaSig->format('Y-m-d'),
							'dias' => $lnDiasIncap,
						];

						date_add($ldFechaInicio, date_interval_create_from_date_string('30 days'));
						date_add($ldFechaSig, date_interval_create_from_date_string('30 days'));
						if ($ldFechaSig > $ldFechaFin) {
							$ldFechaSig = $ldFechaFin;
						}
					}
				}

				if (is_array($this->aIncapacidadH)) {
					if (count($this->aIncapacidadH) > 0) {
						$lcDetalle	= '';
						foreach ($this->aIncapacidadH as $laFecha) {
							$lcDetalle .= '- Inicio Incapacidad: ' . $laFecha['FecInicio'] .
								'          Fin Incapacidad: ' . $laFecha['FecFin'] . $lcSL .
								'  Días Incapacidad: ' . $laFecha['dias'] . $lcSL . $lcSL;
						}
						$this->aOrdenes['6']['IncHospital'] = trim($lcDetalle);
					}
				}
			}
		}
	}


	private function fnPieDePaginaNopos()
	{
		$this->cPiePaginaNopos =
			'<table width="100%" cellpadding="1" cellspacing="0" border="0">
				<tr align="center">
				<td width="135"><b>Fundación Abood Shaio<br>Código MinSalud 1100106447</b></td>
				<td width="10">|<br>|</td>
				<td width="130"><b>Diagonal 115A No 70C-75<br>Bogotá - Colombia</b></td>
				<td width="10">|<br>|</td>
				<td width="120"><b>PBX: (57 1) 593 82 10<br>FAX: (57 1) 271 49 30</b></td>
				<td width="10">|<br>|</td>
				<td width="120"><b>Email: info@shaio.org<br>www.shaio.org</b></td>
			</tr></table>';
	}


	private function fnEncabezadoNopos($tcTipoEncabezado = '')
	{
		$lcTipoEncabezado = $tcTipoEncabezado == 'P' ? '' : '<tr><td align="center"><b> Ordenes Ambulatorias </b></td></tr>';
		$this->cEncabezadoNopos =
			'<table cellpadding="1" cellspacing="1">' . $lcTipoEncabezado . '
				<tr><td width="77">Paciente. . . :</td><td width="240"><b>' . $this->aPaciente['cNombre'] . '</b></td>
					<td width="77">Nro. Doc. . . :</td><td width="110"><b>' . $this->aPaciente['cTipDocPac'] . ' ' . $this->aPaciente['nNumDocPac'] . '</b></td></tr>
				<tr><td width="77">Sexo. . . . . :</td><td width="240"><b>' . $this->aPaciente['cSexoPaciente'] . '</b></td>
					<td width="77">Vía Ingreso . :</td><td width="110"><b>' . $this->aPaciente['cDescripcionViaIngreso'] . '</b></td></tr>
				<tr><td width="77">Entidad. . . .:</td><td width="240"><b>' . $this->aPaciente['Plan'] . '</b></td>
					<td width="77">Fecha Realiza :</td><td width="110"><b>' . $this->aPaciente['cFechaRealizado'] . '</b></td></tr>
			</table>';
	}


	private function fnObtieneFirmaNopos()
	{
		$lcRutaFirma = str_replace('\\', '/', trim($this->oDb->ObtenerTabMae1('DE2TMA', 'FIRMADIG', ['CL1TMA' => 'RUTA', 'ESTTMA' => ''], null, '')));

		if (!empty($lcRutaFirma)) {
			$lcArchivo = $lcRutaFirma . $this->cRegistroMedico . '.JPG';
			$lnFormatoImg = $lnEstadoImg = 0;
			$lcTipoMiMe = '';
			$lcImagenFirma = '@' . AplicacionFunciones::obtenerRemoto($lcArchivo, $lnFormatoImg, $lnEstadoImg, $lcTipoMiMe, $this->oDatSrv['workgroup'], $this->oDatSrv['user'], $this->oDatSrv['pass']);
			$this->cImagenFirma = $lnEstadoImg > 0 ? $lcImagenFirma : '';
		}
	}


	private function fnObtieneLogoShaio()
	{
		$this->cLogoShaio = K_PATH_IMAGES . PDF_HEADER_LOGO;
	}


	private function fnPaginaUsuario()
	{
		$lcUser = defined('HCW_NAME') ? (isset($_SESSION[HCW_NAME]) ? $_SESSION[HCW_NAME]->oUsuario->getUsuario() . ' - ' : '') : '';
		$this->cPaginaUsuario = 'IMPRESIÓN: ' . $lcUser . date('Y-m-d H:i:s') . ' - LIBROHCWEB' . str_repeat('&nbsp;', 60) . 'Pag. 1 DE 1';
	}


	// Medicamentos
	private function fnActualizarMedicamentos($taDatos = '', $tnTipo = 1, $taListaImp)
	{
		$lcSL = $this->cSL;
		$this->fnIniciaCursores();
		$lcCharReg = chr(24);
		$laWordsReg = explode($lcCharReg, $taDatos);
		$lnIndReg = 0;
		$lbTodo = empty($taListaImp) || in_array('10', $taListaImp);
		$lbFormula = $lbTodo || !in_array('99', $taListaImp);
		$lbCTC = $lbTodo || in_array('99', $taListaImp);
		$this->aDetalleCtcMedicamentos = [];
		$laDetalle = [];

		foreach ($laWordsReg as $laRegs) {

			if ($lbCTC) {
				$this->fnEncabezadoNopos('M');
				$this->fnPieDePaginaNopos();
			}

			$lnIndReg++;
			$lcCharItm = chr(25);
			$laWordsItm = explode($lcCharItm, $laRegs);

			if (count($laWordsItm) > 1) {

				// Dosis
				$key = array_search($laWordsItm[4] ?? '', array_column($this->aDosis, 'CODIGO'));
				if (is_numeric($key)) {
					$laWordsItm[4] = $this->aDosis[$key]['DESCRIP'];
				}
				// Frecuencia
				$key = array_search($laWordsItm[6] ?? '', array_column($this->aFrecuen, 'CODIGO'));
				if (is_numeric($key)) {
					$laWordsItm[6] = $this->aFrecuen[$key]['DESCRIP'];
				}
				// Via
				$key = array_search($laWordsItm[49] ?? '', array_column($this->aVia, 'CODIGO'));
				if (is_numeric($key)) {
					$laWordsItm[49] = $this->aVia[$key]['DESCRIP'];
				}

				if ($lbFormula) {

					$lcSubtitulo =  ($tnTipo == 1 ? '- ' : $lnIndReg . '. ') . trim($laWordsItm[2] ?? '');

					// Presentacion
					$this->aPresenta = $this->oDb
						->select('PRESE')
						->from('INVMEDA')
						->where(['CODIGO' => trim($laWordsItm[1]),])
						->get('array');
					$lcPresenta = trim($this->aPresenta['PRESE'] ?? '');
					$lcDatosPresentacion = trim($this->aPresenta['PRESE'] ?? '') . (substr(trim($this->aPresenta['PRESE'] ?? ''), -1) == 'S' ? '' : '(S)');
					$lcSubtitulo .= '  [ CANTIDAD : ' . $laWordsItm[11] . ' ' . (!empty($laWordsItm[51] ?? '') ? $laWordsItm[51] ?? '' : $lcDatosPresentacion) . ' ]';

					$lcDetalle  = trim(($laWordsItm[3] ?? '') . ' ' . ($laWordsItm[4] ?? '')
						. (!empty($laWordsItm[5] ?? '') ? ', CADA ' . $laWordsItm[5] ?? '' : '')
						. (!empty($laWordsItm[6] ?? '') ? ' ' . $laWordsItm[6] ?? '' : '')
						. (!empty($laWordsItm[9] ?? '') ? ', DURANTE ' . $laWordsItm[9] ?? '' : '')
						. (!empty($laWordsItm[10] ?? '') ? ' ' . $laWordsItm[10] ?? '' : '')
						. (!empty($laWordsItm[49] ?? '') ? ', VIA ' . $laWordsItm[49] ?? '' . '.' : '')
						. (!empty($laWordsItm[12] ?? '') ? $lcSL . $laWordsItm[12] ?? '' . '.' : ''));

					if ($tnTipo == 1) {
						$this->aOrdenes['8']['titulo'] = 'MEDICAMENTOS';
						$this->aOrdenes['8']['subtitulo'] .= $lcSubtitulo . '¤';
						$this->aOrdenes['8']['descripcion'] .= $lcDetalle . $lcSL . '¤';
					} else {
						$laDetalle[] = ['titulo2', $lcSubtitulo];
						$laDetalle[] = ['texto10', $lcDetalle];
					}
				}

				if ($lbCTC && ($laWordsItm[46] ?? '') == '1') {
					$this->fnInformeNoposMedicamentos($laWordsItm);
				}
			}
		}

		if ($lbFormula) {
			if ($tnTipo == 2 && count($laDetalle) > 0) {
				$this->aOrdenesDet[0] = [
					'titulop' => 'FÓRMULA MÉDICA',
					'cuerpo' => $laDetalle,
					'encabezado' => true,
					'firma' => true,
				];
			}
		}

		// TRAER TITULO Y MODIFICAR
		if ($lbCTC) {
			foreach ($this->aDetalleCtcMedicamentos as $lcCtcMedicamentos) {
				$this->aOrdenesDet[] = [
					'titulop' => 'FUNDACION ABOOD SHAIO' . $lcSL . 'JUSTIFICACION DE MEDICAMENTOS FUERA DE LA RESOLUCIÓN 5521 DE DICIEMBRE 27 DE 2013' . $lcSL . 'AL COMITE TECNICO CIENTIFICO DE LA EPS',
					'descripcion' => '',
					'cuerpo' => $lcCtcMedicamentos,
					'encabezado' => false,
				];
			}
		}
	}


	// Procedimientos
	private function fnActualizarProcedimientos($taDatos = '', $tnTipo = 1, $taListaImp)
	{
		$lcCharReg = chr(24);
		$laWordsReg = explode($lcCharReg, $taDatos);
		$this->aDetalleCtcCups = [];
		if (count($laWordsReg) > 0) {

			$this->fnIniciaCursoresCups();
			$lcDetalle = $lcDetalleP = '';

			$lbTodo = empty($taListaImp) || in_array('10', $taListaImp);
			$lbCTC = $lbTodo || in_array('99', $taListaImp);
			$lbCarta = $lbTodo || !in_array('99', $taListaImp);

			if ($lbCTC) {
				$this->fnEncabezadoNopos('P');
				$this->fnObtieneFirmaNopos();
			}

			foreach ($laWordsReg as $laRegs) {
				$lcCharItm = chr(25);
				$laWordsItm = array_map('trim', explode($lcCharItm, $laRegs));

				if (count($laWordsItm) > 1) {
					if ($tnTipo == 2) {
						$lcDetalle = '<b> ' . '- ' . (!empty($laWordsItm[1]) && $laWordsItm[20] == 'P' ? '(' . $laWordsItm[1] . ')' : '')
							. (!empty($laWordsItm[2]) ? ' ' . $laWordsItm[2] : '')
							. (!empty($laWordsItm[4]) ? ', CANTIDAD: ' . $laWordsItm[4] . '</b><br>' . str_repeat('&nbsp;', 12) : '')
							. (!empty($laWordsItm[3]) ? str_repeat('&nbsp;', 12) . $laWordsItm[3] . '<br>' . str_repeat('&nbsp;', 12) : '');

						if ($lbCTC && $laWordsItm[21] == '1') {
							$this->fnInformeNoposCups($laWordsItm);
						}
					} else {
						$lcDetalle = '- ' . (!empty($laWordsItm[1]) ? '(' . $laWordsItm[1] . ')' : '')
							. (!empty($laWordsItm[2]) ? ' ' . $laWordsItm[2] : '')
							. (!empty($laWordsItm[4]) ? ', CANTIDAD: ' . trim($laWordsItm[4]) . $this->cSL : '')
							. (!empty($laWordsItm[3]) ? $laWordsItm[3] . $this->cSL : '');
					}

					if (!empty($lcDetalle)) {
						if ($tnTipo == 1) {
							if ($laWordsItm[20] == 'I') {
								if ($tnTipo == 1) {
									$this->aOrdenes['9']['titulo'] = 'INSUMOS';
									$this->aOrdenes['9']['descripcion'] .= $lcDetalle;
								}
							}
							if ($laWordsItm[20] == 'P') {
								if ($tnTipo == 1) {
									$this->aOrdenes['9']['titulo1'] = 'PROCEDIMIENTOS';
									$this->aOrdenes['9']['descripcion1'] .= $lcDetalle;
								}
							}
						} else {
							$lnkey = $laWordsItm[20] . $laWordsItm[22];
							if (!isset($laProcedimientos[$lnkey])) {
								$laProcedimientos[$lnkey] = '';
							}
							$laProcedimientos[$lnkey] .= trim($lcDetalle);
						}
					}
				}
			}

			if (isset($laProcedimientos)) {

				$lcCartaEncabezado = $this->fcEncabezado(true);

				foreach ($laProcedimientos as $lnIndice => $laCarta) {

					if ($lnIndice == 'I') {
						if ($lbTodo || in_array('92', $taListaImp)) {
							$lcCartaCuerpoInsum = $lcCartaEncabezado . trim($laCarta)
								. '<br><br>Por medio de la presente, solicitamos autorización para el(los) insumo(s)'
								. ' en referencia al paciente ' . trim($this->aPaciente['cNombre']) . ' identificado con ' . trim($this->aPaciente['cTipDocDesc'])
								. ' N°' . $this->aPaciente['nNumDocPac'] . '.<br><br>El Diagnóstico es: ' . $this->aIncapacidad['Diagnos']
								. ' - ' . $this->aIncapacidad['DescDiag'] . '.<br><br>Cordialmente,<br><br><br>';
							$this->aOrdenesDet[] = [
								'titulop' => '',
								'cuerpo' => [['txthtml10', $lcCartaCuerpoInsum]],
								'encabezado' => false,
								'firma' => true,
							];
						}
					} else {
						if ($lbTodo || in_array('91', $taListaImp)) {
							$lcCartaCuerpoProc = $lcCartaEncabezado . trim($laCarta)
								. '<br><br>Por medio de la presente, solicitamos autorización para realizar el(los) procedimiento(s) '
								. 'en referencia al paciente ' . trim($this->aPaciente['cNombre']) . ' identificado con ' . trim($this->aPaciente['cTipDocDesc'])
								. ' N°' . $this->aPaciente['nNumDocPac'] . '.<br><br>El Diagnóstico es: ' . $this->aIncapacidad['Diagnos']
								. ' - ' . $this->aIncapacidad['DescDiag'] . '.<br><br>Cordialmente,<br><br><br>';
							$this->aOrdenesDet[] = [
								'titulo' => '',
								'cuerpo' => [['txthtml10', $lcCartaCuerpoProc]],
								'titulop' => '',
								'encabezado' => false,
								'firma' => true,
							];
						}
					}
				}

				if ($lbCTC) {
					foreach ($this->aDetalleCtcCups as $laCtcCups) {
						$this->aOrdenesDet[] = [
							'titulop' => 'FUNDACION ABOOD SHAIO' . $this->cSL . 'JUSTIFICACION DE SERVICIOS MEDICOS O PRESTACIONES DE SALUD NO INCLUIDOS EN EL POS'
								. $this->cSL . 'CNSS - AL COMITE TECNICO CIENTIFCODE LA EPS',
							'cuerpo' => $laCtcCups,
							'encabezado' => false,
						];
					}
				}
			}
		}
	}


	private function fnInformeNoposMedicamentos($taWordsItm = [])
	{
		$this->fnObtieneFirmaNopos();
		$this->fnObtieneLogoShaio();
		$this->fnPaginaUsuario();
		$lcMarcaX = '<u>&nbsp;X&nbsp;</u>';
		$lcMarcaSinX = '<u>&nbsp; &nbsp;</u>';
		$lcRecortar = str_repeat('-', 95);

		$key = array_search($taWordsItm[8] ?? '', array_column($this->aDosis, 'CODIGO'));
		if (is_numeric($key)) {
			$taWordsItm[8] = $this->aDosis[$key]['DESCRIP'];
		}

		$oTabmae = $this->oDb->obtenerTabMae('DE2TMA', 'NOPOS', "CL1TMA='RIESINM' AND CL2TMA='$taWordsItm[48]'");
		$lcDesRiesgoInminente = trim(AplicacionFunciones::getValue($oTabmae, 'DE2TMA', ''), ' ');
		$lcDiagnosticoNopos = $this->aIncapacidad['Diagnos'] . ' ' . $this->aIncapacidad['DescDiag'];


		$key = array_search($taWordsItm[26] ?? '', array_column($this->aDosis, 'CODIGO'));
		if (is_numeric($key)) {
			$taWordsItm[26] = $this->aDosis[$key]['DESCRIP'];
		}

		$key = array_search($taWordsItm[30] ?? '', array_column($this->aDosis, 'CODIGO'));
		if (is_numeric($key)) {
			$taWordsItm[30] = $this->aDosis[$key]['DESCRIP'];
		}

		$key = array_search($taWordsItm[28] ?? '', array_column($this->aFrecuen, 'CODIGO'));
		if (is_numeric($key)) {
			$taWordsItm[28] = $this->aFrecuen[$key]['DESCRIP'];
		}

		$laDxResumen = ['tabla', [], [
			['w' => 190, 'h' => 27, 'd' => ["<b> Diagnóstico del paciente: </b>{$lcDiagnosticoNopos}"]],
			['w' => 190, 'h' => 108, 'd' => ["<b> Resumen de Historia Clínica que Justifique el uso del Servicio Médico NO POS</b><br>{$taWordsItm[45]}"]],
			//['w'=>190, 'h'=>108, 'd'=>["<b> Resumen de Historia Clínica que Justifique el uso del Servicio Médico NO POS</b><br><span style=\"font-size:small\">{$taWordsItm[45]}</small>"]],
		], ['fs' => 9]];

		$laSolMedica = ['tabla', [], [
			['w' => 190, 'd' => ['<b> Solicitud de Medicamento No Pos:</b>'], 'a' => 'C'],
			['w' => [65, 59, 66], 'd' => ['<b>Nombre Genérico</b>', '<b>Dosificación</b>', '<b>Forma farmacéutica y concentración</b>'], 'a' => 'C'],
			['w' => [65, 12, 22, 10, 15, 30, 15, 21], 'd' => [$taWordsItm[2], $taWordsItm[3] . '<br>', $taWordsItm[4], $taWordsItm[5], $taWordsItm[6], $taWordsItm[13], $taWordsItm[14], $taWordsItm[15]], 'a' => 'C'],
			['w' => [45, 45, 18, 82], 'd' => ['<b>Dosis Diaria</b>', '<b>Tiempo de Tratamiento</b>', '<b>Cantidad</b>', '<b>Grupo Terapéutico</b>'], 'a' => 'C'],
			['w' => [15, 30, 15, 30, 18, 82], 'd' => [$taWordsItm[7] . '<br>', $taWordsItm[8], $taWordsItm[9], $taWordsItm[10], $taWordsItm[11], $taWordsItm[16]], 'a' => 'C'],
		],];

		$laDeclaro = ['texto9', 'DECLARO QUE LA INFORMACIÓN AQUI SUMINISTRADA ESTA SOPORTADA EN LA HISTORIA CLINICA EN CONSTANCIA FIRMO:'];
		$laFirmaDeclaro = ['tabla', [], [['w' => 190, 'h' => 82, 'd' => [str_repeat('<br>', 7) . 'DR. ' . $this->cNombreMedico . ' - R.M. ' . $this->cRegistroMedico], 'a' => 'C']]];

		$this->aDetalleCtcMedicamentos[] = [
			['cuadrotxt', [
				'text' => $this->cPaginaUsuario,
				'w' => 175,
				'h' => 5,
				'x' => 15,
				'y' => 15,
				'y_abs' => true,
				'size_text' => 7,
				'html' => true,
				'border' => 0
			]],
			['cuadrotxt', [
				'text' => $this->cEncabezadoNopos,
				'w' => 174,
				'h' => 16,
				'x' => 15,
				'y' => 18,
				'y_abs' => true,
				'html' => true,
				'border' => 1
			]],
			['imagen', [
				'archivo' => $this->cImagenFirma,
				'h' => 25,
				'x' => 60,
				'y' => 214,
				'y_nochange' => true,
			]],
			['saltol', 17],

			['tabla', [], [
				['w' => 190, 'd' => ["<b> Medicamento solicitado en:</b> &nbsp; &nbsp; {$this->aPaciente['cDescripcionViaIngreso']}<br><b> Riesgo Inminente del Paciente: </b>$lcDesRiesgoInminente"]],
			], ['fs' => 10]],
			$laDxResumen, $laSolMedica,

			['tabla', [], [
				['w' => [100, 90], 'd' => ["<b> Tiempo de Respuesta Esperado:</b> {$taWordsItm[17]}", "<b> Registro Invima: </b> {$taWordsItm[18]}"]],
				['w' => 190, 'h' => 27, 'd' => ["<b> Efecto Deseado al Tratamiento:</b> {$taWordsItm[19]}"]],
				['w' => 190, 'h' => 50, 'd' => ["<b> Efectos secundarios y Posibles Riesgos al Tratamiento: </b><br>{$taWordsItm[20]}"]],
				['w' => [134, 28, 28], 'd' => ['<b> El Paciente Esta Informado de Efectos Secundarios y Posibles Riesgos: </b>', 'Si ' . ($taWordsItm[21] == 1 ? $lcMarcaX : $lcMarcaSinX), 'No ' . ($taWordsItm[21] == 0 ? $lcMarcaX : $lcMarcaSinX)], 'a' => ['L', 'C', 'C']],
				['w' => 190, 'h' => 70, 'd' => ["<b> Bibliografia: </b> {$taWordsItm[22]}"]],
			], ['fs' => 9.5]],

			['tabla', [], [
				['w' => [134, 28, 28], 'd' => ['<b> Medicamento de Igual Grupo Terapeutico que se Reemplaza no Sustituyen </b>', 'Si Existe ' . (!empty($taWordsItm[24]) ? $lcMarcaX : $lcMarcaSinX), 'No Existe ' . (empty($taWordsItm[24]) ? $lcMarcaX : $lcMarcaSinX)], 'a' => ['L', 'C', 'C']],
				['w' => [78, 66, 46], 'a' => 'C', 'd' => [
					'<b>Nombre Genérico</b><br>' . mb_substr($taWordsItm[24], 0, 40),
					'<b>Dosificación</b><br>' . ($taWordsItm[25] != '0' ? $taWordsItm[25] : '') . ' ' . $taWordsItm[26] . ' ' . ($taWordsItm[27] != '0' ? $taWordsItm[27] : '') . ' ' . $taWordsItm[28],
					'<b>Dosis Diaria</b><br>' . ($taWordsItm[29] != '0' ? $taWordsItm[29] : '') . ' ' . $taWordsItm[30],
				]],
				['w' => [78, 66, 46], 'a' => 'C', 'd' => [
					"<b>Forma farmacéutica y concentración</b><br>{$taWordsItm[35]}  {$taWordsItm[36]}  {$taWordsItm[37]}",
					"<b>Tipo tratamiento</b><br>" . ($taWordsItm[31] != '0' ? $taWordsItm[31] : '') . ' ' . $taWordsItm[32],
					'<b>Grupo Terapéutico</b><br>' . mb_substr($taWordsItm[38], 0, 23),
				]],
			], ['fs' => 9.5]],

			['tabla', [], [
				['w' => 190, 'h' => 40, 'd' => ["<b> Observaciones:</b> {$taWordsItm[34]}"]],
			], ['fs' => 10]],

			$laDeclaro, $laFirmaDeclaro,

			// RECETA MEDICA
			['saltop', ['titulo' => 'RECETA MEDICA', 'encabezado' => false]],
			['cuadrotxt', [
				'text' => $this->cPaginaUsuario,
				'w' => 175,
				'h' => 5,
				'x' => 15,
				'y' => 12,
				'y_abs' => true,
				'size_text' => 7,
				'html' => true,
				'border' => 0
			]],
			['cuadrotxt', [
				'text' => $this->cEncabezadoNopos,
				'w' => 174,
				'h' => 16,
				'x' => 15,
				'y' => 15,
				'y_abs' => true,
				'aling' => '',
				'html' => true,
				'border' => 1
			]],
			['imagen', [
				'archivo' => $this->cImagenFirma,
				'h' => 25,
				'x' => 60,
				'y' => 56,
				'formato' => '',
				'y_nochange' => true,
			]],
			['cuadrotxt', [
				'text' => $this->cPiePaginaNopos,
				'w' => 194,
				'h' => 5,
				'x' => 0,
				'y' => 115,
				'y_abs' => true,
				'html' => true,
				'border' => 0
			]],
			['saltol', 20],
			$laSolMedica, $laDeclaro, $laFirmaDeclaro,
			['saltol', 25],
			['texto10', $lcRecortar],
			['saltol', 5],

			// RESUMEN HISTORIA CLINICA
			['cuadrotxt', [
				'text' => $this->cPaginaUsuario,
				'w' => 175,
				'h' => 5,
				'x' => 15,
				'y' => 149,
				'y_abs' => true,
				'size_text' => 7,
				'html' => true,
				'border' => 0
			]],
			['cuadrotxt', [
				'text' => $this->cEncabezadoNopos,
				'w' => 174,
				'h' => 16,
				'x' => 15,
				'y' => 152,
				'y_abs' => true,
				'aling' => '',
				'html' => true,
				'border' => 1
			]],
			['imagen', [
				'archivo' => $this->cLogoShaio,
				'h' => 25,
				'x' => -5,
				'y' => 6,
				'formato' => '',
				'y_nochange' => true,
			]],
			['imagen', [
				'archivo' => $this->cImagenFirma,
				'h' => 25,
				'x' => 60,
				'y' => 88,
				'formato' => '',
				'y_nochange' => true,
			]],
			['txthtml10', '<h2>RESUMEN HISTORIA CLINICA</h2>', 'C'],
			['saltol', 30],
			$laDxResumen, $laDeclaro, $laFirmaDeclaro,
		];
	}


	private function fnInformeNoposCups($taWordsItm = [])
	{
		$lcDescripcionServicio = !empty($taWordsItm[5]) ? $this->aListaParametros['UBI'][$taWordsItm[5]] : '';
		$lcMarcaX = '<u>&nbsp;X&nbsp;</u>';
		$lcMarcaSinX = '<u>&nbsp; &nbsp;</u>';
		$lcDescripcionObjetivoCups = !empty($taWordsItm[19]) ? $this->aListaParametros['NPO'][$taWordsItm[19]] : '';
		$lcDxCup = substr($this->aIncapacidad['DescDiag'], 0, 79);
		$lcServMedPos = substr($taWordsItm[10], 0, 86);
		$lcServMedNoPos = substr($taWordsItm[2], 0, 76);
		$lcTipoRiesgoImminente = !empty($taWordsItm[23]) ? $taWordsItm[23] : $this->cRiesgoInminente;


		$this->aDetalleCtcCups[] = [
			['cuadrotxt', [
				'text' => $this->cEncabezadoNopos,
				'w' => 174,
				'h' => 16,
				'x' => 15,
				'y' => 18,
				'y_abs' => true,
				'html' => true,
				'border' => 1
			]],
			['imagen', [
				'archivo' => $this->cImagenFirma,
				'h' => 25,
				'x' => 60,
				'y' => 213,
				'y_nochange' => true,
			]],
			['saltol', 15],

			['tabla', [], [
				['w' => 190, 'd' => [
					'<b> Servicio Médico Solicitado en: </b>' . (!empty($lcDescripcionServicio) ? ' ' . $lcDescripcionServicio : '') . '<br>' .
						'<b> Riesgo Inminente del Paciente:</b> Descartado ' . ($lcTipoRiesgoImminente == 1 ? $lcMarcaX : $lcMarcaSinX) .
						str_repeat('&nbsp;', 9) . 'Mortalidad ' . ($lcTipoRiesgoImminente == 2 ? $lcMarcaX : $lcMarcaSinX) .
						str_repeat('&nbsp;', 9) . 'Morbilidad ' . ($lcTipoRiesgoImminente == 3 ? $lcMarcaX : $lcMarcaSinX) . '<br>' .
						str_repeat('&nbsp;', 32) . 'Falta efectividad ' . ($lcTipoRiesgoImminente == 4 ? $lcMarcaX : $lcMarcaSinX) .
						str_repeat('&nbsp;', 2) . 'No alternativos ' . ($lcTipoRiesgoImminente == 5 ? $lcMarcaX : $lcMarcaSinX) . '<br>' .
						'<b> El tipo de riesgo se encuentra soportado en H.C.: </b>' .
						str_repeat('&nbsp;', 4) . 'SI: ' . ($taWordsItm[6] == 1 ? $lcMarcaX : $lcMarcaSinX) .
						str_repeat('&nbsp;', 5) . 'NO: ' . ($taWordsItm[6] == 0 ? $lcMarcaX : $lcMarcaSinX)
				]],
				['w' => [160, 30], 'h' => 28, 'a' => ['L', 'C'], 'd' => [
					"<b> Diagnóstico del paciente:</b><br> {$lcDxCup}",
					"<b> Código CIE-10</b><br>{$this->aIncapacidad['Diagnos']}",
				]],
			], ['fs' => 10]],
			['saltol', 2],

			['tablaSL', [], [
				['w' => [130, 30, 30], 'a' => ['L', 'C', 'C'], 'd' => [
					'Escriba el SERVICIO MÉDICO O PRESTACIÓN DE SERVICIO DE SALUD POS utilizado y/o realizado en el tratamiento del paciente',
					'SI EXISTE ' . (!empty($taWordsItm[9]) ? $lcMarcaX : $lcMarcaSinX),
					'NO EXISTE ' . (empty($taWordsItm[9]) ? $lcMarcaX : $lcMarcaSinX),
				]],
			], ['fs' => 8.5]],
			['tabla', [], [
				['w' => [170, 20], 'a' => 'C', 'd' => ["<b>Nombre del Servicio Médico</b><br>{$lcServMedPos}", "<b>Cantidad</b><br>{$taWordsItm[12]}",]],
				['w' => 190, 'd' => ["<b> Respuesta Clínica y Paraclínica Alcanzada </b><br>{$taWordsItm[18]}"]],
			], ['fs' => 10]],
			['saltol', 2],

			['txthtml10', '<b> SOLICITUD DEL SERVICIO MÉDICO NO POS:</b>'],
			['txthtml8', ' Escriba el SERVICIO MÉDICO O PRESTACIÓN DE SERVICIO DE SALUD NO POS a utilizar en el tratamiento del paciente.'],
			['tabla', [], [
				['w' => [20, 150, 20], 'a' => 'C', 'd' => ["<b>Código</b><br>{$taWordsItm[1]}", "<b>Nombre del Servicio Médico</b><br>{$lcServMedNoPos}", "<b>Cantidad</b><br>{$taWordsItm[4]}",]],
				['w' => 190, 'h' => 30, 'd' => ["<b> El objetivo del Servicio Médico NO POS es: </b> {$lcDescripcionObjetivoCups}"]],
			], ['fs' => 10]],
			['txthtml10', '<b> El Paciente esta informado: </b> &nbsp; ' . 'SI ' . (!empty($taWordsItm[7]) ? $lcMarcaX : $lcMarcaSinX) . ' &nbsp; &nbsp; NO ' . (empty($taWordsItm[7]) ? $lcMarcaX : $lcMarcaSinX)],

			['tabla', [], [['w' => 190, 'h' => 70, 'd' => ["<b> Bibliografia: </b><br>{$taWordsItm[8]}"]],], ['fs' => 10]],

			['txthtml10', '<b> Resumen de Historia que Justifique el uso del Servicio Médico NO POS</b>'],
			['tabla', [], [['w' => 190, 'h' => 190, 'd' => [$taWordsItm[17]]],], ['fs' => 10]],

			['texto9', ' DECLARO QUE LA INFORMACIÓN AQUI SUMINISTRADA ESTA SOPORTADA EN LA HISTORIA CLINICA EN CONSTANCIA FIRMO'],
			['tabla', [], [['w' => 190, 'h' => 82, 'd' => [str_repeat('<br>', 7) . 'DR. ' . $this->cNombreMedico . ' - R.M. ' . $this->cRegistroMedico], 'a' => 'C']]],
		];
	}


	// Interconsultas
	private function fnActualizarInterconsultas($taDatos = '', $tnTipo = 1)
	{
		// Registros
		$lcCharReg = chr(24);
		$laWordsReg = explode($lcCharReg, $taDatos);

		if ($tnTipo == 2) {
			$lcCartaEncabezado = $this->fcEncabezado(false);
		}

		foreach ($laWordsReg as $laRegs) {
			$lcSL = $this->cSL;
			$lnIndReg = 0;
			$lcDetalle = '';
			$lnIndReg = $lnIndReg + 1;
			$lcCharItm = chr(25);
			$laWordsItm = explode($lcCharItm, $laRegs);

			// Especialidad
			$laWordsItm[0] = str_replace(' ', '', $laWordsItm[0]);
			$aEspecial = $this->oDb
				->select('TRIM(CODESP) CODIGO, TRIM(DESESP) DESCRIP')
				->from('RIAESPE')
				->where([
					'CODESP' => $laWordsItm[0],
				])
				->get('array');
			$laWordsItm[0] = $aEspecial['DESCRIP'];
			$lcDetalle = (!empty($laWordsItm[0]) ? '- ' . trim($laWordsItm[0]) . ($tnTipo == 2 ? '<br>' : $lcSL) : '')
				. (!empty(trim($laWordsItm[1])) ? '  ' . trim($laWordsItm[1]) . $lcSL : '');

			if (!empty($lcDetalle)) {
				if ($tnTipo == 2) {
					$lcCartaCuerpo = $lcCartaEncabezado . '<br><br>Por medio de la presente, solicitamos autorización para INTERCONSULTAR POR MEDICINA ESPECIALIZADA, con '
						. trim($laWordsItm[0]) . ' al paciente ' . trim($this->aPaciente['cNombre']) . ' identificado con '
						. trim($this->aPaciente['cTipDocDesc']) . ' N°' . $this->aPaciente['nNumDocPac'] . '.<br><br>'
						. (!empty($laWordsItm[1]) ? trim($laWordsItm[1]) : '') . '<br><br>Cordialmente,<br><br>';
					$this->aOrdenesDet[] = [
						'titulo' => '',
						'cuerpo' => [['txthtml10', $lcCartaCuerpo]],
						'titulop' => '',
						'encabezado' => false,
						'firma' => true,
					];
				} else {
					$this->aOrdenes['10']['titulo'] = 'INTERCONSULTAS';
					$this->aOrdenes['10']['descripcion'] .= $lcDetalle;
				}
			}
		}
	}


	private function fnIniciaCursores()
	{
		// Dosis
		$this->aDosis = $this->oDb
			->select('TRIM(CL2TMA) CODIGO, TRIM(DE1TMA) DESCRIP')
			->from('TABMAE')
			->where([
				'TIPTMA' => 'MEDDOS',
				'ESTTMA' => ' ',
			])
			->getAll('array');

		// Frecuencia
		$this->aFrecuen = $this->oDb
			->select('TRIM(CL2TMA) CODIGO, TRIM(DE1TMA) DESCRIP')
			->from('TABMAE')
			->where([
				'TIPTMA' => 'MEDFRE',
				'ESTTMA' => ' ',
			])
			->getAll('array');

		// Vía
		$this->aVia = $this->oDb
			->select('TRIM(CL1TMA) CODIGO, TRIM(DE1TMA) DESCRIP')
			->from('TABMAE')
			->where([
				'TIPTMA' => 'MEDVAD',
				'ESTTMA' => ' ',
			])
			->getAll('array');
	}


	private function fnIniciaCursoresCups()
	{
		$this->aPrmTab = $this->oDb
			->select("TABCOD, TABDSC, TABTIP, TRIM(TABTIP)||TRIM(TABCOD) AS CODIGO")
			->from('PRMTAB02')
			->where('TABCOD', '<>', '')
			->in('TABTIP', ['UBI', 'NPO'])
			->getAll('array');
		foreach ($this->aPrmTab as $laPar) {
			$laPar = array_map('trim', $laPar);
			$this->aListaParametros[$laPar['TABTIP']][$laPar['TABCOD']] = $laPar['TABDSC'];
		}
	}


	private function fcEncabezado($llReferencia = true)
	{
		$lcCartaEncabezado = '';
		if (!empty($this->lnFechaFin)) {

			$ldFecha = date_create($this->lnFechaFin);
			$lcFecha = AplicacionFunciones::FechaNombreMes($ldFecha->format('F j Y'));
			$lcReferencia = $llReferencia ? '<b>Referencia:</b>' : '';
			$lcCartaEncabezado = "<br><br><br><br><br><b>Bogotá, D.C., $lcFecha</b><br><br>Señores:<br><b>{$this->aPaciente['Plan']}</b><br>Ciudad.<br><br>$lcReferencia";
		}

		return $lcCartaEncabezado;
	}


	private function BuscarConsecutivo($tnIngreso = 0, $tnConsecCita = 0, $tnConsecCons = 0)
	{
		$lnConsec = 0;

		if (empty(trim($tnConsecCita))) {
			$laConsecutivo = $this->oDb
				->select('CORORA')
				->from('ORDAMBL03')
				->where([
					'INGORA' => $tnIngreso,
					'CCOORA' => $tnConsecCons,
				])
				->get('array');
		} else {
			$laConsecutivo = $this->oDb
				->select('CORORA')
				->from('ORDAMBL03')
				->where([
					'INGORA' => $tnIngreso,
					'CCIORA' => $tnConsecCita,
					'CCOORA' => $tnConsecCons,
				])
				->get('array');
		}

		if (is_array($laConsecutivo)) {
			if (count($laConsecutivo) > 0) {
				$lnConsec = $laConsecutivo['CORORA'];
				settype($lnConsec, 'integer');
			}
		}
		unset($laConsecutivo);
		return $lnConsec;
	}


	private function validarFechaInicio($tnIngreso = 0, $tnConsulta = 0)
	{
		$laTemp = $this->oDb
			->select('CCNEPH, CONEPH')
			->from('RIAEPH')
			->where([
				'NINEPH' => $tnIngreso,
			])
			->getAll('array');

		$lnFechaInicio = 0;
		if (is_array($laTemp)) {
			if (count($laTemp) > 1) {
				$lnConsec = 0;
				foreach ($laTemp as $laConsec) {
					if ($laConsec['CONEPH'] == $tnConsulta) {
						$lnConsec = $laConsec['CCNEPH'] - 1;
					}
				}

				$laTempIH = $this->oDb
					->select('FEEEPD AS FECINI')
					->from('RIAEPHD')
					->where([
						'NINEPD' => $tnIngreso,
						'CCNEPD' => $lnConsec,
						'ESTEPD' => 'A',
					])
					->get('array');
				if (is_array($laTempIH)) {
					if (count($laTempIH) > 0) {
						$lnFechaInicio = $laTempIH['FECINI'];
					}
				}
			}
		}
		unset($laTemp);
		unset($laTempIH);
		return $lnFechaInicio;
	}


	/*
	 *	Retorna array con array para adicionar al cuerpo de datos de impresión en Historia Clínica y Epicrisis
	 */
	public function ordenesHcEpi($taDatos)
	{
		$laOrdenes = $this->retornarDocumento($taDatos, 1);
		$laCuerpo = [];

		if (count($laOrdenes) > 0) {
			$laCuerpo[] = ['titulo1', 'ORDENES AMBULATORIAS'];
			if (!empty(trim($laOrdenes['8']['descripcion']))) {
				$lcInfSubtitulo = explode("¤", $laOrdenes['8']['subtitulo']);
				$lcDetSubtitulo = explode("¤", $laOrdenes['8']['descripcion']);
				if (count($lcInfSubtitulo) > 0) {
					$laCuerpo[] = ['titulo2', $laOrdenes['8']['titulo']];
					foreach ($lcInfSubtitulo as $lnIndice => $laDatoS) {
						$laCuerpo[] = ['texto10', $laDatoS ?? ''];
						$laCuerpo[] = ['texto10', $lcDetSubtitulo[$lnIndice]];
					}
				}
			}

			if (!empty(trim($laOrdenes['9']['descripcion1']))) {
				$laCuerpo[] = ['titulo2', $laOrdenes['9']['titulo1']];
				$laCuerpo[] = ['texto9',	trim($laOrdenes['9']['descripcion1'])];
			}

			if (!empty(trim($laOrdenes['9']['descripcion']))) {
				$laCuerpo[] = ['titulo2', $laOrdenes['9']['titulo']];
				$laCuerpo[] = ['texto9', trim($laOrdenes['9']['descripcion'])];
			}

			if (!empty(trim($laOrdenes['10']['descripcion']))) {
				$laCuerpo[] = ['titulo2', $laOrdenes['10']['titulo']];
				$laCuerpo[] = ['texto9', trim($laOrdenes['10']['descripcion'])];
			}

			if (!empty(trim($laOrdenes['4']['descripcion']))) {
				$laCuerpo[] = ['titulo2', $laOrdenes['4']['titulo']];
				$laCuerpo[] = ['texto9', $laOrdenes['4']['subtitulo']];
				$laCuerpo[] = ['texto9', trim($laOrdenes['4']['descripcion'])];
			}

			if (!empty(trim($laOrdenes['6']['subtitulo']))) {
				$laCuerpo[] = ['titulo2', $laOrdenes['6']['titulo']];
				$laCuerpo[] = ['texto9', $laOrdenes['6']['subtitulo']];
				$laCuerpo[] = ['texto9', trim($laOrdenes['6']['descripcion'])];
			}

			if (!empty(trim($laOrdenes['6']['IncRetroactiva']))) {
				$laCuerpo[] = ['titulo2', 'INCAPACIDAD RETROACTIVA'];
				$laCuerpo[] = ['texto9', trim($laOrdenes['6']['IncRetroactiva'])];
			}

			if (!empty(trim($laOrdenes['6']['IncHospital']))) {
				$laCuerpo[] = ['titulo2', 'INCAPACIDAD HOSPITALARIA'];
				$laCuerpo[] = ['texto9', trim($laOrdenes['6']['IncHospital'])];
			}

			if (!empty(trim($laOrdenes['11']['descripcion'])) || !empty(trim($laOrdenes['12']['descripcion']))) {
				$laCuerpo[] = ['titulo2', 'RECOMENDACIONES'];
				if (!empty(trim($laOrdenes['11']['descripcion']))) {
					$laCuerpo[] = ['titulo3', $laOrdenes['11']['titulo']];
					$laCuerpo[] = ['texto9', trim($laOrdenes['11']['descripcion'])];
				}
				if (!empty(trim($laOrdenes['12']['descripcion']))) {
					$laCuerpo[] = ['titulo3', $laOrdenes['12']['titulo']];
					$laCuerpo[] = ['texto9', trim($laOrdenes['12']['descripcion'])];
				}
			}

			if (!empty(trim($laOrdenes['7']['descripcion']))) {
				$laCuerpo[] = ['titulo2', $laOrdenes['7']['titulo']];
				$laCuerpo[] = ['texto9', trim($laOrdenes['7']['descripcion'])];
			}
		}

		return $laCuerpo;
	}


	/*
	 *	Obtiene datos para nuevos tipos de incapacidad
	 */
	private function obtenerDatosIncapacidad($taData)
	{
		if ($this->nTipoIncapacidad == 2) {
			$laIncapacidad = json_decode($this->aOrdenes['6']['datos'], true);
			$this->aIncapacidad['TipoInc'] = $laIncapacidad['tpi'];
			$this->aIncapacidad['DxRel'] = $laIncapacidad['dxr'];
			$loDatosOA = new DatosAmbulatorios();
			$loParamCn = new ParametrosConsulta();
			$this->aIncapacidad['GrupoServ'] = array_values($loDatosOA->obtenerGrupoServicioVia($taData['cCodVia']))[0] ?? '';
			$this->aIncapacidad['ModPresta'] = $loDatosOA->obtenerModalidadPrestacion($laIncapacidad['mdp']);
			$this->aIncapacidad['Origen'] = $loDatosOA->obtenerParametroIncapacidad(['INCORIG' => 'ORIGEN'], $laIncapacidad['ori']);
			$loParamCn->ObtenerTipoCausa($taData['cCodVia'], ($laIncapacidad['ori'] == '02' ? 'L' : 'C'), $laIncapacidad['cau']);
			$this->aIncapacidad['Causa'] = ucfirst(array_column($loParamCn->TiposCausa(), 'desc')[0] ?? '');
			$this->aIncapacidad['Prorroga'] = $laIncapacidad['pro'] == 'S' ? 'Si' : 'No';

			if (!empty($laIncapacidad['rtr'])) {
				$ldInicio = new \DateTime($laIncapacidad['fir']);
				$ldFinal  = new \DateTime($laIncapacidad['ffr']);
				$lnIntervaloPer = 30;

				$this->aIncapacidad['Retroactiva'] = [
					'tipo'		=> $loDatosOA->obtenerParametroIncapacidad(['INCRTRO' => 'RETROACTIVA'], $laIncapacidad['rtr']),
					'inicio'	=> $laIncapacidad['fir'],
					'final'		=> $laIncapacidad['ffr'],
					'periodos'	=> (new PeriodosFechas($ldInicio, $ldFinal, $lnIntervaloPer))->obtenerPeriodos()
				];
			}
		}
	}
}
