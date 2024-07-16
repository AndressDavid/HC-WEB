<?php

namespace NUCLEO;

require_once (__DIR__ . '/class.SignosNews.php');
require_once (__DIR__ . '/class.NivelesConciencia.php');
require_once (__DIR__ . '/class.AplicacionFunciones.php');
require_once (__DIR__ . '/class.SeccionesHabitacion.php');
require_once (__DIR__ . '/class.Db.php');

use NUCLEO\SignosNews;
use NUCLEO\NivelesConciencia;
use NUCLEO\AplicacionFunciones;
use NUCLEO\SeccionesHabitacion;
use NUCLEO\Db;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;

class SignosNewsConsulta
{
	public $nIngreso;
	public $nFechaDesde;
	public $nFechaHasta;
	public $aSignos;
	public $nNumRegistros=0;
	public $nPagina=1;
	public $nTotalPaginas=0;
	public $nRegPorPag=50;
	public $cOrden='VALFEA';
	public $cDirOrden='ASC';
	public $aCampos=[];


	function __construct()
	{
		$this->aCampos=$this->camposConsulta();
	}

	public function consultar($tbTodo=false)
	{
		global $goDb;
		$lcTabla = 'ALETEMP';

		$laReg = $goDb->count('CONALE','TOTAL')->tabla($lcTabla);
		if (!empty($this->nIngreso)) $goDb->where(['NIGING'=>$this->nIngreso]);
		if (!empty($this->nFechaDesde)) $goDb->where('VALFEA>='.$this->nFechaDesde);
		if (!empty($this->nFechaHasta)) $goDb->where('VALFEA<='.$this->nFechaHasta);
		$laReg = $goDb->get('array');
		$this->nNumRegistros = is_array($laReg) ? $laReg['TOTAL'] : 0;

		$this->nRegPorPag = is_numeric($this->nRegPorPag) ? $this->nRegPorPag : 25;
		$this->nTotalPaginas = ceil($this->nNumRegistros / $this->nRegPorPag);
		$this->nPagina = $this->nPagina > $this->nTotalPaginas ? $this->nTotalPaginas : ($this->nTotalPaginas==0 ? 1 : $this->nPagina);
		if (empty($this->cOrden)) $this->cOrden='VALFEA';

		if ($this->nNumRegistros>0) {
			//$goDb->select(array_keys($this->aCampos))->tabla($lcTabla);
			$laLstCampos=[];
			foreach($this->aCampos as $lcCampo=>$laCampo){
				if ($laCampo['consultar']) $laLstCampos[]=$lcCampo;
			}
			$goDb->select($laLstCampos)->tabla($lcTabla);
			//if (!empty($this->nIngreso)) $goDb->where(['NIGING'=>$this->nIngreso]);
			if (!empty($this->nIngreso)) $goDb->where('NIGING','=',$this->nIngreso);
			if (!empty($this->nFechaDesde)) $goDb->where('VALFEA','>=',$this->nFechaDesde);
			if (!empty($this->nFechaHasta)) $goDb->where('VALFEA','<=',$this->nFechaHasta);
			if (!empty($this->cOrden)) {
				if (in_array($this->cOrden, ['VALFEA','VALHOA'])) {
					$goDb->orderBy('VALFEA '.$this->cDirOrden.', VALHOA '.$this->cDirOrden);
				} else {
					$goDb->orderBy($this->cOrden.' '.$this->cDirOrden);
				}
			}
			if (!$tbTodo) $goDb->pagination($this->nRegPorPag, $this->nPagina);
			$laSignos = $goDb->getAll('array');
			$this->aSignos = is_array($laSignos) ? $laSignos : [];
		} else {
			$this->aSignos = [];
		}

		return $this->aSignos;
	}


	public function exportar()
	{
		// Consulta datos
		$laDatos=$this->consultar(true);

		if (count($laDatos)>0) {

			// Nombre del archivo
			$lcArchivo = 'AlertasTempr_'.date('Y-m-d-H-i-s').'.xlsx';

			// Títulos
			$laTitulos=[];
			foreach ($this->aCampos as $lcCampo=>$laCampo){
				if ($laCampo['exportar']) {
					$laTitulos[]=$laCampo['titulo'];
				}
			}
			$laTitulos[]='Tiempo Rta (min)';

			// Crear libro y establecer propiedades
			require __DIR__ .'/../publico/complementos/spout/3.0.1/Spout/Autoloader/autoload.php';
			$loLibro = WriterEntityFactory::createXLSXWriter();
			$loLibro->openToBrowser($lcArchivo);

			// Estilos para los títulos
			$loBorderTitulo = (new BorderBuilder())
				->setBorderAll(Color::BLUE, Border::WIDTH_THIN, Border::STYLE_SOLID)
				->build();
			$loStyleTitulo = (new StyleBuilder())
				->setBackgroundColor('E2E3E5')
				->setFontColor(Color::BLACK)
				->setFontBold()
				->setBorder($loBorderTitulo)
				->build();

			// Estilos para alertas
			$laRespuestas=(new SignosNews())->getRespuestas();
			foreach ($laRespuestas as $lnIndex=>$laRta) {
				$loStyleAlerta[$lnIndex]=(new StyleBuilder())
					->setBackgroundColor($laRta['colorh'])
					->build();
			}
			$loStyleSigno = (new StyleBuilder())
				->setFontColor(Color::DARK_RED)
				->setFontBold()
				->build();

			// Coloca los datos en la hoja actual
			$loHoja = $loLibro->getCurrentSheet();
			$loHoja->setName('SignosAlertasTempranas');
			$loFila = WriterEntityFactory::createRowFromArray($laTitulos, $loStyleTitulo);
			$loLibro->addRow($loFila);

			// Crear datos para exportar
			foreach($laDatos as $lnFila=>$laDato){
				$loCeldas=[];
				foreach($this->aCampos as $lcCampo=>$laCampo){
					if ($laCampo['exportar']) {
						if (isset($laCampo['lista'])) {
							$lcIndex = sprintf($laCampo['formato'], $laDato[$lcCampo]);
							$lcDato = trim($laCampo['lista'][$lcIndex]['NOMBRE']??'');

						} elseif (isset($laCampo['funcion'])) {
							$lcCmd = '$lcVal='.sprintf($laCampo['funcion'], $laDato[$lcCampo]).';';
							eval($lcCmd);
							$lcDato = $lcVal;

						} elseif (isset($laCampo['tipo'])) {
							switch($laCampo['tipo']){
								case 'hora':
								case 'fecha':
									$lcDato = AplicacionFunciones::formatFechaHora($laCampo['tipo'],$laDato[$lcCampo]);
									break;
								default:
									$lcDato = trim(sprintf($laCampo['formato'], $laDato[$lcCampo]));
							}
						} else {
							$lcDato = trim(sprintf($laCampo['formato'], $laDato[$lcCampo]));
						}
						$lnPuntaje=0;
						if (isset($laCampo['alerta'])) {
							$lnPuntaje=$laCampo['alerta']['default'];
							foreach($laCampo['alerta']['reglas'] as $laRegla) {
								if ($laDato[$lcCampo]>=$laRegla['min'] && $laDato[$lcCampo]<=$laRegla['max']){
									$lnPuntaje=$laRegla['val'];
								}
							}
						}
						if ($lnPuntaje>1) {
							$loCeldas[]=WriterEntityFactory::createCell($lcDato, $loStyleSigno);
						} else {
							$loCeldas[]=WriterEntityFactory::createCell($lcDato);
						}
					}
				}
				// tiempo de respuesta
				if ($laDato['RESFEA']>0) {
					$ltFecHorVal = new \DateTime(AplicacionFunciones::formatFechaHora('fechahora',$laDato['VALFEA'] . $laDato['VALHOA'], '-', ':', 'T'));
					$ltFecHorRta = new \DateTime(AplicacionFunciones::formatFechaHora('fechahora',$laDato['RESFEA'] . $laDato['RESHOA'], '-', ':', 'T'));
					$loDiff = $ltFecHorVal->diff($ltFecHorRta);
					$lcTiempoRta=$loDiff->days*24*60 + $loDiff->h*60 + $loDiff->i;

				} else {
					$lcTiempoRta = '';
				}
				$loCeldas[]=WriterEntityFactory::createCell($lcTiempoRta);
				
				$loFila = WriterEntityFactory::createRow($loCeldas, $loStyleAlerta[$laDato['CLASIF']]);
				$loLibro->addRow($loFila);
			}
			$loLibro->close();
			exit;

		} else {
			echo 'No hay datos para exportar';
		}
	}

	public function getSignos()
	{
		return $this->aSignos;
	}

	private function camposConsulta()
	{
		$laSignos = (new SignosNews())->getSignos();
		$laSecciones=array();
		foreach((new SeccionesHabitacion())->aSecciones as $lcSeccionKey=>$laSeccion){
			$laSecciones[$lcSeccionKey]=array('NOMBRE'=>$lcSeccionKey." - ".ucwords(strtolower($laSeccion['NOMBRE'])));
		}
		return [
			'CONALE' => [
				'titulo'=>'Consec',
				'formato'=>'%s',
				'consultar'=>true,
				'visible'=>false,
				'exportar'=>true,
				],
			'TIDING' => [
				'titulo'=>'Tipo ID',
				'formato'=>'%s',
				'consultar'=>true,
				'visible'=>false,
				'exportar'=>true,
				],
			'NIDING' => [
				'titulo'=>'Núm ID',
				'formato'=>'%s',
				'consultar'=>true,
				'visible'=>false,
				'exportar'=>true,
				],
			'NIGING' => [
				'titulo'=>'Ingreso',
				'formato'=>'%s',
				'consultar'=>true,
				'visible'=>true,
				'exportar'=>true,
				],
			'SECCIN' => [
				'titulo'=>'Sección',
				'formato'=>'%s',			
				'lista'=>$laSecciones,
				'consultar'=>true,
				'visible'=>true,
				'exportar'=>true,
				],
			'HABITA' => [
				'titulo'=>'Habitación',
				'formato'=>'%s',
				'consultar'=>true,
				'visible'=>false,
				'exportar'=>true,
				],
			'VALORA' => [
				'titulo'=>'Val Núm',
				'formato'=>'%s',
				'consultar'=>true,
				'visible'=>false,
				'exportar'=>true,
				],
			'VALFEA' => [
				'titulo'=>'Fecha',
				'formato'=>'%s',
				'tipo'=>'fecha',
				//'funcion'=>'NUCLEO\AplicacionFunciones::formatFechaHora(\'fecha\',%s)',
				'consultar'=>true,
				'visible'=>true,
				'exportar'=>true,
				],
			'VALHOA' => [
				'titulo'=>'Hora',
				'formato'=>'%s',
				'tipo'=>'hora',
				'consultar'=>true,
				'visible'=>true,
				'exportar'=>true,
				],
			'VALFES' => [
				'titulo'=>'Fecha Prox',
				'formato'=>'%s',
				'tipo'=>'fecha',
				'consultar'=>true,
				'visible'=>false,
				'exportar'=>false,
				],
			'VALHOS' => [
				'titulo'=>'Hora Prox',
				'formato'=>'%s',
				'tipo'=>'hora',
				'consultar'=>true,
				'visible'=>false,
				'exportar'=>false,
				],
			'ESTADO' => [
				'titulo'=>'Estado',
				'formato'=>'%1.0f',
				'lista'=>[
					'0'=>['NOMBRE'=>'Latente'], // Se muestra en monitores
					'1'=>['NOMBRE'=>'Latente'], // Monitoreo continuo por enfermeria
					'2'=>['NOMBRE'=>'Acción'],  // Sin asignar
					'3'=>['NOMBRE'=>'Acción'],  // Sin asignar
					'4'=>['NOMBRE'=>'Acción'],  // Sin asignar
					'5'=>['NOMBRE'=>'Acción'],  // Sin asignar
					'6'=>['NOMBRE'=>'Acción'],  // Monitoreo por el equipo de respuesta rapida
					'7'=>['NOMBRE'=>'Acción'],  // Sin Finaliza alerta
					'8'=>['NOMBRE'=>'Acción'],  // Sin Traslado a UCI
					'9'=>['NOMBRE'=>'Expiro']   // Expiro
					],
				'consultar'=>true,
				'visible'=>true,
				'exportar'=>true,
				],				
			'VAR02N' => [
				'titulo'=>'Temp',
				'formato'=>'%3.1f',
				'alerta'=>$laSignos['t'],
				'consultar'=>true,
				'visible'=>true,
				'exportar'=>true,
				],
			'VAR00N' => [
				'titulo'=>'FR',
				'formato'=>'%3.0f',
				'alerta'=>$laSignos['fr'],
				'consultar'=>true,
				'visible'=>true,
				'exportar'=>true,
				],
			'VAR04N' => [
				'titulo'=>'FC',
				'formato'=>'%3.0f',
				'alerta'=>$laSignos['fc'],
				'consultar'=>true,
				'visible'=>true,
				'exportar'=>true,
				],
			'VAR03N' => [
				'titulo'=>'TAS',
				'formato'=>'%3.0f',
				'alerta'=>$laSignos['tas'],
				'consultar'=>true,
				'visible'=>true,
				'exportar'=>true,
				],
			'VAR07N' => [
				'titulo'=>'TAD',
				'formato'=>'%3.0f',
				'alerta'=>$laSignos['tad'],
				'consultar'=>true,
				'visible'=>true,
				'exportar'=>true,
				],
			'VAR01N' => [
				'titulo'=>'SO2',
				'formato'=>'%3.0f',
				'alerta'=>$laSignos['so2'],
				'consultar'=>true,
				'visible'=>true,
				'exportar'=>true,
				],
			'VAR06N' => [
				'titulo'=>'O2 Supl',
				'formato'=>'%1.0f',
				'alerta'=>$laSignos['o2sp'],
				'lista'=>[
					'0'=>['NOMBRE'=>'No'],
					'1'=>['NOMBRE'=>'Si']
					],
				'consultar'=>true,
				'visible'=>true,
				'exportar'=>true,
				],
			'VAR05N' => [
				'titulo'=>'Nivel Conc',
				'formato'=>'%1.0f',
				'alerta'=>$laSignos['nc'],
				'lista'=>(new NivelesConciencia($tbSoloNivelesActivos=false))->aNiveles,
				'consultar'=>true,
				'visible'=>true,
				'exportar'=>true,
				],
			'CLASIF' => [
				'titulo'=>'Clasificación',
				'formato'=>'%1.0f',
				'lista'=>[
					'0'=>['NOMBRE'=>'NORMAL'],
					'1'=>['NOMBRE'=>'MONITOREO'],
					'2'=>['NOMBRE'=>'AMARILLA'],
					'3'=>['NOMBRE'=>'ROJA']
					],
				'consultar'=>true,
				'visible'=>true,
				'exportar'=>true,
				],			
			'VAR28N' => [
				'titulo'=>'Edad',
				'formato'=>'%2.0f',
				'consultar'=>true,
				'visible'=>true,
				'exportar'=>true,
				],				
			'PNEWS2' => [
				'titulo'=>'NEWS2',
				'formato'=>'%3.0f',
				'consultar'=>true,
				'visible'=>true,
				'exportar'=>true,
				],
			'RESFEA' => [
				'titulo'=>'Fecha llegada ERR',
				'formato'=>'%s',
				'tipo'=>'fecha',
				'consultar'=>true,
				'visible'=>false,
				'exportar'=>true,
				],
			'RESHOA' => [
				'titulo'=>'Hora llegada ERR',
				'formato'=>'%s',
				'tipo'=>'hora',
				'consultar'=>true,
				'visible'=>false,
				'exportar'=>true,
				],
			'EQUIPO' => [
				'titulo'=>'Equipo',
				'formato'=>'%s',
				'consultar'=>true,
				'visible'=>false,
				'exportar'=>true,
				],
			'OBSERV' => [
				'titulo'=>'Observación',
				'formato'=>'%s',
				'consultar'=>true,
				'visible'=>false,
				'exportar'=>true,
				],
			'ACCION' => [
				'titulo'=>'Conducta a seguir',
				'formato'=>'%s',
				'lista'=>(new SignosNews())->getConductas(),
				'consultar'=>true,
				'visible'=>true,
				'exportar'=>true,
				],
			'ACCFEC' => [
				'titulo'=>'Fecha Conducta',
				'formato'=>'%s',
				'tipo'=>'fecha',
				'consultar'=>true,
				'visible'=>true,
				'exportar'=>true,
				],
			'ACCHOR' => [
				'titulo'=>'Hora conducta',
				'formato'=>'%s',
				'tipo'=>'hora',
				'consultar'=>true,
				'visible'=>true,
				'exportar'=>true,
				],				
		];
	}
}
