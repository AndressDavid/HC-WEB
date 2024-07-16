<?php
namespace NUCLEO;
require_once __DIR__ . '/class.Db.php';
require_once __DIR__ . '/class.AplicacionFunciones.php';
use NUCLEO\AplicacionFunciones;

class Epidemiologia
{
	public $aParAisl = [];


	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}

	/*
	*	Verifica si el paciente tiene aislamiento, por documento de identidad
	*	@param string $tcTipoIde: tipo de identificación
	*	@param integer $tnNroIde: número de identificación
	*	@return string: alerta si tiene aislamiento
	*/
	public function tieneAislamientoDoc($tcTipoIde, $tnNroIde)
	{
		$lcAlerta = $lcTipo = '';
		$lnItem = 3;
		if(count($this->aParAisl)==0) $this->obtenerDescripciones();

		$laAislamiento = $this->oDb
			->select('E.OP1EPI, E.DETEPI, E.FEIEPI, E.FECEPI, E.DE2EPI')
			->from('EPIRGS E')
			->innerJoin('RIAING I','I.NIGING = E.INGEPI')
			->where([
				'I.TIDING'=>$tcTipoIde,
				'I.NIDING'=>$tnNroIde,
				'E.ITEEPI'=>$lnItem,
				'E.REGEPI'=>1,
			])
			->orderBy('I.NIGING DESC, E.CONEPI DESC')
			->get('array');
		if ($this->oDb->numRows()>0) {
			if ($laAislamiento['OP1EPI']!=='R') {
				$laDet = explode('¤',$laAislamiento['DETEPI']);
				$lcTemp = trim(substr($laDet[0], 5, 6));
				if (!empty($lcTemp)){
					$lcTipo = $this->aParAisl[$lcTemp]??'';
				}

				$lcDias = substr($laDet[1], 5, strlen($laDet[1])-5);

				switch (true) {
					case empty($lcTipo) && empty($lcDias):
						$lcAlerta = '';
						break;

					case (!empty($lcTipo)) && empty($lcDias):
						$lcAlerta = "El paciente requiere Precaución: $lcTipo";
						break;

					case (!empty($lcTipo)) && (!empty($lcDias)):
						$lcAdicionAlerta = '';

						// Fecha CANDIDA AURIS
						$lnPos = mb_strpos($laAislamiento['DETEPI'], '0316', 0, 'UTF-8');
						$lnFechaCA = $lnPos===false ? 0 : intval(mb_substr($laAislamiento['DETEPI'], $lnPos+5, 8, 'UTF-8'));

						// Fecha CARBAPENEMICOS
						$lnFechaCP = intval($laAislamiento['FEIEPI']);

						$laDe2epi = explode('¤', $laAislamiento['DE2EPI']);

						if (empty($lnFechaCP) && empty($lnFechaCA)) {
							$ldFecha = new \DateTime(AplicacionFunciones::formatFechaHora('fecha', $laAislamiento['FECEPI']));
							$ldFecha->add(new \DateInterval('P'.$lcDias.'D'));
						} else {
							$lnFecha  = $lnFechaCA>$lnFechaCP ? $lnFechaCA : $lnFechaCP;
							$ldFecha = new \DateTime(AplicacionFunciones::formatFechaHora('fecha', $lnFecha));
							$ldFecha->add(new \DateInterval('P365D'));
							$lcAdicionAlerta = ' - ' . mb_substr($laDe2epi[0], 5, null, 'UTF-8');
							if ((!empty($lnFechaCP)) && (!empty($lnFechaCA))) {
								$lcAdicionAlerta .= ' y ' . mb_substr($laDe2epi[1], 5, null, 'UTF-8');
							}
						}
						$ldFechaSist = new \DateTime(substr($this->oDb->FechaHoraSistema(),0,10));
						$loIntervalo = date_diff($ldFechaSist, $ldFecha);
						$lnDiasFaltan = intval($loIntervalo->format('%R%a'));

						if ($lnDiasFaltan > 0){
							$lcDiasCHR = $lnDiasFaltan>1 ? ' Días ' : ' Día ';
							$lcAlerta = ($lnDiasFaltan>0 ? "Faltan $lnDiasFaltan $lcDiasCHR de Precauciónes de $lcTipo" : '').$lcAdicionAlerta;
						}
						break;
				}
			}
		}

		return $lcAlerta;
	}

	/*
	 *	Verifica si el paciente presenta COVID-19
	 *	@param string $tcTipoIde: tipo de identificación
	 *	@param integer $tnNroIde: número de identificación
	 *	@return string: alerta si tiene covid
	 */
	public function tieneCovidDoc($tcTipoIde, $tnNroIde)
	{
		$lcAlerta = $lcTipo = '';
		$lnItem = 7;
		if(count($this->aParAisl)==0) $this->obtenerDescripciones();

		$laAislamiento = $this->oDb
			->select('E.OP1EPI, E.DETEPI, E.FEIEPI, E.FECEPI, E.DE2EPI')
			->from('EPIRGS E')
			->innerJoin('RIAING I','I.NIGING = E.INGEPI')
			->where([
				'I.TIDING'=>$tcTipoIde,
				'I.NIDING'=>$tnNroIde,
				'E.ITEEPI'=>$lnItem,
				'E.REGEPI'=>1,
			])
			->orderBy('I.NIGING DESC, E.CONEPI DESC')
			->get('array');
		if ($this->oDb->numRows()>0) {
			if ($laAislamiento['OP1EPI']!=='R') {
				$laDetepi = explode('¤', $laAislamiento['DETEPI']);
				$lcTemp = mb_substr($laDetepi[0], 5, 1, 'UTF-8');
				$lcCovid = $lcTemp=='1' ? 'PACIENTE SOSPECHOSO O CONFIRMADO PARA COVID-19 ' :
						($lcTemp=='3' ? 'PACIENTE SOSPECHOSO PARA COVID-19 ' :
						($lcTemp=='4' ? 'PACIENTE CONFIRMADO PARA COVID-19 ' : ''));

				$lcTemp = mb_substr($laDetepi[1], 5, 6, 'UTF-8');
				if (!empty($lcTemp)){
					$lcTipo = $this->aParAisl[$lcTemp]??'';
				}

				$lcTemp = $laDetepi[2] ?? '';
				$lcDias = mb_substr($lcTemp, 5, null, 'UTF-8');

				if ((!empty($lcTipo)) && (!empty($lcDias))) {
					$ldFecha = new \DateTime(AplicacionFunciones::formatFechaHora('fecha', $laAislamiento['FECEPI']));
					$ldFechaSist = new \DateTime(substr($this->oDb->FechaHoraSistema(),0,10));
					$loIntervalo = date_diff($ldFechaSist, $ldFecha);
					$lnDiasFaltan = intval($lcDias) - intval($loIntervalo->format('%R%a'));

					if ($lnDiasFaltan > 0) {
						$lcDiasCHR = $lnDiasFaltan>1 ? ' Días ' : ' Día ';
						$lcAlerta = $lcCovid . ($lnDiasFaltan>0 ? "Faltan $lnDiasFaltan $lcDiasCHR de Precauciónes de $lcTipo" : '');
					}

				} elseif (!empty($lcTipo)) {
					$lcAlerta = $lcCovid . 'El paciente requiere Precaución: ' . $lcTipo;
				}
			}
		}

		return $lcAlerta;
	}

	public function obtenerDescripciones()
	{
		$laParam = $this->oDb
			->select('DESENF, REFENF, VARENF, TIPENF')
			->from('TABENF')
			->where('TIPENF=28 AND VARENF=1')
			->orderBy('REFENF')
			->getAll('array');
		if (is_array($laParam)){
			foreach ($laParam as $laPar) {
				$laPar = array_map('trim', $laPar);
				$lcCodigo = str_pad($laPar['TIPENF'], 2, '0', STR_PAD_LEFT).str_pad($laPar['VARENF'], 2, '0', STR_PAD_LEFT).str_pad($laPar['REFENF'], 2, '0', STR_PAD_LEFT);
				$this->aParAisl[$lcCodigo] = $laPar['DESENF'];
			}
		}
	}


}