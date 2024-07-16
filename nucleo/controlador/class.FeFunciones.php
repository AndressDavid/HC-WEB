<?php

/* Funciones Facturación Electrónica */

namespace NUCLEO;

class FeFunciones
{
	/* Convierte fecha de formato número a formato AAAA-AA-AA */
	public static function formatFecha($tnFecha, $tcSep='-')
	{
		if(intval($tnFecha)==0) return '';
		$tnFecha = trim($tnFecha);
		return substr($tnFecha, 0, 4).$tcSep.substr($tnFecha, 4, 2).$tcSep.substr($tnFecha, 6, 2);
	}

	/* Convierte hora de formato número a formato HH:MM:SS */
	public static function formatHora($tnHora, $tcSep=':')
	{
		if(intval($tnHora)==0) return '';
		$tnHora = str_pad(trim($tnHora), 6, '0', STR_PAD_LEFT);
		return substr($tnHora, 0, 2).$tcSep.substr($tnHora, 2, 2).$tcSep.substr($tnHora, 4, 2);
	}

	/* Convierte fecha de formato número a formato AAAA-AA-AA */
	public static function formatFechaHora($tnFecha, $tnHora, $tcSep=' ', $tcSepF='-', $tcSepH=':')
	{
		return self::formatFecha($tnFecha, $tcSepF) . $tcSep . self::formatHora($tnHora, $tcSepH);
	}

	/* Crea tabla a partir de una array de datos */
	public static function crearTabla($taDatos=[])
	{
		if ( is_array($taDatos) ) {
			if ( count($taDatos)>0 ){
				//echo '<table class="table table-sm table-bordered table-hover">';
				echo "<table border=1 cellpadding=5 cellspacing=5>";
				echo '<thead><tr>';
				foreach($taDatos[0] as $clave => $valor){
					echo "<th>$clave</t	h>";
				}
				echo '</tr></thead>';
				echo '<tbody>';
				foreach($taDatos as $laDato){
					echo '<tr>';
					foreach($laDato as $clave => $valor){
						echo "<td>$valor</td>";
					}
					echo '</tr>';
				}
				echo '</tbody>';
				echo '</table>';
			}
		}
	}

	/* Elimina espacios repetidos en una cadena */
	public static function quitarEspacios($tcTexto)
	{
		//return trim(preg_replace('/( ){2,}/u',' ',$tcTexto));
		return trim(preg_replace('/\s+/', ' ', $tcTexto));
	}

	/* Elimina asteriscos en una cadena */
	public static function quitarAsteriscos($tcTexto)
	{
		// return preg_replace(['/*****/','/****/'], ['',''], $tcTexto);
		return str_replace(['*****','****'], '', $tcTexto);
	}

	/*
	 *	Retorna el dígito de verificación del número de documento enviado
	 *	@param integer $tnNumDoc Número de documento de 1 a 15 dígitos
	 *	@return integer Digito de verificación
	 */
	public static function digitoVerificacion($tnNumDoc)
	{
		$lnNumID = intval($tnNumDoc);	// falla si el sistema es de 32 bits y el número es superior a 2.147.483.647
		$lcNumID = str_pad($lnNumID, 15, '0', STR_PAD_LEFT);
		$laValPrs = [71,67,59,53,47,43,41,37,29,23,19,17,13,7,3];
		$lnSuma = 0;
		for ($lnI=0; $lnI<15; $lnI++) {
			$lnSuma += $lcNumID[$lnI] * $laValPrs[$lnI];
		}
		$lnVr = $lnSuma % 11;
		$lnDV = $lnVr < 2 ? $lnVr : 11 - $lnVr;

		return $lnDV;
	}

}