<?php
namespace NUCLEO;

class PeriodosFechas
{
	private $dFechaIni;
	private $dFechaFin;
	private $nIntervalo;
	private $oIntervalo;
	private $aPeriodos = [];

	function __construct($tdFechaInicial, $tdFechaFinal, $tnIntervalo)
	{
		$this->dFechaIni = $tdFechaInicial;
		$this->dFechaFin = $tdFechaFinal;
		$this->nIntervalo = $tnIntervalo;
		$this->oIntervalo = new \DateInterval("P{$tnIntervalo}D");
		$this->calcularPeriodos();
	}

	function calcularPeriodos()
	{
		$loIntervalo1 = new \DateInterval('P1D');
		$loPeriodo = new \DatePeriod($this->dFechaIni, $this->oIntervalo, $this->dFechaFin);

		if ($this->dFechaIni==$this->dFechaFin) {
			$this->aPeriodos[0] = [
				'ini' => $this->dFechaFin->format('Y-m-d'),
				'fin' => $this->dFechaFin->format('Y-m-d'),
				'dias'=> 1,
			];
		} else {
			$lnIndice = 0;
			foreach ($loPeriodo as $lnKey=>$ldFecha) {
				$this->aPeriodos[$lnKey]['ini'] = $ldFecha->format('Y-m-d');
				if ($lnKey>0) {
					$this->calcularFinDias($lnKey-1, (new \DateTime($ldFecha->format('Y-m-d')))->sub($loIntervalo1));
				}
				$lnIndice++;
			}
			$this->calcularFinDias($lnIndice-1, $this->dFechaFin);
		}
	}

	function calcularFinDias($tnIndice, $tdFechaFin)
	{
		$ldFechaIni = new \DateTime($this->aPeriodos[$tnIndice]['ini']);
		$this->aPeriodos[$tnIndice]['fin'] = $tdFechaFin->format('Y-m-d');
		$this->aPeriodos[$tnIndice]['dias'] = (date_diff($tdFechaFin, $ldFechaIni))->days + 1;
	}

	function obtenerPeriodos()
	{
		return $this->aPeriodos;
	}
}