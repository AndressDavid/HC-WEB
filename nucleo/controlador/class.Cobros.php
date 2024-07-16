<?php
namespace NUCLEO;

require_once ('class.Db.php') ;
use NUCLEO\Db;

class Cobros
{
	protected $oDb;


	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
    }

	/*
	 *	Llama al procedimiento almacenado FACS22PCPP
	 *
	 *	@param array $taData: datos para cobro. Array con los siguientes elementos:
	 *		ingreso			Integer - Número de ingreso del paciente
	 *		numIdPac		Integer - Número de documento del paciente
	 *		codCup			String - Código CUP del procedimiento
	 *		codVia			String - Código de vía
	 *		codPlan			String - Código del plan
	 *		regMedOrdena	String - Registro médico que ordena (13 caracteres)
	 *		regMedRealiza	String - Registro médico que realiza (13 caracteres)
	 *		espMedRealiza	String - Código de especialidad médico que realiza
	 *		secCama			String - Sección - Cama del paciente (oVar.cSecCam + oVar.cNroCam)
	 *		cnsCita			Integer - Consecutivo de cita
	 *		portatil		String - Portátil (opcional)
	 *	@return el valor devuelto por el storedProcedure o un boolean indicando si se ejecutó o no el store procedure
	*/
	public function cobrarProcedimiento($taData)
	{
		// parámetros entrada
		$laParamEntrada = $this->creaParam($taData);

		return $this->oDb->storedProcedure('FACS22PCPP', $laParamEntrada);
    }

	/*
	 *	Llama al procedimiento almacenado FACS24PCPP
	 *
	 *	@param array $taData: datos para cobro. Array con los siguientes elementos:
	 *		ingreso			Integer - Número de ingreso del paciente
	 *		numIdPac		Integer - Número de documento del paciente
	 *		codCup			String - Código CUP del procedimiento
	 *		codVia			String - Código de vía
	 *		codPlan			String - Código del plan
	 *		regMedOrdena	String - Registro médico que ordena (13 caracteres)
	 *		regMedRealiza	String - Registro médico que realiza (13 caracteres)
	 *		espMedRealiza	String - Código de especialidad médico que realiza
	 *		secCama			String - Sección - Cama del paciente (oVar.cSecCam + oVar.cNroCam)
	 *		cnsCita			Integer - Consecutivo de cita
	 *		portatil		String - Portátil (opcional)
	 *		cantidad		Integer - Cantidad en horas a cobrar
	 *	@return el valor devuelto por el storedProcedure o un boolean indicando si se ejecutó o no el store procedure
	*/
	public function cobrarProcedimientoCantidad($taData)
	{
		// parámetros entrada
		$laParamEntrada = $this->creaParam($taData) + [
			'cantidad'	=> [$taData['cantidad']??'1' , \PDO::PARAM_STR],
		];

		return $this->oDb->storedProcedure('FACS24PCPP', $laParamEntrada);
    }

	/*
	 *	Llama al procedimiento almacenado FACS25PCP
	 *
	 *	@param array $taData: datos para cobro. Array con los siguientes elementos:
	 *		ingreso			Integer - Número de ingreso del paciente
	 *		numIdPac		Integer - Número de documento del paciente
	 *		codCup			String - Código CUP del procedimiento
	 *		codVia			String - Código de vía
	 *		codPlan			String - Código del plan
	 *		regMedOrdena	String - Registro médico que ordena (13 caracteres)
	 *		regMedRealiza	String - Registro médico que realiza (13 caracteres)
	 *		espMedRealiza	String - Código de especialidad médico que realiza
	 *		secCama			String - Sección - Cama del paciente (oVar.cSecCam + oVar.cNroCam)
	 *		cnsCita			Integer - Consecutivo de cita
	 *		portatil		String - Portátil (opcional)
	 *		centroServicio	String - Centro de servicio
	 *	@return el valor devuelto por el storedProcedure o un boolean indicando si se ejecutó o no el store procedure
	*/
	public function cobrarCupsCentroServicio($taData)
	{
		// parámetros entrada
		$laParamEntrada = $this->creaParam($taData) + [
			'centroServicio'	=> [$taData['centroServicio']??'' , \PDO::PARAM_STR],
		];

		return $this->oDb->storedProcedure('FACS25PCP', $laParamEntrada);
    }

	/*
	 *	Genera array para enviar a storedProcedure
	 *
	 *	@param array $taData: datos para cobro
	 */
	private function creaParam($taData)
	{
		$lcUser = defined('HCW_NAME') ? (isset($_SESSION[HCW_NAME]) ? $_SESSION[HCW_NAME]->oUsuario->getUsuario() : 'SRVHCWEB') : 'SRVHCWEB';
		return [
			'ingreso'		=> [str_pad(trim($taData['ingreso']), 8, '0', STR_PAD_LEFT), \PDO::PARAM_STR],
			'codVia'		=> [$taData['codVia'], \PDO::PARAM_STR],
			'codPlan'		=> [str_pad(trim($taData['codPlan']), 6, ' ', STR_PAD_RIGHT), \PDO::PARAM_STR],
			'regMedOrdena'	=> [$taData['regMedOrdena']??'' , \PDO::PARAM_STR],
			'regMedRealiza'	=> [$taData['regMedRealiza']??'', \PDO::PARAM_STR],
			'numIdPac'		=> [$this->obtenerId13($taData['numIdPac']), \PDO::PARAM_STR],
			'codCup'		=> [str_pad(trim($taData['codCup']), 8, ' ', STR_PAD_RIGHT), \PDO::PARAM_STR],
			'secCama'		=> [str_pad(trim($taData['secCama']), 8, ' ', STR_PAD_RIGHT), \PDO::PARAM_STR],
			'espMedRealiza'	=> [str_pad(trim($taData['espMedRealiza']), 6, ' ', STR_PAD_RIGHT), \PDO::PARAM_STR],
			'cnsCita'		=> [str_pad(trim($taData['cnsCita']), 6, '0', STR_PAD_LEFT), \PDO::PARAM_STR],
			'valor'			=> [str_repeat('0', 15), \PDO::PARAM_STR],
			'centroCosto'	=> [$taData['centroCosto']??'' , \PDO::PARAM_STR],
			'portatil'		=> [is_string($taData['portatil']) ? $taData['portatil'] : '', \PDO::PARAM_STR],
			'usuario'		=> [$lcUser, \PDO::PARAM_STR],
		];
	}

	/*
	 *	Obtiene id para cobro
	 */
	public function obtenerId13($tnNroIde)
	{
		if ($tnNroIde > 9999999999) {
			$lnRes = $tnNroIde % 10000000000;
			$lcNidPac = str_pad(substr(strval(($tnNroIde - $lnRes)/10000000000) . strval($lnRes), 0, 13), 13, '0', STR_PAD_LEFT);
		} else {
			$lcNidPac = str_pad(strval($tnNroIde), 13, '0', STR_PAD_LEFT);
		}

		return $lcNidPac;
	}

}