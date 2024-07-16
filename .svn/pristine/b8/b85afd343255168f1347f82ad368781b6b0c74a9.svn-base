<?php
namespace NUCLEO;

require_once ('class.Db.php') ;
require_once ('class.Persona.php');
require_once ('class.Especialidad.php');

use NUCLEO\Db;
use NUCLEO\Persona;
use NUCLEO\Especialidad;

class UsuarioRegMedico extends Persona
{

	protected $cUsuario = '';
	protected $cRegistro = '';
	protected $nTipoUsuario = 0;
	protected $cCodEspecialidad = [];
	protected $aEspecialidad = [];
	protected $cError = '';


    function __construct()
    {
    }

    public function cargarUsuario($tcUsuario='')
	{
		$llCargado=false;
		$this->datosEnBlanco();

		global $goDb;
		if(isset($goDb)){
			if(!empty($tcUsuario)){
				// Buscando datos del usuario
				$llCargado = $this->cargar($goDb->from('RIARGMN')->where('USUARI', '=', $tcUsuario)->get('array'));
			}
		}

		return $llCargado;
    }


    public function cargarRegistro($tcRegistro='')
	{
		$llCargado=false;
		$this->datosEnBlanco();

		global $goDb;
		if(isset($goDb)){
			if(!empty($tcRegistro)){
				$lcRegistro = str_pad(trim($tcRegistro), 13, '0', STR_PAD_LEFT);
				// Buscando datos del usuario
				$llCargado = $this->cargar($goDb->from('RIARGMN')->where('REGMED', '=', $lcRegistro)->get('array'));
			}
		}
		return $llCargado;
    }


    public function cargarFiltro($taFiltro, $taVista='RIARGMN')
	{
		$llCargado=false;
		$this->datosEnBlanco();

		global $goDb;
		if(isset($goDb)){
			if(!empty($taFiltro)){
				// Buscando datos del usuario
				$llCargado = $this->cargar($goDb->from($taVista)->where($taFiltro)->get('array'));
			}
		}
		return $llCargado;
    }


	private function cargar($taRiaRgmn)
	{
		$llCargado = false;
		if (is_array($taRiaRgmn)) {
			if (count($taRiaRgmn)>0) {
				$this->setTipoId(trim(strtoupper($taRiaRgmn['TIDRGM'])));
				$this->nId = intval($taRiaRgmn['NIDRGM']);
				$this->cNombre1 = trim($taRiaRgmn['NNOMED']);
				$this->cApellido1 = trim($taRiaRgmn['NOMMED']);
				$this->cUsuario = trim(strtoupper($taRiaRgmn['USUARI']));
				$this->cRegistro = trim($taRiaRgmn['REGMED']);
				$this->nTipoUsuario = intval($taRiaRgmn['TPMRGM']);
				$this->cCodEspecialidad = trim($taRiaRgmn['CODRGM']);
				$llCargado = true;
			}
		}
		return $llCargado;
	}


	public function cargarEspecialidad($tcCodEspecialidad='')
	{
		$this->aEspecialidad = [];
		if (is_string($tcCodEspecialidad)) {
			if (!empty($tcCodEspecialidad)) {
				$this->aEspecialidad = new Especialidad($tcCodEspecialidad);
			}
		}
	}

	public function datosEnBlanco()
	{
		$this->setTipoId('');
		$this->nId = 0;
		$this->cNombre1 = '';
		$this->cApellido1 = '';
		$this->cUsuario = '';
		$this->cRegistro = '';
		$this->nTipoUsuario = 0;
		$this->aEspecialidad = [];
	}

	public function getUsuario(){
		return $this->cUsuario;
	}
	public function getRegistro(){
		return $this->cRegistro;
	}
	public function getTipoUsuario(){
		return $this->nTipoUsuario;
	}
	public function getObjEspecialidad(){
		return $this->aEspecialidad;
	}
	public function getCodEspecialidad(){
		return $this->cCodEspecialidad ?? '';
	}
	public function getDscEspecialidad(){
		return $this->aEspecialidad->cNombre ?? '';
	}
}
