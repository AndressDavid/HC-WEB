<?php
namespace NUCLEO;

require_once ('class.AplicacionFunciones.php');
require_once ('class.UsuarioRegMedico.php');
require_once ('class.SignosNews.php');
use NUCLEO\UsuarioRegMedico;
use NUCLEO\SignosNews;


class Doc_AlertaTemprana
{
	protected $oDb;
	protected $aDoc = [];
	protected $oSignos = null;
	protected $aReporte = [
					'cTitulo' => 'REGISTRO DE SIGNOS Y ALERTA TEMPRANA',
					'lMostrarEncabezado' => true,
					'lMostrarFechaRealizado' => false,
					'lMostrarViaCama' => true,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => '',
					'cTxtLuegoDeCup' => '',
					'aCuerpo' => [],
					'aFirmas' => [],
					'aNotas' => ['notas'=>false,],
				];

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
		$this->oSignos = new SignosNews($tbNivelesConcienciaActivos=false);
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
		$laAlerta = $this->oDb
			->select([
				'CONALE',		// CONSECUTIVO REGISTRO
				'NIGING',		// NUMERO INGRESO
				'TIDING',		// TIPO DE DOCUMENTO
				'NIDING',		// NUMERO DE DOCUMENTO
				'SECCIN',		// SECCION
				'HABITA',		// HABITACION
				'VALORA',		// CONSECUTIVO VALORACION
				'VALFEA',		// FECHA VALORACION
				'VALHOA',		// HORA VALORACION
				'VALFES',		// FECHA SIGUIENTE VALORACION
				'VALHOS',		// HORA SIGUIENTE VALORACION
				'ESTADO',		// ESTADO
				'RESFEA',		// FECHA RESPUESTA
				'RESHOA',		// HORA RESPUESTA
				'VAR00N FR',	// FRECUENCIA RESPIRATORIA
				'VAR01N SO2',	// SATURACION OXIGENO
				'VAR02N TEMP',	// TEMPERATURA
				'VAR03N PAS',	// PRESION ARTERIAL SISTOLICA
				'VAR04N FC',	// FRECUENCIA CARDIACA
				'VAR05N NC',	// NIVEL DE CONCIENCIA
				'VAR06N O2SUP',	// OXIGENO SUPLEMENTARIO
				'VAR07N PAD',	// PRESION ARTERIAL DIASTLICA
				'VAR26N QSOFA',	// QSOFA
				'VAR27N QSOFAP',// PUNTAJE QSOFA
				'VAR28N EDAD',	// EDAD
				'VAR29N TIPO',	// TIPO DE ALERTA
				'VAR30N ACTMAN',// ALERTA ACTIVADA MANUAL
				'CLASIF',		// CLASIFICACION VALORACION
				'PNEWS2',		// PUNTAJE NEWS2
				'EQUIPO',		// EQUIPO
				'OBSERV',		// OBSERVACION
				'ACCION',		// ACCION
				'ACCFEC',		// FECHA ACCION
				'ACCHOR',		// HORA ACCION
				'ACCSIL',		// PERMITIR SILENCIAR ALERTAS
				'USCALE',		// USUARIO CREO
				'PGCALE',		// PROGRAMA CREO
				'FECALE',		// FECHA CREO
				'HOCALE',		// HORA CREO
				'USMALE',		// USUARIO
				'PGMALE',		// PROGRAMA MODIFICO
				'FEMALE',		// FECHA MODIFICO
				'HOMALE',		// HORA MODIFICO
				])
			->from('ALETEMP')
			->where([
				'NIGING'=>$taData['nIngreso'],
				'CONALE'=>$taData['nConsecDoc'],
			])
			->get('array');
		if (is_array($laAlerta)){
			if (count($laAlerta)>0){
				$this->aDoc = array_map('trim',$laAlerta);
				$this->oSignos->medir([
					'ingreso'=>$laAlerta['NIGING'],
					'fr'=>$laAlerta['FR'],
					'so2'=>$laAlerta['SO2'],
					't'=>$laAlerta['TEMP'],
					'tas'=>$laAlerta['PAS'],
					'tad'=>$laAlerta['PAD'],
					'fc'=>$laAlerta['FC'],
					'nc'=>$laAlerta['NC'],
					'o2sp'=>$laAlerta['O2SUP'],
				]);
			}
		}
	}


	/*
	 *	Prepara array $aReporte con los datos para imprimir
	 */
	private function prepararInforme($taData)
	{
		if(count($this->aDoc)==0) return;

		// SEGUIMIENTO DE SIGNOS VITALES
		//$laTr['aCuerpo'][] = ['titulo1', "SEGUIMIENTO DE SIGNOS VITALES No. {$this->aDoc['CONALE']} INGRESO {$this->aDoc['NIGING']}"];
		$laTr['aCuerpo'][] = ['titulo1', "SEGUIMIENTO DE SIGNOS VITALES No. {$this->aDoc['CONALE']}"];
		$laTr['aCuerpo'][] = ['texto9', 'Fecha y hora: '.AplicacionFunciones::formatFechaHora('fechahora', $this->aDoc['VALFEA'].$this->aDoc['VALHOA'])];

		$laTr['aCuerpo'][] = ['titulo3', 'Decisión Clínica'];
		$laRespuesta = $this->oSignos->getRespuesta();
		$laRespuestaQSOFA = $this->oSignos->getRespuestaQSOFA();
		$lcTiempo = $laRespuesta['decision']['tiempo']>0 ? "Respuesta en menos de {$laRespuesta['decision']['tiempo']} minutos, " : '' ;
		$lcArea = empty($laRespuesta['decision']['area']) ? '' : $laRespuesta['decision']['area'].', ';
		$lcNivel = empty($laRespuesta['decision']['nivel']) ? '' : "Nivel de atención: {$laRespuesta['decision']['nivel']}, ";
		$lcDesicion = "<b>{$laRespuesta['decision']['respuesta']}</b><br/>{$lcTiempo}{$lcArea}{$lcNivel}{$laRespuestaQSOFA['decision']['respuesta']}.";
		$laTr['aCuerpo'][] = ['txthtml9', $lcDesicion];

		$laTr['aCuerpo'][] = ['titulo3', 'Puntaje de Alerta Temprana'];
		$lnAncho1=60; $lnAncho2=25;
		$laAnchoCol = [$lnAncho1,$lnAncho2,$lnAncho2,$lnAncho2];
		$laAlinea = ['L','C','C','C'];
		$laSignos = $this->oSignos->getSignos();
		$laFilas = [];
		foreach($laSignos as $lcSigno => $laSigno){
			$laFilas[] = [ 'w'=>$laAnchoCol, 'a'=>$laAlinea,
				'd'=>[
					$laSigno['titulo'],
					$laSigno['tipo']=='select' ? ($laSigno['valores'][$laSigno['valor']]['NOMBRE'] ?? "") : $laSigno['valor']??'',
					$laSigno['puntaje'],
					(isset($laSigno['puntajeQSOFA'])?$laSigno['puntajeQSOFA']:'--'),
				]
			];
		}
		$laFilas[] = [
			'w'=>[$lnAncho1+$lnAncho2,$lnAncho2,$lnAncho2],
			'a'=>['L','C','C',],
			'd'=>['<b>TOTALES</b>','<b>'.$this->oSignos->getPuntaje().'</b>','<b>'.$this->oSignos->getPuntajeQSOFA().'</b>'],
		];
		$laTr['aCuerpo'][] = ['tabla', [
				[ 'w'=>$laAnchoCol, 'a'=>$laAlinea, 'd'=>['Parámetro','Medición','NEWS','qSOFA',] ]
			],	$laFilas, ['l'=>(190-$lnAncho1-$lnAncho2*3)/2] ];

		// REGISTRO DE LA ACCIÓN
		if($this->aDoc['ACCION']>0){
			$laTr['aCuerpo'][] = ['saltol', 5];
			$laTr['aCuerpo'][] = ['titulo1', 'REGISTRO DE LA ACCIÓN'];
			$laTr['aCuerpo'][] = ['texto9', 'Fecha y hora: '.AplicacionFunciones::formatFechaHora('fechahora', $this->aDoc['ACCFEC'].$this->aDoc['ACCHOR'])];

			$laTr['aCuerpo'][] = ['titulo3', 'Integrantes del equipo de respuesta rápida'];
			$loUsuario = new UsuarioRegMedico();
			$laEquipo = explode(',', $this->aDoc['EQUIPO']);
			$lcEquipo = $lcSalto = '';
			foreach($laEquipo as $lcUsuario){
				$loUsuario->cargarUsuario($lcUsuario);
				$lcEquipo .= $lcSalto.$loUsuario->cNombre1.' '.$loUsuario->cApellido1;
				$lcSalto = PHP_EOL;
			}
			$laTr['aCuerpo'][] = ['texto9', $lcEquipo];

			$laTr['aCuerpo'][] = ['titulo3', 'Conducta a seguir con la alerta'];
			$laConductas = $this->oSignos->getConductas();
			$laTr['aCuerpo'][] = ['texto9', $laConductas[$this->aDoc['ACCION']]['NOMBRE']];
/*
			$laTr['aCuerpo'][] = ['titulo3', 'Tipo de alerta a responder'];
			$laTr['aCuerpo'][] = ['texto9', $this->aDoc['TIPO']];
*/
			$laTr['aCuerpo'][] = ['titulo3', 'Descripción'];
			$laTr['aCuerpo'][] = ['texto9', $this->aDoc['OBSERV']];
		}


		$this->aReporte = array_merge($this->aReporte, $laTr);
	}
}
