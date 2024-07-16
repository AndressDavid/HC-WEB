<?php
namespace NUCLEO;

require_once __DIR__ . '/class.AplicacionFunciones.php';


class FuncionesInv
{

	/*
	 *	Retorna el CUM y Registro Invima de un medicamento, se tiene en cuenta el homólogo si existe
	 *
	 * @param integer $tnIngreso: Número de ingreso
	 * @param string $tcCodMed: Código Shaio del medicamento
	 * @param integer $tnCnsCons: Consecutivo de consumo
	 * @param boolean $tlConvoca: Busca CUM en la convocatoria si es true
	 * @param boolean $tlInvDes: Busca CUM en el maestro de medicamentos si es true
	 * @return array con CUM e INVIMA
	 */
	public static function fObtenerCUM($tnIngreso=0, $tcCodMed='', $tnCnsCons=0, $tlConvoca=false, $tlInvDes=false)
	{
		$laReturn = self::fObtenerCUMMov($tnIngreso, $tcCodMed, $tnCnsCons, $tlConvoca, $tlInvDes);
		if(!empty($laReturn['CUM'])){
			$lcHomologoCUM = self::fObtenerHomologoCUM($tnIngreso, $laReturn['CUM']);
			if(!empty($lcHomologoCUM)){ $laReturn['CUM']=$lcHomologoCUM; }
		}

		return $laReturn;
	}


	/*
	 *	Retorna el CUM y Registro Invima de un medicamento a partir de su movimiento
	 *
	 * @param integer $tnIngreso: Número de ingreso
	 * @param string $tcCodMed: Código Shaio del medicamento
	 * @param integer $tnCnsCons: Consecutivo de consumo
	 * @param boolean $tlConvoca: Busca CUM en la convocatoria si es true
	 * @param boolean $tlInvDes: Busca CUM en el maestro de medicamentos si es true
	 * @return array con CUM e INVIMA
	 */
	public static function fObtenerCUMMov($tnIngreso=0, $tcCodMed='', $tnCnsCons=0, $tlConvoca=false, $tlInvDes=false)
	{
		if (empty($tnIngreso) || empty($tcCodMed)) return ['CUM'=>'', 'INVIMA'=>'', ];
		global $goDb;

		$lcCum = $lcInvima = $lcRta = '';
		$lnRecNo = 0;

		$lcDocSalidas  = $goDb->obtenerTabMae1('DE2TMA', 'NOPOS', ['CL1TMA'=>'CUM', 'CL2TMA'=>'DOC_SAL'], null, "'CE','CF','CG','CT'");
		$laWhere = [
			'INGRES' => $tnIngreso,
			'CODSHA' => $tcCodMed,
		];
		// Busca última salida de farmacia para el ingreso y consec de consumo 
		if ($tnCnsCons>0) {
			$laData = $goDb
				->select('CODCUM, REGSAN')
				->from('SIMNIMOV')
				->where([
					'INGRES' => $tnIngreso,
					'CODSHA' => $tcCodMed,
					'CONSEC' => $tnCnsCons,
				])
				->where("TIPDOC IN ($lcDocSalidas) AND CODCUM<>''")
				->orderBy('FECCRE', 'DESC')->orderBy('HORCRE', 'DESC')
				->get('array');
			if ($goDb->numRows()>0) {
				return [
					'CUM' => trim($laData['CODCUM']),
					'INVIMA' => trim($laData['REGSAN']),
				];
			}
		}
		// Busca última salida de farmacia para el ingreso
		$laData = $goDb
			->select('CODCUM, REGSAN')
			->from('SIMNIMOV')
			->where([
				'INGRES' => $tnIngreso,
				'CODSHA' => $tcCodMed,
			])
			->where("TIPDOC IN ($lcDocSalidas) AND CODCUM<>''")
			->orderBy('FECCRE', 'DESC')->orderBy('HORCRE', 'DESC')
			->get('array');
		if ($goDb->numRows()>0) {
			return [
				'CUM' => trim($laData['CODCUM']),
				'INVIMA' => trim($laData['REGSAN']),
			];
		}

		$lnFechaCns = 0;
		//Obtener fecha consumo por ingreso y conse de consumo
		if ($tnCnsCons>0) {
			$laData = $goDb
				->select('FINEST')
				->from('RIAESTM')
				->where([
					'INGEST' => $tnIngreso,
					'ELEEST' => $tcCodMed,
					'CNSEST' => $tnCnsCons,
				])
				->get('array');
			if ($goDb->numRows()>0) {
				$lnFechaCns = $laData['FINEST'];
			}
		}
		//Obtener fecha consumo por ingreso
		if (empty($lnFechaCns)) {
			$laData = $goDb
				->select('FINEST')
				->from('RIAESTM')
				->where([
					'INGEST' => $tnIngreso,
					'ELEEST' => $tcCodMed,
				])
				->get('array');
			if ($goDb->numRows()>0) {
				$lnFechaCns = $laData['FINEST'];
			}
		}
		if (empty($lnFechaCns)) {
			// Obtener fecha egreso
			$laData = $goDb->select('FEEING')->from('RIAING')->where(['niging'=>$tnIngreso])->get('array');
			if ($goDb->numRows()>0) {
				$lnFechaCns = $laData['FEEING'];
			}
		}


		// Busca última salida de farmacia para cualquier ingreso
		$lnDias = $goDb->obtenerTabMae1('OP3TMA', 'NOPOS', ['CL1TMA'=>'CUM', 'CL2TMA'=>'DIAS_SAL'], null, 0);
		if ($lnFechaCns>0) {
			$ldFechaCns = \DateTime::createFromFormat('Ymd', $lnFechaCns.'');
			$lcCondicion = ($lnDias>0 ? ' AND feccre>'.date('Ymd',strtotime("-$lnDias day", $ldFechaCns->format("Ymd"))) : '') . ' AND feccre<='.$lnFechaCns;
		} else {
			$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
			$lcFecha = $ltAhora->format("Y-m-d");
			$lcCondicion = $lnDias>0 ? ' AND feccre>'.date('Ymd',strtotime("-$lnDias day", $lcFecha)) : '';
		}

		$laData = $goDb
			->select('CODCUM, REGSAN')
			->from('SIMNIMOV')
			->where("TIPDOC IN ($lcDocSalidas) AND CODSHA='$tcCodMed' AND CODCUM<>'' $lcCondicion")
			->orderBy('FECCRE', 'DESC')->orderBy('HORCRE', 'DESC')
			->get('array');
		if ($goDb->numRows()>0) {
			return [
				'CUM' => trim($laData['CODCUM']),
				'INVIMA' => trim($laData['REGSAN']),
			];
		}


		// Busca entradas del medicamento
		$lcDocEntradas = $goDb->obtenerTabMae1('DE2TMA', 'NOPOS', ['CL1TMA'=>'CUM', 'CL2TMA'=>'DOC_ENT'], null, "'EC','EN','EM'");
		$lnDias = $goDb->obtenerTabMae1('OP3TMA', 'NOPOS', ['CL1TMA'=>'CUM', 'CL2TMA'=>'DIAS_ENT'], null, 0);
		if ($lnFechaCns>0) {
			$ldFechaCns = \DateTime::createFromFormat('Ymd', $lnFechaCns.'');
			$lcCondicion = ($lnDias>0 ? ' AND feccre>'.date('Ymd',strtotime("-$lnDias day", $ldFechaCns->format("Ymd"))) : '') . ' AND feccre<='.$lnFechaCns;
		} else {
			$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
			$lcFecha = $ltAhora->format("Y-m-d");
			$lcCondicion = $lnDias>0 ? ' AND feccre>'.date('Ymd',strtotime("-$lnDias day", $lcFecha)) : '';
		}
		
		$laData = $goDb
			->select('CODCUM, REGSAN')
			->from('SIMNIMOV')
			->where("TIPDOC IN ($lcDocEntradas) AND CODSHA='$tcCodMed' AND CODCUM<>'' $lcCondicion")
			->orderBy('FECCRE', 'DESC')->orderBy('HORCRE', 'DESC')
			->get('array');
		if ($goDb->numRows()>0) {
			return [
				'CUM' => trim($laData['CODCUM']),
				'INVIMA' => trim($laData['REGSAN']),
			];
		}


		// Usa CUM de convocatoria
		if ($tlConvoca) {
			// Falta consulta a la convocatoria
		}


		// Usa el CUM de INVDES
		if ($tlInvDes) {
			$laData = $goDb
				->select('CBADES')
				->from('INVDES')
				->where(['REFDES'=>$tcCodMed])
				->get('array');
			if ($goDb->numRows()>0) {
				return [
					'CUM' => trim($laData['CBADES']),
					'INVIMA' => '',
				];
			}
		}

		return ['CUM'=>'', 'INVIMA'=>'', ];
	}


	/*
	 *	Busca y retorna el homólogo del CUM, para un ingreso y CUM. Si no existe retorna string vacío
	 *
	 * @param integer $tnIngreso: Número de ingreso
	 * @param string $tcCUM: Código CUM a validar
	 * @return string CUM homólogo si se encuentra o vacío si no lo hay
	 */
	public static function fObtenerHomologoCUM($tnIngreso=0, $tcCUM='')
	{
		global $goDb;
		$lcReturn = '';
		if(empty($tnIngreso) || empty($tcCUM)){return $lcReturn;}

		$laData = $goDb
			->select('TRIM(CUNCHO) AS CUM_HOMOLOGO')
			->from('CUMSHO')
			->where(['INGCHO'=>$tnIngreso, 'CUMCHO'=>$tcCUM])
			->where('ESTCHO=\'\'')
			->get('array');
		if ($goDb->numRows()>0) {
			$lcReturn = $laData['CUM_HOMOLOGO'] ?? '';
		}
		return $lcReturn;
	}


	/*
	 *	Retorna listado de consumos de medicamentos y procedimientos NoPOS por ingreso
	 *	si existen procedimientos también obtiene la lista de elementos
	 *
	 * @param integer $tnIngreso: Número de ingreso
	 * @param string $tcInsumos: TODOS, CIRUGIAS o NOPOS (NOPOS = de cirugías con cups NoPBS, CIRUGIAS = de todas las cirugías, TODOS = todos los insumos)
	 * @return array listado de medicamentos (código shaio, cum, descripción, cantidad, valor unidad, valor total) agrupados por código shaio, cum y valor unidad
	 */
	public static function consumosNoPosPorIngreso($tnIngreso=0, $tcInsumos='TODOS')
	{
		$laMedicamentos = self::medicamentosNoPosPorIngreso($tnIngreso);
		$laProcedimientos = self::procedimientosNoPosPorIngreso($tnIngreso);
		$laElementos = self::elementosNoPosPorIngreso($tnIngreso, $laProcedimientos, $tcInsumos);
		self::adicionarCumVacio($laProcedimientos);
		self::adicionarCumVacio($laElementos);

		return array_merge($laProcedimientos, $laMedicamentos, $laElementos);
	}
	private static function adicionarCumVacio(&$taDatos)
	{
		foreach($taDatos as $lcClave=>&$laDato){
			//$laDato['CUM']='';
			$laDato=[
				'TIPO'			=> $laDato['TIPO'],
				'CODIGO'		=> $laDato['CODIGO'],
				'CUM'			=> '',
				'DESCRIPCION'	=> $laDato['DESCRIPCION'],
				'FECHA'			=> $laDato['FECHA'],
				'CANTIDAD'		=> $laDato['CANTIDAD'],
				'VALOR_UD'		=> $laDato['VALOR_UD'],
				'TOTAL'			=> $laDato['TOTAL'],
			];
		}
	}

	/*
	 *	Retorna listado de consumos de medicamentos NoPOS por ingreso
	 *
	 * @param integer $tnIngreso: Número de ingreso
	 * @return array listado de medicamentos (código shaio, cum, descripción, cantidad, valor unidad, valor total) agrupados por código shaio, cum y valor unidad
	 */
	public static function medicamentosNoPosPorIngreso($tnIngreso=0)
	{
		$laLista = [];
		if(empty($tnIngreso)){ return $laLista; }

		global $goDb;
		$laData = $goDb
			->select('C.CNSEST, C.FINEST, C.HINEST, C.ELEEST, M.DESDES, C.QCOEST, C.VNGEST, C.VUNEST, C.VPREST')
			->from('RIAESTM C')
			->leftJoin('INVDES M','C.ELEEST=M.REFDES')
			->where(['C.INGEST'=>$tnIngreso])
			->where("C.TINEST='500' AND C.RF4EST='NOPOS' AND C.ESFEST<>5")
			->orderBy('C.ELEEST, C.FINEST, C.HINEST')
			->getAll('array');
		if(is_array($laData)){
			$lcCodTecno=$lcCodCum='';
			$lnNum=$lnValorUd=-1;
			foreach($laData as $lnKey=>$laTecno){
				$laTecno=array_map('trim',$laTecno);
				$lcCUM = self::fObtenerCUM($tnIngreso, $laTecno['ELEEST'], $laTecno['CNSEST'])['CUM'];

				if($lcCodTecno!==$laTecno['ELEEST'] || $lcCodCum!==$lcCUM || $lnValorUd!==$laTecno['VUNEST']) {
					$lnNum++;
					$laLista[$lnNum]=[
						'TIPO'			=> 'M',
						//'CONSUMO'		=> $laTecno['CNSEST'],
						'CODIGO'		=> $laTecno['ELEEST'],
						'CUM'			=> $lcCUM,
						'DESCRIPCION'	=> $laTecno['DESDES'],
						'FECHA'			=> AplicacionFunciones::formatFechaHora('fecha',$laTecno['FINEST'],'/'),
						'CANTIDAD'		=> $laTecno['QCOEST'],
						'VALOR_UD'		=> $laTecno['VUNEST'],
						'TOTAL'			=> $laTecno['VPREST'],
					];
					$lcCodTecno=$laTecno['ELEEST'];
					$lcCodCum=$lcCUM;
					$lnValorUd=$laTecno['VUNEST'];
				} else {
					//$laLista[$lnNum]['CONSUMO'].=','.$laTecno['CNSEST'];
					$laLista[$lnNum]['CANTIDAD']+=$laTecno['QCOEST'];
					$laLista[$lnNum]['TOTAL']+=$laTecno['VPREST'];
				}
			}
		}

		return $laLista;
	}

	/*
	 *	Retorna listado de consumos de procedimientos NoPOS por ingreso
	 *
	 * @param integer $tnIngreso: Número de ingreso
	 * @return array listado de procedimientos (cup, descripción, cantidad, valor unidad, valor total)
	 */
	public static function procedimientosNoPosPorIngreso($tnIngreso=0)
	{
		$laLista = [];
		if(empty($tnIngreso)){ return $laLista; }

		global $goDb;
		$laData = $goDb
			->select('C.CNSEST, C.CNCEST, C.FINEST, C.HINEST, C.CUPEST, M.DESCUP, C.QCOEST, C.VNGEST, C.VUNEST, C.VPREST')
			->from('RIAESTM C')
			->leftJoin('RIACUP M','C.CUPEST=M.CODCUP')
			->where(['C.INGEST'=>$tnIngreso])
			->where("C.NPREST='0' AND C.TINEST='400' AND C.RF5EST='NOPB' AND C.ESFEST<>5")
			->orderBy('C.CUPEST, C.FINEST, C.HINEST')
			->getAll('array');
		if(is_array($laData)){
			$lcCodTecno=$lcCodQx='';
			$lnNum=$lnValorUd=-1;
			foreach($laData as $lnKey=>$laTecno){
				$laTecno=array_map('trim',$laTecno);

				if($lcCodTecno!==$laTecno['CUPEST'] || $lcCodQx!==$laTecno['CNCEST'] || $lnValorUd!==$laTecno['VUNEST']) {
					$lnNum++;
					$laLista[$lnNum]=[
						'TIPO'			=> 'P',
						'CNSCIR'		=> $laTecno['CNCEST'],
						'CODIGO'		=> $laTecno['CUPEST'],
						'DESCRIPCION'	=> $laTecno['DESCUP'],
						'FECHA'			=> AplicacionFunciones::formatFechaHora('fecha',$laTecno['FINEST'],'/'),
						'CANTIDAD'		=> $laTecno['QCOEST'],
						'VALOR_UD'		=> $laTecno['VUNEST'],
						'TOTAL'			=> $laTecno['VPREST'],
					];
					$lcCodTecno=$laTecno['CUPEST'];
					$lcCodQx=$laTecno['CNCEST'];
					$lnValorUd=$laTecno['VUNEST'];
				} else {
					$laLista[$lnNum]['CANTIDAD']+=$laTecno['QCOEST'];
					$laLista[$lnNum]['TOTAL']+=$laTecno['VPREST'];
				}
			}
		}

		return $laLista;
	}

	/*
	 *	Retorna listado de consumos de elementos por ingreso
	 *
	 * @param integer $tnIngreso: Número de ingreso
	 * @param array $taProcedimientos: Lista de procedimientos del ingreso
	 * @param string $tcInsumos: TODOS, CIRUGIAS o NOPOS (NOPOS = de cirugías con cups NoPBS, CIRUGIAS = de todas las cirugías, TODOS = todos los insumos)
	 * @return array listado de elementos (código, descripción, cantidad, valor unidad, valor total)
	 */
	public static function elementosNoPosPorIngreso($tnIngreso=0, $taProcedimientos=[], $tcInsumos='TODOS')
	{
		$laLista = [];
		if(empty($tnIngreso) || count($taProcedimientos)==0){ return $laLista; }

		$laCnsQx = [];
		foreach($taProcedimientos as $laProc){
			if($laProc['CNSCIR']>0){
				if(!in_array($laProc['CNSCIR'],$laCnsQx)){
					$laCnsQx[]=$laProc['CNSCIR'];
				}
			}
		}
		if(count($laCnsQx)==0){ return $laLista; }

		global $goDb;
		if($tcInsumos=='NOPOS'){
			// Solo de las cirugías con cups NoPOS
			$goDb->in('CNCEST', $laCnsQx);
		} elseif ($tcInsumos=='CIRUGIAS'){
			// De todas las cirugías
			$goDb->where('CNCEST>0');
		}
		$laData = $goDb
			->select('C.CNSEST, C.FINEST, C.HINEST, C.ELEEST, M.DESDES, C.QCOEST, C.VNGEST, C.VUNEST, C.VPREST')
			->from('RIAESTM C')
			->leftJoin('INVDES M','C.ELEEST=M.REFDES')
			->where(['C.INGEST'=>$tnIngreso])
			->where("C.TINEST='600' AND C.ESFEST<>5 AND CCBEST<>'N'") // C.VPREST>0 equivale a CCBEST<>'N'
			->orderBy('C.ELEEST, C.FINEST, C.HINEST')
			->getAll('array');
		if(is_array($laData)){
			$lcCodTecno='';
			$lnNum=$lnValorUd=-1;
			foreach($laData as $lnKey=>$laTecno){
				$laTecno=array_map('trim',$laTecno);

				if($lcCodTecno!==$laTecno['ELEEST'] || $lnValorUd!==$laTecno['VUNEST']) {
					$lnNum++;
					$laLista[$lnNum]=[
						'TIPO'			=> 'E',
						'CODIGO'		=> $laTecno['ELEEST'],
						'DESCRIPCION'	=> $laTecno['DESDES'],
						'FECHA'			=> AplicacionFunciones::formatFechaHora('fecha',$laTecno['FINEST'],'/'),
						'CANTIDAD'		=> $laTecno['QCOEST'],
						'VALOR_UD'		=> $laTecno['VUNEST'],
						'TOTAL'			=> $laTecno['VPREST'],
					];
					$lcCodTecno=$laTecno['ELEEST'];
					$lnValorUd=$laTecno['VUNEST'];
				} else {
					$laLista[$lnNum]['CANTIDAD']+=$laTecno['QCOEST'];
					$laLista[$lnNum]['TOTAL']+=$laTecno['VPREST'];
				}
			}
		}

		return $laLista;
	}
	
	/*
	 *	Retorna el código IUM 
	 *
	 * @param string $tcCodCums: Código CUMS
	 * @return array con código IUM y tipo de medicamento (RIPS)
	 */
	public static function fObtenerIum($tcCodCums='')
	{
		global $goDb;
		$laDatosCums=[];
		$lcCodigoIum=$lcTipoMedicamento='';
		$laIum = $goDb
		->select('trim(IUMEDI) CODIGOIUM, trim(TIPMED) TIPOMEDICAMENTO')
		->from('SIMNCUM')
		->where('CODCUM', '=', $tcCodCums)
		->get('array');
		if ($goDb->numRows()>0) {
			$lcCodigoIum=trim($laIum['CODIGOIUM']) ?? '';
			$lcTipoMedicamento=trim($laIum['TIPOMEDICAMENTO']) ?? '';
		}	
		$laDatosCums=[			
			'codigoium'=>$lcCodigoIum,
			'tipomedicamento'=>$lcTipoMedicamento,
		];
		unset($laIum);	
		return $laDatosCums;
	}

}



