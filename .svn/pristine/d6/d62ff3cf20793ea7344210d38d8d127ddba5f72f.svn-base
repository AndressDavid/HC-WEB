<?php
namespace NUCLEO;

class Doc_ResumenAdmin
{
	protected $oDb;
	protected $aDocumento = [];
	protected $aReporte = [
					'cTitulo' => "RESUMEN MEDICO ADMINISTRATIVO",
					'lMostrarFechaRealizado' => true,
					'lMostrarViaCama' => false,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => '',
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
		$lnNumMaxLineas = 50000;

		// tiempo máximo para la consulta
		ini_set('max_execution_time', 600); // 10 minutos de consulta

		$laCondiciones = ['NINEPH'=>$taData['nIngreso'], 'CCNEPH'=>99];
		$laRiaephE001 = $this->oDb
						->select('CONEPH')
						->from('RIAEPH')
						->where($laCondiciones )
						->orderBy('CONEPH')
						->getAll('array');
		if(is_array($laRiaephE001))
		{
			if(count($laRiaephE001)>0)
			{
				$laCondiciones = ['TIDEPI'=>$taData['cTipDocPac'], 'NIDEPI'=>$taData['nNumDocPac'], 'NINEPI'=>$taData['nIngreso'], 'CCNEPI'=>$taData['nConsecDoc'], 'CONEPI'=>$taData['nConsecCons']];
				$laRiaepiE003 = $this->oDb
											->from('RIAEPI')
											->where($laCondiciones)
											->getAll('array');
				if(is_array($laRiaepiE003))
				{
					if(count($laRiaepiE003)>0)
					{
						foreach($laRiaepiE003 as $laData)
						{
							switch(true)
							{
								case $laData['COSEPI'] == 1:
										$laDocumento['lcEntidad'] = trim(substr($laData['DESEPI'],10,60));
										break;

								case $laData['COSEPI'] >= 2 AND $laData['COSEPI'] <= $lnNumMaxLineas:
											$laDocumento['lcObservaciones'] .= $laData['DESEPI'];
											if(($laData['COSEPI'] == 501	) AND (substr($laData['DESEPI'],0, 10) == 'Medico  : ') AND (substr($laData['DESEPI'], 30,10) == 'Fec Rep : '))
											{
												$laDocumento['lcRegMedico'] = trim(substr($laData['DESEPI'], 10,20));
												$laDocumento['lnFechaReporte'] = (int)(substr($laData['DESEPI'], 40,8));
												$laDocumento['lnFechaEgreso'] = (int)(substr($laData['DESEPI'], 60,8));
												$laDocumento['lcCodEspecialidad']= trim(substr($laData['DESEPI'], 80,5));
											}
											break;
								case $laData['COSEPI'] == 500001:
											$laDocumento['lcRegMedico'] = trim(substr($laData['DESEPI'], 10,20));
											$laDocumento['lnFechaReporte'] = (int)(substr($laData['DESEPI'], 40,8));
											$laDocumento['lnFechaEgreso'] = (int)(substr($laData['DESEPI'], 60,8));
											$laDocumento['lcCodEspecialidad']= trim(substr($laData['DESEPI'], 80,5));
											break;
							}
						}
						$lnfechaHoraSistema = substr($this->oDb->fechaHoraSistema(),0,10);
						$lnfechaHoraSistema = (int)str_replace(array('','-'),'',$lnfechaHoraSistema);
						$laDocumento['lnFechaReporte'] = $laDocumento['lnFechaReporte'] ?? $lnfechaHoraSistema;

						$this->oIngreso = new Ingreso();
						$this->oIngreso->cargarIngreso($taData['nIngreso']);
						$laDocumento['lnFechaEgreso'] = $laDocumento['lnFechaEgreso'] != 0 ? $laDocumento['lnFechaEgreso'] : $this->oIngreso->nEgresoFecha;

						if(empty($laDocumento['lcRegMedico']))
						{
							$laCondiciones =['USUARI'=>trim($laData['USREPI'])];
							$lcMedicoTemp = $this->oDb
													->select('REGMED,CODRGM')
													->from('RIARGMN')
													->where($laCondiciones)
													->get('array');
							if(is_array($lcMedicoTemp))
							{
								if(count($lcMedicoTemp)>0)
								{
									$laDocumento['lcRegMedico'] = trim($lcMedicoTemp['REGMED']);
									$laDocumento['lcCodEspecialidad']=trim($lcMedicoTemp['CODRGM']);
								}
							}
							$laDocumento['lnFechaReporte'] = trim($laData['FECEPI']);
						}
					}
				}
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
		if(!empty($this->aDocumento['lcObservaciones']))
		{
			$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['lcObservaciones']];
		}
		$laTr['aCuerpo'][]=['firmas', [
				['registro' => $this->aDocumento['lcRegMedico'],'prenombre'=>'Dr. ', 'codespecialidad' => $this->aDocumento['lcCodEspecialidad']]]];

		$this->aReporte = array_merge($this->aReporte, $laTr);
		$this->aReporte['cTxtAntesDeCup'] = 'F.Ingreso : '.AplicacionFunciones::formatFechaHora('fecha', $taData['oIngrPaciente']->nIngresoFecha)
		.str_repeat(' ',43).'F.Egreso  : '.AplicacionFunciones::formatFechaHora('fecha', $this->aDocumento['lnFechaEgreso']);
	}

	private function datosBlanco()
	{
		return [
			'lcEntidad' => '',
			'lcObservaciones' => '',
			'lcRegMedico' => '',
			'lnFechaReporte' => 0,
			'lnFechaEgreso' => 0,
			'lcCodEspecialidad'=>'',
		];
	}
}