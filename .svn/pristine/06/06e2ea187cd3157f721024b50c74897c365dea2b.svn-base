<?php
namespace NUCLEO;

require_once 'class.Db.php';
require_once 'class.Persona.php';

use NUCLEO\Db;
use NUCLEO\Persona;


class NutricionConsulta
{
	private $oDb;

	function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}


	/*
	 *	Consulta los datos de las dietas de los pacientes de una sección
	 *	@param array $tcTipo: tipo de consulta SECCION, INGRESO o HABITA
	 *	@param string $tcParam: Sección o número de ingreso a consultar, de acuerdo al tipo
	 *	@return array: datos de los pacientes y las dietas
	 */
	public function consultarDatosDieta($tcTipo='', $tcParam='')
	{
		$laWhere =	in_array($tcTipo, ['SECCION'])			? ['H.SECHAB' => $tcParam] : (
					in_array($tcTipo, ['INGRESO','HABITA'])	? ['H.INGHAB' => $tcParam] : '');
		$laPacientes = $this->oDb
			->select('H.SECHAB CODSECC, S.DE1TMA SECCION, H.NUMHAB CAMA, H.INGHAB INGRESO, H.TIDHAB TIPOID, H.NIDHAB NUMID, H.ESTHAB EST_HABITACION')
			->select('P.NM1PAC NOMBRE1, P.NM2PAC NOMBRE2, P.AP1PAC APELLIDO1, P.AP2PAC APELLIDO2, P.FNAPAC FECHANAC, I.ESTING EST_INGRESO')
			->select('IFNULL((SELECT MAX(CONNTR) FROM RIANUTR N WHERE N.TIDNTR=H.TIDHAB AND N.NIDNTR=H.NIDHAB AND N.NINNTR=H.INGHAB AND N.ESTNTR=3),0) CNSNUT')
			->from('FACHAB H')
			->innerJoin('RIAPAC P', 'H.TIDHAB=P.TIDPAC AND H.NIDHAB=P.NIDPAC')
			->innerJoin('RIAING I', 'H.INGHAB=I.NIGING')
			->innerJoin('TABMAE S', "S.TIPTMA='SECHAB' AND H.SECHAB=S.CL1TMA")
			->where($laWhere)->where("H.IDDHAB=0 AND H.INGHAB>0 AND H.NIDHAB>0 AND H.ESTHAB IN ('1', '2')")
			->getAll('array');
		if ($this->oDb->numRows()==0) {
			return [];
		}
		$loPersona = new Persona();

		$ltAhora = new \DateTime($this->oDb->fechaHoraSistema());
		$lcFecha = $ltAhora->format('Ymd');

		foreach ($laPacientes as &$laPaciente) {
			$laPaciente = array_map('trim',$laPaciente);

			// Calcular la edad
			$loPersona->nNacio = $laPaciente['FECHANAC'];
			$laEdad = explode('-', $loPersona->getEdad($lcFecha, '%y-%m-%d'));
			$laPaciente['EDAD'] = (is_array($laEdad) && count($laEdad)>=3) ? ['A'=>$laEdad[0], 'M'=>$laEdad[1], 'D'=>$laEdad[2]] : ['A'=>0, 'M'=>0, 'D'=>0];

			if ($laPaciente['EST_INGRESO']=='2') {
				// Obtener dieta
				$laDieta = $this->oDb
					->select('DESNTR, ALMNTR, COMNTR, CONSDT, ODDNTR')
					->from('RIANUDT')
					->where([
						'NINNDT' => $laPaciente['INGRESO'],
						'CONNDT' => $laPaciente['CNSNUT'],
					])
					->orderBy('CONSDT')
					->getAll('array');
				if ($this->oDb->numRows()>0) {
					// Dietas
					$laPaciente['DIETA_D'] = trim($laDieta[0]['DESNTR']);
					$laPaciente['DIETA_A'] = trim($laDieta[0]['ALMNTR']);
					$laPaciente['DIETA_C'] = trim($laDieta[0]['COMNTR']);
					$laPaciente['OBS_D'] = $laPaciente['OBS_A'] = $laPaciente['OBS_C'] = '';
					// Observaciones
					foreach ($laDieta as $laFila) {
						if ($laFila['CONSDT']>100) {
							$lcClave =	$laFila['CONSDT']>100 && $laFila['CONSDT']<200 ? 'OBS_D' : (
										$laFila['CONSDT']>200 && $laFila['CONSDT']<300 ? 'OBS_A' : (
										$laFila['CONSDT']>300 && $laFila['CONSDT']<400 ? 'OBS_C' : ''));
							$laPaciente[$lcClave] .= $laFila['ODDNTR'];
						}
					}
					$laPaciente['OBS_D'] = trim($laPaciente['OBS_D']);
					$laPaciente['OBS_A'] = trim($laPaciente['OBS_A']);
					$laPaciente['OBS_C'] = trim($laPaciente['OBS_C']);
				}
			}
		}

		return $laPacientes;
	}


	/*
	 *	Consulta datos de envío para Medirest y hace el envío correspondiente
	 *	@param array $tcTipo: tipo de consulta SECCION, INGRESO, HABITA (Envío de una sección, de un ingreso o de un ingreso para hacer cambio de habitación)
	 *	@param string $tcParam: Sección o número de ingreso a consultar, de acuerdo al tipo
	 *	@param string $tbSalida: marcar paciente con salida (solo si se envía tipo INGRESO)
	 *	@return array: Datos organizados para envío
	 */
	public function generarEnviarDatosMedirest($tcTipo, $tcParam, $tbSalida=false)
	{
		$laDatos = $this->generarDatosMedirest($tcTipo, $tcParam, $tbSalida);
		if ($laDatos['success']) {
			if (isset($laDatos['datos'])) {
				if (is_array($laDatos['datos'])) {
					if (count($laDatos['datos'])>0 ) {
						$laRta = $this->enviarDatosMedirest($laDatos['datos']);
					} else {
						$laRta = ['success'=>false, 'message'=>'No existen datos para enviar.'];
					}
				} else {
					$laRta = ['success'=>false, 'message'=>'No se recuperaron datos.'];
				}
			} else {
				$laRta = ['success'=>false, 'message'=>'Ocurrió un error al consultar los datos.'];
			}
		} else {
			$laRta = $laDatos;
		}

		return $laRta;
	}


	/*
	 *	Genera array con datos de envío para Medirest
	 *	@param array $tcTipo: tipo de consulta SECCION, INGRESO, HABITA (Envío de una sección, de un ingreso o de un ingreso para hacer cambio de habitación)
	 *	@param string $tcParam: Sección o número de ingreso a consultar, de acuerdo al tipo
	 *	@param string $tbSalida: marcar paciente con salida (solo si se envía tipo INGRESO)
	 *	@return array: Datos organizados para envío
	 */
	public function generarDatosMedirest($tcTipo, $tcParam, $tbSalida=false)
	{
		$lcUsuario = '2265';
		$lcCodUnidad = '34';
		$laPacientes = [];

		// Validar los tipos
		$tcTipo = strtoupper($tcTipo);
		if (!in_array($tcTipo, ['SECCION', 'INGRESO', 'HABITA'])) {
			return [
				'success' => false,
				'message' => 'Tipo de consulta incorrecta.',
			];
		}

		$laActivo = json_decode(trim($this->oDb->obtenerTabmae1("DE2TMA","NUTRICIO","CL1TMA='DIETAS' AND CL2TMA='ACTIVO' AND ESTTMA=''", null, '{"envio":false,"salida":false}')), true);

		if ($tbSalida) {
			if ($laActivo['salida']==false) {
				return [
					'success' => false,
					'message' => 'Envío de Salida nutrición no se encuentra activo.',
				];
			}
			$laPacientes[] = $this->datosSalida($tcParam);

		} else {
			if ($laActivo['envio']==false) {
				return [
					'success' => false,
					'message' => 'Envío nutrición no se encuentra activo.',
				];
			}
			// Validar los horarios de envío válidos
			$laHorarioDietas = '{"D":{"desde":60000,"hasta":100000},"A":{"desde":100000,"hasta":160000},"C":{"desde":160000,"hasta":200000}}';
			$laHorarioDietas = json_decode($this->oDb->obtenerTabmae1('DE2TMA||OP5TMA','NUTRICIO',"CL1TMA='DIETAS' AND CL2TMA='HORARIOS' AND ESTTMA=''", null, $laHorarioDietas), true);
			$ltAhora = new \DateTime($this->oDb->fechaHoraSistema());
			$lcFecha = $ltAhora->format('Ymd');
			$lcHora  = $ltAhora->format('His');
			$lcDieta = '';
			foreach ($laHorarioDietas as $lcLetra => $laRango) {
				if ($lcHora>=$laRango['desde'] && $lcHora<=$laRango['hasta']) {
					$lcDieta = $lcLetra;
				}
			}
			if (empty($lcDieta)) {
				if ($tcTipo=='HABITA') {
					$laPacientes[] = $this->datosSalida($tcParam);
				} else {
					return [
						'success' => false,
						'message' => 'No se encuentra en una de las horas de envío.',
					];
				}

			} else {
				// Obtener los datos
				$taDatos = $this->consultarDatosDieta($tcTipo, $tcParam);
				if (count($taDatos)==0) {
					return [
						'success' => false,
						'message' => 'No existen datos para enviar.',
					];
				}
				$laDietas = $this->consultaDietas(true);

				foreach ($taDatos as $laDato) {
					$lcDietaPac = isset($laDato['DIETA_'.$lcDieta]) ? ($laDietas[$laDato['DIETA_'.$lcDieta]] ?? $laDato['DIETA_'.$lcDieta]) : '';
					if ($tcTipo=='HABITA' && empty($lcDietaPac)) {
						$laPacientes[] = $this->datosSalida($tcParam);
					} else {
						$lcObserva = isset($laDato['OBS_D']) ? trim($laDato['OBS_D']."\n".$laDato['OBS_A']."\n".$laDato['OBS_C']) : '';
						//	$lcEdad = "{$laDato['EDAD']['A']}a {$laDato['EDAD']['M']}m {$laDato['EDAD']['D']}d";
						$lcEdad = $laDato['EDAD']['A']>0 ? $laDato['EDAD']['A'].' AÑO'.($laDato['EDAD']['A']>1 ? 'S'  : '') :
								( $laDato['EDAD']['M']>0 ? $laDato['EDAD']['M'].' MES'.($laDato['EDAD']['M']>1 ? 'ES' : '') :
								( $laDato['EDAD']['D']>0 ? $laDato['EDAD']['D'].' DÍA'.($laDato['EDAD']['D']>1 ? 'S'  : '') : ''));
						$laPacientes[] = [
							'HISTORIA' => $laDato['INGRESO'],
							'UBICACION' => $laDato['SECCION'],
							'CAMA' => $laDato['CODSECC'].'-'.$laDato['CAMA'],
							'PACIENTE' => trim($laDato['NOMBRE1'].' '.$laDato['NOMBRE2'].' '.$laDato['APELLIDO1'].' '.$laDato['APELLIDO2']),
							'VIP' => 'NO',
							'EDAD' => $lcEdad,
							'DIETA' => $lcDietaPac,
							'OBSERVACIONES' => $lcObserva,
							'ALERGIAS' => '',
							'ESTADO' => $laDato['EST_INGRESO']==2 ? 1 : 0,
						];
					}
				}
			}
		}

		return [
			'success' => true,
			'datos' => [
				'PacienteCenso' => $laPacientes,
				'IdUsuario' => $lcUsuario,
				'CodUnidad' => $lcCodUnidad,
				'ValidarDieta' => 'S',
			],
		];
	}


	/*
	 *	Obtienen array de salida de un ingreso
	 *	@param numero $tnIngreso: Número de ingreso
	 *	@return array: datos para enviar
	 */
	public function datosSalida($tnIngreso)
	{
		$laPac = $this->oDb
			->select('FNAPAC,NM1PAC,NM2PAC,AP1PAC,AP2PAC')
			->from('RIAING')
			->innerJoin('RIAPAC','TIDING=TIDPAC AND NIDING=NIDPAC')
			->where(['NIGING'=>$tnIngreso])
			->get('array');
		if ($this->oDb->numRows()==0) {
			return [];
		}
		$laPac = array_map('trim', $laPac);

		/*
		// Calcular la edad
		$ltAhora = new \DateTime($this->oDb->fechaHoraSistema());
		$lcFecha = $ltAhora->format('Ymd');
		$loPersona = new Persona();
		$loPersona->nNacio = $laPac['FNAPAC'];
		$laEdadP = explode('-', $loPersona->getEdad($lcFecha, '%y-%m-%d'));
		$laEdad = (is_array($laEdadP) && count($laEdadP)>=3) ? ['A'=>$laEdadP[0], 'M'=>$laEdadP[1], 'D'=>$laEdadP[2]] : ['A'=>0, 'M'=>0, 'D'=>0];
		unset($loPersona);
		$lcEdad = $laEdad['A']>0 ? $laEdad['A'].' AÑO'.($laEdad['A']>1 ? 'S'  : '') :
				( $laEdad['M']>0 ? $laEdad['M'].' MES'.($laEdad['M']>1 ? 'ES' : '') :
				( $laEdad['D']>0 ? $laEdad['D'].' DÍA'.($laEdad['D']>1 ? 'S'  : '') : ''));
		*/
		$lcEdad = '';

		return [
			'HISTORIA' => $tnIngreso,
			'ESTADO' => 0,
			'UBICACION' => '',
			'CAMA' => '',
			'PACIENTE' => trim($laPac['NM1PAC'].' '.$laPac['NM2PAC'].' '.$laPac['AP1PAC'].' '.$laPac['AP2PAC']),
			'VIP' => 'NO',
			'EDAD' => $lcEdad,
			'DIETA' => '',
			'OBSERVACIONES' => '',
			'ALERGIAS' => '',
		];
	}


	/*
	 *	Envío de datos al servidor Medirest Diet
	 *	@param array $taDatos: Datos a enviar
	 *	@return array: respuesta o error
	 */
	public function enviarDatosMedirest($taDatos)
	{
		$lcURL = 'https://medirest.compass-group.com.co/MedirestDietMobile/api/carguemasivocenso';
		$lcURL = trim($this->oDb->obtenerTabmae1('DE2TMA','NUTRICIO',"CL1TMA='DIETAS' AND CL2TMA='URLDIET' AND ESTTMA=''", null, $lcURL));
		$laEstadosOK = explode(',', trim($this->oDb->obtenerTabmae1('DE2TMA','NUTRICIO',"CL1TMA='DIETAS' AND CL2TMA='ESTADOK' AND ESTTMA=''", null, '0,44,47,58,55,50,51')));
		$laResultado = [
			'success' => false,
			'header' => '',
			'body' => '',
			'http_code' => '',
			'last_url' => '',
			'rta' => '',
			'message' => '',
		];

		try {
			$lcPostData = json_encode($taDatos);
			$loCurl = curl_init();
			curl_setopt_array($loCurl, [
				CURLOPT_URL => $lcURL,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_HEADER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => $lcPostData,
				CURLOPT_HTTPHEADER => [
					'Content-Type: application/json'
				],
			]);


			$this->fnEscribirLog('Envío: ' . $lcPostData);


			$lnRtaNum = 0;
			$lcRtaBody = '';
			while (empty($lcRtaBody) && $lnRtaNum<20) {
				$loRespuesta = curl_exec($loCurl);
				$lcError = curl_error($loCurl);

				if ($lcError != '') {
					curl_close($loCurl);
					$laResultado['message'] = 'CURL:' . $lcError;
					return $laResultado;
				}

				$header_size = curl_getinfo($loCurl, CURLINFO_HEADER_SIZE);
				$laResultado['header'] = substr($loRespuesta, 0, $header_size);
				$laResultado['body'] = substr($loRespuesta, $header_size);
				$laResultado['http_code'] = curl_getinfo($loCurl, CURLINFO_HTTP_CODE);
				$laResultado['last_url'] = curl_getinfo($loCurl, CURLINFO_EFFECTIVE_URL);
				$laResultado['rta'] = json_decode($laResultado['body'], true);

				$lcRtaBody = trim($laResultado['body']);
				if (empty($laResultado['body']) || in_array($laResultado['body'], ['[]','[ ]'])) {
					$lcRtaBody = '';
					usleep(0.5*1000000);
					// sleep(1);
				}
				$lnRtaNum++;
			}
			curl_close($loCurl);


			$this->fnEscribirLog('Respuesta: ' . $laResultado['body']);

			if (in_array($laResultado['body'], ['','[]','[ ]'])) {
				$laResultado['message'] = 'MedirestDiet no retornó resultado';
				$laResultado['success'] = false;
			} else {
				if ((!empty($laResultado['body'])) && is_array($laResultado['rta'])) {
					$laResultado['success'] = true;
					foreach ($laResultado['rta'] as $laPac) {
						$laResultado['message'] .= "{$laPac['cama']} | {$laPac['historia']} | {$laPac['validacion']}\r\n";
						if (!in_array($laPac['idEstadoCargueExcel'], $laEstadosOK)) {
							$laResultado['success'] = false;
						}
					}
				} else {
					$laResultado['message'] = $laResultado['body'];
					$laResultado['success'] = false;
				}
			}

		} catch (\Exception $e) {
			$laResultado['message'] = $e->getMessage();
			if ($laResultado['body']) {
				$laResultado['message'] .= ' | ' . $laResultado['body'];
			}
		}

		return $laResultado;
	}


	/*
	*	Consulta Dietas
	*	@param array $tbAsociativo: true si se desea retornar un array asociativo, de lo contrario un array con CODIGO, DESCRIPCION
	*	@return array: lista de dietas
	*/
	public function consultaDietas($tbAsociativo=false)
	{
		$laDietas = $this->oDb
			->select('TRIM(DE1TMA) AS CODIGO, TRIM(DE2TMA) AS DESCRIPCION')
			->from('TABMAE')
			->where("TIPTMA='DIESHA' AND ESTTMA=''")
			->orderBy('DE2TMA')
			->getAll('array');
		if ($this->oDb->numRows()==0) {
			return [];
		} else {
			if ($tbAsociativo) {
				$laRta = [];
				foreach ($laDietas as $laDieta) {
					$laRta[$laDieta['CODIGO']] = $laDieta['DESCRIPCION'];
				}
				return $laRta;
			} else {
				return $laDietas;
			}
		}
	}



	function fnEscribirLog($tcMensaje) {
		$lcFileLog = __DIR__ . '/../../logs/Log_Nutricion_'. date('Ym').'.log';
		$lcFecha = (new \DateTime())->format('Y-m-d H:i:s.u');
		$lcMensaje = $lcFecha . ' | ' . $tcMensaje . PHP_EOL;
		$lnFile = fopen($lcFileLog, 'a');
		chmod($lcFileLog, 0777);
		fputs($lnFile, $lcMensaje);
		fclose($lnFile);
	}

}
