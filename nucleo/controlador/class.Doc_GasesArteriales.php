<?php
namespace NUCLEO;

class Doc_GasesArteriales
{
	protected $oDb;
	protected $aDocumento = [];
	protected $aReporte = [
				'cTitulo' => 'GASES ARTERIALES',
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

	public function retornarDocumento($taData)
	{
		$this->consultarDatos($taData);
		$this->prepararInforme($taData);
		return $this->aReporte;
	}

	private function consultarDatos($taData)
	{
		$laDocumento = $this->datosBlanco();
		$laCondiciones = ['NROGAS'=>$taData['nIngreso'], 'CONGAS'=>$taData['nConsecCita'], 'SUBGAS'=>$taData['cCUP'] ];
		$laGases = $this->oDb
			->from('GASES')
			->where($laCondiciones)
			->getAll('array');
		if(is_array($laGases)==true)
		{
			if(count($laGases) > 0)
			{
				
				foreach($laGases as $laData)
				{
					switch(true)
					{
					// Cargar Cabecera
						case $laData['INDGAS']==1 && $laData['CNLGAS']==1:
							$laDocumento['lnPacienteCritico'] = trim(substr($laData['DESGAS'], 10, 9));
							$laDocumento['lcMedicoRealizo'] = trim(substr($laData['DESGAS'], 31, 13));
							$laDocumento['lcCreoUsuario'] = $laData['USRGAS'];
							$laDocumento['lcCreoPrograma'] = $laData['PGMGAS'];
							$laDocumento['lnCreoFecha'] = $laData['FECGAS'];
							$laDocumento['lnCreoHora'] = $laData['HORGAS'];
							$laDocumento['lcModiUsuario'] = $laData['UMOGAS'];
							$laDocumento['lcModiPrograma'] = $laData['PMOGAS'];
							$laDocumento['lnModiFecha'] = $laData['FMOGAS'];
							$laDocumento['lnModiHora'] = $laData['HMOGAS'];
							break;

					// Cargado arterial
						case $laData['INDGAS']==2 && $laData['CNLGAS'] == 1:
							$laDocumento['lnFi02'] = floatval(trim(substr($laData['DESGAS'], 6, 8))); 
							$laDocumento['lnPh'] = floatval(trim(substr($laData['DESGAS'], 21, 7)));	
							$laDocumento['lnPaCO2'] = floatval(trim(substr($laData['DESGAS'], 36, 8)));
							$laDocumento['lnPaO2'] = floatval(trim(substr($laData['DESGAS'], 51, 8)));	
							$laDocumento['lnHC03'] = (trim(substr($laData['DESGAS'], 66, 8)));	
							$laDocumento['lnBaseExceso'] = (trim(substr($laData['DESGAS'], 81, 8)));	
							$laDocumento['lnSaturacionArterial02'] = floatval(trim(substr($laData['DESGAS'], 96, 8)));	
							$laDocumento['lnNa'] = (trim(substr($laData['DESGAS'], 111, 8)));	
							break;
						case $laData['INDGAS']==2 && $laData['CNLGAS'] == 2:
							$laDocumento['lnK'] = (trim(substr($laData['DESGAS'], 6, 8)));	
							$laDocumento['lnCa'] = (trim(substr($laData['DESGAS'], 21, 8)));	
							$laDocumento['lnHemoglobina'] = floatval(trim(substr($laData['DESGAS'], 36, 8)));	
							$laDocumento['lnHemotocrito'] = (trim(substr($laData['DESGAS'], 51, 8)));	
							break;

					// Cargando Venoso
						case $laData['INDGAS']==3 && $laData['CNLGAS'] == 1:
							$laDocumento['lnPhV'] = floatval(trim(substr($laData['DESGAS'], 6, 8)));	
							$laDocumento['lnPaCO2V'] = floatval(trim(substr($laData['DESGAS'], 21, 8)));	
							$laDocumento['lnPaO2V'] = floatval(trim(substr($laData['DESGAS'], 36, 8)));	
							$laDocumento['lnHC03V'] = (trim(substr($laData['DESGAS'], 51, 8)));	
							$laDocumento['lnSaturacionVenosa02'] = floatval(trim(substr($laData['DESGAS'], 66, 8)));	
							$laDocumento['lnP50'] = (trim(substr($laData['DESGAS'], 81, 8)));	
							$laDocumento['lnIndiceCardiaco'] = floatval(trim(substr($laData['DESGAS'], 96, 8)));	
							$laDocumento['lnDiasAlteracionAcidoBase'] = (trim(substr($laData['DESGAS'], 111, 8)));	
							break;

					//Cargando glucosa, lactato
						case $laData['INDGAS']==4 && $laData['CNLGAS'] == 1:
							$laDocumento['lnGlucosa'] = (trim(substr($laData['DESGAS'], 6, 8)));	
							$laDocumento['lnLactato'] = (trim(substr($laData['DESGAS'], 21, 8)));	
							break;

					//Cargando observacion
						case $laData['INDGAS']==5 && $laData['CNLGAS'] == 1 :
							$laDocumento['lcObservacionVenoso'] .= $laData['DESGAS'];
							$laDocumento['lcObservacionVenoso'] = trim($laDocumento['lcObservacionVenoso']);
							break;
					}
				}
				if (empty($laDocumento['lcMedicoRealizo']))
				{
					$laDocumento['lcMedicoRealizo'] = !empty($taData['cRegMedRealiza'])? $taData['cRegMedRealiza']:'';
				}

				$lngavinf = 1;
				$lnPa1 = 560;
				$lnPa2 = 47;
				$lnPa3 = 0.85;
				$lnPa4 = 1.34;
				$lnPa5 = 0.0031;
				$lnPa6 = 80;
				$lnPa7 = 0.75;
				$lnPa8 = 10;
				$lnPa9 = 10;
				$lnPa8 = $laDocumento['lnPacienteCritico'] == 1 ? 27 : $lnPa8;
				$laDocumento['lnPresionAlveolar'] = ($lnPa1 - $lnPa2)* $laDocumento['lnFi02'] - ($laDocumento['lnPaCO2'] / $lnPa3); //&&	PAO2
				$laDocumento['lnContCapilarO2'] = ($lnPa4 * $laDocumento['lnHemoglobina']) + ($lnPa5 * $laDocumento['lnPresionAlveolar'] ); //&&	CcO2
				$laDocumento['lnContArterialO2'] = ($lnPa4 * $laDocumento['lnHemoglobina'] * ($laDocumento['lnSaturacionArterial02'] * 0.01) ) + ($lnPa5 * $laDocumento['lnPaO2']); //&& CaO2
				$laDocumento['lnContVenosoO2'] = ($lnPa4 * $laDocumento['lnHemoglobina'] * ($laDocumento['lnSaturacionVenosa02'] * 0.01)) + ($lnPa5 * $laDocumento['lnPaO2V']); //&&	CvO2
				$laDocumento['lnContArtVenO2'] = $laDocumento['lnContArterialO2'] - $laDocumento['lnContVenosoO2']; //&&	CaO2 - CvO2
				$laDocumento['lnDifAlvArt'] = $laDocumento['lnPresionAlveolar'] - $laDocumento['lnPaO2']; //&&	DAaO2
				$laDocumento['lnShunt'] = ($laDocumento['lnContCapilarO2'] - $laDocumento['lnContVenosoO2']) == 0 ? 0 : $laDocumento['lnShunt'] = (($laDocumento['lnContCapilarO2'] - $laDocumento['lnContArterialO2']) / ($laDocumento['lnContCapilarO2'] - $laDocumento['lnContVenosoO2'])) * 100; //&&	Qs/Qt
				$laDocumento['lnExtraccionO2'] = $laDocumento['lnContArterialO2'] == 0 ? 0 :($laDocumento['lnContArtVenO2'] / $laDocumento['lnContArterialO2']) * 100; //&&	Extraccion O2
				$laDocumento['lnDeltaPh'] = $laDocumento['lnPh'] - $laDocumento['lnPhV']; //&&	D pH
				$laDocumento['lnDeltaCO2'] = $laDocumento['lnPaCO2V'] - $laDocumento['lnPaCO2']; // &&	D CO2
				$laDocumento['lnHidrMetaArt'] = ($lnPa6 - ((fmod($laDocumento['lnPh'], 1)) * 100)) - ($lnPa7 * $laDocumento['lnPaCO2'] + $lnPa8);	//&&	Harterial
				$laDocumento['lnHidrMetaVen'] = ($lnPa6 - ((fmod($laDocumento['lnPhV'], 1)) * 100)) - ($lnPa7 * $laDocumento['lnPaCO2V'] + $lnPa8); //&&	Hvenoso
				$laDocumento['lnPa02Fi02'] = $laDocumento['lnFi02'] == 0 ? 0 : $laDocumento['lnPaO2'] / $laDocumento['lnFi02']; //&&	PaO2 / FiO2
				$laDocumento['lnPa02PA02'] = $laDocumento['lnPresionAlveolar'] == 0 ? 0 : $laDocumento['lnPaO2'] / $laDocumento['lnPresionAlveolar']; //&&	PaO2 / PAO2
				$laDocumento['lnAporteO2'] = $laDocumento['lnContArterialO2'] * $laDocumento['lnIndiceCardiaco'] * $lnPa9; //&&	IDO2
				$laDocumento['lnConsumoO2'] = $laDocumento['lnDifAlvArt'] * $laDocumento['lnIndiceCardiaco'] * $lnPa9; // &&	IVO2
			}
		}
		$this->aDocumento = $laDocumento;
	}

	private function prepararInforme($taData)
	{
		$lnAnchoPagina = 90;
		$lcSL = PHP_EOL;
		$cVacios = $this->cTituloVacios;
	
	// Cuerpo
		$laData = [
			'ARTERIAL'=>[
				['Fi02','lnFi02',' %'],
				['pH','lnPh',''],
				['PaCO2','lnPaCO2',' mmHg'],
				['PaO2', 'lnPaO2', ' mmHg'],
				['HC03', 'lnHC03', ' meq/L'],
				['Base Exceso', 'lnBaseExceso', ' meq/L'],
				['Saturación 02', 'lnSaturacionArterial02', ' %'],
				['Na', 'lnNa', ''],
				['K', 'lnK', ''],
				['Ca', 'lnCa', ''],
				['Hemoglobina', 'lnHemoglobina', ' gr/dL'],
				['Hemotocrito', 'lnHemotocrito', ''],
				['Glucosa', 'lnGlucosa', ''],
				['Lactato', 'lnLactato', ''],
			],
			'VENOSO'=>[
				['pH', 'lnPhV', ''],
				['PvCO2', 'lnPaCO2V', ' mmHg'],
				['PvO2', 'lnPaO2V', ' mmHg'],
				['HC03', 'lnHC03V', ' meq/L'],
				['Saturación 02', 'lnSaturacionVenosa02', ' %'],
				['Hemoglobina', 'lnHemoglobina', ' gr/dL'],
				['Hemotocrito', 'lnHemotocrito', ''],
				['p50', 'lnP50', ' mmHg'],
				['Indice Cardiaco', 'lnIndiceCardiaco', ' L/min/m2'],
				['Días Alteración Acido Base', 'lnDiasAlteracionAcidoBase', ''],
			],
			'CALCULOS'=>[
				['Ca02 -Cv02', 'lnContArtVenO2', ' mL/dL'],
				['IDO2', 'lnAporteO2', ' mL/min/m2'],
				['IVO2', 'lnConsumoO2', ' mL/min/m2'],
				['Extracción O2', 'lnExtraccionO2', ' %'],
				['Delta pH', 'lnDeltaPh', ''],
				['Harteriales', 'lnHidrMetaArt', ' nM Ag'],
				['Hvenosos', 'lnHidrMetaVen', ' nM Ag'],
				['Delta CO2', 'lnDeltaCO2', ''],
				['PA02', 'lnPresionAlveolar', ' mmHg'],
				['DAa02', 'lnDifAlvArt', ' mmHg'],
				['Pa02 / Fi02', 'lnPa02Fi02', ''],
				['Pa02 / PA02', 'lnPa02PA02', ''],
				['Qs / Qt', 'lnShunt', ' %'],
				['Cc02', 'lnContCapilarO2', ' mL/dL'],
				['Ca02', 'lnContArterialO2', ' mL/dL'],
				['Cv02', 'lnContVenosoO2', ' mL/dL'],
			]
		];
		foreach($laData as $lcTitulo => $laRegistros)
		{
			$laTr['aCuerpo'][] = ['titulo1', $lcTitulo, 'C'];
			$lcTexto ='';
			foreach($laRegistros as $lnIndice=> $lcValor)
			{
				$lcTexto .= ' '.$lcValor[0] . str_pad(sprintf("%01.2f", $this->aDocumento[$lcValor[1]]) . $lcValor[2].' ', 45 - mb_strlen($lcValor[0]), '.', STR_PAD_LEFT). (fmod($lnIndice, 2) != 0 ? PHP_EOL : ' | ');
			}
			$laTr['aCuerpo'][] = ['texto9', $lcTexto];
		}
		if(!empty($this->aDocumento['lcObservacionVenoso']))
		{
			$laTr['aCuerpo'][] = ['titulo1',	'Observación', 'C'];
			$laTr['aCuerpo'][] = ['txthtml9',	$this->aDocumento['lcObservacionVenoso'], 'C'];
		}
		
		$this->aReporte = array_merge($this->aReporte, $laTr);
	}

	private function datosBlanco()
	{
		return [
			'lnFi02' => 0,
			'lnPh' => 0,
			'lnPaCO2' => 0,
			'lnPaO2' => 0,
			'lnHC03' => 0,
			'lnBaseExceso' => 0,
			'lnSaturacionArterial02' => 0,
			'lnNa' => 0,
			'lnK' => 0,
			'lnCa' => 0,
			'lnHemoglobina' => 0,
			'lnHemotocrito' => 0,
			'lnGlucosa' => 0,
			'lnLactato' => 0,
			'lnPhV' => 0,
			'lnPaCO2V' => 0,
			'lnPaO2V' => 0,
			'lnHC03V' => 0,
			'lnSaturacionVenosa02' => 0,
			'lnP50' => 0,
			'lnIndiceCardiaco' => 0,
			'lnDiasAlteracionAcidoBase' => 0,
			'lnContArtVenO2' => 0,
			'lnAporteO2' => '0.00',
			'lnConsumoO2' => 0,
			'lnExtraccionO2' => 0,
			'lnDeltaPh' => 0,
			'lnHidrMetaArt' => 0,
			'lnHidrMetaVen' => 0,
			'lnDeltaCO2' => 0,
			'lnPresionAlveolar' => 0,
			'lnDifAlvArt' => 0,
			'lnPa02Fi02' => 0,
			'lnPa02PA02' => 0,
			'lnShunt' => 0,
			'lnContCapilarO2' => 0,
			'lnContArterialO2' => 0,
			'lnContVenosoO2' => 0,
			'lcObservacionVenoso' => '',
			'lnPacienteCritico' => 0,
			'lnPa02' => 0,
			'lcMedicoRealizo' => '',
			'lcCreoUsuario' => '',
			'lcCreoPrograma' => '',
			'lnCreoFecha' => 0,
			'lnCreoHora' => 0,
			'lcModiUsuario' => '',
			'lcModiPrograma' => '',
			'lnModiFecha' => '',
			'lnModiHora' => '',
		];
	}
}