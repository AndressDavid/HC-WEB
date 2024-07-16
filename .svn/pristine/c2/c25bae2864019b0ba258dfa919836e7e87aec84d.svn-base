<?php
namespace NUCLEO;

require_once ('class.Db.php');
require_once ('class.Doc_JuntaMedica.php');
require_once('class.AplicacionFunciones.php');

use NUCLEO\Db;
use NUCLEO\Doc_JuntaMedica;

class Epicrisis_Ingreso
{
	protected $oDb;
	protected $aRetorno = [
				'Mensaje' => "",
				'Objeto' => "",
				'Valido' => true,
				'Estado' => 0,
				'Analisis' => "",
				'CodSalida' => "",
				'Fibrilacion' => false,
				'ConsDocum' => 0,
				'ConsConsulta' => 0,
				'CieFallece' => "",
				'DescripcionCieFallece' => "",
				];

	public function __construct()
    {
		global $goDb;
		$this->oDb = $goDb;
    }

	// Obtiene los estados del paciente
	public function obtenerEstadosTodos($tnIngreso=0)
	{
		$this->estadoPaciente($tnIngreso);
		$this->obtenerAnalisisEpi($tnIngreso);
		$this->listainterpretacionExamenes($tnIngreso);
		if($this->aRetorno['Estado']==2){
			$this->tipoEstadoFallece($tnIngreso);
			$this->diagnosticoFallece($tnIngreso);
		}
		$this->ObtenerFibrilacion($tnIngreso);

		return $this->aRetorno;
	}

	public function existeEpicrisis($tnIngreso=0)
	{
		$laTempEPI = $this->oDb
			->select('NINEPH, CCNEPH, CONEPH')
				->from('RIAEPH')
				->where([
						'NINEPH'=>$tnIngreso,
						'ESTEPH'=>3,
						])
				->In('PGMEPH',['EPIPPAL', 'EPIPPALWEB'])
				->get('array');

		if(is_array($laTempEPI) && count($laTempEPI)>0){
			$this->aRetorno = [
				'Mensaje'=>'Ya existe epicrisis para el ingreso',
				'Valido'=>false,
				'ConsDocum'=>$laTempEPI['CCNEPH'],
				'ConsConsulta'=>$laTempEPI['CONEPH'],
			];
		}
		unset($laTempEPI);
		return $this->aRetorno;
	}

	public function estadoPaciente($tnIngreso=0, $tlbuscaHC=true)
	{
		$this->aRetorno['Estado']=0;
		$llSeguir=true;
		if($tlbuscaHC){
		$laTempEPI = $this->oDb
			->select('CODIGO ')
				->from('RIAHIS')
				->where([
						'NROING'=>$tnIngreso,
						'INDICE'=>55,
						])
				->get('array');

		if(is_array($laTempEPI)){
			if(count($laTempEPI)>0){
				$this->aRetorno['Estado'] = $laTempEPI['CODIGO'];
				$llSeguir=false;
			}
		} else{
				$llSeguir=true;
			}
		}

		if($llSeguir){
			$laTempEPI = $this->oDb
			->select('DESEVL')
				->from('EVOLUC')
				->where('NINEVL','=',$tnIngreso)
				->in('CNLEVL', [5995,6995])
				->orderBy('CONEVL DESC')
				->get('array');

			if(is_array($laTempEPI) && count($laTempEPI)>0){
				$laCadena =  explode(chr(13),trim($laTempEPI['DESEVL']));
				if ($laCadena[1]??''==='Salida'){
					$this->aRetorno['Estado'] = (substr(trim($laCadena[2]??''),17)=='VIVO'?1:(substr(trim($laCadena[2]??''),17)=='FALLECE'?2:0));
				}
			}
		}
		unset($laTempEPI);
		return $this->aRetorno['Estado'];
	}

	function tipoEstadoFallece($tnIngreso=0)
	{
		$lnHoras = $ltFechaTriage = 0 ;
		$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema());

		$laTempEPI = $this->oDb
		->select('FEIING, HORING')
			->from('RIAING')
			->where('NIGING', '=', $tnIngreso)
			->get('array');

		if(is_array($laTempEPI) && count($laTempEPI)>0){
			$ltFechaTriage = new \DateTime(AplicacionFunciones::formatFechaHora('fechahora', $laTempEPI['FEIING'].' '.$laTempEPI['HORING'])) ;
		}

		$laHoras = $ltAhora->diff($ltFechaTriage);
		if($laHoras->format('%y') > 0 || $laHoras->format('%m') > 0 || $laHoras->format('%d') > 1 ){
			$lnHoras = 50;
		}else {
			$lnHoras = ($laHoras->format('%d') * 24 ) + $laHoras->format('%h');
		}
		switch (true){
			case $lnHoras <= 24  :
				$this->aRetorno['CodSalida'] = '06';
				break;
			case $lnHoras > 24 && $lnHoras <= 48 :
				$this->aRetorno['CodSalida'] = '03';
				break;
			case $lnHoras > 48 :
				$this->aRetorno['CodSalida'] = '04';
				break;
		}
	}

	function diagnosticoFallece($tnIngreso=0)
	{
		$laTempEPI = $this->oDb
		->select('OP5EDC AS FALLECE')
			->from('EVODIA')
			->where('INGEDC', '=', $tnIngreso)
			->where('INDEDC', '=', 1)
			->where('OP5EDC', '<>', '')
			->orderBy('EVOEDC DESC')
			->get('array');

		if(is_array($laTempEPI) && count($laTempEPI)>0){
			$lcFallece = explode('=',$laTempEPI['FALLECE']);
			$this->aRetorno = array_merge($this->aRetorno, [
				'HorFallece' => mb_substr(trim($lcFallece[0]),0,4),
				'FecFallece' => trim($lcFallece[1]),
				'CieFallece' => trim($lcFallece[2]),
			]);
			$laDescripcionFallece = $this->oDb->select(trim('DE2RIP'))->from('RIACIE')->where(['ENFRIP'=>$this->aRetorno['CieFallece']])->get("array");
			$this->aRetorno['DescripcionCieFallece'] = trim($laDescripcionFallece['DE2RIP']);
		}
		unset($laTempEPI);
	}

	function obtenerAnalisisEpi($tnIngreso=0)
	{
		$lcSL = "\n"; //PHP_EOL;
		$laJuntaMedica = [];
		$laAnalisis = [];
		$laTemp = $this->oDb
			->select('CEVAEP CONSEC, CNLAEP LINEA, DESAEP DESCRI, FECAEP FECHA, HORAEP HORA, FECAEP * 1000000 + HORAEP FECHORA')
				->from('ANAEPI')
				->where('INGAEP', '=', $tnIngreso)
				->orderBy('FECAEP, HORAEP, CEVAEP, CNLAEP')
				->getAll('array');

		if(is_array($laTemp) && count($laTemp)>0){
			$lcDescrip = $lcEncab = $lnFechaHora = '';
			$lnConsec = $laTemp[0]['CONSEC'];
			foreach($laTemp as $lnClave=>$laInformacion) {
				if($laInformacion['CONSEC']!==$lnConsec){
					$laAnalisis[] = ['DESCRI' => $lcEncab . $lcDescrip,
									 'FECHORA'=> $lnFechaHora];
					$lcDescrip = '';
					$lnConsec = $laInformacion['CONSEC'];
				}
				$lcEncab = AplicacionFunciones::formatFechaHora('fechahora', $laInformacion['FECHA'].' '.$laInformacion['HORA']) . $lcSL ;
				$lnFechaHora =  $laInformacion['FECHORA'];
				$lcDescrip .= $laInformacion['DESCRI'];
			}
			if(!empty(trim($lcDescrip))){
				$laAnalisis[] = ['DESCRI' => $lcEncab . $lcDescrip,
								 'FECHORA'=> $lnFechaHora];
			}
		}

		//  ANALISIS DE EPICRISIS PARA JUNTA MEDICA
		$lcIncluyeJuntaMedica = $lcMostrarPlanes = trim($this->oDb->obtenerTabMae1('OP2TMA', "EPICRIS", "CL1TMA='ADDJMED' AND ESTTMA=''", null, ''));
		if($lcIncluyeJuntaMedica=='SI'){
			$laConse = $this->oDb
					->select('CJUJUN')
					->from('RIAJUNL02')
					->where('INGJUN', '=', $tnIngreso)
					->groupBy('CJUJUN')
					->getAll('array');

			if(is_array($laConse) && (count($laConse)>0)){
				foreach ($laConse as $lnkey=>$laReg){
					$laDatos = [
						'nIngreso'		=> $tnIngreso,
						'nConsecCons' 	=> $laReg['CJUJUN'],
					];
					$loJuntaMedica= new Doc_JuntaMedica();
					$laRetorno = $loJuntaMedica->retornarDocumento($laDatos, true);
					$laJuntaMedica[$lnkey] = array_merge($laJuntaMedica, $laRetorno);

					// NOTAS ACLARATORIAS DE LA JUNTA MEDICA
					$laTemp = $this->oDb
						->select('DESNIM DESCRI, FECNIM FECHA, HORNIM HORA, FECNIM * 1000000 + HORNIM FECHORA')
						->from('NOTIMAL02')
						->where('INGNIM', '=', $tnIngreso)
						->where('CCINIM', '=', $laRetorno['Junta']['lnConCit'])
						->orderBy('FECNIM, HORNIM, CNLNIM')
						->getAll('array');

					if(is_array($laTemp) && count($laTemp)>0){
						$lcDescrip = 'NOTA ACLARATORIA JUNTA MEDICA' . $lcSL;
						foreach ($laTemp as $laReg){
							$lcDescrip .= $laReg['DESCRI'];
						}

						if(!empty(trim($lcDescrip))){
							$lnFechaHora = strval($laTemp[0]['FECHA'] * 1000000 + $laTemp[0]['HORA']);
							$laAnalisis[] = ['DESCRI' => $lcDescrip,
											 'FECHORA'=> $lnFechaHora];
						}
					}
				}

				if(count($laJuntaMedica)>0){
					foreach ($laJuntaMedica as $lnkey=>$laReg){
						$laParticipantes = $laReg['Participantes'];
						$laDatosJunta = $laReg['Junta'];
						$lcDescrip = '';
						if(count($laParticipantes)>0){
							$lcDescrip .= 'PARTICIPANTES:' . $lcSL;
							foreach ($laParticipantes as $laParticipa){
								$lcDescrip .= $laParticipa['nmeunm'] .' - ' . $laParticipa['desunm'] . $lcSL;
							}
						}

						if(isset($laDatosJunta)){
							// DATOS MOTIVO JUNTA
							if(!empty(trim($laDatosJunta['lcMotpjm']))){
								$lcDescrip .= 'MOTIVO JUNTA' . $lcSL . trim($laDatosJunta['lcMotpjm']) . $lcSL;
							}

							// DATOS DISCUSION DEL CASO CLINICO
							if(!empty(trim($laDatosJunta['lcDispjm']))){
								$lcDescrip .= 'DISCUSIÓN DEL CASO CLÍNICO' . $lcSL . trim($laDatosJunta['lcDispjm']) . $lcSL;
							}

							// CONCLUSION
							if(!empty(trim($laDatosJunta['lcConpjm']))){
								$lcDescrip .= 'CONCLUSIÓN' . $lcSL . trim($laDatosJunta['lcConpjm']) . $lcSL;
							}
						}

						if(!empty(trim($lcDescrip))){
							$lcEncab = AplicacionFunciones::formatFechaHora('fechahora',$laDatosJunta['lfJupjm'] . ' ' . $laDatosJunta['lhJupjm']) . ' JUNTA MEDICA' . $lcSL ;
							$lnFechaHora = strval($laDatosJunta['lfJupjm'] * 1000000 + $laDatosJunta['lhJupjm']);
							$laAnalisis[] = ['DESCRI' => $lcEncab . $lcDescrip . $lcSL,
											 'FECHORA'=> $lnFechaHora];
						}
					}
				}
			}

			unset($laJuntaMedica);

			// NOTAS ACLARATORIAS
			$laIngreso = $this->oDb
				->select('TIDING, NIDING, FEIING')
				->from('RIAING')
				->where('NIGING', '=', $tnIngreso)
				->get('array');

			if(is_array($laIngreso) &&(count($laIngreso)>0)){
				$laTemp = $this->oDb
					->select('CONNOT CONSEC, DESNOT DESCRI, FECNOT FECHA, HORNOT HORA, FECNOT * 1000000 + HORNOT FECHORA, CNLNOT LINEA')
					->from('NOTACL')
					->where(['TIDNOT'=>$laIngreso['TIDING'], 'IDENOT'=>$laIngreso['NIDING']])
					->where('FECNOT', '>=', $laIngreso['FEIING'])
					->orderBy('FECNOT, HORNOT, CNLNOT')
					->getAll('array');

				if(is_array($laTemp) && count($laTemp)>0){
					$lcDescrip = '';
					$lnConsec = $laTemp[0]['CONSEC'];
					foreach($laTemp as $lnClave=>$laInformacion) {
						if($laInformacion['CONSEC']!==$lnConsec){
							$laAnalisis[] = ['DESCRI' => $lcEncab . $lcDescrip . $lcSL,
											'FECHORA'=> $laInformacion['FECHORA']];
							$lcDescrip = '';
							$lnConsec = $laInformacion['CONSEC'];
						}
						$lcEncab = 'NOTA ACLARATORIA' . $lcSL ;
						$lnFechaHora = $laInformacion['FECHORA'];
						$lcDescrip .=  ($laInformacion['LINEA']==2? $lcSL:'') . $laInformacion['DESCRI'];
					}

					if(!empty(trim($lcDescrip))){
						$laAnalisis[] = ['DESCRI' => $lcEncab . $lcDescrip . $lcSL,
											'FECHORA'=> $lnFechaHora];
					}
				}
			}
			unset($laIngreso);
			unset($laTemp);
		}

		if(is_array($laAnalisis) && count($laAnalisis)>0){
			AplicacionFunciones::ordenarArrayMulti($laAnalisis, 'FECHORA');
			$lcDescrip = '';
			foreach($laAnalisis as $laInformacion) {
				$lcDescrip .= trim($laInformacion['DESCRI']) . $lcSL . $lcSL;
			}
			if(!empty($lcDescrip)){
				$this->aRetorno['Analisis'] = $lcDescrip;
			}
		}

		unset($laAnalisis);

		// SI NO HAY INFORMACION DE EVOLUCIONES CONSULTA HC
		if(empty(trim($this->aRetorno['Analisis']))){
			$laTempEPI = $this->oDb
			->select('INDICE INDICE, DESCRI DESCRIP')
				->from('RIAHIS')
				->where('NROING', '=', $tnIngreso)
				->In('INDICE',[30, 35, 50])
				->getAll('array');

			if(is_array($laTempEPI) && count($laTempEPI)>0){
				$lcPlan = $lcEscalas = $lcTuvo = '';
				foreach($laTempEPI as $laTemp) {
					switch($laTemp['INDICE']){
						case 30:
							$lcPlan.= $laTemp['DESCRIP'];
							break;
						case 35:
							$lcEscalas .= $laTemp['DESCRIP'] . $lcSL;
							break;
						case 50:
							$lcTuvo .= $laTemp['DESCRIP'];
							break;
					}
				}

				$this->aRetorno['Analisis'] .= 'Presenta tuvo electrocardiograma?: ' . (empty($lcTuvo)? 'NO': 'SI') . $lcSL
											. (!empty(trim($lcTuvo))?trim($lcTuvo). $lcSL:'')
											. (!empty(trim($lcPlan))?'Descripción Plan de Manejo: ' . trim($lcPlan) . $lcSL:'')
											. (!empty(trim($lcEscalas))?trim($lcEscalas):'') . $lcSL ;
			}
		}
	}

	function ObtenerFibrilacion($tnIngreso=0)
	{
		$laTempEPI = $this->oDb
			->select('NINORD')
				->from('RIAORDL26')
				->where([
						'NINORD'=>$tnIngreso,
						'CODORD'=>'130',
						'COAORD'=>'22',
						])
				->get('array');

		if(is_array($laTempEPI) && count($laTempEPI)>0){
				$this->aRetorno['Fibrilacion'] = true;
		}
	}

	function listainterpretacionExamenes($tnIngreso)
	{
		$this->aRetorno['Interpreta'] = '';
		$lcSL = "\n"; //PHP_EOL;
		$laTemp = $this->oDb
				->select('CONEVL CONSEC, CCIEVL INDICE, DESEVL DESCRI, FECEVL FECHA, HOREVL HORA, FECEVL * 1000000 + HOREVL FECHORA')
				->from('EVOLUC')
				->where( ['NINEVL'=>$tnIngreso])
				->between('CNLEVL',7001,7451)
				->orderBy('CONEVL DESC,CNLEVL')
				->getAll('array');

		if(is_array($laTemp) && count($laTemp)>0){
			
			$lcDescrip = $lcEncab = $lnFechaHora = '';
			$lnConsec = $laTemp[0]['CONSEC'];
			$lnIndice = 0;
			$lnInd = 0;
			foreach($laTemp as $lnClave=>$laInformacion) {
				if($laInformacion['CONSEC']!==$lnConsec){
					$laInterpreta[] = [ 'DESCRI' => $lcEncab . $lcDescrip,	
										'FECHORA'=> $lnFechaHora];
					$lcDescrip = '';
					$lnInd = 0;
					$lnConsec = $laInformacion['CONSEC'];
				}

				if ($laInformacion['INDICE']!==$lnIndice){
					$lnInd++;
					$lcDescrip .= $lcSL . $lnInd  . ') ';
					$lnIndice = $laInformacion['INDICE']; 
				}else{$lcDescrip .= $lcSL . '   ';}
				$lcEncab = AplicacionFunciones::formatFechaHora('fechahora', $laInformacion['FECHA'].' '.$laInformacion['HORA']) . $lcSL;
				$lnFechaHora = $laInformacion['FECHORA'];
				$lcDescrip .=  trim($laInformacion['DESCRI']);
			}
			if(!empty(trim($lcDescrip))){
				$laInterpreta[] = ['DESCRI' => $lcEncab . $lcDescrip,
									'FECHORA'=> $lnFechaHora];
			}

				$lcDescrip = '';
			if(is_array($laInterpreta) && count($laInterpreta)>0){
				AplicacionFunciones::ordenarArrayMulti($laAnalisis, 'FECHORA');
				foreach($laInterpreta as $laInformacion) {
					$lcDescrip .=$laInformacion['DESCRI'] . $lcSL . $lcSL;
				}
				if(!empty($lcDescrip)){
					$this->aRetorno['Interpreta'] = $lcDescrip;
				}
			}
			unset($laTemp);
		}
	}
}
