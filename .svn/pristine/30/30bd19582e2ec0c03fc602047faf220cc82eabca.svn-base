<?php

namespace NUCLEO;

require_once ('class.Db.php') ;
require_once ('class.Especialidad.php');

use NUCLEO\Db;
use NUCLEO\Especialidad;

class Medico {
	
	protected $aTipoId = ['TIPO' => '','ABRV' => '','NOMBRE' => '','NUMERO' => ''];
	protected $cUsuario = '';
	protected $nRegistroMedico = 0;
	protected $cRegistroMedico = '';
	protected $oEspecialidad = null;
	protected $nId = 0;
	protected $cNombres = '';
	protected $cApellidos = '';
	protected $cEmail = '';
	protected $nEstado = 0;
	protected $cEstado = '';
	protected $lCargado = false;
	
    function __construct($tcRegistroMedico=''){
		$this->oEspecialidad = new Especialidad();
		$this->cargarRegistroMedico($tcRegistroMedico);
    }

    public function cargarRegistroMedico($tcRegistroMedico=''){
		$lnRegistroMedico = intval($tcRegistroMedico);
		$tcRegistroMedico = str_pad(trim(strval($tcRegistroMedico)), 13, '0', STR_PAD_LEFT);

		global $goDb;
		if(isset($goDb)){
			if(empty($tcRegistroMedico)==false){

				// Información de la tabla paciente
				$laCampos = ['M.REGMED', 'M.USUARI', 'M.NOMMED', 'M.NNOMED', 'M.TIDRGM', 'M.NIDRGM', 'M.CODRGM', 'M.ESTRGM'];
				
				$laMedico = $goDb
					->select($laCampos)
					->from('RIARGMN M')
					->where('M.REGMED','=',$tcRegistroMedico)
					->get('array');
				
 				if(is_array($laMedico)==true){
					if(count($laMedico)>0){
						$this->cUsuario = trim($laMedico['USUARI']);
						$this->nRegistroMedico = intval($laMedico['REGMED']);
						$this->cRegistroMedico = trim($laMedico['REGMED']);
						$this->setTipoId(trim($laMedico['TIDRGM']));
						$this->nId = intval($laMedico['NIDRGM']);
						$this->cNombres = trim($laMedico['NNOMED']);
						$this->cApellidos = trim($laMedico['NOMMED']);
						$this->nEstado = intval($laMedico['ESTRGM']);
						$this->cEstado = ($this->nEstado==1?'ACTIVO':'INACTIVO');
						
						$this->cEmail = $this->Notificaciones(trim($laMedico['USUARI']));
						$this->oEspecialidad->cargar($laMedico['CODRGM']);
					}
				}
			}
		}
		
		$this->lCargado = ($this->cRegistroMedico==$tcRegistroMedico);
		return $this->lCargado;
    }

	public function consultarEspecialidadesPorMedico($tcRegistroMedico=''){
		
		$laEspecialidadesMedico = array();
		global $goDb;
		if(isset($goDb)){
			$laEspecialidadesMedico = $goDb
				->select('TRIM(B.ESPTUS) CODIGO')
				->select('(SELECT TRIM(DESESP) FROM RIAESPE WHERE CODESP=B.ESPTUS) AS DESCRIPCION')
				->from('RIARGMN AS A')
				->leftJoin("MEDTUS B", "A.REGMED=B.REGTUS", null)
				->where('A.ESTRGM', '=', 1)
				->where('A.REGMED', '=', $tcRegistroMedico)
				->in('B.TUSTUS', ['1','11','3','4','6','16'])
				->orderBy('B.ESPTUS')
				->getAll('array');			
		}			
		return $laEspecialidadesMedico;
	}	
	 
	private function Notificaciones($tcUsuario=''){
		$lcEmail = '';
		$tcUsuario = trim(strval($tcUsuario));

		if(!empty($tcUsuario)){
			global $goDb;
			if(isset($goDb)){
				$laCampos = ['CL1TMA','DE1TMA','ESTTMA'];
				$laNotificaciones = $goDb->select($laCampos)->from('TABMAE')->where('TIPTMA', '=', 'MAIMED')->where('CL2TMA', '=', $tcUsuario)->getAll('array');

				if(is_array($laNotificaciones)==true){
					if(count($laNotificaciones)>0){
						foreach($laNotificaciones as $laNotificacion){
							$this->aNotificaciones[] = array('ESPECIALIDAD'=>trim($laNotificacion['CL1TMA']),
															 'EMAIL'=>trim($laNotificacion['DE1TMA']),
															 'ESTADO'=>trim($laNotificacion['ESTTMA']));
							if(trim(strtoupper($laNotificacion['ESTTMA']))<>'I'){
								$lcEmail = (empty($lcEmail)?trim($laNotificacion['DE1TMA']):$lcEmail);
							}
						}
					}
				}
			}
		}
		return $lcEmail;
	}

	public function setTipoId($tcIdTipo)
	{
		$this->aTipoId = (new TipoDocumento($tcIdTipo))->aTipo;
	}	

	public function getDocumentoTipo($llArray=false){
		if($llArray==true){
			return $this->aTipoId;
		}
		return $this->aTipoId['TIPO'];
	}
	
	public function getUsuario(){
		return $this->cUsuario;
	}
	
	public function getRegistroMedico($tlNumero=true){
		if($tlNumero==true){
			return $this->nRegistroMedico;
		}
		return $this->cRegistroMedico;
	}
	
	public function getEspecialidad() {
		return $this->oEspecialidad;
	}
	
	public function getDocumento(){
		return $this->nId;
	}
	
	public function getEmail(){
		return $this->cEmail;
	}
	
	public function getEstado($tlNumero=true){
		if($tlNumero==true){
			return $this->nEstado;
		}
		return $this->cEstado;
	}
	
	public function isCargado(){
		return $this->lCargado;
	}

	public function getNombres(){
		return $this->cNombres;
	}
	public function getApellidos(){
		return $this->cApellidos;
	}
	

	public function getNombreCompleto()
	{
		return $this->getNombresApellidos();
	}
	public function getNombresApellidos()
	{
		return $this->pregReplaceComun($this->getNombres() . ' ' . $this->getApellidos());
	}
	public function getApellidosNombres()
	{
		return $this->pregReplaceComun($this->getApellidos() . ' ' . $this->getNombres());
	}
	private function pregReplaceComun($tcTexto)
	{
		return preg_replace('/( ){2,}/u', ' ', $tcTexto);
	}	
}

?>