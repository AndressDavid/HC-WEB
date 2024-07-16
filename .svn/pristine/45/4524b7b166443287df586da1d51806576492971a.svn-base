<?php
namespace NUCLEO;
require_once __DIR__ .'/class.Plan.php';
require_once __DIR__ .'/class.TipoAfiliado.php';

class Doc_Triage
{
	protected $oDb;
	protected $aDocumento = [];
	protected $aReporte = [
					'cTitulo' => 'REGISTRO DE ATENCIÓN TRIAGE',
					'lMostrarEncabezado' => true,
					'lMostrarFechaRealizado' => false,
					'lMostrarViaCama' => false,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => '',
					'cTxtLuegoDeCup' => '',
					'aCuerpo' => [],
					'aNotas' => ['notas'=>false,],
				];
	protected $lTituloVacios=false;
	protected $cTituloVacios='';


	public function __construct()
    {
		global $goDb;
		$this->oDb = $goDb;

		$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'DATING', ['CL1TMA'=>'TRIAGE', 'CL2TMA'=>'01200201', 'CL3TMA'=>'REPORTE']);
		$this->lTituloVacios = 'SI'==trim(AplicacionFunciones::getValue($oTabmae, 'DE2TMA', 'NO'));

		if ($this->lTituloVacios) {
			$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'DATING', ['CL1TMA'=>'TRIAGE', 'CL2TMA'=>'01200201', 'CL3TMA'=>'TEXTO']);
			$this->cTituloVacios = trim(AplicacionFunciones::getValue($oTabmae, 'DE2TMA', 'Ausente'));
		}
    }


	/*
	 *	Retornar array con los datos del documento
	 */
	public function retornarDocumento($taData)
	{
		$this->consultarDatos($taData);
		$this->prepararInforme($taData);

		return $this->aReporte;
	}


	/*
	 *	Consulta los datos del documento desde la BD en el array $aDocumento
	 */
	private function consultarDatos($taData)
	{
		$laTriage = $this->oDb
			->select('CNSTRI, FACTRI, HACTRI, FECTRI, HORTRI, PRCTRI, USRTRI')
			->from('TRIAGU')
			->where([
				'NIGTRI'=>$taData['nIngreso'],
				'CNSTRI'=>$taData['nConsecDoc'],
			])
			->get('array');
		$laDoc = array_merge($this->datosBlanco(), $laTriage);

		$laTriage = $this->oDb
			->select('INDHTR, CLNHTR, DESHTR')
			->from('HISTRI')
			->where([
				'INGHTR'=>$taData['nIngreso'],
				'CTRHTR'=>$taData['nConsecDoc'],
			])
			->orderBy('INDHTR, CLNHTR')
			->getAll('array');

		foreach($laTriage as $laData) {

			switch ($laData['INDHTR']) {

				// Enfermedad profesional, Accidente de trabajo y motivo del consulta
				case '1':
					$laDoc['cEnfermedadProf'] = trim(substr($laData['DESHTR'],0,5));
					$laDoc['cAccidenteTrab'] = trim(substr($laData['DESHTR'],5,5));
					$laDoc['cMotivoConsulta'] = trim(substr($laData['DESHTR'],10,5));
					$laDoc['nTriage'] = (int) trim(substr($laData['DESHTR'],15,5));
					break;

				// Plan entidad, Tipo afiliado
				case '5':
					$laDoc['cPlanEntidad'] = trim(substr($laData['DESHTR'],0,10));
					$laDoc['cTipoAfiliado'] = trim(substr($laData['DESHTR'],10,10));
					$laDoc['cPlanEntidadDsc'] = (new Plan($laDoc['cPlanEntidad']))->cDescripcion;
					$laDoc['cTipoAfiliadoDsc'] = (new TipoAfiliado($laDoc['cTipoAfiliado']))->cDescripcion;
					break;

				// Enfermedad actual
				case '10':
					$laDoc['cEnfermedadActual'] .= $laData['DESHTR'];
					break;

				// Revision por sistemas
				case '25':
					$laDoc['cRevisionSistema'] .= $laData['DESHTR'];
					break;

				// Nivel de conciencia
				case '40':
					$laDoc['cNivelConciencia'] = trim(substr($laData['DESHTR'],0,10));
					$laDoc['cAperturaOcular'] = trim(substr($laData['DESHTR'],10,10));
					$laDoc['cRespuestaVerbal'] = trim(substr($laData['DESHTR'],20,10));
					$laDoc['cRespuestaMotora'] = trim(substr($laData['DESHTR'],30,10));
					$laDoc['nEscalaGlasgow'] = trim(substr($laData['DESHTR'],40,10));
					break;

				// Signos Vitales
				case '45':
					$laDoc['nPASistolica'] = intval(trim(substr($laData['DESHTR'],0,10)));
					$laDoc['nPADiastolica'] = intval(trim(substr($laData['DESHTR'],10,10)));
					$laDoc['nFCardiaca'] = intval(trim(substr($laData['DESHTR'],20,10)));
					$laDoc['nFRespiratoria'] = intval(trim(substr($laData['DESHTR'],30,10)));
					$laDoc['nTemperatura'] = floatval(trim(substr($laData['DESHTR'],40,10)));
					$laDoc['nEscalaDolor'] = intval(trim(substr($laData['DESHTR'],50,10)));
					$laDoc['nSaturacion'] = intval(trim(substr($laData['DESHTR'],60,10)));
					break;

				// Clasificacion triage
				case '50':
					$laDoc['cClasificacion'] = trim($laData['DESHTR']);
					break;

				// Observaciones
				case '55':
					$laDoc['cObservaciones'] .= $laData['DESHTR'];
					break;

				// Grupo Sanguineo RH
				case '56':
					$laDoc['cGrupoSangreRH'] = trim(substr($laData['DESHTR'],20,10));
					break;

				// Presenta Alergias
				case '57':
					$laDoc['cAlergias'] .= $laData['DESHTR'];
					break;
				
				case '58':
					$laDoc['cFiebreCuantificada'] .= trim(substr($laData['DESHTR'],0,2));
					$laDoc['cTos'] .= trim(substr($laData['DESHTR'],3,2));
					$laDoc['cAdinamia'] .= trim(substr($laData['DESHTR'],6,2));
					$laDoc['cOdinofagia'] .= trim(substr($laData['DESHTR'],9,2));
					$laDoc['cExantema'] .= trim(substr($laData['DESHTR'],12,2));
					break;

				case '59':
					$laDoc['cPuntajeCovid19'] .= trim(substr($laData['DESHTR'],0,2));
					$laDoc['cAlertaCovid19Guardado'] .= trim(substr($laData['DESHTR'],3,215));
					break;

				case '60':
					$laDoc['cViruelaSimica'] .= trim(substr($laData['DESHTR'],0,2));
					$laDoc['cAlertaViruelaSimica'] .= trim(substr($laData['DESHTR'],3,215));
					break;
				
				// Paciente sin ingreso
				case '90':
					$laDoc['cObsSinIngreso'] .= $laData['DESHTR'];
					break;
			}
		}
		$laDoc['cEnfermedadActual'] = trim($laDoc['cEnfermedadActual']);
		$laDoc['cRevisionSistema'] = trim($laDoc['cRevisionSistema']);
		$laDoc['cObservaciones'] = trim($laDoc['cObservaciones']);
		$laDoc['cObsSinIngreso'] = trim($laDoc['cObsSinIngreso']);

		$this->aDocumento = $laDoc;
	}


	/*
	 *	Prepara array $aReporte con los datos para imprimir
	 */
	private function prepararInforme($taData)
	{
		$lnAnchoPagina = 90;
		$lcSL = PHP_EOL;
		$cVacios = $this->cTituloVacios;

		$oTabmae = $this->oDb->ObtenerTabMae('DE1TMA', 'CODCEX', ['CL1TMA'=>$this->aDocumento['cMotivoConsulta']]);
		$cMotivoConsulta = trim(AplicacionFunciones::getValue($oTabmae, 'DE1TMA', ''));

		$oTabmae = $this->oDb->ObtenerTabMae('DE1TMA', 'CODNCO', ['CL1TMA'=>$this->aDocumento['cNivelConciencia']]);
		$cNivelConciencia = trim(AplicacionFunciones::getValue($oTabmae, 'DE1TMA', ''));

		
		// Encabezado
		$this->aReporte['cTxtLuegoDeCup'] =
				  str_pad('Fecha Nacimiento: '.AplicacionFunciones::formatFechaHora('fecha', $taData['oIngrPaciente']->oPaciente->nNacio), $lnAnchoPagina/2, ' ').$lcSL
				. str_pad('Llegada         : '.AplicacionFunciones::formatFechaHora('fechahora', $this->aDocumento['FACTRI'].' '.$this->aDocumento['HACTRI']), $lnAnchoPagina/2, ' ')
					.'Realizado    : '.AplicacionFunciones::formatFechaHora('fechahora', $this->aDocumento['FECTRI'].$this->aDocumento['HORTRI']).$lcSL
				. str_pad('Clasificación   : Triage '.$this->aDocumento['PRCTRI'], $lnAnchoPagina/2, ' ').' '
					.'Valoración No: '.$this->aDocumento['nTriage'].', Consecutivo '.$this->aDocumento['CNSTRI'].$lcSL;

		// Cuerpo
		$laTr[] = ['titulo1', 'Causa externa'];
		$laTr[] = ['texto9',  $cMotivoConsulta];
		$laTr[] = ['titulo1', 'Enfermedad Actual / Motivo de Consulta'];
		$laTr[] = ['texto9',  $this->aDocumento['cEnfermedadActual']];
		if (!empty($this->aDocumento['cAlergias'])) {
			$laTr[] = ['titulo1', 'Alergias'];
			$laTr[] = ['texto9', trim($this->aDocumento['cAlergias'])];
		}
		$laTr[] = ['titulo1', 'Examen físico'];
		$laTr[] = ['titulo2', 'Hallazgos'];
		$laTabla = [
			['w'=>[40,100], 'd'=>['NIVEL DE CONCIENCIA', $cNivelConciencia]],
			['w'=>[40,100], 'd'=>['ESCALA GLASGOW', $this->aDocumento['nEscalaGlasgow'].'/15']],
			['w'=>60, 'd'=>['PRESIÓN ARTERIAL', 'FRECUENCIA /Min', 'OTROS']],
			['w'=>30,
				'd'=> [ '- Sistólica', $this->aDocumento['nPASistolica']>0 ? $this->aDocumento['nPASistolica'] : $cVacios,
						'- Cardíaca', $this->aDocumento['nFCardiaca']>0 ? $this->aDocumento['nFCardiaca'] : $cVacios,
						'- Temperatura', $this->aDocumento['nTemperatura']>0 ? number_format($this->aDocumento['nTemperatura']+0.0, 2, ',', '.').'°C' : $cVacios]],
		];
		if ($this->aDocumento['nSaturacion']>0) {
			$laTabla[] = [
				'w'=>30,
				'd'=> [ '- Diastólica', $this->aDocumento['nPADiastolica']>0 ? $this->aDocumento['nPADiastolica'] : $cVacios,
						'- Respiratoria', $this->aDocumento['nFRespiratoria']>0 ? $this->aDocumento['nFRespiratoria'] : $cVacios,
						'- Saturación', $this->aDocumento['nSaturacion'].'%']
			];
			$laTabla[] = [			
				'w'=>30,
				'd'=> [ '', '', '', '',
						'- Escala dolor', $this->aDocumento['nEscalaDolor']>0 ? $this->aDocumento['nEscalaDolor'] : $cVacios]
			];
		} else {
			$laTabla[] = [
				'w'=>30,
				'd'=> [ '- Diastólica', $this->aDocumento['nPADiastolica']>0 ? $this->aDocumento['nPADiastolica'] : $cVacios,
						'- Respiratoria', $this->aDocumento['nFRespiratoria']>0 ? $this->aDocumento['nFRespiratoria'] : $cVacios,
						'- Escala dolor', $this->aDocumento['nEscalaDolor']>0 ? $this->aDocumento['nEscalaDolor'] : $cVacios]
			];
		}
		$laTr[] = ['tablaSL', [], $laTabla, ];
		if (!empty($this->aDocumento['cRevisionSistema'])) {
			$laTr[] = ['titulo2', 'Revisión por sistema'];
			$laTr[] = ['texto9', $this->aDocumento['cRevisionSistema']];
		}
		if (!empty($this->aDocumento['cObservaciones'])) {
			$laTr[] = ['titulo1', 'Observaciones'];
			$laTr[] = ['texto9', $this->aDocumento['cObservaciones']];
		}
		$laTr[] = ['titulo1', 'Clasificación'];
		$laTr[] = ['texto9', 'Triage ' . $this->aDocumento['PRCTRI']];

		// Paciente sin ingreso
		if (!empty($this->aDocumento['cObsSinIngreso'])) {
			$laTr[] = ['titulo1', 'Paciente SIN INGRESO'];
			$laTr[] = ['texto9', $this->aDocumento['cObsSinIngreso']];
		}

		// Entidad
		if (!empty($this->aDocumento['cPlanEntidadDsc'])) {
			$laTr[] = ['titulo1', 'Entidad'];
			$laTr[] = ['texto9', $this->aDocumento['cPlanEntidadDsc']];
		}
		
		if (!empty($this->aDocumento['cTipoAfiliadoDsc'])) {
			$laTr[] = ['titulo1', 'Tipo afiliado'];
			$laTr[] = ['texto9', $this->aDocumento['cTipoAfiliadoDsc']];
		}

		if (!empty($this->aDocumento['cPuntajeCovid19'])) {
			$laTr[] = ['titulo1', 'Escala de riesgo para precauciones en ambiente hospitalario'];

			if (!empty($this->aDocumento['cFiebreCuantificada'])) {
				$laTr[] = ['texto9', 'a. ¿Ha presentado fiebre cuantificada en los últimos 14 días? : '. $this->aDocumento['cFiebreCuantificada']];
			}	

			if (!empty($this->aDocumento['cTos'])) {
				$laTr[] = ['texto9', 'b. ¿Ha presentado tos en los últimos 14 días? : '. $this->aDocumento['cTos']];
			}	
			
			if (!empty($this->aDocumento['cAdinamia'])) {
				$laTr[] = ['texto9', 'c. ¿Ha presentado adinamia en los últimos 14 días? '. $this->aDocumento['cAdinamia']];
			}	
			
			if (!empty($this->aDocumento['cOdinofagia'])) {
				$laTr[] = ['texto9', 'd. ¿Ha presentado odinofagia en los últimos 14 días? : '. $this->aDocumento['cOdinofagia']];
			}	
			
			if (!empty($this->aDocumento['cExantema'])) {
				$laTr[] = ['texto9', 'e. ¿Ha presentado astenia en los últimos 14 días? : '. $this->aDocumento['cExantema']];
			}	
			$laTr[] = ['texto9', 'Total puntaje: '. $this->aDocumento['cPuntajeCovid19']];
			$laTr[] = ['texto9', $this->aDocumento['cAlertaCovid19Guardado']];
		}

		if ($this->aDocumento['cViruelaSimica']=='Si') {
			$laTr[] = ['titulo1', 'Sospecha viruela SIMICA'];
			$laTr[] = ['texto9', '¿Presenta lesiones o erupciones en la piel? : '. $this->aDocumento['cViruelaSimica']];
			$laTr[] = ['texto9', $this->aDocumento['cAlertaViruelaSimica']];
		}

		// Escala ROSIER 
		$laTempEscala = $this->oDb
			->select('DETROS DESCRIP, PUNROS PUNTOS')
			->from('ENROSI')
			->where([
				'INGROS'=>$taData['nIngreso'],
				'NOTROS'=>0,
			])
			->get('array');

		if(is_array($laTempEscala)){
			if(count($laTempEscala)>0){
				$laTr[] = ['titulo1', 'ESCALA ROSIER'];
				$lnPuntos = $laTempEscala['PUNTOS'];
				$laDatos = explode('¤',$laTempEscala['DESCRIP']);
				foreach($laDatos as $laPregunta){
					
					$laDetalle = explode(':',$laPregunta);

					switch (true){
						case $laDetalle[0]=='01' :
							$laTr[] = ['texto9', '1. ¿ Pérdida de conciencia o síncope ?          :'. $laDetalle[1]];
							break;
						case$laDetalle[0]=='02' :
							$laTr[] = ['texto9', '2. ¿ Presencia de convulsión ?                  :'. $laDetalle[1]];
							break;
						case $laDetalle[0]=='03' :
							$laTr[] = ['texto9', '3. ¿ Parálisis facial asimetrica ?              :'. $laDetalle[1]];
							break;
						case $laDetalle[0]=='04' :
							$laTr[] = ['texto9', '4. ¿ Parálisis braquial asimetrica ?            :'. $laDetalle[1]];
							break;
						case $laDetalle[0]=='05' :
							$laTr[] = ['texto9', '5. ¿ Parálisis miembros inferiores asimetrica ? :'. $laDetalle[1]];
							break;
						case $laDetalle[0]=='06' :
							$laTr[] = ['texto9', '6. ¿ Trastorno del habla ?                      :'. $laDetalle[1]];
							break;
						case $laDetalle[0]=='07' :
							$laTr[] = ['texto9', '7. ¿ Defecto de campo visual ?                  :'. $laDetalle[1]];
							break;
					}
				}
				$laTr[] = ['texto9', 'Total puntaje: '. $lnPuntos];

			}
		}
		
		// Firma
		$laTr[] = ['firmas', [ ['prenombre'=>'', 'usuario' => $this->aDocumento['USRTRI'],],], ];

		$this->aReporte['aCuerpo'] = $laTr;
	}

	/*
	 *	Array de datos de documento vacío
	 */
	private function datosBlanco()
	{
		return [
			'cEnfermedadProf'	=> '',
			'cAccidenteTrab'	=> '',
			'cMotivoConsulta'	=> '',
			'nTriage'			=> 0,
			'cPlanEntidad'		=> '',
			'cPlanEntidadDsc'	=> '',
			'cTipoAfiliado'		=> '',
			'cTipoAfiliadoDsc'	=> '',
			'cEnfermedadActual'	=> '',
			'cRevisionSistema'	=> '',
			'cNivelConciencia'	=> '',
			'cAperturaOcular'	=> '',
			'cRespuestaVerbal'	=> '',
			'cRespuestaMotora'	=> '',
			'nEscalaGlasgow'	=> 0,
			'nPASistolica'		=> 0,
			'nPADiastolica'		=> 0,
			'nFCardiaca'		=> 0,
			'nFRespiratoria'	=> 0,
			'nTemperatura'		=> 0,
			'nSaturacion'		=> 0,
			'nEscalaDolor'		=> 0,
			'cClasificacion'	=> '',
			'cObservaciones'	=> '',
			'cGrupoSangreRH'	=> '',
			'cAlergias'			=> '',
			'cObsSinIngreso'	=> '',
			'cFiebreCuantificada'	=> '',
			'cTos'				=> '',
			'cAdinamia'			=> '',
			'cOdinofagia'		=> '',
			'cExantema'			=> '',
			'cPuntajeCovid19'	=> '',
			'cAlertaCovid19Guardado' => '',
			'cViruelaSimica' 	=> '',
			'cAlertaViruelaSimica'=> '',
			];
	}
}