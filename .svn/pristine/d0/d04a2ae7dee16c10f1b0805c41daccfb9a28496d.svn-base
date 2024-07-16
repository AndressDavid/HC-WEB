<?php

namespace NUCLEO;

require_once __DIR__ .'/class.Db.php';

use NUCLEO\Db;

class EscalasRiesgoSangrado
{
	protected $oDb = null;
	public $aElemHasbled = [];
	public $aElemChadsvas = [];
	public $aElemCrusade = [];
	public $aInterHasbled = [];
	public $aInterChadsvas = [];
	public $aError = [
		'Mensaje' => '',
		'Objeto' => '',
		'Valido' => true,
	];


	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}


	public function validarEscalaHasbled($taDiagnosticos=[], $taDatos=[])
	{
		$laHasbled = $this->oDb
			->select('CL2TMA, CL3TMA, DE2TMA, OP3TMA, OP4TMA, OP5TMA, OP6TMA, OP7TMA')
			->from('TABMAE')
			->where(['TIPTMA'=>'BLDSCORE', 'CL1TMA'=>'HASBLED'])
			->in('CL2TMA', ['ELEM','DX'])
			->getAll('array');
		if(is_array($laHasbled)){
			if(count($laHasbled) > 0){
				$lnEdadMinima = 20;
				foreach($laHasbled as $lnIndice => $laRegistro){
					if (trim($laRegistro['CL2TMA'])=='ELEM'){
						$laLetrasHasbled[] = [
							'OP6TMA'=>trim($laRegistro['OP6TMA']),
							'DE2TMA'=>trim($laRegistro['DE2TMA']),
						];
					}
					if (trim($laRegistro['CL2TMA'])=='DX'){
						$lcLstDxHasbled = trim(str_replace('\'', '', $laRegistro['DE2TMA'].$laRegistro['OP5TMA']));
						$lnEdadMinima = empty($laRegistro['OP3TMA']) ? $lnEdadMinima : $laRegistro['OP3TMA'];
					}
				}
				if(count($taDiagnosticos) > 0){
					$lcLstDiagnosticos = array_column($taDiagnosticos, 'CODIGO');
					if(in_array($lcLstDxHasbled, $lcLstDiagnosticos)){
						$lnCantaDatos = count($taDatos);
						$lnCanHasbled = count($laLetrasHasbled);
						if($lnCanHasbled > 0 && $lnCantaDatos > 0){
							if($lnCanHasbled != $lnCantaDatos){
								$this->aError = [
									'Mensaje' =>'Número incorrecto de valores HASBLED',
									'Objeto'  => 'cboSiNoeshas0',
									'Valido'=>false,
								];
							}else{
								foreach($laLetrasHasbled as $lnIndice => $laRegistro){
									$lcLetrasHasbled[$lnIndice] = trim($laRegistro['OP6TMA']);
									$lcDscHasbled[$lnIndice] = trim($laRegistro['DE2TMA']);
								}
								foreach($taDatos as $lnIndice => $laElemento){
									$lcLetrastaDatos[$lnIndice] = trim($laElemento['lcLetra']);
									$lnCanLetrasDatos = count($lcLetrastaDatos);
									$lnCantUniLetrasDatos = count(array_unique($lcLetrastaDatos));

									switch(true){

										case $laElemento['lnEdad'] < $lnEdadMinima :
											$this->aError = [
												'Mensaje' =>'Edad menor a la minima requerida para Puntaje HASBLED',
												'Objeto'  => 'cboSiNoeshas0',
												'Valido'=>false,
											];
											break;

										case !in_array($laElemento['lcLetra'], $lcLetrasHasbled):
											$this->aError = [
												'Mensaje' => 'Letra no corresponde',
												'Objeto'  => 'cboSiNoeshas'.$lnIndice,
												'Valido'  => false,
											];
											break 2;

										case !in_array(intval($laElemento['lnValor']), [0,1]) :
											$this->aError = [
												'Mensaje'=>'Valor no corresponde',
												'Objeto'=>'cboSiNoeshas'.$lnIndice,
												'Valido'=>false,];
											break 2;

										case $laElemento['lcLetra'] == 'E':
											if(($laElemento['lnEdad'] > 65 && $laElemento['lnValor'] != 1) || ($laElemento['lnEdad'] < 65 &&  $laElemento['lnValor'] != 0))	{
											$this->aError = [
												'Mensaje'=>$lcDscHasbled[$lnIndice] . ', Valor no corresponde a la edad',
												'Objeto'=>'cboSiNoeshas'.$lnIndice,
												'Valido'=>false,];
											}
											break 2;

										case $laElemento['lnValor'] == '' :
											$this->aError = [
												'Mensaje'=>$lcDscHasbled[$lnIndice] . ' debe ser valorado',
												'Objeto'=>'cboSiNoeshas'.$lnIndice,
												'Valido'=>false,
											];
											break 2;

										case $lnCanLetrasDatos > $lnCantUniLetrasDatos :
											$this->aError = [
												'Mensaje' => 'Letra Repetida',
												'Objeto'  => 'cboSiNoeshas'.$lnIndice,
												'Valido'  => false,
											];
											break 2;
									}
								}
							}
						}else{
							$this->aError = [
								'Mensaje' =>'Faltan datos para valorar la escala HASBLED',
								'Objeto'  => 'cboSiNoeshas0',
								'Valido'=>false,
							];
						}
					}else{
						$this->aError = [
							'Mensaje' =>'No existe parametrización de Dx que requieren puntaje HASBLED',
							'Objeto'  => 'cboSiNoeshas0',
							'Valido'=>true,
						];
					}
				}else{
					$this->aError = [
						'Mensaje' =>'No existen Diagnosticos',
						'Objeto'  => 'cboSiNoeshas0',
						'Valido'=>false,
					];
				}
			}
		}
		return $this->aError;
	}


	public function cargarDatosEscHas()
	{
		$laElemHasbled = $this->oDb
			->select('CL2TMA, CL3TMA, DE2TMA, OP3TMA, OP4TMA, OP5TMA, OP6TMA, OP7TMA')
			->from('TABMAE')
			->where(['TIPTMA'=>'BLDSCORE', 'CL1TMA'=>'HASBLED', ])
			->orderBy('CL2TMA, CL3TMA')
			->getAll('array');
		if(is_array($laElemHasbled)){
			if(count($laElemHasbled)>0){
				foreach($laElemHasbled as $lcClave=>$itemHasbled ){
					$laElemHasbled[$lcClave] = array_map('trim', $itemHasbled);
				}
				$this->aElemHasbled = $laElemHasbled;
				return $laElemHasbled;
			}
		}
	}


	public function interpretarEscHas()
	{
		$laInterHasbled = $this->oDb
			->select('CL3TMA, DE2TMA, OP3TMA, OP4TMA, OP5TMA, OP7TMA')
			->from('TABMAE')
			->where(['TIPTMA'=>'BLDSCORE', 'CL1TMA'=>'HASBLED', 'CL2TMA'=>'INTERP'])
			->getAll('array');
		if(is_array($laInterHasbled)){
			if(count($laInterHasbled)>0){
				foreach($laInterHasbled as $lcClave=>$itemInterHasbled ){
					$laInterHasbled[$lcClave] = array_map('trim', $itemInterHasbled);
				}
				$this->aInterHasbled = $laInterHasbled;
				return $laInterHasbled;
			}
		}
	}

	public function guardarDatosEsHasbledHC($taDatos=[], $tnIngreso=0, $tcCodiVia='', $tnConCon=1, $tnConEvo=0, $tnEntidad=0, $tnTidhis=0, $tnIdenti=0, $tcFecCre='', $tcHorCre='', $tcUsuCre='', $tcPrgCre='', $tlRequiereAval=false)
	{
		$lcTexto = 'Puntaje HASBLED: ' . str_repeat(' ', 3) . $taDatos[0]['lnPuntaje'] . str_repeat(' ',4) . $taDatos[0]['lcInterpretacion'];
		$lcHasbled ='';
		$lcSepara ='';
		foreach ($taDatos as $laRegistro){
			$lcHasbled .= $lcSepara . $laRegistro['lcLetra'] . '=' . ($laRegistro['lcCboValor']=='SI'?'Si':'No');
			$lcSepara = ';';
		}
		if($tlRequiereAval){
			if($tcPrgCre=='HCPPALWEB'){
				$laDatosEscala= ['Indice'=> 35,
								  'SubIndice'=> 1,
								  'Codigo'=> 0,
								  'Linea'=> 1,
								];
			}else{
				$laDatosEscala= ['Indice'=> 30,
								  'SubIndice'=> 5951,
								  'Codigo'=> 1,
								  'Linea'=> 1,
								];
			}
			$this->GuardarEscalaAval($taDatos, $tnIngreso, $tcCodiVia, $tnConCon, $tnConEvo, $tnEntidad, $tnTidhis, $tnIdenti, $tcFecCre, $tcHorCre, $tcUsuCre, $tcPrgCre, $tlRequiereAval, $lcTexto, $lcHasbled, $laDatosEscala);
		}else{
			$lcTabla = 'ESCHCL';
			$lcTipo = ($tcCodiVia=='01'?'HCURG':($tcCodiVia=='02'?'HCCEX':'HCHOS'));
			$laDatosESCHCL = [
				'INGEHC' => $tnIngreso,
				'TIPEHC' => $lcTipo,
				'CHCEHC' => $tnConCon,
				'CEVEHC' => $tnConEvo,
				'INDEHC' => 1,
				'IN2EHC' => 1,
				'DESEHC' => $lcHasbled,
				'OP5EHC' =>	$lcTexto,
				'USREHC' => $tcUsuCre,
				'PGMEHC' => $tcPrgCre,
				'FECEHC' => $tcFecCre,
				'HOREHC' => $tcHorCre,
			];
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatosESCHCL);
		}
	}

	public function validarEscalaChadsvas($taDiagnosticos=[], $taDatos=[])
	{
		$laChadsvas = $this->oDb
			->select('CL2TMA, CL3TMA, DE2TMA, OP3TMA, OP4TMA, OP5TMA, OP6TMA, OP7TMA')
			->from('TABMAE')
			->where(['TIPTMA'=>'BLDSCORE', 'CL1TMA'=>'CHADSVAS'])
			->getAll('array');
		if($this->oDb->numRows()>0){
			$lnEdadMinima = 20;
			foreach($laChadsvas as $lnIndice => $laRegistro){
				if (trim($laRegistro['CL2TMA'])=='ELEM'){
					$laLetrasChadsvas[] = [
						'OP6TMA'=>trim($laRegistro['OP6TMA']),
						'DE2TMA'=>trim($laRegistro['DE2TMA']),
					];
				}else if (trim($laRegistro['CL2TMA'])=='DX'){
					$lcLstDxChadsvas = trim(str_replace('\'', '', $laRegistro['DE2TMA'].$laRegistro['OP5TMA']));
					$lnEdadMinima = !empty($laRegistro['OP3TMA']) ? $laRegistro['OP3TMA'] : $lnEdadMinima;
				}
			}
			if(count($taDiagnosticos)>0){
				foreach($taDiagnosticos as $lnIndice => $laDiagnostico){
					$lcLstDiagnosticos[] = $laDiagnostico['CODIGO'];
				}
				if(in_array($lcLstDxChadsvas, $lcLstDiagnosticos)){
					$lnCantaDatos = count($taDatos);
					$lnCanChadsvas = count($laLetrasChadsvas);
					if($lnCanChadsvas>0 && $lnCantaDatos>0){
						if($lnCanChadsvas != $lnCantaDatos){
							$this->aError = [
								'Mensaje' =>'Número incorrecto de valores CHADSVAS',
								'Objeto'  => 'cboSiNoeschads0',
								'Valido'=>false,
							];
						}else{
							foreach($laLetrasChadsvas as $lnIndice => $laRegistro){
								$lcLetrasChadsvas[$lnIndice] = trim($laRegistro['OP6TMA']);
								$lcDscChadsvas[$lnIndice] = trim($laRegistro['DE2TMA']);
							}
							foreach($taDatos as $lnIndice => $laElemento){
								$lcLetrastaDatos[$lnIndice] = trim($laElemento['lcLetra']);
								$lnCanLetrasDatos = count($lcLetrastaDatos);
								$lnCantUniLetrasDatos = count(array_unique($lcLetrastaDatos));

								switch(true){

									case $laElemento['lnEdad'] < $lnEdadMinima :
										$this->aError = [
											'Mensaje' =>'Edad menor a la mínima requerida para Puntaje CHADSVAS',
											'Objeto'  => 'cboSiNoeschads0',
											'Valido'=>false,
										];
										break;

									case $laElemento['lnValor'] == '' :
										$this->aError = [
											'Mensaje'=>$lcDscChadsvas[$lnIndice] . ', debe ser valorado',
											'Objeto'=>'cboSiNoeschads'.$lnIndice,
											'Valido'=>false,
										];
										break 2;

									case !in_array($laElemento['lcLetra'], $lcLetrasChadsvas):
										$this->aError = [
											'Mensaje' => 'Letra no corresponde',
											'Objeto'  => 'cboSiNoeschads'.$lnIndice,
											'Valido'  => false,
										];
										break 2;

									case ($laElemento['lcLetra'] == 'S'  && $laElemento['lnGenero'] == 'F' && $laElemento['lnValor'] != 1) ||($laElemento['lcLetra'] == 'S'  && $laElemento['lnGenero'] == 'M' && $laElemento['lnValor'] != 0):
										$this->aError = [
											'Mensaje' => 'Valor no corresponde al género',
											'Objeto'  => 'cboSiNoeschads'.$lnIndice,
											'Valido'  => false,
										];
										break 2;

									case ($laElemento['lcLetra'] == 'C' || $laElemento['lcLetra'] == 'H' || $laElemento['lcLetra'] == 'D' || $laElemento['lcLetra'] == 'V' || $laElemento['lcLetra'] == 'A' || $laElemento['lcLetra'] == 'S' ) && !in_array(intval($laElemento['lnValor']), [0, 1]):
										$this->aError = [
											'Mensaje'=>'Valor no corresponde',
											'Objeto'=>'cboSiNoeschads'.$lnIndice,
											'Valido'=>false,
										];
										break 2;

									case ($laElemento['lcLetra'] == 'A2' || $laElemento['lcLetra'] == 'S2' ) && !in_array(intval($laElemento['lnValor']), [0, 2]):
										$this->aError = [
											'Mensaje'=>'Valor no corresponde',
											'Objeto'=>'cboSiNoeschads'.$lnIndice,
											'Valido'=>false,
										];
										break 2;

									case ($laElemento['lcLetra'] == 'A2' && $laElemento['lnEdad'] >= 75 && $laElemento['lnValor'] != 2) || ($laElemento['lcLetra'] == 'A2' && $laElemento['lnEdad'] < 75 && $laElemento['lnValor'] != 0):
										$this->aError = [
											'Mensaje'=>$lcDscChadsvas[$lnIndice] . ', Valor no corresponde a la edad',
											'Objeto'=>'cboSiNoeschads'.$lnIndice,
											'Valido'=>false,
										];
										break 2;

									case $lnCanLetrasDatos > $lnCantUniLetrasDatos:
										$this->aError = [
											'Mensaje' => 'Letra Repetida',
											'Objeto'  => 'cboSiNoeschads'.$lnIndice,
											'Valido'  => false,
										];
										break 2;
								}
							}
						}
					}else{
						$this->aError = [
							'Mensaje' =>'Faltan datos para valorar la escala CHADSVAS',
							'Objeto'  => 'cboSiNoeschads0',
							'Valido'=>false,
						];
					}
				}else{
					$this->aError = [
						'Mensaje' =>'No existe parametrización de Dx que requieren puntaje CHADSVAS',
						'Objeto'  => 'cboSiNoeschads0',
						'Valido'=>false,
					];
				}
			}else{
				$this->aError = [
					'Mensaje' =>'No existen Diagnosticos',
					'Objeto'  => 'cboSiNoeschads0',
					'Valido'=>false,
				];
			}
		}
		return $this->aError;
	}


	public function cargarDatosEsChadsvas()
	{
		$laElemChadsvas = $this->oDb
			->select('CL2TMA, CL3TMA, DE2TMA, OP3TMA, OP4TMA, OP5TMA, OP6TMA, OP7TMA')
			->from('TABMAE')
			->where(['TIPTMA'=>'BLDSCORE', 'CL1TMA'=>'CHADSVAS', ])
			->orderBy('CL2TMA, CL3TMA')
			->getAll('array');
		if(is_array($laElemChadsvas)){
			if(count($laElemChadsvas)>0){
				foreach($laElemChadsvas as $lcClave=>$itemChadsvas ){
					$laElemChadsvas[$lcClave] = array_map('trim', $itemChadsvas);
				}
				$this->aElemChadsvas = $laElemChadsvas;
				return $laElemChadsvas;
			}
		}
	}


	public function interpretarEsChadsvas()
	{
		$laInterChadsvas = $this->oDb
			->select('CL3TMA, DE2TMA, OP3TMA, OP4TMA, OP5TMA, OP7TMA')
			->from('TABMAE')
			->where(['TIPTMA'=>'BLDSCORE', 'CL1TMA'=>'CHADSVAS', 'CL2TMA'=>'INTERP'])
			->getAll('array');
		if(is_array($laInterChadsvas)){
			if(count($laInterChadsvas)>0){
				foreach($laInterChadsvas as $lcClave=>$itemInterChadsvas ){
					$laInterChadsvas[$lcClave] = array_map('trim', $itemInterChadsvas);
				}
				$this->aInterChadsvas = $laInterChadsvas;
				return $laInterChadsvas;
			}
		}
	}

	public function guardarDatosEsChadsvasHC($taDatos=[], $tnIngreso=0, $tcCodiVia, $tnConCon=1, $tnConEvo=0, $tnEntidad=0, $tnTidhis, $tnIdenti, $tcFecCre='', $tcHorCre='', $tcUsuCre='', $tcPrgCre='', $tlRequiereAval=false)
	{
		$lcTexto = 'Puntaje CHA2DS2VAS: ' . $taDatos[0]['lnPuntaje'] . str_repeat(' ', 4) . $taDatos[0]['lcInterpretacion'];
		$lcChadsvas = '';
		$lcSepara ='';
		foreach($taDatos as $laRegistro){
			$lcChadsvas .= $lcSepara . $laRegistro['lcLetra'] . '=' . ($laRegistro['lcCboValor']=='SI'?'Si':'No');
			$lcSepara =';';
		}
		if($tlRequiereAval){
			if($tcPrgCre=='HCPPALWEB'){
				$laDatosEscala= ['Indice'=> 35,
								  'SubIndice'=> 2,
								  'Codigo'=> 0,
								  'Linea'=> 1,
								];
			}else{
				$laDatosEscala= ['Indice'=> 30,
								  'SubIndice'=> 5952,
								  'Codigo'=> 2,
								  'Linea'=> 2,
								];
			}
			$this->GuardarEscalaAval($taDatos, $tnIngreso, $tcCodiVia, $tnConCon, $tnConEvo, $tnEntidad, $tnTidhis, $tnIdenti, $tcFecCre, $tcHorCre, $tcUsuCre, $tcPrgCre, $tlRequiereAval, $lcTexto, $lcChadsvas, $laDatosEscala);
		} else{
			$lcTabla = 'ESCHCL';
			$lcTipo = ($tcCodiVia=='01'?'HCURG':($tcCodiVia=='02'?'HCCEX':'HCHOS'));
			$laDatosESCHCL = [
				'INGEHC' => $tnIngreso,
				'TIPEHC' => $lcTipo,
				'CHCEHC' => $tnConCon,
				'CEVEHC' => $tnConEvo,
				'INDEHC' => 1,
				'IN2EHC' => 2,
				'DESEHC' => $lcChadsvas,
				'OP5EHC' =>	$lcTexto,
				'USREHC' => $tcUsuCre,
				'PGMEHC' => $tcPrgCre,
				'FECEHC' => $tcFecCre,
				'HOREHC' => $tcHorCre,
			];
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatosESCHCL);
		}
	}

	public function validarEscalaCrusade($taDiagnosticos=[], $taDatos=[])
	{
		$laCrusade = $this->oDb
			->select('CL2TMA, CL3TMA, DE2TMA || OP5TMA AS DE2TMA, OP1TMA, OP2TMA, OP3TMA, OP4TMA, OP6TMA, OP7TMA')
			->from('TABMAE')
			->where(['TIPTMA'=>'BLDSCORE', 'CL1TMA'=>'CRUSADE',])
			->getAll('array');
		if(is_array($laCrusade)){
			if(count($laCrusade)>0){
				$lnEdadMinima = 20;
				foreach($laCrusade as $lnIndice => $laRegistro){
					if(substr(($laRegistro['CL2TMA']), 0, 4) == 'ELEM' && substr(($laRegistro['CL3TMA']), 1, 2) == 01){
						$laLetrasCrusade[] = [
							'OP6TMA'=>trim($laRegistro['OP6TMA']),
							'DE2TMA'=>trim($laRegistro['DE2TMA']),
						];
					}else if (trim($laRegistro['CL2TMA']) == 'DX'){
						$lcLstDxCrusade = trim(str_replace('\'', '', $laRegistro['DE2TMA']));
						$lnEdadMinima = !empty($laRegistro['OP3TMA']) ? $laRegistro['OP3TMA'] : $lnEdadMinima;
					}
				}
				$laDxCrusade = explode(',', $lcLstDxCrusade);
				if(count($taDiagnosticos) > 0 ){
					$lbExiste = 0;
					foreach($taDiagnosticos as $lcDiagnostico){
						if(in_array($lcDiagnostico['CODIGO'], $laDxCrusade)){
							$lbExiste++;
						}
					}
					if($lbExiste > 0){
						$lnCantaDatos = (count($taDatos ));
						$lnCanCrusade = count($laLetrasCrusade);
						if($lnCanCrusade > 0 && $lnCantaDatos > 0){
							if(($lnCanCrusade + 9) != $lnCantaDatos){
								$this->aError = [
									'Mensaje' =>'Número incorrecto de valores CRUSADE',
									'Objeto'  => 'cboRangoHematocrito',
									'Valido'=>false,
								];
							}else{
								$laMinMaxHemat = explode('~', trim($this->oDb->obtenerTabmae1('DE2TMA','BLDSCORE',['CL1TMA'=>'CRUSADE','CL2TMA'=>'RANGO','CL3TMA'=>'HEMATOCR','ESTTMA'=>''],null,'0~100')));
								$lnMinHemat = $laMinMaxHemat[0];
								$lnMaxHemat = $laMinMaxHemat[1];

								switch (true){

									case $taDatos['lnEdad'] < $lnEdadMinima :
										$this->aError = [
											'Mensaje' =>'Edad menor a la mínima requerida para Puntaje CRUSADE',
											'Objeto'  => 'lnValorHematocrito',
											'Valido'=>false,
										];
										break;

									case ($taDatos['lnHematocrito'] <= $lnMinHemat || $taDatos['lnHematocrito'] >= $lnMaxHemat):
										$this->aError = [
											'Mensaje'=>'El valor de Hematocrito que digitó esta fuera del rango',
											'Objeto'=>'lnValorHematocrito',
											'Valido'=>false,
										];
										break;

									case ($taDatos['lnCockcroft'] < 0 || $taDatos['lnCockcroft'] > 300):
										$this->aError = [
											'Mensaje'=>'Valor CockCroft Gault no corresponde',
											'Objeto'=>'lnValorCreatinina',
											'Valido'=>false,
										];
										break;

									case ($taDatos['lnFreCardi'] != 0 && ($taDatos['lnFreCardi'] < 20 || $taDatos['lnFreCardi'] > 300)):
										$this->aError = [
											'Mensaje'=>'Valor Frecuencia Cardiáca no corresponde',
											'Objeto'=>'txtFC',
											'Valido'=>false,
										];
										break ;

									case ($taDatos['G']['lnValor'] == 'F'  && $taDatos['G']['lnPuntaje'] != 8) || ($taDatos['G']['lnValor'] == 'M' && $taDatos['G']['lnPuntaje'] != 0):
										$this->aError = [
											'Mensaje' => 'Valor del género no corresponde.',
											'Objeto'  => 'lnValorHematocrito',
											'Valido'  => false,
										];
										break ;

									case ($taDatos['SF']['lnValor'] == 'S' && $taDatos['SF']['lnPuntaje'] != 7) || ($taDatos['SF']['lnValor'] == 'N' && $taDatos['SF']['lnPuntaje'] != 0):
										$this->aError = [
											'Mensaje'=>'Valor Signos de falla cardíaca a la presentación no corresponde',
											'Objeto'=>'cboFallaCardi',
											'Valido'=>false,
										];
										break;

									case ($taDatos['EV']['lnValor'] == 'S' && $taDatos['EV']['lnPuntaje'] != 6) || ($taDatos['EV']['lnValor'] == 'N' && $taDatos['EV']['lnPuntaje'] != 0):
										$this->aError = [
											'Mensaje'=>'Valor Enfermedad vascular previa no corresponde',
											'Objeto'=>'cboVascularPrevia',
											'Valido'=>false,
										];
										break;

									case ($taDatos['DM']['lnValor'] == 'S' && $taDatos['DM']['lnPuntaje'] != 6) || ($taDatos['DM']['lnValor'] == 'N' && $taDatos['DM']['lnPuntaje'] != 0):
										$this->aError = [
											'Mensaje'=>'Valor Diabetes Mellitus no corresponde',
											'Objeto'=>'cboDiabetesMellitus',
											'Valido'=>false,
										];
										break;

									case ($taDatos['lnFreCardi'] != 0 && ($taDatos['lnArteSisto'] < 40 || $taDatos['lnArteSisto'] > 300)):
										$this->aError = [
											'Mensaje'=>'Valor Tensión Arterial Sistólica no corresponde',
											'Objeto'=>'txtTAS',
											'Valido'=>false,
										];
										break ;
								}
							}
						}else{
							$this->aError = [
								'Mensaje' =>'Faltan datos para valorar la escala CRUSADE',
								'Objeto'  => 'cboRangoHematocrito',
								'Valido'=>false,
							];
						}
					}else{
						$this->aError = [
							'Mensaje' =>'No existe parametrización de Dx que requieren puntaje CRUSADE',
							'Objeto'  => 'cboRangoHematocrito',
							'Valido'=>false,
						];
					}
				}else{
					$this->aError = [
						'Mensaje' =>'No existen Diagnosticos',
						'Objeto'  => 'cboRangoHematocrito',
						'Valido'=>false,
					];
				}
			}
		}
		return $this->aError;
	}

	public function cargarDatosEsCrusade($tnIngreso=0)
	{
		$laElemCrusade = $this->oDb
			->select('CL2TMA, CL3TMA, DE2TMA, OP1TMA, OP2TMA, OP3TMA, OP4TMA, OP5TMA, OP6TMA, OP7TMA')
			->from('TABMAE')
			->where(['TIPTMA'=>'BLDSCORE', 'CL1TMA'=>'CRUSADE',])
			->orderBy('CL2TMA, CL3TMA')
			->getAll('array');
		if($this->oDb->numRows()>0){
			$lcLaboratorios = '';
			foreach($laElemCrusade as $lcClave=>$itemCrusade ){
				if (trim($itemCrusade['CL2TMA'])!=='INTERP') {
					$itemCrusade['DE2TMA'] = $itemCrusade['DE2TMA'].$itemCrusade['OP5TMA'];
				}
				$laElemCrusade[$lcClave] = array_map('trim', $itemCrusade);
				if(trim($itemCrusade['CL2TMA']) == 'LABS'){
					$lcLaboratorios = preg_replace("/'/", '', trim($itemCrusade['DE2TMA']));
				}
			}
			$laLaboratorios = explode(',', $lcLaboratorios);
			if(count($laLaboratorios) > 0){
				$lbhabilita = true;
				foreach($laLaboratorios as $lcLaboratorio){
					$cTmpRtaLab = $this->oDb
						->select('COAORD')
						->from('RIAORD')
						->where(['NINORD'=>$tnIngreso, 'COAORD'=>$lcLaboratorio, 'ESTORD'=>3])
						->getAll('array');
					if(is_array($cTmpRtaLab)){
						if(count($cTmpRtaLab) == 0){
							$lbhabilita = false;
						}
					}else{
						$lbhabilita = false;
					}
				}
			}
			$lnResultado = array_merge($laElemCrusade, ['Habilita'=>$lbhabilita]);
			$this->aElemCrusade = $lnResultado;
			return $lnResultado;
		}
	}

	public function calcularPuntajeCreatinina($tnValorCreatinina, $tnPeso, $tnEdad, $tcSexo)
	{
		$laCreatinina = ['MENSAJE' =>''];

		if($tnPeso <= 0){
			$laCreatinina = [
				'MENSAJE' => 'Falta o es incorrecto el valor del Peso del paciente.',
			];
		}else{
			$laMinMax = explode('~', trim($this->oDb->obtenerTabmae1('DE2TMA','BLDSCORE',['CL1TMA'=>'CRUSADE','CL2TMA'=>'RANGO','CL3TMA'=>'CREATINI','ESTTMA'=>''],null,'0.50~6.00')));
			$lnMin = $laMinMax[0];
			$lnMax = $laMinMax[1];
			if($tnValorCreatinina < $lnMin || $tnValorCreatinina > $lnMax){
				$laCreatinina = [
					'MENSAJE' => "Valor de Creatinina debe estar entre $lnMin y $lnMax  mg/dL.",
				];
			}else{
				$lnCcGl = $tnValorCreatinina;
				$lnPeso = $tnPeso;
				$lnEdad = $tnEdad;
				$lcSexo = $tcSexo;
				$lnSexo = $lcSexo=='F' ? 0.85 : 1;
				$lnCrtn = $lnSexo *((140 - $lnEdad) /  $lnCcGl) * ($lnPeso/72);
				$txtCreatinina = number_format((($lnCrtn *100)/ 100), 1);
				$txtCreatinina < 0 ? $txtCreatinina = 0 : $txtCreatinina;
				$laCreatinina = [
					'CREATININA' => $txtCreatinina,
				];
			}
		}
		return $laCreatinina;
	}

	public function guardarDatosEsCrusadeHC($taDatos=[], $tnIngreso=0, $tcCodiVia, $tnConCon=1, $tnConEvo=0, $tnEntidad=0, $tnTidhis, $tnIdenti, $tcFecCre='', $tcHorCre='', $tcUsuCre='', $tcPrgCre='', $tlRequiereAval=false)
	{
		$lcTexto = 'Puntaje CRUSADE: ' . str_repeat(' ', 3) . $taDatos['lnPuntaje'] . str_repeat(' ', 3) . $taDatos['lcInterpretacion'];
		$lcCrusade = "HB={$taDatos['lnHematocrito']};DC={$taDatos['lnCockcroft']};FC={$taDatos['lnFreCardi']};"
					."G={$taDatos['G']['lnPuntaje']};SF={$taDatos['SF']['lnPuntaje']};EV={$taDatos['EV']['lnPuntaje']};"
					."DM={$taDatos['DM']['lnPuntaje']};PS={$taDatos['lnArteSisto']};Peso={$taDatos['lnPeso']}";
		if($tlRequiereAval){
			if($tcPrgCre=='HCPPALWEB'){
				$laDatosEscala= ['Indice'=> 35,
								  'SubIndice'=> 3,
								  'Codigo'=> 0,
								  'Linea'=> 1,
								];
			}else{
				$laDatosEscala= ['Indice'=> 30,
								  'SubIndice'=> 5953,
								  'Codigo'=> 3,
								  'Linea'=> 3,
								];
			}
			$this->GuardarEscalaAval($taDatos, $tnIngreso, $tcCodiVia, $tnConCon, $tnConEvo, $tnEntidad, $tnTidhis, $tnIdenti, $tcFecCre, $tcHorCre, $tcUsuCre, $tcPrgCre, $tlRequiereAval, $lcTexto, $lcCrusade, $laDatosEscala);
		}else{
			$lcTabla = 'ESCHCL';
			$lcTipo = ($tcCodiVia=='01'?'HCURG':($tcCodiVia=='02'?'HCCEX':'HCHOS'));
			$laDatosESCHCL = [
				'INGEHC' => $tnIngreso,
				'TIPEHC' => $lcTipo,
				'CHCEHC' => $tnConCon,
				'CEVEHC' => $tnConEvo,
				'INDEHC' => 1,
				'IN2EHC' => 3,
				'DESEHC' => $lcCrusade,
				'OP5EHC' =>	$lcTexto,
				'USREHC' => $tcUsuCre,
				'PGMEHC' => $tcPrgCre,
				'FECEHC' => $tcFecCre,
				'HOREHC' => $tcHorCre,
			];
		}
		$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatosESCHCL);
	}

	public function ConsultarEscalaSangrado($tnIngreso=0, $tnIndice=0, $taDatosEsc=[])
	{
		$laDatosRetorno = [];
		if(count($taDatosEsc)==0){
			$laEscalas = $this->oDb
				->select('TRIM(DESEHC) DESEHC, TRIM(OP5EHC) OP5EHC')
				->from('ESCHCL')
				->where(['INGEHC'=>$tnIngreso, 'IN2EHC' => $tnIndice])
				->orderBy ('FECEHC DESC, HOREHC DESC')
				->get('array');
		}else{
			$laEscalas = $taDatosEsc;
		}

		if(is_array($laEscalas)){
			$laDatosEscala =  explode(';',$laEscalas['DESEHC']);
			foreach($laDatosEscala as $laRegs) {
				if(!empty(trim($laRegs))){
					$laDatos = explode('=',$laRegs);
					if(is_array($laDatos)){
						$laDatosRetorno[$laDatos[0]] = (trim($laDatos[1]??'')=='Si' || trim($laDatos[1]??'')=='SI'?'SI':(trim($laDatos[1]??'')=='No' || trim($laDatos[1]??'')=='NO'?'NO':''));
					}
				}
			}
		}
		$laDatosRetorno['INTERPRETA'] = $laEscalas['OP5EHC']??'';
		return $laDatosRetorno;
	}

	public function GuardarEscalaAval($taDatos=[], $tnIngreso=0, $tcCodiVia='', $tnConCon=1, $tnConEvo=0, $tnEntidad=0, $tnTidhis=0, $tnIdenti=0, $tcFecCre='', $tcHorCre='', $tcUsuCre='', $tcPrgCre='', $tlRequiereAval=false, $tcTexto='', $tcEscala='',$taDatosEscala=[])
	{
		$lcTabla = ($tcPrgCre=='HCPPALWEB'?'HISINT':'REINDE');
		if($lcTabla=='HISINT'){
			$lcTipo = 'HC';
			$laDatosESCHCL = [
				'INGHIN' => $tnIngreso,
				'TIPHIN' => $lcTipo,
				'CCOHIN' => $tnConCon,
				'INDHIN' => $taDatosEscala['Indice'],
				'SUBHIN' => $taDatosEscala['SubIndice'],
				'CODHIN' => $taDatosEscala['Codigo'],
				'CLNHIN' => $taDatosEscala['Linea'],
				'DESHIN' => $tcEscala,
				'OP5HIN' => $tcTexto,
				'USRHIN' => $tcUsuCre,
				'PGMHIN' => $tcPrgCre,
				'FECHIN' => $tcFecCre,
				'HORHIN' => $tcHorCre,
			];
		}else{
			$lcTipo = ($tcPrgCre=='EVOPIWEB'?'EP':($tcPrgCre=='EVOURWEB'?'ER':($tcPrgCre=='EVOUNWEB'?'EU':'')));
			$laDatosESCHCL = [
				'INGRID'=>$tnIngreso,
				'TIPRID'=>$lcTipo,
				'CONRID'=>$tnConCon,
				'CEXRID'=>$taDatosEscala['Codigo'],
				'CLIRID'=>$taDatosEscala['Linea'],
				'INDRID'=>$taDatosEscala['Indice'],
				'IN2RID'=>$taDatosEscala['SubIndice'],
				'DESRID'=>$tcTexto,
				'OP5RID'=>$tcEscala,
				'USRRID'=>$tcUsuCre,
				'PGMRID'=>$tcPrgCre,
				'FECRID'=>$tcFecCre,
				'HORRID'=>$tcHorCre,
			];
		}
		$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatosESCHCL);
	} 

}