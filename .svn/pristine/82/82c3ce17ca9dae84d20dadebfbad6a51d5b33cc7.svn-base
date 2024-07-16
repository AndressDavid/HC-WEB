<?php
namespace NUCLEO;

require_once __DIR__ . '/../../nucleo/controlador/class.Db.php';
require_once __DIR__ . '/../../nucleo/controlador/class.AplicacionFunciones.php';
require_once __DIR__ . '/../../nucleo/controlador/class.FeFunciones.php';
require_once __DIR__ . '/../../nucleo/controlador/class.FeConsultar06FA.php';
use NUCLEO\Db;
use NUCLEO\AplicacionFunciones;
use NUCLEO\FeFunciones;
use NUCLEO\FeConsultar06FA;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;


class DatosPlanosRips
{
	protected $cDb;
	protected $aConfigFac;
	protected $aConfigEnt;
	protected $aTipos=[];
	public $aFactura;


	function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}


	public function consultarFactura($tnFactura)
	{
		$laConfigFac = require __DIR__ . '/../privada/fe_config.php';
		$loFE=new FeConsultar06FA($laConfigFac, '');
		$loFE->consultaCabecera($tnFactura);
		return $this->aFactura = [
			'cabecera' => $loFE->aDatosFactura(),
			'paciente' => $loFE->aDatosPaciente(),
			'cliente' => $loFE->aDatosAdquiriente(),
			'error' => $loFE->aError(),
		];
	}


	public function consultarDatos($tcTipoFuncion, $tcTipoResultado, $taDatos=[])
	{
		$this->obtenerListaTipos();
		if(!in_array($tcTipoFuncion, array_keys($this->aTipos))){
			return $laRespuesta=['error'=>'Tipo de formato no configurado.'];
		}
		$laResultados=explode('|',$this->aTipos[$tcTipoFuncion]['RES']);
		if(!in_array($tcTipoResultado, $laResultados)){
			return $laRespuesta=['error'=>'Tipo de resultado no configurado.'];
		}
		$this->aConfigEnt = $this->obtenerConfigEntidad($tcTipoFuncion);

		return $this->{$this->aTipos[$tcTipoFuncion]['FUN']}($taDatos, $tcTipoResultado);
	}


	/*
	 *	Retorna datos de facturas con formato Elyon para Compensar
	 *
	 */
	public function fnCompensarElyon($taDatos, $tcTipoResultado)
	{
		// Array datos Encabezado y Detalle
		$laRes = ['ENC'=>[],'DET'=>[]];

		// Datos enviados
		$laFacturas = $taDatos['facturas'];
		$lcCategoria = $taDatos['categoria'];

		// Consulta se genera a partir de clase de facturación
		$laConfigFac = require __DIR__ . '/../privada/fe_config.php';
		$laConfigFac['parFac']['addSubDetalles']=false;
		$loFE=new FeConsultar06FA($laConfigFac, '');


		ini_set('max_execution_time', 7200); // 7200 segundos = 120 minutos
		ini_set('memory_limit', '-1');


		foreach($laFacturas as $lnFactura){

			$loFE->consultaCabecera($lnFactura);
			$loFE->consultaDetalle();

			$laFactura = $loFE->aDatosFactura();
			$laPaciente = $loFE->aDatosPaciente();
			$laDetalles = $loFE->aDatosDetalles();


			// *** DATOS ENCABEZADO ***
			$laRes['ENC'][] = [
				'CODEPS'=>'8',
				'NITIPS'=>'860006656',
				'PREFIJO'=>'',
				'FACTURA'=>$laFactura['FRACAB'],
				'FECHA'=>AplicacionFunciones::formatFechaHora('fecha', $laFactura['FEFCAB'], ''),
				'VALOR'=>$laFactura['VAFCAB'],
			];
			$lnCopagoFac=$laFactura['COPAGO'];
			$laDF = [
				'INGRESO'	=>$laFactura['NIGING'],
				'FACTURA'	=>$laFactura['FRACAB'],
				'PLAN'		=>$laFactura['PLNCAB'],
				'ESNOPOS'	=>$laFactura['esNoPOS'],
				'ESNOPOS'	=>$laFactura['esNoPOS'],
				'ESNOPOS'	=>$laFactura['esNoPOS'],
				'FECHAING'	=>$laFactura['FEIING'],
				'FECHAEGR'	=>$laFactura['FEEING'],
				'TIPOID'	=>($this->aConfigEnt['HOMDOC'][$laPaciente['tipoId']]??$laPaciente['tipoId']),
				'NUMID'		=>$laPaciente['numeId'],
				'APELLIDO1'	=>$this->quitarEspeciales($laPaciente['apellido1']),
				'APELLIDO2'	=>$this->quitarEspeciales($laPaciente['apellido2']),
				'NOMBRE1'	=>$this->quitarEspeciales($laPaciente['nombre1']),
				'NOMBRE2'	=>$this->quitarEspeciales($laPaciente['nombre2']),
			];
			unset($laFactura,$laPaciente);

			// Causa externa
			// $laDF['CAUSAEXT']='13';
			$laCausa=$this->oDb->select('SUBIND')->from('RIAHIS')->where("NROING={$laDF['INGRESO']} AND INDICE=5 AND CODIGO=1 AND PGMHIS IN ('HCPPALWEB','HCPPAL')")->get('array');
			$laDF['CAUSAEXT']=trim($laCausa['SUBIND']??'13');
			// Diagnóstico
			// $laDF['DIAGNOS']='';
			$laDx=$this->oDb->select('DIPEDC')->from('EVODIA')->where("INGEDC={$laDF['INGRESO']} AND TIPEDC IN ('HC','HP','HU','RF') AND INDEDC=1")->orderBy('FECEDC DESC,HOREDC DESC')->get('array');
			$laDF['DIAGNOS']=trim($laDx['DIPEDC']??'');



			// *** DATOS AUTORIZACIONES ***
			$lcAutorizaCierre='';
			$laAutoriza=[];

			// Autorizaciones NO POS
			if($laDF['ESNOPOS']){
				$lcSql = implode(' ', [
					"SELECT D.TINNOD TIPO, TRIM(D.INSNOD) COD, M.FEHOMAX, D.OP5NOD AUT, SUM(D.CGRNOD) CANT",
					"FROM NPOSDE D",
						"INNER JOIN (",
							"SELECT I.INGNOD, I.TINNOD, I.INSNOD, I.OP3NOD, MAX(I.FECNOD*1000000+I.HORNOD) FEHOMAX",
							"FROM NPOSDE I",
							"WHERE I.INGNOD=:ingresop AND I.ESTNOD='30' AND I.TINNOD='400'",
							"GROUP BY I.INGNOD, I.TINNOD, I.INSNOD, I.OP3NOD",
						") M ON	M.INGNOD=D.INGNOD",
							"AND M.TINNOD=D.TINNOD",
							"AND M.INSNOD=D.INSNOD",
							"AND M.OP3NOD=D.OP3NOD",
							"AND D.FECNOD*1000000+D.HORNOD=M.FEHOMAX",
					"WHERE D.ESTNOD='30'",
					"GROUP BY D.TINNOD, D.INSNOD, M.FEHOMAX, D.OP5NOD",
					"UNION ALL",
					"SELECT DISTINCT C.TINNOC TIPO, TRIM(C.INSNOC) COD, C.FRENOC*1000000+C.HRENOC FEHOMAX, D.OP5NOD AUT, C.OP7NOC CANT",
					"FROM NPOSCA C",
						"INNER JOIN NPOSDE D",
							"ON	C.INGNOC=D.INGNOD",
							"AND C.TINNOC=D.TINNOD",
							"AND C.INSNOC=D.INSNOD",
							"AND C.NUJNOC=D.NUJNOD",
						"INNER JOIN (",
							"SELECT I.INGNOD, I.TINNOD, I.INSNOD, I.NUJNOD, MAX(I.FECNOD*1000000+I.HORNOD) FEHOMAX",
							"FROM NPOSDE I",
							"WHERE I.INGNOD=:ingresom AND I.ESTNOD='30' AND I.TINNOD<>'400'",
							"GROUP BY I.INGNOD, I.TINNOD, I.INSNOD, I.NUJNOD",
						") M ON	M.INGNOD=D.INGNOD",
							"AND M.TINNOD=D.TINNOD",
							"AND M.INSNOD=D.INSNOD",
							"AND M.NUJNOD=D.NUJNOD",
							"AND D.FECNOD*1000000+D.HORNOD=M.FEHOMAX",
					"WHERE D.ESTNOD='30'",
					"ORDER BY 1,2,3",
				]);
				$laDatVin=[':ingresop'=>$laDF['INGRESO'],':ingresom'=>$laDF['INGRESO']];
				$laAuts=$this->oDb->query($lcSql,$laDatVin,true);
				if($this->oDb->numRows()>0){
					foreach($laAuts as $laAut){
						$laAutoriza['_'.$laAut['COD']][]=['AUT'=>$laAut['AUT'],'CANT'=>$laAut['CANT'],'USO'=>true];
					}
				}

			// Autorizaciones POS
			}else{
				// Busca autorización cierre plan
				$laAuts=$this->oDb
					->select('CONAUS, TRIM(DESAUS) DESAUS')
					->from('AUTASE')
					->where("INGAUS={$laDF['INGRESO']} AND INDAUS=4")
					->orderBy('CONAUS')
					->getAll('array');
				if($this->oDb->numRows()>0){
					$laAutos=[];
					$lnCnsAut='0000';
					foreach($laAuts as $laAut){
						if(mb_substr($laAut['DESAUS'],0,17,'UTF-8')=='No. Autorización:'){
							$laAutos[$laAut['CONAUS']]=trim(mb_substr($laAut['DESAUS'],17,NULL,'UTF-8'));
						}else
						if(mb_substr($laAut['DESAUS'],0,12,'UTF-8')=='Plan Cierre:'){
							$lcPlan=trim(mb_substr($laAut['DESAUS'],12,NULL,'UTF-8'));
							$laPlan=explode('-',$lcPlan);
							$lcCodPlan=trim($laPlan[0]);
							if($lcCodPlan==$laDF['PLAN']){
								$lnCnsAut=$laAut['CONAUS'];
								break;
							}
						}
					}
					$lcAutorizaCierre=$laAutos[$lnCnsAut]??'';
				}

				// Busca autorizaciones por elemento
				if(empty($lcAutorizaCierre)){
					$laAuts=$this->oDb
						->select('CITAAX CITA, TRIM(PROAAX) COD, TRIM(AUTAAX) AUT')
						->from('AUTANX')
						//->where("INGAAX={$laDF['INGRESO']} AND PLAAAX='{$laDF['PLAN']}'") // Plan no coindice siempre ej Fact 5776114 ing 3463157
						->where("INGAAX={$laDF['INGRESO']}")
						->getAll('array');
					if($this->oDb->numRows()>0){
						foreach($laAuts as $laAut){
							// $laAutoriza[$laAut['CITA']]['_'.$laAut['COD']]=$laAut['AUTAAX']; // Cita no se encuentra en detalle facturación
							$laAutoriza['_'.$laAut['COD']][]=['AUT'=>$laAut['AUT'],'CANT'=>1];
						}
					}
				}
			}



			// *** DATOS DETALLES ***
			$lbRegUno=true;
			foreach($laDetalles as $laDetalle){
				if ($this->aConfigEnt['COPAGO']['detalle']) {
					$lnCopago = $this->aConfigEnt['COPAGO']['soloReg1'] ? ($laDetalle['tipoItem']=='900' ? $lnCopagoFac : 0) : $lnCopagoFac;
				} else {
					if ($laDetalle['tipoItem']=='900') continue;
					$lnCopago = $this->aConfigEnt['COPAGO']['soloReg1'] ? ($lbRegUno ? $lnCopagoFac : 0) : $lnCopagoFac;
					$lbRegUno=false;
				}
				$lcCodItem=$laDetalle['codShaio']??$laDetalle['codItem'];
				if($laDF['ESNOPOS']){
					if(isset($laAutoriza['_'.$lcCodItem])){
						$lnCntTotal=0;
						foreach($laAutoriza['_'.$lcCodItem] as $lnKey=>$laAut){
							if($laAut['USO']){
								$lnCntItem=$laAut['CANT'];
								$lnCntTotal+=$lnCntItem;
								$lcAutoriza=$laAut['AUT'];
								if($lnCntTotal>$laDetalle['cantidad']){
									$lnCntItem-=($lnCntTotal-$laDetalle['cantidad']);
									if($lnCntItem>0){
										$laRes['DET'][]=$this->insertarDetalleElyon($laDF, $laDetalle, $lcAutoriza, $lnCntItem, $lnCopago, true);
									}
									break;
								}else{
									if($lnCntItem>0){
										$laRes['DET'][]=$this->insertarDetalleElyon($laDF, $laDetalle, $lcAutoriza, $lnCntItem, $lnCopago, true);
									}
								}
								$laAutoriza['_'.$lcCodItem][$lnKey]['USO']=false;
							}
						}
						if($lnCntTotal<$laDetalle['cantidad']){
							$lnCntItem=$laDetalle['cantidad']-$lnCntTotal;
							$lcAutoriza='';
							if($lnCntItem>0){
								$laRes['DET'][]=$this->insertarDetalleElyon($laDF, $laDetalle, $lcAutoriza, $lnCntItem, $lnCopago, true);
							}
						}
					}else{
						$lnCntItem=str_replace('.00','',$laDetalle['cantidad']);
						$lcAutoriza='';
						$laRes['DET'][]=$this->insertarDetalleElyon($laDF, $laDetalle, $lcAutoriza, $lnCntItem, $lnCopago, false);
					}
				}else{
					$lcAutoriza=empty($lcAutorizaCierre) ? (
						isset($laAutoriza['_'.$lcCodItem]) ?
							$lcAutoriza=$laAutoriza['_'.$lcCodItem][0]['AUT'] :
							'') :
						$lcAutorizaCierre;
					$lnCntItem=str_replace('.00','',$laDetalle['cantidad']);
					$laRes['DET'][]=$this->insertarDetalleElyon($laDF, $laDetalle, $lcAutoriza, $lnCntItem, $lnCopago, false);
				}
			}
		}
		unset($loFE);

		$lcTxt = $this->aConfigEnt['HTMLRET'];
		switch($tcTipoResultado){
			case 'TABLA':
				$lcTblEnc = $this->generaTabla($laRes['ENC']);
				$lcTblDet = $this->generaTabla($laRes['DET']);
				$lcTxt = str_replace(['||ENCABEZADO||','||DETALLES||'],['<div id="datPlEncabezado">'.$lcTblEnc.'</div>','<div id="datPlDetalle">'.$lcTblDet.'</div>'],$lcTxt);
				return ['datos'=>$lcTxt, 'error'=>''];
				break;
			case 'PLANO':
				$lcSep='|';
				$lcTxtEnc = $this->generaPlano($laRes['ENC'],$lcSep);
				$lcTxtDet = $this->generaPlano($laRes['DET'],$lcSep);
				$lcTxt = str_replace(['||ENCABEZADO||','||DETALLES||'],['<pre id="datPlEncabezado">'.$lcTxtEnc.'</pre>','<pre id="datPlDetalle">'.$lcTxtDet.'</pre>'],$lcTxt);
				return ['datos'=>$lcTxt, 'error'=>''];
				break;
			case 'EXCEL':
				$laTitulos = ['ENC'=>'Encabezado','DET'=>'Detalle'];
				$lcMensaje = $this->generaXlsx($laRes, $laTitulos, $lcCategoria);
				return ['datos'=>$lcMensaje, 'error'=>''];
				break;
		}
	}

	private function insertarDetalleElyon($taDataFac, $taDetalle, $tcNumAut, $tnCant, $tnCopago, $tbCalcular)
	{
		$lnVrUnidad = abs(str_replace('.00','',$taDetalle['vrUnidad']));
		$lnVrTotal = $tbCalcular ? $tnCant*$lnVrUnidad : abs($taDetalle['vrTotal']);
		return [
			'PREFIJO'	=>'',
			'FACTURA'	=>$taDataFac['FACTURA'],
			'AUTORIZA'	=>$tcNumAut,
			'CODSERV'	=>$taDetalle['codItem'],
			'DSCSERV'	=>$this->quitarEspeciales($taDetalle['dscItem']),
			'CANTIDAD'	=>$tnCant,
			'VRUNIDAD'	=>$lnVrUnidad,
			'VRTOTAL'	=>$lnVrTotal,
			'COPAGO'	=>$tnCopago,
			'DIAGNOS'	=>$taDataFac['DIAGNOS'],
			'TIPOID'	=>$taDataFac['TIPOID'],
			'NUMID'		=>$taDataFac['NUMID'],
			'APELLIDO1'	=>$taDataFac['APELLIDO1'],
			'APELLIDO2'	=>$taDataFac['APELLIDO2'],
			'NOMBRE1'	=>$taDataFac['NOMBRE1'],
			'NOMBRE2'	=>$taDataFac['NOMBRE2'],
			'FECHAING'	=>$taDataFac['FECHAING'],
			'FECHAEGR'	=>$taDataFac['FECHAEGR'],
			'CAUSAEXT'	=>$taDataFac['CAUSAEXT'],
		];
	}


	private function generaPlano($taArray, $tcSep='|')
	{
		$lcTxt='';
		foreach($taArray as $laFila){
			$lcTxt.=implode($tcSep,$laFila).PHP_EOL;
		}
		return $lcTxt;
	}


	private function generaTabla($taArray)
	{
		$laKeys = array_keys($taArray[0]);
		//$lcTbl = '<table style="border-collapse: collapse;" border="1" cellpadding="3"><thead><tr>';
		$lcTbl = '<table class="table table-hover table-bordered table-sm table-responsive"><thead class="thead-dark"><tr>';
		foreach($laKeys as $lcKey){
			$lcTbl.= "<th>$lcKey</th>";
		}
		$lcTbl.= '</tr></thead><tbody>';
		foreach($taArray as $laFila){
			$lcTbl.= '<tr>';
			foreach($laFila as $lcCampo){
				$lcTbl.= "<td>$lcCampo</td>";
			}
			$lcTbl.= '</tr>';
		}
		$lcTbl.= '</tbody></table>';
		return $lcTbl;
	}


	private function generaXlsx($taDatos, $taTitulos=[], $tcNombreArchivo='')
	{
		require_once __DIR__ .'/../publico/complementos/spout/3.0.1/Spout/Autoloader/autoload.php';

		$ltAhora=new \DateTime($this->oDb->fechaHoraSistema());
		$lcArchivo=empty($tcNombreArchivo) ? 'Libro_'.$ltAhora->format('Y-m-d-H-i-s').'.xlsx' : str_replace(' ','_',trim($tcNombreArchivo)).'.xlsx';

		// Crear libro y establecer propiedades
		$loLibro=WriterEntityFactory::createXLSXWriter();
		//$loLibro->openToFile($lcArchivo);
		$loLibro->openToBrowser($lcArchivo); // Descargar el archivo

		// Estilos para los títulos
		$loColorBorde=Color::BLUE;
		$loWidthBorde=Border::WIDTH_THIN;
		$loStyleBorde=Border::STYLE_SOLID;
		$loBorderTitulo=(new BorderBuilder())
			->setBorderAll($loColorBorde, $loWidthBorde, $loStyleBorde)
			->build();
		$loStyleTitulo=(new StyleBuilder())
			->setBackgroundColor(Color::rgb(214,234,248))
			->setFontColor($loColorBorde)
			->setFontBold()
			->setBorder($loBorderTitulo)
			->build();
		$lnNumHoja=1;
		foreach($taDatos as $laTipo=>$laData){
			if (is_array($laData) && count($laData)>0 && is_array($laData[0]) && count($laData[0])>0) {
				$loHoja=$lnNumHoja==1? $loLibro->getCurrentSheet(): $loLibro->addNewSheetAndMakeItCurrent();
				$loHoja->setName($taTitulos[$laTipo]??'Hoja'.$lnNumHoja);
				$laTitulos=array_keys($laData[0]);
				$loLibro->addRow(WriterEntityFactory::createRowFromArray($laTitulos, $loStyleTitulo));
				foreach($laData as $laFila){
					$loLibro->addRow(WriterEntityFactory::createRowFromArray(array_map('trim', array_values($laFila))));
				}
				$lnNumHoja++;
			}
		}
		$loHojas=$loLibro->getSheets();
		$loLibro->setCurrentSheet($loHojas[0]);
		$loLibro->close();
		exit();
		return 'Archivo xlsx generado';
	}


	private function quitarEspeciales($tcString)
	{
		//return preg_replace('/^[[:alnum:]]+$/','',$tcString);
		//return preg_replace("/^[[:alnum:]]+$/",'',$tcString);
		//return preg_replace("/^[a-z0-9]+$/i",'',$tcString);
		//return preg_replace('([^A-Za-z0-9])', '', $tcString);
		$laNormalizeChars = [
			'Á'=>'A', 'É'=>'E', 'Í'=>'I', 'Ó'=>'O', 'Ú'=>'U', 'Ñ'=>'N',
			'á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u', 'ñ'=>'n',
			'\''=>'', '"'=>'', '#'=>'', '('=>'', ')'=>'', '['=>'', ']'=>'', '{'=>'', '}'=>'', '¡'=>'', '!'=>'', '¿'=>'', '?'=>'', '^'=>'',
			'\\'=>'', '/'=>'', '%'=>'', '&'=>'', '$'=>'', '='=>'', '¨'=>'', '+'=>'', '-'=>'', '*'=>'', ','=>'', '.'=>'', ';'=>'', ':'=>'',
			'À'=>'A', 'Ã'=>'A', 'Æ'=>'A', 'Â'=>'A', 'Å'=>'A', 'Ä'=>'Ae',
			'È'=>'E', 'Ë'=>'E', 'Ê'=>'E',
			'Ï'=>'I', 'Î'=>'I', 'Ì'=>'I',
			'Ø'=>'O', 'Ò'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'Oe', 'Ø'=>'O',
			'Ù'=>'U', 'Û'=>'U', 'Ü'=>'UE',
			'Þ'=>'B', 'Ç'=>'C', 'Ŕ'=>'R', 'Š'=>'S', 'Ș'=>'S', 'Ş'=>'S', 'Ț'=>'t', 'Ţ'=>'t', 'Ý'=>'Y', 'Ž'=>'Z',
			'â'=>'a', 'ã'=>'a', 'å'=>'a', 'à'=>'a', 'ä'=>'ae', 'æ'=>'ae',
			'è'=>'e', 'ë'=>'e', 'ê'=>'e',
			'î'=>'i', 'ï'=>'i', 'ì'=>'i',
			'ø'=>'o', 'ò'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'oe', 'ø'=>'o',
			'ù'=>'u', 'û'=>'u', 'ü'=>'ue',
			'þ'=>'b', 'ç'=>'c', 'ŕ'=>'r', 'š'=>'s', 'ș'=>'s', 'ş'=>'s', 'ț'=>'t', 'ţ'=>'t', 'ý'=>'y', 'ž'=>'z',
		];
		$tcString = strtr($tcString, $laNormalizeChars);
		return $tcString;
	}


	private function obtenerConfigEntidad($tcCodEnt)
	{
		$laConfEnt = [];
		$laConfigs = $this->oDb
			->select('TRIM(CL2TMA) CODIGO, TRIM(CL3TMA) LINEA, TRIM(DE1TMA) DESC, DE2TMA||OP5TMA OPCIONES, TRIM(OP2TMA) TIPO')
			->from('TABMAE')
			->where("TIPTMA='PLANOENT' AND CL1TMA='$tcCodEnt' AND CL2TMA<>'' AND ESTTMA=''")
			->orderBy('CL1TMA, CL2TMA, CL3TMA')
			->getAll('array');
		if($this->oDb->numRows()>0){
			$laCnfTmp=[];
			foreach($laConfigs as $laConfig){
				$laCnfTmp[$laConfig['CODIGO']]=[
					't'=>$laConfig['TIPO'],
					'o'=>($laCnfTmp[$laConfig['CODIGO']]['o']??'').$laConfig['OPCIONES'],
				];
			}
			foreach($laCnfTmp as $lcKey=>$laConfig){
				$lcOpc = trim($laConfig['o']);
				$laConfEnt[$lcKey] = $laConfig['t']=='JSON' ? json_decode($lcOpc, true) : $lcOpc;
			}
		}

		return $laConfEnt;
	}


	private function obtenerListaTipos()
	{
		$this->aTipos=[];
		$laTipos = $this->oDb
			->select('TRIM(CL1TMA) CODIGO, TRIM(DE1TMA) DESC, TRIM(DE2TMA||OP5TMA) OPCIONES, TRIM(OP2TMA) TIPO')
			->from('TABMAE')
			->where("TIPTMA='PLANOENT' AND CL1TMA<>'' AND CL2TMA='' AND ESTTMA=''")
			->getAll('array');
		if($this->oDb->numRows()>0){
			foreach($laTipos as $laTipo){
				$this->aTipos[$laTipo['CODIGO']] = json_decode(trim($laTipo['OPCIONES']), true);
			}
		}
	}
	public function aTipos()
	{
		if(count($this->aTipos)==0) $this->obtenerListaTipos();
		return $this->aTipos;
	}

}