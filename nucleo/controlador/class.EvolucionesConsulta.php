<?php
namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';
require_once __DIR__ . '/class.AplicacionFunciones.php';
require_once __DIR__ . '/class.ListaDocumentos.php';
require_once __DIR__ . '/class.Doc_Evolucion.php';
require_once __DIR__ . '/class.TextHC.php';
use NUCLEO\AplicacionFunciones;
use NUCLEO\ListaDocumentos;
use NUCLEO\Doc_Evolucion;
use NUCLEO\TextHC;


class EvolucionesConsulta
{

	/*
	 *	Retorna los parámetros para la consulta de evoluciones
	 */
	public function obtenerParametros()
	{
		global $goDb;
		return [
			'diasAnt' => intval($goDb->obtenerTabMae1('OP3TMA', 'EVOLUC', "CL1TMA='EVOWEB' AND CL2TMA='DIASANT' AND ESTTMA=''", null, '6')),
			'diasMax' => intval($goDb->obtenerTabMae1('OP3TMA', 'EVOLUC', "CL1TMA='EVOWEB' AND CL2TMA='DIASMAX' AND ESTTMA=''", null, '0')),
		];
	}


	/*
	 *	Consulta las evoluciones
	 *	@param integer $tnIngreso: número de ingreso
	 *	@param string $tcFechaIni: fecha inicial de consulta en formato AAAA-MM-DD
	 *	@param string $tcFechaFin: fecha final de consulta en formato AAAA-MM-DD
	 *	@param string $tcTodasFechas: Se debe consultar todas las fechas? SI o NO
	 *	@return array con los elementos error y html
	 */
	public function consultaEvoluciones($tnIngreso, $tcFechaIni, $tcFechaFin, $tcTodasFechas)
	{
		$laRetorna = ['error'=>''];
		$lnFechaIni = intval(str_replace('-','',$tcFechaIni));
		$lnFechaFin = intval(str_replace('-','',$tcFechaFin));

		if ($tnIngreso > 1000000 && $tnIngreso < 9999999) {
			if ($lnFechaFin >= $lnFechaIni) {
				$laPar = $this->obtenerParametros();
				$ldFechaIni = new \DateTime($tcFechaIni);
				$ldFechaFin = new \DateTime($tcFechaFin);
				$loDif = $ldFechaIni->diff($ldFechaFin);

				if ($laPar['diasMax'] == 0 || $loDif->days <= $laPar['diasMax']) {
					ini_set('max_execution_time', 60*60); // máximo 60 minutos de consulta
					$loEvo = new Doc_Evolucion;
					if ($tcTodasFechas=='NO') {
						$laEnvia = [
							'nIngreso'=>$tnIngreso,
							'nFechaDesde'=>$lnFechaIni,
							'nFechaHasta'=>$lnFechaFin,
						];
					} else {
						$laEnvia = [
							'nIngreso'=>$tnIngreso,
						];
					}
					$laRta = $loEvo->retornarDocumento($laEnvia);
					unset($loEvo);

					if (count($laRta['aCuerpo'])>0) {
						foreach ($laRta['aCuerpo'] as &$laFila) {
							if(($laFila[0]??'')=='firmas'){
								$laFila[1] = [['txt'=>$laFila[1][0]['texto_firma']??'']];
							}
						}
						$loTextHC = new TextHC();
						$loTextHC->procesar([
							'Titulo' => "EVOLUCIONES - INGRESO $tnIngreso <br> <small>Entre $tcFechaIni y $tcFechaFin</small>",
							'Cabeza' => [
								'mostrar'=>false,
								'logo'=>false,
								'mostrarpie'=>false,
								'texto'=>'',
							],
							'Cuerpo' => $laRta['aCuerpo'],
							'DatLog' => '',
						]);
						$lcHtml = $loTextHC->cHtml();
						unset($loTextHC);
						// $laRetorna += $laRta;
						$laRetorna['html'] = $lcHtml;
					} else {
						$laRetorna['error'] = "No se encontraron registros de evoluciones para el ingreso $tnIngreso";
					}
				} else {
					$laRetorna['error'] = "Rango de fechas no puede ser superior a {$laPar['diasMax']} días";
				}
			} else {
				$laRetorna['error'] = "Fecha Desde no puede ser mayo a Fecha Hasta";
			}
		} else {
			$laRetorna['error'] = "Número de ingreso $tnIngreso incorrecto";
		}

		return $laRetorna;
	}


	/*
	 *	Consulta la lista de documentos para un cierto tipo
	 *	@param integer $tnIngreso: número de ingreso
	 *	@param string $tcTipo: tipo de documento a consultar, predeterminado EVOLUCION
	 *	@return array lista de documentos
	 */
	public function listaDocumentos($tnIngreso, $tcTipo='EVOLUCION')
	{
		$loLista = new ListaDocumentos();
		$loLista->cargarDatos($tnIngreso, '', 0, '', '', false, false);
		$loLista->obtenerVia($tnIngreso);
		$loLista->obtenerHabitaciones($tnIngreso);
		switch ($tcTipo) {
			case 'EVOLUCION':
				$loLista->consultarEvolucionesSinDetalleOM($tnIngreso);
				break;
			case 'ENFNOTAS':
				$loLista->consultarEnfNotas($tnIngreso);
				break;
			case 'ENFADMMED':
				$loLista->consultarEnfAdministraMed($tnIngreso);
				break;
			case 'ENFBALLIQ':
				$loLista->consultarEnfBalanceLiq($tnIngreso);
				break;
		}
		$laListaEvo = $loLista->organizarDocumentos();
		unset($loLista);

		return $laListaEvo;
	}

}
