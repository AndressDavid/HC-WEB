<?php
namespace NUCLEO;

require_once ('class.Db.php') ;
use NUCLEO\Db;

class DatosPlanManejo
{
	public $aConsultaPlanManejo = [];

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}

	public function ConsultaPlanManejo($tnIngreso=0){
		if (!empty($tnIngreso)){
			$lnReg=0 ;

			if(isset($this->oDb)){
				$laDatosPlan = $this->oDb
					->select('A.INDICE INDICE, A.SUBIND SUBINDICE, A.CODIGO CODIGO, A.CONSEC LINEA, trim(A.SUBORG) SUBCODIGO, trim(A.DESCRI) DESCRIPCION')
					->tabla('RIAHIS AS A')
					->where('NROING', '=', $tnIngreso)
					->where('CONCON', '=', 1)
					->in('INDICE', ['30','40','50','54','55','85','88'])	
					->orderBy('INDICE, CONSEC')
					->getAll('array');
				$this->aConsultaPlanManejo = $laDatosPlan;
			}
		}
		return $this->aConsultaPlanManejo;
	}
	
	public function validacion($detallePlanManejo){
		if(isset($this->oDb)){
			$laRetornar = [
			'valido'=>true,
			'mensaje'=>'',
			];
			
			foreach ($detallePlanManejo as $validarPlanManejo){
				$lcIndice = trim($validarPlanManejo['indice']);
				$lcCodigo = trim($validarPlanManejo['codigo']);
				$lcDescripcion = trim($validarPlanManejo['descripcion']);
				
				if ($lcIndice==40){
					if ($lcDescripcion!='N' && $lcDescripcion!='S'){
						$laRetornar['valido'] = false;
						$laRetornar['mensaje'] = "NO es válido el dato de Doctor informa al paciente";
						break;
					}		
				}
				
				if ($lcIndice==88){
					if ($lcDescripcion!='N' && $lcDescripcion!='S'){
						$laRetornar['valido'] = false;
						$laRetornar['mensaje'] = "NO es válido el dato Reingreso misma causa";
						break;
					}		
				}
				
				if ($lcIndice==50){
					if ($lcDescripcion=='' && $lcCodigo=='Si'){
						$laRetornar['valido'] = false;
						$laRetornar['mensaje'] = "NO es válido el dato descripción Tuvo electrocardiograma";
						break;
					}
				}
				
				if ($lcIndice==30){
					if ($lcDescripcion==''){
						$laRetornar['valido'] = false;
						$laRetornar['mensaje'] = "NO es válido el dato descripción Análisis y plan de manejo";
						break;
					}
				}
				
				//	54 CONDUCTA SEGUIR, BUSCAR CÓDIGO
				if ($lcIndice==54){
					if (!empty($lcCodigo)){
						$laErrores = [];
						$lcTablaValida = 'TABMAE';
						$lcTipo = 'CNDSGR';
						
						
						$laWhere=['TIPTMA'=>$lcTipo,'CL1TMA'=>$lcCodigo,];
						try {
							$lbValidar = false;
							$laReg = $this->oDb->tabla($lcTablaValida)->where($laWhere)->getAll('array');
							if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;

							if (!$lbValidar) {
								$laRetornar['valido'] = false;
								$laRetornar['mensaje'] = "NO se encontro el código de Conducta a seguir";
								break;
							}
						} catch(Exception $loError){
							$laErrores[] = $loError->getMessage();
						} catch(PDOException $loError){
							$laErrores[] = $loError->getMessage();
						}
					}	
				}
					
				//	55 ESTADO SALIDA, BUSCAR CÓDIGO
				if ($lcIndice==55){
					if (!empty($lcCodigo)){
						$laErrores = [];
						$lcTablaValida = 'PRMTAB';
						$lcTipo = 'ESL';
						$laWhere=['TABTIP'=>$lcTipo,'TABCOD'=>$lcCodigo,];
						try {
							$lbValidar = false;
							$laReg = $this->oDb->tabla($lcTablaValida)->where($laWhere)->getAll('array');
							if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;

							if (!$lbValidar) {
								$laRetornar['valido'] = false;
								$laRetornar['mensaje'] = "NO se encontro el código de Estado Salida";
								break;
							}
						} catch(Exception $loError){
							$laErrores[] = $loError->getMessage();
						} catch(PDOException $loError){
							$laErrores[] = $loError->getMessage();
						}
					}	
				}
					
			}
		}	
		return $laRetornar;	
	}
		
	public function validarReingreso(){
		if(isset($this->oDb)){
			$laRetornar = [
			'validar'=>false,
			];
			
			$loTabmae = $this->oDb->ObtenerTabMae('trim(DE2TMA) || \'~\' || trim(OP2TMA) AS FILTRO', 'DATING', ['CL1TMA'=>'REINGRES', 'CL2TMA'=>'VIAMIN', 'ESTTMA'=>'']);
			$lcFiltro = explode('~',trim(AplicacionFunciones::getValue($loTabmae, 'FILTRO', '')));
			$lcFiltroViaIngreso = $lcFiltro[0];
			$lnMaximoReingreso = intval($lcFiltro[1]);
			$fechaActual = new \DateTime( $this->oDb->fechaHoraSistema());
						
			$lcTipoIden = 'C';
			$lnNroiden = 0;
			 
			if (!empty(trim($lcTipoIden)) && $lnNroiden>0){
				$laIngresoPaciente = $this->oDb
				->select('FEEING AS FECHA_EGRESO, HREING AS HORA_EGRESO')
				->tabla('RIAING AS A')
				->where('TIDING', '=', $lcTipoIden)
				->where('NIDING', '=', $lnNroiden)
				->where('FEEING', '>', 0)
				->where('HREING', '>', 0)
				->in('VIAING', ['01','05','06'])
				->orderBy('FEEING DESC')
				->getAll('array');
				
				if (is_array($laIngresoPaciente)) {
					if(count($laIngresoPaciente)>0){
						if (!empty(trim($laIngresoPaciente[0]['FECHA_EGRESO'])) && !empty(trim($laIngresoPaciente[0]['HORA_EGRESO']))
							){
							$fechaUltima = $laIngresoPaciente[0]['FECHA_EGRESO'] .str_pad($laIngresoPaciente[0]['HORA_EGRESO'], 6, '0', STR_PAD_LEFT);
							$fechaTrae = date_create_from_format('YmdHis', $fechaUltima);
							$fechaEgreso = new \DateTime(date_format($fechaTrae, 'Y-m-d H:i:s'));//fecha de cierre
							$intervalo = $fechaActual->diff($fechaEgreso);
							$lnDiferenciaHoras = intval(($intervalo->y * 365.25 + $intervalo->m * 30 + $intervalo->d) * 24 + $intervalo->h + $intervalo->i/60);
							
							if ($lnDiferenciaHoras<=$lnMaximoReingreso){
								$laRetornar['validar'] = true;
							}
						}
					}	
				}	
			}
		}
		return $laRetornar;	
	}

}
