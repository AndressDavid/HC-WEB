<?php
namespace NUCLEO;

class Interpretacion
{
	protected $aRetorno = [
		'Mensaje' => "",
		'Ruta' => "",
		'Acceso' => "",
	];

	public function __construct($lcCups='')
	{
		global $goDb;
		$this->oDb = $goDb;
    }

	// Verifica el tipo de interpretación 
	public function VerificarTipoInterpreta($tcCup='', $tnIngreso=0, $tcEstado='', $tnConCit=0, $tcCodEsp='')
	{
		$lcRutaServidorLab = '';
		if(!empty($tcCup)){
			$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'BANSAN', ['CL1TMA'=>'EXCCUPS', 'CL2TMA'=>$tcCup, 'ESTTMA'=>'']);
			$llExcluyeBancoSangre = trim(AplicacionFunciones::getValue($oTabmae,'DE2TMA',''))==''?false:true;
			$lcEspecialidad = $tcCodEsp ;
			
			if($llExcluyeBancoSangre){
				$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'BANSAN', ['CL1TMA'=>'ESPBANC', 'ESTTMA'=>'']);
				$lcEspecialidadBancoSangre = trim(AplicacionFunciones::getValue($oTabmae,'DE2TMA',''),' ');
				$lcEspecialidad = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getEspecialidad():'');
				if($lcEspecialidadBancoSangre == $lcEspecialidad){
					$this->aRetorno = [
						'Mensaje'=>'',
						'Ruta'=>'LIBROHC',
						'Acceso'=>'',
					];
				}
				if(!empty($this->aRetorno['Ruta'])) {
					return $this->aRetorno;
				}
			}
			
			$lcListaGlucoGases = $this->fCargarLista('GLUCGAS');
			if(!empty($lcListaGlucoGases)){
				$laListaGlucoGases = explode(',', str_replace("'","",$lcListaGlucoGases));
			}
			$lcListaGlucometrias = $this->fCargarLista('GLUCOME');
			$lcListaGlucometrias = str_replace("'","",$lcListaGlucometrias);
			
			if(!empty($lcListaGlucometrias)){
				$laListaGlucometrias = explode(',',str_replace(" ","",$lcListaGlucometrias));
			}
					
			if($lcEspecialidad == '353' && !in_array($tcCup, $laListaGlucoGases)){
				$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'LIBROHC', ['CL1TMA'=>'LABORATO', 'CL2TMA'=>'SERVER', 'ESTTMA'=>'']);
				$lcRutaServidorLab = trim(AplicacionFunciones::getValue($oTabmae,'DE2TMA',''),' ');
				$this->aRetorno = [
					'Mensaje'=>'',
					'Ruta'=> $lcRutaServidorLab,
					'Acceso'=>'',
				];
			}
		}
		
		if(!empty($this->aRetorno['Ruta'])) {
			return $this->aRetorno;
		}
		$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'AGFASO', ['CL1TMA'=>'10', 'ESTTMA'=>'']);
		$lcListaEsp = trim(AplicacionFunciones::getValue($oTabmae,'DE2TMA',''),' ');

		if(!empty($lcListaEsp)){
			$laListaEsp = explode(',',str_replace(" ","",$lcListaEsp));
		}
		$lcServicios = '';
		if(in_array($lcEspecialidad, $laListaEsp)){
			$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'AGFASO', ['CL1TMA'=>'11', 'CL2TMA'=>'2', 'ESTTMA'=>'']);
			$lcServicios = trim(AplicacionFunciones::getValue($oTabmae,'DE2TMA',''),' ');
			if(!empty($lcServicios)){
				$laServicios = explode(',',str_replace(" ","",$lcServicios));
			}
			if(in_array($tcEstado, $laServicios)){
				$lcAcceso = '';
				if($lcEspecialidad=='124' && ($tcEstado=='3' || $tcEstado=='59')){
					$this->oDb->in('TIPAGF',['ORU^R01^OR']);
				} else{
					$this->oDb->in('TIPAGF',['ORU^R01^OR', 'ORM^O01^OR']);
				}

				$laTemp = $this->oDb
					->select('ANUAGF')
						->from('ARCAGFAL04')
						->where(['INGAGF'=> $tnIngreso, 'CCIAGF'=> $tnConCit, 'TXTAGF'=> 'Satisfactorio.'])
						->get('array');
				if($this->oDb->numRows()==0){
					$this->oDb->in('TIPAGF',['ORU^R01^OR', 'ORM^O01^OR']);
					$laTemp = $this->oDb
						->select('ANUAGF')
							->from('ARCAGFAL04')
							->where(['INGAGF'=> $tnIngreso, 'CCIAGF'=> $tnConCit, 'TXTAGF'=> 'Satisfactorio.'])
							->where('ANUAGF', '>', 0)
							->get('array');
					if($this->oDb->numRows()>0){
						$lcAcceso =	$laTemp['ANUAGF'];
					}
				}else{
					$lcAcceso =	$laTemp['ANUAGF'];
				}

				if(!empty($lcAcceso)){
					$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'AGFASO', ['CL1TMA'=>'11', 'CL2TMA'=>'1', 'ESTTMA'=>'']);
					$lcRutaServidorLab = trim(AplicacionFunciones::getValue($oTabmae,'DE2TMA',''),' ');
					$this->aRetorno = [
						'Mensaje'=>'Esta imágen/reporte se visualizara el Agility',
						'Ruta'=> $lcRutaServidorLab,
						'Acceso'=>$lcAcceso,
					];
				}
			}
		}

		if(empty(trim($this->aRetorno['Ruta']))) {
			$this->aRetorno = [
				'Mensaje'=>'',
				'Ruta'=>'LIBROHC',
				'Acceso'=>'',
			];
			
		}
		return $this->aRetorno;
	}
	
	function fCargarLista($tcTipo='')
	{
		$lcLista='';
		if(!empty($tcTipo)){
			$lcLista = $this->oDb->obtenerTabMae1('DE2TMA', 'FORMEDIC', ['CL1TMA'=>$tcTipo, 'ESTTMA'=>''], null, '');
		}
		return $lcLista;
	}
}
