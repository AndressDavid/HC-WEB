<?php
namespace NUCLEO;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;

require_once __DIR__ . '/class.Db.php';
require_once __DIR__ . '/class.AplicacionFunciones.php';


class MiPresFunciones
{
	protected static $estado = [
		'0'=>'Anulado',
		'1'=>'Activo',
		'2'=>'Procesado',
	];
	public static $cUsuario = '';
	protected static $aPermisosN = [
		'10'=>'usarput',
		'11'=>'conregexp',
	];
	public static $aPermisos = [];


	/*  OBTIENE PERMISOS DEL USUARIO ACTUAL  */
	public static function getUsuarioPermisos()
	{
		global $goDb;

		// Permisos MiPres - NoPOS
		if (is_array(self::$aPermisos) && count(self::$aPermisos)>0) return;

		$laDatas = $goDb
			->select('TRIM(CL3TMA) AS CODIGO, TRIM(OP2TMA) AS PERMISO')
			->from('TABMAE')
			->where('TIPTMA=\'NOPOS\' AND CL1TMA=\'OPCUSU\' AND CL2TMA=\'OPCIONES\' AND ESTTMA=\'\' AND OP2TMA<>\'\'')
			->getAll('array');
		if(is_array($laDatas)){
			foreach($laDatas as $laData){
				self::$aPermisosN[$laData['CODIGO']]=$laData['PERMISO'];
			}
		}
		self::$cUsuario = $_SESSION[HCW_NAME]->oUsuario->getUsuario();
		$lcPermisos = trim($goDb->obtenerTabMae1('DE2TMA','NOPOS',['cl1tma'=>'OPCUSU','cl2tma'=>'PERMISOS','cl3tma'=>self::$cUsuario,'esttma'=>''],null,''));
		$laPermisos = explode(',',$lcPermisos);
		foreach(self::$aPermisosN as $lcKey=>$lcValor){
			if(in_array($lcKey,$laPermisos)){
				self::$aPermisos[]=$lcValor;
			}
		}
	}


	/*  CONSUMO DE MIPRES  */
	public static function fnConsumirMiPres($taVar, $tcMetodo)
	{
		global $goDb;

		$laReturn['error'] = '';
		ini_set('max_execution_time', 60*30); // 30 minutos de consulta

		$laArray = [
			'{nit}'			=> self::fcVariables('nit'),
			'{token}'		=> self::fcVariables('token'),
			'{tokentmp}'	=> self::fcVariables('tokentmp'),
			'{tokenfac}'	=> self::fcVariables('tokenfac'),
			'{tokenfactmp}'	=> self::fcVariables('tokenfactmp'),
			'{fecha}'		=> $taVar['fecha']   ?? '',
			'{tipodoc}'		=> $taVar['tipDoc']  ?? '',
			'{numdoc}'		=> $taVar['numDoc']  ?? '',
			'{NoPresc}'		=> $taVar['numPrs']  ?? '',
			'{IdAnular}'	=> $taVar['idAnula'] ?? '',
		];
		$lcUrl = str_replace(array_keys($laArray), array_values($laArray), $taVar['url']);

		require_once __DIR__ . '/class.MiPresCurlRequest.php';
		$loCurl = new MiPresCurlRequest();

		try
		{
			$params = [
				'url' => $lcUrl,
				'method' => $tcMetodo,
				'post_fields' => isset($taVar['datos']) ? $taVar['datos'] : '',
			];
			$loCurl->fnInit($params);
			$result = $loCurl->fnEjecutar();
			if ($result['curl_error'])			{ throw new \Exception($result['curl_error']); }
			if ($result['http_code']!='200')	{ throw new \Exception('HTTP Código = '.$result['http_code']); }
			if (!$result['body'])				{ throw new \Exception('Consulta MiPres no obtuvo resultado'); }

			$laReturn['MIPRES'] = json_decode($result['body']);
		}
		catch (\Exception $e)
		{
			$laReturn['error'] = $e->getMessage();
			if ($result['body']) {
				$laReturn['body'] = json_decode($result['body']);
				//if ( is_object($result['body']) ) {
				if (isset($laReturn['body']->Message)) {
					$laReturn['error'] .= ' - ' . $laReturn['body']->Message . (isset($laReturn['body']->Errors[0])? ' - ' . $laReturn['body']->Errors[0]: '');
				}
			}
		}

		// Guardar log de registros realizados por PUT
		if ( $tcMetodo=='PUT' ) {
			if ($laReturn['error']==='') {
				try {
					$loDataRet = isset($laReturn['MIPRES']['0']->Id) ? $laReturn['MIPRES'] :
							json_decode(str_replace('[{"Mensaje":"Anulación Exitosa ID: ','[{"ID":"',$result['body']));
				} finally { }
				$lnIdMiPres = isset($loDataRet['0']->Id) ? $loDataRet['0']->Id : '';	// Id principal del direccionamiento
				$lnIdTrnscc = isset($loDataRet['0']->IdProgramacion) ? $loDataRet['0']->IdProgramacion :
							( isset($loDataRet['0']->IdEntrega) ? $loDataRet['0']->IdEntrega :
							( isset($loDataRet['0']->IdReporteEntrega) ? $loDataRet['0']->IdReporteEntrega :
							( isset($loDataRet['0']->IdFacturacion) ? $loDataRet['0']->IdFacturacion :
							( isset($loDataRet['0']->ID) ? $loDataRet['0']->ID : '' ) ) ) );	// Id anulado
				if (strpos(strtoupper($lcUrl),'WSFAC')===false) {
					$lcServicio = str_replace(self::fcVariables('urlDispensar'), '', $lcUrl);
					if(substr($lcServicio,0,20)=='AnularEntregaCodigos'){
						$lcServicio='AnularEC';
					} else {
						$lcServicio = str_replace('/', '', substr($lcServicio, 0, 8));
					}
				} else {
					$lcServicio = strpos(strtoupper($lcUrl),'FACTURACIONANULAR')===false ? 'Facturac' : 'AnularFa';
				}
				$lcUsuario = $_SESSION[HCW_NAME]->oUsuario->getUsuario();
				$lcProgram = substr(pathinfo(__FILE__, PATHINFO_FILENAME),6,10);
				$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
				$lcFecha = $ltAhora->format('Ymd');
				$lcHora  = $ltAhora->format('His');

				$lcDataEnv = empty($laArray['{IdAnular}']) ?
							str_replace('\'','',var_export($taVar['datos'], true)) :
							'{IdAnular:"'.$laArray['{IdAnular}'].'"}';
				$lcDataRec = str_replace('\'','',$result['body']);

				// Guardar log de envío
				try {
					$goDb->from('MPDISPR')
						->insertar([
							//'CODIGO'=>$lnCns, // Automático AS400
							'SERVIC' => $lcServicio,
							'IDMIPR' => $lnIdMiPres,
							'IDTRNS' => $lnIdTrnscc,
							'DATENV' => $lcDataEnv,
							'DATRTA' => $lcDataRec,
							'USRCRE' => $lcUsuario,
							'PRGCRE' => $lcProgram,
							'FECCRE' => $lcFecha,
							'HORCRE' => $lcHora,
						]);
				} catch (\Exception $e) {
					$laReturn['error_bd'] = $e->getMessage();
				}

				$lcFecHoraAcc = substr(AplicacionFunciones::formatFechaHora('fechahora',$lcFecha.$lcHora),0,16);
				switch ($lcServicio) {
					case 'Programa':
						$lbInsertar = true;
						$lcTabla = 'MIPRPRO';
						$laDatos = [
							'IDENTF' => $lnIdMiPres,
							'IDPROG' => $lnIdTrnscc,
							'FECHAP' => $lcFecha,
							'FECMAX' => $taVar['form']['FecMaxEnt'],
							'CODSRV' => $taVar['form']['CodSerTecAEntregar']??'',
							'CATTOT' => $taVar['form']['CantTotAEntregar'],
							'FECPRO' => $lcFecHoraAcc,
							'ESTPRO' => 1,
							'USCPMP' => $lcUsuario,
							'PGCPMP' => $lcProgram,
							'FECPMP' => $lcFecha,
							'HOCPMP' => $lcHora,
						];
						break;
					case 'AnularPr':
						$lbInsertar = false;
						$lcTabla = 'MIPRPRO';
						$laDatos = [
									'ESTPRO' => 0,
									'FECPAN' => $lcFecHoraAcc,
									'USMPMP' => $lcUsuario,
									'PGMPMP' => $lcProgram,
									'FEMPMP' => $lcFecha,
									'HOMPMP' => $lcHora,
						];
						$laWhere = ['IDPROG' => $lnIdTrnscc,];
						break;
					case 'Entrega':
						// A PARTIR DE ID DIRECCIONAMIENTO BUSCAR DATOS DE PRESCRIPCIÓN PARA INSERTAR
						if ( !isset($taVar['fila']['NUMPRES']) ) {
							if (!isset($taVar)) { $taVar = []; }
							if (!isset($taVar['fila'])) { $taVar['fila'] = []; }
							try {
								$laDirSel = $goDb->from('MIPRDIR')->where(['IDENTF' => $lnIdMiPres])->get('array');
								if (is_array($laDirSel)) {
									if (count($laDirSel)>0) {
										$taVar['fila']['NUMPRES']  = $laDirSel['NUMPRS'];
										$taVar['fila']['TIPOTEC']  = $laDirSel['TIPTEC'];
										$taVar['fila']['CNSTEC']   = $laDirSel['CONTEC'];
										$taVar['fila']['TIPIDPAC'] = $laDirSel['TIPIDP'];
										$taVar['fila']['NUMIDPAC'] = $laDirSel['NUMIDP'];
									}
								}
							} catch (\Exception $e) {
								$laReturn['error_bd'] = $e->getMessage();
							}
						}
						// No se hace break
					case 'EntregaA':
						$lbInsertar = true;
						$lcTabla = 'MIPRENT';
						$laDatos = [
							'IDENTF' => $lnIdMiPres,
							'IDENTR' => $lnIdTrnscc,
							'FECHAE' => $lcFecha,
							'NUMPRS' => $taVar['form']['NoPrescripcion']??($taVar['fila']['NUMPRES']??''),
							'TIPTEC' => $taVar['form']['TipoTec']??($taVar['fila']['TIPOTEC']??''),
							'CONTEC' => $taVar['form']['ConTec']??($taVar['fila']['CNSTEC']??0),
							'TIPIDP' => $taVar['form']['TipoIDPaciente']??($taVar['fila']['TIPIDPAC']??''),
							'NUMIDP' => $taVar['form']['NoIDPaciente']??($taVar['fila']['NUMIDPAC']??''),
							'NUMENT' => $taVar['form']['NoEntrega']??1,
							'CODSRV' => $taVar['form']['CodSerTecEntregado']??'',
							'CATTOT' => $taVar['form']['CantTotEntregada']??0,
							'ENTTOT' => $taVar['form']['EntTotal']??0,
							'CAUSNO' => $taVar['form']['CausaNoEntrega']??0,
							'FECENT' => $taVar['form']['FecEntrega']??'',
							'NOLOTE' => $taVar['form']['NoLote']??'',
							'TIDREC' => $taVar['form']['TipoIDRecibe']??'',
							'NIDREC' => $taVar['form']['NoIDRecibe']??'',
							'ESTENT' => 1,
							'USCEMP' => $lcUsuario,
							'PGCEMP' => $lcProgram,
							'FECEMP' => $lcFecha,
							'HOCEMP' => $lcHora,
						];
						break;
					case 'EntregaC':
						if(isset($taVar['fila'])){
							self::actualizaEntCod($taVar['fila']['NUMPRES'], $taVar['fila']['IDENTREGA']);
							$laDatos = [];
						} else {
							$lcCodAdd = $result['body'];
							$lbInsertar = false;
							$lcTabla = 'MIPRENT';
							$laDatos = [
								'CODENT' => $lcCodAdd,
								'USMEMP' => $lcUsuario,
								'PGMEMP' => $lcProgram,
								'FEMEMP' => $lcFecha,
								'HOMEMP' => $lcHora,
							];
							$laWhere = ['IDENTR' => $lnIdTrnscc,];
						}
						break;
					case 'AnularEn':
						$lbInsertar = false;
						$lcTabla = 'MIPRENT';
						$laDatos = [
							'ESTENT' => 0,
							'FECEAN' => $lcFecHoraAcc,
							'USMEMP' => $lcUsuario,
							'PGMEMP' => $lcProgram,
							'FEMEMP' => $lcFecha,
							'HOMEMP' => $lcHora,
						];
						$laWhere = ['IDENTR' => $lnIdTrnscc,];
						break;
					case 'AnularEC':
						if(isset($taVar['fila'])){
							self::actualizaEntCod($taVar['fila']['NUMPRES'], $taVar['fila']['IDENTREGA']);
						}
						$laDatos = [];
						break;
					case 'ReporteE':
						if (!isset($taVar['fila']['NUMPRES'])) {
							if (!isset($taVar)) { $taVar = []; }
							if (!isset($taVar['fila'])) { $taVar['fila'] = []; }
							try {
								$laDirSel = $goDb->from('MIPRDIR')->where(['IDENTF' => $lnIdMiPres])->get('array');
								if (is_array($laDirSel)) {
									if (count($laDirSel)>0) {
										$taVar['fila']['NUMPRES']  = $laDirSel['NUMPRS'];
									}
								}
							} catch (\Exception $e) {
								$laReturn['error_bd'] = $e->getMessage();
							}
						}
						$lbInsertar = true;
						$lcTabla = 'MIPRREP';
						$laDatos = [
							'IDENTF' => $lnIdMiPres,
							'IDREPE' => $lnIdTrnscc,
							'FECHAR' => $lcFecha,
							'NUMPRS' => $taVar['fila']['NUMPRES']??'',
							'ESTENT' => $taVar['form']['EstadoEntrega'],
							'CAUSNO' => $taVar['form']['CausaNoEntrega'],
							'VALORE' => $taVar['form']['ValorEntregado'],
							'FECREP' => $lcFecHoraAcc,
							'ESTREP' => 1,
							'USCRMP' => $lcUsuario,
							'PGCRMP' => $lcProgram,
							'FECRMP' => $lcFecha,
							'HOCRMP' => $lcHora,
						];
						break;
					case 'AnularRe':
						$lbInsertar = false;
						$lcTabla = 'MIPRREP';
						$laDatos = [
							'ESTREP' => 0,
							'FECRAN' => $lcFecHoraAcc,
							'USMRMP' => $lcUsuario,
							'PGMRMP' => $lcProgram,
							'FEMRMP' => $lcFecha,
							'HOMRMP' => $lcHora,
						];
						$laWhere = ['IDREPE' => $lnIdTrnscc,];
						break;
					case 'Facturac':
						$lbInsertar = true;
						$lcTabla = 'MIPRFAC';
						$laDatos = [
							'IDENTF' => $lnIdMiPres,
							'IDFACT' => $lnIdTrnscc,
							'FECHAF' => $lcFecha,
							'NUMPRS' => $taVar['form']['NoPrescripcion']??($taVar['fila']['NUMPRES']??''),
							'TIPTEC' => $taVar['form']['TipoTec']??($taVar['fila']['TIPOTEC']??''),
							'CONTEC' => $taVar['form']['ConTec']??($taVar['fila']['CNSTEC']??0),
							'TIPIDP' => $taVar['form']['TipoIDPaciente']??($taVar['fila']['TIPIDPAC']??''),
							'NUMIDP' => $taVar['form']['NoIDPaciente']??($taVar['fila']['NUMIDPAC']??''),
							'NUMENT' => $taVar['form']['NoEntrega']??1,
							'NUMFAC' => $taVar['form']['NoFactura'],
							'NITEPS' => $taVar['form']['NoIDEPS'],
							'CODEPS' => $taVar['form']['CodEPS'],
							'CODSRV' => $taVar['form']['CodSerTecAEntregado'],
							'CNTUMN' => $taVar['form']['CantUnMinDis'],
							'VLRFAC' => $taVar['form']['ValorUnitFacturado'],
							'VLRFAT' => $taVar['form']['ValorTotFacturado'],
							'CUOTAM' => $taVar['form']['CuotaModer']??0,
							'COPAGO' => $taVar['form']['Copago']??0,
							'FECFAC' => $lcFecHoraAcc,
							'ESTFAC' => 1,
							'USCFMP' => $lcUsuario,
							'PGCFMP' => $lcProgram,
							'FECFMP' => $lcFecha,
							'HOCFMP' => $lcHora,
						];
						break;
					case 'AnularFa':
						$lbInsertar = false;
						$lcTabla = 'MIPRFAC';
						$laDatos = [
							'ESTFAC' => 0,
							'FECFAN' => $lcFecHoraAcc,
							'USMFMP' => $lcUsuario,
							'PGMFMP' => $lcProgram,
							'FEMFMP' => $lcFecha,
							'HOMFMP' => $lcHora,
						];
						$laWhere = ['IDFACT' => $lnIdTrnscc,];
						break;
				}
				try {
					if (count($laDatos)>0) {
						if ($lbInsertar) {
							$goDb->tabla($lcTabla)->insertar($laDatos);
						} else {
							$goDb->tabla($lcTabla)->where($laWhere)->actualizar($laDatos);
						}
					}
				} catch (\Exception $e) {
					$laReturn['error_bd'] = $e->getMessage();
				}
			}
		}

		return $laReturn;
	}


	/*  Guarda registro de consulta  */
	public static function fcGuardarDireccionamiento($laMiPresArray)
	{
		if (is_array($laMiPresArray)) {

			global $goDb;
			$laErrores = [];
			$lcTabla = 'MIPRDIR';
			$lcUsuario = $_SESSION[HCW_NAME]->oUsuario->getUsuario();
			$lcProgram = substr(pathinfo(__FILE__, PATHINFO_FILENAME),6,10);
			$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
			$lcFecha = $ltAhora->format('Ymd');
			$lcHora  = $ltAhora->format('His');
			$lnNumI=$lnNumU=0;

			foreach ($laMiPresArray as $laMiPres) {

				$laWhere=['IDDIRC' => $laMiPres->IDDireccionamiento,];
				$laData=[
					'IDENTF' => $laMiPres->ID,
					'IDDIRC' => $laMiPres->IDDireccionamiento,
					'FECHAD' => str_replace('-','',substr($laMiPres->FecDireccionamiento,0,10)),
					'NUMPRS' => $laMiPres->NoPrescripcion,
					'TIPTEC' => $laMiPres->TipoTec,
					'CONTEC' => $laMiPres->ConTec,
					'TIPIDP' => $laMiPres->TipoIDPaciente,
					'NUMIDP' => $laMiPres->NoIDPaciente,
					'NUMENT' => $laMiPres->NoEntrega,
					'NUMSBE' => $laMiPres->NoSubEntrega,
					'FECMAX' => $laMiPres->FecMaxEnt,
					'CATTOT' => $laMiPres->CantTotAEntregar,
					'DIRPAC' => $laMiPres->DirPaciente??'',
					'CODSRV' => $laMiPres->CodSerTecAEntregar??'',
					'NITEPS' => $laMiPres->NoIDEPS,
					'CODEPS' => $laMiPres->CodEPS,
					'FECDIR' => $laMiPres->FecDireccionamiento,
					'ESTDIR' => $laMiPres->EstDireccionamiento,
					'FECANL' => $laMiPres->FecAnulacion??'',
				];
				$laDataI=[
					'USCDMP' => $lcUsuario,
					'PGCDMP' => $lcProgram,
					'FECDMP' => $lcFecha,
					'HOCDMP' => $lcHora,
				];
				$laDataU=[
					'USMDMP' => $lcUsuario,
					'PGMDMP' => $lcProgram,
					'FEMDMP' => $lcFecha,
					'HOMDMP' => $lcHora,
				];

				try {
					$lbInsertar = true;
					$laReg = $goDb->tabla($lcTabla)->where($laWhere)->get('array');
					if (is_array($laReg)) if (count($laReg)>0) $lbInsertar = false;

					if ($lbInsertar) {
						$goDb->tabla($lcTabla)->insertar(array_merge($laData,$laDataI));
						$lnNumI++;
					} else {
						$goDb->tabla($lcTabla)->where($laWhere)->actualizar(array_merge($laData,$laDataU));
						$lnNumU++;
					}
				} catch(Exception $loError){
					$laErrores[] = $loError->getMessage();
				} catch(PDOException $loError){
					$laErrores[] = $loError->getMessage();
				}
			}

			return [
				'msg'=>'Se insertaron '.$lnNumI.' y se actualizaron '.$lnNumU.' - Total registros '.count($laMiPresArray),
				'err'=>implode(PHP_EOL, $laErrores),
			];

		} else {
			return [
				'msg'=>'No hay registros para guardar',
				'err'=>'',
			];
		}
	}



	/*  LISTA DE OPCIONES PARA TIPO CONSULTA  */
	public static function fcListaTiposConsulta($tcCodigo)
	{
		global $goDb;
		$laReturn['error'] = '';

		// Permiso para usar opciones PUT y ANULAR
		self::getUsuarioPermisos();
		$lbPuedeUsarPUT = in_array('usarput',self::$aPermisos);

		$goDb->select('CL2TMA AS CODIGO,DE1TMA AS DESCRIP,DE2TMA AS RUTA,OP2TMA AS NOMBRE,OP6TMA AS NUMPUT')
			->from('TABMAE')
			->where(['TIPTMA'=>'NOPOS','CL1TMA'=>'WSMIPRES','SUBSTR(CL2TMA,1,2)'=>$tcCodigo,'OP1TMA'=>'W','ESTTMA'=>'',])
			->orderBy('CL2TMA');
		if (!$lbPuedeUsarPUT) {
			$goDb->notIn('OP2TMA',['PUT','ANULAR',]);
		}

		$laReturn['TIPOS'] = $goDb->getAll('array');
		foreach($laReturn['TIPOS'] as $lnClave => $loTipo) {
			$laReturn['TIPOS'][$lnClave] = array_map('trim', $laReturn['TIPOS'][$lnClave]);
		}

		return $laReturn;
	}


	/*  RETORNA OPCIONES PARA UN TIPO CONSULTA  */
	public static function fcOpcionesTipoConsulta($tcCodigo='')
	{
		global $goDb;
		$laReturn['error'] = '';

		$laReturn['OPCIONES'] = $goDb
			->select('CL2TMA AS CODIGO,DE1TMA AS DESCRIP,DE2TMA AS RUTA,OP2TMA AS NOMBRE,OP6TMA AS NUMPUT')
			->from('TABMAE')
			->where(['TIPTMA'=>'NOPOS','CL1TMA'=>'WSMIPRES','CL2TMA'=>$tcCodigo,'OP1TMA'=>'W','ESTTMA'=>'',])
			->get('array');
		$laReturn['OPCIONES'] = array_map('trim', $laReturn['OPCIONES']);

		return $laReturn;
	}


	/*  Variables Para Formulario PUT  */
	public static function fcControlesPUT($tcCodigo)
	{
		global $goDb;
		$laReturn['error'] = '';

		$laReturn['TIPOS'] = $goDb
			->select('CL2TMA AS CODIGO,DE1TMA AS VARIABLE,DE2TMA AS DESCRIP,OP1TMA AS TIPOD,OP2TMA AS VALOR,OP5TMA AS PROP')
			->from('TABMAE')
			->where(['TIPTMA'=>'NOPOS','CL1TMA'=>'WSMIPRES','SUBSTR(CL2TMA,1,2)'=>$tcCodigo,'ESTTMA'=>''])
			->orderBy('CL2TMA')
			->getAll('array');
		foreach($laReturn['TIPOS'] as $lnClave => $loTipo) {
			$laReturn['TIPOS'][$lnClave] = array_map('trim', $laReturn['TIPOS'][$lnClave]);
			$laPartes=explode('~',$laReturn['TIPOS'][$lnClave]['DESCRIP']);
			$laReturn['TIPOS'][$lnClave]['DESCRIP']=$laPartes[0];
			$laReturn['TIPOS'][$lnClave]['VALOR']=$laPartes[1]??'';
		}

		return $laReturn;
	}


	/*  Variables Generales del Formulario  */
	public static function fcObtenerVariables($tcCodRuta)
	{
		global $goDb;
		$laReturn['error'] = '';

		$laVariables = $goDb
			->select('CL2TMA AS CODIGO,DE1TMA AS DESCRIP,DE2TMA AS VARIABLE,OP2TMA AS NOMBRE,OP5TMA AS VALOR')
			->from('TABMAE')
			->where(['TIPTMA'=>'NOPOS','CL1TMA'=>'WSMIPRES','SUBSTR(CL2TMA,1,2)'=>'03','ESTTMA'=>''])
			->orderBy('CL2TMA')
			->getAll('array');
		foreach($laVariables as $lnClave => $loTipo) {
			$laReturn['VARIABLES'][$lnClave] = array_map('trim', $laVariables[$lnClave]);
		}
		$laReturn['URL'] = self::fcObtenerUrlMiPres($tcCodRuta);
		unset($laVariables);

		return $laReturn;
	}


	/*  Obtener URL para consultas MiPres  */
	public static function fcObtenerUrlMiPres($tcCodRuta)
	{
		global $goDb;
		$laUrlMiPres = $goDb
			->select('DE2TMA AS URL')
			->from('TABMAE')
			->where(['TIPTMA'=>'NOPOS','CL1TMA'=>'WSMIPRES','CL2TMA'=>$tcCodRuta])
			->get('array');
		$lcReturn = trim($laUrlMiPres['URL'] ?? '');
		unset($laUrlMiPres);

		return $lcReturn;
	}


	/*  Retorna Token Temporal  */
	public static function fcObtenerTokenTmp($tcTipo='tokentmp')
	{
		return [ 'token' => self::fcVariables($tcTipo.'SinEnc'), 'error' => '' ];
	}


	/*  Obtiene Nuevo Token Temporal  */
	public static function fcGenerarTokenTmp($tcTipo='tokentmp')
	{
		if ($tcTipo=='tokentmp') {
			$lcUrl = self::fcVariables('urlDispensar') . 'GenerarToken/{nit}/{token}';
			$laCod = '03000007';

		} else {	// 'tokenfactmp'
			$lcUrl = self::fcVariables('urlFacturar') . 'GenerarToken/{nit}/{tokenfac}';
			$laCod = '03000010';
		}

		// generar token tmp
		$laReturn = self::fnConsumirMiPres([ 'url'=>$lcUrl, ], 'GET');

		if (isset($laReturn['MIPRES']))
			$lcTokenTmp = str_replace('"', '\'', $laReturn['MIPRES']);
		else
			return $laReturn['error'];

		// guardar nuevo token tmp
		global $goDb;
		$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
		$lcFecha = $ltAhora->format('Ymd');
		$lcHora  = $ltAhora->format('His');
		$laDatos = [
			'OP5TMA'=>$lcTokenTmp,
			'UMOTMA'=>'SRVWEB',
			'PMOTMA'=>'TOKENTMP',
			'FMOTMA'=>$lcFecha,
			'HMOTMA'=>$lcHora
			];
		$lbRta = $goDb
			->tabla('TABMAE')
			->where(['TIPTMA'=>'NOPOS', 'CL1TMA'=>'WSMIPRES', 'CL2TMA'=>$laCod])
			->actualizar($laDatos);

		return $lcTokenTmp;
	}


	/* Exportación a Excel */
	public static function exportarExcel($taResumen, $taDispensacion, $taAnulaciones)
	{
		require __DIR__ .'/../publico/complementos/spout/3.0.1/Spout/Autoloader/autoload.php';

		$lcArchivo = 'MiPresDP_'.date('Y-m-d-H-i-s').'.xlsx';
		$laCampos = array_keys(self::camposResumen());

		// Crear libro y establecer propiedades
		$loLibro = WriterEntityFactory::createXLSXWriter();
		//$loLibro->openToFile($lcArchivo);
		$loLibro->openToBrowser($lcArchivo);

		// Estilos para los títulos
		$loColor = Color::BLUE; $loWidth = Border::WIDTH_THIN; $loStyle = Border::STYLE_SOLID;

		$loBorderTitulo = (new BorderBuilder())
			->setBorderAll($loColor, $loWidth, $loStyle)
			->build();
		$loStyleTitulo = (new StyleBuilder())
			->setBackgroundColor(Color::rgb(214,234,248))
			->setFontColor($loColor)
			->setFontBold()
			->setBorder($loBorderTitulo)
			->build();

		$loBorderTitulo = (new BorderBuilder())
			->setBorderAll($loColor, $loWidth, $loStyle)
			->build();
		$loStyleTituloB = (new StyleBuilder())
			->setBackgroundColor(Color::rgb(214,234,248))
			->setFontColor(Color::BLUE)
			->setFontBold()
			->setBorder($loBorderTitulo)
			->build();
		$loColor = Color::GREEN;
		$loBorderTituloG = (new BorderBuilder())
			->setBorderAll($loColor, $loWidth, $loStyle)
			->build();
		$loStyleTituloG = (new StyleBuilder())
			->setBackgroundColor(Color::rgb(214,248,234))
			->setFontColor($loColor)
			->setFontBold()
			->setBorder($loBorderTituloG)
			->build();

		// Primera hoja con el resumen de lo realizado en el rango de fechas
		$loHojaUno = $loLibro->getCurrentSheet();
		$loHojaUno->setName('Resumen');

		//$loFila = WriterEntityFactory::createRowFromArray($laCampos, $loStyleTitulo);
		$lnNumTit=0;
		$laNumCampos=[11,14,5,10,6,4,3,3,3,3];
		$loCeldas = [];
		foreach($laNumCampos as $lnIndex=>$lnNumCmp) {
			for ($lnNum=1; $lnNum<=$lnNumCmp; $lnNum++){
				$loCeldas[]=WriterEntityFactory::createCell($laCampos[$lnNumTit], ($lnIndex%2==1 ? $loStyleTituloG : $loStyleTituloB) );
				$lnNumTit++;
			}
		}
		$loFila = WriterEntityFactory::createRow($loCeldas);

		$loLibro->addRow($loFila);
		foreach($taResumen as $laData) {
			$laAdd = [];
			foreach($laCampos as $lcCampo) {
				$laAdd[] = $laData[$lcCampo] ?? $laData[strtoupper($lcCampo)] ?? '';
			}
			$loLibro->addRow(WriterEntityFactory::createRowFromArray($laAdd));
		}

		// Segunda hoja con la lista de registros
		$loHoja = $loLibro->addNewSheetAndMakeItCurrent();
		$loHoja->setName('Registros');
		$laTitulos = ['Tipo', 'Enviado', 'Recibido', 'Id Direcc', 'Id Tipo', 'Usuario', 'Fecha', 'Hora'];
		$loFila = WriterEntityFactory::createRowFromArray($laTitulos, $loStyleTitulo);
		$loLibro->addRow($loFila);
		foreach($taDispensacion as $laData) {
			$loLibro->addRow(WriterEntityFactory::createRowFromArray($laData));
		}

		// Tercer hoja con la lista de las anulaciones
		$loHoja = $loLibro->addNewSheetAndMakeItCurrent();
		$loHoja->setName('Anulaciones');
		$laTitulos = ['Tipo', 'Id Direcc', 'Id Anulado', 'Usuario', 'Fecha', 'Hora'];
		$loFila = WriterEntityFactory::createRowFromArray($laTitulos, $loStyleTitulo);
		$loLibro->addRow($loFila);
		foreach($taAnulaciones as $laData) {
			$loLibro->addRow(WriterEntityFactory::createRowFromArray($laData));
		}

		$loLibro->setCurrentSheet($loHojaUno);
		$loLibro->close();
		exit;
	}


	/* Obtener datos para exportar */
	public static function datosExportar($tnFecIni=0, $tnFecFin=0, &$taResumen, &$taDispensacion, &$taAnulaciones)
	{
		global $goDb;
		$lbReturn=false;
		$taResumen=[];

		ini_set('max_execution_time', 15*60); // 15 minutos de consulta
		ini_set('memory_limit', '-1');

		// PROGRAMACIONES
		$laRegistros=$goDb
			->select('M.IDENTF,M.IDPROG,M.FECPRO,M.CODSRV,M.CATTOT,M.FECHAP,M.ESTPRO')
			->select('IFNULL(D.USRCRE,\'\') USRCRE,IFNULL(D.FECCRE,0) FECCRE,IFNULL(D.HORCRE,0) HORCRE')
			->from('MIPRPRO M')
			->leftJoin('MPDISPR D', 'CHAR(M.IDPROG)=D.IDTRNS AND D.SERVIC=\'Programa\'', null)
			->between('M.FECHAP', $tnFecIni, $tnFecFin)
			//->between('REPLACE(SUBSTR(M.FECPRO,1,10),\'-\', \'\')', $tnFecIni, $tnFecFin)
			->orderBy('M.IDPROG')
			->getAll('array');
		if (is_array($laRegistros)) {
			if (count($laRegistros)>0) {
				foreach($laRegistros as $laReg) {
					$laReg=array_map('trim',$laReg);
					$lnIdDir=$laReg['IDENTF'];
					$taResumen[$lnIdDir]=self::obtenerDireccionamiento($laReg['IDENTF']);
					$taResumen[$lnIdDir]['IdProgramacion']=$laReg['IDPROG'];
					$taResumen[$lnIdDir]['FecProgramacion']=$laReg['FECPRO'];
					$taResumen[$lnIdDir]['CodSerTecAEntregar']=$laReg['CODSRV'];
					$taResumen[$lnIdDir]['CantTotAEntregar']=$laReg['CATTOT'];
					$taResumen[$lnIdDir]['EstPrograma']=self::$estado[$laReg['ESTPRO']];
					$taResumen[$lnIdDir]['usu_Programa']=$laReg['USRCRE'];
					$taResumen[$lnIdDir]['fec_Programa']=$laReg['FECHAP']; // FECCRE
					$taResumen[$lnIdDir]['hor_Programa']=$laReg['HORCRE'];
				}
				$lbReturn=true;
			}
		}

		// ENTREGAS
		$laRegistros=$goDb
			->select('M.IDENTF,M.IDENTR,M.CODSRV,M.CATTOT,M.ENTTOT,M.CAUSNO,M.FECENT,M.NOLOTE,M.TIDREC,M.NIDREC,M.FECHAE,M.ESTENT')
			->select('M.NUMPRS,M.TIPTEC,M.CONTEC,M.TIPIDP,M.NUMIDP,M.NUMENT')
			->select('IFNULL(D.USRCRE,\'\') USRCRE,IFNULL(D.FECCRE,0) FECCRE,IFNULL(D.HORCRE,0) HORCRE')
			->from('MIPRENT M')
			->leftJoin('MPDISPR D', 'CHAR(M.IDENTR)=D.IDTRNS AND D.SERVIC IN (\'Entrega\',\'EntregaA\')', null)
			// ->between('M.FECHAE', $tnFecIni, $tnFecFin)
			->between('REPLACE(SUBSTR(M.FECENT,1,10),\'-\', \'\')', $tnFecIni, $tnFecFin)
			->orderBy('M.IDENTR')
			->getAll('array');
		if (is_array($laRegistros)) {
			if (count($laRegistros)>0) {
				foreach($laRegistros as $laReg) {
					$laReg=array_map('trim',$laReg);
					$lnIdDir=$laReg['IDENTF'];
					if (!isset($taResumen[$lnIdDir])) {
						$taResumen[$lnIdDir]=self::obtenerDireccionamiento($laReg['IDENTF']);
						if (empty($taResumen[$lnIdDir]['NoPrescripcion'])) {
							$taResumen[$lnIdDir]['ID']=$laReg['IDENTF'];
							$taResumen[$lnIdDir]['NoPrescripcion']=$laReg['NUMPRS'];
							$taResumen[$lnIdDir]['TipoTec']=$laReg['TIPTEC'];
							$taResumen[$lnIdDir]['ConTec']=$laReg['CONTEC'];
							$taResumen[$lnIdDir]['TipoIDPaciente']=$laReg['TIPIDP'];
							$taResumen[$lnIdDir]['NoIDPaciente']=$laReg['NUMIDP'];
							$taResumen[$lnIdDir]['NoEntrega']=$laReg['NUMENT'];
						}
					}
					$taResumen[$lnIdDir]['IdEntrega']=$laReg['IDENTR'];
					$taResumen[$lnIdDir]['CodSerTecEntregado']=$laReg['CODSRV'];
					$taResumen[$lnIdDir]['CantTotEntregada']=$laReg['CATTOT'];
					$taResumen[$lnIdDir]['EntTotal']=$laReg['ENTTOT'];
					$taResumen[$lnIdDir]['CausaNoEntrega']=$laReg['CAUSNO'];
					$taResumen[$lnIdDir]['FecEntrega']=$laReg['FECENT'];
					$taResumen[$lnIdDir]['NoLote']=$laReg['NOLOTE'];
					$taResumen[$lnIdDir]['TipoIDRecibe']=$laReg['TIDREC'];
					$taResumen[$lnIdDir]['NoIDRecibe']=$laReg['NIDREC'];
					$taResumen[$lnIdDir]['EstEntrega']=self::$estado[$laReg['ESTENT']];
					$taResumen[$lnIdDir]['usu_Entrega']=$laReg['USRCRE'];
					$taResumen[$lnIdDir]['fec_Entrega']=$laReg['FECHAE']; // FECCRE
					$taResumen[$lnIdDir]['hor_Entrega']=$laReg['HORCRE'];
				}
				$lbReturn=true;
			}
		}

		// REPORTES DE ENTREGA
		$laRegistros=$goDb
			->select('M.IDENTF,M.IDREPE,M.ESTENT,M.CAUSNO,M.VALORE,M.FECREP,M.FECHAR,M.ESTREP,M.NUMPRS')
			->select('IFNULL(D.USRCRE,\'\') USRCRE,IFNULL(D.FECCRE,0) FECCRE,IFNULL(D.HORCRE,0) HORCRE')
			->from('MIPRREP M')
			->leftJoin('MPDISPR D', 'CHAR(M.IDREPE)=D.IDTRNS AND D.SERVIC=\'ReporteE\'', null)
			->between('M.FECHAR', $tnFecIni, $tnFecFin)
			//->between('REPLACE(SUBSTR(M.FECREP,1,10),\'-\', \'\')', $tnFecIni, $tnFecFin)
			->orderBy('M.IDREPE')
			->getAll('array');
		if (is_array($laRegistros)) {
			if (count($laRegistros)>0) {
				foreach($laRegistros as $laReg) {
					$laReg=array_map('trim',$laReg);
					$lnIdDir=$laReg['IDENTF'];
					if (!isset($taResumen[$lnIdDir])) {
						$taResumen[$lnIdDir]=self::obtenerDireccionamiento($laReg['IDENTF']);
						if (empty($taResumen[$lnIdDir]['NoPrescripcion'])) {
							$taResumen[$lnIdDir]['ID']=$laReg['IDENTF'];
							$taResumen[$lnIdDir]['NoPrescripcion']=$laReg['NUMPRS'];
						}
					}
					$taResumen[$lnIdDir]['IDReporteEntrega']=$laReg['IDREPE'];
					$taResumen[$lnIdDir]['EstadoEntrega']=$laReg['ESTENT'];
					$taResumen[$lnIdDir]['CausaNoEntrega_RE']=$laReg['CAUSNO'];
					$taResumen[$lnIdDir]['ValorEntregado']=$laReg['VALORE'];
					$taResumen[$lnIdDir]['FecRepEntrega']=$laReg['FECREP'];
					$taResumen[$lnIdDir]['EstReporteEnt']=self::$estado[$laReg['ESTREP']];
					$taResumen[$lnIdDir]['usu_ReporteE']=$laReg['USRCRE'];
					$taResumen[$lnIdDir]['fec_ReporteE']=$laReg['FECHAR']; // FECCRE
					$taResumen[$lnIdDir]['hor_ReporteE']=$laReg['HORCRE'];
				}
				$lbReturn=true;
			}
		}

		// REPORTES DE FACTURACIÓN
		$laRegistros=$goDb
			->select('E.IDENTF,M.IDENTF IDENT_F,M.IDFACT,M.ESTFAC,M.FECFAC,M.FECHAF,M.NUMPRS')
			->select('IFNULL(D.USRCRE,\'\') USRCRE,IFNULL(D.FECCRE,0) FECCRE,IFNULL(D.HORCRE,0) HORCRE')
			->from('MIPRFAC M')
			->innerJoin('MIPRENT E', 'M.NUMPRS=E.NUMPRS AND M.TIPTEC=E.TIPTEC AND M.CONTEC=E.CONTEC AND M.NUMENT=E.NUMENT')
			->leftJoin('MPDISPR D', 'CHAR(M.IDFACT)=D.IDTRNS AND D.SERVIC=\'Facturac\'', null)
			->between('M.FECHAF', $tnFecIni, $tnFecFin)
			->orderBy('M.IDFACT')
			->getAll('array');
		if (is_array($laRegistros)) {
			if (count($laRegistros)>0) {
				foreach($laRegistros as $laReg) {
					$laReg=array_map('trim',$laReg);
					$lnIdDir=$laReg['IDENTF'];
					if (!isset($taResumen[$lnIdDir])) {
						$taResumen[$lnIdDir]=self::obtenerDireccionamiento($laReg['IDENTF']);
						if (empty($taResumen[$lnIdDir]['NoPrescripcion'])) {
							//$taResumen[$lnIdDir]['ID']=$laReg['IDENTF'];
							$taResumen[$lnIdDir]['NoPrescripcion']=$laReg['NUMPRS'];
						}
					}
					$taResumen[$lnIdDir]['IDFactura']=$laReg['IDENT_F'];
					$taResumen[$lnIdDir]['IDRepoFact']=$laReg['IDFACT'];
					$taResumen[$lnIdDir]['EstadoRepoFact']=$laReg['ESTFAC'];
					$taResumen[$lnIdDir]['FechaRepoFact']=$laReg['FECFAC'];
					$taResumen[$lnIdDir]['usu_RepoFact']=$laReg['USRCRE'];
					$taResumen[$lnIdDir]['fec_RepoFact']=$laReg['FECHAF']; // FECCRE
					$taResumen[$lnIdDir]['hor_RepoFact']=$laReg['HORCRE'];
				}
				$lbReturn=true;
			}
		}


		// PRESCRIPCIONES
		foreach ($taResumen as $lnIdDir=>$laReg) {
			if (!empty($laReg['NoPrescripcion']) && !empty($laReg['TipoTec']) && !empty($laReg['ConTec'])) {
				$laPres=self::obtenerPrescripcion($laReg['NoPrescripcion'], $laReg['TipoTec'], $laReg['ConTec']);
				if (count($laPres)>0){
					$taResumen[$lnIdDir]['FPrescripcion']=$laPres['FPRESCRIPCION'];
					$taResumen[$lnIdDir]['IngresoPres']=$laPres['INGRESOPRES'];
					$taResumen[$lnIdDir]['TipoIDProf']=$laPres['TIPOIDPROF'];
					$taResumen[$lnIdDir]['NumIDProf']=$laPres['NUMIDPROF'];
					$taResumen[$lnIdDir]['Profesional']=$laPres['PROFESIONAL'];
					$taResumen[$lnIdDir]['Paciente']=$laPres['PACIENTE'];
					$taResumen[$lnIdDir]['CodAmbAte']=$laPres['CODAMBATE'];
					$taResumen[$lnIdDir]['AmbitoAte']=$laPres['AMBITOATE'];
					$taResumen[$lnIdDir]['Codigo']=trim($laPres['CODIGO'] ?? '');
					$taResumen[$lnIdDir]['Descripcion']=trim($laPres['DESCRIPCION'] ?? '');
					if (empty($taResumen[$lnIdDir]['TipoIDPaciente'])) $taResumen[$lnIdDir]['TipoIDPaciente']=$laPres['TIPOIDPACIENTE'];
					if (empty($taResumen[$lnIdDir]['NoIDPaciente'])) $taResumen[$lnIdDir]['NoIDPaciente']=$laPres['NOIDPACIENTE'];
				}
				$lbReturn=true;
			}
		}

		// REGISTROS DEL PERIODO EN AS400
		$laRegistros = $goDb
			->select('M.SERVIC,M.DATENV,M.DATRTA,M.IDMIPR,M.IDTRNS,M.USRCRE,M.FECCRE,M.HORCRE')
			->from('MPDISPR M')
			->between('M.FECCRE', $tnFecIni, $tnFecFin)
			->getAll('array');
		if (is_array($laRegistros)) {
			if (count($laRegistros)>0) {
				foreach($laRegistros as $lnIndex => $laRegistro) {
					$laRegistros[$lnIndex] = array_map('trim',$laRegistro);
				}

				// Configuración
				$laTipos = ['Programa','Entrega','EntregaA','ReporteE'];
				$laCamposAnula = [
					'AnularPr'=>'IdProgramacion',
					'AnularEn'=>'IdEntrega',
					'AnularRe'=>'IdReporteEntrega',
					];
				$laTipAnulaTip = [
					'Programa'=>'AnularPr',
					'Entrega' =>'AnularEn',
					'EntregaA'=>'AnularEn',
					'ReporteE'=>'AnularRe',
					];
				$laDatosAnul = [
					'AnularPr'=>[],
					'AnularEn'=>[],
					'AnularRe'=>[],
					];
				$laTiposAnula = array_keys($laCamposAnula);
				$laDirec = array_keys($laCamposAnula);

				// Ciclo para obtener registros a exportar
				foreach($laRegistros as $lnIndex => $laRegistro) {

					// Datos recibidos
					$lcRspta = substr($laRegistro['DATRTA'], 1, strlen($laRegistro['DATRTA'])-2);
					$laRspta = json_decode($lcRspta, true);

					if (in_array($laRegistro['SERVIC'], $laTipos)) {
						$laDatosAnul[$laTipAnulaTip[$laRegistro['SERVIC']]][$laRegistro['IDMIPR']] = $laRegistro['IDTRNS'];
						$taDispensacion[] = array_slice(array_values($laRegistro), 0, 8);
					}
				}

				// Marcar registros anulados (solo los anulados entre las fechas indicadas)
				foreach($laRegistros as $lnIndex => $laRegistro) {
					if (in_array($laRegistro['SERVIC'], $laTiposAnula)) {
						$lcIdDir = array_search($laRegistro['IDTRNS'], $laDatosAnul[$laRegistro['SERVIC']]);
						if ($lcIdDir===false) $lcIdDir = '';
						$taAnulaciones[] = [
							substr($laCamposAnula[$laRegistro['SERVIC']],2),	// tipo
							$lcIdDir,											// id dirreccionamiento
							$laRegistro['IDTRNS'],								// id Anulado
							$laRegistro['USRCRE'],								// usuario
							$laRegistro['FECCRE'],								// fecha
							$laRegistro['HORCRE'],								// hora
						];
					}
				}
				unset($laRegistros);
				$lbReturn = true;
			}
		}

		return $lbReturn;
	}


	/* Obtener datos de direccionamiento */
	private static function obtenerDireccionamiento($tnID, $tnUsarCamposResumen=true)
	{
		global $goDb;
		$laResumen = $tnUsarCamposResumen ? self::camposResumen() : [];

		$laDir=$goDb
			->select([
				'TIPIDP TipoIDPaciente',
				'NUMIDP NoIDPaciente',
				'NUMPRS NoPrescripcion',
				'TIPTEC TipoTec',
				'CONTEC ConTec',
				'NUMENT NoEntrega',
				'NUMSBE NoSubEntrega',
				'FECMAX FecMaxEnt',
				'CATTOT CantTotAEntregarDir',
				'CODSRV CodSerTecAEntregarDir',
				'NITEPS NoIDEPS',
				'CODEPS CodEPS',
				'FECDIR FecDireccionamiento',
				'IDENTF ID',
				'ESTDIR Estado',
			])
			->from('MIPRDIR')
			->where('IDENTF','=',$tnID)
			->get('array');
		if (is_array($laDir)) {
			if (count($laDir)>0) {
				$laDir=array_map('trim',$laDir);
				if ($tnUsarCamposResumen) {
					$laCampos=array_keys($laResumen);
					foreach($laCampos as $laCampo) {
						$laResumen[$laCampo]=$laDir[strtoupper($laCampo)]??'';
					}
					$laResumen['EstDireccion']=self::$estado[$laDir['ESTADO']];
				} else {
					$laResumen = $laDir;
				}
			}
		}
		return $laResumen;
	}


	/* Obtener datos de direccionamiento desde la prescripción */
	public static function obtenerDirDesdePrsc($tcNumPres, $tcTipoTec, $tnConTec)
	{
		global $goDb;
		$laDir=$goDb
			->select([
				'IDENTF ID',
				'TIPIDP TipoIDPaciente',
				'NUMIDP NoIDPaciente',
				'NUMENT NoEntrega',
				'FECMAX FecMaxEnt',
				'NUMSBE NoSubEntrega',
				'CATTOT CantTotAEntregarDir',
				'CODSRV CodSerTecAEntregarDir',
				'NITEPS NoIDEPS',
				'CODEPS CodEPS',
				'FECDIR FecDireccionamiento',
				'ESTDIR Estado',
			])
			->from('MIPRDIR')
			->where(['NUMPRS'=>$tcNumPres, 'TIPTEC'=>$tcTipoTec, 'CONTEC'=>$tnConTec, ])
			->orderBy('IDENTF DESC')
			->get('array');
		return is_array($laDir) ? array_map('trim', $laDir) : [];
	}


	/* Obtener datos de entrega desde la prescripción */
	public static function obtenerEntDesdePrsc($tcNumPres, $tcTipoTec, $tnConTec)
	{
		global $goDb;
		$laEnt=$goDb
			->select([
				'IDENTF ID',
				'IDENTR IdEntrega',
				'TIPIDP TipoIDPaciente',
				'NUMIDP NoIDPaciente',
				'NUMENT NoEntrega',
				'CATTOT CantTot',
				'CODSRV CodSerTec',
				'FECHAE FecEntrega',
				'ESTENT Estado',
			])
			->from('MIPRENT')
			->where(['NUMPRS'=>$tcNumPres, 'TIPTEC'=>$tcTipoTec, 'CONTEC'=>$tnConTec, ])
			->orderBy('IDENTR DESC')
			->get('array');
		return is_array($laEnt) ? array_map('trim', $laEnt) : [];
	}


	/* Obtener datos de prescripciones */
	public static function obtenerPrescripcion($tcNumPres, $tcTipoTec='', $tnContec=0)
	{
		global $goDb;
		$goDb
			->select([
				'P.FEPWCA FPrescripcion',
				'P.INGWCA IngresoPres',
				'P.TDPWCA TipoIDProf',
				'P.NDPWCA NumIDProf',
				'TRIM(P.PNPWCA)||\' \'||TRIM(P.SNPWCA)||\' \'||TRIM(P.PAPWCA)||\' \'||TRIM(P.SAPWCA) Profesional',
				'P.TDUWCA TipoIDPaciente',
				'P.NDUWCA NoIDPaciente',
				'TRIM(P.PNUWCA)||\' \'||TRIM(P.SNUWCA)||\' \'||TRIM(P.PAUWCA)||\' \'||TRIM(P.SAUWCA) Paciente',
				'P.CARWCA CodAmbAte',
				'SUBSTR(IFNULL(A.DE2TMA,\'\'),1,30) AmbitoAte',
				'P.EPSWCA CodEPS',
			])
			->from('WSNPCA P')
			->leftJoin('TABMAE A','A.TIPTMA=\'WSNOPOS\' AND A.CL1TMA=\'AMBATEN\' AND P.CARWCA=A.CL2TMA', null)
			->where(['P.NPRWCA'=>$tcNumPres]);
		switch($tcTipoTec) {
			case 'P':
				$goDb
					->select('IFNULL(S.CUPWCU,\'\') Codigo, SUBSTR(IFNULL(DESCUP, \'\'), 1, 120) AS Descripcion')
					->leftJoin('WSNPCU S', 'P.NPRWCA=S.NPRWCU AND S.CNSWCU='.$tnContec, null)
					->leftJoin('RIACUP C', 'C.CODCUP=S.CUPWCU', null);
				break;
			case 'M':
				$goDb
					->select('SUBSTR(IFNULL(S.DPAWME,\'\'), 1, 250) AS Descripcion') // S.CFFWME Codigo,
					->leftJoin('WSNPME S', 'P.NPRWCA=S.NPRWME AND S.CNSWME='.$tnContec, null);
				break;
			case 'N':
				$goDb
					->select('IFNULL(S.PNUWNU,\'\') Codigo, IFNULL(N.DE2TMA,\'\') Descripcion')
					->leftJoin('WSNPNU S', 'P.NPRWCA=S.NPRWNU AND S.CNSWNU='.$tnContec, null)
					->leftJoin('TABMAE N', 'N.TIPTMA=\'WSNOPOS\' AND N.CL1TMA=\'PRODNUT\' AND N.CL3TMA=S.PNUWNU ', null);
				break;
		}
		$laPres = $goDb->get('array');

		return is_array($laPres) ? array_map('trim', $laPres) : [];
	}


	/* Obtener datos de una factura */
	public static function obtenerDatosFac($tcNumFac=0)
	{
		global $goDb;
		$laFact = [];

		// Cabecera de factura
		$lcDocFac = substr(str_pad($tcNumFac, 8, '0', STR_PAD_LEFT), 2, 6);
		$laFactura = $goDb
			->select('FRACAB, INGCAB, NITCAB, CONCAB, PLNCAB')
			->from('FACCABF')
			->where([ 'FRACAB' => $tcNumFac, 'DOCCAB' => $lcDocFac ])->where('MA1CAB<>\'A\'')
			->get('array');

		if (is_array($laFactura)) {
			if (count($laFactura)>0) {
				// Detalle de factura
				$laDetalles = $goDb
					->select('ELEDFA AS CODIGO')->sum('VPRDFA','VR_TOTAL')
					->from('FACDETF')
					->where([
						'INGDFA'=>$laFactura['INGCAB'],
						'NFADFA'=>$laFactura['FRACAB'],
						'CFADFA'=>$laFactura['CONCAB'],
						'PLADFA'=>$laFactura['PLNCAB'],
						'DOCDFA'=>$lcDocFac,
						'TINDFA'=>'900' ])
					->in('ELEDFA', ['COPAGO','CUOTAM',])
					->notLike('CUPDFA', 'C%')
					->groupBy('ELEDFA')
					->having('SUM(VPRDFA)', '<', 0)
					->getAll('array');

				$laFact = [
					'INGRESO'=> $laFactura['INGCAB'],
					'NIT'	 => $laFactura['NITCAB'],
					'CODIGO' => trim($laDetalles[0]['CODIGO'] ?? ''),
					'VALOR'	 => abs($laDetalles[0]['VR_TOTAL'] ?? 0),
				];
			}
		}

		return $laFact;
	}


	/*
	 *	Obtener datos de facturación de un ingreso
	 */
	public static function obtenerDatosFacIng($tnIngreso=0)
	{
		global $goDb;
		$laFact = [];

		// Cabecera de factura
		$laFactura = $goDb
			->select('FRACAB, INGCAB, NITCAB, CONCAB, PLNCAB')
			->from('FACCABF')
			->where("INGCAB=$tnIngreso AND MA1CAB<>'A' AND PLNCAB<>'SHAIO1'")
			->get('array');

		if (is_array($laFactura)) {
			if (count($laFactura)>0) {
				$lcDocFac = substr(str_pad($laFactura['FRACAB'], 8, '0', STR_PAD_LEFT), 2, 6);
				// Detalle de factura
				$laDetalles = $goDb
					->select('ELEDFA AS CODIGO')->sum('VPRDFA','VR_TOTAL')
					->from('FACDETF')
					->where([
						'INGDFA'=>$laFactura['INGCAB'],
						'NFADFA'=>$laFactura['FRACAB'],
						'CFADFA'=>$laFactura['CONCAB'],
						'PLADFA'=>$laFactura['PLNCAB'],
						'DOCDFA'=>$lcDocFac,
						'TINDFA'=>'900' ])
					->in('ELEDFA', ['COPAGO','CUOTAM',])
					->notLike('CUPDFA', 'C%')
					->groupBy('ELEDFA')
					->having('SUM(VPRDFA)', '<', 0)
					->getAll('array');

				$laFact = [
					'INGRESO'=> $laFactura['INGCAB'],
					'FACTURA'=> $laFactura['FRACAB'],
					'NIT'	 => $laFactura['NITCAB'],
					'CODIGO' => trim($laDetalles[0]['CODIGO'] ?? ''),
					'VALOR'	 => abs($laDetalles[0]['VR_TOTAL'] ?? 0),
				];
			}
		}
		return $laFact;
	}


	/*
	 *	Obtener datos de facturación de un ingreso - tecnología
	 */
	public static function obtenerDatosFacTecno($tnIngreso=0, $tcPrescripcion, $tcTipo, $tnConsecutivo, $tcCodigo='')
	{
		global $goDb;
		$laFact = [];

		if ($tcTipo=='P') {
			//$lcCodigo = $tcCodigo;
			$laWhere = [
				'D.INGDFA'=>$tnIngreso,
				'D.CUPDFA'=>$tcCodigo,
				'D.TINDFA'=>'400',
			];
		} else {
			$lcCodigo = $tcCodigo ?? self::obtenerCodShaio($tnIngreso, $tcPrescripcion, $tcTipo, $tnConsecutivo);
			$laWhere = [
				'D.INGDFA'=>$tnIngreso,
				'D.ELEDFA'=>$lcCodigo,
				//D.TINDFA IN ('500','600')
			];
		}

		// Factura que contiene el código seleccionado
		$laDetalles = $goDb
			->select('F.FRACAB, D.CFADFA, D.DOCDFA, D.VUNDFA, F.NITCAB, D.NENDFA, D.PLADFA, QCODFA')
			->from('FACDETF D')
			->innerJoin('FACCABF F', 'D.INGDFA=F.INGCAB AND D.NFADFA=F.FRACAB AND D.CFADFA=F.CONCAB AND D.PLADFA=F.PLNCAB', null)
			->where($laWhere)->where('F.MA1CAB<>\'A\'')
			->getAll('array');

		// Copago - Cuota Moderadora de factura
		$lnCant = 0;
		$lnVrUd = $laDetalles[0]['VUNDFA'] ?? 0;
		$llVrVarios = false;
		if (is_array($laDetalles) && count($laDetalles)>0) {
			foreach ($laDetalles as $laDet) {
				$lnCant += $laDet['QCODFA'];
				if ($lnVrUd !== $laDet['VUNDFA']) {
					$lnVrUd = 0;
					$llVrVarios = true;
				}
			}
			$laFactura = $goDb
				->select('ELEDFA AS CODIGO')->sum('VPRDFA','VR_TOTAL')
				->from('FACDETF')
				->where([
					'INGDFA'=>$tnIngreso,
					'NFADFA'=>$laDetalles[0]['FRACAB'],
					'CFADFA'=>$laDetalles[0]['CFADFA'],
					'PLADFA'=>$laDetalles[0]['PLADFA'],
					'DOCDFA'=>$laDetalles[0]['DOCDFA'],
					'TINDFA'=>'900' ])
				->in('ELEDFA', ['COPAGO','CUOTAM',])
				->notLike('CUPDFA', 'C%')
				->groupBy('ELEDFA')
				->having('SUM(VPRDFA)', '<', 0)
				->getAll('array');
		}

		$laFact = [
			'INGRESO'=> $tnIngreso,
			'FACTURA'=> $laDetalles[0]['FRACAB'] ?? '',
			'NIT'	 => $laDetalles[0]['NITCAB'] ?? '',
			'VALORUD'=> $lnVrUd,
			'DIFVR'	 => $llVrVarios ? 'S' : 'N',
			'CODIGO' => trim($laFactura[0]['CODIGO'] ?? ''),
			'VALOR'	 => abs($laFactura[0]['VR_TOTAL'] ?? 0),
			'CANT'	 => $lnCant,
		];

		return $laFact;
	}


	/*
	 *	Obtiene cantidad facturada de un medicamento para una prescripción
	 */
	public static function cantidadFactPres($tnIngreso=0, $tcPrescripcion, $tcTipo, $tnConsecutivo, $tcCodShaio)
	{
		global $goDb;
		$laReturn = [];
		$lcCodShaio = $tcCodShaio ?? self::obtenerCodShaio($tnIngreso, $tcPrescripcion, $tcTipo, $tnConsecutivo);

		if (!empty($lcCodShaio)) {
			// Cantidad de prescripciones anteriores
			$lcTipo = $tcTipo=='M' ? '500' : ( $tcTipo=='N' ? '701' : '0' );
			$laCons = $goDb
				->select('IFNULL(SUM(CANJMP),0) CANTIDAD')
				->from('NPJSMP')
				->where("INGJMP=$tnIngreso AND NPRJMP<'$tcPrescripcion' AND TICJMP='$lcTipo'")
				->get('array');
			$lnCntAntes = $laCons['CANTIDAD'] ?? 0;

			// Cantidad Total Grabado
			$laCons = $goDb
				->select('NFAEST, SUM(QCOEST) CANTIDAD, SUM(VPREST) VR_PRELIQ')
				->from('RIAESTM')
				->where("INGEST=$tnIngreso AND ELEEST='$lcCodShaio' AND TINEST IN ('500', '600') AND (RF4EST = 'NOPOS' OR RF5EST = 'NOPB') AND NPREST='0' AND ESFEST <> 5")
				->groupBy('NFAEST')
				->get('array');
			$lnCntGrab = $laCons['CANTIDAD'] ?? 0;

			$laReturn['cantTotal'] = $lnCntGrab;
			$laReturn['vrPreliq'] = $laCons['VR_PRELIQ'] ?? 0;
			$laReturn['cant'] = $lnCntGrab > $lnCntAntes ? $lnCntGrab - $lnCntAntes : 0;
		}

		return $laReturn;
	}


	/*
	 *	Obtener CUM de un medicamento o producto nutricional
	 */
	public static function obtenerCUM($tnIngreso=0, $tcPrescripcion, $tcTipo, $tnConsecutivo, $tcCodShaio)
	{
		global $goDb;
		$laReturn['CODSHAIO'] = $tcCodShaio ?? self::obtenerCodShaio($tnIngreso, $tcPrescripcion, $tcTipo, $tnConsecutivo);
		if (!empty($laReturn['CODSHAIO'])) {
			require_once __DIR__ . '/class.FuncionesInv.php';
			$laReturn['CUM'] = FuncionesInv::fObtenerCUM($tnIngreso, $laReturn['CODSHAIO'])['CUM'];
		}
		return $laReturn;
	}


	/*
	 *	Obtener código shaio de un Medicamento o Producto Nutricional (Recepción/Radicación)
	 */
	public static function obtenerCodShaio($tnIngreso=0, $tcPrescripcion, $tcTipo, $tnConsecutivo)
	{
		global $goDb;
		$lcReturn = '';
		$lcTipo = $tcTipo=='M' ? '500' :
				( $tcTipo=='N' ? '701' :
				( $tcTipo=='S' ? '700' :
				( $tcTipo=='D' ? '600' :
				( $tcTipo=='P' ? '400' : '0' ) ) ) );

		// Obtener código shaio en Recepción/Radicación
		$laRecRad = $goDb
			->select('CCOJMP')
			->from('NPJSMP')
			->where([
				'INGJMP'=>$tnIngreso,
				'NPRJMP'=>$tcPrescripcion,
				'TICJMP'=>$lcTipo,
				'CNSJMP'=>$tnConsecutivo,
				])
			->get('array');

		if (is_array($laRecRad))
			if (count($laRecRad)>0)
				$lcReturn = trim($laRecRad['CCOJMP']);
		return $lcReturn;
	}



	/* Retorna array con títulos para hoja resumen de la exportación */
	public static function camposResumen()
	{
		return [
			// PRESCRIPCIÓN
			'NoPrescripcion'=>'',
			'FPrescripcion'=>'',
			'IngresoPres'=>'',
			'TipoIDProf'=>'',
			'NumIDProf'=>'',
			'Profesional'=>'',
			'TipoIDPaciente'=>'',
			'NoIDPaciente'=>'',
			'Paciente'=>'',
			'CodAmbAte'=>'',
			'AmbitoAte'=>'',
			// DIRECCIONAMIENTO
			'ID'=>'',
			'EstDireccion'=>'',
			'FecDireccionamiento'=>'',
			'TipoTec'=>'',
			'ConTec'=>'',
			'Codigo'=>'',
			'Descripcion'=>'',
			'NoEntrega'=>'',
			'NoSubEntrega'=>'',
			'FecMaxEnt'=>'',
			'CantTotAEntregarDir'=>'',
			'CodSerTecAEntregarDir'=>'',
			'NoIDEPS'=>'',
			'CodEPS'=>'',
			// PROGRAMACION
			'IdProgramacion'=>'',
			'EstPrograma'=>'',
			'FecProgramacion'=>'',
			'CodSerTecAEntregar'=>'',
			'CantTotAEntregar'=>'',
			// ENTREGA
			'IdEntrega'=>'',
			'EstEntrega'=>'',
			'CodSerTecEntregado'=>'',
			'CantTotEntregada'=>'',
			'EntTotal'=>'',
			'CausaNoEntrega'=>'',
			'FecEntrega'=>'',
			'NoLote'=>'',
			'TipoIDRecibe'=>'',
			'NoIDRecibe'=>'',
			// REPORTE DE ENTREGA
			'IDReporteEntrega'=>'',
			'EstReporteEnt'=>'',
			'EstadoEntrega'=>'',
			'CausaNoEntrega_RE'=>'',
			'ValorEntregado'=>'',
			'FecRepEntrega'=>'',
			// REPORTE DE FACTURACIÓN
			'IDFactura'=>'',
			'IDRepoFact'=>'',
			'EstadoRepoFact'=>'',
			'FechaRepoFact'=>'',
			// LOGS
			'usu_Programa'=>'',
			'fec_Programa'=>'',
			'hor_Programa'=>'',
			'usu_Entrega'=>'',
			'fec_Entrega'=>'',
			'hor_Entrega'=>'',
			'usu_ReporteE'=>'',
			'fec_ReporteE'=>'',
			'hor_ReporteE'=>'',
			'usu_RepoFact'=>'',
			'fec_RepoFact'=>'',
			'hor_RepoFact'=>'',
		];
	}


	/* Obtener variables de consulta */
	public static function fcVariables($tcVar)
	{
		global $goDb;
		$luReturn = null;

		switch ($tcVar) {
			case 'urlPrescribe':
				$loCons = $goDb->obtenerTabMae('DE2TMA', 'NOPOS', ['CL1TMA'=>'WSMIPRES','CL2TMA'=>'01000001','ESTTMA'=>'']);
				$luReturn = str_replace("'","",trim($loCons->DE2TMA));
				break;
			case 'urlDispensar':
				$loCons = $goDb->obtenerTabMae('DE2TMA', 'NOPOS', ['CL1TMA'=>'WSMIPRES','CL2TMA'=>'01000002','ESTTMA'=>'']);
				$luReturn = str_replace("'","",trim($loCons->DE2TMA));
				break;
			case 'urlFacturar':
				$loCons = $goDb->obtenerTabMae('DE2TMA', 'NOPOS', ['CL1TMA'=>'WSMIPRES','CL2TMA'=>'01000003','ESTTMA'=>'']);
				$luReturn = str_replace("'","",trim($loCons->DE2TMA));
				break;
			case 'nit':
				$loCons = $goDb->obtenerTabMae('OP5TMA', 'NOPOS', ['CL1TMA'=>'WSMIPRES','CL2TMA'=>'03000001','ESTTMA'=>'']);
				$luReturn = str_replace("'","",trim($loCons->OP5TMA));
				break;
			case 'token':
				$loCons = $goDb->obtenerTabMae('OP5TMA', 'NOPOS', ['CL1TMA'=>'WSMIPRES','CL2TMA'=>'03000002','ESTTMA'=>'']);
				$luReturn = urlencode(str_replace("'","",trim($loCons->OP5TMA)));
				break;
			case 'tokenfac':
				$loCons = $goDb->obtenerTabMae('OP5TMA', 'NOPOS', ['CL1TMA'=>'WSMIPRES','CL2TMA'=>'03000009','ESTTMA'=>'']);
				$luReturn = urlencode(str_replace("'","",trim($loCons->OP5TMA)));
				break;
			case 'tokentmp':
				$loCons = $goDb->obtenerTabMae('OP5TMA', 'NOPOS', ['CL1TMA'=>'WSMIPRES','CL2TMA'=>'03000007','ESTTMA'=>'']);
				$luReturn = urlencode(str_replace("'","",trim($loCons->OP5TMA)));
				break;
			case 'tokenfactmp':
				$loCons = $goDb->obtenerTabMae('OP5TMA', 'NOPOS', ['CL1TMA'=>'WSMIPRES','CL2TMA'=>'03000010','ESTTMA'=>'']);
				$luReturn = urlencode(str_replace("'","",trim($loCons->OP5TMA)));
				break;
			case 'tokentmpSinEnc':
				$loCons = $goDb->obtenerTabMae('OP5TMA', 'NOPOS', ['CL1TMA'=>'WSMIPRES','CL2TMA'=>'03000007','ESTTMA'=>'']);
				$luReturn = str_replace("'","",trim($loCons->OP5TMA));
				break;
			case 'tokenfactmpSinEnc':
				$loCons = $goDb->obtenerTabMae('OP5TMA', 'NOPOS', ['CL1TMA'=>'WSMIPRES','CL2TMA'=>'03000010','ESTTMA'=>'']);
				$luReturn = str_replace("'","",trim($loCons->OP5TMA));
				break;
		}

		return $luReturn;
	}


	/* Obtiene lista de EPS para MiPres */
	public static function getListaEPS()
	{
		$laReturn=[];
		global $goDb;
		$laEPS = $goDb
			->select('CL2TMA,DE2TMA')
			->from('TABMAE')
			->where(['TIPTMA'=>'WSNOPOS','CL1TMA'=>'CODEPS'])
			->orderBy('DE2TMA')
			->getAll('array');
		if (is_array($laEPS)) {
			//$laReturn = AplicacionFunciones::mapear($laEPS, 'CL2TMA', 'DE2TMA');
			$laReturn = [];
			foreach($laEPS as $lnIndex=>$laValor) {
				$laReturn[trim($laValor['CL2TMA'])] = trim($laValor['DE2TMA']);
			}
		}
		return $laReturn;
	}


	/*
	 *	Obtiene lista de Prescripciones
	 *
	 * @param string/integer $tdFechaIni: fecha inicio en formato YYYYmmdd
	 * @param string/integer $tdFechaFin: fecha fin en formato YYYYmmdd
	 * @param string $tcNumPres: número de prescripción
	 * @param integer $tnIngreso: número de ingreso del paciente
	 * @param string $tcTipoDoc: tipo de documento del paciente
	 * @param string/integer $tcNumDoc: número de documento del paciente
	 * @param string $tcCodEps: código de la EPS
	 * @param array $taAmbitos: ámbitos a mostrar
	 * @param string $tcTipTecno: tipo de tecnología
	 * @param integer $tnCnsTecno: consecutivo de tecnología
	 */
	public static function getPrescripciones(
		$tdFechaIni,
		$tdFechaFin,
		$tcNumPres='',
		$tnIngreso=0,
		$tcTipoDoc='',
		$tcNumDoc='',
		$tcCodEps='',
		$taAmbitos=[],
		$tcTipTecno='',
		$tnCnsTecno=0)
	{
		global $goDb;
		$laPro = $laMed = $laNut = $laSrv = $laDis = $laResult = [];

		// Campos comunes a las consultas
		$laCamposPres = [
				'P.NPRWCA NumPres',
				'P.FEPWCA FecPres',
				'P.INGWCA Ingreso',
				'IFNULL(I.FEIING,0) FechaIng',
				'IFNULL(I.FEEING,0) FechaEgr',
				'P.TDUWCA TipIDPac',
				'P.NDUWCA NumIDPac',
				'TRIM(P.PNUWCA)||\' \'||TRIM(P.SNUWCA)||\' \'||TRIM(P.PAUWCA)||\' \'||TRIM(P.SAUWCA) Paciente',
				'P.CARWCA CodAmbAte',
				'IFNULL(A.OP6TMA,\'\') Ambito',
				'P.EPSWCA CodEPS',
				'IFNULL(ME.IDENTF,0) Identificador',
				'IFNULL(ME.IDENTR,0) IdEntrega',
				'IFNULL(ME.CATTOT,0) CantEntr',
				'IFNULL(MR.IDREPE,0) IdReporte',
				'IFNULL(MF.IDENTF,0) IdentifFact',
				'IFNULL(MF.IDFACT,0) IdFactura',
				'IFNULL(ME.CODSRV,\'\') CodSrvEnt',
				'TRIM(IFNULL(ME.CODENT,\'\')) CodEntAdd',
			];

		// Condición para consultas
		$lcWhere = '';
		switch (true) {
			case !empty($tcNumPres):
				$lcWhere = 'P.NPRWCA=\''.$tcNumPres.'\'';
				break;
			case !empty($tnIngreso):
				$lcWhere = 'P.INGWCA='.$tnIngreso;
				break;
			case !empty($tcTipoDoc) && !empty($tcNumDoc):
				$lcWhere = 'P.TDUWCA=\''.$tcTipoDoc.'\' AND P.NDUWCA=\''.$tcNumDoc.'\'';
				break;
			default:
				$lcWhere = 'P.FEPWCA BETWEEN '.$tdFechaIni.' AND '.$tdFechaFin;
				if (!empty($tcCodEps))
					$lcWhere .= " AND P.EPSWCA='$tcCodEps'";
		}
		if (count($taAmbitos)>0) {
			$lcComa = '';
			$lcWhere .= ' AND P.CARWCA IN (';
			foreach ($taAmbitos as $lcAmbito) {
				$lcWhere .= "$lcComa '$lcAmbito'";
				$lcComa = ',';
			}
			$lcWhere .= ')';
		}


		// Procedimientos
		$lcTipo='P'; $lcTipTec = "'$lcTipo'";
		if (empty($tcTipTecno) || $tcTipTecno==$lcTipo) {
			if ($tcTipTecno==$lcTipo && $tnCnsTecno>0) $lcWhere .= ' AND S.CNSWCU='.$tnCnsTecno;
			$laPro = $goDb
				->select(array_merge($laCamposPres,[
					$lcTipTec.' TipoTec',
					'S.CNSWCU CnsTec',
					'IFNULL(S.CUPWCU,\'\') Codigo',
					'SUBSTR(IFNULL(C.DESCUP, \'\'), 1, 120) Descripcion',
					'S.CFOWCU Cantidad',
					'\'\' Unidad',
				]))
				->from('WSNPCA P')
				->innerJoin('WSNPCU S', 'P.NPRWCA=S.NPRWCU', null)
				->leftJoin('RIAING I', 'P.INGWCA=I.NIGING', null)
				->leftJoin('TABMAE A', 'A.TIPTMA=\'WSNOPOS\' AND A.CL1TMA=\'AMBATEN\' AND A.CL2TMA=P.CARWCA', null)
				->leftJoin('RIACUP C', 'C.CODCUP=S.CUPWCU', null)
				->leftJoin('MIPRENT ME', 'ME.NUMPRS=P.NPRWCA AND ME.TIPTEC='.$lcTipTec.' AND ME.CONTEC=S.CNSWCU AND ME.ESTENT>0', null)
				//->leftJoin('MIPRREP MR', 'MR.IDENTF=ME.IDENTF AND MR.NUMPRS=P.NPRWCA AND MR.ESTREP>0', null)
				->leftJoin('MIPRREP MR', 'MR.IDENTF=ME.IDENTF AND MR.ESTREP>0', null)
				->leftJoin('MIPRFAC MF', 'MF.NUMPRS=P.NPRWCA AND MF.TIPTEC='.$lcTipTec.' AND MF.CONTEC=S.CNSWCU AND MF.ESTFAC>0', null)
				->where($lcWhere)
				->getAll('array');
		}


		// Medicamentos
		$lcTipo='M'; $lcTipTec = "'$lcTipo'";
		if (empty($tcTipTecno) || $tcTipTecno==$lcTipo) {
			if ($tcTipTecno==$lcTipo && $tnCnsTecno>0) $lcWhere .= ' AND S.CNSWME='.$tnCnsTecno;
			$laMed = $goDb
				->select(array_merge($laCamposPres,[
					$lcTipTec.' TipoTec',
					'S.CNSWME CnsTec',
					//'\'\' Codigo',
					'IFNULL((SELECT CCOJMP FROM NPJSMP WHERE INGJMP=P.INGWCA AND NPRJMP=P.NPRWCA AND CNSJMP=S.CNSWME AND TICJMP=\'500\' FETCH FIRST 1 ROWS ONLY),\'\') Codigo',
					'SUBSTR(IFNULL(S.DPAWME,\'\'), 1, 250) Descripcion',
					'S.CTFWME Cantidad',
					'U.DE2TMA Unidad',
				]))
				->from('WSNPCA P')
				->innerJoin('WSNPME S', 'P.NPRWCA=S.NPRWME', null)
				->leftJoin('RIAING I', 'P.INGWCA=I.NIGING', null)
				->leftJoin('TABMAE A', 'A.TIPTMA=\'WSNOPOS\' AND A.CL1TMA=\'AMBATEN\' AND A.CL2TMA=P.CARWCA', null)
				->leftJoin('TABMAE U', 'U.TIPTMA=\'WSNOPOS\' AND U.CL1TMA=\'UNIFARMA\' AND U.CL2TMA=S.UFAWME', null)
				->leftJoin('MIPRENT ME', 'ME.NUMPRS=P.NPRWCA AND ME.TIPTEC='.$lcTipTec.' AND ME.CONTEC=S.CNSWME AND ME.ESTENT>0', null)
				->leftJoin('MIPRREP MR', 'MR.IDENTF=ME.IDENTF AND MR.ESTREP>0', null)
				->leftJoin('MIPRFAC MF', 'MF.NUMPRS=P.NPRWCA AND MF.TIPTEC='.$lcTipTec.' AND MF.CONTEC=S.CNSWME AND MF.ESTFAC>0', null)
				->where($lcWhere)
				->getAll('array');
		}


		// Productos Nutricionales
		$lcTipo='N'; $lcTipTec = "'$lcTipo'";
		if (empty($tcTipTecno) || $tcTipTecno==$lcTipo) {
			if ($tcTipTecno==$lcTipo && $tnCnsTecno>0) $lcWhere .= ' AND S.CNSWNU='.$tnCnsTecno;
			$laNut = $goDb
				->select(array_merge($laCamposPres,[
					$lcTipTec.' TipoTec',
					'S.CNSWNU CnsTec',
					//'\'\' Codigo',
					'IFNULL((SELECT CCOJMP FROM NPJSMP WHERE INGJMP=P.INGWCA AND NPRJMP=P.NPRWCA AND CNSJMP=S.CNSWNU AND TICJMP=\'701\' FETCH FIRST 1 ROWS ONLY),\'\') Codigo',
					'IFNULL(N.DE2TMA,\'\') Descripcion',
					'S.CTFWNU Cantidad',
					'U.DE2TMA Unidad',
				]))
				->from('WSNPCA P')
				->innerJoin('WSNPNU S', 'P.NPRWCA=S.NPRWNU', null)
				->leftJoin('RIAING I', 'P.INGWCA=I.NIGING', null)
				->leftJoin('TABMAE A', 'A.TIPTMA=\'WSNOPOS\' AND A.CL1TMA=\'AMBATEN\' AND A.CL2TMA=P.CARWCA', null)
				->leftJoin('TABMAE N', 'N.TIPTMA=\'WSNOPOS\' AND N.CL1TMA=\'PRODNUT\' AND N.CL3TMA=S.PNUWNU ', null)
				->leftJoin('TABMAE U', 'U.TIPTMA=\'WSNOPOS\' AND U.CL1TMA=\'FORMNUT\' AND U.CL2TMA=S.UFAWNU', null)
				->leftJoin('MIPRENT ME', 'ME.NUMPRS=P.NPRWCA AND ME.TIPTEC='.$lcTipTec.' AND ME.CONTEC=S.CNSWNU AND ME.ESTENT>0', null)
				->leftJoin('MIPRREP MR', 'MR.IDENTF=ME.IDENTF AND MR.ESTREP>0', null)
				->leftJoin('MIPRFAC MF', 'MF.NUMPRS=P.NPRWCA AND MF.TIPTEC='.$lcTipTec.' AND MF.CONTEC=S.CNSWNU AND MF.ESTFAC>0', null)
				->where($lcWhere)
				->getAll('array');
		}


		// Dispositivos
		$lcTipo='D'; $lcTipTec = "'$lcTipo'";
		if (empty($tcTipTecno) || $tcTipTecno==$lcTipo) {
			if ($tcTipTecno==$lcTipo && $tnCnsTecno>0) $lcWhere .= ' AND S.CNSWDI='.$tnCnsTecno;
			$laDis = $goDb
				->select(array_merge($laCamposPres,[
					$lcTipTec.' TipoTec',
					'S.CNSWDI CnsTec',
					'S.CODWDI Codigo',
					'IFNULL(N.DE2TMA,\'\') Descripcion',
					'S.CFOWDI Cantidad',
					'\'\' Unidad',
				]))
				->from('WSNPCA P')
				->innerJoin('WSNPDI S', 'P.NPRWCA=S.NPRWDI', null)
				->leftJoin('RIAING I', 'P.INGWCA=I.NIGING', null)
				->leftJoin('TABMAE A', 'A.TIPTMA=\'WSNOPOS\' AND A.CL1TMA=\'AMBATEN\' AND A.CL2TMA=P.CARWCA', null)
				->leftJoin('TABMAE N', 'N.TIPTMA=\'WSNOPOS\' AND N.CL1TMA=\'DISPMEDI\' AND N.CL3TMA=S.CODWDI ', null)
				->leftJoin('MIPRENT ME', 'ME.NUMPRS=P.NPRWCA AND ME.TIPTEC='.$lcTipTec.' AND ME.CONTEC=S.CNSWDI AND ME.ESTENT>0', null)
				->leftJoin('MIPRREP MR', 'MR.IDENTF=ME.IDENTF AND MR.ESTREP>0', null)
				->leftJoin('MIPRFAC MF', 'MF.NUMPRS=P.NPRWCA AND MF.TIPTEC='.$lcTipTec.' AND MF.CONTEC=S.CNSWDI AND MF.ESTFAC>0', null)
				->where($lcWhere)
				->getAll('array');
		}


		// Servicios Complementarios
		$lcTipo='S'; $lcTipTec = "'$lcTipo'";
		if (empty($tcTipTecno) || $tcTipTecno==$lcTipo) {
			if ($tcTipTecno==$lcTipo && $tnCnsTecno>0) $lcWhere .= ' AND S.CNSWCO='.$tnCnsTecno;
			$laSrv = $goDb
				->select(array_merge($laCamposPres,[
					$lcTipTec.' TipoTec',
					'S.CNSWCO CnsTec',
					'S.CSCWCO Codigo',
					'IFNULL(N.DE2TMA,\'\') Descripcion',
					'S.CFOWCO Cantidad',
					'\'\' Unidad',
				]))
				->from('WSNPCA P')
				->innerJoin('WSNPCO S', 'P.NPRWCA=S.NPRWCO', null)
				->leftJoin('RIAING I', 'P.INGWCA=I.NIGING', null)
				->leftJoin('TABMAE A', 'A.TIPTMA=\'WSNOPOS\' AND A.CL1TMA=\'AMBATEN\' AND A.CL2TMA=P.CARWCA', null)
				->leftJoin('TABMAE N', 'N.TIPTMA=\'WSNOPOS\' AND N.CL1TMA=\'PRODNUT\' AND N.CL3TMA=S.CSCWCO ', null)
				->leftJoin('MIPRENT ME', 'ME.NUMPRS=P.NPRWCA AND ME.TIPTEC='.$lcTipTec.' AND ME.CONTEC=S.CNSWCO AND ME.ESTENT>0', null)
				->leftJoin('MIPRREP MR', 'MR.IDENTF=ME.IDENTF AND MR.ESTREP>0', null)
				->leftJoin('MIPRFAC MF', 'MF.NUMPRS=P.NPRWCA AND MF.TIPTEC='.$lcTipTec.' AND MF.CONTEC=S.CNSWCO AND MF.ESTFAC>0', null)
				->where($lcWhere)
				->getAll('array');
		}


		// Une todos los resultados y los organiza
		$laPres = array_merge($laPro, $laMed, $laNut, $laSrv, $laDis);
		if (is_array($laPres)) {
			foreach($laPres as $lnIndex=>$laDatos) {
				$laResult[] = array_merge(array_map('trim', $laDatos));
			}
			AplicacionFunciones::ordenarArrayMulti($laResult, ['NUMPRES','TIPOTEC','CNSTEC']);
		}
		return $laResult;
	}


	/*
	 *	Obtiene lista de Direccionamientos
	 *
	 * @param string/integer $tdFechaIni: fecha inicio en formato YYYYmmdd
	 * @param string/integer $tdFechaFin: fecha fin en formato YYYYmmdd
	 * @param string $tcNumPres: número de prescripción
	 * @param integer $tnIngreso: número de ingreso del paciente
	 * @param string $tcTipoDoc: tipo de documento del paciente
	 * @param string/integer $tcNumDoc: número de documento del paciente
	 * @param string $tcCodEps: código de la EPS
	 * @param array $taAmbitos: ámbitos a mostrar
	 * @param string $tcTipTecno: tipo de tecnología
	 * @param integer $tnCnsTecno: consecutivo de tecnología
	 */
	public static function getDireccionamientos(
		$tdFechaIni,
		$tdFechaFin,
		$tcNumPres='',
		$tnIngreso=0,
		$tcTipoDoc='',
		$tcNumDoc='',
		$tcCodEps='',
		$taAmbitos=[],
		$tcTipTecno='',
		$tnCnsTecno=0,
		$tnNumDir=0)
	{
		global $goDb;
		$laPro = $laMed = $laNut = $laSrv = $laDis = $laResult = [];

		// Campos comunes a las consultas
		$laCamposPres = [
				'D.IDENTF Id',
				'D.IDDIRC IdDir',
				'D.FECHAD FecDir',
				'D.NUMPRS NumPres',
				'D.TIPTEC TipoTec',
				'D.CONTEC CnsTec',
				'D.CODSRV CodTecEnt',
				'D.TIPIDP TipIDPac',
				'D.NUMIDP NumIDPac',
				'D.NUMENT NumEnt',
				'D.NUMSBE NumSub',
				'D.FECMAX FecMaxEnt',
				'D.CATTOT CantTotal',
				'D.NITEPS NitEPS',
				'D.CODEPS CodEPS',
				'D.ESTDIR Estado',
				'P.INGWCA Ingreso',
				'IFNULL(I.FEIING,0) FechaIng',
				'IFNULL(I.FEEING,0) FechaEgr',
				'TRIM(P.PNUWCA)||\' \'||TRIM(P.SNUWCA)||\' \'||TRIM(P.PAUWCA)||\' \'||TRIM(P.SAUWCA) Paciente',
				'IFNULL(MP.IDPROG,0) IdPrograma',
				'IFNULL(MP.CODSRV,\'\') CodigoPro',
				'IFNULL(MP.CATTOT,0) CantTotPro',
				'IFNULL(ME.IDENTR,0) IdEntrega',
				'IFNULL(ME.CATTOT,0) CantEntr',
				'IFNULL(MR.IDREPE,0) IdReporte',
				'IFNULL(MF.IDENTF,0) IdentifFact',
				'IFNULL(MF.IDFACT,0) IdFactura',
				'IFNULL(ME.CODSRV,\'\') CodSrvEnt',
				'TRIM(IFNULL(ME.CODENT,\'\')) CodEntAdd',
			];

		// Condición para consultas
		$lcWhere = '';
		switch (true) {
			case !empty($tnNumDir):
				$lcWhere = 'D.IDENTF='.$tnNumDir;
				break;
			case !empty($tcNumPres):
				$lcWhere = 'D.NUMPRS=\''.$tcNumPres.'\'';
				if (!empty($tcTipTecno) && empty($tnCnsTecno))
					$lcWhere .= " AND D.TIPTEC='$tcTipTecno' AND D.CONTEC=$tnCnsTecno";
				break;
			case !empty($tnIngreso):
				$lcWhere = 'P.INGWCA='.$tnIngreso;
				break;
			case !empty($tcTipoDoc) && !empty($tcNumDoc):
				$lcWhere = 'D.TIPIDP=\''.$tcTipoDoc.'\' AND D.NUMIDP=\''.$tcNumDoc.'\'';
				break;
			default:
				$lcWhere = 'D.FECHAD BETWEEN '.$tdFechaIni.' AND '.$tdFechaFin;
				if (!empty($tcCodEps))
					$lcWhere .= " AND D.CODEPS='$tcCodEps'";
		}

		// Procedimientos
		$lcTipo='P'; $lcTipTec = "'$lcTipo'";
		if (empty($tcTipTecno) || $tcTipTecno==$lcTipo) {
			$laPro = $goDb
				->select(array_merge($laCamposPres,[
					'IFNULL(S.CUPWCU,\'\') Codigo',
					'SUBSTR(IFNULL(C.DESCUP, \'\'), 1, 120) Descripcion',
					'S.CFOWCU Cantidad',
					'\'\' Unidad',
				]))
				->from('MIPRDIR D')
				->innerJoin('WSNPCU S', 'D.NUMPRS=S.NPRWCU AND D.CONTEC=S.CNSWCU', null)
				->leftJoin('WSNPCA P', 'D.NUMPRS=P.NPRWCA', null)
				->leftJoin('RIAING I', 'P.INGWCA=I.NIGING', null)
				->leftJoin('RIACUP C', 'C.CODCUP=S.CUPWCU', null)
				->leftJoin('MIPRPRO MP', 'MP.IDENTF=D.IDENTF AND MP.ESTPRO>0', null)
				->leftJoin('MIPRENT ME', 'ME.IDENTF=D.IDENTF AND ME.ESTENT>0', null)
				->leftJoin('MIPRREP MR', 'MR.IDENTF=D.IDENTF AND MR.ESTREP>0', null)
				->leftJoin('MIPRFAC MF', 'MF.NUMPRS=D.NUMPRS AND MF.TIPTEC=D.TIPTEC AND MF.CONTEC=D.CONTEC AND MF.NUMENT=D.NUMENT AND MF.ESTFAC>0', null)
				->where($lcWhere)
				->getAll('array');
		}


		// Medicamentos
		$lcTipo='M'; $lcTipTec = "'$lcTipo'";
		if (empty($tcTipTecno) || $tcTipTecno==$lcTipo) {
			$laMed = $goDb
				->select(array_merge($laCamposPres,[
					'IFNULL((SELECT CCOJMP FROM NPJSMP WHERE INGJMP=P.INGWCA AND NPRJMP=P.NPRWCA AND CNSJMP=S.CNSWME AND TICJMP=\'500\' FETCH FIRST 1 ROWS ONLY),\'\') Codigo',
					'SUBSTR(IFNULL(S.DPAWME,\'\'), 1, 250) Descripcion',
					'S.CTFWME Cantidad',
					'U.DE2TMA Unidad',
				]))
				->from('MIPRDIR D')
				->innerJoin('WSNPME S', 'D.NUMPRS=S.NPRWME AND D.CONTEC=S.CNSWME', null)
				->leftJoin('WSNPCA P', 'D.NUMPRS=P.NPRWCA', null)
				->leftJoin('RIAING I', 'P.INGWCA=I.NIGING', null)
				->leftJoin('TABMAE U', 'U.TIPTMA=\'WSNOPOS\' AND U.CL1TMA=\'UNIFARMA\' AND U.CL2TMA=S.UFAWME', null)
				->leftJoin('MIPRPRO MP', 'MP.IDENTF=D.IDENTF AND MP.ESTPRO>0', null)
				->leftJoin('MIPRENT ME', 'ME.IDENTF=D.IDENTF AND ME.ESTENT>0', null)
				->leftJoin('MIPRREP MR', 'MR.IDENTF=D.IDENTF AND MR.ESTREP>0', null)
				->leftJoin('MIPRFAC MF', 'MF.NUMPRS=D.NUMPRS AND MF.TIPTEC=D.TIPTEC AND MF.CONTEC=D.CONTEC AND ME.NUMENT=MF.NUMENT AND MF.ESTFAC>0', null)
				->where($lcWhere)
				->getAll('array');
		}


		// Productos Nutricionales
		$lcTipo='N'; $lcTipTec = "'$lcTipo'";
		if (empty($tcTipTecno) || $tcTipTecno==$lcTipo) {
			$laNut = $goDb
				->select(array_merge($laCamposPres,[
					'IFNULL((SELECT CCOJMP FROM NPJSMP WHERE INGJMP=P.INGWCA AND NPRJMP=P.NPRWCA AND CNSJMP=S.CNSWNU AND TICJMP=\'701\' FETCH FIRST 1 ROWS ONLY),\'\') Codigo',
					'IFNULL(N.DE2TMA,\'\') Descripcion',
					'S.CTFWNU Cantidad',
					'U.DE2TMA Unidad',
				]))
				->from('MIPRDIR D')
				->innerJoin('WSNPNU S', 'D.NUMPRS=S.NPRWNU AND D.CONTEC=S.CNSWNU', null)
				->leftJoin('WSNPCA P', 'D.NUMPRS=P.NPRWCA', null)
				->leftJoin('RIAING I', 'P.INGWCA=I.NIGING', null)
				->leftJoin('TABMAE N', 'N.TIPTMA=\'WSNOPOS\' AND N.CL1TMA=\'PRODNUT\' AND N.CL3TMA=S.PNUWNU ', null)
				->leftJoin('TABMAE U', 'U.TIPTMA=\'WSNOPOS\' AND U.CL1TMA=\'FORMNUT\' AND U.CL2TMA=S.UFAWNU', null)
				->leftJoin('MIPRPRO MP', 'MP.IDENTF=D.IDENTF AND MP.ESTPRO>0', null)
				->leftJoin('MIPRENT ME', 'ME.IDENTF=D.IDENTF AND ME.ESTENT>0', null)
				->leftJoin('MIPRREP MR', 'MR.IDENTF=D.IDENTF AND MR.ESTREP>0', null)
				->leftJoin('MIPRFAC MF', 'MF.NUMPRS=D.NUMPRS AND MF.TIPTEC=D.TIPTEC AND MF.CONTEC=D.CONTEC AND ME.NUMENT=MF.NUMENT AND MF.ESTFAC>0', null)
				->where($lcWhere)
				->getAll('array');
		}


		// Dispositivos
		$lcTipo='D'; $lcTipTec = "'$lcTipo'";
		if (empty($tcTipTecno) || $tcTipTecno==$lcTipo) {
			$laDis = $goDb
				->select(array_merge($laCamposPres,[
					'S.CODWDI Codigo',
					'IFNULL(N.DE2TMA,\'\') Descripcion',
					'S.CFOWDI Cantidad',
					'\'\' Unidad',
				]))
				->from('MIPRDIR D')
				->innerJoin('WSNPDI S', 'D.NUMPRS=S.NPRWDI AND D.CONTEC=S.CNSWDI', null)
				->leftJoin('WSNPCA P', 'D.NUMPRS=P.NPRWCA', null)
				->leftJoin('RIAING I', 'P.INGWCA=I.NIGING', null)
				->leftJoin('TABMAE N', 'N.TIPTMA=\'WSNOPOS\' AND N.CL1TMA=\'DISPMEDI\' AND N.CL3TMA=S.CODWDI ', null)
				->leftJoin('MIPRPRO MP', 'MP.IDENTF=D.IDENTF AND MP.ESTPRO>0', null)
				->leftJoin('MIPRENT ME', 'ME.IDENTF=D.IDENTF AND ME.ESTENT>0', null)
				->leftJoin('MIPRREP MR', 'MR.IDENTF=D.IDENTF AND MR.ESTREP>0', null)
				->leftJoin('MIPRFAC MF', 'MF.NUMPRS=D.NUMPRS AND MF.TIPTEC=D.TIPTEC AND MF.CONTEC=D.CONTEC AND ME.NUMENT=MF.NUMENT AND MF.ESTFAC>0', null)
				->where($lcWhere)
				->getAll('array');
		}


		// Servicios Complementarios
		$lcTipo='S'; $lcTipTec = "'$lcTipo'";
		if (empty($tcTipTecno) || $tcTipTecno==$lcTipo) {
			$laSrv = $goDb
				->select(array_merge($laCamposPres,[
					'S.CSCWCO Codigo',
					'IFNULL(N.DE2TMA,\'\') Descripcion',
					'S.CFOWCO Cantidad',
					'\'\' Unidad',
				]))
				->from('MIPRDIR D')
				->innerJoin('WSNPCO S', 'D.NUMPRS=S.NPRWCO AND D.CONTEC=S.CNSWCO', null)
				->leftJoin('WSNPCA P', 'D.NUMPRS=P.NPRWCA', null)
				->leftJoin('RIAING I', 'P.INGWCA=I.NIGING', null)
				->leftJoin('TABMAE N', 'N.TIPTMA=\'WSNOPOS\' AND N.CL1TMA=\'PRODNUT\' AND N.CL3TMA=S.CSCWCO ', null)
				->leftJoin('MIPRPRO MP', 'MP.IDENTF=D.IDENTF AND MP.ESTPRO>0', null)
				->leftJoin('MIPRENT ME', 'ME.IDENTF=D.IDENTF AND ME.ESTENT>0', null)
				->leftJoin('MIPRREP MR', 'MR.IDENTF=D.IDENTF AND MR.ESTREP>0', null)
				->leftJoin('MIPRFAC MF', 'MF.NUMPRS=D.NUMPRS AND MF.TIPTEC=D.TIPTEC AND MF.CONTEC=D.CONTEC AND ME.NUMENT=D.NUMENT AND MF.ESTFAC>0', null)
				->where($lcWhere)
				->getAll('array');
		}


		// Une todos los resultados y los organiza
		$laPres = array_merge($laPro, $laMed, $laNut, $laSrv, $laDis);
		if (is_array($laPres)) {
			foreach($laPres as $lnIndex=>$laDatos) {
				$laResult[] = array_merge(array_map('trim', $laDatos));
			}
			AplicacionFunciones::ordenarArrayMulti($laResult, ['NUMPRES','TIPOTEC','CNSTEC']);
		}
		return $laResult;
	}


	/* Obtiene datos de una Acción (Programación, Entrega, Reporte de Entrega, Reporte de Factura) */
	public static function getAcciones($tcTipo, $tnId)
	{
		global $goDb;
		$laRta = [];

		switch ($tcTipo) {

			case 'Programa':
				$laRta = $goDb->from('MIPRPRO')->where(['IDPROG'=>$tnId])->get('array');
				break;

			case 'Entrega':
				$laRta = $goDb->from('MIPRENT')->where(['IDENTR'=>$tnId])->get('array');
				break;

			case 'Reporte':
				$laRta = $goDb->from('MIPRREP')->where(['IDREPE'=>$tnId])->get('array');
				break;

			case 'Factura':
				$laRta = $goDb->from('MIPRFAC')->where(['IDFACT'=>$tnId])->get('array');
				break;
		}

		return array_map('trim', $laRta);
	}


	/* Actualiza Entrega Códigos */
	public static function actualizaEntCod($tcNumPres, $tnIdEntrega)
	{
		global $goDb;

		// Consulta códigos adicionales
		$lnIndEntr=-1;
		$laVar = [
			'url' => self::fcVariables('urlDispensar').'EntregaXPrescripcion/{nit}/{tokentmp}/{NoPresc}',
			'numPrs' => $tcNumPres,
		];
		$laRetorna = self::fnConsumirMiPres($laVar, 'GET');
		foreach($laRetorna['MIPRES'] as $lnIndice=>$loEntrega) {
			if($loEntrega->IDEntrega==$tnIdEntrega){
				$lnIndEntr=$lnIndice;
			}
		}
		if($lnIndEntr>=0){
			if(!empty($laRetorna['MIPRES'][$lnIndEntr]->CodigosEntrega)){
				$lcCodEnt = json_encode($laRetorna['MIPRES'][$lnIndEntr]->CodigosEntrega);
				$lcUsuario = $_SESSION[HCW_NAME]->oUsuario->getUsuario();
				$lcProgram = substr(pathinfo(__FILE__, PATHINFO_FILENAME),6,10);
				$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
				$lcFecha = $ltAhora->format('Ymd');
				$lcHora  = $ltAhora->format('His');
				$laDatos = [
					'CODENT' => $lcCodEnt,
					'USMEMP' => $lcUsuario,
					'PGMEMP' => $lcProgram,
					'FEMEMP' => $lcFecha,
					'HOMEMP' => $lcHora,
				];
				$goDb->tabla('MIPRENT')->where(['IDENTR'=>$tnIdEntrega])->actualizar($laDatos);

				return true;
			}
		}
		return false;
	}

}
