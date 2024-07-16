<?php
namespace NUCLEO;
require_once ('class.Db.php') ;
require_once ('class.Diagnostico.php');
require_once __DIR__ .'/class.FormulacionParametros.php';

use NUCLEO\Db;
use NUCLEO\Diagnostico;

class Texto_Diagnostico
{
	private $cDescripcion = '';
	public $cTextoC = '';
	protected $aDiagnostico = [];
		
    public function __construct() {
		global $goDb;
		$this->oDb = $goDb;
    }
	
	public function retornarDocumento($taDiagnostico=[], $tnIngreso=0, $tnConsecEPI=0, $tnTipoDoc='')
	{
		$this->cargar($taDiagnostico, $tnIngreso, $tnConsecEPI,$tnTipoDoc);
		return $this->aDiagnostico ;
	}
	
	public function cargar($taDiagnostico=[], $tnIngreso, $tnConsecEPI,$tnTipoDoc){
		
		$lnReg = count($taDiagnostico);
		
		if (!empty($lnReg)){
			global $goDb;
			$lcSL = "\n";
			
			/* Tipo de Diagnostico */
			$laTipoDiag = $this->oDb
				->select('TABDSC DESTDX, SUBSTR(TABCOD,2,1) CODTDX')
				->from('PRMTAB04')
				->where(['TABTIP'=>'TDX',
						 'SUBSTR(TABCOD,1,1)'=>'A',
						])
				->getAll('array');

			/* Clase de Diagnostico */
			$laClaseDiag = $this->oDb
				->select('TABDSC DESTDX, SUBSTR(TABCOD,2,1) CODTDX')
				->from('PRMTAB04')
				->where(['TABTIP'=>'TDX',
						 'SUBSTR(TABCOD,1,1)'=>'B',
						])
				->getAll('array');

			/* Tipo de Tratamiento */
			$laTipoTrata = $this->oDb
				->select('trim(CL2TMA) CODIGO, SUBSTR(trim(DE2TMA),1,30) DESCRIPCION')
				->from('TABMAE')
				->where(['TIPTMA'=>'ESTDIAG',
						 'CL1TMA'=>'TIPOTRA',
						 'ESTTMA'=>'',
						])
				->getAll('array');
				
			$lcDiagnostico = '';
			$lnInd = 0;
			foreach($taDiagnostico as $lnkey=>$laData) {
				
				if ($lcDiagnostico!=$laData['DIAGNOS']){
					$lcDiagnostico=$laData['DIAGNOS'];
					$lnInd++;
					$this->aDiagnostico[$lnInd]['Observa'] = '';

					/* Tipo de Diagnostico */
					$lcTipo='';
					$lcTemp = (empty($laData['TRATAMIENTO'])  && trim($laData['INDICE'])==3)? 5 : trim($laData['INDICE']) ;
					$key = array_search($lcTemp, array_column($laTipoDiag, 'CODTDX'));
					if (is_numeric($key)){
						$lcTipo = trim($laTipoDiag[$key]['DESTDX']) ;
					}
					$this->aDiagnostico[$lnInd]['TipoDiag'] = $lcTipo;
					
					/* Clase de Diagnostico */
					$lcClase='';
					$lcTemp = trim($laData['CLIEDC']);
					$key = array_search($lcTemp, array_column($laClaseDiag, 'CODTDX')) ;
					if (is_numeric($key)){
						$lcClase = trim($laClaseDiag[$key]['DESTDX']) ;
					}
					$this->aDiagnostico[$lnInd]['ClaseDiag']=$lcClase;
				
					/* Tipo de Tratamiento */
					$lcTipo='';
					$lcTemp = trim($laData['TRATAMIENTO']??'') ;
					$key = array_search($lcTemp, array_column($laTipoTrata, 'CODIGO')) ;
					if (is_numeric($key)){
						$lcTipo = trim($laTipoTrata[$key]['DESCRIPCION']) ;
					}
					$this->aDiagnostico[$lnInd]['TipoTrata']=$lcTipo;
				
					/* Tipo de Diagnóstico */				
					switch ($laData['INDICE']){
						case '1' :
							$lcTipo = 'Diagnóstico Principal';
							break;
						case '2' :
							$lcTipo = 'Diagnósticos Relacionados';
							break;
						case '3' :
							$lcTipo = 'Diagnóstico de Complicación';
							break;
						case '4' :
							$lcTipo = 'Diagnóstico Fallece';
							break;
					
					}
				
					$this->aDiagnostico[$lnInd]['tipo_d']=$lcTipo;
				
					$lcCodigoDiag = trim($laData['DIAGNOS']);
					$loDiagnostico = new Diagnostico($laData['DIAGNOS'],$laData['FECHADIA']);
					$this->aDiagnostico[$lnInd]['desc_d'] = $loDiagnostico->getTexto();

					if ($laData['INDICE']<>'4'){
					
						/* Justificación Descarte */
						$lcObserva = '' ;
						$laDetalle = $this->oDb
							->select('DESJUD DETALLE')
							->from('JUSDESL01')
							->where(['INGJUD'=>$tnIngreso,
								 'TIPJUD'=>$tnTipoDoc,
								 'CEVJUD'=>$tnConsecEPI,
								 'CIEJUD'=>$lcCodigoDiag,
								])
							->getAll('array');
						
						if(is_array($laDetalle)){
						
							foreach($laDetalle as $laDesc) {
								$lcObserva .= $laDesc['DETALLE'];
							}
						
						}
					
						$this->aDiagnostico[$lnInd]['Descarte'] = trim($lcObserva);

						/* Análisis Conducta */
						$lcObserva = '' ;
						$laDetalle = $this->oDb
							->select('DESANA DETALLE')
							->from('CIEANAL01')
							->where(['INGANA'=>$tnIngreso,
								 'TIPANA'=>$tnTipoDoc,
								 'CEVANA'=>$tnConsecEPI,
								 'CIEANA'=>$lcCodigoDiag,
								])
							->getAll('array');
						
						if(is_array($laDetalle)){
						
							foreach($laDetalle as $laDesc) {
								$lcObserva .= $laDesc['DETALLE'];
							}
						
						}
					
						$this->aDiagnostico[$lnInd]['Analisis'] = trim($lcObserva);
						
					}

				}
				
				$this->aDiagnostico[$lnInd]['TRATAMIENTO'] = $laData['TRATAMIENTO'];
				$this->aDiagnostico[$lnInd]['FECHADIA'] = $laData['FECHADIA'];
				$this->aDiagnostico[$lnInd]['INDICE'] = $laData['INDICE'];
				$this->aDiagnostico[$lnInd]['DIAGNOS'] = $laData['DIAGNOS'];
				$this->aDiagnostico[$lnInd]['CLIEDC'] = $laData['CLIEDC'];
				$this->aDiagnostico[$lnInd]['Observa'] .= $laData['Observa']?? '';
					
			}	
			
		}
	}
	
}