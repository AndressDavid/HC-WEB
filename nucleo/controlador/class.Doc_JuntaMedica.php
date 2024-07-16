<?php
namespace NUCLEO;

class Doc_JuntaMedica
{
	protected $oDb;
	protected $aDocumento = [];
	protected $aReporte = [
					'cTitulo' => 'FORMATO DE JUNTA MEDICA',
					'lMostrarFechaRealizado' => true,
					'lMostrarViaCama' => true	,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => '',
					'cTxtLuegoDeCup' => '',
					'aCuerpo' => [],
					'aNotas' => ['notas'=>true,],
				];
	protected $lTituloVacios=false;
	protected $cTituloVacios='';


	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}


	public function retornarDocumento($taData, $tlConsultaEpi=false)
	{
		$this->consultarDatos($taData);
		if($tlConsultaEpi){
			$laDatos = ['Participantes' => $this->adparjunm,
						'Junta' => $this->aDocumento];
			return $laDatos;
		}else{
			$this->prepararInforme($taData);
			return $this->aReporte;
		}
	}

	private function consultarDatos($taData)
	{
		$laDocumento = $this->datosBlanco();
		$ladparjunm=[];

		//DATOSJUNTA
		$laCampos = ['J.CNIJUN', 'J.DESJUN', 'J.MRJJUN', 'J.FJUJUN', 'J.HJUJUN', 'J.RPRJUN', 'J.CUPJUN', 'J.CCIJUN', 
					'MC.NOMMED AS NOMMEDCRE', 'MC.NNOMED AS NNOMEDCRE', 'M.NOMMED', 'M.NNOMED', 'O.CODORD', 'M.TPMRGM', 'E.DESESP',
					'TRIM(SUBSTR(J.CA3JUN, 1, 4)) CIEPRINCIPAL, TRIM(SUBSTR(TRIM(D.DE2RIP), 1, 120)) DESCCIEPRINCIPAL',
					'TRIM(SUBSTR(J.CA3JUN, 11, 1)) TIPOCIEPRINCIPAL, TRIM(D1.TABDSC) TIPODESCCIEPRINCIPAL',
					'TRIM(SUBSTR(J.CA3JUN, 14, 2)) FINALIDAD, TRIM(D2.DE2TMA) DESCRIPFINALIDAD'];
		$laCondiciones = ['INGJUN'=>$taData['nIngreso'], 'CJUJUN'=>$taData['nConsecCons']];
		$lcJunta = $this->oDb
						->select($laCampos)
						->from('RIAJUNL02 AS J')
						->leftJoin('RIARGMN5 AS MC', 'J.MRJJUN=MC.REGMED', null)
						->leftJoin('RIARGMN5 AS M', 'J.RPRJUN=M.REGMED', null)
						->leftJoin('RIACIE AS D', 'TRIM(SUBSTR(J.CA3JUN, 1, 4))=TRIM(D.ENFRIP)', null)
						->leftJoin('PRMTAB AS D1', "TABTIP='TDX' AND 'B'|| '' ||TRIM(SUBSTR(J.CA3JUN, 11, 1))=TRIM(D1.TABCOD)", null)
						->leftJoin('TABMAE AS D2', "TIPTMA='CODFIN' AND TRIM(SUBSTR(J.CA3JUN, 14, 2))=TRIM(D2.CL1TMA)", null)
						->leftJoin('RIAORD AS O', 'J.INGJUN=O.NINORD AND J.CJUJUN=O.CCOORD AND J.CCIJUN=O.CCIORD AND J.RPRJUN=O.RMEORD', null)
						->leftJoin('RIAESPEL01 	AS E', 'O.CODORD=E.CODESP', null)
						->where($laCondiciones )
						->orderBy('J.CNIJUN,J.CNLJUN')
						->getAll('array');

		if(is_array($lcJunta))
		{
			if(count($lcJunta)>0)
			{
				$laDocumento['luRegmed']	= trim($lcJunta[0]['MRJJUN']);
				$laDocumento['lfJupjm']		= $lcJunta[0]['FJUJUN'];
				$laDocumento['lhJupjm']		= $lcJunta[0]['HJUJUN'];
				$laDocumento['lnRegMedico']	= trim($lcJunta[0]['MRJJUN']);

				//PARTICIPANTES
				foreach($lcJunta as $laData)
				{
					$laData = array_map('trim',$laData);
					switch(true)
					{
						case $laData['CNIJUN'] == 1:
							$laDocumento['lsRepr'] = $laData['RPRJUN'];
							$laDocumento['lcNome'] = is_null($laData['NOMMED']) ? '' : $laData['NOMMED'].' '. $laData['NNOMED'];
							$laDocumento['lcEspe'] = $laData['CODORD'] ?? '';
							$laDocumento['lcTius'] = $laData['TPMRGM'] ?? 0;
							$laDocumento['lcDees'] = substr($laData['DESESP'] ?? '',0,60);
							$laDocumento['lcDiagnosticoPrincipal'] = (is_null($laData['CIEPRINCIPAL']) || empty($laData['CIEPRINCIPAL'])) ? '' :$laData['CIEPRINCIPAL'].'-'.$laData['DESCCIEPRINCIPAL'];
							$laDocumento['lcTipoDiagnosticoPrincipal'] = (is_null($laData['TIPODESCCIEPRINCIPAL']) || empty($laData['TIPODESCCIEPRINCIPAL'])) ? '' :$laData['TIPODESCCIEPRINCIPAL'];
							$laDocumento['lcFinalidadJunta'] = (is_null($laData['DESCRIPFINALIDAD']) || empty($laData['DESCRIPFINALIDAD'])) ? '' :$laData['DESCRIPFINALIDAD'];
							
							if($laData['MRJJUN'] == $laData['RPRJUN']){
								$laDocumento['lnRegMedico'] = $laDocumento['lsRepr'];
								$laDocumento['lcNombreMedico'] = $laDocumento['lcNome'];
								$laDocumento['lnCodEspMedico'] = $laDocumento['lcEspe'];
								$laDocumento['lcEspecialidadMedico'] = $laDocumento['lcDees'];
								$laDocumento['lnConCit'] = $laData['CCIJUN'];
								$laDocumento['lcCodPro'] = $laData['CUPJUN'];
							}else{
								$ladparjunm[] = [
									'selecc' => 0,
									'rmeunm' => $laDocumento['lsRepr'],
									'tusunm' => $laDocumento['lcTius'],
									'nmeunm' => $laDocumento['lcNome'],
									'espunm' => $laDocumento['lcEspe'],
									'desunm' => $laDocumento['lcDees'],
								];
							}
							break;
						case $laData['CNIJUN'] == 2:
							$laDocumento['lcMotpjm'] .=  $laData['DESJUN'];
							break;
						case $laData['CNIJUN'] == 3:
							$laDocumento['lcDispjm'] .= $laData['DESJUN'];
							break;
						case $laData['CNIJUN'] == 4:
							$laDocumento['lcConpjm'] .= $laData['DESJUN'];
							break;
					}
				}
				$laDocumento['lcMotpjm'] = trim	($laDocumento['lcMotpjm']);
				$laDocumento['lcDispjm'] = trim($laDocumento['lcDispjm']);
				$laDocumento['lcConpjm'] = trim($laDocumento['lcConpjm']);
				if(!is_null($lcJunta[0]['NOMMEDCRE']))
				{
					$laDocumento['luNommed'] = trim($lcJunta[0]['NOMMEDCRE']).' '.trim($lcJunta[0]['NNOMEDCRE']);
				}
			}
		}
		$this->adparjunm = $ladparjunm;
		$this->aDocumento = $laDocumento;
	}

	private function prepararInforme($taData)
	{
		$lnAnchoPagina = 90;
		$lcSL = PHP_EOL;
		$cVacios = $this->cTituloVacios;

		// Cuerpo
		$laW = [68,68];
		$lcEi = '<span style="font-weight:bold;font-size:10px;">'; //'<b>';
		$lcEf = '</span>'; //'</b>';
		$laFilas = [ ['w'=>$laW,'a'=>'C','d'=>["$lcEi PARTICIPANTE $lcEf","$lcEi ESPECIALIDAD $lcEf"] ] ];
		$laParticipa = [];
		foreach ($this->adparjunm as $lnIndice=> $lcValor){
			$laFilas[] = [
				'w'=>$laW, 'a'=>'L',
				'd'=>[
					trim(substr($lcValor['nmeunm'],0, 34)),
					trim(substr($lcValor['desunm'],0, 34)),
				]
			];
			$laParticipa[]=['registro'=>$lcValor['rmeunm'], 'codespecialidad'=>$lcValor['espunm']];
		}
		$laTr['aCuerpo'][] = ['tablaSL', [], $laFilas ];

		if(!empty($this->aDocumento['lcDiagnosticoPrincipal']))
		{
			$laTr['aCuerpo'][] = ['titulo1', 'DIAGNÓSTICO PRINCIPAL', 'L'];
			$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['lcDiagnosticoPrincipal']];
		}	
		
		if(!empty($this->aDocumento['lcTipoDiagnosticoPrincipal']))
		{
			$laTr['aCuerpo'][] = ['titulo1', 'TIPO DIAGNÓSTICO PRINCIPAL', 'L'];
			$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['lcTipoDiagnosticoPrincipal']];
		}	

		if(!empty($this->aDocumento['lcFinalidadJunta']))
		{
			$laTr['aCuerpo'][] = ['titulo1', 'Finalidad', 'L'];
			$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['lcFinalidadJunta']];
		}	

		if(!empty($this->aDocumento['lcMotpjm']))
		{
			$laTr['aCuerpo'][] = ['titulo1', 'MOTIVO JUNTA', 'L'];
			$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['lcMotpjm']];
		}
		if(!empty($this->aDocumento['lcDispjm']))
		{
			$laTr['aCuerpo'][] = ['titulo1', 'DISCUSION DEL CASO CLINICO', 'L'];
			$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['lcDispjm']];
		}
		if(!empty($this->aDocumento['lcConpjm']))
		{
			$laTr['aCuerpo'][] = ['titulo1', 'CONCLUSIONES', 'L'];
			$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['lcConpjm']];
		}

		// Participantes
		$laTr['aCuerpo'][]=['titulo2', 'Participantes', 'L'];
		$laTr['aCuerpo'][]=['firmas', $laParticipa];

		// Realizado por
		$laTr['aCuerpo'][]=['titulo2', 'Realizado Por', 'L'];
		$laTr['aCuerpo'][]=['firmas', [['registro'=> $this->aDocumento['lnRegMedico'], 'prenombre'=>'Dr. ', 'codespecialidad'=>$this->aDocumento['lcEspe']],]];

		$this->aReporte = array_merge($this->aReporte, $laTr);
	}


	private function datosBlanco()
	{
		return [
			'lcTipIde' => '',
			'lnNroIde' => 0,
			'lnCodEnt' => 0,
			'lcCodPla' => '',
			'lcCodVia' => '',
			'lcNomPac' => '',
			'lnNroHcl' => 0,
			'lcSexPac' => '',
			'lnFecNac' => 0,
			'lcHabita' => '',
			'lcSecCam' => '',
			'lcNroCam' => '',
			'luRegmed' => '',
			'lfJupjm' => '',
			'lhJupjm' => '',
			'lsRepr' => '',
			'lcNome' => '',
			'lcEspe' => '',
			'lcTius' => '',
			'lcDees' => '',
			'lnRegMedico' => 0,
			'lcNombreMedico' => '',
			'lnCodEspMedico' => 0,
			'lcEspecialidadMedico' => '',
			'lcMotpjm' => '',
			'lcDispjm' => '',
			'lcConpjm' => '',
			'luNommed' => '',
			'lnConCit' => 0,
			'lcCodPro' => 0,
			'lcDiagnosticoPrincipal' => '',
			'lcTipoDiagnosticoPrincipal' => '',
			'lcFinalidadJunta' => '',
		];
	}
}