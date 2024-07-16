<?php
namespace NUCLEO;

class Doc_Glucometria
{
	protected $oDb;
	protected $aDocumento = [];
	protected $aReporte = [
				'cTitulo' => "RESULTADO GLUCOMETRIA",
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
		$laEngluco = $this->oDb
			->from('ENGLUCO')
			->where([
				'INGGLU'=>$taData['nIngreso'],
				'CNTGLU'=>$taData['nConsecCita']
			])
			->get('array');
		if ($this->oDb->numRows()>0) {
			$laDocumento['nNotaActual'] = $laEngluco['CONGLU'];
			$laDocumento['nFechaTomado'] = $laEngluco['FDIGLU'];
			$laDocumento['nFechaHoraTomado'] = $laEngluco['FDIGLU'].' '.$laEngluco['HDIGLU'];
			$laDocumento['nValorGluc'] = $laEngluco['MEDGLU'];
			$laDocumento['cSignoGluc'] = $laEngluco['MAYGLU'];
			$laDocumento['cUnidades'] = $laEngluco['UMEGLU'];
			$laDocumento['cAdminInsulina'] = trim($laEngluco['FL1GLU']);
			$laDocumento['nUnidadesInsulina'] = $laEngluco['DOSGLU'];
			$laDocumento['cCodMedicamento'] = trim($laEngluco['REFGLU']);
			$laDocumento['cObservaciones'] = trim($laEngluco['OBSGLU']);
			$laDocumento['cUsuarioCrea'] = trim($laEngluco['USRGLU']);
			$laDocumento['cAdminInsulinaSN'] = trim($laEngluco['FL1GLU'] = 'S' ? 'SI -' : trim($laEngluco['FL1GLU'] = 'N' ? 'NO' : '') );
		} else {
			$laRiahis = $this->oDb
				->from('RIAHIS')
				->where([
					'NROING'=>$taData['nIngreso'],
					'SUBORG'=>$taData['cCUP'],
					'CONCON'=>$taData['nConsecCita']
				])
				->orderBy('CONSEC')
				->getAll('array');
			foreach($laRiahis as $laValor) {
				$lnConsec = $laValor['CONSEC'];
				switch(true) {
					case $lnConsec >=1 && $lnConsec <= 100:
						$laDocumento['cObservaciones'] .= $laValor['DESCRI'];
						break;
					case $lnConsec == 101:
						$laDocumento['cRegMed'] = trim(substr($laValor['DESCRI'], 0, 13));
						$laDocumento['cRegUsuCrea'] = $laDocumento['cRegMed'];
						break;
				}
			}
			$laDocumento['cObservaciones'] = trim($laDocumento['cObservaciones']);
		}
		if (!empty($laDocumento['cUsuarioCrea'])) {
			$laRegMed = $this->oDb
				->from('RIARGMN4')
				->where(['USUARI'=>$laDocumento['cUsuarioCrea']])
				->get('array');
			if ($this->oDb->numRows()>0) {
				$laDocumento['cNombreCrea'] = strtoupper(trim($laRegMed['NOMMED']).' '.trim($laRegMed['NNOMED']));
				$laDocumento['cRegUsuCrea'] = trim($laRegMed['REGMED']);
			}
		} else {
			if (!empty($laDocumento['cRegUsuCrea']))
			{
				$laRegMed = $this->oDb
					->from('RIARGMN5')
					->where(['REGMED'=>$laDocumento['cRegUsuCrea']])
					->getAll('array');
				if ($this->oDb->numRows()>0) {
					$laDocumento['cNombreCrea'] = strtoupper(trim($laRegMed['NOMMED']).' '.trim($laRegMed['NNOMED']));
				}
			}
		}
		$laDocumento['nFecRea'] = $laDocumento['nFechaTomado'];
		if(!empty($laDocumento['cCodMedicamento'])) {
			$laDocumento['cCodMedicamento'] = trim($laDocumento['cCodMedicamento']);
			$laDocumento['cDesMedicamento'] = '';
			if(!empty($laDocumento['cCodMedicamento'])) {
				$laDesMed = $this->oDb
					->from('INVDESL3')
					->where(['REFDES'=>$laDocumento['cCodMedicamento']])
					->get('array');
				if(is_Array($laDesMed)==true) {
					count($laDesMed) > 0 ? $laDocumento['cDesMedicamento'] = trim($laDesMed['DESDES']) : '';
				}
			}
		}
		$laGlucObs = $this->oDb
			->select('A.*, B.NOMMED, B.NNOMED')
			->from('ENGLUOBS AS A')
			->leftJoin('RIARGMN AS B', 'A.USRGLU = B.USUARI', null)
			->where([
				'A.INGGLU'=>$taData['nIngreso'],
				'A.CONGLU'=>$laDocumento['nNotaActual'],
				'A.CNTGLU'=>$taData['nConsecCita']
			])
			->orderBy('A.LINGLU')
			->getAll('array');
		if ($this->oDb->numRows()>0) {
			foreach ($laGlucObs as $laValor) {
				$lnFechaHora = AplicacionFunciones::formatFechaHora('fechahora', $laValor['FECGLU'].$laValor['HORGLU']);
				$laDocumento['cObservaciones'] .= '<br><br>Enfermera/o : '.(trim($laValor['NOMMED']))
												. ' '.(trim($laValor['NNOMED'])).' - '.$lnFechaHora
												. '<br>'.(trim($laValor['OBSGLU']));
			}
		}

		//echo '<pre>'.var_export($laDocumento,true).'</pre>';
		//dd($laDocumento);

		$this->aDocumento = $laDocumento;
	}

	private function prepararInforme($taData)
	{
		$lnAnchoPagina = 90;
		$lcSL = PHP_EOL;
		$cVacios = $this->cTituloVacios;
		$laTr = [];

		if(!empty($this->aDocumento['nValorGluc']))
		{
			$laTr[]=['txthtml10', '<b>Valor Glucometria:</b> '.intval($this->aDocumento['nValorGluc']).' '.$this->aDocumento['cSignoGluc'].' '.$this->aDocumento['cUnidades']];
		}
		if(!empty($this->aDocumento['nFechaHoraTomado']))
		{
			$lnFechaHora = AplicacionFunciones::formatFechaHora('fechahora', $this->aDocumento['nFechaHoraTomado']);
			$laTr[]=['txthtml10', '<b>Fecha/Hora Tomado:</b> '.$lnFechaHora];
		}
		if(!empty($this->aDocumento['cAdminInsulina']) || !empty($this->aDocumento['nUnidadesInsulina']))
		{
			$laTr[]=['txthtml10', '<b>Administra:</b>&nbsp; &nbsp; &nbsp; &nbsp; '.$this->aDocumento['cAdminInsulinaSN'].' '.(empty($this->aDocumento['nUnidadesInsulina'])	? '':' ').' '.(int)($this->aDocumento['nUnidadesInsulina']).' '.'Unidad'.($this->aDocumento['nUnidadesInsulina']=1?'':'es')];
		}
		if(!empty($this->aDocumento['cDesMedicamento']))
		{
			$laTr[]=['txthtml10','<b>Medicamento:</b>&nbsp; &nbsp; &nbsp; &nbsp;'.$this->aDocumento['cDesMedicamento']];
		}
		if(!empty($this->aDocumento['cObservaciones']))
		{
			$laTr[] = ['titulo1', 'Observaciones', 'L'];
			$laTr[] = ['txthtml9', $this->aDocumento['cObservaciones']];
		}
		$laTr[]=['firmas', [
			['registro' => $this->aDocumento['cRegUsuCrea'],'prenombre'=>'']]];
		$this->aReporte['aCuerpo'] = $laTr;
	}

	private function datosBlanco()
	{
		return [
			'nNotaActual' => 0,
			'nFechaTomado' => 0,
			'nFechaHoraTomado' => 0,
			'nValorGluc' => '',
			'cSignoGluc' => '',
			'cAdminInsulina' => '',
			'nUnidadesInsulina' => '',
			'cCodMedicamento' => '',
			'cObservaciones' => '',
			'cUsuarioCrea' => '',
			'cAdminInsulinaSN' => '',
			'cRegMed' => '',
			'cRegUsuCrea' => '',
			'cNombreCrea' => '',
			'nFecRea' => 0,
			'cDesMedicamento' => '',
		];
	}
}