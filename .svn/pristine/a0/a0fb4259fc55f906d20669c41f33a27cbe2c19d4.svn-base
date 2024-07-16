<?php
namespace NUCLEO;
require_once __DIR__ .'/class.Diagnostico.php';
require_once __DIR__ .'/class.AplicacionFunciones.php';


class Doc_Fisio_Evolucion
{
	protected $oDb;
	protected $aReporte = [
					'cTitulo' => 'EVOLUCIÓN FISIOTERAPIA',
					'lMostrarEncabezado' => true,
					'lMostrarFechaRealizado' => false,
					'lMostrarViaCama' => false,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => '',
					'cTxtLuegoDeCup' => '',
					'aCuerpo' => [],
					'aNotas' => ['notas'=>false,],
				];
	protected $cSL = PHP_EOL;
	protected $cCumplioObjetivos = '¿Cumplió los objetivos? ';


	// Programas que registran titulo en linea 1
	protected $laPrgTitulo = ['EV0015', 'EV0017', 'EV0019', 'EV0023AN', 'EV0023', 'ORDMEDWEB', 'EXA001', 'EV00171', 'EV0022AN', 'NUT010'];

	// Programas que solicitan ordenes médicas
	protected $laPrgOrdenes = ['EV0017', 'EV0019', 'EV0023AN', 'EV0023', 'ORDMEDWEB', 'EV00171'];

	// Programas que interpretan exámenes
	protected $laPrgInterpEx = ['EV0015', 'EV0017', 'EV0019', 'EXA001', 'EV00171'];



	public function __construct()
    {
		global $goDb;
		$this->oDb = $goDb;
    }


	/*
	 *	Retornar array con los datos del documento
	 */
	public function retornarDocumento($taData)
	{
		$this->consultarDatos($taData);
		return $this->aReporte;
	}


	/*
	 *	Consulta los datos del documento
	 */
	private function consultarDatos($taData)
	{
		$laTr = [];

		$laFisEvol = $this->oDb
			->select('CNLEFI, DESEFI, FECEFI')
			->from('EVOFISL02')
			->where([
				'INGEFI'=>$taData['nIngreso'],
				'CONEFI'=>$taData['nConsecDoc'],
			])
			->orderBy('CNLEFI')
			->getAll('array');

		if (is_array($laFisEvol)) { if (count($laFisEvol)>0) {

			$lcObsPiel = '';
			$lnObsPiel = 0;
			$lbObjetivos = false;
			$lnSugerencias = 0;
			$lcSugerencias = '';
			$lnEvolucion = 0;
			$lcEvolucion = '';

			foreach ($laFisEvol as $laFsEv) {
				$lnCns = intval($laFsEv['CNLEFI']);

				switch (true) {

					case $lnCns==1:
						$lcDatosEnc = '';
						$laTr[] = ['titulo1', trim($laFsEv['DESEFI'])];
						$laTr[] = ['texto9', 'Vía: ' . ucwords(strtolower($taData['oIngrPaciente']->cDescVia))];
						break;

					case $lnCns==2:
						$lcDescripcion=$lcHumidificacion=$lcTubo=$lcTipoTubo=$lcSondaSuccion=$lcCircuito=$lcMacrogoteo=$lcAguaEsteril=$lcHigrobak=$lcTipoVentilacion=$lcDesTipoVentilacion='';

						$lcTipoVentilacion = trim(mb_substr($laFsEv['DESEFI'],40,2));
						$oTabmae = $this->oDb->obtenerTabMae('DE2TMA', 'FISRES', "CL1TMA='20' AND CL2TMA='$lcTipoVentilacion'");
						$lcDesTipoVentilacion = trim(AplicacionFunciones::getValue($oTabmae,'DE2TMA',''),' ');

						$laTr[] = ['titulo2', 'Ventilación mecánica: ' . $lcDesTipoVentilacion];

						$lcTubo = trim(mb_substr($laFsEv['DESEFI'],0,2));
						$lcTipoTubo = trim(mb_substr($laFsEv['DESEFI'],35,3));

						if (!empty($lcTubo)) {
							$oTabmae = $this->oDb->obtenerTabMae('DE2TMA', 'FISRES', "CL1TMA='17' AND CL2TMA='$lcTubo'");
							$lcDescripcion = 'Tubo: ' . trim(AplicacionFunciones::getValue($oTabmae,'DE2TMA',''),' ');
							if (!empty($lcTipoTubo)) {
								$oTabmae=$this->oDb->obtenerTabMae('DE2TMA', 'FISRES', "CL1TMA='18' AND CL2TMA='$lcTipoTubo'");
								$lcDescripcion .= '   número: ' . trim(AplicacionFunciones::getValue($oTabmae,'DE2TMA',''),' ') . ' mm';
							}
							$laTr[] = ['texto9', $lcDescripcion];
						}

						$lcHumidificacion = trim(mb_substr($laFsEv['DESEFI'],5,2));
						if (!empty($lcHumidificacion)) {
							$oTabmae = $this->oDb->obtenerTabMae('DE2TMA', 'FISRES', "CL1TMA='19' AND CL2TMA='$lcHumidificacion'");
							$lcDescripcion = 'Humidificación: ' . trim(AplicacionFunciones::getValue($oTabmae,'DE2TMA',''),' ') . $this->cSL;

							switch (intval($lcHumidificacion)) {
								case 1:	// ACTIVO
									$lcSondaSuccion	= $this->ValorSiNo(substr($laFsEv['DESEFI'],10,1));
									$lcCircuito		= $this->ValorSiNo(substr($laFsEv['DESEFI'],15,1));
									$lcMacrogoteo	= $this->ValorSiNo(substr($laFsEv['DESEFI'],20,1));
									$lcAguaEsteril	= $this->ValorSiNo(substr($laFsEv['DESEFI'],25,1));

									$lcDescripcion  .= 'Cambio sonda succión: ' . $lcSondaSuccion . '      '
													.  'Cambio circuito: ' . $lcCircuito . '           '
													.  'Cambio agua estéril: ' . $lcAguaEsteril . $this->cSL
													.  'Cambio macrogoteo: ' . $lcMacrogoteo;
									break;

								case 2:	// PASIVO
									$lcSondaSuccion	= $this->ValorSiNo(substr($laFsEv['DESEFI'],10,1));
									$lcCircuito		= $this->ValorSiNo(substr($laFsEv['DESEFI'],15,1));
									$lcHigrobak		= $this->ValorSiNo(substr($laFsEv['DESEFI'],30,1));
									$lcDescripcion	.= 'Cambio sonda succión: ' . $lcSondaSuccion . '      '
													.  'Cambio circuito: ' . $lcCircuito . '           '
													.  'Cambio Higrobak: ' . $lcHigrobak;
									break;

							}

							$laTr[] = ['texto9', $lcDescripcion];
						}

						//if (!empty($lcTubo))
						//	$laTr[] = ['saltol', 5];
						break;

					//	DATOS VENTILACION MECANICA NO INVASIVA
					case $lnCns==3:
						$lcDescripcion=$lcHigrobackNoI=$lcOronasalNoI=$lcFullfaceNoI=$lcNasalNoI=$lcProtesisNoI=$lcNoInvasiva=$lcInterface='';

						$lcNoInvasiva	= trim(substr($laFsEv['DESEFI'], 0, 2));
						$lcHigrobackNoI	= $this->ValorSiNo(trim(substr($laFsEv['DESEFI'], 5, 2)));
						$lcInterface	= trim(substr($laFsEv['DESEFI'], 10, 2));
						$lcProtesisNoI	= $this->ValorSiNo(trim(substr($laFsEv['DESEFI'], 15, 2)));

						if (!empty($lcNoInvasiva)) {
							$oTabmae = $this->oDb->obtenerTabMae('DE2TMA', 'FISRES', "CL1TMA='22' AND CL2TMA='$lcNoInvasiva'");
							$lcDescripcion = trim(AplicacionFunciones::getValue($oTabmae,'DE2TMA',''),' ');
							$laTr[] = ['titulo2', 'Ventilación mecánica NO invasiva: ' . $lcDescripcion];
						}

						if (!empty($lcHigrobackNoI)) {
							$laTr[] = ['texto9', 'Cambio Higrobak: ' . $lcHigrobackNoI];
						}

						if (!empty($lcInterface)) {
							$oTabmae = $this->oDb->obtenerTabMae('DE2TMA', 'FISRES', "CL1TMA='21' AND CL2TMA='$lcInterface'");
							$lcDescripcion = trim(AplicacionFunciones::getValue($oTabmae,'DE2TMA',''),' ');
							$laTr[] = ['titulo2', 'Interface'];
							$laTr[] = ['texto9', $lcDescripcion];
						}

						if (!empty($lcProtesisNoI)) {
							$laTr[] = ['titulo2', '¿Verificó prótesis dental?'];
							$laTr[] = ['texto9', $lcProtesisNoI];
						}
						break;


					// EVALUACIÓN DE PIEL
					case $lnCns==4:
						$laTr[] = ['titulo2', 'Evaluación de Piel - V.M.N.I.'];
						$laTr[] = ['titulo3', 'Evaluación inicial'];
						$laTr[] = ['texto9', 'Piel íntegra: ' . $this->ValorSiNo(mb_substr($laFsEv['DESEFI'], 0, 1)) . '. ' . trim(mb_substr($laFsEv['DESEFI'], 2))];
						break;
					case $lnCns==5:
						$laTr[] = ['texto9', 'Hidratación de la piel: ' . $this->ValorSiNo(mb_substr($laFsEv['DESEFI'], 0, 1)) . '. ' . trim(mb_substr($laFsEv['DESEFI'], 2))];
						break;
					case $lnCns==6:
						$laTr[] = ['texto9', 'Barba-bigote: ' . $this->ValorSiNo(mb_substr($laFsEv['DESEFI'], 0, 1)) . '. ' . trim(mb_substr($laFsEv['DESEFI'], 2))];
						break;
					case $lnCns==7:
						$laTr[] = ['texto9', 'Paciente mayor de 60 años: ' . $this->ValorSiNo(mb_substr($laFsEv['DESEFI'], 0, 1)) . '. ' . trim(mb_substr($laFsEv['DESEFI'], 2))];
						break;
					case $lnCns==8:
						$laTr[] = ['texto9', 'Antecedente diabético: ' . $this->ValorSiNo(mb_substr($laFsEv['DESEFI'], 0, 1)) . '. ' . trim(mb_substr($laFsEv['DESEFI'], 2))];
						break;
					case $lnCns==9:
						$laTr[] = ['texto9', 'Cubre zonas de presión: ' . $this->ValorSiNo(mb_substr($laFsEv['DESEFI'], 0, 1)) . '. ' . trim(mb_substr($laFsEv['DESEFI'], 2))];
						break;
					case $lnCns==10:
						$laTr[] = ['texto9', 'Sonda nasográstrica: ' . $this->ValorSiNo(mb_substr($laFsEv['DESEFI'], 0, 1)) . '. ' . trim(mb_substr($laFsEv['DESEFI'], 2))];
						break;

					case $lnCns==11:
						$laTr[] = ['titulo3', 'Evaluación periódica por turno'];
						$lcPielIntegra	 = $this->ValorSiNo(mb_substr($laFsEv['DESEFI'],  0, 1));
						$lcZonaPresion	 = $this->ValorSiNo(mb_substr($laFsEv['DESEFI'],  5, 1));
						$lcApositoGel	 = $this->ValorSiNo(mb_substr($laFsEv['DESEFI'], 10, 1));
						$lcCambioAPosito = $this->ValorSiNo(mb_substr($laFsEv['DESEFI'], 15, 1));
						$lcTipoInterface = $this->ValorSiNo(mb_substr($laFsEv['DESEFI'], 20, 1));
						$laTr[] = ['texto9', 
								  'Piel íntegra: ' . $lcPielIntegra . '              '
								. 'Zona de presión: ' . $lcZonaPresion . '           '
								. 'Uso de apósito de gel: ' . $lcApositoGel . $this->cSL
								. 'Requiere cambio de apósito de protección: ' . $lcCambioAPosito
								. ( empty($lcTipoInterface)? '': '                Tipo de interface: ' . $lcTipoInterface ) ];
						break;

					case $lnCns>=100 && $lnCns<=300:
						if ($lnCns==100) {
							$laTr[] = ['titulo3', 'Describa el estado de la piel fácil al retirar o suspender la ventilación no invasiva y soporte de oxigeno con el que queda el paciente'];
							$lnObsPiel = count($laTr);
							$laTr[] = [];
						}
						$lcObsPiel .= $laFsEv['DESEFI'];
						break;


					// DIAGNÓSTICOS
					case $lnCns==450:
						$lcCodDxMedico = trim(substr($laFsEv['DESEFI'],  0, 4));
						$lcCodDxFisiot = trim(substr($laFsEv['DESEFI'], 10, 1));

						// Diagnóstico médico
						$lcDscDxMed = (new Diagnostico($lcCodDxMedico, $laFsEv['FECEFI']))->getTexto();
						$laTr[] = ['titulo2', 'Diagnóstico médico'];
						$laTr[] = ['texto9', $lcCodDxMedico . ' - ' . $lcDscDxMed];

						// Diagnóstico fisiotérapeutico
						if (!empty($lcCodDxFisiot)) {
							$laTr[] = ['titulo2', 'Diagnóstico fisiotérapeutico'];
							$oTabmae = $this->oDb->obtenerTabMae('DE2TMA', 'PATFIS', "CL1TMA='$lcCodDxFisiot'");
							$laTr[] = ['texto9', 'PATRON ' . $lcCodDxFisiot . $this->cSL . trim(AplicacionFunciones::getValue($oTabmae,'DE2TMA',''),' ')];
						}
						break;


					// OBJETIVOS
					case $lnCns>=451 && $lnCns<=457:
						if ($lnCns==451) {
							$lcListaObj = str_replace('"',"'",trim($laFsEv['DESEFI']));
							$laObjetivos = $this->oDb
								->select('DE2TMA')
								->from('TABMAE')
								->where("TIPTMA='FISRES' AND CL1TMA=23 AND CL2TMA='$lcTipoVentilacion' AND CL3TMA IN ($lcListaObj)")
								->orderBy('DE2TMA')
								->getAll('array');
							$lcDescripcion = '';
							foreach ($laObjetivos as $laObj) {
								$lcDescripcion .= '* ' . trim($laObj['DE2TMA']) . $this->cSL;
							}
							$laTr[] = ['titulo2', 'Objetivos de intervención en ventilación mecánica ' . $lcDesTipoVentilacion];
							$laTr[] = ['texto9', $lcDescripcion];
						
						// Cumplió los objetivos?
						} elseif ($lnCns==452) {
							$laTr[] = ['texto9', $this->cCumplioObjetivos . $this->ValorSiNo(mb_substr($laFsEv['DESEFI'], 0, 1))];

						// Sugerencias
						} else {
							if ($lnCns==453) {
								$laTr[] = ['titulo2', 'Sugerencia:'];
								$lnSugerencias = count($laTr);
								$laTr[] = [];
							}
							$lcSugerencias .= $laFsEv['DESEFI'];
						}
						break;


					// EVOLUCIÓN
					case $lnCns>=500 && $lnCns<=1000:
						if ($lnCns==500) {
							$laTr[] = ['titulo2', 'Evolución'];
							$lnEvolucion = count($laTr);
							$laTr[] = [];
						}
						$lcEvolucion .= $laFsEv['DESEFI'];
						break;


					// FIRMAS
					case $lnCns==1999:
						$lcFirma = str_replace(chr(10), '', str_replace(chr(13), '', trim($laFsEv['DESEFI'])));
						$lnPos = mb_strpos($lcFirma, ' - ');
						$lcNombreMedico = mb_substr($lcFirma, 0, $lnPos + 1);
						$lcRegistroMedico = intval(mb_substr($lcFirma, $lnPos + 3, 13));
						$lcEspecialidadMedico = mb_substr($lcFirma, $lnPos + 16);
						$laFirma =	[
										'texto_firma' => $lcNombreMedico.$this->cSL.'Registro No.'.$lcRegistroMedico.$this->cSL.$lcEspecialidadMedico,
										'registro' => $lcRegistroMedico,
									];
						$laTr[] = ['firmas', [ $laFirma, ]];
						break;

				}
			}

			if (!empty($lcObsPiel)) $laTr[$lnObsPiel] = ['texto9', trim($lcObsPiel)];
			if (!empty($lcSugerencias)) $laTr[$lnSugerencias] = ['texto9', trim($lcSugerencias)];
			if (!empty($lcEvolucion)) $laTr[$lnEvolucion] = ['texto9', trim($lcEvolucion)];

			$this->aReporte['aCuerpo'] = $laTr;

		} }
	}

	private function ValorSiNo ($tcSN)
	{
		return ( $tcSN=='S' ? 'SI' : ( $tcSN=='N' ? 'NO' : '' ) );
	}

}
