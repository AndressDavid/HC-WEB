<?php
namespace NUCLEO;

require_once __DIR__ . '/class.AplicacionFunciones.php';
require_once __DIR__ . '/class.Db.php';

use NUCLEO\Db;

class Facturacion
{
	public static function saldoFactura($taData)
	{
		global $goDb;
		$laParamEntrada=self::creaParam($taData);
		$laParamSalida = [
			'PRM3'	=> [\PDO::PARAM_STR, 9],
		];
		return $goDb->storedProcedure('CXCGA020CP', $laParamEntrada, $laParamSalida);
    }

	static function creaParam($taData)
	{
		return [
			'PRM1'	=> [str_pad(trim($taData['nit']), 13, '0', STR_PAD_LEFT), \PDO::PARAM_STR],
			'PRM2'	=> [str_pad(trim($taData['factura']), 8, '0', STR_PAD_LEFT), \PDO::PARAM_STR],
		];
	}
	
	/* Retorna procedimientos disminuye valor */
	public function procedimientosDisminuyeValor($tnFechaInicio=0,$tnFechaFinal=0)
	{
		$laListaConsumos=[];
		$lnValorComparar=$lnFechaAnterior=0;
		$lcPlanCompara=$lcCupsCompara='';
		if(empty($tnFechaInicio)){ return $laListaConsumos; }
		if(empty($tnFechaFinal)){ return $laListaConsumos; }
		$lcTipoConsumo='400';
		$lnEstadoConsumo=5;
		$lcNivelCups='0';
		$lcCoberturaCups='S';

		global $goDb;
		//	NIVEL 0
		$laData=$goDb
			->select('TRIM(A.PLAEST) PLAN')
			->select('(SELECT TRIM(DSCCON) FROM FACPLNC WHERE PLNCON=A.PLAEST) AS DESCRIPCION_PLAN')
			->select("CASE WHEN TRIM(A.ELEEST)<>'' THEN A.ELEEST ELSE A.CUPEST END PROCEDIMIENTO")
			->select("CASE WHEN TRIM(A.ELEEST)<>'' THEN IFNULL((SELECT TRIM(RF1CUP) FROM RIACUP WHERE CODCUP=A.ELEEST), '') ELSE IFNULL((SELECT TRIM(RF1CUP) FROM RIACUP WHERE CODCUP=A.CUPEST), '') END AS CAPITULO")
			->select("CASE WHEN TRIM(A.ELEEST)<>'' THEN IFNULL((SELECT TRIM(DESCUP) FROM RIACUP WHERE CODCUP=A.ELEEST), '') ELSE IFNULL((SELECT TRIM(DESCUP) FROM RIACUP WHERE CODCUP=A.CUPEST), '') END AS DESCRIPCION_CUPS")
			->select('A.FINEST FECHA')
			->select('A.HINEST HORA')
			->select('INT(A.VUNEST) VALOR_UNITARIO')
			->select('A.INGEST INGRESO')
			->select('A.CVLEST NROVALE')
			->select("IFNULL((SELECT TRIM(NPREST) FROM RIAESTM WHERE INGEST=A.INGEST AND CVLEST=A.CVLEST AND NPREST='1' ORDER BY NPREST DESC FETCH FIRST 1 ROW ONLY), '') AS NIVEL1")
			->from('RIAESTM A')
			->where('A.TINEST', '=', $lcTipoConsumo)
			->where('A.ESFEST', '<>', $lnEstadoConsumo)
			->where('A.NPREST', '=', $lcNivelCups)
			->where('A.CCBEST', '=', $lcCoberturaCups)
			->where('A.VUNEST', '>', 0)
			->between('A.FINEST', $tnFechaInicio, $tnFechaFinal)
			->groupBy('A.PLAEST, A.CUPEST, A.ELEEST, A.FINEST, A.HINEST, A.VUNEST, A.INGEST, A.CVLEST')
			->orderBy('A.PLAEST, A.CUPEST, A.ELEEST, A.FINEST DESC, A.HINEST DESC, A.VUNEST')
			->getAll('array');
		
		if(is_array($laData)){
			foreach($laData as $lnKey=>$laConsumo){
				$lcNivel1=trim($laConsumo['NIVEL1']);
				$lnIngreso=$laConsumo['INGRESO'];
				$lcCups=trim($laConsumo['PROCEDIMIENTO']);
				$lcDescripcionCups=trim($laConsumo['DESCRIPCION_CUPS']);
				$lcPlan=trim($laConsumo['PLAN']);
				$lcDescripcionPlan=trim($laConsumo['DESCRIPCION_PLAN']);
				$lcCapituloCups=trim($laConsumo['CAPITULO']);
				$lnValorUnitario=intval($laConsumo['VALOR_UNITARIO']);
				$lnFechaGrabacion=intval($laConsumo['FECHA']);
				
				if (empty($lcNivel1)){
					if ($lnValorComparar>0 && $lnValorUnitario>$lnValorComparar && $lcPlan==$lcPlanCompara && $lcCups==$lcCupsCompara){
						$laListaConsumos[]=[
							'INGRESO'=> $lnIngreso,
							'PLAN'=> $lcPlan,
							'DESCRIPCION_PLAN'=> $lcDescripcionPlan,
							'CAPITULO_CUPS'=> $lcCapituloCups,
							'PROCEDIMIENTO'=> $lcCups,
							'DESCRIPCION_PROCEDIMIENTO'=> $lcDescripcionCups,
							'FECHAGRABACION'=> $lnFechaGrabacion,
							'VALORINICIAL'=> $lnValorUnitario,
							'FECHACOMPARACION'=> $lnFechaAnterior,
							'VALORCOMPARACION'=> $lnValorComparar,
							'NIVELCUP'=> '0',
						];
					}
					$lnValorComparar=$lnValorUnitario;
					$lcPlanCompara=$lcPlan;
					$lcCupsCompara=$lcCups;
					$lnFechaAnterior=$lnFechaGrabacion;
				}
			}
		}

		//	NIVEL 1
		$lcNivelCups='1';
		$lnValorComparar=$lnFechaAnterior=0;
		$lcPlanCompara=$lcCupsCompara='';
		$laData=$goDb
			->select('TRIM(A.PLAEST) PLAN')
			->select('(SELECT TRIM(DSCCON) FROM FACPLNC WHERE PLNCON=A.PLAEST) AS DESCRIPCION_PLAN')
			->select('TRIM(A.ELEEST) PROCEDIMIENTO')
			->select('(SELECT TRIM(DESCUP) FROM RIACUP WHERE CODCUP=A.ELEEST) AS DESCRIPCION_CUPS')
			->select('(SELECT TRIM(RF1CUP) FROM RIACUP WHERE CODCUP=A.ELEEST) AS CAPITULO')
			->select('A.FINEST FECHA')
			->select('A.HINEST HORA')
			->select('INT(A.VUNEST) VALOR_UNITARIO')
			->select('A.INGEST INGRESO')
			->from('RIAESTM A')
			->where('A.TINEST', '=', $lcTipoConsumo)
			->where('A.ESFEST', '<>', $lnEstadoConsumo)
			->where('A.NPREST', '=', $lcNivelCups)
			->where('A.CCBEST', '=', $lcCoberturaCups)
			->where('A.VUNEST', '>', 0)
			->between('A.FINEST', $tnFechaInicio, $tnFechaFinal)
			->groupBy('A.PLAEST, A.ELEEST, A.FINEST, A.HINEST, A.VUNEST, A.INGEST')
			->orderBy('A.PLAEST, A.ELEEST, A.FINEST DESC, A.HINEST DESC, A.VUNEST')
			->getAll('array');
		if(is_array($laData)){
			foreach($laData as $lnKey=>$laConsumo){
				$lnIngreso=$laConsumo['INGRESO'];
				$lcCups=trim($laConsumo['PROCEDIMIENTO']);
				$lcDescripcionCups=trim($laConsumo['DESCRIPCION_CUPS']);
				$lcPlan=trim($laConsumo['PLAN']);
				$lcDescripcionPlan=trim($laConsumo['DESCRIPCION_PLAN']);
				$lcCapituloCups=trim($laConsumo['CAPITULO']);
				$lnValorUnitario=intval($laConsumo['VALOR_UNITARIO']);
				$lnFechaGrabacion=intval($laConsumo['FECHA']);

				if ($lnValorComparar>0 && $lnValorUnitario>$lnValorComparar && $lcPlan==$lcPlanCompara && $lcCups==$lcCupsCompara){
					$laListaConsumos[]=[
						'INGRESO'=> $lnIngreso,
						'PLAN'=> $lcPlan,
						'DESCRIPCION_PLAN'=> $lcDescripcionPlan,
						'CAPITULO_CUPS'=> $lcCapituloCups,
						'PROCEDIMIENTO'=> $lcCups,
						'DESCRIPCION_PROCEDIMIENTO'=> $lcDescripcionCups,
						'FECHAGRABACION'=> $lnFechaGrabacion,
						'VALORINICIAL'=> $lnValorUnitario,
						'FECHACOMPARACION'=> $lnFechaAnterior,
						'VALORCOMPARACION'=> $lnValorComparar,
						'NIVELCUP'=> '1',
					];
				}
				$lnValorComparar=$lnValorUnitario;
				$lcPlanCompara=$lcPlan;
				$lcCupsCompara=$lcCups;
				$lnFechaAnterior=$lnFechaGrabacion;
			}
		}			
		return $laListaConsumos;
	}

	/* Retorna elementos/medicamentos disminuye valor */
	public function productoDisminuyeValor($tnFechaInicio=0,$tnFechaFinal=0)
	{
		$laListaConsumos=[];
		$lnValorComparar=$lnFechaAnterior=0;
		$lcPlanCompara=$lcConsumoCompara=$lcReguladoAnterior='';
		if(empty($tnFechaInicio)){ return $laListaConsumos; }
		if(empty($tnFechaFinal)){ return $laListaConsumos; }
		$lnEstadoConsumo=5;
		$lcNivelCups='0';
		$lcCoberturaCups='S';

		global $goDb;
		$laData=$goDb
			->select('TRIM(A.PLAEST) PLAN')
			->select('(SELECT TRIM(DSCCON) FROM FACPLNC WHERE PLNCON=A.PLAEST) AS DESCRIPCION_PLAN')
			->select('A.TINEST TIPO_CONSUMO')
			->select("(SELECT TRIM(SUBSTR(TRIM(DE2TMA), 1, 30)) FROM TABMAE WHERE TIPTMA='DATING' AND CL1TMA='TIPCONS' AND CL2TMA=A.TINEST) AS DESCR_TIPO_CONSUMO")
			->select('A.ELEEST CONSUMO')
			->select("(SELECT TRIM(DESDES) FROM INVDES WHERE REFDES=A.ELEEST) AS DESCRIPCION_CONSUMO")
			->select('A.FINEST FECHA')
			->select('A.HINEST HORA')
			->select('A.VUNEST VALOR_UNITARIO')
			->select('A.INGEST INGRESO')
			->select("(SELECT TRIM(REFRGL) FROM INVPRERGL WHERE REFRGL=A.ELEEST AND (A.FINEST BETWEEN FEXRGL AND FVERGL) ORDER BY REFRGL DESC FETCH FIRST 1 ROW ONLY) AS MEDREGULADO")
			->from('RIAESTM A')
			->where('A.ESFEST', '<>', $lnEstadoConsumo)
			->where('A.NPREST', '=', $lcNivelCups)
			->where('A.CCBEST', '=', $lcCoberturaCups)
			->where('A.ELEEST', '<>', '')
			->where('A.VUNEST', '>', 0)
			->in('A.TINEST', ['500', '600'])
			->between('A.FINEST', $tnFechaInicio, $tnFechaFinal)
			->groupBy('A.PLAEST, A.TINEST, A.ELEEST, A.FINEST, A.HINEST, A.VUNEST, A.INGEST')
			->orderBy('A.PLAEST, A.TINEST, A.ELEEST, A.FINEST DESC, A.HINEST DESC, A.VUNEST')
			->getAll('array');
		
		if(is_array($laData)){
			foreach($laData as $lnKey=>$laConsumo){
				$lnIngreso=$laConsumo['INGRESO'];
				$lcPlan=trim($laConsumo['PLAN']);
				$lcTipoConsumo=trim($laConsumo['TIPO_CONSUMO']);
				$lcDescripcionPlan=trim($laConsumo['DESCRIPCION_PLAN']);
				$lcCodigoConsumo=trim($laConsumo['CONSUMO']);
				$lcDescripcionConsumo=trim($laConsumo['DESCRIPCION_CONSUMO']);
				$lcRegulado=trim($laConsumo['MEDREGULADO']);
				$lnFechaGrabacion=intval($laConsumo['FECHA']);
				$lnValorUnitario=intval($laConsumo['VALOR_UNITARIO']);
				
				if ($lnValorComparar>0 && $lnValorUnitario>$lnValorComparar && $lcPlan==$lcPlanCompara && $lcCodigoConsumo==$lcConsumoCompara){
					$laListaConsumos[]=[
						'INGRESO'=> $lnIngreso,
						'PLAN'=> $lcPlan,
						'DESCRIPCION_PLAN'=> $lcDescripcionPlan,
						'TIPO_CONSUMO'=> $lcTipoConsumo,
						'CONSUMO'=> $lcCodigoConsumo,
						'DESCRIPCION_CONSUMO'=> $lcDescripcionConsumo,
						'FECHAGRABACION'=> $lnFechaGrabacion,
						'VALORINICIAL'=> $lnValorUnitario,
						'FECHACOMPARACION'=> $lnFechaAnterior,
						'VALORCOMPARACION'=> $lnValorComparar,
						'REGULADO'=> $lcReguladoAnterior,
					];
				}
				$lnValorComparar=$lnValorUnitario;
				$lcPlanCompara=$lcPlan;
				$lcConsumoCompara=$lcCodigoConsumo;
				$lnFechaAnterior=$lnFechaGrabacion;
				$lcReguladoAnterior=!empty($lcRegulado)?'R':'';
			}
		}
		return $laListaConsumos;
	}	
	
	/* Retorna parametrización facturación duplicada  */
	public function parametrizacionDuplicada($tnNit=0,$tnFechaVigencia=0)
	{
		$laListaDuplicados=[];
		if(empty($tnNit)){ return $laListaDuplicados; }
		if(empty($tnFechaVigencia)){ return $laListaDuplicados; }

		global $goDb;
		$laData=$goDb
			->select('B.NI1CON, A.PLNTRP, TRIM(B.DSCCON) DESCPLAN, A.TIPTRP, A.AUXTRP')
			->select('(SELECT TRIM(G.DESCUP) FROM RIACUP AS G WHERE G.CODCUP=A.AUXTRP FETCH FIRST 1 ROWS ONLY) AS DESCRIPCIONCUPS')
			->select('A.RF1TRP, A.RF2TRP, A.RF3TRP, A.RF4TRP, A.SECTRP, A.VIATRP, COUNT(*) CANTIDAD')
			->from('FACTRPL A')
			->leftJoin("FACPLNC B", "A.PLNTRP=B.PLNCON", null)
			->where('B.NI1CON', '=', $tnNit)
			->where('A.FHHTRP', '>=', $tnFechaVigencia)
			->where('A.STSTRP', '=', '0')
			->groupBy('B.NI1CON, A.PLNTRP, TRIM(B.DSCCON), A.TIPTRP, A.AUXTRP, A.RF1TRP, A.RF2TRP, A.RF3TRP, A.RF4TRP, A.SECTRP, A.VIATRP')
			->orderBy('B.NI1CON, A.PLNTRP, A.TIPTRP, A.AUXTRP, A.RF1TRP, A.RF2TRP, A.RF3TRP, A.RF4TRP, A.SECTRP, A.VIATRP')
			->getAll('array');
		if(is_array($laData)){
			foreach($laData as $lnKey=>$laConsumo){
				$lnCantidad=intval($laConsumo['CANTIDAD']);
				
				if ($lnCantidad>1){
					$lcPlan=trim($laConsumo['PLNTRP']);
					$lcDescripcionPlan=trim($laConsumo['DESCPLAN']);
					$lcTipoConsumo=trim($laConsumo['TIPTRP']);
					$lcCodigoConsumo=trim($laConsumo['AUXTRP']);
					$lcDescripcionConsumo=trim($laConsumo['DESCRIPCIONCUPS']);
					$lcReferencia1=trim($laConsumo['RF1TRP']);
					$lcReferencia2=trim($laConsumo['RF2TRP']);
					$lcReferencia3=trim($laConsumo['RF3TRP']);
					$lcReferencia4=trim($laConsumo['RF4TRP']);
					$lcSeccion=trim($laConsumo['SECTRP']);
					$lcVia=trim($laConsumo['VIATRP']);

					$laListaDuplicados[]=[
						'PLAN'=> $lcPlan,
						'DESCRIPCION_PLAN'=> $lcDescripcionPlan,
						'TIPO_CONSUMO'=> $lcTipoConsumo,
						'CONSUMO'=> $lcCodigoConsumo,
						'DESCRIPCION_CONSUMO'=> $lcDescripcionConsumo,
						'REFERENCIA1'=> $lcReferencia1,
						'REFERENCIA2'=> $lcReferencia2,
						'REFERENCIA3'=> $lcReferencia3,
						'REFERENCIA4'=> $lcReferencia4,
						'VIA'=> $lcVia,
						'SECCION'=> $lcSeccion,
					];
				}	
				
			}
		}	
		return $laListaDuplicados;
	}
	
	/* Retorna facturas con saldo */
	public function facturasConSaldo($tnFechaInicio=0,$tnFechaFinal=0)
	{
		$laListaFacturas = [];
		if(empty($tnFechaInicio)){ return $laListaFacturas; }
		if(empty($tnFechaFinal)){ return $laListaFacturas; }

		global $goDb;
		$laData = $goDb
			->select('A.FRACAB FACTURA, A.NITCAB NIT')
			->from('FACCABF A')
			->leftJoin("FACPLNC B", "A.PLNCAB=B.PLNCON", null)
			->where("A.MA1CAB<>'A'")
			->where("B.NI1CON<>860006656")
			->between('A.FEFCAB', $tnFechaInicio, $tnFechaFinal)
			->orderBy('A.FRACAB')
			->getAll('array');
			
		if(is_array($laData)){
			$lnNum=-1;
			foreach($laData as $lnKey=>$laFactura){
				$laFactura=array_map('trim',$laFactura);
				$lnFactura=$laFactura['FACTURA'];
				$lnNit=$laFactura['NIT'];
				$lnSaldoFactura=0;
				$laData = [ 'nit'     => $lnNit, 'factura' => $lnFactura, 'saldo'   => 0, ];
				$laRetorno = self::saldoFactura($laData);
				$lnSaldoFactura=intval($laRetorno['PRM3']);

				if ($lnSaldoFactura>0){
					$lnNum++;
					$laDatosFactura = $goDb
						->select('A.INGCAB INGRESO, A.FEFCAB FECHA_FACTURA, INT(A.VAFCAB) VALOR_FACTURA, TRIM(A.PLNCAB) PLAN')
						->select('B.FEIING FECHA_INGRESO, B.FEEING FECHA_EGRESO')
						->select('(SELECT TRIM(DESVIA) FROM RIAVIA WHERE CODVIA=B.VIAING) AS VIA_INGRESO')
						->select('(SELECT TRIM(DSCCON) FROM FACPLNC WHERE PLNCON=A.PLNCAB) AS DESCRIPCION_PLAN')
						->select('(SELECT TRIM(TE1SOC) FROM PRMTE1 WHERE TE1COD=DIGITS(A.NITCAB)) AS DESCRIPCION_NIT')
						->select('(SELECT TRIM(DESESTA) FROM AMESTA WHERE CODESTA=C.ESTFAC) AS ENVIADO_RECIBIDO')
						->select('(SELECT TRIM(DESESTA) FROM AMESTA WHERE CODESTA=C.ESGFAC) AS ESTADO_GENERAL')
						->select('TRIM(C.USRR2FAC) USUARIO_RECEPTOR, TRIM(C.USRR3FAC) USUARIO_RESPONSABLE')
						->select('(SELECT UPPER(TRIM(NNOMED)||\' \'||TRIM(NOMMED)) FROM RIARGMN WHERE USUARI=C.USRR2FAC) AS NOMBRE_USUARIO_RECEPTOR')
						->select('(SELECT UPPER(TRIM(NNOMED)||\' \'||TRIM(NOMMED)) FROM RIARGMN WHERE USUARI=C.USRR3FAC) AS NOMBRE_USUARIO_RESPONSABLE')
						->select('(SELECT MAX(FECFACD) FROM AMFACD WHERE FRAFACD=A.FRACAB) AS FECHA_ULTIMO_MOVIMIENTO')
						->from('FACCABF A')
						->leftJoin("RIAING B", "A.INGCAB=B.NIGING", null)
						->leftJoin("AMFAC C", "A.FRACAB=C.FRAFAC", null)
						->where('A.FRACAB', '=', $lnFactura)
						->get('array');
					
					$laListaFacturas[$lnNum]=[
							'FACTURA'=> $lnFactura,
							'INGRESO'=> $laDatosFactura['INGRESO'],
							'FECHA_INGRESO'=> $laDatosFactura['FECHA_INGRESO'],
							'FECHA_EGRESO'=> $laDatosFactura['FECHA_EGRESO'],
							'VIA_INGRESO'=> $laDatosFactura['VIA_INGRESO'],
							'FECHA_FACTURA'=> $laDatosFactura['FECHA_FACTURA'],
							'VALOR_FACTURA'=> $laDatosFactura['VALOR_FACTURA'],
							'PLAN_FACTURA'=> $laDatosFactura['PLAN'],
							'DESCRIPCION_PLAN_FACTURA'=> $laDatosFactura['DESCRIPCION_PLAN'],
							'NIT'=> $lnNit,
							'DESCRIPCION_NIT'=> $laDatosFactura['DESCRIPCION_NIT'],
							'ENVIADO_RECIBIDO'=> $laDatosFactura['ENVIADO_RECIBIDO'],
							'ESTADO_GENERAL'=> $laDatosFactura['ESTADO_GENERAL'],
							'USUARIO_RECEPTOR'=> $laDatosFactura['USUARIO_RECEPTOR'],
							'NOMBRE_USUARIO_RECEPTOR'=> $laDatosFactura['NOMBRE_USUARIO_RECEPTOR'],
							'USUARIO_RESPONSABLE'=> $laDatosFactura['USUARIO_RESPONSABLE'],
							'NOMBRE_USUARIO_RESPONSABLE	'=> $laDatosFactura['NOMBRE_USUARIO_RESPONSABLE'],
							'SALDO_FACTURA'=> $lnSaldoFactura,
							'FECHA_ULTIMO_MOVIMIENTO'=> $laDatosFactura['FECHA_ULTIMO_MOVIMIENTO'],
						];
				}		
			}
		}
		return $laListaFacturas;
	}
	
	public function consultaConsumos($tnIngreso=0)
	{
		global $goDb;
		$laDatosConsumos=[];
		if ($tnIngreso>0){
			$laDatosConsumos = $goDb
				->select('A.INGEST, A.CNSEST, A.TINEST, TRIM(A.CUPEST) CODIGO_CUPS, TRIM(A.VNGEST) VIAINGRESO')
				->select('TRIM(A.RF4EST) REFERENCIA4, TRIM(A.RF5EST) REFERENCIA5, TRIM(A.CCBEST) COBRABLE, A.CVLEST NUMVALE')
				->select('INT(A.QCOEST) CANTIDAD, INT(A.VPREST) VALOR')
				->select('(SELECT TRIM(DESVIA) FROM RIAVIA WHERE CODVIA=A.VNGEST) AS DESC_VIAINGRESO')
				->select('(SELECT TRIM(DESCUP) FROM RIACUP WHERE CODCUP=A.CUPEST) AS DESCRIPCION_CUPS')
				->select('(SELECT TRIM(DE2TMA) FROM TABMAE WHERE TIPTMA=\'DATING\' AND CL1TMA=\'TIPCONS\' AND CL2TMA=A.TINEST) AS TIPOCONSUMO')
				->select('(SELECT CVLEST FROM RIAESTM WHERE INGEST=A.INGEST AND CUPEST=A.CUPEST AND CVLEST=A.CVLEST AND NPREST=\'1\' AND ESFEST<>\'5\' AND NFAEST=\' \' FETCH FIRST 1 ROWS ONLY) AS MARCA_VALE')
				->from('RIAESTM A')
				->where('A.INGEST', '=', $tnIngreso)
				->where('A.NPREST', '=', '0')
				->where('A.ESFEST', '<>', 5)
				->where('A.NFAEST', '=', '')
				->orderBy('A.TINEST, A.CUPEST, A.ELEEST')
				->getAll('array');
		}		
		return $laDatosConsumos;
	}

	public function consultaDetalleConsumo($tnIngreso=0,$tnVale=0)
	{
		global $goDb;
		$laDatosConsumos=[];

		if ($tnIngreso>0){
			$laDatosConsumos = $goDb
				->select('A.INGEST, A.CNSEST CONSECUTIVO, A.TINEST, TRIM(A.CUPEST) CODIGO_CUPS, TRIM(A.VNGEST) VIAINGRESO')
				->select('A.CVLEST NUMVALE, TRIM(A.ELEEST) AUXILIAR, INT(A.QCOEST) CANTIDAD, INT(A.VPREST) VALOR, A.VD2EST VALOR_LIBRE')
				->select('A.CCBEST COBRABLE')
				->select('(SELECT TRIM(DESVIA) FROM RIAVIA WHERE CODVIA=A.VNGEST) AS DESC_VIAINGRESO')
				->select('(SELECT TRIM(DESCUP) FROM RIACUP WHERE CODCUP=A.ELEEST) AS DESCRIPCION_ELEMENTO')
				->select('(SELECT TRIM(DE2TMA) FROM TABMAE WHERE TIPTMA=\'DATING\' AND CL1TMA=\'TIPCONS\' AND CL2TMA=A.TINEST) AS TIPOCONSUMO')
				->from('RIAESTM A')
				->where('A.INGEST', '=', $tnIngreso)
				->where('A.CVLEST', '=', $tnVale)
				->where('A.TINEST', '=', '400')
				->where('A.NPREST', '=', '1')
				->where('A.ESFEST', '<>', 5)
				->where('A.NFAEST', '=', '')
				->orderBy('A.CUPEST, A.ELEEST')
				->getAll('array');
		}
		return $laDatosConsumos;
	}
	
	public function actualizarDetalleSubnivel($taDatos=[])
	{
		global $goDb;
		$laDatosConsumos=[];
		$lnIngreso=isset($taDatos['INGEST'])?intval($taDatos['INGEST']):0;
		$lnConsecutivo=isset($taDatos['CONSECUTIVO'])?intval($taDatos['CONSECUTIVO']):0;
		$lcTipoConsumo=isset($taDatos['TINEST'])?$taDatos['TINEST']:'';
		$lcCodigoCups=isset($taDatos['CODIGO_CUPS'])?$taDatos['CODIGO_CUPS']:'';
		$lnNroVale=isset($taDatos['NUMVALE'])?intval($taDatos['NUMVALE']):0;
		$lcCodigoAuxiliar=isset($taDatos['AUXILIAR'])?$taDatos['AUXILIAR']:'';
		$lnCantidad=isset($taDatos['CANTIDAD'])?intval($taDatos['CANTIDAD']):0;
		$lnValorLibre=isset($taDatos['VALOR_LIBRE'])?intval(round($taDatos['VALOR_LIBRE'], 0)):0;
		$lcCobrable=isset($taDatos['COBRABLE'])?$taDatos['COBRABLE']:'';
		$lcCobrable=$lcCobrable=='S'?'N':'S';
		$lnValorUnitario=$lcCobrable=='N'?0:$lnValorLibre;
		$lnValorPreliquidacion=$lcCobrable=='N'?0:$lnValorLibre*$lnCantidad;
  
		if ($lnIngreso>0 && $lnConsecutivo>0){
			$ltAhora=new \DateTime( $goDb->fechaHoraSistema() );
			$lcUsuCre=(isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getUsuario():'');
			$lcPrgCre='ACTSUBWEB';
			$lcFecCre=$ltAhora->format('Ymd');
			$lcHorCre=$ltAhora->format('His');
			
			$laConsumo = $goDb
					->select('INGEST')
					->from('RIAESTM')
					->where('INGEST', '=', $lnIngreso)
					->where('CNSEST', '=', $lnConsecutivo)
					->getAll('array');
			if($goDb->numRows()>0){
				
				$lcTabla = 'RIAESTM';
				$laDatos = [
					'VUNEST'=>$lnValorUnitario,
					'VPREST'=>$lnValorPreliquidacion,
					'VLIEST'=>$lnValorPreliquidacion,
					'CCBEST'=>$lcCobrable,
					'UMOEST'=>$lcUsuCre,
					'PMOEST'=>$lcPrgCre,
					'FMOEST'=>$lcFecCre,
					'HMOEST'=>$lcHorCre,
				];
				$llResultado = $goDb->tabla($lcTabla)
								->where('INGEST', '=', $lnIngreso)
								->where('CNSEST', '=', $lnConsecutivo)
								->where('CVLEST', '=', $lnNroVale)
								->where('TINEST', '=', $lcTipoConsumo)
								->where('NPREST', '=', '1')
								->where('ELEEST', '=', $lcCodigoAuxiliar)
								->actualizar($laDatos);

				//	ACTUALIZA CABECERA
				$laConsumoTotal = $goDb
					->select('SUM(VPREST) SUMASUB')
					->from('RIAESTM')
					->where('INGEST', '=', $lnIngreso)
					->where('CVLEST', '=', $lnNroVale)
					->where('TINEST', '=', $lcTipoConsumo)
					->where('CUPEST', '=', $lcCodigoCups)
					->where('NPREST', '=', '1')
					->get('array');
				if($goDb->numRows()>0){
					$lnValorTotal=$laConsumoTotal['SUMASUB'];
					$laDatos = [
						'VPREST'=>$lnValorTotal,
						'VLIEST'=>$lnValorTotal,
						'UMOEST'=>$lcUsuCre,
						'PMOEST'=>$lcPrgCre,
						'FMOEST'=>$lcFecCre,
						'HMOEST'=>$lcHorCre,
					];
					$llResultado = $goDb->tabla($lcTabla)
									->where('INGEST', '=', $lnIngreso)
									->where('CVLEST', '=', $lnNroVale)
									->where('TINEST', '=', $lcTipoConsumo)
									->where('NPREST', '=', '0')
									->where('CUPEST', '=', $lcCodigoCups)
									->actualizar($laDatos);
				}
			}	
		}	
		return $laDatosConsumos;
	}

}
