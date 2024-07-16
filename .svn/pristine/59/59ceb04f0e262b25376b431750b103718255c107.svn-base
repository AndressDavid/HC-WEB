<?php

namespace NUCLEO;

require_once ('class.AplicacionFunciones.php');
require_once ('class.Db.php');

use NUCLEO\AplicacionFunciones;
use NUCLEO\Db;

class UsuarioRecordatorio
{
	protected $aLista = array(array('',false),array('',false),array('',false));
	protected $aRecordatorios = array();
	protected $nRecordatorios = 0;
	protected $cEspecialidades = '';
	protected $cRecordatorios = '';
	protected $cRegistroMedico ='';
	protected $cUsuario = '';
	protected $dFecha = null;
	protected $nFecha = 0;
	protected $nHora = 0;

    public function __construct ($tcUsuario='')	
	{
		global $goDb;
		settype($tcUsuario,"string");
		$this->dFecha = date_create($goDb->fechaHoraSistema());
		$this->nFecha = intval($this->dFecha->format("Ymd"));
		$this->nHora  = intval($this->dFecha->format("His"));
		$this->usuario($tcUsuario); 
		
	}

	private function between($tnValor, $tnMenor, $tnMayor)
	{
		return ($tnValor-$tnMenor)*($tnValor-$tnMayor) <= 0;
	}
	
	private function usuario($tcUsuario='')
	{
		settype($tcUsuario,"string");
		$this->cUsuario = $tcUsuario;
		$this->cRecordatorios = '';
		$this->cEspecialidades = '';
		$this->cRegistroMedico = '';
		$loAplicacionFunciones = new AplicacionFunciones();
			
		for($lnLista=0;$lnLista<count($this->aLista);$lnLista++){
			$this->aLista[$lnLista][0]='';
			$this->aLista[$lnLista][1]=false;
		}
		
		if(!empty($this->cUsuario)){
			global $goDb;
			if(isset($goDb)){
				$lcTabla = 'RIARGMN';
				$laCampos = ['REGMED', 'TPMRGM', 'CODRGM'];
				$laUsuario = $goDb->select($laCampos)->tabla($lcTabla)
							 ->where('USUARI', '=', $this->cUsuario)
							 ->get('array');
						
				if(is_array($laUsuario)==true){
					if(count($laUsuario)>0){
						$lcEspecialidad = "[".trim($laUsuario['TPMRGM'])."|".trim($laUsuario['CODRGM'])."]";
						$llExiste = $loAplicacionFunciones->isSearchStrInStr($this->cEspecialidades, $lcEspecialidad);
						if($llExiste==false){
							$this->cEspecialidades .= (empty($this->cEspecialidades)?'':',').$lcEspecialidad;
						}
						$this->cRegistroMedico = (empty($this->cRegistroMedico)?trim($laUsuario['REGMED']):$this->cRegistroMedico);
					}
				}
		
				if(!empty($this->cRegistroMedico)){
					$lcTabla = 'MEDTUSL01';
					$laCampos = ['TUSTUS', 'ESPTUS'];
					$laEspecialidad = $goDb->select($laCampos)->tabla($lcTabla)
										->where('REGTUS', '=', $this->cRegistroMedico)
										->get('array');					
						
					if(is_array($laEspecialidad)==true){
						if(count($laEspecialidad)>0){
							$lcEspecialidad = "[".trim($laEspecialidad['TUSTUS'])."|".trim($laEspecialidad['ESPTUS'])."]";
							$llExiste = $loAplicacionFunciones->isSearchStrInStr($this->cEspecialidades, $lcEspecialidad);
							if($llExiste==false){
								$this->cEspecialidades .= (empty($this->cEspecialidades)?'':',').$lcEspecialidad;
							}
						}
					}
				}
				
				$this->cEspecialidades = trim(strtoupper(strtolower($this->cEspecialidades)));
			}
		}
	}

	private function marcar($tnRecordatorio=0, $tnVistoId=0, $tnVisto=0, $tnRepeticion=0)
	{
		global $goDb;
		$llResultado=false;
		$lcUsuario=$this->cUsuario;
		$lcPrograma = substr(pathinfo(__FILE__, PATHINFO_FILENAME),0,10);
		$lcTabla = 'SISMENREU';

		if($tnVistoId<=0){
			$tnVistoId = $this->nuevo();
			$laDatos = ['CODREU'=>$tnVistoId,
						'CODREC'=>$tnRecordatorio,
						'USUARI'=>$this->cUsuario,
						'FEPREU'=>$this->nFecha,
						'HOPREU'=>$this->nHora,
						'FECREU'=>$this->nFecha,
						'HORREU'=>$this->nHora,
						'VISREU'=>$tnVisto+1,
						'REPREU'=>$tnRepeticion+1,
						'UCRREC'=>$lcUsuario,
						'PCRREC'=>$lcPrograma,
						'FCRREC'=>$this->nFecha,
						'HCRREC'=>$this->nHora
						];
			$llResultado = $goDb->tabla($lcTabla)->insertar($laDatos);
		}else{
			$laDatos = ['FECREU'=>$this->nFecha,
						'HORREU'=>$this->nHora,
						'VISREU'=>$tnVisto+1,
						'REPREU'=>$tnRepeticion+1,
						'UMOREC'=>$lcUsuario,
						'PMOREC'=>$lcPrograma,
						'FMOREC'=>$this->nFecha,
						'HMOREC'=>$this->nHora
						];			
			$llResultado = $goDb->tabla($lcTabla)->where('CODREC', '=', $tnRecordatorio)->where('USUARI', '=', $this->cUsuario)->actualizar($laDatos);
		} 
		return $llResultado;		
	}	

	private function nuevo()
	{
		global $goDb;
		$lnConsecutivo = 0;
		$lcTabla = 'SISMENREU';
		$laRegistro = $goDb->max('CODREU', 'REGISTRO')->from($lcTabla)->get('array');	
					
		if(isset($goDb)){
			if(is_array($laRegistro)==true){
				if(count($laRegistro)>0){
					$lnConsecutivo=$laRegistro['REGISTRO'];
				}
			}
		}
		return $lnConsecutivo+1;		
	}	

	private function validarLapso($tnRecordatorio=0, $tnLapso=0, $tnRepeticiones=0, &$tnRepeticion=0, &$tnVistoId=0, &$tnVisto=0)
	{
		global $goDb;
		$llValido = false;
		$tnRepeticion=0;
		$tnVistoId=0;
		$tnVisto=0;
				
		if($tnRecordatorio>0){
			$lcTabla = 'SISMENREU';
			$laCampos = ['CODREU', 'FECREU', 'HORREU', 'REPREU', 'VISREU'];
			$laEspecialidades = $goDb->select($laCampos)->tabla($lcTabla)
								->where('USUARI', '=', $this->cUsuario)
								->getAll('array');					
				
			if(is_array($laEspecialidades)==true){
				if(count($laEspecialidades)==0){
					$llValido=true;
				}else{
					foreach($laEspecialidades as $laEspecialidad){
						if(empty($laEspecialidad['FECREU'])){
							$ldFecha=$this->dFecha;
						}else{
							$ldFecha = date_create($laEspecialidad['FECREU']);
						}
						
						$tnVistoId=$laEspecialidad['CODREU'];
						$tnRepeticion=(date_format($ldFecha,'Y-m-d')==date_format($this->dFecha,'Y-m-d')?$laEspecialidad['REPREU']:0);
						$toDiferencia= date_diff($ldFecha, $this->dFecha);
						if((($toDiferencia->days-$tnLapso)>=0 || date_format($ldFecha,'Y-m-d')==date_format($this->dFecha,'Y-m-d')) && $tnRepeticion<$tnRepeticiones){
							$tnVisto=$laEspecialidad['VISREU'];
							$llValido=true;
						}
					}
				}
			}
		} 
			
		return $llValido;		
	}	

	private function validarListas($tcTipoUsuarios='', $tcEspecialidades='', $tcUsuarios='')
	{
		$loAplicacionFunciones = new AplicacionFunciones();
		$laParametros = func_get_args();
		for($lnLista=0;$lnLista<count($this->aLista);$lnLista++){
			$this->aLista[$lnLista][0]=$laParametros[$lnLista];
			$this->aLista[$lnLista][1]=false;
		}
		
		for($lnLista=0;$lnLista<count($this->aLista);$lnLista++){
			if(trim($this->aLista[$lnLista][0])=="*" || empty($this->aLista[$lnLista][0])==true){
				$this->aLista[$lnLista][1]=true;
			}else{
				$laLista = explode(",", $this->aLista[$lnLista][0]);
				for($lnItem=0;$lnItem<count($laLista);$lnItem++){
					$lcItem = trim(strtoupper(strtolower($laLista[$lnItem])));
					$lcItem = ($lnLista==0?"[".trim($lcItem)."|":($lnLista==1?"|".trim($lcItem)."]":$lcItem));
					
					if($this->between($lnLista,0,1)){
						$llExiste = $loAplicacionFunciones->isSearchStrInStr($this->cEspecialidades, $lcItem);
						if($llExiste==true){
							$this->aLista[$lnLista][1]=true;
						}
					}else{
						if(trim(strtoupper(strtolower($this->cUsuario)))==$lcItem){
							$this->aLista[$lnLista][1]=true;
						}
					}
				}
			}
		}
		
		return ($this->aLista[0][1]==true && $this->aLista[1][1]==true && $this->aLista[2][1]==true);
	}

	public function cargar($tcTipo='P')
	{
		global $goDb;
		$lcTabla = 'SISMENREC';
		$laCampos = ['CODREC', 'TITREC', 'FECINI', 'FECFIN', 'RECORD', 'LINREC', 'LINTIT', 'TIPUSU', 'ESPUSU', 'LISUSU', 'LAPREC', 'REPETI'];
		$laRegistro = $goDb->select($laCampos)
					  ->tabla($lcTabla)
					  ->where('ESTREC', '=', 'A')
					  ->where('TIPREC', '=', $tcTipo)
					  ->get('array');
				
		if(is_array($laRegistro)==true){
			if(count($laRegistro)>0){
				if(($laRegistro['FECINI']==0 || $laRegistro['FECFIN']==0) || ($this->between($this->nFecha,$laRegistro['FECINI'],$laRegistro['FECFIN'])==true)){
					if($this->ValidarListas($laRegistro['TIPUSU'], $laRegistro['ESPUSU'], $laRegistro['LISUSU'])==true){
						$lnVisto=0;
						$lnVistoId=0;
						$lnRepeticion=$laRegistro['REPETI'];
						
						if($this->ValidarLapso($laRegistro['CODREC'], $laRegistro['LAPREC'], $laRegistro['REPETI'], $lnRepeticion, $lnVistoId, $lnVisto)==true){
							$this->aRecordatorios[] = array('TITULO'=>$laRegistro['TITREC'],
															 'RECORDATORIO'=>$laRegistro['RECORD'],
															 'LINK'=>$laRegistro['LINREC'],
															 'ALIAS'=>$laRegistro['LINTIT']);
							$this->nRecordatorios+=1;
							$this->marcar($laRegistro['CODREC'], $lnVistoId, $lnVisto, $lnRepeticion);
						}
					} 
				} 
			}
		}
	}
	
	public function getRecordatorios(){
		return $this->aRecordatorios;
	}
	
	public function getCuentaRecordatorios(){
		return $this->nRecordatorios;
	}
}

?>