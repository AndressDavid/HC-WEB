<?php
namespace NUCLEO;

class Doc_Epidemiologia
{
	protected $oDb;
	protected $aDocumento = [];
	protected $aReporte = [
		'cTitulo' => "VIGILANCIA EPIDEMIOLÓGICA",
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
		$laDocumento = [];
		$laOrdenTipo = [];
		$laCampos = ['CL2TMA AS CODIGO','INT(CL3TMA) AS NIVEL',	'CL4TMA AS PREGUN', 'DE2TMA AS DETALLE', 'OP5TMA AS OBJETO', 'OP1TMA AS ACTIVO','INT(OP3TMA) AS ORDEN'];
		$laCondiciones = ['TIPTMA'=>'EPIDEMIO', 'CL1TMA'=>'NUMERAL'];
		$laEpidem = $this->oDb
			->select($laCampos)
			->from('TABMAE')
			->where($laCondiciones)
			->where('ESTTMA','<>',1)
			->orderBy('INT(OP3TMA)')
			->getAll('array');
		foreach($laEpidem as $lnClave=>$laEpd){
			$laEpidem[$lnClave]=array_map('trim',$laEpd);
			if(strlen($laEpidem[$lnClave]['CODIGO'])==2){
				$laOrdenTipo[$laEpidem[$lnClave]['CODIGO']]=str_pad($laEpidem[$lnClave]['ORDEN'],3,'0',STR_PAD_LEFT);
			}
		}
		$laCampos = ['SUBSTR(OP5TMA,0,70) AS DESCR', 'DE2TMA AS DESCRI', 'CL4TMA AS VALOR'];
		$laCondiciones = ['TIPTMA' => 'EPIDEMIO', 'CL1TMA' => 'ESCALAF'];
		$laEscalaFB =	$this->oDb->distinct()
			->select($laCampos)
			->from('TABMAE')
			->where($laCondiciones)
			->getAll('array');
		foreach($laEscalaFB as $lnClave => $laEsFB){
			$laEscalaFB[$lnClave] = array_map('trim', $laEsFB);
		}
		$laEnfCodigos=[];
		$laCampos =['DESENF AS DESEVE', 'REFENF AS REFEVE', 'VARENF AS VAREVE', 'TIPENF AS TIPO'];
		$laTemporal = $this->oDb
			->select($laCampos)
			->from('TABENF')
			->in('VARENF',[1, 2, 3, 4, 8, 9])
			->in('TIPENF', [3, 5, 11, 16, 19, 23, 28, 75, 76])
			->orderBy('REFENF')
			->getAll('array');
		
		foreach($laTemporal as $lnClave => $laTemp){
			$laTemp = array_map('trim', $laTemp);
			$laTmpCODIGO = str_pad(trim($laTemp['TIPO']), 2, "0", STR_PAD_LEFT)
						. str_pad(trim($laTemp['VAREVE']), 2, "0", STR_PAD_LEFT)
						. str_pad(trim($laTemp['REFEVE']), 2, "0", STR_PAD_LEFT);
			$laEnfCodigos[$laTmpCODIGO] = trim($laTemp['DESEVE']);
		}
		$lcCod03='';
		$laCondiciones = ['INGEPI'=>$taData['nIngreso'], 'CONEPI'=>$taData['nConsecDoc']];
		$laEpirgs = $this->oDb
			->from('EPIRGS')
			->where($laCondiciones)
			->getAll('array');
		if(is_Array($laEpirgs))
		{
			if(count($laEpirgs)>0)
			{
				foreach($laEpirgs as $laClave => $laRegistros)
				{
					$laRegistros = array_map('trim', $laRegistros);
					$lcCod03 = substr($laRegistros['DETEPI'],0,4) == '0301'? $laRegistros['REGEPI'] : $lcCod03;
					$lcInformacion = explode("¤", trim($laRegistros['DETEPI']));
					$laRegistros['OP1EPI'] = is_null($laRegistros['OP1EPI']) ? '' : $laRegistros['OP1EPI'];

					foreach($lcInformacion as $lcValor)
					{
						$lcCodPreg = substr($lcValor, 0, 4);
						$lcPreg = AplicacionFunciones::lookup($laEpidem, 'DETALLE', $lcCodPreg, 'CODIGO');
						$lnTitulo = substr($lcValor, 0, 2);
						$lcTitulo = AplicacionFunciones::lookup($laEpidem, 'DETALLE', $lnTitulo, 'CODIGO');
						$lnOrden = AplicacionFunciones::lookup($laEpidem, 'ORDEN', $lcCodPreg, 'CODIGO');
						$lnOrden = str_pad($lnOrden, 2, "0", STR_PAD_LEFT);
						$lcDesResp = '';
						$lcOpcional = '';
						switch (true)
						{
							case in_array($lcCodPreg, ['0111','0112','0209','0210','0316','0611','0612']):
								$lcFecha = substr($lcValor,5,8);
								$lcDesResp = AplicacionFunciones::formatFechaHora('fecha', $lcFecha) ;
								break;

							case in_array($lcCodPreg, ['0110','0113','0208','0213','0214','0610','0613','0614','0616','0701','0707','0708','0709','0902','0903','1002','1009','1102']):
								$lcResp = substr($lcValor,5, 1);

								switch(true)
								{
									case in_array($lcCodPreg,['0110','0208','0610','0902']):
										$lcResp = '1601' . str_pad($lcResp, 2, "0", STR_PAD_LEFT);
										$lcDesResp = $laEnfCodigos[$lcResp] ?? '';
										break;

									case $lcCodPreg =='0113':
										$lcResp = '1602' . str_pad($lcResp, 2, "0", STR_PAD_LEFT);
										$lcDesResp = '    ' . $laEnfCodigos[$lcResp] ?? '';
										break;

									case in_array($lcCodPreg,['0213','0214','0616']):
										$lcResp = ($lcCodPreg=='0213' ? '2301' : '2302') . str_pad($lcResp, 2, "0", STR_PAD_LEFT);
										$lcDesResp = $laEnfCodigos[$lcResp] ?? '';
										break;

									case in_array($lcCodPreg,['0613','0614']):
										$lcResp = ($lcCodPreg=='0613' ? '0508' : '0509') . str_pad($lcResp, 2, "0", STR_PAD_LEFT);
										$lcDesResp = $laEnfCodigos[$lcResp] ?? '';
										break;

									case substr($lcCodPreg, 0, 2)== '07' :
										if($lcCodPreg==='0708'){
											$lcDesResp = ($lcResp=='1'? 'VIVO':'FALLECE');
										} else{
											$lcResp = ($lcCodPreg=='0701' ? '0301' : ($lcCodPreg=='0707' ? '0302' : ($lcCodPreg=='0709' ? '0303' : $lcResp))) .  str_pad($lcResp, 2, "0", STR_PAD_LEFT);
											$lcDesResp = $laEnfCodigos[$lcResp] ?? '';
										}
										break;

									case $lcCodPreg == '0903':
										$lcResp = '1901' . str_pad($lcResp, 2, "0", STR_PAD_LEFT);
										$lcDesResp = $laEnfCodigos[$lcResp] ?? '';
										break;

									case in_array($lcCodPreg,['1002','1102']):
										$lcResp = '1604' . str_pad($lcResp, 2, "0", STR_PAD_LEFT);
										$lcDesResp = $laEnfCodigos[$lcResp] ?? '';
										break;
									
									case $lcCodPreg == '1009':

										$lcResp = '1604' . str_pad($lcResp, 2, "0", STR_PAD_LEFT);
										$lcDesResp = $laEnfCodigos[$lcResp] ?? '';
										break;
								}
								break;

							case $lcCodPreg == '0302' || $lcCodPreg == '0703':
								$lcDesResp = substr($lcValor,5);
								break;

							case $lcCodPreg == '0607':
								$lcResp = str_pad(substr($lcValor,5, 1), 2, "0", STR_PAD_LEFT);
								$lcDesResp = substr($lcValor,5,1) . ' - ' .trim(AplicacionFunciones::lookup($laEscalaFB, 'DESCR', $lcResp, 'VALOR'));
								break;

							case strlen($lcValor) == 6:
								$lcResp = substr($lcValor,5, 1);
								$lcDesResp = $lcResp == '1'  ? 'SI': ($lcResp == '2' ? 'NO' :'');
								break;

							case strlen($lcValor) == 11:
								$lcResp = substr($lcValor, 5,6);
								if ($lcResp !== '0')
								{
									if(in_array($laRegistros['ITEEPI'], [2,3,4,7]))
									{
										$lcDesResp = $laEnfCodigos[$lcResp] ?? '';
									}
								}
								break;

							case  strlen($lcValor) == 15:
								$lcResp = substr($lcValor, 5,6);
								if ( $lcCodPreg == '0307' )
								{
									$laRegistros['REGEPI'] = $lcCod03;
									$lcOpcional =  $laEnfCodigos[$lcResp] . ' ' .substr($lcValor, 12,1);
									$lcDesResp = (substr($lcValor,14,1) == '1' ) ? 'Si' : ( substr($lcValor,14,1) == '2' ? 'No':'' );
								}
								break;

							default:
								$lcResp = '';
								$lcOpcional = '';
								$lcDesResp = '';
								break;
						}

						$lcCodPreg = empty($lcDesResp) ? '' : $lcCodPreg;
						if (!empty($lcCodPreg))
						
						{
							$laDocumento[] = [
								'ORDEN'		=> $laOrdenTipo[$lnTitulo] . $laRegistros['REGEPI'] . $lnOrden . '0',
								'CODPREG'	=> $lcCodPreg,
								'DESPREG'	=> trim($lcPreg),
								'RESPUESTA'	=> $lcDesResp,
								'OPCIONAL'	=> $lcOpcional,
								'REGISTRO'	=> $laRegistros['REGEPI'],
								'TIPO'		=> '',
								'REGIF'		=> $laRegistros['OP1EPI'],
								'TITULO'	=> $lcTitulo,
								'USUEPI'	=> $laRegistros['USUEPI'],
							];
						}
					}

				if(($laRegistros['ITEEPI'] == 3 || $laRegistros['ITEEPI'] == 7) && !empty($laRegistros['FEIEPI']))
					{
						$lcCodPreg = $laRegistros['ITEEPI']==3 ? '0311' : '0704';
						$lnTitulo = $laRegistros['ITEEPI']==3 ? '03' : '07';
						$lcTitulo = AplicacionFunciones::lookup($laEpidem, 'DETALLE', $lnTitulo, 'CODIGO');
						$lcPreg = AplicacionFunciones::lookup($laEpidem, 'DETALLE', $lcCodPreg, 'CODIGO');
						$lnOrden = AplicacionFunciones::lookup($laEpidem, 'ORDEN', $lcCodPreg, 'CODIGO');
						$lnOrden = str_pad($lnOrden, 2, "0", STR_PAD_LEFT);
						$lcDesResp = AplicacionFunciones::formatFechaHora('fecha', $laRegistros['FEIEPI']) ;

						$laDocumento[] = [
							'ORDEN'		=> $laOrdenTipo[$lnTitulo] . $laRegistros['REGEPI'] . $lnOrden . '0',
							'CODPREG'	=> $lcCodPreg,
							'DESPREG'	=> trim($lcPreg),
							'RESPUESTA'	=> $lcDesResp,
							'OPCIONAL'	=> $lcOpcional,
							'REGISTRO'	=> $laRegistros['REGEPI'],
							'TIPO'		=> '',
							'REGIF'		=> $laRegistros['OP1EPI'],
							'TITULO'	=> $lcTitulo,
							'USUEPI'	=> $laRegistros['USUEPI'],
						];
					}

				if(!empty($laRegistros['DE2EPI']))
					{
						$lcInformacion = explode("¤",  trim($laRegistros['DE2EPI']));
						foreach($lcInformacion as $lcValor)
						{
							$lcCodPreg = trim(substr($lcValor, 0, 4));
							$lcPreg = AplicacionFunciones::lookup($laEpidem, 'DETALLE', $lcCodPreg, 'CODIGO');
							$lcDesResp = trim(substr($lcValor,5));
							$lnTitulo = substr($lcValor, 0, 2);
							$lcTitulo = AplicacionFunciones::lookup($laEpidem, 'DETALLE', $lnTitulo, 'CODIGO');
							$lnOrden = AplicacionFunciones::lookup($laEpidem, 'ORDEN', $lcCodPreg, 'CODIGO');
							$lnOrden = str_pad($lnOrden, 2, "0", STR_PAD_LEFT);
							if(!empty($lcCodPreg))
							{
								$laDocumento[] = [
									'ORDEN'		=> $laOrdenTipo[$lnTitulo] . $laRegistros['REGEPI'] . $lnOrden  . '1',
									'CODPREG'	=> $lcCodPreg,
									'DESPREG'	=> $lcPreg,
									'RESPUESTA'	=> $lcDesResp,
									'OPCIONAL'	=> $lcOpcional,
									'REGISTRO'	=> $laRegistros['REGEPI'],
									'TIPO'		=> 'O',
									'REGIF'		=> $laRegistros['OP1EPI'],
									'TITULO'	=> $lcTitulo,
									'USUEPI'	=> $laRegistros['USUEPI'],
								];
							}
						}
					}

					if(!empty(trim($laRegistros['OBSEPI'])))
					{
						$lcInformacion = explode("¤",  trim($laRegistros['OBSEPI']));
						foreach($lcInformacion as $lcValor)
						{
							$lcCodPreg = trim(substr($lcValor, 0, 4));
							$lcPreg = AplicacionFunciones::lookup($laEpidem, 'DETALLE', $lcCodPreg, 'CODIGO');
							$lcDesResp = trim(substr($lcValor,5));
							$lnTitulo = substr($lcValor, 0, 2);
							$lcTitulo = AplicacionFunciones::lookup($laEpidem, 'DETALLE', $lnTitulo, 'CODIGO');
							$lnOrden = AplicacionFunciones::lookup($laEpidem, 'ORDEN', $lcCodPreg, 'CODIGO');
						    $lnOrden = str_pad($lnOrden, 2, "0", STR_PAD_LEFT);
							if(!empty($lcCodPreg))
							{
								$laDocumento[] = [
									'ORDEN'		=> $laOrdenTipo[$lnTitulo] . $laRegistros['REGEPI'] . $lnOrden  . '1',
									'CODPREG'	=> $lcCodPreg,
									'DESPREG'	=> $lcPreg,
									'RESPUESTA'	=> $lcDesResp,
									'OPCIONAL'	=> $lcOpcional,
									'REGISTRO'	=> $laRegistros['REGEPI'],
									'TIPO'		=> 'O',
									'REGIF'		=> $laRegistros['OP1EPI'],
									'TITULO'	=> $lcTitulo,
									'USUEPI'	=> $laRegistros['USUEPI'],
								];
							}
						}
					}

				}
			}
		}
		array_multisort(array_values($laDocumento), array_keys($laDocumento), $laDocumento);
		$this->aDocumento = $laDocumento;
	}

	private function prepararInforme($taData)
	{
		$lnAnchoPagina = 90;
		$lcSL = PHP_EOL;
		$cVacios = $this->cTituloVacios;
		$lcDescrip = '';
		$lcTemp = '';
		$lcCod03='';

		foreach($this->aDocumento as $laRegistros)
		{
			if($lcTemp !== substr($laRegistros['ORDEN'],0,4))
			{
				if(substr($lcTemp,0,3) != substr($laRegistros['ORDEN'],0,3))
				{
					$laTr['aCuerpo'][] = ['titulo1', $laRegistros['REGIF'] === 'I' ? $laRegistros['TITULO'] . ' INICIO AISLAMIENTO':($laRegistros['REGIF'] === 'R' ? $laRegistros['TITULO'] . ' RETIRO AISLAMIENTO': $laRegistros['TITULO']), 'L'];
				}
					$lcTemp = substr($laRegistros['ORDEN'],0,4);
					$lnNum = count($laTr['aCuerpo']);
					$laTr['aCuerpo'][$lnNum] = [
						'tabla', [
							['w'=>[160, 30], 'a'=>'C',
							'd' => ['ITEM', 'CUMPLIMIENTO']],
						],
						[]
					];
			}
			if($laRegistros['TIPO'] == 'O' && ($laRegistros['RESPUESTA'] != '' ))
			{
				$lcCambio = $laRegistros['CODPREG']==='0310'?'Observaciones Carbapenemicos: ':($laRegistros['CODPREG']==='0315'?'Observaciones Candida Auris: ':$laRegistros['DESPREG']);
				$laTr['aCuerpo'][$lnNum][2][] = ['w'=>[190],'a' =>'L', 'd' =>[$lcCambio . trim($laRegistros['RESPUESTA']) ]];
			}
			else
			{
				if($laRegistros['CODPREG'] == '0307')
				{
					if ($lcCod03 !== $laRegistros['DESPREG'])
					{
						$lcCod03 = $laRegistros['DESPREG'];
						$laTr['aCuerpo'][$lnNum][2][] = ['w'=>[190],'a' =>'L', 'd' => [$laRegistros['DESPREG']]];
					}
					if($laRegistros['RESPUESTA']==='Si')
					{
						$laTr['aCuerpo'][$lnNum][2][] = ['w'=>[160, 15, 15],'a' =>['L','C','C'], 'd' => [trim($laRegistros['OPCIONAL']),$laRegistros['RESPUESTA'],' ']];
					} else {
						$laTr['aCuerpo'][$lnNum][2][] = ['w'=>[160, 15, 15],'a' =>['L','C','C'], 'd' => [' '.trim($laRegistros['OPCIONAL']),' ',$laRegistros['RESPUESTA']]];
					}

				} else {
					switch (true)
					{
						case trim($laRegistros['RESPUESTA']) == 'SI':
							$laTr['aCuerpo'][$lnNum][2][] = ['w'=>[160, 15, 15],'a' =>['L','C','C'], 'd' =>[empty($laRegistros['OPCIONAL']) ? $laRegistros['DESPREG'] : trim($laRegistros['OPCIONAL']), 'Si', '']];
							break;

						case trim($laRegistros['RESPUESTA']) == 'NO':
							$laTr['aCuerpo'][$lnNum][2][] = ['w'=>[160, 15, 15],'a' =>['L','C','C'], 'd' =>[empty($laRegistros['OPCIONAL']) ? (($laRegistros['CODPREG'] == '0100' ||  $laRegistros['CODPREG'] == '0600') ? 'NO VALORABLE' : $laRegistros['DESPREG'] ): trim($laRegistros['OPCIONAL']), '', 'No']];
							break;

						default:
							if($laRegistros['RESPUESTA'] != '' )
							{
								$laTr['aCuerpo'][$lnNum][2][] = ['w'=>[190],'a' =>'L', 'd' =>[(empty($laRegistros['OPCIONAL']) ? $laRegistros['DESPREG'] : trim($laRegistros['OPCIONAL'])) . ' : ' .trim($laRegistros['RESPUESTA'])]];
							}
							break;
					}
				}
			}
		}
		$laTr['aCuerpo'][]=[
			'firmas', [
				[
					'usuario' => $laRegistros['USUEPI']
				]
			]
		];

		$this->aReporte = array_merge($this->aReporte, $laTr);
	}
}












