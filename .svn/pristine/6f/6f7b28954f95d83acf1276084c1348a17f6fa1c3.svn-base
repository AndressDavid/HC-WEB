<?php

namespace NUCLEO;

require_once (__DIR__ . '/class.Db.php');
require_once (__DIR__ . '/class.Ingreso.php');
require_once (__DIR__ . '/class.SignosNews.php');
require_once (__DIR__ . '/class.Diagnostico.php');
require_once (__DIR__ . '/class.NivelesConciencia.php');
require_once (__DIR__ . '/class.AplicacionFunciones.php');
require_once (__DIR__ .'/../publico/complementos/spout/3.0.1/Spout/Autoloader/autoload.php');

use NUCLEO\Db;
use NUCLEO\Ingreso;
use NUCLEO\SignosNews;
use NUCLEO\Diagnostico;
use NUCLEO\NivelesConciencia;
use NUCLEO\AplicacionFunciones;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\Border;


class SignosNewsEstudio
{
	public $nFechaDesde;
	public $nFechaHasta;
	public $aSignos;
	public $cError;
	public $aCampos=[];


	function __construct($tnFechaDesde=0, $tnFechaHasta=0)
	{
		$this->aCampos=$this->dataVacio();
		$this->nFechaDesde=$tnFechaDesde;
		$this->nFechaHasta=$tnFechaHasta;
	}

	public function consultar()
	{
		global $goDb;

		$lnFechaIni = $this->nFechaDesde;
		$lnFechaFin = $this->nFechaHasta;

		if (empty($lnFechaIni) || empty($lnFechaFin) || $lnFechaIni>$lnFechaFin) {
			$this->cError = 'Debe indicar un intervalo de fechas válido.';
			return false;
		}

		// tiempo máximo para la consulta
		ini_set('max_execution_time', 1800); // 30 minutos de consulta


		$lcWhere = 'A.VALFEA BETWEEN '.$lnFechaIni.' AND '.$lnFechaFin;

		// Obtener listado de ingresos
		$laQry = $goDb
			->distinct()->select("A.NIGING")
			->from('ALETEMP A')
			->where($lcWhere)
			->getAll('array');
		if (!is_array($laQry)) {
			$this->cError = 'Ocurrió un error al consultar los ingresos con reporte de signos en el periodo indicado.';
			return false;
		}

		$laIngresos=[];
		foreach($laQry as $laIng){ $laIngresos[] = $laIng['NIGING']; }

		if (count($laIngresos)<=0) {
			$this->cError = 'No se encontraron ingresos.';
			return false;
		}

		$laNiveles = (new NivelesConciencia($tbSoloNivelesActivos=false))->aNiveles;
		$loIngreso = new Ingreso();
		$laConductas = (new SignosNews())->getConductas();
		$laConductas = AplicacionFunciones::mapear($laConductas, 'TIPO', 'NOMBRE');
		$laConductas[0]='';

		$laReturn = [];
		foreach($laIngresos as $lnIngr){

			$lcWhere2 = $lcWhere . (empty($lcWhere)?'':' AND ') . 'A.NIGING='.$lnIngr;

			$loIngreso->cargarIngreso($lnIngr);

			$laReturn[$lnIngr] = $this->aCampos;
			$laReturn[$lnIngr]['INGRESO']=$lnIngr;
			$laReturn[$lnIngr]['SEXO']=$loIngreso->oPaciente->cSexo;
			$laReturn[$lnIngr]['FECHA_NAC']=AplicacionFunciones::formatFechaHora('fecha',$loIngreso->oPaciente->nNacio);
			$laReturn[$lnIngr]['FECHA_INGRESO']=AplicacionFunciones::formatFechaHora('fecha',$loIngreso->nIngresoFecha);

			$ldFechaNac = new \DateTime($laReturn[$lnIngr]['FECHA_NAC']);
			$ldFechaIng = new \DateTime($laReturn[$lnIngr]['FECHA_INGRESO']);
			$loDiff = $ldFechaNac->diff($ldFechaIng);
			$laReturn[$lnIngr]['EDAD_A']=$loDiff->y;
			$laReturn[$lnIngr]['EDAD_M']=$loDiff->m;
			$laReturn[$lnIngr]['EDAD_D']=$loDiff->d;

			if ($loIngreso->nEgresoFecha>0) {
				$laReturn[$lnIngr]['FECHA_EGRESO']=AplicacionFunciones::formatFechaHora('fecha',$loIngreso->nEgresoFecha);
				$ldFechaEgr = new \DateTime($laReturn[$lnIngr]['FECHA_EGRESO']);
			} else {
				$ldFechaEgr = new \DateTime();
			}
			$loDiff = $ldFechaIng->diff($ldFechaEgr);
			$laReturn[$lnIngr]['DIAS_ESTANCIA']=$loDiff->days;


			// Lugar de procedencia y de residencia
			$laQry = $goDb
				->select("IFNULL(AN.DESPAI,'') PAISN, IFNULL(DN.DESDEP,'') MUNICN, IFNULL(CN.DESCIU,'') CIUDADN")
				->select("IFNULL(AR.DESPAI,'') PAISR, IFNULL(DR.DESDEP,'') MUNICR, IFNULL(CR.DESCIU,'') CIUDADR")
				->from('RIAING I')
				->leftJoin('RIAPAC P','I.TIDING=P.TIDPAC AND I.NIDING=P.NIDPAC')
				->leftJoin('COMPAI AN','P.PAIPAC=AN.CODPAI')
				->leftJoin('COMDEP DN','P.PAIPAC=DN.PAIDEP AND P.DEPPAC=DN.CODDEP')
				->leftJoin('COMCIU CN','P.PAIPAC=CN.PAICIU AND P.DEPPAC=CN.DEPCIU AND P.MUNPAC=CN.CODCIU')
				->leftJoin('COMPAI AR','P.PARPAC=AR.CODPAI')
				->leftJoin('COMDEP DR','P.PARPAC=DR.PAIDEP AND P.DERPAC=DR.CODDEP')
				->leftJoin('COMCIU CR','P.PARPAC=CR.PAICIU AND P.DERPAC=CR.DEPCIU AND P.MURPAC=CR.CODCIU')
				->where('I.NIGING='.$lnIngr)
				->get('array');
			if (is_array($laQry)) {
				$laQry = array_map('trim',$laQry);
				$laReturn[$lnIngr]['LUGAR_NAC']=$laQry['CIUDADN'].', '.$laQry['MUNICN'].', '.$laQry['PAISN'];
				$laReturn[$lnIngr]['LUGAR_RES']=$laQry['CIUDADR'].', '.$laQry['MUNICR'].', '.$laQry['PAISR'];
			}

			// Peso y talla del paciente
			$loIngreso->obtenerPesoTalla($lnIngr);
			$laReturn[$lnIngr]['TALLA']=$loIngreso->aTalla['valor'];
			$laReturn[$lnIngr]['TALLA_UD']=$loIngreso->aTalla['unidad'];
			$laReturn[$lnIngr]['PESO']=$loIngreso->nPeso;
			$laReturn[$lnIngr]['PESO_UD']=$loIngreso->cTipoPeso;

			// Antecedentes Clínicos
			$laAntec=$loIngreso->obtenerAntecedentes($lnIngr, '1');
			$laReturn[$lnIngr]['ANTEC_CLINICOS']=trim($laAntec['descripcion']);

			// Diagnósticos
			$laQry = $goDb
				->select('SUBIND, SUBORG')
				->from('RIAHIS')
				->where('NROING='.$lnIngr.' AND PGMHIS=\'HCPPAL\' AND INDICE=25')
				->orderBy('SUBIND, SUBORG', 'ASC')
				->getAll('array');
			if (is_array($laQry)) {
				$loDx = new Diagnostico();
				$lnNum = 0;
				foreach($laQry as $laDato) {
					$laDato = array_map('trim', $laDato);
					if ($lnNum==0) {
						$laReturn[$lnIngr]['DX_PRINCIPAL']=$laDato['SUBORG'];
						$loDx->cargar($laDato['SUBORG']);
						$laReturn[$lnIngr]['DX_PRINCIPAL_DSC']=$loDx->getTexto();
					} else {
						$laReturn[$lnIngr]['DX_RELACION'.$lnNum]=$laDato['SUBORG'];
						$loDx->cargar($laDato['SUBORG']);
						$laReturn[$lnIngr]['DX_RELACION'.$lnNum.'_DSC']=$loDx->getTexto();
						if ($lnNum>=4) break;
					}
					$lnNum++;
				}
			}

			// Primera Alerta
			$laQry = $goDb
				->from('ALETEMP A')
				->where($lcWhere2)
				->in('A.CLASIF',[2,3])
				->orderBy('A.VALFEA ASC, A.VALHOA ASC')
				->get('array');
			if (is_array($laQry)) {
				$laQry = array_map('trim', $laQry);

				// Fecha hora de la alerta
				$laReturn[$lnIngr]['PA_FECHA']=AplicacionFunciones::formatFechaHora('fechahora',$laQry['VALFEA'].$laQry['VALHOA']);
				$laReturn[$lnIngr]['PA_SECCION']=$laQry['SECCIN'];
				$ldFechaAle = new \DateTime($laReturn[$lnIngr]['PA_FECHA']);

				// Días desde ingreso hasta alerta
				$loDiff = $ldFechaIng->diff($ldFechaAle);
				$laReturn[$lnIngr]['PA_DIAS']=$loDiff->days;

				// Signos alerta
				$laReturn[$lnIngr]['PA_FC']=$laQry['VAR04N'];
				$laReturn[$lnIngr]['PA_FR']=$laQry['VAR00N'];
				$laReturn[$lnIngr]['PA_SO2']=$laQry['VAR01N'];
				$laReturn[$lnIngr]['PA_O2_SUPL']=$laQry['VAR06N'];
				$laReturn[$lnIngr]['PA_PAS']=$laQry['VAR03N'];
				$laReturn[$lnIngr]['PA_PAD']=$laQry['VAR07N'];
				$laReturn[$lnIngr]['PA_TEMP']=$laQry['VAR02N'];
				$laReturn[$lnIngr]['PA_NIVEL_CONC']=intval($laQry['VAR05N']);
				$laReturn[$lnIngr]['PA_NIVEL_CONC_DSC']=$laNiveles[$laReturn[$lnIngr]['PA_NIVEL_CONC']]['NOMBRE']??'';
				$laReturn[$lnIngr]['PA_CLASIFICA']=$laQry['CLASIF'];
				$laReturn[$lnIngr]['PA_NEWS2']=$laQry['PNEWS2'];

				// Tiempo de llegada en minutos
				if ($laQry['RESFEA']>0) {
					$laReturn[$lnIngr]['PA_FECHA_LLEGADA']=AplicacionFunciones::formatFechaHora('fechahora',$laQry['RESFEA'].$laQry['RESHOA']);
					$ldFechaRta = new \DateTime($laReturn[$lnIngr]['PA_FECHA_LLEGADA']);
					$loDiff = $ldFechaAle->diff($ldFechaRta);
					$laReturn[$lnIngr]['PA_MINUTOS_LLEGADA']=$loDiff->days*24*60 + $loDiff->h*60 + $loDiff->i;
				}

				if (!empty($laQry['EQUIPO'])) {
					if ($laQry['ACCFEC']>0) {
						// Tiempo de respuesta en minutos
						$laReturn[$lnIngr]['PA_FECHA_RTA']=AplicacionFunciones::formatFechaHora('fechahora',$laQry['ACCFEC'].$laQry['ACCHOR']);
						$ldFechaRta = new \DateTime($laReturn[$lnIngr]['PA_FECHA_RTA']);
						$loDiff = $ldFechaAle->diff($ldFechaRta);
						$laReturn[$lnIngr]['PA_MINUTOS_RTA']=$loDiff->days*24*60 + $loDiff->h*60 + $loDiff->i;
					}
					$laReturn[$lnIngr]['PA_CONDUCTA']=$laConductas[$laQry['ACCION']]??'';
					$laReturn[$lnIngr]['PA_EQUIPO']=$laQry['EQUIPO'];
					$laReturn[$lnIngr]['PA_OBSERVACION']=$laQry['OBSERV'];
				}
			}

			// Máximo puntaje NEWS2
			$laQry = $goDb
				->from('ALETEMP A')
				->where($lcWhere2)
				->orderBy('A.PNEWS2 DESC, A.VALFEA ASC, A.VALHOA ASC')
				->get('array');
			if (is_array($laQry)) {
				$laQry = array_map('trim', $laQry);

				// Fecha hora de la alerta
				$laReturn[$lnIngr]['MP_FECHA']=AplicacionFunciones::formatFechaHora('fechahora',$laQry['VALFEA'].$laQry['VALHOA']);
				$laReturn[$lnIngr]['MP_SECCION']=$laQry['SECCIN'];
				$ldFechaAle = new \DateTime($laReturn[$lnIngr]['MP_FECHA']);

				// Días desde ingreso hasta alerta
				$loDiff = $ldFechaIng->diff($ldFechaAle);
				$laReturn[$lnIngr]['MP_DIAS']=$loDiff->days;

				// Signos alerta
				$laReturn[$lnIngr]['MP_FC']=$laQry['VAR04N'];
				$laReturn[$lnIngr]['MP_FR']=$laQry['VAR00N'];
				$laReturn[$lnIngr]['MP_SO2']=$laQry['VAR01N'];
				$laReturn[$lnIngr]['MP_O2_SUPL']=$laQry['VAR06N'];
				$laReturn[$lnIngr]['MP_PAS']=$laQry['VAR03N'];
				$laReturn[$lnIngr]['MP_PAD']=$laQry['VAR07N'];
				$laReturn[$lnIngr]['MP_TEMP']=$laQry['VAR02N'];
				$laReturn[$lnIngr]['MP_NIVEL_CONC']=intval($laQry['VAR05N']);
				$laReturn[$lnIngr]['MP_NIVEL_CONC_DSC']=$laNiveles[$laReturn[$lnIngr]['MP_NIVEL_CONC']]['NOMBRE']??'';
				$laReturn[$lnIngr]['MP_CLASIFICA']=$laQry['CLASIF'];
				$laReturn[$lnIngr]['MP_NEWS2']=$laQry['PNEWS2'];

				// Tiempo de llegada en minutos
				if ($laQry['RESFEA']>0) {
					$laReturn[$lnIngr]['MP_FECHA_LLEGADA']=AplicacionFunciones::formatFechaHora('fechahora',$laQry['RESFEA'].$laQry['RESHOA']);
					$ldFechaRta = new \DateTime($laReturn[$lnIngr]['MP_FECHA_LLEGADA']);
					$loDiff = $ldFechaAle->diff($ldFechaRta);
					$laReturn[$lnIngr]['MP_MINUTOS_LLEGADA']=$loDiff->days*24*60 + $loDiff->h*60 + $loDiff->i;
				}

				if (!empty($laQry['EQUIPO'])) {
					if ($laQry['ACCFEC']>0) {
						// Tiempo de respuesta en minutos
						$laReturn[$lnIngr]['MP_FECHA_RTA']=AplicacionFunciones::formatFechaHora('fechahora',$laQry['ACCFEC'].$laQry['ACCHOR']);
						$ldFechaRta = new \DateTime($laReturn[$lnIngr]['MP_FECHA_RTA']);
						$loDiff = $ldFechaAle->diff($ldFechaRta);
						$laReturn[$lnIngr]['MP_MINUTOS_RTA']=$loDiff->days*24*60 + $loDiff->h*60 + $loDiff->i;
					}
					$laReturn[$lnIngr]['MP_CONDUCTA']=$laConductas[$laQry['ACCION']]??'';
					$laReturn[$lnIngr]['MP_EQUIPO']=$laQry['EQUIPO'];
					$laReturn[$lnIngr]['MP_OBSERVACION']=$laQry['OBSERV'];
				}

			}

		}
		$this->aSignos = $laReturn;
		return true;
	}


	public function obtener($tcTipo='xlsx')
	{
		switch ($tcTipo) {
			case 'tbl':
				$llTitulos=true;
				$lcTbl='<table id="tblSignosEstudio">';
				foreach($this->aSignos as $laIngreso){
					if ($llTitulos) {
						$lcTbl.='<thead><tr>';
						foreach ($laIngreso as $lcTtl=>$lcValor){
							$lcTbl.="<th>$lcTtl</th>";
						}
						$lcTbl.='</tr></thead><tbody>';
						$llTitulos=false;
					}
					$lcTbl.='<tr>';
					foreach ($laIngreso as $lcValor){
						$lcTbl.="<td>$lcValor</td>";
					}
					$lcTbl.='</tr>';
				}
				$lcTbl.='</tbody></table><br>';
				return $lcTbl;
				break;

			case 'json':
				return json_encode($this->aSignos);
				break;

			case 'xlsx':
				$this->exportarExcel();
				break;

			default:
				return 'tipo no determinado';
				break;
		}
	}


	// Retorna archivo en excel
	public function exportarExcel()
	{
		$lcArchivo = 'EstudioAlertas_'.date('Y-m-d-H-i-s').'.xlsx';
		$laCampos = $this->camposRet();

		// Crear libro y establecer propiedades
		$loLibro = WriterEntityFactory::createXLSXWriter();
		$loLibro->openToBrowser($lcArchivo);

		// Estilos para los títulos
		$loColor = Color::BLUE; $loWidth = Border::WIDTH_THIN; $loStyle = Border::STYLE_SOLID;
		$loBorderTitulo = (new BorderBuilder())
			->setBorderAll($loColor, $loWidth, $loStyle)
			->build();
		$loStyleTitulo = (new StyleBuilder())
			->setBackgroundColor(Color::rgb(214,234,248))
			->setFontColor(Color::BLUE)
			->setFontBold()
			->setBorder($loBorderTitulo)
			->build();

		// Nombre de la hoja
		$loHoja = $loLibro->getCurrentSheet();
		$loHoja->setName('Registros Signos');

		// Adiciona los títulos
		$loTitulo = WriterEntityFactory::createRowFromArray(array_keys($laCampos), $loStyleTitulo);
		$loLibro->addRow($loTitulo);

		// Adiciona los datos
		foreach($this->aSignos as $lnIngreso => $laData) {
			$loLibro->addRow(WriterEntityFactory::createRowFromArray($laData));
		}

		// Nueva hoja con las descripciones de los campos
		$loHoja2 = $loLibro->addNewSheetAndMakeItCurrent();
		$loHoja2->setName('Descripción Campos');

		// Adiciona los títulos
		$loTitulo = WriterEntityFactory::createRowFromArray(['Campo','Descripción'], $loStyleTitulo);
		$loLibro->addRow($loTitulo);

		// Adiciona los datos
		foreach($laCampos as $lcCampo => $laData) {
			$loLibro->addRow(WriterEntityFactory::createRowFromArray([$lcCampo, $laData['descr']]));
		}

		$loLibro->setCurrentSheet($loHoja);


		$loLibro->close();
	}


	// Array de datos vacío
	private function dataVacio()
	{
		$laCampos=$this->camposRet();
		$laReturn=[];
		foreach($laCampos as $lcCampo=>$laDatos){
			$laReturn[$lcCampo]=$laDatos['valor'];
		}
		return $laReturn;
	}

	// Lista de columnas para exportar
	private function camposRet()
	{
		return [
			'INGRESO'=>[
					'valor'=>'',
					'descr'=>'Ingreso del paciente',
				],
			'SEXO'=>[
					'valor'=>'',
					'descr'=>'Sexo (F=femenino, M=masculino)',
				],
			'FECHA_NAC'=>[
					'valor'=>'',
					'descr'=>'fecha de nacimiento (aaaa-mm-dd)',
				],
			'EDAD_A'=>[
					'valor'=>'',
					'descr'=>'Edad años',
				],
			'EDAD_M'=>[
					'valor'=>'',
					'descr'=>'Edad meses',
				],
			'EDAD_D'=>[
					'valor'=>'',
					'descr'=>'Edad días',
				],
			'LUGAR_NAC'=>[
					'valor'=>'',
					'descr'=>'Lugar de nacimiento',
				],
			'LUGAR_RES'=>[
					'valor'=>'',
					'descr'=>'Lugar de residencia',
				],
			'PESO'=>[
					'valor'=>'',
					'descr'=>'Peso',
				],
			'PESO_UD'=>[
					'valor'=>'',
					'descr'=>'Unidad de peso',
				],
			'TALLA'=>[
					'valor'=>'',
					'descr'=>'Talla',
				],
			'TALLA_UD'=>[
					'valor'=>'',
					'descr'=>'Unidad de talla',
				],
			'ANTEC_CLINICOS'=>[
					'valor'=>'',
					'descr'=>'Antecedentes clínicos registrados en la historia',
				],
			'DX_PRINCIPAL'=>[
					'valor'=>'',
					'descr'=>'Diagnóstico principal registrado en historia',
				],
			'DX_PRINCIPAL_DSC'=>[
					'valor'=>'',
					'descr'=>'Descripción diagnóstico principal',
				],
			'DX_RELACION1'=>[
					'valor'=>'',
					'descr'=>'Diagnóstico relacionado 1 en historia',
				],
			'DX_RELACION1_DSC'=>[
					'valor'=>'',
					'descr'=>'Descripción diagnóstico relacionado 1 en historia',
				],
			'DX_RELACION2'=>[
					'valor'=>'',
					'descr'=>'Diagnóstico relacionado 2 en historia',
				],
			'DX_RELACION2_DSC'=>[
					'valor'=>'',
					'descr'=>'Descripción diagnóstico relacionado 2 en historia',
				],
			'DX_RELACION3'=>[
					'valor'=>'',
					'descr'=>'Diagnóstico relacionado 3 en historia',
				],
			'DX_RELACION3_DSC'=>[
					'valor'=>'',
					'descr'=>'Descripción diagnóstico relacionado 3 en historia',
				],
			'DX_RELACION4'=>[
					'valor'=>'',
					'descr'=>'Diagnóstico relacionado 4 en historia',
				],
			'DX_RELACION4_DSC'=>[
					'valor'=>'',
					'descr'=>'Descripción diagnóstico relacionado 4 en historia',
				],
			'DX_RELACION5'=>[
					'valor'=>'',
					'descr'=>'Diagnóstico relacionado 5 en historia',
				],
			'DX_RELACION5_DSC'=>[
					'valor'=>'',
					'descr'=>'Descripción diagnóstico relacionado 5 en historia',
				],
			'FECHA_INGRESO'=>[
					'valor'=>'',
					'descr'=>'Fecha de ingreso del paciente',
				],
			'FECHA_EGRESO'=>[
					'valor'=>'',
					'descr'=>'Fecha de egreso del paciente',
				],
			'DIAS_ESTANCIA'=>[
					'valor'=>'',
					'descr'=>'Número de días de estancia (fecha de egreso – fecha de ingreso)',
				],
			'PA_FECHA'=>[
					'valor'=>'',
					'descr'=>'Primera Alerta – Fecha y hora (aaaa-mm-dd hh:mm:ss)',
				],
			'PA_DIAS'=>[
					'valor'=>'',
					'descr'=>'Primera Alerta – Días desde el ingreso hasta la alerta',
				],
			'PA_SECCION'=>[
					'valor'=>'',
					'descr'=>'Primera Alerta – Sección',
				],
			'PA_FC'=>[
					'valor'=>'',
					'descr'=>'Primera Alerta – Frecuencia Cardiaca',
				],
			'PA_FR'=>[
					'valor'=>'',
					'descr'=>'Primera Alerta – Frecuencia Respiratoria',
				],
			'PA_SO2'=>[
					'valor'=>'',
					'descr'=>'Primera Alerta – Saturación de oxígeno',
				],
			'PA_O2_SUPL'=>[
					'valor'=>'',
					'descr'=>'Primera Alerta – Requiere oxígeno suplementario (SI / NO)',
				],
			'PA_PAS'=>[
					'valor'=>'',
					'descr'=>'Primera Alerta – Presión arterial sistólica',
				],
			'PA_PAD'=>[
					'valor'=>'',
					'descr'=>'Primera Alerta – Presión arterial diastólica',
				],
			'PA_TEMP'=>[
					'valor'=>'',
					'descr'=>'Primera Alerta – Temperatura',
				],
			'PA_NIVEL_CONC'=>[
					'valor'=>'',
					'descr'=>'Primera Alerta – Nivel de conciencia (valor)',
				],
			'PA_NIVEL_CONC_DSC'=>[
					'valor'=>'',
					'descr'=>'Primera Alerta – Nivel de conciencia (descripción)',
				],
			'PA_CLASIFICA'=>[
					'valor'=>'',
					'descr'=>'Primera Alerta – Clasificación de la alerta',
				],
			'PA_NEWS2'=>[
					'valor'=>'',
					'descr'=>'Primera Alerta – Puntaje NEWS2',
				],
			'PA_FECHA_LLEGADA'=>[
					'valor'=>'',
					'descr'=>'Primera Alerta – Fecha y hora de llegada (aaaa-mm-dd hh:mm:ss)',
				],
			'PA_MINUTOS_LLEGADA'=>[
					'valor'=>'',
					'descr'=>'Primera Alerta – Minutos desde alerta hasta llegada',
				],
			'PA_FECHA_RTA'=>[
					'valor'=>'',
					'descr'=>'Primera Alerta – Fecha y hora de respuesta (aaaa-mm-dd hh:mm:ss)',
				],
			'PA_MINUTOS_RTA'=>[
					'valor'=>'',
					'descr'=>'Primera Alerta – Minutos desde alerta hasta respuesta',
				],
			'PA_EQUIPO'=>[
					'valor'=>'',
					'descr'=>'Primera Alerta – Equipo de respuesta',
				],
			'PA_OBSERVACION'=>[
					'valor'=>'',
					'descr'=>'Primera Alerta – Observación de respuesta',
				],
			'PA_CONDUCTA'=>[
					'valor'=>'',
					'descr'=>'Primera Alerta – Conducta seleccionada en la respuesta',
				],
			'MP_FECHA'=>[
					'valor'=>'',
					'descr'=>'Máximo Puntaje – Fecha y hora (aaaa-mm-dd hh:mm:ss)',
				],
			'MP_DIAS'=>[
					'valor'=>'',
					'descr'=>'Máximo Puntaje – Días desde el ingreso hasta la alerta',
				],
			'MP_SECCION'=>[
					'valor'=>'',
					'descr'=>'Máximo Puntaje – Sección',
				],
			'MP_FC'=>[
					'valor'=>'',
					'descr'=>'Máximo Puntaje – Frecuencia Cardiaca',
				],
			'MP_FR'=>[
					'valor'=>'',
					'descr'=>'Máximo Puntaje – Frecuencia Respiratoria',
				],
			'MP_SO2'=>[
					'valor'=>'',
					'descr'=>'Máximo Puntaje – Saturación de oxígeno',
				],
			'MP_O2_SUPL'=>[
					'valor'=>'',
					'descr'=>'Máximo Puntaje – Requiere oxígeno suplementario (SI / NO)',
				],
			'MP_PAS'=>[
					'valor'=>'',
					'descr'=>'Máximo Puntaje – Presión arterial sistólica',
				],
			'MP_PAD'=>[
					'valor'=>'',
					'descr'=>'Máximo Puntaje – Presión arterial diastólica',
				],
			'MP_TEMP'=>[
					'valor'=>'',
					'descr'=>'Máximo Puntaje – Temperatura',
				],
			'MP_NIVEL_CONC'=>[
					'valor'=>'',
					'descr'=>'Máximo Puntaje – Nivel de conciencia (valor)',
				],
			'MP_NIVEL_CONC_DSC'=>[
					'valor'=>'',
					'descr'=>'Máximo Puntaje – Nivel de conciencia (descripción)',
				],
			'MP_CLASIFICA'=>[
					'valor'=>'',
					'descr'=>'Máximo Puntaje – Clasificación de la alerta',
				],
			'MP_NEWS2'=>[
					'valor'=>'',
					'descr'=>'Máximo Puntaje – Puntaje NEWS2',
				],
			'MP_FECHA_LLEGADA'=>[
					'valor'=>'',
					'descr'=>'Máximo Puntaje – Fecha y hora de llegada (aaaa-mm-dd hh:mm:ss)',
				],
			'MP_MINUTOS_LLEGADA'=>[
					'valor'=>'',
					'descr'=>'Máximo Puntaje – Minutos desde alerta hasta llegada',
				],
			'MP_FECHA_RTA'=>[
					'valor'=>'',
					'descr'=>'Máximo Puntaje – Fecha y hora de respuesta (aaaa-mm-dd hh:mm:ss)',
				],
			'MP_MINUTOS_RTA'=>[
					'valor'=>'',
					'descr'=>'Máximo Puntaje – Minutos desde alerta hasta respuesta',
				],
			'MP_EQUIPO'=>[
					'valor'=>'',
					'descr'=>'Máximo Puntaje – Equipo de respuesta',
				],
			'MP_OBSERVACION'=>[
					'valor'=>'',
					'descr'=>'Máximo Puntaje – Observación de respuesta',
				],
			'MP_CONDUCTA'=>[
					'valor'=>'',
					'descr'=>'Máximo Puntaje – Conducta seleccionada en la respuesta',
				],
		];
	}

}
