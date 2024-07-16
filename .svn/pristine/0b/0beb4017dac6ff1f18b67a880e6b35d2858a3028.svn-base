<?php
namespace NUCLEO;
require_once __DIR__ .'/class.Diagnostico.php';
require_once __DIR__ .'/class.AplicacionFunciones.php';

class Doc_Hemocomponentes
{
	protected $oDb;
	protected $aReporte = [
					'cTitulo' => 'HEMOCOMPONENTE',
					'lMostrarFechaRealizado' => true,
					'lMostrarViaCama' => true,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => '',
					'cTituloCup' => '',
					'cTxtLuegoDeCup' => '',
					'aCuerpo' => [],
					'aFirmas' => [],
					'aNotas' => ['notas'=>false],
				];

	public function __construct()
    {
		global $goDb;
		$this->oDb = $goDb;
    }

	 //Retornar array con los datos del documento
	public function retornarDocumento($taData)
	{
		$this->consultarDatos($taData);
		return $this->aReporte;
	}

	//	Consulta los datos del documento desde la BD en el array $aDocumento
	 private function consultarDatos($taData)
	{
		$this->laTr = [];
		$oTabmae = $this->oDb->ObtenerTabMae('CL4TMA', 'BANSAN', ['CL1TMA'=>'APLICA', 'CL2TMA'=>$taData['cCUP'], 'ESTTMA'=>' ']);
		$lcGrupoHemocomponente=trim(AplicacionFunciones::getValue($oTabmae,'CL4TMA',''),' ');

		$this->aPrmTab = $this->oDb
			->select("CL1TMA, CL2TMA, DE2TMA, TIPTMA, TRIM(CL1TMA)||TRIM(CL2TMA) AS CODIGO")
			->from('TABMAE')
			->where('TIPTMA','=','BANSAN')
			->in('CL1TMA', ['NOTRANS','ASPECTO','COOMBS','SERVICIO','TIPODEV'])
			->getAll('array');
		foreach($this->aPrmTab as $laPar) {
			$laPar = array_map('trim', $laPar);
			$this->aListaParametros[$laPar['CL1TMA']][$laPar['CL2TMA']] = $laPar['DE2TMA'];
		}
		
		$laObtenerOrden = $this->oDb
		->from('BANSACL01')
		->where([
			'INGBSC'=>$taData['nIngreso'],
			'CCIBSC'=>$taData['nConsecCita'],
			'INDBSC'=>1,
		])
		->getAll('array');
		if (is_array($laObtenerOrden)) { if (count($laObtenerOrden)>0) { $lcConsecutivoOrden = trim($laObtenerOrden[0]['CORBSC'],' ');	}}
			$laTr=$this->fnInsertarCabecera($taData['cCUP'],$taData['oCup']->cDscrCup);
			$laTr=array_merge($laTr,  $this->fnDatosOrdenHemocomponente($taData['nIngreso'], $lcConsecutivoOrden, $lcGrupoHemocomponente));
			$laTr=array_merge($laTr,  $this->fnDatosCitaHemocomponente($taData['nIngreso'], $lcConsecutivoOrden, $taData['nConsecCita']));
			$laTr=array_merge($laTr,  $this->fnDatosListaChequeo($taData['nIngreso'], $lcConsecutivoOrden, $taData['nConsecCita']));
			$laTr=array_merge($laTr,  $this->fnComponentesLista($taData['nIngreso'], $lcConsecutivoOrden, $taData['nConsecCita']));
			$laTr=array_merge($laTr,  $this->fnDatosTransfucionMedico($taData['nIngreso'], $lcConsecutivoOrden, $taData['nConsecCita']));
			$laTr=array_merge($laTr,  $this->fnDatosSignosVitales($taData['nIngreso'], $lcConsecutivoOrden, $taData['nConsecCita']));
			$laTr=array_merge($laTr,  $this->fnDevolucion($taData['nIngreso'], $lcConsecutivoOrden, $taData['nConsecCita']));
			$laTr=array_merge($laTr,  $this->fnNoTransfunde($taData['nIngreso'], $lcConsecutivoOrden, $taData['nConsecCita']));
			$this->aReporte['aCuerpo'] = array_merge($this->aReporte['aCuerpo'], $laTr);
	}

	private function fnInsertarCabecera($tcCups,$tcDescripcionCups)
	{
		$laRetorno = [];
		$laRetorno[] = ['titulo2',	'Solicitud hemocomponente'];
		$laRetorno[] = ['titulo3',	'Procedimiento solicitado:'];	
		$laRetorno[] = ['txthtml9',$tcCups .'-'.$tcDescripcionCups];			
		return $laRetorno;
	}
	
	private function fnNoTransfunde($tnIngreso, $tnConsOrden, $tnConsCita)
	{
		$laRetorno = [];
		$lcReturn = '';
		$laNoTransfunde = $this->oDb
		->select('INDBSC AS INDICE, DESBSC AS DESCRIPCION, USRBSC AS USUARIO_REALIZA, FECBSC AS FECHA_REALIZA, HORBSC AS HORA_REALIZA')
		->from('BANSAC')
		->where(['INGBSC'=>$tnIngreso, 'CORBSC'=>$tnConsOrden, 'CCIBSC'=>$tnConsCita, 'INDBSC'=>21,])
		->orderBy('INDBSC, LINBSC')
		->getAll('array');
		if(is_array($laNoTransfunde)){ 	if(count($laNoTransfunde)>0){
			foreach($laNoTransfunde as $laNoTransfundeIng){
				$lcCausaNoTransfunde = trim(mb_substr($laNoTransfundeIng['DESCRIPCION'], 10, 3));
				
				if (!empty(trim($lcCausaNoTransfunde))){
					$laRetorno[] = ['titulo2',	'Causa no transfunde:'];
					$lcCausaNoTransfunde = !empty($lcCausaNoTransfunde)? $this->aListaParametros['NOTRANS'][$lcCausaNoTransfunde] :'';
					$laRetorno[] = ['texto9',	$lcCausaNoTransfunde];	
				}		
			}	
			$lcMedicoRealiza = $this->fnUsuarioRealiza(trim($laNoTransfundeIng['USUARIO_REALIZA']),$laNoTransfundeIng['FECHA_REALIZA'],$laNoTransfundeIng['HORA_REALIZA']);
			if(!empty($lcMedicoRealiza)){ $laRetorno[] = ['titulo3', 'Realiza cancelación: ' .$lcMedicoRealiza]; }			
		}}
		return $laRetorno;	
	}
	
	private function fnDevolucion($tnIngreso, $tnConsOrden, $tnConsCita)
	{
		$laRetorno = [];
		$lcReturn = '';
		$laDevolucion = $this->oDb
		->select('INDBSC AS INDICE, DESBSC AS DESCRIPCION, USRBSC AS USUARIO_REALIZA, FECBSC AS FECHA_REALIZA, HORBSC AS HORA_REALIZA')
		->from('BANSAC')
		->where(['INGBSC'=>$tnIngreso, 'CORBSC'=>$tnConsOrden, 'CCIBSC'=>$tnConsCita, 'INDBSC'=>19,])
		->orderBy('INDBSC, LINBSC')
		->getAll('array');
		if(is_array($laDevolucion)){ 	if(count($laDevolucion)>0){
			foreach($laDevolucion as $laDevolucionIng){
				$lnTemperaturaDevolucion = trim(mb_substr($laDevolucionIng['DESCRIPCION'], 0, 2));
				$lcAspectoDevolucion = trim(mb_substr($laDevolucionIng['DESCRIPCION'], 10, 3));
				$lcTipodevolucion = trim(mb_substr($laDevolucionIng['DESCRIPCION'], 15, 1));
				$lcCausaDescruce = trim(mb_substr($laDevolucionIng['DESCRIPCION'], 20, 3));
				
				if (!empty(trim($lnTemperaturaDevolucion)) || !empty(trim($lcAspectoDevolucion)) || !empty(trim($lcTipodevolucion)) || !empty(trim($lcCausaDescruce)) 
				){
					$laRetorno[] = ['titulo2',	'Devolución:'];

					if (!empty(trim($lnTemperaturaDevolucion)) || !empty(trim($lcAspectoDevolucion))) {
						$lcDetalle='';
						$oTabmae=$this->oDb->obtenerTabMae('DE2TMA', 'BANTEMP', "CL1TMA='$lnTemperaturaDevolucion'");
						$lcDescrcTemperaturaDevolucion=trim(AplicacionFunciones::getValue($oTabmae,'DE2TMA',''),' ');
						$lcAspectoDevolucion = !empty($lcAspectoDevolucion)? $this->aListaParametros['ASPECTO'][$lcAspectoDevolucion] :'';
						
						$lcDetalle = ( empty($lcDescrcTemperaturaDevolucion)? '': AplicacionFunciones::mb_str_pad('Temperatura devolución: '.$lcDescrcTemperaturaDevolucion, 50, ' ') )
											.( empty($lcAspectoDevolucion)? '': AplicacionFunciones::mb_str_pad('Aspecto devolución: '.$lcAspectoDevolucion, 50) );
						$laRetorno[] = ['texto9',	$lcDetalle];		
					}
					
					if (!empty(trim($lcTipodevolucion))){
						$lcTipodevolucion = !empty($lcTipodevolucion)? $this->aListaParametros['TIPODEV'][$lcTipodevolucion] :'';
						$laRetorno[] = ['texto9',	'Tipo devolución: ' .$lcTipodevolucion];	
					}
					
					if (!empty(trim($lcCausaDescruce))){
						$lcCausaDescruce = !empty($lcCausaDescruce)? $this->aListaParametros['NOTRANS'][$lcCausaDescruce] :'';
						$laRetorno[] = ['texto9',	'Causa drescruce: ' .$lcCausaDescruce];	
					}
				}	
			}
			$lcMedicoRealiza = $this->fnUsuarioRealiza(trim($laDevolucionIng['USUARIO_REALIZA']),$laDevolucionIng['FECHA_REALIZA'],$laDevolucionIng['HORA_REALIZA']);
			if(!empty($lcMedicoRealiza)){ $laRetorno[] = ['titulo3', 'Realiza devolución: ' .$lcMedicoRealiza]; }			
		}}
		return $laRetorno;	
	}
	
	private function fnDatosListaChequeo($tnIngreso, $tnConsOrden, $tnConsCita)
	{
		$laRetorno = [];
		$lcReturn = '';
		
		$laListaChequeo = $this->oDb
		->select('trim(DESBSC) AS DESCRIPCION')
		->from('BANSAC')
		->where(['INGBSC'=>$tnIngreso, 'CORBSC'=>$tnConsOrden, 'CCIBSC'=>$tnConsCita, 'INDBSC'=>13,])
		->orderBy('INDBSC, LINBSC')
		->getAll('array');
		if(is_array($laListaChequeo)){ 	if(count($laListaChequeo)>0){
			foreach($laListaChequeo as $laListaChequeo){
				$lcDatosListaChequeo = trim($laListaChequeo['DESCRIPCION']);
			}	
		}}
				
		if (!empty(trim($lcDatosListaChequeo))){
			$laRetorno[] = ['titulo2',	'Lista de chequeo'];	
			$aChequeo = explode(",", $lcDatosListaChequeo);
			
			foreach($aChequeo as $laDataLista) {
				$lcItemLista = AplicacionFunciones::mb_str_pad($laDataLista, 3, '0', STR_PAD_LEFT);
				
				$this->aListaChequeo = $this->oDb
				->select('trim(DE2TMA) AS DESCRIPCION')
				->from('TABMAE')
				->where([
					'TIPTMA'=>'BANSAN',
					'CL1TMA'=>'LISCHEQ',
					'CL2TMA'=>$lcItemLista,
					'CL4TMA'=>'I',
				])
				->getAll('array');
				$lcDescripcionChequeo='';
				if(is_array($this->aListaChequeo)){ 	if(count($this->aListaChequeo)>0){
					foreach($this->aListaChequeo as $laDescripChequeo){
						$lcDescripcionChequeo .= trim($laDescripChequeo['DESCRIPCION']);
					}	
				}}
				if (!empty(trim($lcDescripcionChequeo))){
					$laRetorno[] = ['texto9', '*  ' .$lcDescripcionChequeo];
				}	
			}
		}
		return $laRetorno;
	}
	
	private function fnComponentesLista($tnIngreso, $tnConsOrden, $tnConsCita)
	{
		$laRetorno = [];
		$lcReturn = '';
		
		$laComponentesLista = $this->oDb
		->select('trim(DESBSC) AS DESCRIPCION, USRBSC, FECBSC, HORBSC')
		->from('BANSAC')
		->where(['INGBSC'=>$tnIngreso, 'CORBSC'=>$tnConsOrden, 'CCIBSC'=>$tnConsCita, 'INDBSC'=>7,])
		->orderBy('INDBSC, LINBSC')
		->getAll('array');
		if(is_array($laComponentesLista)){ 	if(count($laComponentesLista)>0){
			foreach($laComponentesLista as $laComponentesListaCh){
				$lcAspecto = trim(mb_substr($laComponentesListaCh['DESCRIPCION'], 10, 3));
				$lcFiltros = trim(mb_substr($laComponentesListaCh['DESCRIPCION'], 20, 10));
				$lcRealizoTransfusion = trim(mb_substr($laComponentesListaCh['DESCRIPCION'], 30, 1));
				$cNoTransfundir = trim(mb_substr($laComponentesListaCh['DESCRIPCION'], 40, 3));
			}	
		}}
		
		if (!empty(trim($lcAspecto)) || !empty(trim($lcFiltros))  || !empty(trim($lcRealizoTransfusion)) 
			){
			$laRetorno[] = ['titulo2',	'Componentes entregados de acuerdo con los siguientes parámetros:'];

			if (!empty(trim($lcAspecto))){
				$lcAspecto = !empty($lcAspecto)? $this->aListaParametros['ASPECTO'][$lcAspecto] :'';
				$laRetorno[] = ['texto9', 'Aspecto: '. $lcAspecto];		
			}

			if (!empty(trim($lcFiltros))){
				$lcDescripFiltro='';
 				$laFiltro = $this->oDb
				->select('trim(DESDES) AS DESCRIPCION')
				->from('INVDES')
				->where(['REFDES'=>$lcFiltros,])
				->getAll('array');
				if(is_array($laFiltro)){ 	if(count($laFiltro)>0){
					foreach($laFiltro as $laFiltroUtil){
						$lcDescripFiltro = trim($laFiltroUtil['DESCRIPCION']);
					}	
				}}
				if (empty(trim($lcDescripFiltro)) || !empty(trim($lcFiltros))){
					$oTabmae=$this->oDb->obtenerTabMae('DE2TMA', 'BANSAN', "CL1TMA='FILTROS' AND OP5TMA='$lcFiltros'");
					$lcDescripFiltro=trim(AplicacionFunciones::getValue($oTabmae,'DE2TMA',''),' ');
				}
				
				if (!empty(trim($lcDescripFiltro))){
					$laRetorno[] = ['texto9', 'Filtro utilizado: '. $lcDescripFiltro];		
				}
			}
			
			if (!empty(trim($lcRealizoTransfusion))){
				$lcRealizoTransfusion = $lcRealizoTransfusion=='S'? 'Si' : 'No';
				$laRetorno[] = ['titulo2',	'Transfundir (enfermería):'];
				$laRetorno[] = ['texto9', 'Transfundir: '. $lcRealizoTransfusion];		
			}
			
			if (!empty(trim($cNoTransfundir))){
				$cNoTransfundir = !empty($cNoTransfundir)? $this->aListaParametros['NOTRANS'][$cNoTransfundir] :'';
				$laRetorno[] = ['texto9',	'Causa no transfundir: ' .$cNoTransfundir];
			}
			$lcMedicoRealiza = $this->fnUsuarioRealiza(trim($laComponentesListaCh['USRBSC']),$laComponentesListaCh['FECBSC'],$laComponentesListaCh['HORBSC']);
			if(!empty($lcMedicoRealiza)){ $laRetorno[] = ['titulo3', 'Realiza lista chequeo: ' .$lcMedicoRealiza]; }
		}
		return $laRetorno;
	}
	
	private function fnDatosOrdenHemocomponente($tnIngreso, $tnConsOrden, $tcGrupoHemo)
	{
		$laRetorno = [];
		$lcReturn = '';
		$lcMedicoRealiza = '';
		
		$laOrdenS = $this->oDb
			->select('INDBSO,TJUBSO,JUSBSO,LINBSO,DESBSO,USRBSO,FECBSO,HORBSO')
			->from('BANSAO')
			->where('INGBSO', '=', $tnIngreso)
			->where('CORBSO', '=', $tnConsOrden)
			->in('JUSBSO',[$tcGrupoHemo, ''])
			->getAll('array');
		if(is_array($laOrdenS)){ 
			if(count($laOrdenS)>0){
				foreach($laOrdenS as $laOrden){
					$lnIndice=intval(trim($laOrden['INDBSO']));
					switch($lnIndice){

						case 1:
							$lcCodTipoRes=trim(mb_substr($laOrden['DESBSO'], 0, 2),' ');
							if (empty($lcCodTipoRes)){
								$lcTipoReserva='';
							} else {
								$oTabmae=$this->oDb->obtenerTabMae('DE2TMA', 'BANSANR', "CL1TMA='TIPORES' AND CL2TMA='$lcCodTipoRes'");
								$lcTipoReserva=trim(AplicacionFunciones::getValue($oTabmae,'DE2TMA',''),' ');
							}
							$lcFecha				= trim(mb_substr($laOrden['DESBSO'], 30, 8));
							$lcHemoclasificacion	= trim(mb_substr($laOrden['DESBSO'], 15, 6),' ');
							$lcFechaProcedimiento	= empty($lcFecha) ? '' : AplicacionFunciones::formatFechaHora('fecha', $lcFecha);
							$lcHb 					= intval(mb_substr($laOrden['DESBSO'], 45, 15)) ==0 ? '' : trim(mb_substr($laOrden['DESBSO'], 45, 15));
							$lcHematocrito			= intval(mb_substr($laOrden['DESBSO'], 60, 15)) ==0 ? '' : trim(mb_substr($laOrden['DESBSO'], 60, 15));
							$lcPlaquetas			= intval(mb_substr($laOrden['DESBSO'], 75, 15)) ==0 ? '' : trim(mb_substr($laOrden['DESBSO'], 75, 15));
							$lcInr					= intval(mb_substr($laOrden['DESBSO'], 90, 15)) ==0 ? '' : trim(mb_substr($laOrden['DESBSO'], 90, 15));
							$lcPt					= intval(mb_substr($laOrden['DESBSO'], 105, 15))==0 ? '' : trim(mb_substr($laOrden['DESBSO'], 105, 15));
							$lcPtt					= intval(mb_substr($laOrden['DESBSO'], 120, 15))==0 ? '' : trim(mb_substr($laOrden['DESBSO'], 120, 15));
							$lcFibronogeno			= intval(mb_substr($laOrden['DESBSO'], 135, 15))==0 ? '' : trim(mb_substr($laOrden['DESBSO'], 135, 15));
							$lcCodDx				= trim(mb_substr($laOrden['DESBSO'], 150, 5));
							$lcDiagnostico			= empty($lcCodDx)? '' : $lcCodDx.' - '.(new Diagnostico($lcCodDx, $laOrden['FECBSO']))->getTexto();
							$lcRiesgoTransfusional	= trim(mb_substr($laOrden['DESBSO'], 160, 2),' ');
							$lcCodReqFiltro			= trim(mb_substr($laOrden['DESBSO'], 170, 1),' ');
							$lcRequierefiltro		= $lcCodReqFiltro=='S'? 'SI': ( $lcCodReqFiltro=='N'? 'NO': '' );
					
							if (!empty(trim($lcTipoReserva)) || !empty(trim($lcFechaProcedimiento))){
								$lcDetalle = ( empty($lcTipoReserva)? '': AplicacionFunciones::mb_str_pad('Tipo reserva: '.$lcTipoReserva, 50, ' ') )
											.( empty($lcFechaProcedimiento)? '': AplicacionFunciones::mb_str_pad('Fecha estimada procedimiento: '.$lcFechaProcedimiento, 50) );
								$laRetorno[] = ['titulo3',	'Datos de la transfusión:'];	
								$laRetorno[] = ['texto9',	$lcDetalle];			
							}	
							
							if (!empty(trim($lcHemoclasificacion))){
								$laRetorno[] = ['texto9',	'Grupo sanguíneo: ' .$lcHemoclasificacion];	
							}
							
							if (!empty(trim($lcHb)) || !empty(trim($lcHematocrito))){
								$lcDetalle = '';
								$lcDetalle = ( empty($lcHb)? '': AplicacionFunciones::mb_str_pad('Hb(g/dl): '.$lcHb, 50) )
												.( empty($lcHematocrito)? '': AplicacionFunciones::mb_str_pad('Hematocrito: '.$lcHematocrito, 50) );
								$laRetorno[] = ['titulo3',	'Datos de laboratorio:'];					
								$laRetorno[] = ['texto9',	$lcDetalle];						
							}
							
							if (!empty(trim($lcDiagnostico))){
								$laRetorno[] = ['titulo3',	'Diagnóstico:'];					
								$laRetorno[] = ['texto9',	trim($lcDiagnostico)];				
							}
							break;

							case 2:
								$lcDetalle = '';

								if(trim($laOrden['LINBSO'])==1){
									$laRetorno[] = ['titulo3',	'Procedimiento a realizar:'];			
									$laRetorno[] = ['txthtml9',	trim($laOrden['DESBSO'])];
									
									if (!empty(trim($lcRiesgoTransfusional)) || !empty(trim($lcRequierefiltro))){
										$lcDetalle = ( empty($lcRiesgoTransfusional)? '': AplicacionFunciones::mb_str_pad('Riesgo transfusional: '.$lcRiesgoTransfusional, 50) )
													.( empty($lcRequierefiltro)? '': AplicacionFunciones::mb_str_pad('Requiere filtro: '.$lcRequierefiltro, 50) );
										$laRetorno[] = ['texto9',	$lcDetalle];
									}
								}	
							break;
							
							case 3:
								$lcCodigoJustif	= trim($laOrden['TJUBSO'],' ');
								
								if (!empty(trim($lcCodigoJustif))){
									$oTabmae=$this->oDb->obtenerTabMae('DE2TMA', 'BANSAN', "CL1TMA='JUSTIF' AND CL3TMA='$lcCodigoJustif' AND CL4TMA='$tcGrupoHemo'");
									$lcDescrTipoJustificacion=trim(AplicacionFunciones::getValue($oTabmae,'DE2TMA',''),' ');
									$laRetorno[] = ['titulo3',	'Justificación: ' .$lcDescrTipoJustificacion];
								}
								
								if (!empty(trim($laOrden['DESBSO']))){
									$laRetorno[] = ['txthtml9',	trim($laOrden['DESBSO'],' ')];
								}
								$lcMedicoRealiza = $this->fnUsuarioRealiza(trim($laOrden['USRBSO']),$laOrden['FECBSO'],$laOrden['HORBSO']);
								if(!empty($lcMedicoRealiza)){ $laRetorno[] = ['titulo3', 'Realiza solicitud: ' .$lcMedicoRealiza]; }
							break;
					}
				}
			}
		}
		return $laRetorno;
	}

	private function fnDatosCitaHemocomponente($tnIngreso, $tnConsOrden, $tnConsCita)
	{
		$laRetorno = [];
		$lcReturn = '';
		$lcMedicoRealiza = '';
		
		$laDatosCita = $this->oDb
			->select('INDBSC AS INDICE, DESBSC AS DESCRIPCION, USRBSC AS USUARIO_REALIZA, FECBSC AS FECHA_REALIZA, HORBSC AS HORA_REALIZA')
			->from('BANSAC')
			->where('INGBSC', '=', $tnIngreso)
			->where('CORBSC', '=', $tnConsOrden)
			->where('CCIBSC', '=', $tnConsCita)
			->in('INDBSC',[2, 5])
			->orderBy('INDBSC, LINBSC')
			->getAll('array');
			
		if(is_array($laDatosCita)){ 
			if(count($laDatosCita)>0){
				foreach($laDatosCita as $laResultadoCita){
					$lnIndice=intval(trim($laResultadoCita['INDICE']));
					switch($lnIndice){

						case 2:
							$lcDetalle='';
							$lcGrupoSanguineo=trim(mb_substr($laResultadoCita['DESCRIPCION'], 0, 2),' ');
							$lcCoombs=trim(mb_substr($laResultadoCita['DESCRIPCION'], 10, 1),' ');
							$lcRai=trim(mb_substr($laResultadoCita['DESCRIPCION'], 20, 1),' ');
							$lcAutoControl=trim(mb_substr($laResultadoCita['DESCRIPCION'], 30, 1),' ');
							$lcSelloCalidad=trim(mb_substr($laResultadoCita['DESCRIPCION'], 40, 10),' ');
							$lcFechaExpira=trim(mb_substr($laResultadoCita['DESCRIPCION'], 90, 8),' ');
							$lcPruebaCruzada=trim(mb_substr($laResultadoCita['DESCRIPCION'], 140, 1),' ');
							$lcCantidadPruebaCruzada=trim(mb_substr($laResultadoCita['DESCRIPCION'], 145, 3),' ');
							$lcGrupoUnidad=trim(mb_substr($laResultadoCita['DESCRIPCION'], 150, 5),' ');
							$lcIncompatible=trim(mb_substr($laResultadoCita['DESCRIPCION'], 175, 3),' ');
							$lcIncompatibleCantidad=trim(mb_substr($laResultadoCita['DESCRIPCION'], 180, 3),' ');
							$lcIncompatible=trim(mb_substr($laResultadoCita['DESCRIPCION'], 175, 3),' ');
							$lcIncompatibleCantidad=trim(mb_substr($laResultadoCita['DESCRIPCION'], 180, 3),' ');
							$lcGrupoInversa=trim(mb_substr($laResultadoCita['DESCRIPCION'], 165, 5),' ');
							$lcIrradiado=trim(mb_substr($laResultadoCita['DESCRIPCION'], 160, 1),' ');
							$lcVolumen=trim(mb_substr($laResultadoCita['DESCRIPCION'], 170, 4),' ');
							
							$laRetorno[] = ['titulo2',	'Datos laboratorio:'];	
							$laRetorno[] = ['titulo3',	'Resultado:'];	

							if (!empty(trim($lcGrupoSanguineo)) || !empty(trim($lcRai))){
								$lcRai = !empty($lcRai)? $this->aListaParametros['COOMBS'][$lcRai] :'';
								$lcDetalle = ( empty($lcGrupoSanguineo)? '': AplicacionFunciones::mb_str_pad('Grupo y RH paciente: '.$lcGrupoSanguineo, 50, ' ') )
											.( empty($lcRai)? '': AplicacionFunciones::mb_str_pad('R.A.I.: '.$lcRai, 50) );
								$laRetorno[] = ['texto9',	$lcDetalle];			
							}	
							
							if (!empty(trim($lcGrupoInversa)) || !empty(trim($lcIrradiado))){
								$lcDetalle = '';	
								$lcDetalle = ( empty($lcGrupoInversa)? '': AplicacionFunciones::mb_str_pad('Hemoclasificación inversa: '.$lcGrupoInversa, 50, ' ') )
											.( empty($lcIrradiado)? '': AplicacionFunciones::mb_str_pad('Irradiado: '.($lcIrradiado=='S'? 'Si': ( $lcIrradiado=='N'? 'No': '' )), 50) );
								$laRetorno[] = ['texto9',	$lcDetalle];	
							}	
							
							if (!empty(trim($lcCoombs)) || !empty(trim($lcAutoControl))){
								$lcDetalle = '';	
								$lcCoombs = !empty($lcCoombs)? $this->aListaParametros['COOMBS'][$lcCoombs] :'';
								$lcAutoControl = !empty($lcAutoControl)? $this->aListaParametros['COOMBS'][$lcAutoControl] :'';
								$lcDetalle = ( empty($lcCoombs)? '': AplicacionFunciones::mb_str_pad('COOMBS DTO: '.$lcCoombs, 50, ' ') )
											.( empty($lcAutoControl)? '': AplicacionFunciones::mb_str_pad('Autocontrol: '.$lcAutoControl, 50) );
								$laRetorno[] = ['texto9',	$lcDetalle];	
							}				
							$lcPruebaCruzada = !empty(trim($lcPruebaCruzada)) ? $lcPruebaCruzada : $lcIncompatible;
							$lcCantidadPruebaCruzada = !empty(trim($lcCantidadPruebaCruzada)) ? $lcCantidadPruebaCruzada : $lcIncompatibleCantidad;
							
							if (!empty(trim($lcPruebaCruzada)) || !empty(trim($lcCantidadPruebaCruzada)) || !empty(trim($lcGrupoUnidad))){
								$lcDetalle = '';	
								$lcPruebaCruzada = $lcPruebaCruzada=='1'? 'Compatible': ( $lcPruebaCruzada=='2'? 'Incompatible': '' );
								$lcDetalle = (empty($lcPruebaCruzada)? '': AplicacionFunciones::mb_str_pad('Prueba cruzada: '.$lcPruebaCruzada .' ('.$lcCantidadPruebaCruzada.')', 50, ' ') )
											.(empty($lcGrupoUnidad)? '': AplicacionFunciones::mb_str_pad('Grupo RH (unidad): ' .$lcGrupoUnidad, 50) );
								$laRetorno[] = ['texto9',	$lcDetalle];	
							}	
							
							if (!empty(trim($lcSelloCalidad)) || !empty(trim($lcFechaExpira))){
								$lcDetalle = '';	
								$lcDetalle = ( empty($lcSelloCalidad)? '': AplicacionFunciones::mb_str_pad('Sello calidad: '.$lcSelloCalidad, 50, ' ') )
											.( empty($lcFechaExpira)? '': AplicacionFunciones::mb_str_pad('Fecha vencimiento: ' .AplicacionFunciones::formatFechaHora('fecha', $lcFechaExpira), 50) );
								$laRetorno[] = ['texto9',	$lcDetalle];	
							}							
							
							if (!empty(trim($lcVolumen))){
								$laRetorno[] = ['texto9',	'Volumen(ml): '. $lcVolumen];	
							}
							
							$lcMedicoRealiza = $this->fnUsuarioRealiza(trim($laResultadoCita['USUARIO_REALIZA']),$laResultadoCita['FECHA_REALIZA'],$laResultadoCita['HORA_REALIZA']);
							if(!empty($lcMedicoRealiza)){ $laRetorno[] = ['titulo3', 'Realiza resultado: ' .$lcMedicoRealiza]; }
						break;
							
						case 5:
							$lcDetalle='';	
							$lcTemperaturaLabora=trim(mb_substr($laResultadoCita['DESCRIPCION'], 0, 2),' ');
							$lcCantidadLaboratorio=trim(mb_substr($laResultadoCita['DESCRIPCION'], 10, 5),' ');
							$lcArnes=trim(mb_substr($laResultadoCita['DESCRIPCION'], 20, 3),' ');
							$lcCargoEntrega=trim(mb_substr($laResultadoCita['DESCRIPCION'], 30, 100),' ');
							$laRetorno[] = ['titulo3',	'Componentes entregados de acuerdo con los siguientes parámetros:'];	
							
							if (!empty(trim($lcTemperaturaLabora))){
								$lcTemperaturaLabora = AplicacionFunciones::mb_str_pad($lcTemperaturaLabora, 3, '0', STR_PAD_LEFT);
								$oTabmae=$this->oDb->obtenerTabMae('DE2TMA', 'BANTEMP', "CL1TMA='$lcTemperaturaLabora'");
								$lcDescrcTemperaturaLabora=trim(AplicacionFunciones::getValue($oTabmae,'DE2TMA',''),' ');
								
								$laTemperaturaLlega = $this->oDb
								->select('trim(DESBSC) AS DESCRIPCION')
								->from('BANSAC')
								->where(['INGBSC'=>$tnIngreso, 'CORBSC'=>$tnConsOrden, 'CCIBSC'=>$tnConsCita, 'INDBSC'=>7,])
								->orderBy('INDBSC, LINBSC')
								->getAll('array');
								if(is_array($laTemperaturaLlega)){ 	if(count($laTemperaturaLlega)>0){
									foreach($laTemperaturaLlega as $laTemperaturaLlega){
										$lcTemperaturaLllegada = AplicacionFunciones::mb_str_pad(trim(mb_substr($laTemperaturaLlega['DESCRIPCION'], 0, 2),' '), 3, '0', STR_PAD_LEFT);
									}	
								}}
								
								if (!empty(trim($lcTemperaturaLllegada))){
									$oTabmae=$this->oDb->obtenerTabMae('DE2TMA', 'BANTEMP', "CL1TMA='$lcTemperaturaLllegada'");
									$lcDescrcTemperaturaLllegada=trim(AplicacionFunciones::getValue($oTabmae,'DE2TMA',''),' ');
								}	
								$lcDetalle = ( empty($lcTemperaturaLabora)? '': AplicacionFunciones::mb_str_pad('Temperatura envió: '.$lcDescrcTemperaturaLabora .'°C', 50, ' ') )
											.( empty($lcTemperaturaLabora)? '': AplicacionFunciones::mb_str_pad('Temperatura llegada: '.$lcDescrcTemperaturaLllegada .'°C', 50) );
								$laRetorno[] = ['texto9',	$lcDetalle];	
							}
							
							if (!empty(trim($lcCantidadLaboratorio)) || !empty(trim($lcArnes))){
								$oTabmae=$this->oDb->obtenerTabMae('DE2TMA', 'BANARNES', "CL1TMA='$lcArnes'");
								$lcDescrcArnes=trim(AplicacionFunciones::getValue($oTabmae,'DE2TMA',''),' ');
								$lcDetalle = ( empty($lcCantidadLaboratorio)? '': AplicacionFunciones::mb_str_pad('Cantidad: '.$lcCantidadLaboratorio, 50, ' ') )
											.( empty($lcArnes)? '': AplicacionFunciones::mb_str_pad('Tipo: ' .$lcDescrcArnes, 50) );
								$laRetorno[] = ['texto9',	$lcDetalle];	
							}							
							
							if (!empty(trim($lcCargoEntrega))){
								$laRetorno[] = ['texto9',	'Recibe: ' . $lcCargoEntrega];	
							}
							
							$lcObservacionesEntrega = '';
							$laObservacionesEntrega = $this->oDb
							->select('trim(DESBSC) AS DESCRIPCION')
							->from('BANSAC')
							->where(['INGBSC'=>$tnIngreso, 'CORBSC'=>$tnConsOrden, 'CCIBSC'=>$tnConsCita, 'INDBSC'=>6,])
							->orderBy('INDBSC, LINBSC')
							->getAll('array');
							if(is_array($laObservacionesEntrega)){ 	if(count($laObservacionesEntrega)>0){
								foreach($laObservacionesEntrega as $laObservacionesEntrega){
									$lcObservacionesEntrega .= trim($laObservacionesEntrega['DESCRIPCION'],' ');
								}
							}}		
							if (!empty(trim($lcObservacionesEntrega))){ 
								$laRetorno[] = ['texto9',	'Observaciones: ' . $lcObservacionesEntrega]; 
							}
			
							$lcMedicoRealiza = $this->fnUsuarioRealiza(trim($laResultadoCita['USUARIO_REALIZA']),$laResultadoCita['FECHA_REALIZA'],$laResultadoCita['HORA_REALIZA']);
							if(!empty($lcMedicoRealiza)){ $laRetorno[] = ['titulo3', 'Realiza entrega(envio): ' .$lcMedicoRealiza]; }
						break;
					}
				}
			}
		}
		return $laRetorno;
	}

	private function fnDatosSignosVitales($tnIngreso, $tnConsOrden, $tnConsCita)
	{
		$laRetorno = [];
		$lcReturn = '';
		$lcMedicoRealiza = '';
		$lcDetalle='';
		
		$laDatosSignosVitales = $this->oDb
			->select('INDBSC AS INDICE, DESBSC AS DESCRIPCION, USRBSC AS USUARIO_REALIZA, FECBSC AS FECHA_REALIZA, HORBSC AS HORA_REALIZA')
			->from('BANSAC')
			->where('INGBSC', '=', $tnIngreso)
			->where('CORBSC', '=', $tnConsOrden)
			->where('CCIBSC', '=', $tnConsCita)
			->in('INDBSC',[8, 9, 10, 11, 12, 14])
			->orderBy('INDBSC, LINBSC')
			->getAll('array');

		if(is_array($laDatosSignosVitales)){ 
			if(count($laDatosSignosVitales)>0){
				foreach($laDatosSignosVitales as $laResultadoSignos){
					$lnIndice=intval(trim($laResultadoSignos['INDICE']));
					switch($lnIndice){

						case 8:
							$lnTASEst=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 0, 6),' ');
							$lnTADEst=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 10, 6),' ');
							$lnFCEst=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 20, 6),' ');
							$lnTempEst=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 30, 9),' ');
							$lnFREst=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 40, 6),' ');
							$lnSaturaEst=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 50, 6),' ');
							$lcServicio=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 60, 2),' ');
						
							if (!empty(trim($lnTASEst)) || !empty(trim($lnTADEst)) || !empty(trim($lcServicio)) || !empty(trim($$lnFCEst)) || !empty(trim($lnTempEst)) || !empty(trim($lnFREst)) || !empty(trim($lnSaturaEst))
								){
								$laRetorno[] = ['titulo2',	'Signos vitales:'];	
								
								if (!empty(trim($lcServicio))){
									$lcServicio = !empty(trim($lcServicio))? $this->aListaParametros['SERVICIO'][$lcServicio] :'';
									$laRetorno[] = ['titulo3',	'Servicio realiza: ' .$lcServicio];
								}	
								
								if (!empty(trim($lnTASEst)) || !empty(trim($lnTADEst)) || !empty(trim($lnFCEst)) || !empty(trim($lnTempEst)) || !empty(trim($lnFREst)) || !empty(trim($lnSaturaEst))
									){
									$lcDetalle = ((!empty(trim($lnTASEst)) || !empty(trim($lnTADEst))) ? AplicacionFunciones::mb_str_pad(('PA: ' .$lnTASEst .'/' .$lnTADEst), 15, ' ', STR_PAD_LEFT):'' )
												.(!empty(trim($lnFCEst)) ? AplicacionFunciones::mb_str_pad('FC: ' .$lnFCEst, 15, ' ', STR_PAD_LEFT):'')	
												.(!empty(trim($lnTempEst)) ? AplicacionFunciones::mb_str_pad('Temp°: ' .$lnTempEst, 20, ' ', STR_PAD_LEFT):'')	
												.(!empty(trim($lnFREst)) ? AplicacionFunciones::mb_str_pad('FR: ' .$lnFREst, 15, ' ', STR_PAD_LEFT):'')	
												.(!empty(trim($lnSaturaEst)) ? AplicacionFunciones::mb_str_pad('SAT 02%: ' .$lnSaturaEst, 20, ' ', STR_PAD_LEFT):'');
									$laRetorno[] = ['texto1',	' '];
									$laRetorno[] = ['texto1',	'Inicio: ' .$lcDetalle];
								}

								$lcMedicoRealiza = $this->fnUsuarioRealiza(trim($laResultadoSignos['USUARIO_REALIZA']),$laResultadoSignos['FECHA_REALIZA'],$laResultadoSignos['HORA_REALIZA']);
								if(!empty($lcMedicoRealiza)){ $laRetorno[] = ['titulo3', 'Realiza signos vitales inicio: ' .$lcMedicoRealiza]; }
							}	
						break;
						
						case 9:
							$lnVistoInicio=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 0, 1),' ');
							
							if (!empty(trim($lnVistoInicio))){
								$lnVistoInicio = $lnVistoInicio=='1'? 'Si' : 'No';
								$laRetorno[] = ['texto1',	' '];
								$laRetorno[] = ['titulo3',	'Visto bueno inicial: ' .$lnVistoInicio];
								
								$lcMedicoRealiza = $this->fnUsuarioRealiza(trim($laResultadoSignos['USUARIO_REALIZA']),$laResultadoSignos['FECHA_REALIZA'],$laResultadoSignos['HORA_REALIZA']);
								if(!empty($lcMedicoRealiza)){ $laRetorno[] = ['titulo3', 'Realiza visto bueno inicio: ' .$lcMedicoRealiza]; }
							}
						break;

						case 10:
							$lcDetalle = '';
							$lnTASdurante=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 0, 6),' ');
							$lnTADDurante=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 10, 6),' ');
							$lnFCDurante=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 20, 6),' ');
							$lnTempDurante=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 30, 9),' ');
							$lnFRDurante=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 40, 6),' ');
							$lnSaturaDurante=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 50, 6),' ');
							
							if (!empty(trim($lnTASdurante)) || !empty(trim($lnTADDurante)) || !empty(trim($lnFCDurante)) || !empty(trim($lnTempDurante)) || !empty(trim($lnFRDurante)) || !empty(trim($lnSaturaDurante))
									){
									$lcDetalle = ((!empty(trim($lnTASdurante)) || !empty(trim($lnTADDurante))) ? AplicacionFunciones::mb_str_pad(('PA: ' .$lnTASdurante .'/' .$lnTADDurante), 15, ' ', STR_PAD_LEFT):'' )
												.(!empty(trim($lnFCDurante)) ? AplicacionFunciones::mb_str_pad('FC: ' .$lnFCDurante, 15, ' ', STR_PAD_LEFT):'')	
												.(!empty(trim($lnTempDurante)) ? AplicacionFunciones::mb_str_pad('Temp°: ' .$lnTempDurante, 20, ' ', STR_PAD_LEFT):'')	
												.(!empty(trim($lnFRDurante)) ? AplicacionFunciones::mb_str_pad('FR: ' .$lnFRDurante, 15, ' ', STR_PAD_LEFT):'')	
												.(!empty(trim($lnSaturaDurante)) ? AplicacionFunciones::mb_str_pad('SAT 02%: ' .$lnSaturaDurante, 20, ' ', STR_PAD_LEFT):'');
									$laRetorno[] = ['texto1',	' '];
									$laRetorno[] = ['texto1',	'Durante: ' .$lcDetalle];
							}
							$lcMedicoRealiza = $this->fnUsuarioRealiza(trim($laResultadoSignos['USUARIO_REALIZA']),$laResultadoSignos['FECHA_REALIZA'],$laResultadoSignos['HORA_REALIZA']);
							if(!empty($lcMedicoRealiza)){ $laRetorno[] = ['titulo3', 'Realiza signos vitales durante: ' .$lcMedicoRealiza]; }
						break;
						
						case 11:
							$lcDetalle = '';
							$lnTASDespues=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 0, 6),' ');
							$lnTADDespues=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 10, 6),' ');
							$lnFCDespues=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 20, 6),' ');
							$lnTempDespues=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 30, 9),' ');
							$lnFRDespues=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 40, 6),' ');
							$lnSaturaDespues=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 50, 6),' ');
							
							if (!empty(trim($lnTASDespues)) || !empty(trim($lnTADDespues)) || !empty(trim($lnFCDespues)) || !empty(trim($lnTempDespues)) || !empty(trim($lnFRDespues)) || !empty(trim($lnSaturaDespues))
									){
									$lcDetalle = ((!empty(trim($lnTASDespues)) || !empty(trim($lnTADDespues))) ? AplicacionFunciones::mb_str_pad(('PA: ' .$lnTASDespues .'/' .$lnTADDespues), 15, ' ', STR_PAD_LEFT):'' )
												.(!empty(trim($lnFCDespues)) ? AplicacionFunciones::mb_str_pad('FC: ' .$lnFCDespues, 15, ' ', STR_PAD_LEFT):'')	
												.(!empty(trim($lnTempDespues)) ? AplicacionFunciones::mb_str_pad('Temp°: ' .$lnTempDespues, 20, ' ', STR_PAD_LEFT):'')	
												.(!empty(trim($lnFRDespues)) ? AplicacionFunciones::mb_str_pad('FR: ' .$lnFRDespues, 15, ' ', STR_PAD_LEFT):'')	
												.(!empty(trim($lnSaturaDespues)) ? AplicacionFunciones::mb_str_pad('SAT 02%: ' .$lnSaturaDespues, 20, ' ', STR_PAD_LEFT):'');
									$laRetorno[] = ['texto1',	' '];
									$laRetorno[] = ['texto1',	'Finales: ' .$lcDetalle];
							}
							$lcMedicoRealiza = $this->fnUsuarioRealiza(trim($laResultadoSignos['USUARIO_REALIZA']),$laResultadoSignos['FECHA_REALIZA'],$laResultadoSignos['HORA_REALIZA']);
							if(!empty($lcMedicoRealiza)){ $laRetorno[] = ['titulo3', 'Realiza signos vitales finales: ' .$lcMedicoRealiza]; }
						break;
						
						case 12:
							$lnVistoDespues=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 0, 1),' ');
							
							if (!empty(trim($lnVistoDespues))){
								$lnVistoDespues = $lnVistoDespues=='1'? 'Si' : 'No';
								$laRetorno[] = ['texto1',	' '];
								$laRetorno[] = ['titulo3',	'Visto bueno final: ' .$lnVistoDespues];
								
								$lcMedicoRealiza = $this->fnUsuarioRealiza(trim($laResultadoSignos['USUARIO_REALIZA']),$laResultadoSignos['FECHA_REALIZA'],$laResultadoSignos['HORA_REALIZA']);
								if(!empty($lcMedicoRealiza)){ $laRetorno[] = ['titulo3', 'Realiza visto bueno final: ' .$lcMedicoRealiza]; }
							}
						break;
						
						case 14:
							$lcDetalle='';
							$lcRat=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 0, 1),' ');
							$lnFiebre=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 5, 1),' ');
							$lnEscalofrio=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 8, 1),' ');
							$lnHipotension=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 11, 1),' ');
							$lnHipertension=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 14, 1),' ');
							$lnOliguria=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 17, 1),' ');
							$lnConvulsiones=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 20, 1),' ');
							$lnHemorragia=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 23, 1),' ');
							$lnUrticaria=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 26, 1),' ');
							$lnNauseas=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 29, 1),' ');
							$lnIctericia=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 32, 1),' ');
							$lnTaquicardia=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 35, 1),' ');
							$lnSomnolencia=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 38, 1),' ');
							$lnDolorlumbar=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 41, 1),' ');
							$lnDolortoracico=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 44, 1),' ');
							$lnDolorinfusion=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 47, 1),' ');
							$lnCefalea=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 50, 1),' ');
							$lnPrurito=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 53, 1),' ');
							$lnConfusion=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 56, 1),' ');
							$lnHipoxemia=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 59, 1),' ');
							$lnHemoglobinuria=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 62, 1),' ');
							$lnDisnea=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 65, 1),' ');
							$lnTos=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 68, 1),' ');
							$lnCianosis=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 71, 1),' ');
							$lnEstupor=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 74, 1),' ');
							$lnArritmias=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 77, 1),' ');
							$lnParestesias=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 80, 1),' ');
							$lnTetania=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 83, 1),' ');
							$lnEritrodermia=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 86, 1),' ');
							$lnEdemaPulmonar=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 89, 1),' ');
							$lnDelirio=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 92, 1),' ');
							$lnEritema=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 95, 1),' ');
							$lnEdema=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 98, 1),' ');
							$lnChoque=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 101, 1),' ');
							$lnDiarrea=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 104, 1),' ');
							$lnPetequias=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 107, 1),' ');
							$lnComa=trim(mb_substr($laResultadoSignos['DESCRIPCION'], 110, 1),' ');
							
							if (!empty(trim($lcRat))){
								$lcRat = $lcRat=='S'? 'Si' : 'No';
								$laRetorno[] = ['texto1',	' '];
								$laRetorno[] = ['titulo3',	'RAT: ' .$lcRat];
							}
							$lcDetalle = ((!empty(trim($lnFiebre)) ? 'Fiebre' .PHP_EOL:''))
										 .((!empty(trim($lnEscalofrio)) ? 'Escalofrio' .PHP_EOL:''))
										 .((!empty(trim($lnHipotension)) ? 'Hipotensión' .PHP_EOL:''))
										 .((!empty(trim($lnHipertension)) ? 'Hipertensión' .PHP_EOL:''))
										 .((!empty(trim($lnOliguria)) ? 'Oliguria/anuria' .PHP_EOL:''))
										 .((!empty(trim($lnConvulsiones)) ? 'Convulsiones' .PHP_EOL:''))
										 .((!empty(trim($lnIctericia)) ? 'Ictericia' .PHP_EOL:''))
										 .((!empty(trim($lnTaquicardia)) ? 'Taquicardia' .PHP_EOL:''))
										 .((!empty(trim($lnSomnolencia)) ? 'Somnolencia' .PHP_EOL:''))
										 .((!empty(trim($lnDolorlumbar)) ? 'Dolor lumbar' .PHP_EOL:''))
										 .((!empty(trim($lnDolortoracico)) ? 'Dolor torácico' .PHP_EOL:''))
										 .((!empty(trim($lnDolorinfusion)) ? 'Dolor en el sitio de la infusión' .PHP_EOL:''))
										 .((!empty(trim($lnHipoxemia)) ? 'Hipoxemia' .PHP_EOL:''))
										 .((!empty(trim($lnHemoglobinuria)) ? 'Hemoglobinuria' .PHP_EOL:''))
										 .((!empty(trim($lnDisnea)) ? 'Disnea' .PHP_EOL:''))
										 .((!empty(trim($lnTos)) ? 'Tos' .PHP_EOL:''))
										 .((!empty(trim($lnCianosis)) ? 'Cianosis' .PHP_EOL:''))
										 .((!empty(trim($lnEstupor)) ? 'Estupor' .PHP_EOL:''))
										 .((!empty(trim($lnEritrodermia)) ? 'Eritrodermia' .PHP_EOL:''))
										 .((!empty(trim($lnDelirio)) ? 'Delirio' .PHP_EOL:''))
										 .((!empty(trim($lnEdema)) ? 'Edema' .PHP_EOL:''))
										 .((!empty(trim($lnDiarrea)) ? 'Diarrea' .PHP_EOL:''))
										 .((!empty(trim($lnComa)) ? 'Coma' .PHP_EOL:''))
										 .((!empty(trim($lnParestesias)) ? 'Parestesias' .PHP_EOL:''))
										 .((!empty(trim($lnEdemaPulmonar)) ? 'Edema Pulmonar' .PHP_EOL:''))
										 .((!empty(trim($lnEritema)) ? 'Eritema' .PHP_EOL:''))
										 .((!empty(trim($lnChoque)) ? 'Choque' .PHP_EOL:''))
										 .((!empty(trim($lnPetequias)) ? 'Petequias' .PHP_EOL:''))
										 .((!empty(trim($lnTetania)) ? 'Tetania' .PHP_EOL:''))
										 .((!empty(trim($lnConfusion)) ? 'Confusión' .PHP_EOL:''))
										 .((!empty(trim($lnUrticaria)) ? 'Urticaria' .PHP_EOL:''))
										 .((!empty(trim($lnHemorragia)) ? 'Hemorragia' .PHP_EOL:''))
										 .((!empty(trim($lnCefalea)) ? 'Cefalea' .PHP_EOL:''))
										 .((!empty(trim($lnArritmias)) ? 'Arritmias cardiácas' .PHP_EOL:''))
										 .((!empty(trim($lnPrurito)) ? 'Prurito' .PHP_EOL:''))
										 .((!empty(trim($lnNauseas)) ? 'Náuseas/Vómito' .PHP_EOL:''))
										;
							
							if (!empty(trim($lcDetalle))){
								$laRetorno[] = ['texto9',	$lcDetalle];
							}	
							$lcMedicoRealiza = $this->fnUsuarioRealiza(trim($laResultadoSignos['USUARIO_REALIZA']),$laResultadoSignos['FECHA_REALIZA'],$laResultadoSignos['HORA_REALIZA']);
							if(!empty($lcMedicoRealiza)){ $laRetorno[] = ['titulo3', 'Realiza RAT: ' .$lcMedicoRealiza]; }

							

						break;
						
					}
				}	
			}			
		}
		return $laRetorno;
	}						
						
	private function fnDatosTransfucionMedico($tnIngreso, $tnConsOrden, $tnConsCita)
	{
		$laRetorno = [];
		$lcReturn = '';
		$lcMedicoRealiza = '';
		
		$laDatosTransfundir = $this->oDb
			->select('INDBSC AS INDICE, DESBSC AS DESCRIPCION, USRBSC AS USUARIO_REALIZA, FECBSC AS FECHA_REALIZA, HORBSC AS HORA_REALIZA')
			->from('BANSAC')
			->where('INGBSC', '=', $tnIngreso)
			->where('CORBSC', '=', $tnConsOrden)
			->where('CCIBSC', '=', $tnConsCita)
			->in('INDBSC',[8, 20])
			->orderBy('INDBSC, LINBSC')
			->getAll('array');

		if(is_array($laDatosTransfundir)){ 
			if(count($laDatosTransfundir)>0){
				foreach($laDatosTransfundir as $laResultadoTransfundir){
					$lnIndice=intval(trim($laResultadoTransfundir['INDICE']));
					switch($lnIndice){

						case 20:
							$lcTransfundirMedico=trim(mb_substr($laResultadoTransfundir['DESCRIPCION'], 0, 1),' ');
							$lcCausaNoTransMedico=trim(mb_substr($laResultadoTransfundir['DESCRIPCION'], 10, 3),' ');
							$lcTransfundirMedico = $lcTransfundirMedico=='S'? 'Si' : 'No';
							
							$laRetorno[] = ['titulo2',	'Transfundir (médico):'];	
							$laRetorno[] = ['texto9',	'Transfundir: ' .$lcTransfundirMedico];

							if (!empty(trim($lcCausaNoTransMedico))){
								$lcCausaNoTransMedico = !empty($lcCausaNoTransMedico)? $this->aListaParametros['NOTRANS'][$lcCausaNoTransMedico] :'';
								$laRetorno[] = ['texto9',	'Causa no transfundir: ' .$lcCausaNoTransMedico];
							}
							
							$lcMedicoRealiza = $this->fnUsuarioRealiza(trim($laResultadoTransfundir['USUARIO_REALIZA']),$laResultadoTransfundir['FECHA_REALIZA'],$laResultadoTransfundir['HORA_REALIZA']);
							if(!empty($lcMedicoRealiza)){ $laRetorno[] = ['titulo3', 'Realiza transfusión: ' .$lcMedicoRealiza]; }
						break;
					}
				}	
			}
		}
		return $laRetorno;
	}			
				
	function fnUsuarioRealiza($tcUsuarioRealiza,$tcFechaRealiza,$tcHoraRealiza)
	{
		$lcReturn = '';
		$lcFechaHoraRealizado = '';
		
		if (!empty(trim($tcFechaRealiza)) || !empty(trim($tcHoraRealiza))){
			$lcFechaHoraRealizado = AplicacionFunciones::formatFechaHora('fecha', $tcFechaRealiza) .' '.AplicacionFunciones::formatFechaHora('hora12', $tcHoraRealiza);
		}
		
		$laMedicoRealiza = $this->oDb
		->select(' UPPER(TRIM(A.NNOMED)||\' \'||TRIM(A.NOMMED)) AS NOMBRE_PACIENTE, B.TABDSC AS TIPO_USUARIO')
		->from('RIARGMN A')
		->innerJoin('PRMTAB B', 'B.TABCOD=CHAR(A.TPMRGM) AND B.TABTIP=\'TUS\'', null)
		->where([ 
				'A.USUARI'=>$tcUsuarioRealiza,
		])
		->get('array');
			
		if (is_array($laMedicoRealiza)){
			if(count($laMedicoRealiza)>0){
				$lcReturn = trim($laMedicoRealiza['NOMBRE_PACIENTE']) .' (' . trim($laMedicoRealiza['TIPO_USUARIO']) .'), ' .$lcFechaHoraRealizado;
			}
		}	
		return trim($lcReturn,' ');
	}
}
