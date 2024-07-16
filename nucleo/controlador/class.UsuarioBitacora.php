<?php

namespace NUCLEO;

require_once ('class.Db.php') ;
require_once ('class.AplicacionFunciones.php');

use NUCLEO\Db;

class UsuarioBitacora
{
	public $oDepartamentos = null;
	public $oAreas = null;
	public $oCargos = null;
	public $oEstados = null;
	public $oPerfiles = null;
	public $oUsuaioTipos = null;
	public $oEspecialidades = null;
    public $aUsuarios = array();
	public $lCargarPerfiles = false;
	private $cPrograma = '';
	private $cKey = '';
	private $cServerName = '';
	private $cServerIp = '';
	private $cLocalIp = '';
	protected $cEncriptadoMetodo = '';
	protected $cEncriptadoClave = '';
	protected $cEncriptadoIV = '';

    public function __construct() {
		$this->cKey = uniqid("KEY");
		$this->cPrograma = substr(pathinfo(__FILE__, PATHINFO_FILENAME),0,10);
		$this->cServerName = trim(strtolower(strval(AplicacionFunciones::serverProperty('SERVER_NAME'))));
		$this->cServerIp = trim(strtolower(strval(AplicacionFunciones::serverIp())));
		$this->cLocalIp = trim(strtolower(strval(AplicacionFunciones::localIp())));
	}
	


	public function historico($tcUsuario=''){
		$lcHistorico = "";
		$lcFechaBitacora = "";
		$tcUsuario = (trim(strval($tcUsuario)));

		if (!empty($tcUsuario)){
					
			global $goDb;
			if(isset($goDb)){			
				$laCampos = ['A.RATING','A.FCRBIT','A.HCRBIT','A.BITKEY','A.UCRBIT','A.COMMEN'];			
				$laRegistros = $goDb->select($laCampos)
									->from('SISMENBIT A')
									->where('A.USUARI', '=', $tcUsuario)
									->orderBy('A.FCRBIT','DESC')
									->orderBy('A.HCRBIT','DESC')
									->getAll('array');

				if(is_array($laRegistros)==true){
					if(count($laRegistros)>0){
						foreach($laRegistros as $laRegistro){
							$lcRating = str_repeat('<i class="fas fa-star"></i>',$laRegistro['RATING']).str_repeat('<i class="far fa-star"></i>',5-$laRegistro['RATING']);
							$lcFechaBitacora = $laRegistro['FCRBIT']." ".$laRegistro['HCRBIT'];
						
							$lcHistorico = $lcHistorico.
										   (empty($lcHistorico)?"":"<hr/>").
										   sprintf('<blockquote class="blockquote"><p class="mb-0">%s %s %s</p><p>%s</p><footer class="blockquote-footer">%s</footer></blockquote>',$lcFechaBitacora,trim($laRegistro['BITKEY']),trim($laRegistro['UCRBIT']),$lcRating,trim($laRegistro['COMMEN']));							   
						}
					}
				}
			}
		}
		
		return $lcHistorico;
	}
	
	public function insertarEntrada($tcUsuario='', $tcTipo='', $tnRating=0, $tcId='', $tcBitacora='', $tcGestor=''){
		$tcUsuario = (trim(strval($tcUsuario)));
		$tcTipo = (trim(strval($tcTipo)));
		$tnRating = intval($tnRating);
		$tcId = (trim(strval($tcId)));
		$tcBitacora = str_replace(["'"],[""],trim(strval($tcBitacora)));
		
		// Datos de auditoria
		$lcPrograma = $this->cPrograma;
		$llResultado = false;		

		// Insertando la nueva lectura
		global $goDb;
		if(isset($goDb)){
			$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
			$lcFecha = $ltAhora->format("Ymd");
			$lcHora  = $ltAhora->format("His");
		
			$laDatos = ['USUARI'=>$tcUsuario,
						'BITKEY'=>$tcTipo,
						'RATING'=>$tnRating,
						'BINNAC'=>$tcId,
						'COMMEN'=>$tcBitacora,
						'UCRBIT'=>$tcGestor,
						'PCRBIT'=>$lcPrograma,
						'FCRBIT'=>$lcFecha,
						'HCRBIT'=>$lcHora,
						];

			$llResultado = $goDb->tabla('SISMENBIT')->insertar($laDatos);
		}
		
		return $llResultado;
	}


	public function existe($tcCursor='', $tcFieldKey='', $tcFieldValue='', $tcQuery=''){
		$tcCursor=trim(strval($tcCursor));
		$tcFieldKey=trim(strval($tcFieldKey));
		$tcFieldValue=trim(strval($tcFieldValue));
		$tcQuery=trim(strval($tcQuery));
		$llExiste=false;

		if(!empty($tcCursor) && !empty($tcFieldKey)){
					
			global $goDb;
			if(isset($goDb)){
				
				if(!empty($tcQuery)){
					$lcSql = "SELECT COUNT(".$tcFieldKey.") AS REGISTROS FROM ".$tcCursor." WHERE ".$tcQuery;
					$laRegistros = $goDb->query($lcSql); // Retorna un array
				}else{
					$laRegistros = $goDb->count($tcFieldKey, 'REGISTROS')
										->from($tcCursor)
										->where($tcFieldKey, '=', $tcFieldValue)
										->getAll('array');
				}
				
				if(is_array($laRegistros)==true){
					if(count($laRegistros)>0){
						foreach($laRegistros as $laRegistro){
							$llExiste = ($laRegistro['REGISTROS']>0);
						}
					}
				}

			}
		}
		return $llExiste;
	}

	public function getKey(){
		return $this->cKey;
	}
	public function getServerName(){
		return $this->cServerName;
	}
	public function getServerIp(){
		return $this->cServerIp;
	}
	public function getLocalIp(){
		return $this->cLocalIp;
	}
	public function getInstancia(){
		return "HOST: ".$this->cServerName.", Server IP:".$this->cServerIp.", Local IP:".$this->cLocalIp;
	}
}
?>