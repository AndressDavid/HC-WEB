<?php
	require_once (__DIR__ .'/../../../publico/constantes.php') ;
	require_once (__DIR__ .'/../../../../nucleo/controlador/class.Db.php');
	require_once (__DIR__ .'/../../../../nucleo/controlador/class.AgendaSalasCirugia.php');
	require_once (__DIR__ .'/../../../../nucleo/controlador/class.Paciente.php');
	require_once (__DIR__ .'/../../../../nucleo/controlador/class.AplicacionFunciones.php');	
	require_once (__DIR__ .'/../../../../nucleo/controlador/class.Cup.php');	
	use NUCLEO\Cup;

	$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
	$lnHoyFecha = intval($ltAhora->format("Ymd"));
	$lnHoyHora = intval($ltAhora->format("His"));	
	
	$lcSala = (isset($_GET['selSalasConsulta'])?$_GET['selSalasConsulta']:''); 
	$ldFechaInicio = (isset($_GET['inicio'])?$_GET['inicio']:$ltAhora->format("Y-m-d")); 
	$ldFechaFin = (isset($_GET['fin'])?$_GET['fin']:$ltAhora->format("Y-m-d"));

	$loAgendaSalas = new NUCLEO\AgendaSalasCirugia;
	$loPaciente = new NUCLEO\Paciente;
	
	$laDispositivos = $loAgendaSalas->DispositivosCardiacaSalas();
	$laEquipos = $loAgendaSalas->EquiposEspecialesSala();
	$laLateralidad = $loAgendaSalas->LateralidadSalas();
	$laOrigen = $loAgendaSalas->OrigenSalas();
	$laTipoAnestesia = $loAgendaSalas->TipoAnestesiaSala();
	$laTipoProcedimiento = $loAgendaSalas->NaturalezaSalas();
														
	$laRegistros = $loAgendaSalas->consultaAgendaPaciente($lcSala,intval(str_replace('-','',$ldFechaInicio)), intval(str_replace('-','',$ldFechaFin)));
	$laTabla=array();
	
	if(is_array($laRegistros)){
		if(count($laRegistros)>0){
			foreach($laRegistros as $lnRegistro => $laRegistro){
				$lcProcedimientos=$lcEquiposEspeciales='';
				$laDatos = $loAgendaSalas->consultaDatosAgendamiento($laRegistro['CONSAL']);
				$laProcedimientos=explode('|', $laDatos['cups']);
				$loPaciente->cargarPaciente(trim(strval($laDatos['TIDSAL'])), $laDatos['NIDSAL'], $laDatos['NIGSAL']);
				$laEquiposEspeciales = explode(',',str_replace('-', '', $laDatos['equiposEspeciales']));
				
				foreach($laEquiposEspeciales as $lcEquipoEspecial){
					if (!empty($lcEquipoEspecial)){
						$lcEquiposEspeciales .= (empty($lcEquiposEspeciales)?'':', ').getListaValue($laEquipos, $lcEquipoEspecial);
					}	
				}				
				
				foreach($laProcedimientos as $lcProcedimiento){
					$lcDescripcionCups='';
					if (!empty($lcProcedimiento)){
						$lcCodigoCups = explode('~', $lcProcedimiento)[0];
						$loCup = new Cup($lcCodigoCups);
						$lcDescripcionCups=trim($loCup->cDscrCup);
						
					}	
					$lcProcedimientos .= (empty($lcProcedimientos)?'':'<br>') .$lcCodigoCups .'-' .$lcDescripcionCups;
				}	
				
				$laTabla[]=[
							'CONSECUTIVO'=>$laRegistro['CONSAL'],
							'SALA'=>str_replace(' ','',$laRegistro['SALSAL']),
							'FUTURA'=>(intval($laRegistro['FPRSAL'])>=$lnHoyFecha && intval($laRegistro['HPRSAL'])>=$lnHoyHora?'S':$laRegistro['FPRSAL']."-".$laRegistro['HPRSAL']),
							'FECHAHORA'=>strval($laRegistro['FPRSAL']).str_pad(strval($laRegistro['HPRSAL']),6,"0",STR_PAD_LEFT),
							'FECHA'=>NUCLEO\AplicacionFunciones::formatFechaHora($tcFormato='fecha', intval($laRegistro['FPRSAL'])),
							'HORA'=>NUCLEO\AplicacionFunciones::formatFechaHora($tcFormato='hora', intval(str_pad(strval($laRegistro['HPRSAL']),6,"0",STR_PAD_LEFT))),
							'ID'=>Sprintf('%s-%s',$loPaciente->aTipoId['TIPO'],$loPaciente->nId),
							'INGRESO'=>$laDatos['NIGSAL'],
							'ENTIDAD'=>$laDatos['ENTIDAD'],
							'EDAD'=>$loPaciente->getEdad(),
							'DISPOSITIVO'=>getListaValue($laDispositivos,$laDatos['dispositivoCardiaca']),
							'NOMBRE'=>strtoupper($loPaciente->getNombreCompleto()),
							'ORIGEN'=>getListaValue($laOrigen,$laDatos['origen']),
							'HABITACION'=>$laDatos['HACSAL'],
							'PROCEDIMIENTO'=>$lcProcedimientos,
							'TIPOPROCEDIMIENTO'=>getListaValue($laTipoProcedimiento,$laDatos['tipoProcedimiento']),
							'LATERALIDAD'=>getListaValue($laLateralidad,$laDatos['lateralidad']),
							'TIEMPO'=>getTiempoCups($laDatos['tiempoCups'], $laDatos['tiempoMinutosCups']),
							'CIRUJANO'=>$laRegistro['MEDICO'],
							'ANESTESIOLOGO'=>$laRegistro['ANESTESIOLOGO'],
							'ANESTESIA'=>getListaValue($laTipoAnestesia,$laDatos['tipoAnestesia']),
							'CONTAMINADA'=>$laDatos['cirugiaContaminada'],
							'AYUDANTE'=>$laDatos['ayudanteQuirurgico'],
							'AUTORIZADA'=>$laDatos['autorizada']=='S' ? 'SI' : ($laDatos['autorizada']=='N' ? 'NO' : ''),
							'EQUIPOS'=>$lcEquiposEspeciales
							];
			}
		}
	}
	
	include (__DIR__ .'/../../../publico/headJSON.php');
	print(json_encode($laTabla??''));
										
	function getTiempoCups($lnHoras=0, $lnMinutos=0){
		$lcTiempoCups='';
		$lnHoras=intval($lnHoras);
		$lcTiempoCups=($lnHoras>0?sprintf('%s<sup>H</sup>',$lnHoras):'');
		$lnMinutos=intval($lnMinutos);
		
		return ($lnMinutos>0?$lcTiempoCups.(!empty($lcTiempoCups)?', ':'').sprintf('%s<sup>m</sup>',$lnMinutos):$lcTiempoCups);		
	}
	
	function getListaValue($taLista=array(), $tcValor='', $tcCampoCodigo='CODIGO', $tcCampoDescripcion='DESCRIPCION'){
		if(is_array($taLista)){
			if(count($taLista)>0){
				$lnKey=intval(array_search($tcValor,array_column($taLista,$tcCampoCodigo)));
				
				if(isset($taLista[$lnKey])){
					if(isset($taLista[$lnKey][$tcCampoDescripcion])){
						return $taLista[$lnKey][$tcCampoDescripcion];
					}
				}
			}
		}
		return '?';
	}
	
?>
