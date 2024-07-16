<?php
namespace NUCLEO;
require_once __DIR__ . '/class.Db.php';


class frmInput
{

	protected $oDb;
	protected $aTiposPermitidos=['N','C','M','R','D','H','T','L','S','Z',];
	public $cErrVal='';

	function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}

	/*
	 *	Tipos de datos permitidos
	 */
	public function tiposDatos()
	{
		$this->aTipos = [];

		$laTipos = $this->oDb
			->select('TRIM(OP1TMA) AS TIPO, TRIM(SUBSTR(DE1TMA,1,30)) AS DESCRIP')
			->from('TABMAE')
			->where("TIPTMA='FRMINPUT' AND CL1TMA='TIPODATO' AND CL2TMA<>''")
			->orderBy('INT(CL2TMA)')
			->getAll('array');

		if(is_array($laTipos)){
			foreach($laTipos as $laTipo){
				$this->aTipos[$laTipo['TIPO']] = $laTipo['DESCRIP'];
			}
		}

		return $this->aTipos;
	}

	/*
	 *	Valida el Json y hace consulta de sentencias SQL
	 */
	public function validarJson($tcJson)
	{
		$loJson=json_decode($tcJson, true);

		// nodos obligatorios
		$laKeys=['Opciones','Controles',];
		foreach($laKeys as $lcKey){
			if(!isset($loJson[$lcKey])){
				$this->cErrVal="Falta nodo '$lcKey'";
				return false;
			}
		}

		// debe haber al menos un control
		if(count($loJson['Controles'])<1){
			$this->cErrVal="Nodo 'Controles' no contiene elementos";
			return false;
		}

		$laKeys=['tipo','variable','Texto','Obligar','ValorxDefecto','OpcionesControl',];
		foreach($loJson['Controles'] as $lcCodigo=>$laControl){
			// nodos obligatorios en controles
			foreach($laKeys as $lcKey){
				if(!isset($laControl[$lcKey])){
					$this->cErrVal="Falta nodo '$lcKey' en 'Controles' nodo '$lcCodigo'";
					return false;
				}
			}
			// tipos permitidos
			if(!in_array($laControl['tipo'],$this->aTiposPermitidos)){
				$this->cErrVal="Tipo de dato no permitido en 'Controles' nodo '$lcCodigo'";
				return false;
			}
			// adiciona datos si es tipo S = sentencia SQL
			if($laControl['tipo']=='S'){
				$laLista=$lcSep='';
				$laOpciones=$this->oDb->query($laControl['SentenciaSelect'],true,true);
				if(is_array($laOpciones)){
					foreach($laOpciones as $laOpcion){
						$laLista.=$lcSep.trim($laOpcion['CODIGO']).'~'.trim($laOpcion['DESCRIPCION']);
						$lcSep='|';
					}
					$loJson['Controles'][$lcCodigo]['Lista']=$laLista;
				} else {
					$this->cErrVal="Sentencia SQL no válida en 'Controles' nodo '$lcCodigo'";
					return false;
				}
			}
		}

		return json_encode($loJson);
	}

}
