<?php
namespace NUCLEO;
require_once __DIR__ .'/class.TextHC.php';

class ConsultaInfectologia
{
	protected $aDosis = [] ;
	protected $aFrecuen = [] ;
	protected $aVia = [] ;
	protected $aParamAntib = [];
	protected $aParamFrm = [];
	protected $oDb;
	protected $cEspecialidad = '';

	/*
		*	Consulta Infectologia
		*	@param integer $tnIngreso: número de ingreso
		*	@param string $tcFechaIni: fecha inicial de consulta en formato AAAA-MM-DD
		*	@param string $tcFechaFin: fecha final de consulta en formato AAAA-MM-DD
		*	@param string $tcMedicamento: Se debe consultar por medicamento
		*	@return array con los elementos error y html
		*/

	public function ConsultaInfectologia($tnIngreso, $tcFechaIni, $tcFechaFin, $tcMedicamento)
	{
		global $goDb;
		$this->oDb = $goDb;
		$laRetorna = ['error'=>''];
		$lnFechaIni = intval(str_replace('-','',$tcFechaIni));
		$lnFechaFin = intval(str_replace('-','',$tcFechaFin));
		$this->cEspecialidad = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getEspecialidad():'');

		if ($tnIngreso > 1000000 && $tnIngreso < 9999999) {
			if ($lnFechaFin >= $lnFechaIni) {
				if(empty(trim($tcMedicamento))){
					$laCondiciones = ['INGANT'=>$tnIngreso];
				} else {
					$laCondiciones = ['INGANT'=>$tnIngreso, 'MEDANT'=>$tcMedicamento];
				}

				$laConsulta = $this->oDb
					->select('A.*, B.DESDES AS MDDANT, IFNULL(TRIM(C.NOMMED)||\' \'||TRIM(C.NNOMED), \'\') AS MEDFORMULA')
					->select('M.REGMED, TRIM(M.NNOMED)||\' \'||TRIM(M.NOMMED) MEDAPRU')
					->select('MR.REGMED, TRIM(MR.NNOMED)||\' \'||TRIM(MR.NOMMED) MEDREVISA')
					->from('USOANT AS A')
					->leftJoin('INVDES AS B', 'A.MEDANT=B.REFDES', null)
					->leftJoin('RIARGMN AS C', 'A.USUANT=C.USUARI', null)
					->leftJoin('RIARGMN AS M', 'A.USMANT=M.USUARI', null)
					->leftJoin('RIARGMN AS MR', 'A.USVANT=MR.USUARI', null)
					->where($laCondiciones)
					->between('A.FECANT',$lnFechaIni,$lnFechaFin)
					->orderBy ('A.FECANT DESC, A.HORANT DESC, B.DESDES')
					->getAll('array');

					if(is_array($laConsulta)){
						if(count($laConsulta)>0){
							$this->Crear_parametroAntib();
							$laPar = $this->aParamAntib;
							$laAnchos = [30,30,130];
							$laAnchos1 = [30,160];
							$lcDetalle = '';
							$lnNumReg = 0;
							$laFilas = [];

							foreach($laConsulta as $laReg){
								$lnNumReg ++;
								// Medicamento
								$laFilas[] = [ 'w'=>190, 'd'=>['<b>MEDICAMENTO ' .  $lnNumReg . ': ' . trim($laReg['MEDANT']) . ' - ' . trim($laReg['MDDANT']) . '</b>' ,], 'a'=>'L', ] ;
								$laFilas[] = [ 'w'=>$laAnchos1, 'd'=>['FORMULACION:&nbsp;','Dr. ' . trim($laReg['MEDFORMULA']) . '  ' . AplicacionFunciones::formatFechaHora('fechahora12', $laReg['FECANT'].' '.$laReg['HORANT']) ,], 'a'=>['R','L','L'], ];

								/* Días y Fecha Fin
								$laFilas[] = [ 'w'=>$laAnchos, 'd'=>['','DIAS DE USO: ' , trim($laReg['DIAANT']),], 'a'=>'L', ] ;
								$laFilas[] = [ 'w'=>$laAnchos, 'd'=>['','FECHA FIN: ' , AplicacionFunciones::formatFechaHora('fecha', $laReg['FEFANT']),], 'a'=>'L', ] ;*/

								// Dosis
								$key = array_search(trim($laReg['UDOANT'])??'', array_column($this->aDosis, 'CODIGO'));
								if (is_numeric($key)){
									$lcTemp=$this->aDosis[$key]['DESCRIP'];
									$laFilas[] = [ 'w'=>$laAnchos, 'd'=>['','DOSIS: ' , trim($laReg['DOSANT']) . ' ' .  trim($lcTemp) ,], 'a'=>'L', ] ;
								}

								// Frecuencia
								$key = array_search(trim($laReg['UFRANT'])??'', array_column($this->aFrecuen, 'CODIGO'));
								if (is_numeric($key)){
									$lcTemp=$this->aFrecuen[$key]['DESCRIP'];
									$laFilas[] = [ 'w'=>$laAnchos, 'd'=>['','FRECUENCIA: ' , trim($laReg['FRCANT']) . ' ' .  trim($lcTemp) ,], 'a'=>'L', ] ;
								}

								// Via
								$key = array_search(trim($laReg['VIAANT'])??'', array_column($this->aVia, 'CODIGO'));
								if (is_numeric($key)){
									$lcTemp=$this->aVia[$key]['DESCRIP'];
									$laFilas[] = [ 'w'=>$laAnchos, 'd'=>['','VIA: ' , trim($lcTemp) ,], 'a'=>'L', ] ;
								}

								$lcDetalle = '';
								if(!empty(trim($laReg['DIOANT']) )){
									$lcTemp = 'DIAGNOS' . trim($laReg['DIOANT']) ;
									$lcDetalle = ' / ' . $laPar[$lcTemp] ;
								}

								$llRevisa = false;
								switch (true){

									case $laReg['ESTANT']=='1' :
										$laFilas[] = [ 'w'=>$laAnchos1, 'd'=>['<b>CONTROL ANTIBIOTICO:&nbsp;</b>','<b>Dr. ' . trim($laReg['MEDAPRU']) . '  ' . AplicacionFunciones::formatFechaHora('fechahora12', $laReg['FEMANT'].' '.$laReg['HOMANT']) . '</b>',], 'a'=>['R','L'], ];
										$laFilas[] = [ 'w'=>$laAnchos, 'd'=>['','Consideración Uso: ' , '<b><u><font COLOR="green">APROPIADO </font></u></b>',], 'a'=>'L', ] ;
										if(!empty(trim($laReg['OBVANT']))){
											$laFilas[] = [ 'w'=>$laAnchos, 'd'=>['','Observaciones: ', trim($laReg['OBVANT']),], 'a'=>'L', ] ;
										}
										$llRevisa = ($this->cEspecialidad=='781'?true:false) ;
										break ;

									case $laReg['ESTANT']=='2' || $laReg['ESTANT']=='4' :
										$laFilas[] = [ 'w'=>$laAnchos1, 'd'=>['<b>CONTROL ANTIBIOTICO:&nbsp;</b>','<b>Dr. ' . trim($laReg['MEDAPRU']) . '  ' . AplicacionFunciones::formatFechaHora('fechahora12', $laReg['FEMANT'].' '.$laReg['HOMANT']) . '</b>',], 'a'=>['R','L'], ];
										$laFilas[] = [ 'w'=>$laAnchos, 'd'=>['','Consideración Uso: ' , '<b><u><font COLOR="red">INAPROPIADO </font></u></b>',], 'a'=>'L', ] ;
										if(!empty(trim($laReg['MTVANT']))){
											$lcTemp = 'MOTIVOS' . trim($laReg['MTVANT']) ;
											$laFilas[] = [ 'w'=>$laAnchos, 'd'=>['','Motivo Inapropiado: ' ,  $laPar[$lcTemp],], 'a'=>'L', ] ;
										}
										if(!empty(trim($laReg['OBVANT']))){
											$laFilas[] = [ 'w'=>$laAnchos, 'd'=>['','Observaciones: ', trim($laReg['OBVANT']),], 'a'=>'L', ] ;
										}

										$llRevisa = ($this->cEspecialidad=='781'?true:false) ;
										break ;

									case $laReg['ESTANT']=='3' :
										$laFilas[] = [ 'w'=>$laAnchos1, 'd'=>['<b>CONTROL ANTIBIOTICO:&nbsp;</b>','<b>Dr. ' . trim($laReg['MEDAPRU']) . '  ' . AplicacionFunciones::formatFechaHora('fechahora12', $laReg['FEMANT'].' '.$laReg['HOMANT']) . '</b>',], 'a'=>['R','L'], ];
										$laFilas[] = [ 'w'=>$laAnchos, 'd'=>['','Consideración Uso: ' , 'PENDIENTE',], 'a'=>'L', ] ;
										$llRevisa = ($this->cEspecialidad=='781'?true:false) ;
										break ;

									default:
										$llRevisa = false;
										break ;
								}

								if($llRevisa == true){

									// Medicamento
									$laTemp = $this->oDb
									->select('DOSFMD, DDOFMD, FREFMD, DFRFMD, VIAFMD, DIAFMD, CANFMD, DCAFMD, ESTFMD')
									->from('FORMED')
									->where([
										'NINFMD'=>$laReg['INGANT'],
										'MEDFMD'=>$laReg['MEDANT'],
										'FECFMD'=>$laReg['FEVANT'],
										'HORFMD'=>$laReg['HOVANT'],
									])
									->get('array');

									if(is_array($laTemp)){

										if (in_array($laTemp['ESTFMD'],['15','16'])) {
											$laTemp['ESTFMD']='11';
										}

										if(!empty(trim($laReg['FEVANT']))){
											$laFilas[] = [ 'w'=>$laAnchos1, 'd'=>['RESPUESTA:&nbsp', 'Dr. ' . trim($laReg['MEDREVISA']) . '  ' . AplicacionFunciones::formatFechaHora('fechahora12', $laReg['FEVANT'].' '.$laReg['HOVANT']) . ' - Estado: ' . trim($this->aParamFrm[$laTemp['ESTFMD']]),], 'a'=>['R','L'], ];
										}

										// Dosis
										$key = array_search(trim($laTemp['DDOFMD'])??'', array_column($this->aDosis, 'CODIGO'));
										if (is_numeric($key)){
											$lcTemp=$this->aDosis[$key]['DESCRIP'];
											$laFilas[] = [ 'w'=>$laAnchos, 'd'=>['','DOSIS : ' , trim($laTemp['DOSFMD']) . ' ' .  trim($lcTemp) ,], 'a'=>'L', ] ;
										}

										// Frecuencia
										$key = array_search(trim($laTemp['DFRFMD'])??'', array_column($this->aFrecuen, 'CODIGO'));
										if (is_numeric($key)){
											$lcTemp=$this->aFrecuen[$key]['DESCRIP'];
											$laFilas[] = [ 'w'=>$laAnchos, 'd'=>['','FRECUENCIA : ' , trim($laTemp['FREFMD']) . ' ' .  trim($lcTemp) ,], 'a'=>'L', ] ;
										}

										// Via
										$key = array_search(trim($laTemp['VIAFMD'])??'', array_column($this->aVia, 'CODIGO'));
										if (is_numeric($key)){
											$lcTemp=$this->aVia[$key]['DESCRIP'];
											$laFilas[] = [ 'w'=>$laAnchos, 'd'=>['','VIA : ' , trim($lcTemp) ,], 'a'=>'L', ] ;
										}
									}
								}
							}
							$loText = new TextHC();
							$loText->procesar([
								'Titulo' => "CONSULTA PROA - INGRESO $tnIngreso <br> <small>Entre $tcFechaIni y $tcFechaFin</small>",
								'Cabeza' => [
									'mostrar'=>false,
									'logo'=>false,
									'mostrarpie'=>false,
									'texto'=>'',
								],
								'Cuerpo' => [ ['tabla', [], $laFilas] ],
								'DatLog' => '',
							]);
							$lcHtml = $loText->cHtml();
							$laRetorna['error'] = '';
							$laRetorna['html'] =  $lcHtml;
						} else {
							$laRetorna['error'] = "No se encontraron registros de Antibióticos para el ingreso $tnIngreso";
						}
					} else {
						$laRetorna['error'] = "No se encontraron registros de Antibióticos para el ingreso $tnIngreso";
					}
			} else {
					$laRetorna['error'] = "Fecha Desde no puede ser mayor a Fecha Hasta";
				}
		} else {
			$laRetorna['error'] = "Número de ingreso $tnIngreso incorrecto";
			}
		return $laRetorna;
	}

	function Crear_parametroAntib()
	{
		//	parámetros para medicamentos
		// Dosis
			$this->aDosis = $this->oDb
			->select('TRIM(CL2TMA) CODIGO, TRIM(DE1TMA) DESCRIP')
			->from('TABMAE')
			->where([
				'TIPTMA'=>'MEDDOS',
				'ESTTMA'=>' ',
			])
			->getAll('array');

		// Frecuencia
		$this->aFrecuen = $this->oDb
			->select('TRIM(CL2TMA) CODIGO, TRIM(DE1TMA) DESCRIP')
			->from('TABMAE')
			->where([
				'TIPTMA'=>'MEDFRE',
				'ESTTMA'=>' ',
			])
			->getAll('array');

		// Vía
		$this->aVia = $this->oDb
			->select('TRIM(CL1TMA) CODIGO, TRIM(DE1TMA) DESCRIP')
			->from('TABMAE')
			->where([
				'TIPTMA'=>'MEDVAD',
				'ESTTMA'=>' ',
			])
			->getAll('array');

		// Consulta parámetros PAE
		$laTempPAE = $this->oDb
			->select('CL1TMA TIPO, CL2TMA CODIGO, DE2TMA DESCRIP')
			->from('TABMAE')
			->where([
				'TIPTMA'=>'USOANTIB',
				'ESTTMA'=>'',
			])
			->orderBy('CL1TMA')
			->getAll('array');

		foreach($laTempPAE as $laReg){
			$this->aParamAntib[trim($laReg['TIPO']).trim($laReg['CODIGO'])] = trim($laReg['DESCRIP']);
		}

			// Consulta parámetros PAE
		$laTempPAE = $this->oDb
			->select('CL1TMA CODIGO, DE1TMA DESCRIP')
			->from('TABMAE')
			->where([
				'TIPTMA'=>'ESTSUMI',
				'ESTTMA'=>'',
			])
			->orderBy('CL1TMA')
			->getAll('array');

		foreach($laTempPAE as $laReg){
			$this->aParamFrm[trim($laReg['CODIGO'])] = trim($laReg['DESCRIP']);
		}
	}

}
