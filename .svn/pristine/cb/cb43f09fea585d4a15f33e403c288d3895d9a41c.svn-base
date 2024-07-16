<?php
namespace NUCLEO;

class NumerosALetras
{
	protected static $aUnidades = [
			'UN','DOS','TRES','CUATRO','CINCO','SEIS','SIETE','OCHO','NUEVE','DIEZ',
			'ONCE','DOCE','TRECE','CATORCE','QUINCE','DIECISÉIS','DIECISIETE','DIECIOCHO','DIECINUEVE','VEINTE',
			'VEINTIUN','VEINTIDOS','VEINTITRES','VEINTICUATRO','VEINTICINCO','VEINTISEIS','VEINTISIETE','VEINTIOCHO','VEINTINUEVE'
		];
	protected static $aDecenas = [
			30=>'TREINTA',
			40=>'CUARENTA',
			50=>'CINCUENTA',
			60=>'SESENTA',
			70=>'SETENTA',
			80=>'OCHENTA',
			90=>'NOVENTA'
		];
	protected static $aCentenas = [
			100=>'CIEN',
			200=>'DOSCIENTOS',
			300=>'TRECIENTOS',
			400=>'CUATROCIENTOS',
			500=>'QUINIENTOS',
			600=>'SEISCIENTOS',
			700=>'SETECIENTOS',
			800=>'OCHOCIENTOS',
			900=>'NOVECIENTOS'
		];

	private static function basico($tnNumero)
	{
		return self::$aUnidades[$tnNumero - 1];
	}

	private static function decenas($tnNumero)
	{
		if( $tnNumero <= 29)
			return self::basico($tnNumero);
		$x = $tnNumero % 10;
		if ( $x == 0 ) {
			return self::$aDecenas[$tnNumero];
		} else
			return self::$aDecenas[$tnNumero - $x].' Y '. self::basico($x);
	}

	private static function centenas($tnNumero)
	{

		if( $tnNumero >= 100) {
			if ( $tnNumero % 100 == 0 ) {
				return self::$aCentenas[$tnNumero];
			} else {
				$u = (int) substr($tnNumero,0,1);
				$d = (int) substr($tnNumero,1,2);
				return (($u == 1)?'CIENTO': self::$aCentenas[$u*100]).' '.self::decenas($d);
			}
		} else
			return self::decenas($tnNumero);
	}

	private static function miles($tnNumero)
	{
		if($tnNumero > 999) {
			if( $tnNumero == 1000) {
				return 'MIL';
			} else {
				$l = strlen($tnNumero);
				$c = (int)substr($tnNumero,0,$l-3);
				$x = (int)substr($tnNumero,-3);
				if($c == 1) {
					$cadena = 'MIL '.self::centenas($x);
				} else if($x != 0) {
					$cadena = self::centenas($c).' MIL '.self::centenas($x);
				} else
					$cadena = self::centenas($c). ' MIL';
				return $cadena;
			}
		} else
			return self::centenas($tnNumero);
	}

	private static function millones($tnNumero)
	{
		if($tnNumero == 1000000) {
			return 'UN MILLÓN';
		} else {
			$l = strlen($tnNumero);
			$c = (int)substr($tnNumero,0,$l-6);
			$x = (int)substr($tnNumero,-6);
			if($c == 1) {
				$cadena = ' MILLÓN ';
			} else {
				$cadena = ' MILLONES ';
			}
			return self::miles($c).$cadena.(($x > 0) ? self::miles($x) : '');
		}
	}

	private static function billones($tnNumero)
	{
		if($tnNumero == 1000000000000) {
			return 'UN BILLÓN';
		} else {
			$l = strlen($tnNumero);
			$c = (int)substr($tnNumero,0,$l-12);
			$x = (int)substr($tnNumero,-12);
			if($c == 1) {
				$cadena = ' BILLÓN ';
			} else {
				$cadena = ' BILLONES ';
			}
			return self::miles($c).$cadena.(($x > 0) ? self::millones($x) : '');
		}
	}

	public static function convertir($tnValor, $tcMoneda=['',''], $tcDecimal=['',''], $tbCentavosEnLetras=false)
	{
		// Texto de moneda y centavos
		if (!is_array($tcMoneda))
			$tcMoneda = is_string($tcMoneda) ? [$tcMoneda,$tcMoneda] : ['',''];
		if (!is_array($tcDecimal))
			$tcDecimal = is_string($tcDecimal) ? [$tcDecimal,$tcDecimal] : ['',''];

		//
		$laValor = explode('.',$tnValor);
		$lnEntero = (int)$laValor[0];
		$lnDecimal = (int)substr((isset($laValor[1]) ? $laValor[1] : '').'00',0,2);

		switch (true) {
			case ($lnEntero >= 0 && $lnEntero < 1):
				$lcTexto = 'CERO';
				break;
			case ($lnEntero >= 1 && $lnEntero < 30):
				$lcTexto = self::basico($lnEntero);
				break;
			case ($lnEntero >= 30 && $lnEntero < 100):
				$lcTexto = self::decenas($lnEntero);
				break;
			case ($lnEntero >= 100 && $lnEntero < 1000):
				$lcTexto = self::centenas($lnEntero);
				break;
			case ($lnEntero >= 1000 && $lnEntero < 1000000):
				$lcTexto = self::miles($lnEntero);
				break;
			case ($lnEntero >= 1000000 && $lnEntero < 1000000000000):
				$lcTexto = self::millones($lnEntero);
				break;
			case ($lnEntero >= 1000000000000):
				$lcTexto = self::billones($lnEntero);
				break;
		}
		$lcTexto .= ' ' . ( $lnEntero==1 ? $tcMoneda[0] : $tcMoneda[1] );
		if ($lnDecimal>0)
			if ($tbCentavosEnLetras)
				$lcTexto .= ' CON ' . self::decenas($lnDecimal) . ' ' . ( $lnDecimal==1 ? $tcDecimal[0] : $tcDecimal[1] ); // centavos con letras
			else
				$lcTexto .= ' CON ' . $lnDecimal . ' ' . ( $lnDecimal==1 ? $tcDecimal[0] : $tcDecimal[1] ); // centavos con número

		return trim($lcTexto);
	}
}
