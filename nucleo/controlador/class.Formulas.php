<?php
namespace NUCLEO;

class Formulas
{
	public static $aMetodosSC = [];
	public static $aMetodosPesoIdeal = [];

	/*
	*	Cálculo de la superficie corporal
	*	@param float $tnPeso: peso en kg
	*	@param integer $tnTalla: talla en cm
	*	@param string $tcMetodo: método a utilizar, por defecto SHAIO
	*	@return float: superficie corporal en m^2
	*/
	public static function SuperficieCorporal($tnPeso, $tnTalla=0, $tcMetodo='SHAIO')
	{
		if (count(self::$aMetodosSC)==0) self::setMetodosSC();
		$tcMetodo = strtoupper(trim($tcMetodo));

		if (!in_array($tcMetodo, array_keys(self::$aMetodosSC))) {
			$tcMetodo='SHAIO';
		}
		$lnSC = self::$aMetodosSC[$tcMetodo]($tnPeso, $tnTalla);

		return number_format($lnSC, 2);
	}

	/*
	*	Cálculo de la superficie corporal
	*	@param float $tnPeso: peso en kg
	*	@param integer $tnTalla: talla en cm
	*	@return array: superficie corporal en m^2 usando todos los métodos
	*/
	public static function SuperficieCorporalTodas($tnPeso, $tnTalla)
	{
		if (count(self::$aMetodosSC)==0) self::setMetodosSC();
		$laSC = [];

		foreach (array_keys(self::$aMetodosSC) as $lcMetodo) {
			$laSC[$lcMetodo] = number_format(self::$aMetodosSC[$lcMetodo]($tnPeso, $tnTalla), 5);
		}

		return $laSC;
	}

	/*
	*	Métodos para cálculo de la Superficie Corporal
	*/
	public static function setMetodosSC()
	{
		self::$aMetodosSC = [
			'SHAIO'			=> function($tnPeso, $tnTalla) { return 0.02 * $tnPeso + 0.4; },
			'VIERORDT'		=> function($tnPeso, $tnTalla) { return 0.123 * ($tnPeso ** (2/3)); },
			'MOSTELLER' 	=> function($tnPeso, $tnTalla) { return sqrt($tnPeso * $tnTalla / 3600); },
			'HAYCOCK'		=> function($tnPeso, $tnTalla) { return 0.024265 * ($tnPeso ** 0.5378) * ($tnTalla ** 0.3974); },
			'BIERING'		=> function($tnPeso, $tnTalla) { return 0.109 * ($tnPeso ** (2/3)); },
			'DUBOIS-DUBOIS'	=> function($tnPeso, $tnTalla) { return 0.007184 * ($tnPeso ** 0.425) * ($tnTalla ** 0.725); },
			'BOYD'			=> function($tnPeso, $tnTalla) { return 0.03330 * ($tnPeso ** (0.6157 - 0.0188 * log10($tnPeso))) * ($tnTalla ** 0.3); },
			'GEHAN'			=> function($tnPeso, $tnTalla) { return 0.0235 * ($tnPeso ** 0.51456) * ($tnTalla ** 0.42246); },
			'ISACKSON'		=> function($tnPeso, $tnTalla) { return 1 + ($tnPeso + $tnTalla - 160) / 100; },
			'BREITMAN'		=> function($tnPeso, $tnTalla) { return 0.0087 * ($tnPeso + $tnTalla) - 0.26; },
			'FUJIMOTO'		=> function($tnPeso, $tnTalla) { return 0.008883 * ($tnPeso ** 0.444) * ($tnTalla ** 0.663); },
			'TAKAHIRA'		=> function($tnPeso, $tnTalla) { return 0.007241 * ($tnPeso ** 0.425) * ($tnTalla ** 0.725); },
			'SHUTER-ASLANI'	=> function($tnPeso, $tnTalla) { return 0.00949 * ($tnPeso ** 0.441) * ($tnTalla ** 0.655); },
			'LIPSCOMBE'		=> function($tnPeso, $tnTalla) { return 0.00878108 * ($tnPeso ** 0.434972) * ($tnTalla ** 0.67844); },
			'SCHLICH-F'		=> function($tnPeso, $tnTalla) { return 0.000975482 * ($tnPeso ** 0.46) * ($tnTalla ** 1.08); },
			'SCHLICH-M'		=> function($tnPeso, $tnTalla) { return 0.000579479 * ($tnPeso ** 0.38) * ($tnTalla ** 1.24); },
		];
	}

	/*
	*	Cálculo del índice de masa corporal IMC
	*	@param float $tnPeso: peso en kg
	*	@param integer $tnTalla: talla en cm
	*	@return float: índice de masa corporal
	*/
	public static function IMC($tnPeso, $tnTalla)
	{
		$lnIMC = ($tnPeso>0 && $tnTalla>0) ? $tnPeso / (($tnTalla / 100) ** 2) : 0;
		return number_format($lnIMC, 2);
	}

	/*
	*	Cálculo del peso ideal
	*	@param integer $tnTalla: talla en cm
	*	@param string $tcSexo: F, FEMENINO, M, MASCULINO
	*	@return array: peso ideal en kg
	*/
	public static function PesoIdeal($tnTalla, $tcSexo, $tcMetodo='DEVINE')
	{
		if (count(self::$aMetodosPesoIdeal)==0) self::metodosPesoIdeal();

		$tcSexo = substr(strtoupper($tcSexo),0,1); $tcSexo = in_array($tcSexo, ['F','M']) ? $tcSexo : 'F';
		$tcMetodo = strtoupper(trim($tcMetodo));

		if (!in_array($tcMetodo, array_keys(self::$aMetodosPesoIdeal))) {
			$tcMetodo='DEVINE';
		}
		$lnPesoIdeal = self::$aMetodosPesoIdeal[$tcMetodo]($tnTalla, $tcSexo);

		return number_format($lnPesoIdeal, 2);
	}

	/*
	*	Cálculo del peso ideal
	*	@param float $tnPeso: peso actual en kg
	*	@param integer $tnTalla: talla en cm
	*	@param string $tcSexo: F, FEMENINO, M, MASCULINO
	*	@return array: peso ideal en kg, % peso ideal, peso por método
	*/
	public static function PesoIdealTodos($tnPeso, $tnTalla, $tcSexo)
	{
		if (count(self::$aMetodosPesoIdeal)==0) self::metodosPesoIdeal();

		$tcSexo = substr(strtoupper($tcSexo),0,1); $tcSexo = in_array($tcSexo, ['F','M']) ? $tcSexo : 'F';
		$laPesoIdeal = [];
		$lnSuma = 0;

		foreach (array_keys(self::$aMetodosPesoIdeal) as $lcMetodo) {
			$lnPeso = self::$aMetodosPesoIdeal[$lcMetodo]($tnTalla, $tcSexo);
			$lnSuma += $lnPeso;
			$laPesoIdeal[$lcMetodo] = number_format($lnPeso, 2);
		}

		$laPesoIdeal['PESOIDEAL'] = number_format($lnSuma / count($laPesoIdeal), 2); // Promedio de los pesos ideales calculados
		$laPesoIdeal['PORCENTAJE'] = $tnPeso>0 ? number_format($tnPeso / $laPesoIdeal['PESOIDEAL'] * 100, 2) : 0;

		return $laPesoIdeal;
	}

	/*
	*	Métodos para cálculo del Peso Ideal
	*/
	public static function metodosPesoIdeal()
	{
		self::$aMetodosPesoIdeal = [
			'DEVINE' 	=> function($tnTalla, $tcSexo) { return ($tcSexo=='F' ? 45.5 : 50) + 2.3/2.54 * ($tnTalla - 152.4); },
			'ROBINSON'	=> function($tnTalla, $tcSexo) { return ($tcSexo=='F' ? (49 + 1.7/2.54 * ($tnTalla - 152.4)) : (52 + 1.9/2.54 * ($tnTalla - 152.4))); },
			'MILLER'	=> function($tnTalla, $tcSexo) { return ($tcSexo=='F' ? (53.1 + 1.36/2.54 * ($tnTalla - 152.4)) : (53.2 + 1.41/2.54 * ($tnTalla - 152.4))); },
			'HAMWI'		=> function($tnTalla, $tcSexo) { return ($tcSexo=='F' ? (45.5 + 2.2/2.54 * ($tnTalla - 152.4)) : (48 + 2.7/2.54 * ($tnTalla - 152.4))); },
			'LEMMENS'	=> function($tnTalla, $tcSexo) { return 0.22 * $tnTalla; },
			'BROCA'		=> function($tnTalla, $tcSexo) { return $tnTalla - 100; },
			'LORENTZ'	=> function($tnTalla, $tcSexo) { return $tnTalla - 100 - ($tcSexo=='F' ? 1/2.5 : 1/4) * ($tnTalla - 150); },
			'MLI'		=> function($tnTalla, $tcSexo) { return 50 + 0.75 * ($tnTalla - 150); },
			'PCI'		=> function($tnTalla, $tcSexo) { return (($tnTalla / 100) ** 2) * ($tcSexo=='F' ? 21.5 : 23); },
		];
	}

	/*
	*	Cálculo de peso ideal y peso ajustado
	*	@param float $tnPeso: peso actual en kg
	*	@param integer $tnTalla: talla en cm
	*	@param string $tcSexo: F, FEMENINO, M, MASCULINO
	*	@param string $tcMetodo: método a utilizar, por defecto PCI
	*	@return array: pesos ideal y ajustado en kg
	*/
	public static function PesoIdealAjustado($tnPeso, $tnTalla, $tcSexo, $tcMetodo='PCI')
	{
		$lnIdeal = self::PesoIdeal($tnTalla, $tcSexo, $tcMetodo);
		$lnAjustado = $lnIdeal + ($tnPeso - $lnIdeal) * 0.25;
		return [
			'IDEAL'		=> $lnIdeal,
			'AJUSTADO'	=> number_format($lnAjustado, 2),
		];
	}

	/*
	*	Cálculo de peso ajustado
	*	@param float $tnPeso: peso actual en kg
	*	@param float $tnPesoIdeal: peso ideal en kg
	*	@return array: peso ajustado en kg
	*/
	public static function PesoAjustado($tnPeso, $tnPesoIdeal)
	{
		return number_format($tnPesoIdeal + ($tnPeso - $tnPesoIdeal) * 0.25, 2);
	}
}