<?php

namespace NUCLEO;

require_once ('class.Db.php') ;
require_once ('class.UsuarioDepartamentos.php');
require_once ('class.UsuarioAreas.php');
require_once ('class.UsuarioCargos.php');
require_once ('class.UsuarioEstados.php');
require_once ('class.UsuarioPerfiles.php');
require_once ('class.UsuarioTipos.php');
require_once ('class.Especialidades.php');

use NUCLEO\Db;
use NUCLEO\UsuarioDepartamentos;
use NUCLEO\UsuarioAreas;
use NUCLEO\UsuarioEstados;
use NUCLEO\UsuarioPerfiles;
use NUCLEO\UsuarioTipos;
use NUCLEO\Especialidades;

class Usuarios
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
	protected $cEncriptadoMetodo = '';
	protected $cEncriptadoClave = '';
	protected $cEncriptadoIV = '';

    public function __construct($tcWhere='', $tlActivo=true, $tcOdenar='TRIM(NOMMED)', $tnLimite=0, $tbOtrasConsultas=true) {
		$this->cEncriptadoMetodo = 'aes-256-cbc';
		$this->cEncriptadoClave = 'b43f06ae4a409702102d01b0a39d2c06';
		$this->cEncriptadoIV = '6451117dcff3fe2b';

		global $goDb;
		if(isset($goDb)){
			$laCampos = ['REGMED','USUARI',"TRIM(NNOMED)||' '||TRIM(NOMMED) NOMBRE",'TIDRGM','NIDRGM'];
			if(!empty($tcWhere)){
				if($tnLimite==0){
					$laUsuarios = $goDb
						->select($laCampos)
						->tabla('RIARGMN')
						->where($tcWhere)
						->orderBy($tcOdenar)->getAll('array');
				}else{
					$laUsuarios = $goDb
						->select($laCampos)
						->tabla('RIARGMN')
						->where($tcWhere)
						->limit($tnLimite)
						->orderBy($tcOdenar)->getAll('array');
				}
			}else{
				if($tnLimite==0){
					$laUsuarios = $goDb
						->select($laCampos)
						->tabla('RIARGMN')
						->where('ESTRGM', '=', ($tlActivo?'2':'1'))
						->orderBy($tcOdenar)->getAll('array');
				}else{
					$laUsuarios = $goDb
						->select($laCampos)
						->tabla('RIARGMN')
						->where('ESTRGM', '=', ($tlActivo?'2':'1'))
						->limit($tnLimite)
						->orderBy($tcOdenar)->getAll('array');
				}
			}
			if(is_array($laUsuarios)==true){
				foreach($laUsuarios as $laUsuario){
					$laUsuario = array_map('trim',$laUsuario);
					$this->aUsuarios[$laUsuario['USUARI']] = [
						'TIPOID'=>$laUsuario['TIDRGM'],
						'ID'=>$laUsuario['NIDRGM'],
						'REGISTRO'=>$laUsuario['REGMED'],
						'NOMBRE'=>$laUsuario['NOMBRE']
					];
				}
			}
		}

		if($tbOtrasConsultas){
			$this->oDepartamentos = new UsuarioDepartamentos();
			$this->oAreas = new UsuarioAreas();
			$this->oCargos = new UsuarioCargos();
			$this->oEstados = new UsuarioEstados();
			$this->oPerfiles = new UsuarioPerfiles();
			$this->oEspecialidades = new Especialidades();
			$this->oUsuaioTipos = new UsuarioTipos();
		}
	}



	public function FirmaEstado($tcFirmaRuta='', $tnTipo=0){
		$lcFirmaEstado='';
		$tcFirmaRuta=trim(sprintf('%s',$tcFirmaRuta));
		$tnTipo = intval($tnTipo);

		switch ($tnTipo) {
			case 1:
				try {
					if (empty($tcFirmaRuta)==true){
						$lcFirmaEstado='NO-ASIGNADO';
					} else {
						if (file_exists($tcFirmaRuta)=== true){
							$lcFirmaEstado='EXISTE';
						} else {
							$lcFirmaEstado='NO-EXISTE';
						}
					}
				} catch (Exception $loError) {
					$lcFirmaEstado='* Error';
				}
				break;

			default:
				$lcFirmaEstado=(!empty($tcFirmaRuta)?'ASIGNADO-SIN-COMPROBAR':'NO-ASIGNADO');
		}

		return $lcFirmaEstado;
	}

	public function Perfiles($tcUsuario=''){
		$lcPerfiles='';
		$tcUsuario=trim(sprintf('%s',$tcUsuario));
		global $goDb;
		if(isset($goDb)){
			if(empty($tcUsuario)==false){
				$lcSql="SELECT PERFIL FROM SISMENPER WHERE APPID='*' AND MENU='*' AND USUARI='".$tcUsuario."' AND PERTYPE='UPROFILE' AND STATE='A'";
				$loPerfiles = $goDb->query($lcSql); // Retorna un array

				if(is_array($loPerfiles)==true){
					if(count($loPerfiles)>0){
						foreach($loPerfiles as $loPerfil){
							$lcPerfiles = $lcPerfiles . (empty($lcPerfiles)?"":", ") . $this->NombrePerfil($loPerfil->PERFIL);
						}
					}
				}
			}
		}
		return $lcPerfiles;
	}

	public function NombreUsuarioTipo($tnId=0){
		$lcUsuarioTipo='';
		$tnId=intval($tnId);
		if(empty($tnId)==false){
			$lcUsuarioTipo = $this->oUsuaioTipos->Nombre($tnId);
		}
		return $lcUsuarioTipo;
	}

	public function NombreEspecialidad($tnId=0){
		$lcEspecialidad='';
		$tnId=intval($tnId);
		if(empty($tnId)==false){
			$lcEspecialidad = $this->oEspecialidades->Nombre($tnId);
		}
		return $lcEspecialidad;
	}

	public function NombrePerfil($tcPerfil=''){
		$lcPerfiles='';
		$tcPerfil=trim(sprintf('%s',$tcPerfil));
		if(empty($tcPerfil)==false){
			$lcPerfiles = $this->oPerfiles->Nombre($tcPerfil);
		}
		return $lcPerfiles;
	}



	public function buscar($tcRegMed='', $tcUsuari='', $tcNomMed='', $tcNnoMed='', $tcTidRgm='', $tnNidRgm=0, $tcCodRgm='', $tcTpmRgm='', $tnEstRgm=0, $tnAccIni=0, $tnAccFin=0, $tnVigIni=0, $tnVigFin=0){

		// Cargando variables locales
		$tcCodRgm=strval($tcCodRgm);
		$tcNnoMed=strtoupper(trim(strval($tcNnoMed)));
		$tcNomMed=strtoupper(trim(strval($tcNomMed)));
		$tcRegMed=strval($tcRegMed); $tcRegMed = (intval($tcRegMed)>0?str_pad(trim(strval(intval($tcRegMed))),13,'0',STR_PAD_LEFT):'');
		$tcTidRgm=strval($tcTidRgm);
		$tcTpmRgm=strval($tcTpmRgm);
		$tcUsuari=strval($tcUsuari);
		$tnAccFin=intval($tnAccFin);
		$tnAccIni=intval($tnAccIni);
		$tnEstRgm=intval($tnEstRgm);
		$tnNidRgm=intval($tnNidRgm);
		$tnVigFin=intval($tnVigFin);
		$tnVigIni=intval($tnVigIni);

		// CREANDO CONSULTA
		//Filtros estandar
		$lcWhere=(!empty($tcRegMed)?"A.REGMED='".$tcRegMed."'":"");
		$lcWhere=$lcWhere.(!empty($lcWhere) && !empty($tcUsuari)                     ?" AND ":"").(!empty($tcUsuari)                     ?(substr_count($tcUsuari,chr(37))>0?"UPPER(TRIM(A.USUARI)) LIKE '".$tcUsuari."'":"A.USUARI='".$tcUsuari."'"):"");
		$lcWhere=$lcWhere.(!empty($lcWhere) && !empty($tcNomMed)                     ?" AND ":"").(!empty($tcNomMed)                     ?(substr_count($tcNomMed,chr(37))>0?"UPPER(TRIM(A.NOMMED)) LIKE '".$tcNomMed."'":"A.NOMMED='".$tcNomMed."'"):"");
		$lcWhere=$lcWhere.(!empty($lcWhere) && !empty($tcNnoMed)                     ?" AND ":"").(!empty($tcNnoMed)                     ?(substr_count($tcNnoMed,chr(37))>0?"UPPER(TRIM(A.NNOMED)) LIKE '".$tcNnoMed."'":"A.NNOMED='".$tcNnoMed."'"):"");
		$lcWhere=$lcWhere.(!empty($lcWhere) && !empty($tcTidRgm)                     ?" AND ":"").(!empty($tcTidRgm)                     ?(substr_count($tcTidRgm,chr(37))>0?"TRIM(A.TIDRGM) LIKE '".$tcTidRgm."'":"A.TIDRGM='".$tcTidRgm."'"):"");
		$lcWhere=$lcWhere.(!empty($lcWhere) && !empty($tnAccIni) && !empty($tnAccFin)?" AND ":"").(!empty($tnAccIni) && !empty($tnAccFin)?"B.ACCFEC BETWEEN ".strval($tnAccIni)." AND ".strval($tnAccFin):"");
		$lcWhere=$lcWhere.(!empty($lcWhere) && !empty($tnVigIni) && !empty($tnVigFin)?" AND ":"").(!empty($tnVigIni) && !empty($tnVigFin)?"A.FVDRGM BETWEEN ".strval($tnVigIni)." AND ".strval($tnVigFin):"");
		$lcWhere=$lcWhere.(!empty($lcWhere) && !empty($tnVigIni) && !empty($tnVigFin)?" AND ":"").(!empty($tnVigIni) && !empty($tnVigFin)?"A.FVHRGM BETWEEN ".strval($tnVigIni)." AND ".strval($tnVigFin):"");
		$lcWhere=$lcWhere.(!empty($lcWhere) && !empty($tnNidRgm)                     ?" AND ":"").(!empty($tnNidRgm)                     ?"A.NIDRGM=".strval($tnNidRgm):"");


		//Tipo de usuario y especialidad
		if(empty($tcTpmRgm) && !empty($tcCodRgm)){
			$lcWhere=$lcWhere.(!empty($lcWhere)?" AND ":"")."((A.REGMED IN (SELECT B.REGTUS FROM MEDTUS AS B WHERE B.ESPTUS IN (".$tcCodRgm."))) "."OR (A.CODRGM IN ("+$tcCodRgm+")))";
		}else if(!empty($tcTpmRgm) && !empty($tcCodRgm)){
			$lcWhere=$lcWhere.(!empty($lcWhere)?" AND ":"")."((A.REGMED IN (SELECT B.REGTUS FROM MEDTUS AS B WHERE B.TUSTUS IN (".$tcTpmRgm.") AND B.ESPTUS IN (".$tcCodRgm."))) "."OR (A.TPMRGM IN (".$tcTpmRgm.") AND A.CODRGM IN (".$tcCodRgm.")))";
		}else if(!empty($tcTpmRgm) && empty($tcCodRgm)){
			$lcWhere=$lcWhere.(!empty($lcWhere)?" AND ":"")."((A.REGMED IN (SELECT B.REGTUS FROM MEDTUS AS B WHERE B.TUSTUS IN (".$tcTpmRgm."))) "."OR (A.TPMRGM IN (".$tcTpmRgm.")))";
		}

		// Estado
		$lcWhere=$lcWhere.(!empty($lcWhere)?" AND ":"").(!empty($tnEstRgm)?"A.ESTRGM=".strval($tnEstRgm):'A.ESTRGM>=0');

		// Iniciando consulta
		return $lcWhere;
	}


	public function contar($tcRegMed='', $tcUsuari='', $tcNomMed='', $tcNnoMed='', $tcTidRgm='', $tnNidRgm=0, $tcCodRgm='', $tcTpmRgm='', $tnEstRgm=0, $tnAccIni=0, $tnAccFin=0, $tnVigIni=0, $tnVigFin=0){
		$lnUsuarios = 0;
		$lcWhere = $this->buscar($tcRegMed, $tcUsuari, $tcNomMed, $tcNnoMed, $tcTidRgm, $tnNidRgm, $tcCodRgm, $tcTpmRgm, $tnEstRgm, $tnAccIni, $tnAccFin, $tnVigIni, $tnVigFin);
		$lcWhere=(!empty($lcWhere)?" AND ":"").$lcWhere;

		global $goDb;
		if(isset($goDb)){
			$lcSql ="SELECT COUNT(A.USUARI) AS USUARIOS FROM RIARGMN AS A LEFT JOIN SISMENSEG AS B ON A.USUARI=B.USUARI  WHERE A.USUARI<>'' ".$lcWhere." FETCH FIRST 1 ROWS ONLY";
			$laUsuarios = $goDb->query($lcSql); // Retorna un array

			if(is_array($laUsuarios)==true){
				if(count($laUsuarios)>0){
					$lnUsuarios = $laUsuarios[0]->USUARIOS;
				}
			}
		}
		return $lnUsuarios;
	}


	public function cargar($tcRegMed='', $tcUsuari='', $tcNomMed='', $tcNnoMed='', $tcTidRgm='', $tnNidRgm=0, $tcCodRgm='', $tcTpmRgm='', $tnEstRgm=0, $tnAccIni=0, $tnAccFin=0, $tnVigIni=0, $tnVigFin=0, $lnRegistroInicio=0, $lnRegistroSalto=0){
		$laUsuarios = array();
		$lcWhere=$this->buscar($tcRegMed, $tcUsuari, $tcNomMed, $tcNnoMed, $tcTidRgm, $tnNidRgm, $tcCodRgm, $tcTpmRgm, $tnEstRgm, $tnAccIni, $tnAccFin, $tnVigIni, $tnVigFin);
		$lcWhere=(!empty($lcWhere)?" AND ":"").$lcWhere;

		global $goDb;
		if(isset($goDb)){
			if ($lnRegistroInicio>0 && $lnRegistroSalto>0){
				$lcSql ="SELECT
						   ROW_NUMBER() OVER ( ORDER BY A.USUARI) AS RECNO,
						   A.USUARI,
						   (SELECT COUNT(C.USUARI) FROM RIARGMN AS C WHERE C.USUARI=A.USUARI AND C.ESTRGM<>1) AS SEMEJANTES,
						   A.TIDRGM,
						   A.NIDRGM,
						   A.REGMED,
						   A.NOMMED,
						   A.NNOMED,
						   A.TPMRGM,
						   '' AS CTPMRGM,
						   A.ESTRGM,
						   '' AS CESTRGM,
						   A.CODRGM,
						   '' AS CCODRGM,
						   IFNULL(B.TAGCH1,'') AS TAGCH1,
						   IFNULL(B.TAGCH2,'') AS TAGCH2,
						   A.FVDRGM AS FVDRGM,
						   A.FVHRGM AS FVHRGM,
						   IFNULL(B.PSSNEX,0) AS PSSNEX,
						   (SELECT COUNT(DE1TMA) FROM TABMAEL01 WHERE TIPTMA='MAIMED' AND CL2TMA=A.USUARI AND ESTTMA<>'I') AS EMAILS,
						   IFNULL((SELECT DE1TMA FROM TABMAEL01 WHERE TIPTMA='MAIMED' AND CL1TMA=A.CODRGM AND CL2TMA=A.USUARI),'') AS EMAIL,
						   IFNULL((SELECT ESTTMA FROM TABMAEL01 WHERE TIPTMA='MAIMED' AND CL1TMA=A.CODRGM AND CL2TMA=A.USUARI),'') AS EMAILING,
						   IFNULL(B.ACCFEC,'') AS ACCFECHA,
						   IFNULL(B.ACCHOR,'') AS ACCHORA,
						   IFNULL(B.ACCPC,'') AS ACCPC,
						   IFNULL(B.ACCIP,'') AS ACCIP,
						   IFNULL(B.IMAGE2,'') AS FIRMARUTA,
						   IFNULL(B.IMAGE2,'') AS FIRMAARCHIVO,
						   '' AS FIRMAESTADO,
						   '' AS ESTADOTRAZA,
						   '' AS PERFILES,
						   '' AS LLAVE,
						   A.USRRGM AS CREO,
						   A.FECRGM AS CREADO,
						   A.HORRGM AS CREOH,
						   A.UMORGM AS MODIFICO,
						   A.FMORGM AS MODIFICADO,
						   A.HMORGM AS MODIFICOH
						 FROM RIARGMN AS A
							LEFT JOIN SISMENSEG AS B ON A.USUARI=B.USUARI
						 WHERE A.USUARI<>'' ".$lcWhere."
						 ORDER BY A.USUARI";

				$lcSql ="SELECT * FROM ( ".$lcSql." ) AS T";
				if($lnRegistroInicio>0 && $lnRegistroSalto>0){
					$lcSql .= " WHERE T.RECNO BETWEEN ".$lnRegistroInicio." AND ".$lnRegistroSalto;
				}

				$laUsuarios = $goDb->query($lcSql); // Retorna un array

				if(is_array($laUsuarios)==true){
					if(count($laUsuarios)>0){
						for($lnUsuario=0;$lnUsuario<count($laUsuarios);$lnUsuario++){
							$laUsuarios[$lnUsuario]->FIRMAESTADO=$this->FirmaEstado($laUsuarios[$lnUsuario]->FIRMARUTA,0);
							$laUsuarios[$lnUsuario]->PERFILES=($this->lCargarPerfiles==true?$this->Perfiles($laUsuarios[$lnUsuario]->USUARI):'SIN-CARGAR');
							$laUsuarios[$lnUsuario]->EMAILING = (!empty($laUsuarios[$lnUsuario]->EMAIL)?(!is_null($laUsuarios[$lnUsuario]->EMAILING)?(trim($laUsuarios[$lnUsuario]->EMAILING)=='I'?'NO':'SI'):'').($laUsuarios[$lnUsuario]->EMAILS>1?"(".intval($laUsuarios[$lnUsuario]->EMAILS).")":""):"");
							$laUsuarios[$lnUsuario]->EMAIL = (is_null($laUsuarios[$lnUsuario]->EMAIL)?'':trim(strtolower($laUsuarios[$lnUsuario]->EMAIL)));
							$laUsuarios[$lnUsuario]->CESTRGM = (intval($laUsuarios[$lnUsuario]->ESTRGM)==2?'INACTIVO':'ACTIVO');
							$laUsuarios[$lnUsuario]->CCODRGM = $this->NombreEspecialidad($laUsuarios[$lnUsuario]->ESTRGM);
							$laUsuarios[$lnUsuario]->CTPMRGM = $this->NombreUsuarioTipo($laUsuarios[$lnUsuario]->TPMRGM);
							$laUsuarios[$lnUsuario]->LLAVE = $this->encriptar($laUsuarios[$lnUsuario]->USUARI);
						}
					}
				}
			}
		}
		return $laUsuarios;
	}

	public function encriptar($tcValor) {
		return base64_encode(openssl_encrypt(strval($tcValor), $this->cEncriptadoMetodo, $this->cEncriptadoClave, false, $this->cEncriptadoIV));
	}

	public function desencriptar($tcValor){
		$tcValor = base64_decode(strval($tcValor));
		return openssl_decrypt($tcValor, $this->cEncriptadoMetodo, $this->cEncriptadoClave, false, $this->cEncriptadoIV);
	}
}
?>