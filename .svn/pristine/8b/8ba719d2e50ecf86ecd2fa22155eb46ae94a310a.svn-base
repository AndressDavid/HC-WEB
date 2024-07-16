<?php
namespace NUCLEO;

require_once ('class.Db.php') ;
use NUCLEO\Db;

class NoPosFunciones
{
	protected $oDb;


	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}


	public function consultarNopos($taLista)
	{
		$lcTextoCups = $lcTextoMedicamento = $lcTextoNopos = '';
		$lcChrEnter = chr(13);

		$laMedNoPos = $laCupNoPos = [];

		if (is_array($taLista['cup'])){
			if (count($taLista['cup'])>0){
				foreach ($taLista['cup'] as $laCups){
					$laReg = $this->oDb
						->select('CODCUP,DESCUP')
						->from('RIACUP')
						->where([
							'IDDCUP'=>'0',
							'CODCUP'=>$laCups['CODIGO'],
							'RF5CUP'=>'NOPB',
						])
						->get('array');
					if (is_array($laReg)){
						if (count($laReg)>0){
							$laCupNoPos[trim($laReg['CODCUP'])] = trim($laReg['DESCUP']);
						}
					}
				}
			}
		}
		if (is_array($taLista['med'])){
			if (count($taLista['med'])>0){
				foreach ($taLista['med'] as $laMed){
					$laReg = $this->oDb
						->select('REFDES,DESDES')
						->from('INVDES')
						->where([
							'REFDES'=>$laMed['CODIGO'],
							'RF4DES'=>'NOPOS',
						])
						->get('array');
					if (is_array($laReg)){
						if (count($laReg)>0){
							$laMedNoPos[trim($laReg['REFDES'])] = trim($laReg['DESDES']);
						}
					}
				}
			}
		}

		return ['med'=>$laMedNoPos,'cup'=>$laCupNoPos,];
	}


	public function obtenerTextoNopos($taLista)
	{
		$lcSL = chr(13);
		$lcTextoNoPos = '';
		if (count($taLista['med'])>0) {
			$lcTextoNoPos .= 'Medicamentos NOPOS' . $lcSL;
			foreach ($taLista['med'] as $lcCodMed=>$lcMed) {
				$lcTextoNoPos .= $lcMed . $lcSL;
			}
			$lcTextoNoPos .= $lcSL;
		}
		if (count($taLista['cup'])>0) {
			$lcTextoNoPos .= 'Procedimientos NOPOS' . $lcSL;
			foreach ($taLista['cup'] as $lcCodCup=>$lcCup) {
				$lcTextoNoPos .= $lcCodCup . ' - ' . $lcCup . $lcSL;
			}
		}
		return $lcTextoNoPos;
	}


	public function entidadMipres($tcPlan)
	{
		$lcRegimenPlan = '';
		$lcPlan	= is_string($tcPlan)? trim($tcPlan): '';
		if (!empty($lcPlan)) {
			$lnValidaPorPlan = intval($this->oDb->obtenerTabMae1('DE2TMA', 'WSNOPOS', 'CL1TMA=\'PLANOPOS\' AND CL2TMA=\'1\' AND ESTTMA=\'\'', null, '0'));

			if ($lnValidaPorPlan>0) {
				$lcComplementario	= trim($this->oDb->obtenerTabMae1('CL3TMA', 'WSNOPOS', "CL1TMA='COMPLE' AND CL2TMA='$lcPlan' AND ESTTMA=''", null, ''));
				$lcTipoEntidad		= empty($lcComplementario)? $this->ObtenerDatosPlan('TENCON', $lcPlan, 'TENCON<>\'\''): $lcComplementario;
				$lcRegimenPlan 		= empty($lcTipoEntidad)? '': trim($this->oDb->obtenerTabMae1('SUBSTR(CL3TMA,1,1) || \'-\' || SUBSTR(CL4TMA,1,1)', 'WSNOPOS', "CL1TMA='TIPENT' AND CL2TMA='$lcTipoEntidad' AND ESTTMA=''", null, ''));
			}
		}

		return $lcRegimenPlan;
	}
	
	public function obligarMipres()
	{
		$llObligarMipres=false;
		$llObligarMipres="1"==$this->oDb->obtenerTabmae1('OP1TMA', 'WSNOPOS', "CL1TMA='PLANOPOS' AND CL2TMA='3' AND ESTTMA=''", null, '');

		return $llObligarMipres;
	}
	
	public function PacienteExcluidoMipres($tnIngreso=0)
	{
		$lcPacientesExcluido='';
		if ($tnIngreso>0){
			$laPacientesExcluido = $this->oDb
				->select('CNSMNE')
				->from('NPSMPEP')
				->where([
					'trgnme'=>'EXC',
					'ingnme'=>$tnIngreso,
				])
				->get('array');
			if (is_array($laPacientesExcluido)){
				if (count($laPacientesExcluido)>0){
					$lcPacientesExcluido='S';
				}
			}
		}
		return $lcPacientesExcluido;
	}


	public function ObtenerDatosPlan($tcCampoRetorno, $tcTipPlan='', $tcWhere='', $tcOrder='')
	{
		$lReturn = '';
		if (is_string($tcCampoRetorno) && !empty($tcCampoRetorno) && is_string($tcTipPlan) && !empty($tcTipPlan) && is_string($tcWhere)) {
			if (!empty($tcWhere)) $this->oDb->where($tcWhere);
			if (!empty($tcOrder)) $this->oDb->orderBy($tcOrder);
			$laTabla = $this->oDb
				->select($tcCampoRetorno.' DATORET')
				->from('FACPLNC')
				->where(['PLNCON'=>$tcTipPlan])
				->get('array');
			if (is_array($laTabla)) {
				if (count($laTabla)>0) {
					$lReturn = $laTabla['DATORET'];
				}
			}
		}
		return $lReturn;
	}
}
