<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class UbicacionesGeograficas {

    public function __construct() {
    }

	public function getPaises() {
		$laPaises = [];
		
		global $goDb;
		if (isset($goDb)) {
			$laCampos = ['TRIM(CHAR(A.CODPAI)) CODIGO', 'TRIM(A.DESPAI) NOMBRE'];
			$laPaises = $goDb
						->select($laCampos)
						->from('COMPAI A')
						->orderBy('A.DESPAI')
						->getAll('array');
		}
		
		return $laPaises;
	}
	
	public function getDepartamentos($tcPais="") {
		$laDepartamentos = [];
		$lnPais = intval($tcPais);
		$lcWhere = sprintf("A.PAIDEP=%s",$lnPais);
		
		global $goDb;
		if (isset($goDb)) {
			$laCampos = ['TRIM(CHAR(A.CODDEP)) CODIGO', 'TRIM(A.DESDEP) NOMBRE'];
			$laDepartamentos = $goDb
						->select($laCampos)
						->from('COMDEP A')
						->where($lcWhere)
						->orderBy('A.DESDEP')
						->getAll('array');
		}
		
		return $laDepartamentos;
	}	
	
	
	public function getCiudades($tcPais="", $tcDepartamento="") {
		$laCiudades = [];
		$lnPais = intval($tcPais);
		$lnDepartamento = intval($tcDepartamento);
		$lcWhere = sprintf("A.PAICIU=%s AND A.DEPCIU=%s", $lnPais, $lnDepartamento);
		
		// Por continuidad no se muestran las localidades
		if($lnPais==101 && $lnDepartamento==11){
			$lcWhere = $lcWhere.(empty($lcWhere)?'':' AND ').'A.CODCIU=1';			
		}
		
		global $goDb;
		if (isset($goDb)) {
			$laCampos = ['TRIM(CHAR(A.CODCIU)) CODIGO', 'TRIM(A.DESCIU) NOMBRE', 'CHAR(A.PAICIU) PAIS', 'CHAR(A.DEPCIU) DEPARTAMENTO'];
			$laCiudades = $goDb
						->select($laCampos)
						->from('COMCIU A')
						->where($lcWhere)
						->orderBy('A.DESCIU')
						->getAll('array');
		}
		
		return $laCiudades;
	}	
	

	public function getLocalidades() {
		$laLocalidades = [];
		
		global $goDb;
		if (isset($goDb)) {
			$laCampos = ['TRIM(CHAR(A.CODCIU)) CODIGO', 'TRIM(A.DESCIU) NOMBRE'];
			$laLocalidades = $goDb
						->select($laCampos)
						->from('COMCIU A')
						->where('A.PAICIU','=',101)
						->where('A.DEPCIU','=',11)
						->where('A.CODCIU','<>',1)
						->orderBy('A.DESCIU')
						->getAll('array');
		}
		
		return $laLocalidades;
	}		
		
}
