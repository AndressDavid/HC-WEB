<?php
namespace NUCLEO;

require_once __DIR__ . '/class.AgendaPaciente.php';


class AgendaPacienteDD extends AgendaPaciente
{
	private $cTokenDD = '';
	private $cCookieDD = '';
	private $cRutaDD = '';
	private $aIPsValidas = '';

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
		$this->obtenerIPsValidas();
		$this->cRutaDD = $this->oDb->obtenerTabMae1('DE2TMA', 'DONDOCT', "CL1TMA='RUTAAPI' AND ESTTMA=''", null, '');
		$this->cCookieDD = $this->oDb->obtenerTabMae1('TRIM(DE2TMA || OP5TMA)', 'DONDOCT', "CL1TMA='COOKIE' AND ESTTMA=''", null, '');
	}


	/*
	 *	Inserta o Actualiza la información de un paciente desde datos de DonDoctor
	 *	@param array $taDatosDD: arreglo con los datos recibidos de DonDoctor
	 *	@param string $tcUser: Usuario que ejecuta la acción
	 *	@param string $tcProg: Programa que ejecuta la acción
	 *	@return array con los elementos: success (true o false), mensaje
	 */
	public function pacienteDD($taDatosDD, $tcUser='AGENDAWEB', $tcProg='PACDD')
	{
		$laDatos = $this->datosPacienteDD($taDatosDD);

		$laRta = $this->validarDatosPac($laDatos);
		if ($laRta['success']) {
			$laExistePac = $this->existePaciente($laDatos['tipoId'], $laDatos['numeroId']);
			if ($laExistePac['success']) {
				$laRta = $this->actualizarPaciente($laDatos, $tcUser, $tcProg, true);
			} else {
				$laRta = $this->insertarPaciente($laDatos, $tcUser, $tcProg);
			}
		}
		if ($laRta['success']) $laRta['datos'] = $laDatos;

		return $laRta;
	}


	/*
	 *	Genera array con info del paciente shaio a partir de datos de DonDoctor
	 *	@param array $taDatosDD: arreglo con los datos recibidos de DonDoctor
	 *	@return array con los datos arreglados para agenda Shaio
	 */
	public function datosPacienteDD($taDatosDD)
	{
		// Extraer los datos necesarios desde los enviados desde DD

		$lcTipoDoc = $taDatosDD['DOCUMENTTYPEID'] ?? (isset($taDatosDD['DOCUMENTTYPE']) ? $taDatosDD['DOCUMENTTYPE']['id'] : '');

		// Obtener el tipo de identificación Shaio del paciente
		$laDatos['tipoId'] = $this->oDb->obtenerTabmae1('TRIM(DE2TMA)', 'DONDOCT', ['CL1TMA'=>'CATALOG', 'CL2TMA'=>'TIPOID', 'CL3TMA'=>$lcTipoDoc], null, 'X');
		if ($laDatos['tipoId']=='X') {
			return [
				'success' => false,
				'message' => 'Tipo de documento no soportado',
			];
		}

		// Otros datos
		$laDatos['fechaNac'] = substr($taDatosDD['BIRTHDATE'], 0, 10);
		$laDatos['genero'] = substr($taDatosDD['GENRE'], 0, 1);
		$laEquiv = [
			'numeroId'	=> 'DOCUMENT',
			'numeroIdAlt'=> 'x',
			'nombre1'	=> 'NAME',
			'nombre2'	=> 'SECONDNAME',
			'apellido1'	=> 'LASTNAME',
			'apellido2'	=> 'SECONDLASTNAME',
			'telefono'	=> 'PHONE',
			'telefono2'	=> 'x',
			'correo'	=> 'EMAIL',
			'celular'	=> 'CELLPHONE',
			'celular2'	=> 'x',
			'direccion'	=> 'ADDRESS',
		];
		foreach ($laEquiv as $lcClaveShaio => $lcClaveDD) {
			$laDatos[$lcClaveShaio] = $taDatosDD[$lcClaveDD] ?? '';
		}
		$laUpper = [
			'nombre1',
			'nombre2',
			'apellido1',
			'apellido2',
			'direccion',
		];
		foreach ($laUpper as $lcClave) {
			$laDatos[$lcClave] = strtoupper($laDatos[$lcClave]);
		}

		if ($laDatos['tipoId']=='P') {
			$laDatos['numeroIdAlt'] = $laDatos['numeroId'];
		}
		if (!is_numeric($laDatos['numeroId'])) {
			$laDatos['numeroId'] = filter_var($laDatos['numeroId'], FILTER_SANITIZE_NUMBER_INT);
		}
		if (strlen($laDatos['numeroId'])>13) {
			$laDatos['numeroId'] = substr($laDatos['numeroId'], 0, 13);
		}

		return $laDatos;
	}


	/*
	 *	Inserta o Actualiza la información de una cita desde datos de DonDoctor
	 *	@param array $taDatosDD: arreglo con los datos recibidos de DonDoctor
	 *	@param string $tcUser: Usuario que ejecuta la acción
	 *	@param string $tcProg: Programa que ejecuta la acción
	 *	@return array con los elementos: success (true o false), mensaje (en caso de success)
	 */
	public function citaDD($taDatosDD, $tcUser='AGENDAWEB', $tcProg='PACDD')
	{
		if (!in_array($taDatosDD['STATE'], ['A','C','N'])) {
			return [
				'success' => true,
				'message' => 'Estado diferente de A, C o N. No se procesa',
			];
		}
		$lcUniAgenda = '';

		// Solo permitir agendar o cancelar citas con fecha mayor o igual a la actual
		$lnFechaCita = str_replace('-', '', substr($taDatosDD['DATE'],0,10));
		$lnHoraCita = str_replace(':', '', $taDatosDD['FROM']);
		$ltAhora = new \DateTime($this->oDb->fechaHoraSistema());
		$lnFechaCrea = $ltAhora->format('Ymd');

		if ($lnFechaCita<$lnFechaCrea) {
			return [
				'success' => false,
				'message' => 'Fecha de cita menor a la fecha actual.',
			];
		}

		$this->obtenerTokenDD();

		// Consultar paciente de DD
		$lcUrl = $this->cRutaDD('users').'Patient/Id';
		$laPacDD = $this->consumirGetDD($lcUrl, ['userId'=>$taDatosDD['USERID']]);
		if (!is_array($laPacDD)) {
			return [
				'success' => false,
				'message' => 'Paciente no encontrado en DD',
			];
		} elseif (isset($laPacDD['message']) && $laPacDD['message']=='Unauthorized') {
			return [
				'success' => false,
				'message' => 'Error de autorización para consultar API',
			];
		}

		// Convierte datos de paciente de DD a Shaio
		$laPacDD = array_change_key_case($laPacDD, CASE_UPPER);
		$laResPac = $this->datosPacienteDD($laPacDD);

		// Consultar servicio de DD
		$lcUrl = $this->cRutaDD('services').'Services';
		$laSrvDD = $this->consumirGetDD($lcUrl, ['ServiceID'=>$taDatosDD['SERVICEID']]);
		if (!is_array($laSrvDD)) {
			return [
				'success' => false,
				'message' => 'Servicio no encontrado en DD',
			];
		}
		$laSrvDD = array_change_key_case($laSrvDD, CASE_UPPER);


		$lcTipoDoc = $laResPac['tipoId'];
		$lnNumDoc = $laResPac['numeroId'];
		$lcCup = $laSrvDD['CUP'] ?? '';
		if (substr($lcCup, -2)=='-P') {
			$lbEsPostOperatorio = true;
			$lcCup = substr($lcCup, 0, strlen($lcCup)-2);
		} else {
			$lbEsPostOperatorio = false;
		}

		// Consulta si existe el CUP
		$laCupShaio = $this->consultarCup($lcCup);
		if ($laCupShaio===false) {
			return [
				'success' => false,
				'message' => "CUP no se encuentra en BD. ServiceID={$taDatosDD['SERVICEID']}, CUP={$laSrvDD['CUP']}",
			];
		}

		// state = 'C' > Cancelar
		// state = 'N' > No asistie
		// state = 'A' > Crear - Actualizar
		$lbCancelarCita = in_array($taDatosDD['STATE'], ['C','N']);

		// Consultar si existe la cita
		$laResCita = $this->consultarCitaPaciente($lcTipoDoc, $lnNumDoc, $lnFechaCita, $lnHoraCita, $lcCup, $lbEsPostOperatorio, $lbCancelarCita);
		if ($laResCita['success']) {
			$lbExisteCita = true;
			$laResCita = $laResCita['datos'];
			$lcUniAgenda = $laResCita['UNIDAD_AGENDA'];
		} else {
			$lbExisteCita = false;
			$laResCita = [];
			$lcUniAgenda = $this->oDb->obtenerTabmae1('TRIM(OP2TMA)', 'DONDOCT', ['CL1TMA'=>'HOMOLOGA','CL2TMA'=>$taDatosDD['SERVICEID']], null, '');
		}

		if ($lbCancelarCita==false) {

			if ($lbExisteCita) {
				// Modificar Cita
				// Se debe permitir modificar ??
				// En correo del 2022-09-02 a las 11:43 indican que se cancela y luego se crea nueva cita, no debería existir modificaciones
				return ['success'=>false, 'message'=>'Cita Ya existe'];

			} else {

				if (!boolval(filter_var($laResPac['correo'], FILTER_VALIDATE_EMAIL))) {
					$laResPac['correo'] = '';
				}
				$laRtaValPac = $this->validarDatosPac($laResPac);
				if ($laRtaValPac['success']==false) {
					return $laRtaValPac;
				}

				if (isset($taDatosDD['INSURANCEPLANID'])) {
					// Consultar plan de DD
					$lcUrl = $this->cRutaDD('plans').'InsurancePlan';
					$laPlanDD = $this->consumirGetDD($lcUrl, ['insurancePlanId'=>$taDatosDD['INSURANCEPLANID']]);
					if (is_array($laPlanDD)) {
						$laPlanDD = array_change_key_case($laPlanDD, CASE_UPPER);
						$lcCodPlan = $laPlanDD['REMOTEID'] ?? '';
					} else {
						$lcCodPlan = '';
					}
				} else {
					return [
						'success' => false,
						'message' => 'Campo requerido InsurancePlanId,  no existe o está vacío.',
					];
				}

				// Consultar especialidad de DD
				$lcUrl = $this->cRutaDD('specialities').'Specialties/Service';
				$laEspDD = $this->consumirGetDD($lcUrl, ['serviceId'=>$taDatosDD['SERVICEID']]);
				$lcEspecialidad = '';
				if (is_array($laEspDD)) {
					$laEspDD = array_change_key_case($laEspDD[0], CASE_UPPER);
					$lcEspecialidad = $laEspDD['REMOTEID'] ?? '';
				}
				if (strlen($lcEspecialidad)==0) {
					return [
						'success' => false,
						'message' => 'No se puede consultar la especialidad del servicio con serviceId='.$taDatosDD['SERVICEID'],
					];
				} else {
					$laEspShaio = $this->consultarEspecialidad($lcEspecialidad);
					if ($laEspShaio===false) {
						return [
							'success' => false,
							'message' => "Especialidad no se encuentra en BD. SpecialtyID={$taDatosDD['SERVICEID']}, RemoteID={$lcEspecialidad}",
						];
					}
				}


				// Obtener datos del Médico
				$lcUrl = $this->cRutaDD('users').'Doctor/Id';
				$laMedDD = $this->consumirGetDD($lcUrl, ['userId'=>$taDatosDD['DOCTORID']]);
				if (isset($laMedDD['document'])) {
					$laMedDD = array_change_key_case($laMedDD, CASE_UPPER);
					$lcRegistroMedico = $laMedDD['REMOTEID'];
					if ($lcRegistroMedico=='0000000000000') {
						$lcRegistroMedico = '';
					} else {
						// Validar que existe en el sistema
						$laMedShaio = $this->consultarMedico($lcRegistroMedico);
						if ($laMedShaio===false) {
							return [
								'success' => false,
								'message' => "Médico no se encuentra en BD. UserId={$taDatosDD['DOCTORID']}, RemoteID={$taDatosDD['REMOTEID']}",
							];
						}
					}
				} else {
					return [
						'success' => false,
						'message' => 'Error al consultar médico en DD. UserId='.$taDatosDD['DOCTORID'],
					];
				}

				$lnEstadoCita = 2;
				$lcViaIng = '02';
				$lnPeso = 0;
				$lcNoInvasivos = '';
				$lcTipoConsulta = 'C';
				$lnFechaDesea = $lnFechaCita;
				$lnHoraDesea = $lnHoraCita;

				if (strlen($lcEspecialidad)==0) {
					$laEsp = $this->oDb->select('ESPCUP')->from('RIACUP')->where("CODCUP='{$lcCup}'")->get('array');
					if ($this->oDb->numRows()>0) {
						$lcEspecialidad = $laEsp['ESPCUP'];
					}
				}


				$lnNroIngreso = 0;
				if ($lbEsPostOperatorio) {
					$lnIndicadorOperatorio = 0;
					$laTemp = $this->oDb->select('NIGING')
						->from('RIAINGL15')
						->where(['TIDING'=>$lcTipoDoc,'NIDING'=>$lnNumDoc])
						->orderBy('NIGING','DESC')->getAll('array');
					if ($this->oDb->numRows()>0) {

						// Cups que solo requieren validar si hubo ingreso
						// Antes correspondía a unidades 37 y 38
						$laCupSoloIngreso = explode(',', $this->oDb->obtenerTabmae1('TRIM(DE2TMA || OP5TMA)', 'DONDOCT', ['CL1TMA'=>'POSTOPER','CL2TMA'=>'SOLOING','ESTTMA'=>''], null, '378501,378503'));
						$lbSoloIngreso = in_array($lcCup, $laCupSoloIngreso) && in_array($lcEspecialidad, ['123','126']);

						if ($lbSoloIngreso) {
							$lnNroIngreso = $laTemp[0]['NIGING'];
							$lnIndicadorOperatorio = 2;
						} else {
							$laDiasQx = $this->oDb->obtenerTabmae1('OP3TMA', 'DONDOCT', ['CL1TMA'=>'POSTOPER','CL2TMA'=>'DIASQX','ESTTMA'=>''], null, 30);
							$lnLimiteOperatorio = (clone $ltAhora)->sub(new \DateInterval("P{$laDiasQx}D"))->format('Ymd');

							foreach ($laTemp as $laFila) {
								$laTempC = $this->oDb
									->select('INGEST,FINEST')
									->from('RIAESTM41')
									->where("INGEST={$laFila['NIGING']} AND (FINEST BETWEEN $lnLimiteOperatorio AND $lnFechaCrea) AND (RF1EST='CIRUG.' OR RF3EST='HEMODI')")
									->orderBy('FINEST','DESC')->get('array');
								if ($this->oDb->numRows()>0) {
									$ldFechaConsumo = \DateTime::createFromFormat('Ymd', $laTempC['FINEST']);
									$ldFechaCita = \DateTime::createFromFormat('Ymd', $lnFechaCita);
									$loDifFecha = $ldFechaConsumo->diff($ldFechaCita);
									if ($loDifFecha->days > $laDiasQx) {
										//	$lnIndicadorOperatorio = 1;
										//	return ['success'=>false, 'message'=>'Fecha Cita Excede el tiempo para asignar POST-OPERATORIO.'];
									} else {
										$lnNroIngreso = $laFila['NIGING'];
										$lnIndicadorOperatorio = 3;
									}
									break;
								}
							}
						}
					} else {
						// return ['success'=>false, 'message'=>'Ingreso no encontrado. NO puede asignarse cita POST-OPERATORIO.'];
					}
					unset($laTemp,$laTempC);
					//	$lnIndicadorOperatorio==0 => return ['success'=>false, 'message'=>'No existen procedimientos QX. NO puede asignarse cita POST-OPERATORIO.'];

					if ($lnNroIngreso > 0) {
						// Vía de ingreso 06 para citas CUP=890226, antes unidad agenda 35 - Valoración Preanestesia
						$laCupVia06 = explode(',', $this->oDb->obtenerTabmae1('TRIM(DE2TMA || OP5TMA)', 'DONDOCT', ['CL1TMA'=>'POSTOPER','CL2TMA'=>'VIA06','ESTTMA'=>''], null, '890226'));
						$lcViaIng = in_array($lcCup, $laCupVia06) ? '06' : '02';
						$lnEstadoCita = 8;
					} else {
						// enviar correo avisando de cita postoperatorio sin ingreso
						$this->enviarEmailPostopSinIngreso([
							'NombrePaciente'=>$laResPac['nombre1'].' '.$laResPac['nombre2'].' '.$laResPac['apellido1'].' '.$laResPac['apellido2'],
							'Documento'		=>$laResPac['tipoId'].' '.$laResPac['numeroId'],
							'FechaCita'		=>(\DateTime::createFromFormat('YmdHis', $lnFechaCita.$lnHoraCita))->format('Y/m/d H:i:s'),
						]);
					}
				}

				// Obtener consecutivo de cita y guardar paciente
				$laLogCita = $this->pacienteCita($laResPac, $tcUser, $tcProg);
				$lnConsecCita = $laLogCita['cita'];
				// Consecutivo de orden
				$lnOrdenCita = $this->oDb->obtenerConsecRiacon(920, $tcProg, 20, $tcUser);
				$lnConsecEvolucion = 1;
				$lcCodigoInicial = '';
				$lnConsecLinea = 1;
				$lnFechaCrea = $laLogCita['fecha'];
				$lnHoraCrea = $laLogCita['hora'];

				// Insertar Cita
				$laData = [
					'TIDCIT' => $lcTipoDoc,
					'NIDCIT' => $lnNumDoc,
					'NINCIT' => $lnNroIngreso,
					'CCICIT' => $lnConsecCita,
					'EVOCIT' => $lnConsecEvolucion,
					'ESCCIT' => $lcUniAgenda,
					'CODCIT' => $lcEspecialidad,
					'CD2CIT' => 0,
					'COACIT' => $lcCup,
					'FCOCIT' => $lnFechaCrea,
					'FRLCIT' => $lnFechaCita,
					'HOCCIT' => $lnHoraCita,
					'RMRCIT' => $lcRegistroMedico,
					'FERCIT' => $lnFechaCita,
					'HRLCIT' => $lnHoraCita,
					'ESTCIT' => $lnEstadoCita,
					'INSCIT' => $lnOrdenCita,
					'VIACIT' => $lcViaIng,
					'NSACIT' => $lcCodigoInicial,
					'USRCIT' => $tcUser,
					'PGMCIT' => $tcProg,
					'FECCIT' => $lnFechaCrea,
					'HORCIT' => $lnHoraCrea,
				];
				if ($this->oDb->from('RIACIT')->insertar($laData)) {
					$laRta = ['success'=>true];
					$lnCcnsAgeWeb = 0;

					// Teleconsulta
					if ($taDatosDD['TELEMEDICINE']) {
						$this->insertarCitaTeleConsulta([
							'tipoId'		=> $lcTipoDoc,
							'numeroId'		=> $lnNumDoc,
							'ingreso'		=> $lnNroIngreso,
							'cnsCita'		=> $lnConsecCita,
							'cnsCant'		=> $lnConsecEvolucion,
							'especialidad'	=> $lcEspecialidad,
							'cup'			=> $lcCup,
							'fechaCrea'		=> $lnFechaCrea,
							'fecha'			=> $lnFechaCita,
							'hora'			=> $lnHoraCita,
							'regMedico'		=> $lcRegistroMedico,
							'teleconsulta'	=> 1,
							'estadoCita'	=> $lnEstadoCita,
							'unidadAgenda'	=> $lcUniAgenda,
							'cnsOrden'		=> $lnOrdenCita,
							'viaIng'		=> $lcViaIng,
						], $tcUser, $tcProg);
						$lnCcnsAgeWeb = 1;
					}

					// Inserta en RIAORD
					$laData = [
						'TIDORD' => $lcTipoDoc,
						'NIDORD' => $lnNumDoc,
						'NINORD' => $lnNroIngreso,
						'CCIORD' => $lnConsecCita,
						'EVOORD' => $lnConsecEvolucion,
						'CODORD' => $lcEspecialidad,
						'CD2ORD' => 0,
						'COAORD' => $lcCup,
						'FCOORD' => $lnFechaCrea,
						'FRLORD' => $lnFechaCita,
						'HOCORD' => $lnHoraCita,
						'RMRORD' => $lcRegistroMedico,
						'FERORD' => $lnFechaCita,
						'HRLORD' => $lnHoraCita,
						'CICORD' => '',
						'ESTORD' => $lnEstadoCita,
						'VIAORD' => $lcViaIng,
						'CATORD' => $lnCcnsAgeWeb,
						'PLAORD' => $lcCodPlan,
						'USRORD' => $tcUser,
						'PGMORD' => $tcProg,
						'FECORD' => $lnFechaCrea,
						'HORORD' => $lnHoraCrea,
					];
					if ($this->oDb->from('RIAORD')->insertar($laData)) {
						// Insertar en RIACID
						$laData = [
							'TIDCID' => $lcTipoDoc,
							'NIDCID' => $lnNumDoc,
							'NINCID' => $lnNroIngreso,
							'UNICID' => $lcUniAgenda=='' ? '0' : $lcUniAgenda,
							'CCICID' => $lnConsecCita,
							'CCUCID' => $lnConsecEvolucion,
							'ORDCID' => $lnOrdenCita,
							'FGRCID' => $lnFechaCrea,
							'HGRCID' => $lnHoraCrea,
							'CLICID' => $lnConsecLinea,
							'CUPCID' => $lcCup,
							'ESTCID' => $lnEstadoCita,
							'CODCID' => $lcEspecialidad,
							'CD2CID' => '',
							'RMECID' => $lcRegistroMedico,
							'FRLCID' => $lnFechaCita,
							'HOCCID' => $lnHoraCita,
							'PLACID' => $lcCodPlan,
							'VIACID' => $lcViaIng,
							'OP1CID' => $lcTipoConsulta,
							'OP2CID' => $lcNoInvasivos,
							'OP4CID' => $lnPeso,
							'OP5CID' => str_repeat(' ',50).$lnFechaDesea.'='.$lnHoraDesea,
							'OP7CID' => $lnFechaDesea,
							'OP3CID' => $lnHoraDesea,
							'USRCID' => $tcUser,
							'PGMCID' => $tcProg,
							'FECCID' => $lnFechaCrea,
							'HORCID' => $lnHoraCrea,
						];
						if ($this->oDb->from('RIACID')->insertar($laData)) {
							return ['success'=>true, 'message'=>'Cita Agendada'];
						} else {
							return ['success'=>false, 'message'=>'Ocurrió un error al registrar la cita en el archivo de detalle de citas.'];
						}
					} else {
						return ['success'=>false, 'message'=>'Ocurrió un error al registrar la cita en el archivo de órdenes.'];
					}

				} else {
					return ['success'=>false, 'message'=>'Ocurrió un error al registrar la cita.'];
				}
			}

		} else {
			// Cancelar cita

			if ($lbExisteCita) {
				$laEstadosCancela = $lbEsPostOperatorio ? [2, 8, 30] : [2, 30];
				if (!in_array($laResCita['ESTADO'], $laEstadosCancela)) {
					return ['success' => false, 'message' => "El estado de la cita no permite cancelar ({$laResCita['ESTADO']})"];
				}
				$lnConsecCita = $laResCita['CITA'];
				$lnConsecEvolucion = $laResCita['CUPO'];
				$lcUnidadAgenda = ''; // Para citas migradas si existe unidad de agenda en BD Shaio, por esto no debe llevar el valor

				 // Motivo de cancelación vacío cuando no llegue
				$lcTipoMotivoC = '00000';
				if ($taDatosDD['STATE']=='N') {
					$lcTipoMotivoC = '04039';
				}

				// Tipo y Motivo de cancelación
				if (($taDatosDD['APPOINTMENTREASONID']??0)>0) {
					$lcUrl = $this->cRutaDD('cancellationreasons').'AppointmentReasons';
					$laTMCDD = $this->consumirGetDD($lcUrl, ['appointmentReasonId'=>$taDatosDD['APPOINTMENTREASONID']]);
					if (is_array($laTMCDD)) {
						$laTMCDD = array_change_key_case($laTMCDD, CASE_UPPER);
						$lcTipoMotivoC = $laTMCDD['REMOTEID'];
					}
				}
				$lnCodTipoCancela = intval(substr($lcTipoMotivoC, 0, 2));
				$lnMotivoCancela = intval(substr($lcTipoMotivoC, 2, 3));
				$lcObservaciones = '';

				// Cancelar Cita
				return $this->cancelarCita([
					'tipoId'		=> $lcTipoDoc,
					'numeroId'		=> $lnNumDoc,
				//	'unidadAgenda'	=> $lcUnidadAgenda,
					'fecha'			=> $lnFechaCita,
					'hora'			=> $lnHoraCita,
					'cnsCita'		=> $lnConsecCita,
					'cnsCant'		=> $lnConsecEvolucion,
					'cup'			=> $lcCup,
					'codTipo'		=> $lnCodTipoCancela,
					'codMotivo'		=> $lnMotivoCancela,
					'observaciones'	=> $lcObservaciones,
				], $tcUser, $tcProg);

			} else {
				// Error no existe Cita
				return ['success' => false, 'message' => 'No existe cita en el sistema'];
			}
		}
	}


	/*
	 *	Consulta citas activas para un paciente
	 *	@param string $tcTipoId: Tipo de documento de identidad del paciente
	 *	@param integer $tnNumId: Número de documento de identidad del paciente
	 *	@param integer $tnFecha: Fecha de la cita
	 *	@param integer $tnHora: Hora de la cita
	 *	@param string $tcCup: Cup de la cita
	 *	@return array con los elementos: success (true o false), mensaje (en caso de success false), datos (array con datos de las citas del paciente)
	 */
	public function consultarCitaPaciente($tcTipoId, $tnNumId, $tnFecha, $tnHora, $tcCup, $tbEsPostOperatorio, $tbCancelar)
	{
		$laRta = $this->validarId($tcTipoId, $tnNumId);
		if (!$laRta['success']) return $laRta;
		$laRta = [];

		$laWhere = [
			'C.TIDCIT'=>$tcTipoId,
			'C.NIDCIT'=>$tnNumId,
			'C.FRLCIT'=>$tnFecha,
			'C.HOCCIT'=>$tnHora,
		];
		if (is_string($tcCup) && strlen($tcCup)>0) {
			$laWhere['C.COACIT'] = $tcCup;
		}
		if (!$tbCancelar) {
			// $this->oDb->in('ESTCIT', $tbEsPostOperatorio ? [2, 8, 30] : [2,30]);
			$this->oDb->in('ESTCIT', [2, 8, 30]);
		}
		$laDatos = $this->oDb
			->select([
				'C.NINCIT INGRESO',
				'C.CCICIT CITA',
				'C.EVOCIT CUPO',
				'C.ESTCIT ESTADO',
				'C.FRLCIT FECHA_CITA',
				'C.HOCCIT HORA_CITA',
				'C.FERCIT FECHA_REALIZADO',
				'C.HRLCIT HORA_REALIZADO',
				"C.COACIT CUP",
				'C.VIACIT COD_VIA',
				"CASE VIACIT WHEN '02' THEN 'CE' WHEN '01' THEN 'UR' WHEN '05' THEN 'HO' WHEN '06' THEN 'AM' ELSE '' END VIA",
				'C.CODCIT ESPECIALIDAD',
				'C.RMRCIT REGMED_REALIZA',
				'C.RMECIT REGMED_ORDENA',
				'C.ENTCIT COD_PLAN',
				'C.ESCCIT UNIDAD_AGENDA',
				'C.NSACIT NUM_SALA',
				"CASE WHEN CUP.RF3CUP='MED.NU' THEN 1 ELSE 0 END MCA",
				"CASE C.NSACIT WHEN 'S' THEN 1 WHEN 'C' THEN 2 WHEN 'P' THEN 3 ELSE 0 END C3D",
			])
			->from('RIACIT C')
				->innerJoin('RIAORD O',  'O.TIDORD=C.TIDCIT AND O.NIDORD=C.NIDCIT AND O.CCIORD=C.CCICIT')
				->leftJoin('RIACUP CUP', 'C.COACIT=CUP.CODCUP')
			->where($laWhere)
				//->in('ESTCIT', $tbEsPostOperatorio ? [2, 8, 30] : [2,30])
			->orderBy('C.CCICIT','DESC')
			->getAll('array');
		if ($this->oDb->numRows()>0) {
			$laCitas = [];
			$laRta = ['success'=>true, 'datos'=>$laDatos[0]];
		} else {
			$laRta = ['success'=>false, 'message'=>'Cita no encontrada'];
		}

		return array_merge(['fechahora'=>date('Y-m-d H:i:s')], $laRta);
	}


	/*
	 *	Solicita el token a la plataforma de DD y la guarda en tabmae
	 */
	public function obtenerTokenDD()
	{
		// Username y Password
		$laSeguridad = $this->oDb->obtenerTabmae1('TRIM(OP5TMA)', 'DONDOCT', ['CL1TMA'=>'RUTAAPI','ESTTMA'=>''], null, '{}');
		$lcUrl = $this->cRutaDD('security').'Login';

		// Obtener token
		$lcToken = '';
		$lnNum = 0;
		while ($lcToken=='' && $lnNum<20) {
			$lnNum++;
			$lcResponse = $this->consultaAPI($lcUrl, 'POST', $laSeguridad, false);
			$laResponse = json_decode($lcResponse, true);
			if (is_array($laResponse) && isset($laResponse['token'])) {
				$lcToken = $laResponse['token'];
			}
		}

		$this->cTokenDD = $lcToken;
	}


	/*
	 *	Retorna el token obtenido
	 */
	public function cTokenDD()
	{
		return $this->cTokenDD;
	}


	/*
	 *	Retorna la ruta para un EndPoint
	 *	@param string $tcEndPoint: nombre del EndPoint
	 */
	public function cRutaDD($tcEndPoint)
	{
		return str_replace('[endpoint]', $tcEndPoint, $this->cRutaDD);
	}


	/*
	 *	Consume EndPoint Get de DD para obtener datos
	 *	@param string $tcUrl dirección url que se va a consultar
	 *	@param array $taDatos parámetros get a incluir en la consulta
	 */
	public function consumirGetDD($tcUrl, $taDatos=[])
	{
		$lcUrl = $tcUrl;
		if (is_array($taDatos) && count($taDatos)>0) {
			$lcSep = '?';
			foreach ($taDatos as $lcClave => $lcValor) {
				$lcUrl .= $lcSep.$lcClave.'='.$lcValor;
				$lcSep = '&';
			}
		}
		$laResponse = [];
		$lnNum = 0;
		while (count($laResponse)==0 && $lnNum<10) {
			$lnNum++;
			$lcResponse = $this->consultaAPI($lcUrl, 'GET');
			$laResponse = json_decode($lcResponse, true);
			if (is_array($laResponse)) {
				if (isset($laResponse['message']) && $laResponse['message']=='Unauthorized' && $lnNum<10) {
					$this->obtenerTokenDD();
					$laResponse = [];
				}
			} else {
				$laResponse = [];
			}
		}

		return $laResponse;
	}


	public function consultaAPI($tcUrl, $tcTipo, $tcDatosPOST=false, $tbToken=true)
	{
		if ($tcTipo=='POST' && $tcDatosPOST==false) $tcTipo=='GET';

		$loCURL = curl_init();
		$laEnvio = [
			CURLOPT_URL => $tcUrl,
			CURLOPT_SSL_VERIFYPEER => false,
			// CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $tcTipo,
			CURLOPT_HEADER => false,
			CURLOPT_HTTPHEADER => [
				$this->cCookieDD,
				'Accept: application/json',
			],
		];
		if ($tcDatosPOST) {
			$laEnvio[CURLOPT_POSTFIELDS] = $tcDatosPOST;
			$laEnvio[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
		}
		if ($tbToken) {
			$laEnvio[CURLOPT_HTTPHEADER][] = 'Authorization: Bearer '.$this->cTokenDD;
		}
		curl_setopt_array($loCURL, $laEnvio);

		$lcResponse = curl_exec($loCURL);
		curl_close($loCURL);

		return $lcResponse;
	}


	public function obtenerIPsValidas()
	{
		$this->aIPsValidas = [];
		$laValIPs = $this->oDb
			->select('TRIM(DE2TMA) AS IPVAL')
			->from('TABMAE')
			->where("TIPTMA='DONDOCT' AND CL1TMA='IPVALIDA' AND ESTTMA=''")
			->getAll('array');
		if ($this->oDb->numRows()>0) {
			foreach ($laValIPs as $laValIP) {
				$this->aIPsValidas[] = $laValIP['IPVAL'];
			}
		}
	}


	public function validarIP($tcIP)
	{
		if (in_array($tcIP, $this->aIPsValidas)) {
			$laReturn = ['success'=>true, 'message'=>'IP válida'];
		} else {
			$laReturn = ['success'=>false, 'message'=>"IP $tcIP NO válida, no se procesa el mensaje"];
		}
		return $laReturn;
	}



	/*
	 *	Envío de correo de notificación de post operatorio sin ingreso
	 */
	private function enviarEmailPostopSinIngreso($taDatos)
	{
		$loMail = new MailEnviar();

		// Plantilla para enviar
		$loMail->obtenerPlantilla('CPOPSING', 'POPSI');
		// $lcPlantilla = utf8_encode($loMail->cPlantilla);
		$lcPlantilla = $loMail->cPlantilla;

		// Configuración para el envío
		$laConfigToda = $loMail->obtenerConfiguracion('CPOPSING');
		$laConfig = $laConfigToda['config'];

		// Reemplazar datos en la plantilla
		$lcPlantilla = strtr($lcPlantilla, [
			'[[NombrePaciente]]'=> $taDatos['NombrePaciente'],
			'[[Documento]]'		=> $taDatos['Documento'],
			'[[FechaCita]]'		=> $taDatos['FechaCita'],
		]);
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




	public function IPsValidas()
	{
		return $this->aIPsValidas;
	}

}
