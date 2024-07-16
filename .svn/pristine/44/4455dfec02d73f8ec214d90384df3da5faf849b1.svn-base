<?php
namespace NUCLEO;

require_once 'class.TipoDocumento.php';
require_once ('class.Db.php');

use NUCLEO\TipoDocumento;
use NUCLEO\Db;

class Persona
{
	public $aTipoId = ['TIPO' => '','ABRV' => '','NOMBRE' => '','NUMERO' => ''];
	public $nId = 0;
	public $cIdLugarExpedicion = '';
	public $cPasaporte = '';
	public $cTipoPermiso = '';
	public $cIdPermiso = '';
	public $cNombre1 = '';
	public $cNombre2 = '';
	public $cApellido1 = '';
	public $cApellido2 = '';
	public $cEmail = '';
	public $nNacio = 0;
	public $cSexo = '';
	public $cDescSexo = '';

	function __construct() {
	}

	public function setTipoId($tcIdTipo)
	{
		$this->aTipoId = (new TipoDocumento($tcIdTipo))->aTipo;
	}

	// Retorna la edad comparando dos fechas
	// Especifique el formato según su necesidad
	// '%y Year %m Month %d Day %h Hours %i Minute %s Seconds' =>  1 Year 3 Month 14 Day 11 Hours 49 Minute 36 Seconds
	// '%a Days =>  468 Days
	public function getEdad($tcFecha='', $tcFormato='', $taNombres=['Años', 'Meses', 'Días'])
	{
		$ldNacio = date_create($this->nNacio);
		$ldCorte = date_create(empty($tcFecha) ? date("Y-m-d") : $tcFecha);
		$lcEdad = '';

		if($ldNacio!==false && $ldCorte!==false){
			$toDiferencia= date_diff($ldNacio, $ldCorte);
			if($toDiferencia!==false){
				if(empty($tcFormato)==true){
					$tcFormato  = ($toDiferencia->y>0 ? "%y $taNombres[0]" : '');
					$tcFormato .= ($toDiferencia->m>0 ? (empty($tcFormato) ? '' : ', ')."%m $taNombres[1]" : '');
					$tcFormato .= ($toDiferencia->d>0 ? (empty($tcFormato) ? '' : ', ')."%d $taNombres[2]" : '');
				}
				$lcEdad = $toDiferencia->format($tcFormato);
			}
		}
		return $lcEdad;
	}

	// Funcion para determinar si la persona es adulta o no
	public function esAdulto($tcFecha='')
	{
		global $goDb;
		$tcFecha = empty($tcFecha) ? date("Y-m-d") : $tcFecha;
		$lnEdad = $this->getEdad($tcFecha,"%y"); $lnEdad+=0;

		$lnEdadMinima = $this->edadPediatrica();

		return ($lnEdad>=$lnEdadMinima);
	}

	// Obtiene edad límite para paciente pediátrico
	public function edadPediatrica()
	{
		global $goDb;
		$loTabMae = $goDb->ObtenerTabMae('DE2TMA', 'EPICRIS', ['CL1TMA'=>'EDADMEN']);
		$lnEdadPediatrica = intval(trim(AplicacionFunciones::getValue($loTabMae,'DE2TMA',15)));

		return $lnEdadPediatrica;
	}

	/*
	 *	Funciones que retornan nombres y apellidos de una persona
	 */
	public function getNombreCompleto()
	{
		return $this->getNombresApellidos();
	}
	public function getNombres()
	{
		return $this->pregReplaceComun(trim($this->cNombre1 . ' ' . $this->cNombre2));
	}
	public function getApellidos()
	{
		return $this->pregReplaceComun(trim($this->cApellido1 . ' ' . $this->cApellido2));
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

	/*
	 *	Retorna descripción del género
	 */
	public function getGenero()
	{
		global $goDb;
		$lcDescripcionSexo = $goDb->obtenerTabmae1('DE2TMA', 'SEXPAC', "CL1TMA='$this->cSexo'", null, '');
		return $lcDescripcionSexo;
	}

}
?>