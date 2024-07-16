<?php
namespace NUCLEO;

require_once ('class.UsuarioRegMedico.php');
require_once ('class.Consecutivos.php');
require_once ('class.Cobros.php');
require_once ('class.Db.php') ;
require_once ('class.AplicacionFunciones.php');

require_once 'class.Consecutivos.php';
require_once __DIR__ . '/class.AplicacionFunciones.php';

use NUCLEO\UsuarioRegMedico;
use NUCLEO\Consecutivos;

class Doc_Interconsulta
{
	protected $oDb;
	public	  $aVar = [];
	protected $oMedicoOrdeno = null;
	protected $oMedicoRealizo = null;
	protected $aReporte = [
				'cTitulo' => 'INTERCONSULTA MÉDICA',
				'lMostrarFechaRealizado' => true,
				'lMostrarViaCama' => true	,
				'cTxtAntesDeCup' => '',
				'cTituloCup' => '',
				'cTxtLuegoDeCup' => '',
				'aCuerpo' => [],
				'aNotas' => ['notas'=>true,],
			];
	protected $aTipos = [
				'O' => ['tipo'=>1, 'desc'=>'Opinión'],
				'M' => ['tipo'=>2, 'desc'=>'Manejo Conjunto'],
				'T' => ['tipo'=>3, 'desc'=>'Traslado'],
			];

	protected $dFechaHoraRta;
	private $conRegistrosEvoluc=10000;

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}


	public function validarEstadoInterconsulta($pcIngreso, $piCita){
		$ovalidaEstado = $this->oDb->select("ESTORD")
		->from("RIAORDL25")
		->where("NINORD","=",$pcIngreso)
		->where("CCIORD","=",$piCita)
		->get("array");

		if($ovalidaEstado["ESTORD"] =='3'){
			return true;
		}
		
		return false;

	}

	public function retornarDocumento($taData)
	{
		$this->consultarDatos($taData);
		$this->prepararInforme($taData);
		return $this->aReporte;
	}


	public function consultarDatos($taData)
	{
		$this->crearVar([
			'Solicitud','Respuesta','UsuarioOrdena','TipoInterc','UsuarioResponde','TipoUsuario',
			'AnalisisEpi','IntPrioridad', 'TextoPrioridad','Seguimientos','TextoPandemia','estudiante',
			'fecha_aval_estu','hora_aval_estu','DiagnosticoPrincipal' ], '');
		
		$this->crearVar(['CodTipoInterc','FechaRta','HoraRta'], 0);

		$where = [
			'INGINT'=>$taData['nIngreso'],
			'CORINT'=>$taData['nConsecCita'],
			'CUPINT'=>$taData['cCUP']
		];

		// Datos de la interconsulta
		$laInterc = $this->oDb
			->select('CONINT, SORINT, OTCINT, CNLINT, DESINT, USRINT, FECINT, HORINT, USRRIC,FECRIC,HORRIC')
			->from('INTCON INTE')
			->leftJoin('RIAORD RI','RI.NINORD = INTE.INGINT AND RI.CCIORD = INTE.CORINT')
			->leftJoin('REINCA RE',"RE.INGRIC = INTE.INGINT AND RE.INDRIC =INTE.CORINT AND RE.TIPRIC ='RI'")
			->where($where)
			->orderBy('INGINT ASC, CORINT ASC, SORINT DESC, CNLINT')
			->getAll('array');

		
		if($this->oDb->numRows()>0){
			foreach($laInterc as $laInt){
				switch(trim($laInt['SORINT'])){

					// Si es Solicitud
					case 'S':
						// Revisa si es urgente
						if($laInt['CNLINT']==600){
							$this->aVar['IntPrioridad'] = trim($laInt['DESINT']);
							$this->aVar['TextoPrioridad']= ($this->aVar['IntPrioridad']=='1') ? 'URGENTE' : 'NO URGENTE';
							$this->aVar['ConEvoSol'] = $laInt['CONINT'];

						// Omitir la línea 600 si es Solicitud
						} else {
							$this->aVar['Solicitud'].= $laInt['DESINT'];
							$this->aVar['CodTipoInterc'] = $this->aTipos[trim($laInt['OTCINT'])]['tipo'] ?? 0;
							$this->aVar['TipoInterc'] = $this->aTipos[trim($laInt['OTCINT'])]['desc'] ?? '';
							$this->aVar['UsuarioOrdena'] = $laInt['USRINT'];
						}
						break;

					// Respuesta y Análisis
					case 'R':
						if($laInt['CNLINT']>=0 && $laInt['CNLINT']<=500){
							$this->aVar['Respuesta'].= $laInt['DESINT'];
						} elseif($laInt['CNLINT']>=501 && $laInt['CNLINT']<=1000){
							$this->aVar['AnalisisEpi'].= $laInt['DESINT'];
						}elseif($laInt['CNLINT']>=5001 && $laInt['CNLINT']<=9000){
							$this->aVar['TextoPandemia'].= $laInt['DESINT'];
						}elseif($laInt['CNLINT']=90000){
							$this->aVar['DiagnosticoPrincipal']= substr($laInt['DESINT'], 0, 6);
						}
						$this->aVar['FechaRta'] = $laInt['FECINT'];
						$this->aVar['HoraRta'] = $laInt['HORINT'];
						$this->aVar['UsuarioResponde'] = trim($laInt['USRINT']);
						$this->aVar['ConEvoRta'] = $laInt['CONINT'];

						break;

				}
			}
			$this->aVar['Solicitud'] = trim($this->aVar['Solicitud']);
			$this->aVar['Respuesta'] = trim($this->aVar['Respuesta']);
			$this->aVar['AnalisisEpi'] = trim($this->aVar['AnalisisEpi']);
			$this->aVar['TextoPandemia'] = trim($this->aVar['TextoPandemia']);
			$this->aVar['DiagnosticoPrincipal'] = trim($this->aVar['DiagnosticoPrincipal']);


			
			//// DATOS DEL ESTUDIANTE QUE CONTESTO EL AVAL
			$this->aVar["estudiante"] = $laInt['USRRIC'];
			$this->aVar["fecha_aval_estu"] = $laInt['FECRIC'];
			$this->aVar["hora_aval_estu"] = $laInt['HORRIC'];

			$this->consultaSeguimientos($taData);

			// MEDICO ORDENO
			if(!empty($this->aVar['UsuarioOrdena'])){
				$this->oMedicoOrdeno = new UsuarioRegMedico();
				if($this->oMedicoOrdeno->cargarUsuario($this->aVar['UsuarioOrdena']))
					$this->oMedicoOrdeno->cargarEspecialidad($this->oMedicoOrdeno->getCodEspecialidad());
			}

			// MEDICO RESPONDE
			if(!empty($this->aVar['UsuarioResponde'])){
				$this->oMedicoRealizo = new UsuarioRegMedico();
				$this->oMedicoRealizo->cargarUsuario($this->aVar['UsuarioResponde']);

				// Utiliza la especialidad almacenada en riaord.codord
				$laEsp = $this->oDb
					->select('CODORD')->from('RIAORD')
					->where([
						'NINORD'=>$taData['nIngreso'],
						'CCIORD'=>$taData['nConsecCita'],
						'COAORD'=>$taData['cCUP'],
					])
					->get('array');
				if(is_array($laEsp))
					$this->oMedicoRealizo->cargarEspecialidad($laEsp['CODORD']);
				$lcTipoUsuario = $this->oMedicoRealizo->getTipoUsuario();
			}

			if(!empty($lcTipoUsuario)){
				$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'EVOLUC', ['CL1TMA'=>'TIPFIRM','CL2TMA'=>$lcTipoUsuario, 'ESTTMA'=>'']);
				$this->aVar['TipoUsuario'] = trim(AplicacionFunciones::getValue($oTabmae, 'DE2TMA', ''));
			}
		}
	}

	private function consultaSeguimientos($taData)
	{
		$laSegs = $this->oDb
			->select('CONINT,DESINT')
			->from('INTSEGL02')
			->where([
				'INGINT'=>$taData['nIngreso'],
				'CCIINT'=>$taData['nConsecCita'],
			])
			->orderBy('CONINT, INDINT, LININT')
			->getAll('array');

		if(is_array($laSegs)){
			if(count($laSegs)>0){
				$lnCon = $laSegs[0]['CONINT'];
				foreach($laSegs as $laSeg){
					$lcDsc = '';
					if($laSeg['CONINT']>$lnCon && !empty($this->aVar['Seguimientos'])){
						$this->aVar['Seguimientos'] = trim($this->aVar['Seguimientos']) . PHP_EOL;
						$lnCon = $laSeg['CONINT'];
					}
					$lcDsc = rtrim($laSeg['DESINT']);
					$lcDsc = strlen($lcDsc)>218 ? $laSeg['DESINT'] : $lcDsc . PHP_EOL;
					$this->aVar['Seguimientos'] .= $lcDsc;
				}
			}
		}
	}

	private function prepararInforme($taData)
	{

		$laTr = [];
		
	
		// SOLICITUD
		$laTr[] = ['saltol', 2];
		$laTr[] = ['titulo1', 'SOLICITUD DE INTERCONSULTA']; //, 'C'
		$laTr[] = ['saltol', 1];
		$lcFechaHora = AplicacionFunciones::formatFechaHora('fechahora12',$taData['oCitaProc']->nFechaRealiza.$taData['oCitaProc']->nHoraRealiza,'/',':',' a las ');
		$lcEspRealiza = is_object($this->oMedicoRealizo)? $this->oMedicoRealizo->getDscEspecialidad(): '';
		$lcTipoSol = mb_strtolower($this->aVar['TipoInterc'],'UTF-8');
		$lcPrioridad = empty($this->aVar['IntPrioridad']) ? '' : ' - Prioridad: ' . ($this->aVar['IntPrioridad']=='1' ? 'URGENTE' : 'NO URGENTE');
		$lcMedOrdena = ($this->oMedicoOrdeno->cApellido1??'').' '.($this->oMedicoOrdeno->cNombre1??'');
		$lcTxt = "El Dr. {$lcMedOrdena} solicita {$lcTipoSol} a {$lcEspRealiza} {$lcPrioridad}"
				. PHP_EOL . 'Respuesta generada el día ' . $lcFechaHora . PHP_EOL;
		$laTr[] = ['texto9', $lcTxt];

		if(!empty($this->aVar['Solicitud'])){
			$laTr[] = ['titulo3', 'COMENTARIO'];
			$laTr[] = ['texto9', $this->aVar['Solicitud']];
			$laTr[] = ['saltol', 3];
		}

		// RESPUESTA
		if(!empty($this->aVar['TextoPandemia'])){
			$laTr[] = ['titulo3', ''];
			$laTr[] = ['saltol', 1];
			$laTr[] = ['texto9', $this->aVar['TextoPandemia']];
		}
		$laTr[] = ['titulo1', 'RESPUESTA DE INTERCONSULTA'];
		$laTr[] = ['saltol', 1];
		$lcEspMedOrd = is_object($this->oMedicoOrdeno)? $this->oMedicoOrdeno->getDscEspecialidad(): '';
		$lcMedRealiza = ($this->oMedicoRealizo->cApellido1??'').' '.($this->oMedicoRealizo->cNombre1??'');
		$lcTxt = "{$this->aVar['TipoUsuario']} {$lcMedRealiza} responde {$lcTipoSol} a {$lcEspMedOrd}" . PHP_EOL;
		$laTr[] = ['texto9', $lcTxt];

		if(!empty($this->aVar['DiagnosticoPrincipal'])){
			$laTr[] = ['saltol', 2];
			$lcDiagnostico=trim(substr($this->aVar['DiagnosticoPrincipal'], 0, 4));
			$lcTipoDiagnostico=trim(substr($this->aVar['DiagnosticoPrincipal'], 5, 1));
			
			if (!empty($lcDiagnostico)){
				$laTr[] = ['titulo3', 'Diagnóstico principal'];
				$laTr[] = ['saltol', 1];
				$laDescripcionPrincipal = $this->oDb->select(trim('DE2RIP'))->from('RIACIE')->where(['ENFRIP'=>$lcDiagnostico])->get("array");
				$lcDiagnostico = $lcDiagnostico.'-'.trim($laDescripcionPrincipal['DE2RIP']);
				$laTr[] = ['texto9', $lcDiagnostico];
			}	

			if (!empty($lcTipoDiagnostico)){
				$laTr[] = ['titulo3', 'Tipo diagnóstico principal'];
				$laTr[] = ['saltol', 1];
				$lcTipoDiagnostico='B'.$lcTipoDiagnostico;
				$laTipoDescripcionPrincipal = $this->oDb->select(trim('TABDSC'))->from('PRMTAB')
				->where(['TABTIP'=>'TDX'])
				->where(['TABCOD'=>$lcTipoDiagnostico])
				->get("array");
				$lcDescTipoDiagnostico = trim($laTipoDescripcionPrincipal['TABDSC']);
				$laTr[] = ['texto9', $lcDescTipoDiagnostico];
			}	
		}

		$laTr[] = ['titulo3', 'COMENTARIO'];
		$laTr[] = ['saltol', 1];
		$laTr[] = ['texto9', $this->aVar['Respuesta']];

		if(!empty($this->aVar['AnalisisEpi'])){
			$laTr[] = ['titulo3', 'ANÁLISIS PARA EPICRISIS'];
			$laTr[] = ['saltol', 1];
			$laTr[] = ['texto9', $this->aVar['AnalisisEpi']];
		}

		if(!empty($this->aVar['estudiante'])){

			$laEstudiante = $this->datosEstudiante($this->aVar['estudiante']);
			$lcPrefijoUR = $this->oDb->obtenerTabmae1('DE1TMA', 'LIBROHC', "CL1TMA='FIRMATIP' AND CL2TMA='{$laEstudiante['TPMRGM']}' AND ESTTMA=''", null, '');
			
			$laTr[] = ['saltol', 2];
			$laTr[] = ['titulo3', 'Realizado por:'];
			$laTr[] = ['saltol', 1];
			$laTr[] = ['txthtml9', "{$lcPrefijoUR} {$laEstudiante['NNOMED']} {$laEstudiante['NOMMED']} Documento: {$laEstudiante['REGMED']}"];
			$laTr[] = ['saltol', 2];
			$laTr[] = ['titulo3', 'Avalado por:'];
		}

		if(!empty($this->aVar['Seguimientos'])){
			$laTr[] = ['titulo3', 'SEGUIMIENTOS'];
			$laTr[] = ['saltol', 1];
			$laTr[] = ['texto9', $this->aVar['Seguimientos']];
		}

		$laTr[] = ['firmas', [ [ 'usuario'=>$this->aVar['UsuarioResponde'], 'especialidad'=>$lcEspRealiza ], ] ];

		$this->aReporte['aCuerpo'] = $laTr;
	}

	/*
	 *	Crea o establece valores a variables en $this->aVar
	 *	@param $taVars: array, lista de variables
	 *	@param $tuValor: valor que deben tomar las variables
	 */
	private function crearVar($taVars=[], $tuValor=null)
	{
		foreach($taVars as $tcVar)
			$this->aVar[$tcVar] = $tuValor;
	}

	public function guardarRespuestasSeguimiento($taData)
	{

		if($this->modoLectura($_SESSION[HCW_NAME]->oUsuario->getTipoUsuario())){
			return ['success'=>false, 'message'=>'No tiene permiso para realizar esta acción'];
		}

		$laRta=['success'=>false, 'message'=>''];
		if (  $taData['tipoTransaccion'] == 'saveInterconsulta') {
			$laRta = $this->guardarRespuestas($taData);
		}
		if ($taData['tipoTransaccion'] == 'saveSeguimiento'){
			$laRta = $this->guardarSeguimiento($taData);
		}
		return $laRta;
	}

	protected function guardarSeguimiento($taData)
	{
		$laRta=['success'=>false];
		if (($taData['DesIntNuevoSeguimiento']<>'') && !($this->EsEstudiante(  $_SESSION[HCW_NAME]->oUsuario->getTipoUsuario() ))){
			$lcFechaHora = $this->oDb->fechaHoraSistema();
			$this->dFechaHoraRta = new \DateTime($lcFechaHora);
			$liConsecutivo = Consecutivos::fCalcularSeguimientoInterconsulta($taData['IngInt'],$taData['CorInt']);
			$lsFilaEncabezadoSeguimiento =
				$liConsecutivo.' - '.$this->dFechaHoraRta->format('Y/m/d').' '.$this->dFechaHoraRta->format('H:i:s').' - '.
				'Habitación: '.$taData['cHabita'].' - '.strtoupper($_SESSION[HCW_NAME]->oUsuario->getNombreCompleto()).' - '.
				'Reg.Med. '.strtoupper($taData['RegMedicoActual']).' - '.strtoupper($taData['NombreEspecialidadMedicoActual']);
			$i = 0;
			$lsAux = "";
			$taPorcionesTexto = AplicacionFunciones::mb_str_split($taData['DesIntNuevoSeguimiento'], 220);
				do {
					if ($i==0){
						$lsAux = $lsFilaEncabezadoSeguimiento;
					}else{
						$lsAux = $taPorcionesTexto[$i-1];
					}
					$laDataPorcion = [
						'INGINT' => $taData['IngInt'],
						'CCIINT' => $taData['CorInt'],
						'CONINT' => $liConsecutivo,
						'INDINT' => $i+1,
						'LININT' => '1', // CONSTANTE?
						'DESINT' => $lsAux,
						'USRINT' => $_SESSION[HCW_NAME]->oUsuario->getUsuario(),
						'PGMINT' => 'HISINTWEB',
						'FECINT' => $this->dFechaHoraRta->format('Ymd'),
						'HORINT' => $this->dFechaHoraRta->format('His')
					];
					if ($this->oDb->tabla('INTSEGL01')->insertar($laDataPorcion, false)) {
						$laRta = ['success'=>true];
					} else {
						$laRta = ['success'=>false, 'message'=>'Ocurrió un error al registrar Seguimiento'];
					}
					$i++;
				} while ( ($i <= count($taPorcionesTexto) )  );
		}else{
			$laRta = ['success'=>false, 'message'=>'No se recibió el texto para guardar el Seguimiento'];
		}
		return $laRta;
	}

	private function datosEstudiante($lcUsuarioRealiza){
		return $this->oDb->select('NNOMED,NOMMED,REGMED,TPMRGM,TABDSC,CODRGM,DESESP')
		->from('RIARGMN')
		->leftJoin('PRMTAB', "TABTIP='TUS' AND TABCOD<>'' AND TPMRGM=INTEGER(TABCOD)")
		->leftJoin('RIAESPE', 'CODRGM=CODESP')
		->where('USUARI','=',trim($lcUsuarioRealiza))
		->get('array');	 
	}

	private function guardarRespuestas($taData)
	{

		$lvalidarEstado = $this->validarEstadoInterconsulta($taData["IngInt"], $taData['CorInt']);
		
		if($lvalidarEstado){
			return ['success'=>false, 'message'=>'En el momento la interconsulta esta cerrada y cobrada'];
		}

		$laRta=['success'=>true, 'message'=>''];

		$lc_tipoInterconsulta='';
		$i=0;
		foreach($this->aTipos as $tipo ) {
			if ($tipo['tipo']==2){
				$tmpArray = array_keys($this->aTipos);
				$lc_tipoInterconsulta = $tmpArray[$i];
			} $i++;
		}
		$lcFechaHora = $this->oDb->fechaHoraSistema();
		$this->dFechaHoraRta = new \DateTime($lcFechaHora);
		if ((($taData['DesInt']!='')&&($taData['DesIntEpi']!='')) ){

			if ( $this->EsEstudiante(  $_SESSION[HCW_NAME]->oUsuario->getTipoUsuario() )){
				$lbRespuesta = $this->guardarRtasEstudiante($taData);

				return $lbRespuesta;
			}else{
				
			$lnConsecutivoEvoluc = (new Consecutivos())->obtenerConsecEvolucion([
				'ingreso' 	=> 	$taData['IngInt'],
				'seccion' 	=> 	$taData['cSeccion'],
				'cama' 		=> 	$taData['cHabita'],
				'usuario' 	=> 	$_SESSION[HCW_NAME]->oUsuario->getUsuario(),
				'programa' 	=> 	'HISINTWEB',
				'estado' 	=>  8
			]);

			if($lnConsecutivoEvoluc){

				$laRta = $this->CrearRegistroInfectologia($taData);
				if(!$laRta){
					return ['success'=>false, 'message'=>'No se pudo guardar la información de infectología'];
				}
				
				$taData['ConInt'] = $lnConsecutivoEvoluc;

				$this->guardarRespuestaInterCon($taData, 'TextoPandemia', 	$lc_tipoInterconsulta);
				$this->guardarRespuestaInterCon($taData, 'DesInt', 			$lc_tipoInterconsulta);
				$this->guardarRespuestaInterCon($taData, 'DesIntEpi', 		$lc_tipoInterconsulta);

				$lsTextoCabEvol = $lnConsecutivoEvoluc." - ".$this->dFechaHoraRta->format('Y/m/d').' '.$this->dFechaHoraRta->format('h:i:s A').' Hab: '.$taData['cSeccion'].'-'.$taData['cHabita'];
				$this->guardarRegEvoluc($lsTextoCabEvol, 				$lnConsecutivoEvoluc,$taData['IngInt'],$taData['CorInt']);
				$this->guardarRegEvoluc(chr(13).'SOLICITUD DE INTERCONSULTA'.chr(13), 	$lnConsecutivoEvoluc,$taData['IngInt'],$taData['CorInt']);
				$this->guardarRegistrosEvoluc($taData,'DesOrd',			$lnConsecutivoEvoluc);
				$this->guardarRegistrosEvoluc($taData,'TextoPandemia',	$lnConsecutivoEvoluc);
				$this->guardarRegEvoluc(chr(13).'RESPUESTA DE INTERCONSULTA'.chr(13), 	$lnConsecutivoEvoluc,$taData['IngInt'],$taData['CorInt']);
				$this->guardarRegistrosEvoluc($taData,'DesInt',			$lnConsecutivoEvoluc);
				$this->guardarRegEvoluc(chr(13).'ANALISIS PARA EPICRISIS'.chr(13), 		$lnConsecutivoEvoluc,$taData['IngInt'],$taData['CorInt']);
				$this->guardarRegistrosEvoluc($taData,'DesIntEpi',		$lnConsecutivoEvoluc);
				$lnTipoUsuario = $_SESSION[HCW_NAME]->oUsuario->getTipoUsuario();
				$lcPrefijo = $this->oDb->obtenerTabmae1('DE1TMA', 'LIBROHC', "CL1TMA='FIRMATIP' AND CL2TMA='{$lnTipoUsuario}' AND ESTTMA=''", null, '');
				$lsTextoCabEvol = "  $lcPrefijo ".strtoupper($_SESSION[HCW_NAME]->oUsuario->getNombreCompleto()).' - RME: '.$taData['RegMedicoActual'].' '.strtoupper($taData['NombreEspecialidadMedicoActual']);
				$this->guardarRegEvoluc($lsTextoCabEvol, $lnConsecutivoEvoluc,$taData['IngInt'],$taData['CorInt']);
				$this->conRegistrosEvoluc = 900009;
				$lsTextoCabEvol = ' '.$taData['EspecialidadMedicoActual'].' - '.$taData['cSeccion'].'-'.$taData['cHabita'];
				$this->guardarRegEvoluc($lsTextoCabEvol, $lnConsecutivoEvoluc,$taData['IngInt'],$taData['CorInt']);

				$laRta= $this->insertarAnaEpi($taData, $lnConsecutivoEvoluc);

				if(!$laRta){
					return ['success'=>false, 'message'=>'Error al guardar el análisis de epicrisis'];
				}

				$laRta = $this->oDb->from('RIAORD')
					->where([
						'NINORD' => $taData['IngInt'],
						'CCIORD' => $taData['CorInt']
					])->actualizar([
						'ESTORD' => '3',
						'RMRORD'=> $taData['RegMedicoActual'],
						'FERORD'=> $this->dFechaHoraRta->format('Ymd'),
						'HRLORD'=> $this->dFechaHoraRta->format('His'),
						'FMOORD'=> $this->dFechaHoraRta->format('Ymd'),
						'HMOORD'=> $this->dFechaHoraRta->format('His'),
						'UMOORD'=> $_SESSION[HCW_NAME]->oUsuario->getUsuario(),
						'PMOORD'=> 'HISINTWEB'
					]);


				if($laRta){
					$this->insertarRiadet($taData);
					$laRta= $this->cobrarProcedimientoInterconsulta($taData);
					if(!$laRta){
						$laRta=['success'=>false, 'message'=>'Error al realizar el cobro del procedimiento'];
					}
				}	


				// solamente actualiza Respuestas de Estudiantes si esta en la interfaz de Avales
				if($taData['interfaz']=='aval'){
					$this->actualizarRtasEstudiante($taData['IngInt'],$lnConsecutivoEvoluc,$taData['CorInt'],$taData['ConsRtaEst']);
				}

				$lcTraslado = 'N';
	
				// gestionar Traslado de Paciente
				if ($taData['AceptaTraslado']=='SI'){
					$this->oDb->from('RIAINGT')
					->where(
						['NIGINT' => $taData['IngInt'] ]
					)
					->actualizar([
						'DPTINT' => $taData['EspecialidadMedicoActual'],
						'MEDINT' => $taData['RegMedicoActual'],
						'UMOINT' => $_SESSION[HCW_NAME]->oUsuario->getUsuario(),
						'PMOINT' => 'HISINTWEB',
						'FMOINT' => $this->dFechaHoraRta->format('Ymd'),
						'HMOINT' => $this->dFechaHoraRta->format('His')
					]);
					$lcTraslado = 'S';
				}

				$this->oDb->tabla('RIAINGTD')
				->insertar([
					'INGDME' => $taData['IngInt'],
					'SECDME' => $taData['cSeccion'],
					'CAMDME' => $taData['cHabita'],
					'MEDDME' => $taData['RegMedicoActual'],
					'FTRDME' => $this->dFechaHoraRta->format('Ymd'),
					'HTRDME' => $this->dFechaHoraRta->format('His'),
					'CNSDME' => 1,
					'CLIDME' => 1,
					'TIIDME' => 'RI',
					'AMTDME' => $lcTraslado,
					'USRDME' => $_SESSION[HCW_NAME]->oUsuario->getUsuario(),
					'PGMDME' => 'HISINTWEB',
					'FECDME' => $this->dFechaHoraRta->format('Ymd'),
					'HORDME' => $this->dFechaHoraRta->format('His')
				], false);


				$laRta=['consecutivo'=>$lnConsecutivoEvoluc, 'fechahora'=>$this->dFechaHoraRta];
			} // si consecutivo Evolucion
		}	// si es profesor
		}else{
			$laRta=['success'=>false, 'message'=>'No se recibieron Respuesta y Análisis para Epicrisis'];
		}
		return $laRta;
	}

	private function guardarRegistrosEvoluc($taData,$lsCampoTexto,$tnConsecutivoEvoluc)
	{
		$laRta=['success'=>false];
		$taPorcionesTexto = AplicacionFunciones::mb_str_split($taData[$lsCampoTexto],220);
		foreach ($taPorcionesTexto as $lsPorcTxt) {
			$this->guardarRegEvoluc($lsPorcTxt, $tnConsecutivoEvoluc,$taData['IngInt'],$taData['CorInt']);
		} ;
		return $laRta;
	}

	private function guardarRegEvoluc($lsPorcionDesc,$tnConsecEvoluc,$IngInt,$CorInt)
	{
		$laRta=['success'=>false];
		$lcFechaHora = $this->oDb->fechaHoraSistema();
		$this->dFechaHoraRta = new \DateTime($lcFechaHora);
			$this->conRegistrosEvoluc++;
			$laDataPorcionEvolucion = [
				'NINEVL' => $IngInt,
				'CONEVL' => $tnConsecEvoluc,
				'CCIEVL' => $CorInt,
				'CNLEVL' => $this->conRegistrosEvoluc,
				'DESEVL' => $lsPorcionDesc,
				'USREVL' => $_SESSION[HCW_NAME]->oUsuario->getUsuario(),
				'PGMEVL' => 'HISINTWEB',
				'FECEVL' =>  $this->dFechaHoraRta->format('Ymd'),
				'HOREVL' =>  $this->dFechaHoraRta->format('His'),
				'UMOEVL' => '',
				'PMOEVL' => '', //
				'FMOEVL' => 0,	//
				'HMOEVL' => 0  	//
			];
			if ($this->oDb->tabla('EVOLUC')->insertar($laDataPorcionEvolucion, false)) {
				$laRta = ['success'=>true];
			} else {
				$laRta = ['success'=>false, 'message'=>'Ocurrió un error al registrar Seguimiento'];
			}
		return $laRta;
	}



	private function guardarRegRtaEstudia($taData, $lsCampoTexto, $tsEncabezado,$lnConsecRtaEstudia,$lnConsecIni, $lbSepararArray)
	{
		$taPorcionesTexto =[];
		if($lbSepararArray){
			$taPorcionesTexto = AplicacionFunciones::mb_str_split($taData[$lsCampoTexto], 500);
		}
		else{
			array_push($taPorcionesTexto,$taData[$lsCampoTexto]);
		}

		
		foreach($taPorcionesTexto as $taPorcion){
			$laDataPorcion = [
				'INGRID' => $taData['IngInt'],
				'TIPRID' =>	'RI',
				'CONRID' => $lnConsecRtaEstudia,
				'CEXRID' => $taData['CorInt'],
				'CORRID' => $taData['numCUP'],
				'CLIRID' => $lnConsecIni,
				'INDRID' => 0,
				'IN2RID' => 0,
				'ESTRID' => 0,
				'DIARID' => $tsEncabezado,
				'DESRID' => $taPorcion,
				'OP1RID' => '',
				'OP2RID' => '',
				'OP3RID' => 0,
				'OP4RID' => 0,
				'OP5RID' => '',
				'OP6RID' => '',
				'OP7RID' => 0,
				'USRRID' => $_SESSION[HCW_NAME]->oUsuario->getUsuario(),
				'PGMRID' => 'HISINTWEB',
				'FECRID' => $this->dFechaHoraRta->format('Ymd'),
				'HORRID' => $this->dFechaHoraRta->format('His'),
				'UMORID' => '',
				'PMORID' => '',
				'FMORID' => '',
				'HMORID' => ''
			];
			$lnConsecIni++;
			if ($this->oDb->tabla('REINDEL01')->insertar($laDataPorcion, false)) {
				$laRta = ['success'=>true];
			} else {
				$laRta = ['success'=>false, 'message'=>'Ocurrió un error al registrar Seguimiento'];
			}
		}
		return $laRta;
	}

	private function guardarRespuestaInterCon($taData, $lsCampoTexto, $lc_tipoInterconsulta)
	{

		$laDataPorcion =[];

		$i = 0;		
		switch ($lsCampoTexto){
			case 'DesInt':
				$lnContadorInfer = 1;
			break;	
			case 'DesIntEpi':
				$lnContadorInfer = 501;
			break;	
			case 'TextoPandemia':			
				$lnContadorInfer = 5001;
			break;
		}	
		do {
			$lsPorcionTexto = substr($taData[$lsCampoTexto], $i*220,220 );
			$laDataPorcion = [
				'INGINT' => $taData['IngInt'],
				'CONINT' => $taData['ConInt'],
				'CORINT' => $taData['CorInt'],
				'CUPINT' => $taData['numCUP'],
				'SORINT' => 'R',
				'OTCINT' => $lc_tipoInterconsulta,
				'CNLINT' => $lnContadorInfer,
				'DESINT' => $lsPorcionTexto,
				'USRINT' => $_SESSION[HCW_NAME]->oUsuario->getUsuario(),
				'PGMINT' => 'HISINTWEB',
				'FECINT' => $this->dFechaHoraRta->format('Ymd'),
				'HORINT' => $this->dFechaHoraRta->format('His'),
				'UMOINT' => '',
				'PMOINT' => '',
				'FMOINT' => 0,
				'HMOINT' => 0
			];
			$lbRta = $this->oDb->tabla('INTCON')->insertar($laDataPorcion, false);
			$i++;
			$lnContadorInfer++;
		} while ( ($i < ceil(strlen($taData[$lsCampoTexto])/220))  || !($lbRta) );

		if(	$lsCampoTexto == 'DesIntEpi' && $lbRta ){
			$lcFormularioDiag = $taData['diagpri'].' '.$taData['tipoDiag'];
			$laDataPorcion["DESINT"] = $lcFormularioDiag;
			$laDataPorcion["CNLINT"] =90000;

			$lbRta = $this->oDb->tabla('INTCON')->insertar($laDataPorcion, false);

			return $lbRta; 
		}

		return $lbRta;
	}

	public function consultarUltimRtasParAvalar($lnIngreso,$lnOrden)
	{
		$lcFechaHora = $this->oDb->fechaHoraSistema();
		$this->dFechaHoraRta = new \DateTime($lcFechaHora);
		$laRta=['success'=>false, 'message'=>''];
		$lnCodMaxRta = 0;
		$lsRespuesta = '';
		$lsAnalisisEpicrisis = '';
		$ConsRtaEst = 0;
		$lnFecMaxRta = '';
		$lnHorMaxRta = '';
		$laRtaMax = $this->oDb
			->select('MAX(CONRID) MAXIMO')
			->from('REINDEL01')
			->where([
				'INGRID'	=> 	intval($lnIngreso),
				'TIPRID'	=> 	'RI',
				'CEXRID'	=>  $lnOrden
			])
			->getAll('array');
		if(is_array($laRtaMax)){
			if(count($laRtaMax)>0){
				$lnCodMaxRta = $laRtaMax[0]['MAXIMO']; settype($lnCodMaxRta,'integer');
				$laRtaFec = $this->oDb
					->select('DISTINCT(FECRID) FECRID, HORRID')
					->from('REINDEL01')
					->where([
						'CONRID' 	=>  $lnCodMaxRta,
						'INGRID'	=> 	intval($lnIngreso),
						'TIPRID'	=> 	'RI',
						'CEXRID'	=>  $lnOrden
					])
					->getAll('array');
				if(is_array($laRtaFec)){
					if(count($laRtaFec)>0){
						$lnFecMaxRta = $laRtaFec[0]['FECRID'];
						$lnHorMaxRta =  str_pad($laRtaFec[0]['HORRID'], 6,'0', STR_PAD_LEFT);
						$ldFechaRta = \DateTime::createFromFormat('Ymd H:i:s', $lnFecMaxRta.' '.substr($lnHorMaxRta,0,2).':'.substr($lnHorMaxRta,2,2).':'.substr($lnHorMaxRta,4,2));
						$ldFechaAhora = \DateTime::createFromFormat('Ymd H:i:s', $this->dFechaHoraRta->format('Ymd').' '.$this->dFechaHoraRta->format('H:i:s') );
						$loDifFecha = $ldFechaRta->diff($ldFechaAhora);
						if($loDifFecha->format('%H')<=24){			// si la fecha de la ultima respuesta es inferior a 24 horas
							$laRtasParaAvalar = $this->oDb
								->select('CLIRID, TRIM(DIARID) AS DIARID, TRIM(DESRID) AS DESRID, CONRID, CEXRID')
								->from('REINDEL01')
								->where([
									'INGRID'	=>	intval($lnIngreso),
									'TIPRID'	=> 'RI',
									'CEXRID'	=>  $lnOrden,
									'CONRID' 	=>  $lnCodMaxRta
								])
								->getAll('array');
							if($this->oDb->numRows()>0){
								foreach($laRtasParaAvalar as $laRta){
									if( mb_strpos($laRta['DIARID'],'RESPUESTA',0,'UTF-8')>-1 ){
										$lsRespuesta  .= $laRta['DESRID'];
									}
									if( mb_strpos($laRta['DIARID'],'EPICRISIS',0,'UTF-8')>-1 ){
										$lsAnalisisEpicrisis  .= $laRta['DESRID'];
									}
									$ConsRtaEst = $laRta['CONRID'];
								}
							}
						}
					}
				}
				unset($laRtaFec);
			}
			unset($laRtaMax);
		}
		$laRta=['UltRtaEstud' => $lsRespuesta,'UltAnaEpicEstud' => $lsAnalisisEpicrisis,'ConsRtaEst' => $ConsRtaEst];
		return $laRta;
	}

	private function actualizarRtasEstudiante($lnIngInt,$lnConsecutivoEvoluc,$lnConsecCita,$lnConsecRta)
	{
		$this->oDb->from('REINCAL01')
			->where([
				'INGRIC' =>	$lnIngInt,
				'TIPRIC' =>	'RI',
				'INDRIC' =>	$lnConsecCita,
				'CONRIC' =>	$lnConsecRta
			])
			->actualizar([
				'ESTRIC' => 'VA',
				'CEVRIC' => $lnConsecutivoEvoluc,
				'UMORIC' => $_SESSION[HCW_NAME]->oUsuario->getUsuario(),
				'PMORIC' => 'HISINTWEB',
				'FMORIC' => $this->dFechaHoraRta->format('Ymd'),
				'HMORIC' => $this->dFechaHoraRta->format('His')
			]);
   }

	private function guardarRtasEstudiante($taData)
	{
		$lnConsecRtaEstudia = Consecutivos::fCalcularConsecutivoEstudiante($taData['IngInt']);
		$laEncabezadoParaEstudiante = [
			'INGRIC' => $taData['IngInt'],
			'TIPRIC' => 'RI',
			'CONRIC' => $lnConsecRtaEstudia,
			'INDRIC' => $taData['CorInt'],
			'ESTRIC' => '', // aval
			'CEVRIC' => 0,  // aval
			'DESRIC' => '',
			'OP1RIC' => '',
			'OP2RIC' => '',
			'OP3RIC' => 0,
			'OP4RIC' => 0,
			'OP5RIC' => '',
			'OP6RIC' => '',
			'OP7RIC' => 0,
			'USRRIC' => $_SESSION[HCW_NAME]->oUsuario->getUsuario(),
			'PGMRIC' => 'HISINTWEB',
			'FECRIC' => $this->dFechaHoraRta->format('Ymd'),
			'HORRIC' => $this->dFechaHoraRta->format('His'),
			'UMORIC' => '', // aval
			'PMORIC' => '', // aval
			'FMORIC' => 0,  // aval
			'HMORIC' => 0   // aval
		];
		if ( $this->oDb->tabla('REINCAL01')->insertar($laEncabezadoParaEstudiante) ) {
			$laRta = ['success'=>true];
			$lbRespuesta = $this->guardarRegRtaEstudia($taData, 'DesInt', 		'RESPUESTA',			$lnConsecRtaEstudia,	1, true);
			$lbRespuesta = $this->guardarRegRtaEstudia($taData, 'DesIntEpi', 	'ANÁLISIS DE EPICRISIS',$lnConsecRtaEstudia,	501, true);
			$lbRespuesta = $this->guardarRegRtaEstudia($taData, 'TextoPandemia','TEXTO PANDEMIA', 		$lnConsecRtaEstudia,	5001, true);
			$lbRespuesta = $this->guardarRegRtaEstudia($taData, 'diagpri','DIAGNÓSTICO PRINCIPAL', 		$lnConsecRtaEstudia,	90000, false);
			$lbRespuesta = $this->guardarRegRtaEstudia($taData, 'tipoDiag','TIPO DIAGNÓSTICO PRINCIPAL', 		$lnConsecRtaEstudia,	90001, false);

			return $lbRespuesta;
		}
	}

	public function tieneOtraEspecialidadPermitida($lnEspecUsuaActual, $lnEspecialidadSolicitada)
	{
		$lbResultado=false;

		$padatos = $this->oDb->obtenerPrmtab('TABDSC','RIN', ['TABCOD'=>$lnEspecUsuaActual, 'TABDSC'=>$lnEspecialidadSolicitada], null);

		if($this->oDb->numRows()>0){
			$lbResultado=true;
		}
		
		return $lbResultado;
	}

	public function esProfesor($liTipoUsuarioActual)
	{
		return !($_SESSION[HCW_NAME]->oUsuario->getRequiereAval());
	}

	public function esEstudiante($liTipoUsuarioActual)
	{
		return $_SESSION[HCW_NAME]->oUsuario->getRequiereAval();
	}

	public function obtieneEspeMedSolilOrden($lnIngreso,$lnOrden)
	{
		$lnEvoOrd = 0;
		$lnCodEspeDesEvl = 0;
		$lnDescEspeDesEvl = '';
		$laRta1 = $this->oDb
			->select('EVOORD')
			->from('RIAORD')
			->where([
				'NINORD' =>	$lnIngreso,
				'CCIORD'=> $lnOrden
			])
			->get('array');
		if(is_array($laRta1)){
			if(count($laRta1)>0){
				$lnEvoOrd = $laRta1['EVOORD']; settype($lnEvoOrd,'integer');
			}
		}
		if ($lnEvoOrd>0){
			$laRta1 = $this->oDb
				->select('DESEVL')
				->from('EVOLUC')
				->where([
					'NINEVL' =>	$lnIngreso,
					'CNLEVL' => 900010,
					'CONEVL' => $lnEvoOrd
					])
				->get('array');
			if(is_array($laRta1)){
				if(count($laRta1)>0){
					$lnCodEspeDesEvl = $laRta1['DESEVL']; settype($lnCodEspeDesEvl,'integer');
				}
			}
			$laRta1 = $this->oDb
				->select('DESESP')
				->from('RIAESPE')
				->where([
					'CODESP' =>	$lnCodEspeDesEvl
					])
				->get('array');
			if(is_array($laRta1)){
				if(count($laRta1)>0){
					$lnDescEspeDesEvl = $laRta1['DESESP']; settype($lnDesEvl,'string');
				}
			}
			if($lnCodEspeDesEvl>0){
				return ['cod'=>$lnCodEspeDesEvl, 'nombre'=> $lnDescEspeDesEvl];
			}else{
				return ['cod'=>false, 'nombre'=> false];
			}
		}else{
			return ['cod'=>false, 'nombre'=> false];
		}
	}

	public function consultarInterConsultaAtendida($laData)
	{
		$laRta1 = $this->oDb
			->select('MAX(CNLEVL) MAXIMO')
			->from('EVOLUC')
			->where([
				'NINEVL'=> $laData['IngInt'],
				'CCIEVL'=> $laData['CorInt']
			])
			->getAll('array');
		if(is_array($laRta1)){
			if(count($laRta1)>0){
				$lnCodMaxEvol = $laRta1[0]['MAXIMO']; settype($lnCodMaxEvol,'integer');
			}
		}
		$laRta2 = $this->oDb
			->select('CONEVL, FECEVL, HOREVL')
			->from('EVOLUC')
			->where([
				'CNLEVL'	=> 	$lnCodMaxEvol,
				'NINEVL'	=>	$laData['IngInt'],
				'CCIEVL'	=>	$laData['CorInt']
			])
			->get('array');
		if(is_array($laRta2)){
			if(count($laRta2)>0){
				$laRta2;
			}else{
				$laRta2 = ['success'=>false, 'message'=>''];
			}
		}
		return $laRta2;
	}

	private function cobrarProcedimientoInterconsulta($ptaData){
		$oCobrar = new Cobros();
		$aInfoCobros = [
			'ingreso'       => $ptaData['IngInt'],
			'numIdPac'      => $ptaData['numIdPac'],
			'codCup'        => $ptaData['numCUP'],
			'codVia'        => $ptaData['cCodVia'],
			'codPlan'       => $ptaData['cPlan'],
			'regMedOrdena'  => str_pad($ptaData['RegMedicoOrdena'], 13,'0', STR_PAD_LEFT),
			'regMedRealiza' => str_pad($ptaData['RegMedicoActual'], 13,'0', STR_PAD_LEFT),
			'espMedRealiza' => $ptaData['EspecialidadMedicoActual'],
			'secCama'       => trim($ptaData['cSeccion']).trim($ptaData['cHabita']),
			'cnsCita'       => $ptaData['CorInt'],
			'portatil'      => '',
		];

		$avalidarCobro = $this->oDb->select("ingest")
		->from("riaestm20")
		->where("ingest", "=", $ptaData['IngInt'])
		->where("cupest", "=", $ptaData['numCUP'] )
		->where("cnoest", "=", $ptaData['CorInt'])
		->get("array");

		if($this->oDb->numRows()< 1){
			$lbRetCobro = $oCobrar->cobrarProcedimiento($aInfoCobros);
			return $lbRetCobro;
		}
		
		$arrayUpdate = array(
            "RMEEST"=>$ptaData['RegMedicoActual'],
            "UMOEST"=>$_SESSION[HCW_NAME]->oUsuario->getUsuario(),
            "PMOEST"=>'HISINTWEB',
            "FMOEST"=>date('Ymd'),
            "HMOEST"=>date('His')
        );
	
        $lbRetCobro= $this->oDb->from("riaestm")
		->where("ingest", "=", $ptaData['IngInt'])
		->where("cupest", "=", $ptaData['numCUP'] )
		->where("cnoest", "=", $ptaData['CorInt'])
        ->actualizar($arrayUpdate);

		return $lbRetCobro;

	}

	private function CrearRegistroInfectologia($pData){

		$oConsecutivo = new Consecutivos;
		$loConsecutivo = $oConsecutivo->fCalcularInfectologia($pData['IngInt']); 

		$result = false;

		$laValidarEspecialidad = $this->oDb->select("tabtip")
		->from("prmtab02")
		->where("tabtip", "=","ESP")
		->where("tabcod", "=",$pData['EspecialidadMedicoActual'])
		->get("array");

		if($this->oDb->numRows()< 1){
			return true;
		}

		$laValidarCabeceraInfectologia= $this->oDb->select("coninc")
		->from("infecal01")
		->where("inginc","=", $pData['IngInt'])
		->get("array");


		$laDatosInfecaCabecera = array(
			'inginc'=>$pData['IngInt'],
			'coninc'=>$loConsecutivo,
			'usrinc'=>$_SESSION[HCW_NAME]->oUsuario->getUsuario(),
			'pgminc'=>'HISINTWEB',
			'fecinc'=>date('Ymd'),
			'horinc'=>date('His')
		);
	
		$result = $this->oDb->from('infecal01')->insertar($laDatosInfecaCabecera);
		

		if($result){
			$result = $this->InsertarDetalleInfectologia($pData, $loConsecutivo);
		}
		
		return $result;	
	}

	private function InsertarDetalleInfectologia($pData, $poConsecutivo){
		$laDatosInsertar=[];
		$desint = $pData['tipoDiag'].' - '. date('Y/m/d h:i:s A').'  Hab.:'.$pData['cSeccion'].'-'.$pData['cHabita'];

		array_push($laDatosInsertar,$desint);
		array_push($laDatosInsertar,'TIPO INTERCONSULTA: '.$pData['tipoConsulta']);
		array_push($laDatosInsertar,'SOLICITUD DE INTERCONSULTA');

		$laDatosInsertar = $this->crearArraysTextos($pData['DesOrd'], $laDatosInsertar);
		$laDatosInsertar = $this->crearArraysTextos($pData['TextoPandemia'], $laDatosInsertar);
		array_push($laDatosInsertar,'RESPUESTA DE INTERCONSULTA');
		$laDatosInsertar = $this->crearArraysTextos($pData['DesInt'], $laDatosInsertar);

		foreach ($laDatosInsertar as $key => $dato) {
			$laDatosInfecaDetalle = array(
				'ingind'=>$pData['IngInt'],
				'conind'=>$poConsecutivo,
				'tipind'=>'INT',
				'habind'=>$pData['cSeccion'].'-'.$pData['cHabita'],
				'usrind'=>$_SESSION[HCW_NAME]->oUsuario->getUsuario(),
				'pgmind'=>'HISINTWEB',
				'fecind'=>date('Ymd'),
				'horind'=>date('His'),
				'linind'=>$key+1,
				'desind'=>$dato,
			);
			$result = $this->oDb->from('infede')->insertar($laDatosInfecaDetalle);
		}

		return $result;

	}

	private function crearArraysTextos($pTexto, $paDatos){
		$laSolicitudConsulta = AplicacionFunciones::mb_str_split(trim($pTexto),220);

		foreach ($laSolicitudConsulta as $key => $Consulta) {
			array_push($paDatos, $Consulta);
		}

		return $paDatos;

	}

	private function insertarAnaEpi($pDatos, $pConsecutivoEvo){

		$lcTIPA="RI";
		$lnINDA=0;
		$laSolicitudConsulta = AplicacionFunciones::mb_str_split(trim($pDatos['DesIntEpi']),220);

		foreach ($laSolicitudConsulta as $key => $value) {
			$laInsertarAnaEpi = array(
				"ingaep"=>$pDatos['IngInt'],
				"tipaep"=>$lcTIPA,
				"cevaep"=>$pConsecutivoEvo,
				"indaep"=>$lnINDA,
				"cnlaep"=>$key + 1,
				"desaep"=>$value,
				"usraep"=>$_SESSION[HCW_NAME]->oUsuario->getUsuario(),
				"pgmaep"=>'HISINTWEB',
				"fecaep"=>date('Ymd'),
				"horaep"=>date('His')
			);

			$result = $this->oDb->from('anaepi')->insertar($laInsertarAnaEpi);
		}

		return $result;
	}

	private function insertarRiadet($pdatos){

		$arrayInser = array(
            "TIDDET"=>$pdatos['Tidord'],
            "NIDDET"=>$pdatos['numIdPac'],
            "INGDET"=>$pdatos['IngInt'],
            "CCIDET"=>$pdatos['CorInt'],
            "CUPDET"=>$pdatos['numCUP'],
            "FERDET"=>date('Ymd'),
            "HRRDET"=>date('His'),
            "ESTDET"=>3,
            "MARDET"=>0,
            "USRDET"=>$_SESSION[HCW_NAME]->oUsuario->getUsuario(),
            "PGMDET"=>'HISINTWEB',
            "FECDET"=>date('Ymd'),
            "HORDET"=>date('His')
        );

        $result= $this->oDb->from("riadet")->insertar($arrayInser);
        return $result;
	}

	public function modoLectura($plcTipoUsuario){
		$lcDatos = $this->oDb->obtenerTabMae1('TRIM(DE2TMA)', 'INTERC', "CL1TMA='CONSULTA'", null);

		$laDatos = explode('|', $lcDatos);

		foreach ($laDatos as $key => $dato) {
			if($plcTipoUsuario == $dato){
				return true;
			}
		}
		return false;

	}

}