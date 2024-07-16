<?php
namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';
require_once __DIR__ . '/class.MiPresFunciones.php';
require_once __DIR__ . '/class.AplicacionFunciones.php';

use NUCLEO\MiPresFunciones;
use NUCLEO\AplicacionFunciones;


class MiPresConsultaGuardar
{
	private $oDb;
	public $aCfg = [];

	function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
		$this->obtenerConfig();
	}

	/*
	 *	Descarga prescripciones y las guarda en AS400
	 *	@param string $tcFecha: fecha en formato AAAA-MM-DD
	 *	@return array: Mensajes de error generados en el proceso
	 */
	public function prescripciones($tcFecha)
	{
		global $goAplicacionTareaManejador;
		$lbAppTarea = is_object($goAplicacionTareaManejador);

		// Parámetros
		$lcAcc = 'Prescripciones';
		$laRta = [];
		$lcUrl = MiPresFunciones::fcVariables('urlPrescribe').'Prescripcion/{nit}/{fecha}/{token}';
		$laRetorna = MiPresFunciones::fnConsumirMiPres(['url'=>$lcUrl, 'fecha'=>$tcFecha], 'GET');

		// Variables de log
		$ltAhora = new \DateTime($this->oDb->fechaHoraSistema());
		$lcFecha = $ltAhora->format('Ymd');
		$lcHora  = $ltAhora->format('His');
		$lcUsu = 'SRVWEB';
		$lcPrg = 'TR_MP_PRS';
		$laLog = [$lcUsu, $lcPrg, $lcFecha, $lcHora];

		// Guarda los datos retornados
		if (isset($laRetorna['MIPRES'])) {
			$laRetorna = json_decode(json_encode($laRetorna), true);

			$lnNumI=$lnNumU=0;
			foreach ($laRetorna['MIPRES'] as $lnNumRet=>$laMiPres) {
				$laMiPres = array_change_key_case($laMiPres, CASE_LOWER);
				try{
					$lbInsertar = true;
					$lcNumPres = $laMiPres['prescripcion']['NoPrescripcion'];
					$laWhere = ['NPRWCA' => $lcNumPres,];
					$lcTabla = $this->aCfg['TABLAS']['prescripcion']['tabla'];
					$laReg = $this->oDb->tabla($lcTabla)->where($laWhere)->get('array');
					if ($this->oDb->numRows() > 0) $lbInsertar = false;

					if ($lbInsertar) {
						foreach ($this->aCfg['SERV'] as $lcServicio=>$laServicio) {
							if (count($laMiPres[$lcServicio]??[])>0) {
								$lcTabla = $this->aCfg['TABLAS'][$lcServicio]['tabla'];
								if ($lcServicio=='prescripcion') {
									$laData = $this->generarArray($lcServicio, $laMiPres[$lcServicio], $lcNumPres, $laLog, $lbInsertar);
									$this->oDb->tabla($lcTabla)->insertar(array_merge($laData[0],$laData[1]));
								} else {
									foreach ($laMiPres[$lcServicio] as $laMiPresServ) {
										$laData = $this->generarArray($lcServicio, $laMiPresServ, $lcNumPres, $laLog);
										$this->oDb->tabla($lcTabla)->insertar(array_merge($laData[0],$laData[1]));
										if ($lcServicio=='medicamentos') {
											$lcTablaPA = $this->aCfg['TABLAS']['principiosactivos']['tabla'];
											foreach ($laMiPresServ['PrincipiosActivos'] as $laPrincActiv) {
												$laData = $this->generarArray('principiosactivos', $laPrincActiv, $lcNumPres, $laLog);
												$this->oDb->tabla($lcTablaPA)->insertar(array_merge($laData[0],$laData[1]));
											}
										}
									}
								}
							}
						}
						$lnNumI++;
					} else {
						// Solo se valida el estado por ser lo único que se actualiza
						if (!($laReg['EPRWCA']==$laMiPres['prescripcion']['EstPres'])) {
							$laData = $this->generarArray('prescripcion', $laMiPres['prescripcion'], $lcNumPres, $laLog);
							$this->oDb->tabla($lcTabla)->where($laWhere)->actualizar(array_merge($laData[0],$laData[2]));
							$lnNumU++;
							// las tecnologías no se modifican, por eso no se actualizan
						// } else {
							// Prescripcion $lcNumPres NO se actualiza
						}
					}

				} catch(Exception $loError){
					$lcMsg = $lcAcc.' - '.$loError->getMessage();
					$laRta[] = $lcMsg;
					if ($lbAppTarea) $goAplicacionTareaManejador->evento($lcMsg);
				} catch(PDOException $loError){
					$lcMsg = $lcAcc.' - '.$loError->getMessage();
					$laRta[] = $lcMsg;
					if ($lbAppTarea) $goAplicacionTareaManejador->evento($lcMsg);
				}
			}
			if ($lbAppTarea) $goAplicacionTareaManejador->evento($lcAcc.'. Se insertaron '.$lnNumI.' y se actualizaron '.$lnNumU.' - Total registros '.count($laRetorna['MIPRES']).' registros para la fecha '.$tcFecha);
		} else {
			$lcMsg = $lcAcc.'. Para la fecha '.$tcFecha.' el webservice no retornó datos';
			if (isset($laRetorna['MIPRES'])) {
				if (!empty($laRetorna['Error'])) {
					$lcMsg = $lcAcc.'. Para la fecha '.$tcFecha.' el webservice retornó error: '.$laRetorna['Error'];
				}
			}
			$laRta[] = $lcMsg;
			if ($lbAppTarea) $goAplicacionTareaManejador->evento($lcMsg);
		}

		return $laRta;
	}


	/*
	 *	Descarga Novedades y las guarda en AS400
	 *	@param string $tcFecha: fecha en formato AAAA-MM-DD
	 *	@return array: Mensajes de error generados en el proceso
	 */
	public function novedades($tcFecha)
	{
		global $goAplicacionTareaManejador;
		$lbAppTarea = is_object($goAplicacionTareaManejador);

		// Parámetros
		$lcAcc = 'Novedades';
		$laRta = [];
		$lcUrl = MiPresFunciones::fcVariables('urlPrescribe').'NovedadesPrescripcion/{nit}/{fecha}/{token} ';
		$laRetorna = MiPresFunciones::fnConsumirMiPres(['url'=>$lcUrl, 'fecha'=>$tcFecha], 'GET');

		// Variables de log (Fecha y hora de consulta)
		$ltAhora = new \DateTime($this->oDb->fechaHoraSistema());
		$laLog = [
			'SRVWEB',					// Usuario
			'TR_MP_PRS',				// Programa
			$ltAhora->format('Ymd'),	// Fecha
			$ltAhora->format('His'),	// Hora
		];

		// Guarda los datos retornados
		if (isset($laRetorna['MIPRES'])) {
			$laRetorna = json_decode(json_encode($laRetorna), true);

			$lnNumI=$lnNumU=0;
			foreach ($laRetorna['MIPRES'] as $laMiPres) {
				try{
					$lbInsertar = true;
					$lcTipoNov = $laMiPres['prescripcion_novedades']['TipoNov']; // 1: Modificación, 2: Anulación, 3: Transcripción
					$lcNumPresI = $laMiPres['prescripcion_novedades']['NoPrescripcion'];
					$lcNumPresF = $laMiPres['prescripcion_novedades']['NoPrescripcionF'];
					$laWhere = ['PINWNO' => $lcNumPresI, 'PFIWNO' => $lcNumPresF];
					$lcTabla = $this->aCfg['TABLAS']['novedades']['tabla'];
					$lcTablaPr = $this->aCfg['TABLAS']['prescripcion']['tabla'];
					$laReg = $this->oDb->tabla($lcTabla)->where($laWhere)->get('array');
					if ($this->oDb->numRows() > 0) $lbInsertar = false;

					if ($lbInsertar) {
						$laData = $this->generarArray('novedades', $laMiPres['prescripcion_novedades'], '', $laLog, $lbInsertar);
						$this->oDb->tabla($lcTabla)->insertar(array_merge($laData[0],$laData[1]));
						// Actualiza la prescripción
						$this->oDb->tabla($lcTablaPr)
							->where(['NPRWCA'=>$lcNumPresI])
							->update([
								'ESTWCA'=>$lcTipoNov,
								'UMOWCA'=>$laLog[0],
								'PMOWCA'=>$laLog[1],
								'FMOWCA'=>$laLog[2],
								'HMOWCA'=>$laLog[3],
							]);
						// Deshabilita Mipres como CTC para Modificación y Anulación
						if ($this->aCfg['OTR_CONF']['NOV_CTC'] && in_array($lcTipoNov, ['1','2'])) {
							$laRta = array_merge($laRta, $this->NovedadCTC($lcNumPresI, $lcTipoNov, $laLog));
						}
						$lnNumI++;
					}

				} catch(Exception $loError){
					$lcMsg = $lcAcc.' - '.$loError->getMessage();
					$laRta[] = $lcMsg;
					if ($lbAppTarea) $goAplicacionTareaManejador->evento($lcMsg);
				} catch(PDOException $loError){
					$lcMsg = $lcAcc.' - '.$loError->getMessage();
					$laRta[] = $lcMsg;
					if ($lbAppTarea) $goAplicacionTareaManejador->evento($lcMsg);
				}
			}
			if ($lbAppTarea) $goAplicacionTareaManejador->evento($lcAcc.'. Se insertaron '.$lnNumI.' - Total registros '.count($laRetorna['MIPRES']).' registros para la fecha '.$tcFecha);
		} else {
			$lcMsg = $lcAcc.'. Para la fecha '.$tcFecha.' el webservice no retornó datos';
			if (isset($laRetorna['MIPRES'])) {
				if (!empty($laRetorna['Error'])) {
					$lcMsg = $lcAcc.'. Para la fecha '.$tcFecha.' el webservice retornó error: '.$laRetorna['Error'];
				}
			}
			$laRta[] = $lcMsg;
			if ($lbAppTarea) $goAplicacionTareaManejador->evento($lcMsg);
		}

		return $laRta;
	}


	/*
	 *	Desactiva Prescripción como CTC después de una novedad
	 *	@param string $tcNumPres: número de prescripción inicial de la novedad
	 *	@param integer $tnTipoNovedad: tipo de novedad
	 *	@param array $taLog: log para guardar o insertar
	 *	@return array: Mensajes de error generados en el proceso
	 */
	private function NovedadCTC($tcNumPres, $tnTipoNovedad, $taLog)
	{
		$laRta = [];
		$laMiPresCTC = $this->oDb
			->select('NJUJMP,INGJMP,TICJMP,CNSJMP,CODJMP,TCNJMP,CCOJMP,CCIJMP')
			->from('NPJSMP')
			->where(['NPRJMP'=>$tcNumPres,'ESTJMP'=>0])
			->getAll('array');
		if ($this->oDb->numRows()>0) {
			$lnEstado = 10-$tnTipoNovedad;

			foreach ($laMiPresCTC as $laCTC) {
				$laCTC = array_map('trim',$laCTC);
				$lnIngresoCTC = $laCTC['INGJMP'];
				$lcTipoConsumoCTC = $laCTC['TCNJMP'];
				$lcTipoMiPresCTC = $laCTC['TICJMP'];
				$lcCnsMiPresCTC = $laCTC['CNSJMP'];
				$lcCodigoCTC = $laCTC['CCOJMP'];
				$lnCnsCitaCTC = $laCTC['CCIJMP'];
				$lnNumJusCTC = $laCTC['NJUJMP'];
				$lcCodMiPresCTC = $laCTC['CODJMP'];

				// Desactivar CTC
				$lbRta = $this->oDb
					->tabla('NPJSMP')
					->where([
						'NPRJMP'=>$tcNumPres,
						'INGJMP'=>$lnIngresoCTC,
						'TCNJMP'=>$lcTipoConsumoCTC,
						'CCOJMP'=>$lcCodigoCTC,
						'CCIJMP'=>$lnCnsCitaCTC,
						'ESTJMP'=>0,
					])
					->update([
						'ESTJMP'=>$lnEstado,
						'UMOJMP'=>$taLog[0],
						'PMOJMP'=>$taLog[1],
						'FMOJMP'=>$taLog[2],
						'HMOJMP'=>$taLog[3],
					]);
				if ($lbRta===true) {
					// Obtener consecutivo de consumo
					$laCnsCns = $this->oDb
						->select('CCONOD')
						->from('NPOSDE')
						->where([
							'INGNOD'=>$lnIngresoCTC,
							'TINNOD'=>$lcTipoConsumoCTC,
							'INSNOD'=>$lcCodigoCTC,
							'NUJNOD'=>$lnNumJusCTC,
							'OP5NOD'=>$tcNumPres,
							'ESTNOD'=>'40',
						])
						->get('array');
					if ($this->oDb->numRows()>0) {
						$lnCnsConsumo = $laCnsCns['CCONOD'];
						// Actualizar Cabecera NOPOS
						$lbRta = $this->oDb
							->tabla('NPOSCA')
							->where([
								'INGNOC'=>$lnIngresoCTC,
								'TINNOC'=>$lcTipoConsumoCTC,
								'INSNOC'=>$lcCodigoCTC,
								'NUJNOC'=>$lnNumJusCTC,
								'CCONOC'=>$lnCnsConsumo,
							])
							->actualizar([
								'ESTNOC'=>'62',
								'UMONOC'=>$taLog[0],
								'PMONOC'=>$taLog[1],
								'FMONOC'=>$taLog[2],
								'HMONOC'=>$taLog[3],
							]);
						if ($lbRta===true) {
							// Registrar Detalle NOPOS
							$lcNovedad = ($this->aCfg['OTR_CONF']['TIPONOV'][$tnTipoNovedad] ?? 'Novedad').' MiPres';
							$lbRta = $this->oDb
								->tabla('NPOSDE')
								->insert([
									'INGNOD'=>$lnIngresoCTC,
									'TINNOD'=>$lcTipoConsumoCTC,
									'INSNOD'=>$lcCodigoCTC,
									'NUJNOD'=>$lnNumJusCTC,
									'CCONOD'=>$lnCnsConsumo,
									'FRENOD'=>$taLog[2],
									'HRENOD'=>$taLog[3],
									'ESTNOD'=>'62',
									'OBSNOD'=>$lcNovedad,
									'OP3NOD'=>$lnCnsCitaCTC,
									'OP2NOD'=>$lcCnsMiPresCTC.$lcCodMiPresCTC,
									'USRNOD'=>$taLog[0],
									'PGMNOD'=>$taLog[1],
									'FECNOD'=>$taLog[2],
									'HORNOD'=>$taLog[3],
								]);
							if ($lbRta===true) {
								// OK
							} else {
								$laRta[] = "Error al registrar detalle NOPOS. NumPres: $tcNumPres";
							}
						} else {
							$laRta[] = "Error al actualizar cabecera NOPOS. NumPres: $tcNumPres";
						}
					} else {
						$laRta[] = "Error al obtener consecutivo de consumo. NumPres: $tcNumPres";
					}
				} else {
					$laRta[] = "Error al desactivar CTC. NumPres: $tcNumPres";
				}
			}
		}
		return [];
	}


	/*
	 *	Genera array de datos para insertar o actualizar
	 *	@param string $tcTipo: tipo de tabla MiPres a utilizar
	 *	@param array $laDatos: datos obtenidos de MiPres
	 *	@param string $lcPrescripcion: número de prescripción MiPres de 20 dígitos
	 *	@param array $laLog: datos para log (usuario, programa, fecha y hora)
	 *	@param boolean $tbInsertar: indica si se va a insertar o no el registro, por defecto false
	 *	@return array: Array con tres elementos (arrays asociativos): Datos, Log Insert, Log Update
	 */
	public function generarArray($tcTipo, $laDatos, $lcPrescripcion, $laLog, $tbInsertar=false)
	{
		$laRta = [[],[],[]]; // Array con Datos, Log Insert, Log Update
		if (isset($this->aCfg['TABLAS'][$tcTipo])) {
			foreach ($this->aCfg['TABLAS'][$tcTipo]['campos'] as $laCampo) {
				switch ($laCampo['tipo']) {
					case 'Z':
						switch ($laCampo['mipres']) {
							// Buscar ingreso para Prescripción
							case '.gIngreso':
								if ($tbInsertar) {
									$laRta[0][$laCampo['as400']] = $this->buscarIngreso($laDatos);
								}
								break;
							// Número de prescripción para servicios
							case '.NumPrescripcion':
								$laRta[0][$laCampo['as400']] = $lcPrescripcion;
								break;
							// Siempre vale cero
							case "'0'":
								$laRta[0][$laCampo['as400']] = '0';
								break;
							// Otros casos ??
							default:
								$laRta[0][$laCampo['as400']] = ($laCampo['mipres']);
						}
						break;

					case 'F':
						$laRta[0][$laCampo['as400']] = str_replace('-','',substr($laDatos[$laCampo['mipres']],0,10));
						break;

					case 'H':
						$laRta[0][$laCampo['as400']] = str_replace(':','',$laDatos[$laCampo['mipres']]);
						break;

					// Otros
					default:
						$laRta[0][$laCampo['as400']] = $laDatos[$laCampo['mipres']] ?? ($laCampo['tipo']=='N' ? '0' : '');
				}
			}
			foreach ($this->aCfg['SERV'][$tcTipo]['insert'] as $lnClave=>$lcCampo) {
				$laRta[1][$lcCampo] = $laLog[$lnClave];
			}
			foreach ($this->aCfg['SERV'][$tcTipo]['update'] as $lnClave=>$lcCampo) {
				$laRta[2][$lcCampo] = $laLog[$lnClave];
			}
		}

		return $laRta;
	}


	/*
	 *	Busca el número de ingreso que corresponde a la prescripción
	 *	@param array $taDatos: Datos recibidos de la prescripción
	 *	@return integer: Número de ingreso, cero si no se encuentra ingreso correspondiente
	 */
	public function buscarIngreso($taDatos)
	{
		$lnIngreso = 0;
		$lcTipoDocMiPres = $taDatos['TipoIDPaciente'];
		$lcTipoDoc = $this->aCfg['OTR_CONF']['TIPDOC_EQ'][$lcTipoDocMiPres] ?? 'X';
		if ($lcTipoDoc=='X') {return 0;}
		$lnNumDoc = $taDatos['NroIDPaciente'];
		if (is_numeric($lnNumDoc)) {
			// si el número es mayor a 13 dígitos
			if (strlen($lnNumDoc)>13) {return 0;}
		} else {
			// si no es número
			return 0;
		}
		$lcFecha = substr($taDatos['FPrescripcion'],0,10);
		$lnFecha = str_replace('-','',$lcFecha);

	// IF !EMPTY($lcTipoDoc) AND !EMPTY($lnNumDoc) AND !EMPTY($lnFecha)
		$lnNumDiasAntes = $this->aCfg['OTR_CONF']['DIAS_ATRAS'] ?? 0;
		if ($lnNumDiasAntes>0) {
			$ldFecha = new \DateTime($lcFecha);
			$lnFechaAntes = $ldFecha->sub(new \DateInterval("P{$lnNumDiasAntes}D"))->format('Ymd');
		} else {
			$lnFechaAntes = $lnFecha;
		}

		$laData = $this->oDb
			->select('NIGING')
			->from('RIAING')
			->where([
				'TIDING'=>$lcTipoDoc,
				'NIDING'=>$lnNumDoc,
			])
			->where("((FEEING > 0 AND ($lnFecha BETWEEN FEIING AND FEEING)) OR (FEEING = 0 AND FEIING <= $lnFecha))")
			->orderBy('NIGING DESC')
			->get('array');
		if ($this->oDb->numRows()>0) {
			$lnIngreso = $laData['NIGING'];
		} else {
			$laData = $this->oDb
				->select('NIGING')
				->from('RIAING')
				->where([
					'TIDING'=>$lcTipoDoc,
					'NIDING'=>$lnNumDoc,
				])
				->where("FEEING > 0 AND (FEEING BETWEEN $lnFechaAntes AND $lnFecha)")
				->orderBy('NIGING DESC')
				->get('array');
			if ($this->oDb->numRows()>0) {
				$lnIngreso = $laData['NIGING'];
			}
		}

		return $lnIngreso;
	}


	/*
	 *	Obtener configuración desde TABMAE en la propiedad $this->aCfg
	 */
	public function obtenerConfig()
	{
		$this->aCfg = [
			'TABLAS' => [],
			'OTR_CONF' => [
				'TIPONOV' => [
					'1' => 'Modificación',
					'2' => 'Anulación',
					'3' => 'Transcripción',
				],
			],
			'SERV' => [
				'prescripcion' => [
					'insert' => ['USRWCA','PGMWCA','FECWCA','HORWCA'],
					'update' => ['UMOWCA','PMOWCA','FMOWCA','HMOWCA'],
				],
				'medicamentos' => [
					'insert' => ['USRWME','PGMWME','FECWME','HORWME'],
					'update' => ['UMOWME','PMOWME','FMOWME','HMOWME'],
				],
				'procedimientos' => [
					'insert' => ['USRWCU','PGMWCU','FECWCU','HORWCU'],
					'update' => ['UMOWCU','PMOWCU','FMOWCU','HMOWCU'],
				],
				'dispositivos' => [
					'insert' => ['USRWDI','PGMWDI','FECWDI','HORWDI'],
					'update' => ['UMOWDI','PMOWDI','FMOWDI','HMOWDI'],
				],
				'productosnutricionales' => [
					'insert' => ['USRWNU','PGMWNU','FECWNU','HORWNU'],
					'update' => ['UMOWNU','PMOWNU','FMOWNU','HMOWNU'],
				],
				'servicioscomplementarios' => [
					'insert' => ['USRWCO','PGMWCO','FECWCO','HORWCO'],
					'update' => ['UMOWCO','PMOWCO','FMOWCO','HMOWCO'],
				],
				'principiosactivos' => [
					'insert' => ['USRWPA','PGMWPA','FECWPA','HORWPA'],
					'update' => ['UMOWPA','PMOWPA','FMOWPA','HMOWPA'],
				],
				'novedades' => [
					'insert' => ['USRWNO','PGMWNO','FECWNO','HORWNO'],
					'update' => ['UMOWNO','PMOWNO','FMOWNO','HMOWNO'],
				],
			],
		];

		// Obtiene campos para prescripciones y novedades
		$laDatos = $this->oDb
			->select('TRIM(CL2TMA) CODIGO, TRIM(DE1TMA) CAMPOMPR, OP1TMA TIPO, TRIM(OP2TMA) CAMPO, TRIM(OP5TMA) VLRZ, TRIM(OP6TMA) ACTUALIZA')
			->from('TABMAE')
			->where("TIPTMA='NOPOS' AND CL1TMA='TBMIPRES' AND NOT(CL2TMA IN ('', '01', '02')) AND ESTTMA=''")
			->orderBy('CL2TMA')
			->getAll('array');
		if ($this->oDb->numRows()>0) {
			$this->aCfg['TABLAS'] = [];
			foreach ($laDatos as $laDato) {
				if (strlen($laDato['CODIGO'])==4) {
					$lcTabla = strtolower($laDato['CAMPOMPR']);
					$laCodigos[$laDato['CODIGO']] = $lcTabla;
					$this->aCfg['TABLAS'][$lcTabla] = [
						'tabla' => $laDato['CAMPO'],
						'campos' => [],
					];
				} else {
					$lcPadre = $laCodigos[substr($laDato['CODIGO'],0,4)] ?? '';
					if (!empty($lcPadre)) {
						$this->aCfg['TABLAS'][$lcPadre]['campos'][] = [
							'mipres' => $laDato['CAMPOMPR'],
							'as400' => $laDato['CAMPO'],
							'tipo' => $laDato['TIPO'],
							'valorz' => $laDato['VLRZ'],
							'actualiza' => $laDato['ACTUALIZA'],
						];
					}
				}
			}
		}

		// Otra configuración
		$laDatos = $this->oDb
			->select('TRIM(CL2TMA) CODIGO, TRIM(DE2TMA) DATO, TRIM(OP2TMA) VAR')
			->from('TABMAE')
			->where("TIPTMA='NOPOS' AND CL1TMA='WSMIPRES' AND SUBSTR(CL2TMA,1,3)='990' AND ESTTMA=''")
			->orderBy('CL2TMA')
			->getAll('array');
		if ($this->oDb->numRows()>0) {
			foreach ($laDatos as $laDato) {
				switch ($laDato['CODIGO']) {
					case '99000001':
						$lcValor = json_decode($laDato['DATO'], 'true');
						break;
					case '99000002':
						$lcValor = intval($laDato['DATO']);
						break;
					case '99000003':
						$lcValor = $laDato['DATO']=='SI';
						break;
					default:
						$lcValor = $laDato['DATO'];
						break;
				}
				$this->aCfg['OTR_CONF'][$laDato['VAR']] = $lcValor;
			}
		}
	}


	public function direccionamientos($tcFechaProc)
	{
		global $goAplicacionTareaManejador;
		$lbAppTarea = is_object($goAplicacionTareaManejador);

		$lcFechaRealiza = str_replace('-','',$tcFechaProc);

		// Consulta direccionamientos día anterior
		$lcUrl = MiPresFunciones::fcVariables('urlDispensar');
		$lcUrlFac = MiPresFunciones::fcVariables('urlFacturar');
		$laConfigs=[
			[
				'url'=>$lcUrl.'DireccionamientoXFecha/{nit}/{tokentmp}/{fecha}',
				'tbl'=>'MIPRDIR',
				'acc'=>'Direccionamiento',
			],
			[
				'url'=>$lcUrl.'ProgramacionXFecha/{nit}/{tokentmp}/{fecha}',
				'tbl'=>'MIPRPRO',
				'acc'=>'Programación',
			],
			[
				'url'=>$lcUrl.'EntregaXFecha/{nit}/{tokentmp}/{fecha}',
				'tbl'=>'MIPRENT',
				'acc'=>'Entrega',
			],
			[
				'url'=>$lcUrl.'ReporteEntregaXFecha/{nit}/{tokentmp}/{fecha}',
				'tbl'=>'MIPRREP',
				'acc'=>'Reporte Entrega',
			],
			[
				'url'=>$lcUrlFac.'FacturacionXFecha/{nit}/{tokenfactmp}/{fecha}',
				'tbl'=>'MIPRFAC',
				'acc'=>'Reporte Facturación',
			],
		];
		$lcUsu='SRVWEB';
		$lcPrg='TR_MP_DIR';

		foreach ($laConfigs as $laConf) {
			$laRetorna = MiPresFunciones::fnConsumirMiPres([ 'url'=>$laConf['url'], 'fecha'=>$tcFechaProc, ], 'GET');
			$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
			$lcFecha = $ltAhora->format('Ymd');
			$lcHora  = $ltAhora->format('His');

			// Guarda los datos retornados
			if (isset($laRetorna['MIPRES'])) {
				$lnNumI=$lnNumU=0;
				foreach ($laRetorna['MIPRES'] as $laMiPres) {
					try{
						switch ($laConf['tbl']) {
							// Direccionamiento
							case 'MIPRDIR':
								$laWhere=['IDDIRC' => $laMiPres->IDDireccionamiento,];
								$laData=[
									'IDENTF' => $laMiPres->ID,
									'IDDIRC' => $laMiPres->IDDireccionamiento,
									'FECHAD' => $lcFechaRealiza,
									'NUMPRS' => $laMiPres->NoPrescripcion,
									'TIPTEC' => $laMiPres->TipoTec,
									'CONTEC' => $laMiPres->ConTec,
									'TIPIDP' => $laMiPres->TipoIDPaciente,
									'NUMIDP' => $laMiPres->NoIDPaciente,
									'NUMENT' => $laMiPres->NoEntrega,
									'NUMSBE' => $laMiPres->NoSubEntrega,
									'FECMAX' => $laMiPres->FecMaxEnt,
									'CATTOT' => $laMiPres->CantTotAEntregar,
									'DIRPAC' => $laMiPres->DirPaciente??'',
									'CODSRV' => $laMiPres->CodSerTecAEntregar??'',
									'NITEPS' => $laMiPres->NoIDEPS,
									'CODEPS' => $laMiPres->CodEPS,
									'FECDIR' => $laMiPres->FecDireccionamiento,
									'ESTDIR' => $laMiPres->EstDireccionamiento,
									'FECANL' => $laMiPres->FecAnulacion??'',
								];
								$laDataI=[
									'USCDMP' => $lcUsu,
									'PGCDMP' => $lcPrg,
									'FECDMP' => $lcFecha,
									'HOCDMP' => $lcHora,
								];
								$laDataU=[
									'USMDMP' => $lcUsu,
									'PGMDMP' => $lcPrg,
									'FEMDMP' => $lcFecha,
									'HOMDMP' => $lcHora,
								];
								break;
							// Programacion
							case 'MIPRPRO':
								$laWhere=['IDPROG' => $laMiPres->IDProgramacion,];
								$laData=[
									'IDENTF' => $laMiPres->ID,
									'IDPROG' => $laMiPres->IDProgramacion,
									'FECHAP' => $lcFechaRealiza,
									'FECMAX' => $laMiPres->FecMaxEnt,
									'CODSRV' => $laMiPres->CodSerTecAEntregar,
									'CATTOT' => $laMiPres->CantTotAEntregar,
									'FECPRO' => $laMiPres->FecProgramacion,
									'ESTPRO' => $laMiPres->EstProgramacion,
									'FECPAN' => $laMiPres->FecAnulacion??'',
								];
								$laDataI=[
									'USCPMP' => $lcUsu,
									'PGCPMP' => $lcPrg,
									'FECPMP' => $lcFecha,
									'HOCPMP' => $lcHora,
								];
								$laDataU=[
									'USMPMP' => $lcUsu,
									'PGMPMP' => $lcPrg,
									'FEMPMP' => $lcFecha,
									'HOMPMP' => $lcHora,
								];
								break;
							// Entrega
							case 'MIPRENT':
								$laWhere=['IDENTR' => $laMiPres->IDEntrega,];
								$laData=[
									'IDENTF' => $laMiPres->ID,
									'IDENTR' => $laMiPres->IDEntrega,
									'FECHAE' => $lcFechaRealiza,
									'NUMPRS' => $laMiPres->NoPrescripcion,
									'TIPTEC' => $laMiPres->TipoTec,
									'CONTEC' => $laMiPres->ConTec,
									'TIPIDP' => $laMiPres->TipoIDPaciente,
									'NUMIDP' => $laMiPres->NoIDPaciente,
									'NUMENT' => $laMiPres->NoEntrega??0,
									'CODSRV' => $laMiPres->CodSerTecEntregado??'',
									'CATTOT' => $laMiPres->CantTotEntregada??0,
									'ENTTOT' => $laMiPres->EntTotal??0,
									'CAUSNO' => $laMiPres->CausaNoEntrega??0,
									'FECENT' => $laMiPres->FecEntrega??'',
									'NOLOTE' => $laMiPres->NoLote??'',
									'TIDREC' => $laMiPres->TipoIDRecibe??'',
									'NIDREC' => $laMiPres->NoIDRecibe??'',
									'ESTENT' => $laMiPres->EstEntrega,
									'FECEAN' => $laMiPres->FecAnulacion??'',
									'CODENT' => isset($laMiPres->CodigosEntrega) ? json_encode($laMiPres->CodigosEntrega) : '',
								];
								$laDataI=[
									'USCEMP' => $lcUsu,
									'PGCEMP' => $lcPrg,
									'FECEMP' => $lcFecha,
									'HOCEMP' => $lcHora,
								];
								$laDataU=[
									'USMEMP' => $lcUsu,
									'PGMEMP' => $lcPrg,
									'FEMEMP' => $lcFecha,
									'HOMEMP' => $lcHora,
								];
								break;
							// Reporte de Entrega
							case 'MIPRREP':
								$laWhere=['IDREPE' => $laMiPres->IDReporteEntrega,];
								$laData=[
									'IDENTF' => $laMiPres->ID,
									'IDREPE' => $laMiPres->IDReporteEntrega,
									'FECHAR' => $lcFechaRealiza,
									'NUMPRS' => $laMiPres->NoPrescripcion,
									'ESTENT' => $laMiPres->EstadoEntrega,
									'CAUSNO' => $laMiPres->CausaNoEntrega??0,
									'VALORE' => $laMiPres->ValorEntregado??0,
									'FECREP' => $laMiPres->FecRepEntrega,
									'ESTREP' => $laMiPres->EstRepEntrega,
									'FECRAN' => $laMiPres->FecAnulacion??'',
								];
								$laDataI=[
									'USCRMP' => $lcUsu,
									'PGCRMP' => $lcPrg,
									'FECRMP' => $lcFecha,
									'HOCRMP' => $lcHora,
								];
								$laDataU=[
									'USMRMP' => $lcUsu,
									'PGMRMP' => $lcPrg,
									'FEMRMP' => $lcFecha,
									'HOMRMP' => $lcHora,
								];
								break;
							// Reporte de Facturación
							case 'MIPRFAC':
								$laWhere=['IDFACT' => $laMiPres->IDFacturacion,];
								$laData=[
									'IDENTF' => $laMiPres->ID,
									'IDFACT' => $laMiPres->IDFacturacion,
									'FECHAF' => $lcFechaRealiza,
									'NUMPRS' => $laMiPres->NoPrescripcion,
									'TIPTEC' => $laMiPres->TipoTec,
									'CONTEC' => $laMiPres->ConTec,
									'TIPIDP' => $laMiPres->TipoIDPaciente,
									'NUMIDP' => $laMiPres->NoIDPaciente,
									'NUMENT' => $laMiPres->NoEntrega??0,
									'NUMFAC' => $laMiPres->NoFactura,
									'NITEPS' => $laMiPres->NoIDEPS,
									'CODEPS' => $laMiPres->CodEPS,
									'CODSRV' => $laMiPres->CodSerTecAEntregado,
									'CNTUMN' => $laMiPres->CantUnMinDis,
									'VLRFAC' => $laMiPres->ValorUnitFacturado,
									'VLRFAT' => $laMiPres->ValorTotFacturado,
									'CUOTAM' => $laMiPres->CuotaModer,
									'COPAGO' => $laMiPres->Copago,
									'FECFAC' => $laMiPres->FecFacturacion,
									'ESTFAC' => $laMiPres->EstFacturacion,
									'FECFAN' => $laMiPres->FecAnulacion??'',
								];
								$laDataI=[
									'USCFMP' => $lcUsu,
									'PGCFMP' => $lcPrg,
									'FECFMP' => $lcFecha,
									'HOCFMP' => $lcHora,
								];
								$laDataU=[
									'USMFMP' => $lcUsu,
									'PGMFMP' => $lcPrg,
									'FEMFMP' => $lcFecha,
									'HOMFMP' => $lcHora,
								];
								break;
						}
						$lbInsertar = true;
						$laReg = $this->oDb->tabla($laConf['tbl'])->where($laWhere)->get('array');
						if (is_array($laReg)) if (count($laReg)>0) $lbInsertar = false;

						if ($lbInsertar) {
							$this->oDb->tabla($laConf['tbl'])->insertar(array_merge($laData,$laDataI));
							$lnNumI++;
						} else {
							$this->oDb->tabla($laConf['tbl'])->where($laWhere)->actualizar(array_merge($laData,$laDataU));
							$lnNumU++;
						}
					} catch(Exception $loError){
						if ($lbAppTarea) $goAplicacionTareaManejador->evento($laConf['acc'].' - '.$loError->getMessage());
					} catch(PDOException $loError){
						if ($lbAppTarea) $goAplicacionTareaManejador->evento($laConf['acc'].' - '.$loError->getMessage());
					}
				}
				if ($lbAppTarea) $goAplicacionTareaManejador->evento($laConf['acc'].'. Se insertaron '.$lnNumI.' y se actualizaron '.$lnNumU.' - Total registros '.count($laRetorna['MIPRES']).' registros para la fecha '.$tcFechaProc);
			} else {
				$lcMsg = $laConf['acc'].'. Para la fecha '.$tcFechaProc.' el webservice no retornó datos';
				if (isset($laRetorna['MIPRES'])) {
					if (!empty($laRetorna['Error'])) {
						$lcMsg = $laConf['acc'].'. Para la fecha '.$tcFechaProc.' el webservice retornó error: '.$laRetorna['Error'];
					}
				}
				if ($lbAppTarea) $goAplicacionTareaManejador->evento($lcMsg);
			}
		}
	}

}