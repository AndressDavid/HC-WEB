<?php
namespace NUCLEO;

class Doc_Hemodinamia
{
	protected $oDb;
	protected $aDocumento = [];
	protected $aReporte = [
					'cTitulo' => "",
					'lMostrarFechaRealizado' => true,
					'lMostrarViaCama' => true,
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

		$laCondiciones = ['INGANG'=>$taData['nIngreso'], 'CONANG'=>$taData['nConsecCita'], 'PROANG'=>$taData['cCUP'], 'INDICE'=>11];
		$laAngioH008 = $this->oDb
						->from('ANGIO')
						->where($laCondiciones )
						->getAll('array');
		foreach($laAngioH008 as $lnClave => $laAng)
		{
			$laAngioH008[$lnClave] = array_map('trim', $laAng);
		}
		if(is_array($laAngioH008))
		{
			if(count($laAngioH008)>0)
			{
				foreach($laAngioH008 as $laData)
				{
					$laDocumento['pdimed'] = trim($laData['REGANG']);
					$laDocumento['pdidia'] = trim($laData['SUBIND']);
					$laDocumento['pdimho'] = trim($laData['ESTANG']);

					if(!empty($laDocumento['pdidia']))
					{
						$laCampos = ['DE1TMA AS DESDIA', 'CL3TMA AS CODDIA', 'OP2TMA AS PGRDIA', 'OP3TMA AS INDICE'];
						$laCondiciones = ['TIPTMA'=>'HEMODI', 'CL1TMA'=>'CLASSHM', 'CL2TMA'=>'HMPRDI', 'CL3TMA'=>$laDocumento['pdidia']];
						$laHmprdi = $this ->oDb
											->select($laCampos)
											->from('TABMAEL01')
											->where($laCondiciones)
											->where('CL3TMA', '<>', '')
											->getAll('array');
						if(is_array($laHmprdi))
						{
							if(count($laHmprdi)>0)
							{
								if(count($laHmprdi)>0)
								{
									foreach($laHmprdi as $laDataHmprdi)
									{
										$lcFor = trim($laDataHmprdi['PGRDIA']);
										$laDocumento['lcFor']= ($lcFor == "HM0010" || $lcFor == "HM0011" || $lcFor =="HM0012") ? $lcFor = "HM0010" : $lcFor = $lcFor;

										if($laDocumento['lcFor'] == 'HM0013')
										{
											$aDescrip = array();
											$laDocumento['cTituloHem'] = "HEMODINAMIA CONGENITAS";
											$laDocumento['cTituloInforme'] = "DEPARTAMENTO DE HEMODINAMIA Y CARDIOLOGIA INTERVENCIONISTA";
											$laSetUp = [[10,10, 'N'], [30,5, 'N'], [45,15, 'C'], [70,15, 'C'], [95,5, 'C'], [10,5, 'C'], [25,5, 'C'], [40,5, 'C'], [55,5, 'C'], [70,5, 'C'], ['','','C']];
											foreach($laSetUp as $lnClave=>$Dato)
											{
												$Dato[2] == 'N' ? $aProcedimiento[$lnClave] = 0 : $aProcedimiento[$lnClave] = '';
											}
											$laCondiciones = ['INGHEM'=>$taData['nIngreso'], 'CONHEM'=>$taData['nConsecCita'], 'CUPHEM'=>$taData['cCUP']];
											$laHemodiH013 = $this->oDb
																	->from('HEMODI')
																	->where($laCondiciones)
																	->orderBy('CNLHEM')
																	->getAll('array');
											if(is_array($laHemodiH013))
											{
												if(count($laHemodiH013) > 0 )
												{
													foreach($laHemodiH013 as $DatoslaHem)
													{
														$lnCns = $DatoslaHem['CNLHEM'];
														switch(true){
															case $lnCns == 1 || $lnCns == 2:
																for($lnI=0;$lnI < 5; $lnI++)
																{
																	$lnCns == 1 ? $lnPos = $lnI+0 : $lnPos = $lnI+5;
																	$lcStp = $laSetUp[$lnPos];
																 $aProcedimiento[$lnPos] = trim(substr($DatoslaHem['DESHEM'], $lcStp[0],$lcStp[1]));
																}
																break;

															case $lnCns >= 1001 && $lnCns <= 1100:
																	$aProcedimiento[10] .=  mb_substr($DatoslaHem['DESHEM'], 9,110);

																break;
														}
													}
												}
											}
											$laSetUp = [[10,5, 'C'], [25,5, 'C'], [40,10, 'N'], [60,10, 'N'], [80,40, 'C'], ['','', 'C'], ['','', 'C'], ['','', 'C']];
											$nCalculos = [];
											$cCalculos = [];
											foreach($laSetUp as $lnClave=>$Dato)
											{
												$Dato[2] == 'N' ? $aDescrip[$lnClave] = 0 : $aDescrip[$lnClave] = '';
												$Dato[2] == 'N' ? $cCalculos[$lnClave] = 0 : $cCalculos[$lnClave] = '';
											}

											if(is_array($laHemodiH013))
											{
												if(count($laHemodiH013) > 0 )
												{
													foreach($laHemodiH013 as $DatoslaHem)
													{
														$lnCns=intval($DatoslaHem['CNLHEM']);
														switch(true)
														{
															case $lnCns == 23 :
																for($lnI=0;$lnI < 5; $lnI++)
																{
																	$lcStp = $laSetUp[$lnI];
																	$aDescrip[$lnI] .= trim(substr($DatoslaHem['DESHEM'], $lcStp[0],$lcStp[1]));
																}
																break;

															case $lnCns >= 1101 && $lnCns <= 1200 :
																$aDescrip[5] .= mb_substr($DatoslaHem['DESHEM'], 9,110);
																break;

															case $lnCns == 19:
																$aDescrip[6] = trim($DatoslaHem['DESHEM']);
																if(!empty($aDescrip[6]))
																{
																	$laCondiciones =['TIPTMA'=>'HEMODI', 'CL1TMA'=>'IMGHMPED', 'CL2TMA'=>trim(substr($aDescrip[6],0,8))];
																	$laDescrip = $this->oDb
																				->select('DE2TMA')
																				->from('TABMAE')
																				->where($laCondiciones)
																				->get('array');
																	$aDescrip[7] = trim($laDescrip['DE2TMA']);
																}
																break;

															case $lnCns >= 21 && $lnCns <= 28 :
																$lnCol = (int)(($lnCns-19)/2);
																if ($lnCns % 2 == 1)
																{
																	$lnIni = 1;
																	$lnFin = 10;
																}
																else
																{
																	$lnIni = 11;
																	$lnFin = 13;
																}
																for($lnFila=$lnIni; $lnFila <= $lnFin ; $lnFila++)
																{
																	$nBasal[$lnCol][$lnFila] = substr($DatoslaHem['DESHEM'], 6+11*($lnFila-$lnIni), 5);
																}
																break;

															case $lnCns >= 31 && $lnCns <= 40 :
																$lnCol = (int)(($lnCns-29)/2);
																if ($lnCns % 2 == 1)
																{
																	$lnIni = 1;
																	$lnFin = 10;
																}
																else
																{
																	$lnIni = 11;
																	$lnFin = 13;
																}
																for($lnFila=$lnIni; $lnFila <= $lnFin ; $lnFila++)
																{
																	$nPostHiperoxia[$lnCol][$lnFila] = substr($DatoslaHem['DESHEM'], 6+11*($lnFila-$lnIni), 5);
																}
																break;

															case $lnCns >= 41 && $lnCns <= 50 :
																switch(true)
																{
																	case $lnCns == 41 || $lnCns == 42 || $lnCns == 43:
																		for ($lnI = 1; $lnI < 6; $lnI++)
																		{
																			$nCalculos[$lnCns-40][$lnI] = substr($DatoslaHem['DESHEM'], $lnI*15-5, 5);
																		}
																		break;

																	case $lnCns == 44:
																		$cCalculos[0] = trim(substr($DatoslaHem['DESHEM'], 9, 5));
																		break;
																}
																break;

															case $lnCns >= 1201 && $lnCns <= 1300 :
																$cCalculos[1] .=  mb_substr($DatoslaHem['DESHEM'], 9, 110);
																break;
														}
													}
												}
											}
											$laCondiciones = ['TIPTMA'=>'HEMODI','CL1TMA'=>'CLASSHM', 'CL2TMA'=>'HMDIAP'];
											$hmdiap = $this->oDb
																	->select('DE1TMA AS DESDIA, CL3TMA AS CODDIA')
																	->from('TABMAEL01')
																	->where($laCondiciones)
																	->where('CL3TMA','<>','')
																	->where('ESTTMA','<>',1)
																	->getAll('array');
											if(is_Array($hmdiap))
											{
												if(count($hmdiap) > 0 )
												{
													foreach($hmdiap as $laClave=>$laDesD)
													{
														$hmdiap[$laClave] = array_map('trim', $laDesD);
													}
													$laDocumento['aProcedimiento1'] = AplicacionFunciones::lookup($hmdiap, 'DESDIA', $aProcedimiento[7], 'CODDIA' );
													$laDocumento['aProcedimiento2'] = AplicacionFunciones::lookup($hmdiap, 'DESDIA', $aProcedimiento[8], 'CODDIA' );
												}
											}
											$this->aDocumentoProc = $aProcedimiento;
											$this->aDocumentoDescrip = $aDescrip;
											$this->aDocumentoCalculos = $cCalculos;
										}
										else
										{
											$laDocumento['cTituloHem'] = "Hemodinamia";
											$laDocumento['cTituloInforme'] = "DEPARTAMENTO DE HEMODINAMIA Y CARDIOLOGIA INTERVENCIONISTA";

											switch ($laDataHmprdi['INDICE'])
											{
												case '21':
													$laDocumento['cTituloHem'] = "Hemodinamia Carótidas";
													break;
												case '31':
													$laDocumento['cTituloHem'] = "Hemodinamia Periféricos";
													break;
												case '41':
													$laDocumento['cTituloHem'] = "Hemodinamia Coronarias";
													break;
											}
											$laCondiciones = ['INGANG'=>$taData['nIngreso'], 'CONANG'=>$taData['nConsecCita'], 'PROANG'=>$taData['cCUP']];
											$laAngioH010 = $this->oDb
																	->from('ANGIO')
																	->where($laCondiciones)
																	->between('INDICE', $laDataHmprdi['INDICE'], $laDataHmprdi['INDICE'] + 9)
																	->getAll('array');
											if(is_array($laAngioH010))
											{
												if(count($laAngioH010) > 0)
												{
													foreach($laAngioH010 as $DatoAngio)
													{
														switch(true)
														{
															case $DatoAngio['SUBIND'] == '1':
																$laDocumento['nNumEstudio'] = trim($DatoAngio['AYUANG']);
																$laDocumento['nViaIngreso'] = $DatoAngio['HRFANG'];
																$laDocumento['cAnestesia'] = trim($DatoAngio['TALANG']);
																$laDocumento['cIndicacion'] = trim($DatoAngio['INIMNG']);
																$laDocumento['cViaEntrada'] = trim($DatoAngio['TAMANG']);
																$laDocumento['cComplica'] = trim($DatoAngio['FLUANG']);
																$laDocumento['cComentarios'] = trim($DatoAngio['DSCANG']);
																$laDocumento['cFE'] = trim($DatoAngio['DOSANG']);
																break;
															case $DatoAngio['SUBIND'] > '1':
																$laDocumento['cComentarios'] .= $DatoAngio['DSCANG'];
																break;
														}
													}
													$laDocumento['lcRegistroMedico'] = trim($DatoAngio['REGANG']);
													if(!empty(trim($laDocumento['lcRegistroMedico'])))
													{
														$laCondiciones = ['REGMED'=>$laDocumento['lcRegistroMedico']];
														$lcCodEspe = $this->oDb 
																			 ->select('CODRGM')
																			 ->from('RIARGMN')
																			 ->where($laCondiciones)
																			 ->where('REGMED','<>', '')
																			 ->get('array');
														$laDocumento['lcEspecialidadMedico'] = trim($lcCodEspe['CODRGM']);
													}
													if(!empty($laDocumento['lcEspecialidadMedico']))
													{
														$laCondiciones = ['TIPTMA'=> 'HEMODI', 'CL1TMA'=>'FIRMREP', 'CL2TMA'=>$laDocumento['lcEspecialidadMedico'], 'ESTTMA'=>''];
														$lcTipFor = $this->oDb
																			->select('DE2TMA')
																			->from('TABMAE')
																			->where($laCondiciones)
																			->get('array');
														$laDocumento['cTipoForma'] = trim($lcTipFor['DE2TMA']);
														$laDocumento['cEspecialidad'] = trim($laDocumento['lcEspecialidadMedico']);
														$laDocumento['cRegMedico'] = trim($laDocumento['lcRegistroMedico']);
														
													}
												}
											}
											$laCampos = ['de2tma AS desind', 'cl4tma AS codind'];
											$laCondiciones = ['TIPTMA'=>'HEMODI', 'CL1TMA'=>'CLASSHM', 'CL2TMA'=>'HMINDICA', 'CL3TMA'=>$laDataHmprdi['INDICE']]; // $laDataHmprdi['INDICE'] == nIndiceAngio
											$laHminca = $this->oDb
																->select($laCampos)
																->from('TABMAEL01')
																->where($laCondiciones)
																->where('CL4TMA', '<>', '')
																->where('ESTTMA', '<>', 1)
																->getAll('array');
											if(is_array($laHminca))
											{
												if(count($laHminca) > 0 )
												{
													foreach($laHminca as $laClave=>$laHmin)
													{
														$laHminca[$laClave] = array_map('trim', $laHmin);
													}
														$laDocumento['lcIndicacion'] = AplicacionFunciones::lookup($laHminca, 'DESIND', $laDocumento['cIndicacion'], 'CODIND' );
														$laDocumento['lnIndice'] = $laDataHmprdi['INDICE'];
												}
											}
											$laCondiciones =['TIPTMA'=>'LIBROHC', 'CL1TMA'=>'FIRMA', 'CL2TMA'=>'HEMODI', 'ESTTMA'=>''];
											$laFirmas = $this->oDb
														->select('DE1TMA')
														->from('TABMAE')
														->where($laCondiciones)
														->where($taData['oCitaProc']->nFechaRealiza.' BETWEEN OP4TMA AND OP7TMA')
														->get('array');
											$laFirmas = array_map('trim', $laFirmas);
											$laDocumento['lcFirmas'] = $laFirmas['DE1TMA'];
										}
										$laCondiciones = ['TIDMOR'=>$taData['cTipDocPac'], 'NIDMOR'=>$taData['nNumDocPac'], 'INGMOR'=>$taData['nIngreso']];
										$laRiamur = $this->oDb
																	->from('RIAMUR')
																	->where($laCondiciones)
																	->getAll('array');
										if(is_array($laRiamur))
										{
											if(count($laRiamur) > 0)
											{
												foreach($laRiamur as $laRiam)
												{
													$laDocumento['pfafdf'] = $laRiam['FDFMOR'] ." ". $laRiam['HDFMOR'];
													$laDocumento['pfadho'] = $laRiam['NDIMOR'] ;
													$laDocumento['pfahho'] = $laRiam['NHRMOR'] ;
													$laDocumento['pfadg1'] = $laRiam['DG1MOR'] =='0' ? ' ' :	$laRiam['DG1MOR'] ;
													$laDocumento['pfadg2'] = $laRiam['DG2MOR'] =='0' ? ' ' :	$laRiam['DG2MOR'] ;
													$laDocumento['pfadg3'] = $laRiam['DG3MOR'] =='0' ? ' ' :	$laRiam['DG3MOR'] ;
													$laDocumento['pfade1'] = $laRiam['DE1MOR'] =='0' ? ' ' :	$laRiam['DE1MOR'] ;
													$laDocumento['pfade2'] = $laRiam['DE2MOR'] =='0' ? ' ' :	$laRiam['DE2MOR'] ;
													$laDocumento['pfade3'] = $laRiam['DE3MOR'] =='0' ? ' ' :	$laRiam['DE3MOR'] ;
													$laDocumento['pfacm1'] = $laRiam['CM1MOR'] =='0' ? ' ' :	$laRiam['CM1MOR'] ;
													$laDocumento['pfacm2'] = $laRiam['CM2MOR'] =='0' ? ' ' :	$laRiam['CM2MOR'] ;
													$laDocumento['pfacca'] = $laRiam['CCRMOR'];
													$laDocumento['pfacnc'] = $laRiam['CLSMOR'] == 'S' ? 1 : 0;
													$laDocumento['pfadlg'] = $laRiam['DLGMOR'];
													$laDocumento['pfamdl'] = $laRiam['MDLMOR'];
													$laDocumento['pfafir'] = $laRiam['FIRMOR'];
													$laDocumento['pfance'] = $laRiam['NCRMOR'];
													$laDocumento['pfaaut'] = $laRiam['AUPMOR'] == 'S' ? 1 : 0;
													$laDocumento['pfaclm'] = $laRiam['CLMMOR'];
													$laDocumento['pfadpt'] = $laRiam['DPTMOR'] == '0' ? '' : $laRiamur['dptmor'] ;
													$laDocumento['pfaobs'] .= $laRiam['DESMOR'];
												}
											}
										}
										$laCondiciones =['INGAPS'=>$taData['nIngreso'], 'CSCAPS'=>$taData['nConsecCita']];
										$laRipaps = $this->oDb
																	->from('RIPAPS')
																	->where($laCondiciones)
																	->getAll('array');
										if(is_array($laRipaps))
										{
											if(count($laRipaps) > 0 )
											{
												foreach($laRipaps as $laRipa)
												{
													$laDocumento['nFinalidad'] = empty($laRipa['FPRAPS']) ? 2 : $laRipa['FPRAPS'];
													$laDocumento['cDxPrin'] = trim($laRipa['DG1APS']) == '0' ?  ' ' : trim($laRipa['DG1APS']);
													$laDocumento['cDxRel'] = trim($laRipa['DG2APS']) == '0' ? ' ' : trim($laRipa['DG2APS']);
													$laDocumento['cDxComplica'] = trim($laRipa['DGCAPS']) == '0' ?  ' ' : trim($laRipa['DGCAPS']);
													$laDocumento['cFormaActoQx'] = trim($laRipa['FAQAPS']);
													$laDocumento['cNumAutoriza'] = trim($laRipa['AUTAPS']);
												}
											}
										}
									}
								}
							}
						}
					}
					if(!empty($laDocumento['pdimho']))
					{
						$laCampos = ['DE1TMA AS DESHOS', 'SUBSTR(CL2TMA,1,2) AS CODHOS'];
						$laCondiciones = ['TIPTMA' => 'HEMODI', 'CL1TMA'=>'HMHOSP', 'SUBSTR(CL2TMA,1,2)'=>$laDocumento['pdimho']];
						$laHmhosp = $this->oDb
											->select($laCampos)
											->from('TABMAEL01')
											->where($laCondiciones)
											->where('CL2TMA','<>','')
											->where('ESTTMA', '<>',1)
											->getAll('array');
						if(is_array($laHmhosp))
						{
							if(count($laHmhosp) > 0)
							{
								foreach($laHmhosp as $laClave=>$laHms)
								{
									$laHmhosp[$laClave] = array_map('trim', $laHms);
								}
							}
						}
					}
					if(empty($laDocumento['pdimed']))
					{
						$laCondiciones = ['INGANG'=>$taData['nIngreso'], 'CONANG'=>$taData['nConsecCita'], 'PROANG'=>$taData['cCUP'], 'SUBIND'=>1];
						$laAngioH008t = $this->oDb
												->from('ANGIO')
												->where($laCondiciones)
												->where('INDICE','>',11)
												->getAll('array');
						if(is_array($laAngioH008t))
						{
							if(count($laAngioH008t) > 0)
							{
								foreach ($laAngioH008t as $laClave => $laAngt)
								{
									$laAngioH008t[$laClave] = array_map('trim', $laAngt);
									$laDocumento['pdimed'] = $laAngioH008t[$laClave]['REGANG'];
								}
							}
						}
					}
					$laCampos = ["TRIM(NOMMED)||' '|| NNOMED AS NOMMED", 'REGMED AS REGMED', 'CODRGM AS ESPMED', 'ESTRGM AS ESTMED', 'NIDRGM AS NIDMED', 'USUARI AS USUMED', 'CHAR(TPMRGM) AS TPMMED'];
					$laCondiciones = ['ESTRGM'=>1,  'REGMED'=> $taData['cRegMedico']];
					$lcRegmed =$this->oDb
										->select($laCampos)
										->from('RIARGMN3')
										->where($laCondiciones)
										->in('TPMRGM',[1, 3, 4, 5, 6, 10, 12, 13, 16, 18])
										->getAll('array');
					if(is_array($lcRegmed))
					{
						if(count($lcRegmed) > 0)
						{
							foreach($lcRegmed as $laClave=>$lcReg)
							{
								$lcRegmed[$laClave] = array_map('trim', $lcReg);
							}
						}
					}
					$laCampos = ['CL1TMA AS CODCUP', 'SUBSTR(DE1TMA,1,13) AS REGMED1', 'SUBSTR(DE1TMA,15,13) AS REGMED2', 'SUBSTR(DE1TMA,29,13) AS REGMED3', 'SUBSTR(DE1TMA,43,13) AS REGMED4', 'SUBSTR(DE1TMA,57,13) AS REGMED5'];
					$laCondiciones = ['TIPTMA'=> 'MEDELEC'];
					$laMedelec =$this->oDb
										->select($laCampos)
										->from('TABMAEL01')
										->where($laCondiciones)
										->where('ESTTMA', '<>',1)
										->getAll('array');
					if(is_array($laMedelec))
					{
						if(count($laMedelec) > 0)
						{
							foreach ($laMedelec as $laClave => $laMed)
							{
								$laMedelec[$laClave] = array_map('trim', $laMed);
							}
						}
					}
					$laCampos = ['DESESP', 'CODESP', "CASE WHEN UBIESP <> ' ' THEN CAST(TRANSLATE(UBIESP) AS DECIMAL(2 , 0)) ELSE 0 END AS ESTESP"];
					$lcIniespe = $this->oDb
										->select($laCampos)
										->from('RIAESPE')
										->getAll('array');
					if(is_Array($lcIniespe))
					{
						if(count($lcIniespe) > 0)
						{
							foreach($lcIniespe as $laClave=>$lcInie)
							{
								$lcIniespe[$laClave] = array_map('trim', $lcInie);
							}
						}
					}
					$laCampos = ['SUBSTR(TRIM(DESRIP),1,160) AS DESRIP', 'ENFRIP', 'EDMRIP', 'EDNRIP', 'SEXRIP', '000000 AS ANTCOD', '10 AS CIE'];
					$lcInidiag = $this->oDb
										->select($laCampos)
										->from('RIACIEL1')
										->getAll('array');
					if(is_array($lcInidiag))
					{
						if(count($lcInidiag) > 0 )
						{
							foreach($lcInidiag as $laClave=>$lcInid)
							{
								$lcInidiag[$laClave] = array_map('trim', $lcInid);
							}
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
		$laTr = [];

		if($this->aDocumento['lcFor'] == 'HM0013')
		{
			if(!(empty($this->aDocumentoProc[7]) && empty($this->aDocumentoProc[8])))
			{
				$laTr[] = ['titulo1', 'GENERAL', 'L'];
				if(!empty($this->aDocumentoProc[7]))
				{
					$laTr[] = ['texto9', 'Dx. Principal:  ' . $this->aDocumento['aProcedimiento1']];
				}
				if(!empty($this->aDocumentoProc[8]))
				{
					$laTr[] = ['texto9', 'Dx. Secundario: ' .  $this->aDocumento['aProcedimiento2']];
				}
			}
			if(!empty($taData['oCup']->cCup))
			{
				$laTr[] = ['titulo1', 'PROCEDIMIENTO', 'L'];
				$laTr[] = ['texto9', $taData['oCup']->cDscrCup];
			}
			if(!empty($this->aDocumentoProc[10]))
			{
				$laTr[] = ['titulo1', 'OBSERVACIONES'];
				$laTr[] = ['texto9', trim($this->aDocumentoProc[10])];
			}
			if(!empty($this->aDocumentoDescrip[5]))
			{
				$laTr[] = ['titulo1', 'TECNICA', 'L'];
				$laTr[] = ['texto9', trim($this->aDocumentoDescrip[5])];
			}
			if(!empty($this->aDocumentoCalculos[1]))
			{
				$laTr[] = ['titulo1', 'CONCLUSIONES'];
				$laTr[] = ['texto9', trim($this->aDocumentoCalculos[1])];
			}
		}	
		if($this->aDocumento['lcFor'] == 'HM0010')
		{
			if(!empty($taData['oCup']->cCup))
			{
					$laTr[] = ['titulo1', 'PROCEDIMIENTO', 'L'];
					$laTr[] = ['texto9', $taData['oCup']->cDscrCup];
			}
			if(!empty($this->aDocumento['lcIndicacion']))
			{
				$laTr[] = ['titulo1', 'INDICACION', 'L'];
				$laTr[] = ['texto9', $this->aDocumento['lcIndicacion']];
			}
			if($this->aDocumento['lnIndice'] == 41 AND !empty($this->aDocumento['cFE']))
			{
				$laTr[] = ['txthtml9', '<b>FE: </b>' . $this->aDocumento['cFE']];
			}
			if(!empty($this->aDocumento['cComentarios']))
			{
				$laTr[] = ['titulo1', 'COMENTARIOS', 'L'];
				$laTr[] = ['texto9', $this->aDocumento['cComentarios']];
			}		
		}		
		$laTr[] = ['saltol', 10];
		if($this->aDocumento['lcFor'] == 'HM0010' && $this->aDocumento['cTipoForma'] == 'A')
		{
			$laTr[] = ['titulo1', $this->aDocumento['lcFirmas'], 'C'];
		}
		else
		{
			$laTr[]=['firmas', [
				['registro' => $this->aDocumento['pdimed'],'prenombre'=>'Dr. ']]];
		}
		$this->aReporte['aCuerpo'] = $laTr;
		$this->aReporte['cTxtLuegoDeCup'] = 'No.Estudio:' . ' ' . (($this->aDocumento['lcFor'] == 'HM0013') ? $this->aDocumentoProc[0] : $this->aDocumento['nNumEstudio']);
		$this->aReporte['cTitulo'] = $this->aDocumento['cTituloHem'] . $lcSL. $this->aDocumento['cTituloInforme'];
	}

	private function datosBlanco()
	{
		return [

			'cTipoForma' => '',
			'lcRegistroMedico' => '',
			'lcEspecialidadMedico' => '',
			'cEspecialidad' => '',
			'cRegMedico' => '',
			'lcFor' => '',
			'pdimed' => '',
			'lcFirmas' => '',
			'pdidia'	=> '',
			'pdimho'	=> '',
			'cTituloHem' => '',
			'cTituloInforme' => '',
			'nNumEstudio' => 0,
			'nViaIngreso' => 0,
			'cAnestesia' => '',
			'cIndicacion' => '',
			'lcIndicacion' => '',
			'lnIndice' => 0,
			'aProcedimiento1' => '',
			'aProcedimiento2' => '',
			'cViaEntrada' => '',
			'cComplica' => '',
			'cFE' => '',
			'nFinalidad' => 0,
			'cDxPrin' => '',
			'cDxRel' => '',
			'cDxComplica' => '',
			'cFormaActoQx' => '',
			'cNumAutoriza' => '',
			'cComentarios' => '',
			'pfafdf' => '',
			'pfadho' => '',
			'pfahho' => '',
			'pfadg1' => '',
			'pfadg2' => '',
			'pfadg3' => '',
			'pfade1' => '',
			'pfade2' => '',
			'pfade3' => '',
			'pfacm1' => '',
			'pfacm2' => '',
			'pfacca' => '',
			'pfacnc' => '',
			'pfadlg' => '',
			'pfamdl' => '',
			'pfafir' => '',
			'pfance' => '',
			'pfaaut' => '',
			'pfaclm' => '',
			'pfadpt' => '',
			'pfaobs' => '',
		];
	}
}