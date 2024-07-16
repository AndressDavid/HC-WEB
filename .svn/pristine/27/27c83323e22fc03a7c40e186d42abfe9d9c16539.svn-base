<?php
namespace NUCLEO;

require_once __DIR__ . '/class.Persona.php';

use NUCLEO\Persona;


class Covid19
{
	protected $oDb;

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
    }

	/*
	 *	Cargar configuración para alerta
	 */
	public function cargarParametros()
	{
		// Valores por defecto
		$laResp = [
			'cAlertaMensaje'=>'1~El paciente presenta uno o más diagnósticos que identifican IRAG~'
							 .'¡¡¡ No olvide realizar las Fichas de Notificación SIVIGILA !!!~'
							 .'¿Paciente Aplica?~SI Aplica~NO Aplica',
			'cLinkFichas'	=>'http://intranet.shaio.org/intranet/index.php?view-explore-folder&P=jgohortiz&S=UTF-8&O=UTF-8&N=RmljaGFzIElSQUc=&F=ZG9jdW1lbnRzLXNoYXJlZC9FcGlkZW1pb2xvZ2lhL0HDkU8gMjAyMC9DT1JPTkFWSVJVUy0xOS9GaWNoYXMgSVJBRy8=',
			'lAlertaActiva'	=>$this->oDb->obtenerTabMae1('OP1TMA', 'COVID19', 'CL1TMA=\'ALERTA\' AND ESTTMA=\'\'', null, '')=='S',
		];
		// Si está activa consulta configuración
		if ($laResp['lAlertaActiva']) {
			$laResp['cAlertaMensaje'] = $this->oDb->obtenerTabMae1('OP1TMA||\'~\'||DE2TMA||OP5TMA', 'COVID19', 'CL1TMA=\'MENSAJE\' AND ESTTMA=\'\'', null, $laResp['cAlertaMensaje']);
			$laResp['cLinkFichas'] = $this->oDb->obtenerTabMae1('DE2TMA||OP5TMA', 'COVID19', 'CL1TMA=\'LINK\' AND ESTTMA=\'\'', null, $laResp['cLinkFichas']);
		}
		return $laResp;
	}

	/*
	 *	Retorna true si el paciente tiene registro en la tabla COVID19R
	 */
	public function validarPaciente($tnIngreso)
	{
		$llResp = false;
		$laTabla = $this->oDb
			->count('*','CUENTA')
			->from('COVID19R')
			->where(['INGRES'=>$tnIngreso, 'ANULAD'=>''])
			->getAll('array');
		if (is_array($laTabla)){
			$llResp = $laTabla[0]['CUENTA']>0;
		}
		return $llResp;
	}

	/*
	 *	Retorna true si uno de los diagnósticos corresponde a IRAG
	 */
	public function ValidaDx($taListaCie10)
	{
		$llResp = false;
		if (is_array($taListaCie10)) {
			if (count($taListaCie10)>0) {
				$laTabla = $this->oDb
					->count('*','CUENTA')
					->from('TABMAE')
					->where('TIPTMA=\'COVID19\' AND CL1TMA=\'CIE10\' AND ESTTMA=\'\'')
					->in('CL2TMA',$taListaCie10)
					->getAll('array');
				if (is_array($laTabla)){
					$llResp = $laTabla[0]['CUENTA']>0;
				}
			}
		}
		return $llResp;
	}

	/*
	 *	Guarda Registro que se mostró alerta del paciente
	 */
	public function guardaPaciente($tnIngreso, $tcDxPrincipal, $tcDxOtros, $tcAplica, $tcPrograma)
	{
		$lcUsuCre = isset($_SESSION[HCW_NAME])? $_SESSION[HCW_NAME]->oUsuario->getUsuario(): '';
		$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
		$lcFecCre = $ltAhora->format('Ymd');
		$lcHorCre = $ltAhora->format('His');
		$this->oDb
			->from('COVID19R')
			->insertar([
				'INGRES'=>$tnIngreso,
				'DXPRIN'=>$tcDxPrincipal,
				'DXRELS'=>$tcDxOtros,
				'APLICA'=>$tcAplica,
				'USCCVR'=>$lcUsuCre,
				'PGCCVR'=>$tcPrograma,
				'FECCVR'=>$lcFecCre,
				'HOCCVR'=>$lcHorCre,
			]);
	}

}
