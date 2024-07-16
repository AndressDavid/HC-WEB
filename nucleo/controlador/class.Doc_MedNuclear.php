<?php
namespace NUCLEO;

class Doc_MedNuclear
{
	protected $oDb;
	protected $aDocumento = [];
	protected $aReporte = [
					'cTitulo' => "MEDICINA NUCLEAR",
					'lMostrarFechaRealizado' => true,
					'lMostrarViaCama' => true,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => 'Estudio',
					'cTxtLuegoDeCup' => '',
					'aCuerpo' => [],
					'aFirmas' => [],
					'aNotas' => ['notas'=>true,],
				];
	protected $lTituloVacios=false;
	protected $cTituloVacios='';


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
		$this->prepararInforme($taData);
		return $this->aReporte;
	}


	/*
	 *	Consulta los datos del documento desde la BD en el array $aDocumento
	 */
	private function consultarDatos($taData)
	{
		$laDocumento = $this->datosBlanco();
		$laCondiciones = ['NRORAD'=>$taData['nIngreso'], 'SUBRAD'=>$taData['cCUP'], 'CONRAD'=>$taData['nConsecCita']];
		$radiogMN01 = $this->oDb
							->from('RADIOG')
							->where($laCondiciones)
							->getAll('array');
		if(is_array($radiogMN01)==true)
		{
			if(count($radiogMN01)>0)
			{
				//Leer Otros Datos Si CUP=920408 INICIALMENTE.  QUEDA PARAMETRIZADO POR LA TABMAE
				$laCondiciones = ['TIPTMA'=>'MEDNUCLE','CL1TMA'=>'OTRDATOS'];
				$curOtrDatos = $this->oDb
							->select('DE1TMA, CL2TMA AS CODIGO')
							->from('TABMAE')
							->where($laCondiciones)
							->orderBy('CL2TMA')
							->get('array');
				$laDocumento['bCupOtrosDatos'] = in_array(trim($taData['cCUP']), explode(',', trim($curOtrDatos['DE1TMA'])));
				foreach($radiogMN01 as $lnvalor)
				{
					$lnCnlrad = $lnvalor['CNLRAD'];
					switch (true)
					{
						case $lnCnlrad > 0 && $lnCnlrad < 101:
							$laDocumento['cResultado'] .= $lnvalor['DESRAD'];
							break;
						case $lnCnlrad == 101:
							$laDocumento['cRegistroMed1'] = trim(substr($lnvalor['DESRAD'], 0,13 ));
							$laDocumento['cRegistroMed2'] = trim(substr($lnvalor['DESRAD'], 14,13 ));
							$laDocumento['cRegistroMed3'] = trim(substr($lnvalor['DESRAD'], 28,13 ));
							$laDocumento['cCodDiagnostico'] = trim(substr($lnvalor['DESRAD'], 42,4 ));
							$laDocumento['cTextoPred'] = trim(substr($lnvalor['DESRAD'], 47,2 ));
							$laDocumento['cCup'] = trim(substr($lnvalor['DESRAD'], 50,8 ));
							$laDocumento['nConsecutivoProc'] = (int)trim(substr($lnvalor['DESRAD'], 59,8 ));
							break;
						case $lnCnlrad == 102:
							$laDocumento['cObservaciones'] = trim($lnvalor['DESRAD']);
							break;
						case $lnCnlrad == 103:
							$laDocumento['cMedicoSolicita'] = trim($lnvalor['DESRAD']);
							break;
						case $lnCnlrad == 104:
							$laDocumento['nFechaMNU'] = (int)trim($lnvalor['DESRAD']);
							break;
						case $lnCnlrad == 105:
							$laDocumento['cSolicitado'] .= $lnvalor['DESRAD'];
							break;
						case $lnCnlrad >= 106 && $lnCnlrad <= 200:
							$laDocumento['cSolicitadoDsc'] .= $lnvalor['DESRAD'];
							break;
						case $lnCnlrad >= 201 && $lnCnlrad <= 300:
							$laDocumento['cConclusion'] .= $lnvalor['DESRAD'];
							break;
						case $lnCnlrad >= 301 && $lnCnlrad <= 304:
							$lnNum = $lnCnlrad - 300;
							for($i=0; $i<5;$i++)
							{
								$laDocumento['nSignosVitales'][$lnNum][$i] = (int)trim(substr($lnvalor['DESRAD'], 8*$i, 8));
							}
							break;
					}
				}
				$laDocumento['cResultado'] = trim($laDocumento['cResultado']);
				$laDocumento['cSolicitado'] = trim($laDocumento['cSolicitado']);
				$laDocumento['cSolicitadoDsc'] = trim($laDocumento['cSolicitadoDsc']);
				if(empty($laDocumento['cSolicitado']) && !empty($laDocumento['cCup']))
				{
					$laCondiciones =['CODCUP' => $laDocumento['cCup']];
					$lcSql= $this->oDb
							->select('DESCUP')
							->from('RIACUP')
							->where($laCondiciones)
							->get('array');
					$laDocumento['cSolicitado'] = trim($lcSql['DESCUP']);
				}
				$laCondiciones = ['NINORD'=>$taData['nIngreso'], 'CCIORD'=>$taData['nConsecCita'], 'COAORD'=>$taData['cCUP']];
				$riaordR001 = $this->oDb
									->select('RMEORD, ESCORD, CIPORD, ESTORD, RATORD, CODORD')
									->from('RIAORD15')
									->where($laCondiciones)
									->get('array');
				if(is_array($riaordR001)==true)
				{
					if(count($riaordR001)>0)
					{
						$laDocumento['nReporteFinal'] = $riaordR001['RATORD'];
						$laDocumento['nReporteFinalEst'] = $riaordR001['ESTORD'];
						$laDocumento['cCodigoEspecialidadMedico'] = $riaordR001['CODORD'];
					}
					if(empty($laDocumento['cMedicoSolicita']) || empty($laDocumento['cObservaciones']))
					{
						$laCondiciones = ['NINORD'=>$taData['nIngreso'], 'CCIORD'=>$taData['nConsecCita'], 'COAORD'=>$taData['cCUP']];
						$riaordR001 = $this->oDb
											->select('RMEORD, ESCORD, CIPORD, ESTORD, RATORD')
											->from('RIAORD15')
											->where($laCondiciones)
											->get('array');
						$laDocumento['nReporteFinal'] = $riaordR001['RATORD'];
						$laDocumento['nReporteFinalEst'] = $riaordR001['ESTORD'];
							if(count($riaordR001)>0)
							{
								$laDocumento['cObservaciones'] = empty($laDocumento['cObservaciones'])? trim($riaordR001['ESCORD']):$laDocumento['cObservaciones'];
								$laDocumento['cCodDiagnostico'] = empty($laDocumento['cCodDiagnostico']) ? trim($riaordR001['CIPORD']) : $laDocumento['cCodDiagnostico'];
								if(empty($laDocumento['cMedicoSolicita']))
								{
									$riargmnR001 = $this->oDb
															->select('NNOMED, NOMMED')
															->from('RIARGMN')
															->where(['REGMED'=>$riaordR001['RMEORD']])
															->get('array');
									$laDocumento['cMedicoSolicita'] = is_array($riargmnR001) ? (count($riargmnR001)>0 ? trim($riargmnR001['NNOMED']).' '.trim($riargmnR001['NOMMED']) : $laDocumento['cMedicoSolicita']) : '';
								}
							}
					}
					if(!empty($laDocumento['cCodDiagnostico']))
					{
						$lcDesDiagnostico = $this->oDb
												->select('DESRIP')
												->from('RIACIEL1')
												->where('ENFRIP', '=', $laDocumento['cCodDiagnostico'])
												->get('array');
						$laDocumento['cDesDiagnostico'] = trim($lcDesDiagnostico['DESRIP']);
					}
				}
					$this->aDocumento = $laDocumento;
			}
		}
	}

	/*
	 *	Prepara array $aReporte con los datos para imprimir
	 */
	private function prepararInforme($taData)
	{		
				$lnAnchoPagina = 90;
				$lcSL = PHP_EOL;
				$cVacios = $this->cTituloVacios;

				// Encabezado
				if(!empty($this->aDocumento['cMedicoSolicita'])){
					$laTr['cTxtAntesDeCup'] = 'Solicita  : '.$this->aDocumento['cMedicoSolicita'];			
				}

				// Cuerpo
				$laTr['aCuerpo'][] = ['titulo1',	$this->aDocumento['cCup'].'  '.$this->aDocumento['cSolicitado']];
				if(!empty($this->aDocumento['cCup']) || !empty($this->aDocumento['cSolicitado'])){
					$laTr['aCuerpo'][] = ['texto9', !empty($this->aDocumento['cSolicitadoDsc']) ? $this->aDocumento['cSolicitadoDsc'] :''];
				}
				
				$laTr['aCuerpo'][] = ['lineah', []];
				if(!empty($this->aDocumento['cCodDiagnostico']))
				{
					$laTr['aCuerpo'][] = ['titulo1',	'DIAGNOSTICO PRINCIPAL'];
					$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cCodDiagnostico'].'   '.$this->aDocumento['cDesDiagnostico']];	
					$laTr['aCuerpo'][] = ['lineah',[]];
				}
				$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cResultado']];
				if(!empty($this->aDocumento['bCupOtrosDatos']))
				{	
					if(count($this->aDocumento['nSignosVitales'])>0){	
						$laTr['aCuerpo'][] = ['titulo1', 'CONTROL DE SIGNOS VITALES'];	
						if($this->aDocumento['bCupOtrosDatos'] && ($this->aDocumento['nSignosVitales'][1][0] > 0 || $this->aDocumento['nSignosVitales'][2][0] > 0 || $this->aDocumento['nSignosVitales'][1][1] > 0))
						{
							$lnNum = count($laTr['aCuerpo']);
							$laTr['aCuerpo'][$lnNum] = ['tabla', [
										['w'=>[30,30,60], 'a'=>'C',
										'd'=>['<br>MINUTOS', 'FRECUENCIA<br>CARDIACA', 'TENSION ARTERIAL<br>Sistólica &nbsp;&nbsp;&nbsp;&nbsp; Diastólica']],
							], [], 30];
							for($i=0; $i<5;$i++)
							{
								if(($this->aDocumento['nSignosVitales'][1][$i] > 0 ) || ($i == 0 && $this->aDocumento['nSignosVitales'][2][$i] > 0))
								{
										$laTr['aCuerpo'][$lnNum][2][] = [
											'w'=>30, 'a'=>'C',
											'd'=>[
												$this->aDocumento['nSignosVitales'][1][$i],
												$this->aDocumento['nSignosVitales'][2][$i],
												$this->aDocumento['nSignosVitales'][3][$i],
												$this->aDocumento['nSignosVitales'][4][$i], ],
										];
								}
							}
						}
					}
				}
				if(!empty($this->aDocumento['cConclusion']))
				{	
					$laTr['aCuerpo'][] = ['lineah', []];
					$laTr['aCuerpo'][] = ['titulo1',	'CONCLUSIÓN'];
					$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cConclusion']];
				}
			
				// Firma
				$laTr['aFirmas'][] = [ 'registro'=>$this->aDocumento['cRegistroMed1'],'prenombre'=>'Dr. ', 'codespecialidad'=>$this->aDocumento['cCodigoEspecialidadMedico'],];
				if(!empty($this->aDocumento['cRegistroMed2'])){
					$laTr['aFirmas'][] = [ 'registro'=>$this->aDocumento['cRegistroMed2'],'prenombre'=>'Dr. ', 'codespecialidad'=>$this->aDocumento['cCodigoEspecialidadMedico'],];					
				}
				if(!empty($this->aDocumento['cRegistroMed3'])){
					$laTr['aFirmas'][] = [ 'registro'=>$this->aDocumento['cRegistroMed3'],'prenombre'=>'Dr. ', 'codespecialidad'=>$this->aDocumento['cCodigoEspecialidadMedico'],];					
				}
				$this->aReporte = array_merge($this->aReporte, $laTr);
	}

	/*
	 *	Array de datos de documento vacío
	 */
	private function datosBlanco()
	{
		return [
			'cResultado' => '',
			'cRegistroMed1' => '',
			'cRegistroMed2' => '',
			'cRegistroMed3' => '',
			'cCodDiagnostico' => '',
			'cDesDiagnostico' => '',
			'cTextoPred' => '',
			'cCup' => '',
			'nConsecutivoProc' => 0,
			'cObservaciones' => '',
			'cMedicoSolicita' => '',
			'nFechaMNU' => 0,
			'cSolicitado' => '',
			'cSolicitadoDsc' => '',
			'cConclusion' => '',
			'nSignosVitales' => [],
			'bCupOtrosDatos'=> false,
			'nReporteFinal'=> 0,
			'nReporteFinalEst'=> 0,
			'cCodigoEspecialidadMedico'=> '',
			'cMotivoConsulta'	=> '',
			'cNivelConciencia'	=> '',
			];
	}
}