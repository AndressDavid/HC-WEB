<?php
namespace NUCLEO;
require_once __DIR__ .'/class.Diagnostico.php';

class Doc_ProcedimientoGenerico
{
	protected $oDb;
	protected $aDocumento = [];
	protected $aReporte = [
					'cTitulo' => '',
					'lcDesEspMed'=>'',
					'lMostrarFechaRealizado' => true,
					'lMostrarViaCama' => true,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => 'Estudio',
					'cTxtLuegoDeCup' => '',
					'aCuerpo' => [],
					'aFirmas' => [],
					'aNotas' => ['notas'=>true,],
				];
	protected $lTituloVacios=false;
	protected $cTituloVacios='';

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}

	public function retornarDocumento($taData)
	{
		$this->consultarDatos($taData);
		$this->prepararInforme($taData);
		return $this->aReporte;
	}

	private function consultarDatos($taData)
	{
		$laDocumento = $this->datosBlanco();
		$laCondiciones = ['NROING'=>$taData['nIngreso'], 'SUBORG'=>$taData['cCUP'], 'CONCON'=>$taData['nConsecCita']];
		$laRiahis = $this->oDb
							->from('RIAHIS')
							->where($laCondiciones)
							->getAll('array');
		if(is_Array($laRiahis))
		{
			if(count($laRiahis) > 0)
			{
				$lcPrograma = trim($laRiahis[0]["PGMHIS"]);

				$lnMaximoValor = ($lcPrograma == 'RIA022W') ? 900001 : 101 ;


				foreach($laRiahis as $laData)
				{
					switch(true)
					{
						case $laData['CONSEC'] >= 1 && $laData['CONSEC'] < $lnMaximoValor:
							$laDocumento['lcResultado'] .= $laData['DESCRI'];

							break;
						case $laData['CONSEC'] == $lnMaximoValor:
							$laDocumento['lcRegMedico'] = trim(substr($laData['DESCRI'], 0, 13));
							$laDocumento['lcCodDxPrin'] = trim(substr($laData['DESCRI'], 14, 4)) ?? " ";
							$laDocumento['lcCodDxRel']  = trim(substr($laData['DESCRI'], 19, 4))?? " ";
							$laDocumento['lcCodEspMed'] = trim(substr($laData['DESCRI'], 24, 3))?? " ";
							$laDocumento['lcCodigoFinalidad'] = trim(substr($laData['DESCRI'], 30, 2))?? " ";
							$laDocumento['lcTipoCiePrincipal'] = trim(substr($laData['DESCRI'], 36, 1))?? " ";
							$laDocumento['lcAsistencia'] = trim(substr($laData['DESCRI'], 38, 2))?? " ";
							break;
					}
				}
				if(!empty($laDocumento['lcCodEspMed']))
				{
					$laCondiciones = ['CODESP'=>$laDocumento['lcCodEspMed']];
					$lnEspMed = $this->oDb
									->select('DESESP')
									->from('RIAESPE')
									->where($laCondiciones)
									->get('array');
					$laDocumento['lcDesEspMed'] = trim($lnEspMed['DESESP'])?? "";
				}

				if(!empty($laDocumento['lcCodigoFinalidad']))
				{
					$laCondiciones = ['TIPTMA'=>'CODFIN', 'CL1TMA'=>$laDocumento['lcCodigoFinalidad']];
					$lnEspMed = $this->oDb
									->select('DE2TMA')
									->from('TABMAE')
									->where($laCondiciones)
									->get('array');
					$laDocumento['lcDesFinalidad'] = trim($lnEspMed['DE2TMA'])?? "";
				}

				if(trim($laDocumento['lcCodDxPrin'])=='0000')
				{
					$laDocumento['lcCodDxPrin']='';
				}
				if($laDocumento['lcCodDxRel']=='0000')
				{
					$laDocumento['lcCodDxRel'] ='';
				}

				if(!empty($laDocumento['lcTipoCiePrincipal']))
				{
					$lcTipoDiagnostico='B'.$laDocumento['lcTipoCiePrincipal'];
					$laTipoDiagnostico = $this->oDb
					->select('trim(TABDSC) TABDSC')
					->from('PRMTAB')
					->where('TABTIP', '=', 'TDX')
					->where('TABCOD', '=', $lcTipoDiagnostico)
					->get('array');
					if ($this->oDb->numRows()>0){
						$laDocumento['lcDesTipoCiePrincipal'] = trim($laTipoDiagnostico['TABDSC']);
					}
				}
			}
		}
		$this->aDocumento = $laDocumento;
	}

	private function prepararInforme($taData)
	{
		$lnAnchoPagina = 90;
		$lcSL = PHP_EOL;
		$cVacios = $this->cTituloVacios;

		if(!empty($this->aDocumento['lcCodDxPrin']))
		{
			$this->oDiagnostico = new Diagnostico();
			$lnfechaRealizado = substr($taData['tFechaHora'],0,10);
			$lnfechaRealizado = (int)str_replace(array('','-'),'',$lnfechaRealizado);
			$this->oDiagnostico->cargar($this->aDocumento['lcCodDxPrin'], $lnfechaRealizado);
			$this->aDocumento['lcDesDxPrin'] = $this->oDiagnostico->getTexto();
			$laTr['aCuerpo'][]=['saltol', 5];
			$laTr['aCuerpo'][]=['txthtml10','Diagnóstico Principal &nbsp; &nbsp; &nbsp;: ' . $this->aDocumento['lcCodDxPrin'] . ' - ' . $this->aDocumento['lcDesDxPrin']];
		}

		if(!empty($this->aDocumento['lcDesTipoCiePrincipal']))
		{
			$laTr['aCuerpo'][]=['txthtml10','Tipo diagnóstico principal : ' . $this->aDocumento['lcDesTipoCiePrincipal']];
		}

		if(!empty($this->aDocumento['lcCodDxRel']))
		{
			$this->oDiagnostico = new Diagnostico();
			$lnfechaRealizado = substr($taData['tFechaHora'],0,10);
			$lnfechaRealizado = (int)str_replace(array('','-'),'',$lnfechaRealizado);
			$this->oDiagnostico->cargar($this->aDocumento['lcCodDxRel'], $lnfechaRealizado);
			$this->aDocumento['lcDesDxRel'] = $this->oDiagnostico->getTexto();
			$laTr['aCuerpo'][]=['txthtml10','Diagnóstico Relacionado &nbsp; &nbsp;: ' . $this->aDocumento['lcCodDxRel'] . ' - ' . $this->aDocumento['lcDesDxRel']];
		}

		if(!empty($this->aDocumento['lcDesFinalidad']))
		{
			$laTr['aCuerpo'][]=['txthtml10','Finalidad &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;: ' . $this->aDocumento['lcDesFinalidad']];
		}

		if(!empty($this->aDocumento['lcAsistencia']))
		{
			$laTr['aCuerpo'][]=['txthtml10','Asistencia: ' . $this->buscarAsistencia($this->aDocumento['lcAsistencia'])];
			$laTr['aCuerpo'][]=['saltol', 5];
		}

		if(!empty($this->aDocumento['lcResultado']))
		{
			$laTr['aCuerpo'][] = ['titulo1',	'RESULTADOS', 'L'];
			$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['lcResultado']];
		}

		//FIRMA
		if(empty($this->aDocumento['lcCodEspMed']) || $this->aDocumento['lcCodEspMed']== " ")
		{
			$laTr['aCuerpo'][]=['firmas', [
				['registro' => $this->aDocumento['lcRegMedico']]]];
		}else
		{
			$laTr['aCuerpo'][]=['firmas', [
				['registro' => $this->aDocumento['lcRegMedico'], 'codespecialidad' => $this->aDocumento['lcCodEspMed']]]];
		}

		$this->aReporte['cTitulo'] = 'RESULTADO DE EXAMENES' . $lcSL . $this->aDocumento['lcDesEspMed'];
		$this->aReporte = array_merge($this->aReporte, $laTr);
	}

	private function datosBlanco()
	{
		return [
			'lcResultado' => '',
			'lcRegMedico' => '',
			'lcCodDxPrin' => '',
			'lcDesDxPrin' => '',
			'lcCodDxRel'  => '',
			'lcDesDxRel'  => '',
			'lcCodEspMed' => '',
			'lcDesEspMed' => '',
			'lcCodigoFinalidad' => '',
			'lcDesFinalidad' => '',
			'lcTipoCiePrincipal' => '',
			'lcDesTipoCiePrincipal' => '',
		];
	}

	private function buscarAsistencia($tcAsistencia){
		$lcAsistencia = $this->oDb->obtenerTabMae1('TRIM(DE2TMA)', 'PROCORD ', "CL1TMA='REHCAR' AND CL2TMA='ASISTE' AND CL3TMA='".$tcAsistencia."'", null, "");

		return $lcAsistencia;
	}
}