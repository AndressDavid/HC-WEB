<?php
namespace NUCLEO;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;

require_once __DIR__ . '/class.Db.php';


class ConsultasAs400
{
	protected $oDb;
	protected $aTiposDato=[];
	protected $cTiposDato='';
	public $cErr='';


	function __construct()
	{
		global $goDb;
		$this->oDb=$goDb;
	}

	/*
	 *	Genera archivo a partir de los datos obtenidos en la consulta
	 *	@param $taDatos: array de datos, los títulos se obtienen de los key del array
	 *	@param $tcNombreHoja: string con el nombre de la hoja
	 */
	public function exportar($taDatos, $tcNombreHoja='Hoja1', $tcNombreArchivo='')
	{
		ini_set('max_execution_time', 3600); //3600 segundos=60 minutos
		ini_set('memory_limit', '-1');

		require_once __DIR__ .'/../publico/complementos/spout/3.0.1/Spout/Autoloader/autoload.php';

		$ltAhora=new \DateTime( $this->oDb->fechaHoraSistema() );
		$lcArchivo=(empty($tcNombreArchivo)? 'Export': trim($tcNombreArchivo)).'_'.$ltAhora->format('Y-m-d-H-i-s').'.xlsx';

		// Crear libro y establecer propiedades
		$loLibro=WriterEntityFactory::createXLSXWriter();
		//$loLibro->openToFile($lcArchivo);
		$loLibro->openToBrowser($lcArchivo); // Descargar el archivo

		// Estilos para los títulos
		$loColor=Color::BLUE;
		$loWidth=Border::WIDTH_THIN;
		$loStyle=Border::STYLE_SOLID;
		$loBorderTitulo=(new BorderBuilder())
			->setBorderAll($loColor, $loWidth, $loStyle)
			->build();
		$loStyleTitulo=(new StyleBuilder())
			->setBackgroundColor(Color::rgb(214,234,248))
			->setFontColor($loColor)
			->setFontBold()
			->setBorder($loBorderTitulo)
			->build();

		// Adiciona Hoja
		$loHoja=$loLibro->getCurrentSheet();
		$loHoja->setName($tcNombreHoja);

		// Adiciona los títulos
		$laTitulos=array_keys($taDatos['rows'][0]);
		$loCeldas=[];
		foreach($laTitulos as $lcTitulo){
			$loCeldas[]=WriterEntityFactory::createCell($lcTitulo, $loStyleTitulo);
		}
		$loFila=WriterEntityFactory::createRow($loCeldas);
		$loLibro->addRow($loFila);

		// Adiciona los registros
		foreach($taDatos['rows'] as $laFila){
			$loLibro->addRow(WriterEntityFactory::createRowFromArray(array_map('trim', array_values($laFila))));
		}
		$loLibro->setCurrentSheet($loHoja);
		$loLibro->close();

		exit;
	}

	/*
	 *	Retorna la lista de consultas activas para el usuario actual
	 */
	public function listaConsultas()
	{
		$laReturn=[];
		$lcUsuario=isset($_SESSION[HCW_NAME]) ? $_SESSION[HCW_NAME]->oUsuario->getUsuario() : '';
		$lbEsAdmin=false;
		$lcPermisos='';

		// Archivos que puede acceder el usuario
		$laPermisos=$this->oDb
			->select('CL3TMA AS LINEA, DE2TMA || OP5TMA AS PERMISOS, OP2TMA AS ESADMIN')
			->from('TABMAE')
			->where("TIPTMA='EXPFILES' AND CL1TMA='USUARIOS' AND CL2TMA='{$lcUsuario}' AND CL3TMA<>'' AND ESTTMA=''")
			->orderBy('CL3TMA')
			->getAll('array');
		if(is_array($laPermisos)){
			if(count($laPermisos)>0){
				$lbEsAdmin=trim($laPermisos[0]['ESADMIN'])=='ADMIN';
				foreach($laPermisos as $laPermiso){
					$lcPermisos .= $laPermiso['PERMISOS'];
				}
				$lcPermisos=trim($lcPermisos);
			}
		}

		if($lbEsAdmin || !empty($lcPermisos)){
			$this->oDb->distinct()
				->select('TRIM(CL2TMA) AS CODIGO, TRIM(DE1TMA) AS TITULO, TRIM(OP2TMA) AS TIPO, OP1TMA AS CLIPAG')
				->from('TABMAE')
				->WHERE("TIPTMA='EXPFILES' AND CL1TMA='EXP_SQL' AND CL2TMA<>'' AND CL3TMA='01' AND ESTTMA=''");
			if (!$lbEsAdmin){
				$this->oDb->in('CL2TMA', explode(',',$lcPermisos));
			}
			$laLista=$this->oDb
				->orderBy('TRIM(DE1TMA)')
				->getAll('array');

			if(is_array($laLista)){
				foreach($laLista as $laElemento){
					$laReturn[$laElemento['CODIGO']]=[
						'TITULO'=>$laElemento['TITULO'],
						'TIPO'	=>$laElemento['TIPO'],
						'CLIPAG'=>$laElemento['CLIPAG'],
					];
				}
			}
		}

		return $laReturn;
	}

	/*
	 *	Obtiene datos de los controles para la consulta
	 *	@param $tcCodigo: string, código de la consulta
	 *	@param $tcTitulo: string, título para la consulta
	 */
	public function obtenerParametrosJson($tcCodigo, $tcTitulo)
	{
		$lcJson='';

		// Archivos que puede acceder el usuario
		$laParams=$this->oDb
			->select('TRIM(CL3TMA) AS CODIGO, TRIM(DE1TMA) AS VARIABLE, TRIM(OP1TMA) AS TIPO, TRIM(DE2TMA || OP5TMA) AS CONFIG')
			->from('TABMAE')
			->where("TIPTMA='EXPFILES' AND CL1TMA='EXP_PAR' AND CL2TMA='$tcCodigo' AND ESTTMA=''")
			->orderBy('INT(CL3TMA)')
			->getAll('array');
		if(is_array($laParams)){
			if(count($laParams)>0){

				$lcJson='{"Opciones": {"Codigo":"'.$tcCodigo.'","Titulo":"'.$tcTitulo.'"}, "Controles":{';
				$lcComa='';

				foreach($laParams as $laParam){
					$lcConfig=$laParam['CONFIG'];

					// genera cadena json
					$lcJson.=$lcComa
						.'"'.str_pad($laParam['CODIGO'],4,'0',STR_PAD_LEFT).'":{'
						.'"variable":"'.$laParam['VARIABLE'].'",'
						.'"tipo":"'.$laParam['TIPO'].'",'.$lcConfig.'}';
					$lcComa=',';
				}
				$lcJson.='}}';

				require_once __DIR__ . '/class.frmInput.php';
				$loFrmInput = new frmInput();
				$lcJson=$loFrmInput->validarJson($lcJson);
				$this->cErr = $loFrmInput->cErrVal;
			}
		}

		return $lcJson;
	}

	/*
	 *	Obtiene tipo de consulta y la sentencia SQL o la función a ejecutar
	 *	@param $tcCodigo: string, código de la consulta
	 */
	public function obtenerTipoSQL($tcCodigo)
	{
		$laReturn = [ 'tipo'=>'', 'sql'=>'', ];
		$laDatos=$this->oDb
			->select('DE2TMA || OP5TMA AS SENTENCIA, OP2TMA AS TIPO')
			->from('TABMAE')
			->where("TIPTMA='EXPFILES' AND CL1TMA='EXP_SQL' AND CL2TMA='$tcCodigo' AND ESTTMA=''")
			->orderBy('INT(CL3TMA)')
			->getAll('array');
		if(is_array($laDatos)){
			if(count($laDatos)>0){
				$lcSql = '';
				foreach($laDatos as $laDato){
					$lcSql.=$laDato['SENTENCIA'];
				}
				$laReturn = [
					'sql'=>trim($lcSql),
					'tipo'=>trim($laDatos[0]['TIPO']),
				];
			}
		} else {
			$this->cErr='No se pudo recuperar el texto de la sentencia SQL para la consulta';
		}

		return $laReturn;
	}

	/*
	 *	Obtiene sentencia SQL y retorna los datos de la consulta
	 *	@param $tcCodigo: string, código de la consulta
	 *	@param $taVariables: array, variables a reemplazar en la consulta
	 *	@param $tnOffSet: número de registros a ingnorar
	 *	@param $tnLimit: número de regsitros a retornar
	 */
	public function consultaDatos($tcCodigo, $taVariables, $tnOffSet=0, $tnLimit=0)
	{
		$laReturn=['total'=>0,'rows'=>[]];
		$laConsulta=$this->obtenerTipoSQL($tcCodigo);

		if(!empty($laConsulta['sql'])){
			// cambia variables por los datos recibidos
			foreach($taVariables as $lcVar=>$lcDato){
				$laConsulta['sql']=str_replace('{{'.strtoupper($lcVar).'}}', $lcDato, $laConsulta['sql']);
			}

			if ($laConsulta['tipo']=='FUN') {
				$laFuncion = explode('¤', $laConsulta['sql']);
				if (method_exists($this, $laFuncion[0])) {
					$laParams = [];
					if (count($laFuncion)>1) {
						foreach($laFuncion as $lnKey=>$lcParam) {
							if ($lnKey>0) {
								$laParams[] = $lcParam;
							}
						}
					}
					$laFilas = $this->{$laFuncion[0]}($laParams);
					$laReturn=['total'=>count($laFilas),'rows'=>$laFilas];
				} else {
					$this->cErr='Método no existe, no se puede realizar la consulta.';
				}

			} else { // $laConsulta['tipo']=='SQL'

				// llevar todos los datos
				if($tnOffSet==0 && $tnLimit==0){
					ini_set('max_execution_time', 0);
					ini_set('memory_limit', '-1');

					// Consulta de datos
					$laDatos=$this->oDb->query($laConsulta['sql'],true,true);

					if(is_array($laDatos)){
						if(count($laDatos)>0){
							$laReturn=['total'=>count($laDatos), 'rows'=>$laDatos];
							unset($laDatos);
						}
					} else {
						$this->cErr='La consulta no se pudo realizar';
					}

				// llevar solo un rango de los datos
				} else {

					//Obtiene el total de registros
					//$laSql=preg_split('//u', $laConsulta['sql'], null, PREG_SPLIT_NO_EMPTY);
					$laSql=preg_split('/(?<!^)(?!$)/u', $laConsulta['sql']);
					$lnParentesis=0;
					$lbComilla=false;
					$lnFrom=$lnPosFrom=$lnOrder=$lnPosOrder=0;
					foreach($laSql as $lnClave=>$lcChar){
						if($lcChar=='('){ if(!$lbComilla) $lnParentesis++; } else {
							if($lcChar==')'){ if(!$lbComilla) $lnParentesis--; } else {
								if($lcChar=="'"){
									$lbComilla=!$lbComilla;
								} else {
									if($lnParentesis==0 && !$lbComilla){
										if($lnPosFrom==0){
											if($lnFrom==0){ if(strtoupper($lcChar)===' ') $lnFrom++; else $lnFrom=0; } else {
												if($lnFrom==1){ if(strtoupper($lcChar)==='F') $lnFrom++; else $lnFrom=0; } else {
													if($lnFrom==2){ if(strtoupper($lcChar)==='R') $lnFrom++; else $lnFrom=0; } else {
														if($lnFrom==3){ if(strtoupper($lcChar)==='O') $lnFrom++; else $lnFrom=0; } else {
															if($lnFrom==4){ if(strtoupper($lcChar)==='M') $lnFrom++; else $lnFrom=0; } else {
																if($lnFrom==5){ if(strtoupper($lcChar)===' '){ $lnPosFrom=$lnClave-4; }}
															}
														}
													}
												}
											}
										/*
										} elseif($lnPosOrder==0) {
											if($lnOrder==0){ if(strtoupper($lcChar)===' ') $lnOrder++; else $lnOrder=0; } else {
												if($lnOrder==1){ if(strtoupper($lcChar)==='O') $lnOrder++; else $lnOrder=0; } else {
													if($lnOrder==2){ if(strtoupper($lcChar)==='R') $lnOrder++; else $lnOrder=0; } else {
														if($lnOrder==3){ if(strtoupper($lcChar)==='D') $lnOrder++; else $lnOrder=0; } else {
															if($lnOrder==4){ if(strtoupper($lcChar)==='E') $lnOrder++; else $lnOrder=0; } else {
																if($lnOrder==5){ if(strtoupper($lcChar)==='R') $lnOrder++; else $lnOrder=0; } else {
																	if($lnOrder==6){ if(strtoupper($lcChar)===' ') { $lnPosOrder=$lnClave-5; break; }}
																}
															}
														}
													}
												}
											}
										*/
										}
									}
								}
							}
						}
					}

					//if($lnPosOrder==0){
						$lnPosOrder=mb_strrpos($laConsulta['sql'],' ORDER BY',0,'UTF-8');
						if($lnPosOrder==0){
							$lcOrder='';
							$lcSqlCount='SELECT COUNT(*) AS CUENTA '.mb_substr($laConsulta['sql'],$lnPosFrom,null,'UTF-8');
						}
					//}
					if($lnPosOrder>0){
						$lcOrder=mb_substr($laConsulta['sql'],$lnPosOrder,null,'UTF-8');
						$lcSqlCount='SELECT COUNT(*) AS CUENTA '.mb_substr(mb_substr($laConsulta['sql'],0,$lnPosOrder,'UTF-8'),$lnPosFrom,null,'UTF-8');
					}
					$laDatos=$this->oDb->query($lcSqlCount,true,true);
					if(is_array($laDatos)){
						$lnCta=$laDatos[0]['CUENTA']??0;
					}

					// Consulta de datos
					$lnOffSet=is_numeric($tnOffSet)?$tnOffSet:0;
					$lnLimit=is_numeric($tnLimit)?$tnLimit:50;
					$lcQuery = 'SELECT * FROM ( '
							.'SELECT ROW_NUMBER() OVER ('.$lcOrder.') AS NUM_FILA_SQL, ' . substr($laConsulta['sql'], 6)
							.' FETCH FIRST ' . strval($lnOffSet + $lnLimit) . ' ROWS ONLY '
							.' ) AS BASEDATA '
							.' WHERE NUM_FILA_SQL BETWEEN ' . strval($lnOffSet + 1) . ' AND ' . strval($lnLimit);
					$laDatos=$this->oDb->query($lcQuery,true,true);
					if(is_array($laDatos)){
						if(count($laDatos)>0){
							$laReturn=['total'=>$lnCta,'rows'=>$laDatos];
						}
					} else {
						$this->cErr='La consulta no se pudo realizar';
					}
				}
			}
		}

		return $laReturn;
	}


/*
 ******************************************************************************************
 *** FUNCIONES PARA LLAMAR CONSULTAS PERSONALIZADAS
 ******************************************************************************************
 */

	/*
	 *	Consumos Tecnologías NO UPC por Ingreso
	 *
	 *	@param array $taDatos: [
	 *		0 => Número de ingreso,
	 *		1 => Incluir insumos (CIRUGIAS~DE TODAS LAS CIRUGÍAS|NOPOS~DE CIRUGIAS CON CUPS NOPBS|TODOS~TODOS LOS INSUMOS)
	 *	]
	 *	@return array asociativo con los datos de la consulta
	 */
	private function fnCumMedicamentoPorIngreso($taDatos)
	{
		$lnIngreso=$taDatos[0];
		$lcInsumos=$taDatos[1];
		require_once __DIR__ .'/class.FuncionesInv.php';
		return FuncionesInv::consumosNoPosPorIngreso($lnIngreso,$lcInsumos);
	}


	/*
	 *	Facturación con saldo
	 *
	 *	@param array $taDatos: [
	 *		0 => Fecha inicio factura,
	 *		1 => Fecha final factura
	 *	]
	 *	@return array asociativo con los datos de la consulta
	 */
	private function fnFacturasConSaldo($taDatos)
	{
		$lnFechaInicial=$taDatos[0];
		$lnFechaFinal=$taDatos[1];
		require_once __DIR__ .'/class.Facturacion.php';
		return (new Facturacion())->facturasConSaldo($lnFechaInicial,$lnFechaFinal);
	}

	/*
	 *	Consumos con dismución de valor a cobrar
	 *
	 *	@param array $taDatos: [
	 *		0 => Fecha inicio consumo,
	 *		1 => Fecha final consumo
	 *	]
	 *	@return array asociativo con los datos de la consulta
	 */
	private function fnProcedimientosDismuyeValor($taDatos)
	{
		$lnFechaInicial=$taDatos[0];
		$lnFechaFinal=$taDatos[1];
		require_once __DIR__ .'/class.Facturacion.php';
		return (new Facturacion())->procedimientosDisminuyeValor($lnFechaInicial,$lnFechaFinal);
	}
	
	/*
	 *	Medicamento/Elemento con dismución de valor a cobrar
	 *
	 *	@param array $taDatos: [
	 *		0 => Fecha inicio consumo,
	 *		1 => Fecha final consumo
	 *	]
	 *	@return array asociativo con los datos de la consulta
	 */
	private function fnProductosDismuyeValor($taDatos)
	{
		$lnFechaInicial=$taDatos[0];
		$lnFechaFinal=$taDatos[1];
		require_once __DIR__ .'/class.Facturacion.php';
		return (new Facturacion())->productoDisminuyeValor($lnFechaInicial,$lnFechaFinal);
	}
	
	/*
	 *	Duplicidad parametrización sistema facturación
	 *
	 *	@param array $taDatos: [
	 *		0 => Nit entidad,
	 *		1 => Fecha vigencia hasta mayor a 
	 *	]
	 *	@return array asociativo con los datos de la consulta
	 */
	private function fnParametrizacionDuplicada($taDatos)
	{
		$lnNit=$taDatos[0];
		$lnFechaVigencia=$taDatos[1];
		require_once __DIR__ .'/class.Facturacion.php';
		return (new Facturacion())->parametrizacionDuplicada($lnNit,$lnFechaVigencia);
	}
	
	/*
	 *	Consulta actividades físicas por periodo
	 *
	 *	@param array $taDatos: [
	 *		0 => Fecha inicio registro,
	 *		1 => Fecha final registro
	 *	]
	 *	@return array asociativo con los datos de la consulta
	 */
	private function fnActividadesFisicas($taDatos)
	{
		$lnFechaInicial=$taDatos[0];
		$lnFechaFinal=$taDatos[1];
		require_once __DIR__ .'/class.AntecedentesConsulta.php';
		return (new AntecedentesConsulta())->consultaActividadesFisicas($lnFechaInicial,$lnFechaFinal);
	}

	/*
	 *	Consulta Muerte encefálica
	 *
	 *	@param array $taDatos: [
	 *		0 => Fecha inicio registro,
	 *		1 => Fecha final registro
	 *	]
	 *	@return array asociativo con los datos de la consulta
	 */
	private function fnMuerteEncefalica($taDatos)
	{
		$lnFechaInicial=$taDatos[0];
		$lnFechaFinal=$taDatos[1];
		require_once __DIR__ .'/class.Epicrisis.php';
		return (new Epicrisis())->consultaMuerteEncefalica($lnFechaInicial,$lnFechaFinal);
	}

	/* Consulta de personas discapacitadas
	   Parametros {Fecha Inicial - Fecha Final }*/
	private function fnInformeDiscapacidad($taDatos)
	{
		$lnFechaInicial=$taDatos[0];
		$lnFechaFinal=$taDatos[1];
		require_once __DIR__ .'/class.AntecedentesConsulta.php';
		return (new AntecedentesConsulta())->consultaDiscapacidad($lnFechaInicial,$lnFechaFinal);
	}

	/* Consulta de Auditoria de sabanas de enfermeria
	Parametros {Ingreso - Fecha Inicial - Fecha Final }*/
	private function fnAuditoriaNotas($taDatos)
	{
		$lnIngreso=$taDatos[0];
		$lnFechaInicial=$taDatos[1];
		$lnFechaFinal=$taDatos[2];
		require_once __DIR__ .'/class.Doc_Enf_Notas.php';
		return (new Doc_Enf_Notas())->fnAuditoriaSabana($lnIngreso,$lnFechaInicial,$lnFechaFinal);
	}

}
