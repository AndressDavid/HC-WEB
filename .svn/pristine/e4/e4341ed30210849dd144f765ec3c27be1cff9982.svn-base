<?php

namespace NUCLEO;

require_once('class.NivelesConciencia.php');
require_once('class.NotaEnfermeria.php');
require_once('class.Habitacion.php');
require_once('class.Ingreso.php');
require_once('class.Db.php');

use NUCLEO\NivelesConciencia;
use NUCLEO\NotaEnfermeria;
use NUCLEO\Habitacion;
use NUCLEO\Ingreso;
use NUCLEO\Db;

class SignosNews
{
	// NEWS
	protected $nPuntaje = 0;
	protected $aPuntaje = [0, 0, 0, 0];
	protected $nResultado = 0;
	protected $aRespuestas = [];
	protected $aSignos = [];
	protected $aValores = [];
	protected $aConductas = [];
	protected $nEdad = 0;
	protected $lAdulto = true;
	public $oIngreso = null;
	public $nConsecutivo = 0;
	public $nProxima = 0;
	public $tProxima = null;

	// QSOFA
	protected $nPuntajeQSOFA = 0;
	protected $nResultadoQSOFA = 0;
	protected $aRespuestasQSOFA = [];

	public function __construct($tbSoloNivelesConcienciaActivos=true)
	{
		$this->oIngreso = new Ingreso();
		$this->nProxima = 0;
		$this->tProxima = time();
		$this->aRespuestas = [
			[
				'icono' => 'fa-check',
				'color' => 'success',
				'colorh' => 'D4EDDA',
				'decision' => [
					'respuesta' => 'Observación de rutina',
					'tiempo' => 0,
					'area' => '',
					'nivel' => '',
					'proximo' => 6,
				],
			],
			[
				'icono' => 'fa-bell',
				'color' => 'primary',
				'colorh' => 'CCE5FF',
				'decision' => [
					'respuesta' => 'Enfermería decide si requiere mayor monitoreo o valoración clínica',
					'tiempo' => 0,
					'area' => '',
					'nivel' => '',
					'proximo' => 2,
				],
			],
			[
				'icono' => 'fa-exclamation',
				'color' => 'warning',
				'colorh' => 'FFEEBA',
				'decision' => [
					'respuesta' => 'Valoración clínica médica',
					'tiempo' => 30,
					'area' => 'Área',
					'nivel' => 'Médico',
					'proximo' => 1,
				],
			],
			[
				'icono' => 'fa-exclamation-triangle',
				'color' => 'danger',
				'colorh' => 'F5C6CB',
				'decision' => [
					'respuesta' => 'Equipo de respuesta rápida',
					'tiempo' => 15,
					'area' => 'Área/UCI',
					'nivel' => 'Médico',
					'proximo' => 0,
				],
			],
		];

		$this->aRespuestasQSOFA = [
			[
				'icono' => 'fa-check',
				'color' => 'success',
				'colorh' => 'D4EDDA',
				'decision' => [
					'respuesta' => 'Sin sospecha de infección por qSOFA',
				],
			],
			[
				'icono' => 'fa-exclamation',
				'color' => 'warning',
				'colorh' => 'FFEEBA',
				'decision' => [
					'respuesta' => 'Sospecha de infección por qSOFA, realizar evaluación completa',
				],
			],
		];

		$this->aSignos = [
			'fr' => [
				'valor' => 0,
				'puntaje' => 0,
				'puntajeQSOFA' => 0,
				'titulo' => 'Frecuencia respiratoria',
				'tipo' => 'text',
				'dato' => 'integer',
				'min' => 4,
				'max' => 90,
				'reglas' => [
					['min' => 0, 'max' => 8, 'val' => 3],
					['min' => 9, 'max' => 11, 'val' => 1],
					['min' => 12, 'max' => 20, 'val' => 0],
					['min' => 21, 'max' => 24, 'val' => 2],
				],
				'reglasQSOFA' => [
					['min' => 21, 'max' => 90, 'val' => 1],
				],
				'default' => 3,
				'defaultQSOFA' => 0,
			],
			'so2' => [
				'valor' => 0,
				'puntaje' => 0,
				'titulo' => 'Saturaci&oacute;n O<sub>2</sub> (%)',
				'tipo' => 'text',
				'dato' => 'integer',
				'min' => 0,
				'max' => 100,
				'reglas' => [
					['min' => 0, 'max' => 79, 'val' => 3],
					['min' => 80, 'max' => 84, 'val' => 2],
					['min' => 85, 'max' => 87, 'val' => 1],
				],
				'default' => 0,
			],
			't' => [
				'valor' => 0,
				'puntaje' => 0,
				'titulo' => 'Temperatura (&deg;C)',
				'tipo' => 'text',
				'dato' => 'float',
				'min' => 31.00,
				'max' => 42.99,
				'reglas' => [
					['min' => 0, 'max' => 35, 'val' => 3],
					['min' => 35.1, 'max' => 35.9, 'val' => 1],
					['min' => 36, 'max' => 38, 'val' => 0],
					['min' => 38.1, 'max' => 38.9, 'val' => 1],
				],
				'default' => 2
			],
			'tas' => [
				'valor' => 0,
				'puntaje' => 0,
				'puntajeQSOFA' => 0,
				'titulo' => 'Presi&oacute;n arterial sist&oacute;lica',
				'tipo' => 'text',
				'dato' => 'integer',
				'min' => 0,
				'max' => 300,
				'reglas' => [
					['min' => 0, 'max' => 90, 'val' => 3],
					['min' => 91, 'max' => 100, 'val' => 2],
					['min' => 101, 'max' => 110, 'val' => 1],
					['min' => 111, 'max' => 219, 'val' => 0],
				],
				'reglasQSOFA' => [
					['min' => 0, 'max' => 99, 'val' => 1],
				],
				'default' => 3,
				'defaultQSOFA' => 0,
			],
			'tad' => [
				'valor' => 0,
				'puntaje' => 0,
				'titulo' => 'Presi&oacute;n arterial diast&oacute;lica',
				'tipo' => 'text',
				'dato' => 'integer',
				'min' => 0,
				'max' => 150,
				'reglas' => [],
				'default' => 0,
			],
			'fc' => [
				'valor' => 0,
				'puntaje' => 0,
				'titulo' => 'Frecuencia card&iacute;aca',
				'tipo' => 'text',
				'dato' => 'integer',
				'min' => 0,
				'max' => 300,
				'reglas' => [
					['min' => 0, 'max' => 40, 'val' => 3],
					['min' => 41, 'max' => 50, 'val' => 1],
					['min' => 51, 'max' => 90, 'val' => 0],
					['min' => 91, 'max' => 110, 'val' => 1],
					['min' => 111, 'max' => 129, 'val' => 2],
				],
				'default' => 3,
			],
			'nc' => [
				'valor' => 0,
				'puntaje' => 0,
				'puntajeQSOFA' => 0,
				'titulo' => 'Nivel de conciencia',
				'tipo' => 'select',
				'valores' => (new NivelesConciencia($tbSoloNivelesConcienciaActivos))->aNiveles,
				'reglas' => [
					['min' => 1, 'max' => 2, 'val' => 0],
				],
				'reglasQSOFA' => [
					['min' => 1, 'max' => 2, 'val' => 0],
				],
				'default' => 3,
				'defaultQSOFA' => 1,
			],
			'o2sp' => [
				'valor' => 0,
				'puntaje' => 0,
				'titulo' => 'Necesita O<sub>2</sub> suplementario',
				'tipo' => 'select',
				'valores' => [
					0 => ['NOMBRE' => 'NO'],
					1 => ['NOMBRE' => 'SI'],
				],
				'reglas' => [
					['min' => 1, 'max' => 1, 'val' => 2],
				],
				'default' => 0,
			],
		];
		$this->aConductas = [
			'6' => ['TIPO' => '6', 'NOMBRE' => 'Continúa en monitoreo por el equipo de respuesta rápida',
			],
			'7' => ['TIPO' => '7', 'NOMBRE' => 'Finaliza alerta',
			],
			'8' => ['TIPO' => '8', 'NOMBRE' => 'Trasladar a UCI',
			],
		];
	}

	public function medir($taValores, $tnIngreso = 0)
	{
		$this->aValores = $taValores;

		// Ingreso
		$this->lAdulto = true;
		if ($tnIngreso <= 0) {
			if (isset($taValores['nIngreso'])) {
				$tnIngreso = $taValores['nIngreso'] + 0;
			}
		}
		if ($tnIngreso > 0) {
			$this->oIngreso->cargarIngreso($tnIngreso);
			$this->lAdulto = $this->oIngreso->oPaciente->esAdulto();
		}

		// Asignando los valores
		if (is_array($this->aValores) == true) {
			foreach ($this->aValores as $lcKey => $lnValor) {
				switch ($lcKey) {
					case 't':
						$lnValor = floatval(sprintf("%01.2f", $lnValor));
						break;
					default:
						settype($lnValor, "float");

				}
				$lcKey = trim(strtolower($lcKey));

				if (!empty($lcKey)) {
					if (isset($this->aSignos[$lcKey]) == true) {
						$this->aSignos[$lcKey]["valor"] = abs($lnValor);
					}
				}
			}
		}

		// Calculando el puntaje NEWS
		$this->nPuntaje = 0;
		$this->nResultado = 0;

		if ($this->lAdulto) {
			foreach ($this->aSignos as $lcKey => $laSigno) {
				$this->aSignos[$lcKey]["puntaje"] = $laSigno["default"];

				// valida las reglas
				foreach ($laSigno["reglas"] as $laRegla) {
					if (count($laRegla) == 3) {
						if ($this->between($laSigno["valor"], $laRegla['min'], $laRegla['max']) == true) {
							$this->aSignos[$lcKey]["puntaje"] = $laRegla['val'];
							break;
						}
					}
				}

				if (isset($this->aPuntaje[$this->aSignos[$lcKey]["puntaje"]]) == true) {
					$this->aPuntaje[$this->aSignos[$lcKey]["puntaje"]] += 1;
				}

				$this->nPuntaje += $this->aSignos[$lcKey]["puntaje"];
			}

			// Validando por puntaje
			if ($this->between($this->nPuntaje, 1, 4) == true) {
				$this->nResultado = 1;
			} elseif ($this->between($this->nPuntaje, 5, 6) == true) {
				$this->nResultado = 2;
			} elseif ($this->nPuntaje > 6) {
				$this->nResultado = 3;
			}

			// Validando por el umbral de una variable
			if ($this->aPuntaje[3] == 1) {
				$this->nResultado = ($this->nPuntaje < 5 ? 2 : $this->nResultado);
			} elseif ($this->aPuntaje[3] > 1) {
				$this->nResultado = ($this->nPuntaje < 7 ? 3 : $this->nResultado);
			}
		}

		// Calculando el puntaje QSOFA
		$this->nPuntajeQSOFA = 0;
		$this->nResultadoQSOFA = 0;

		foreach ($this->aSignos as $lcKey => $laSigno) {
			if (isset($laSigno["defaultQSOFA"]) == true) {
				$this->aSignos[$lcKey]["puntajeQSOFA"] = $laSigno["defaultQSOFA"];

				// valida las reglas
				foreach ($laSigno["reglasQSOFA"] as $laRegla) {
					if (count($laRegla) == 3) {
						if ($this->between($laSigno["valor"], $laRegla['min'], $laRegla['max']) == true) {
							$this->aSignos[$lcKey]["puntajeQSOFA"] = $laRegla['val'];
							break;
						}
					}
				}
				$this->nPuntajeQSOFA += $this->aSignos[$lcKey]["puntajeQSOFA"];
			}
		}

		// Validando por puntaje
		$this->nResultadoQSOFA = ($this->nPuntajeQSOFA > 2 ? 1 : 0);

	}

	private function between($tnValor, $tnMenor, $tnMayor)
	{
		return ($tnValor - $tnMenor) * ($tnValor - $tnMayor) <= 0;
	}

	public function UltimaToma($tnIngreso = 0, $tnTipo = 0)
	{
		$lnConsec = 0;

		global $goDb;
		$laConsecutivo = $goDb->max('CONALE', 'MAXIMO')->from('ALETEMP')->where('NIGING', '=', $tnIngreso)->where('VAR29N', '=', $tnTipo)->get("array");
		if (is_array($laConsecutivo) == true) {
			if (count($laConsecutivo) > 0) {
				$lnConsec = $laConsecutivo['MAXIMO'];
				settype($lnConsec, 'integer');
			}
		}

		return $lnConsec;
	}

	public function consultarUltimos($tnIngreso = 0, $tcTipoId = '', $tnId = 0)
	{
		$laResultado = array();

		global $goDb;
		if (isset($goDb)) {
			if ($tnIngreso > 0) {
				$lcTabla = 'ALETEMP';
				$laCampos = [
					'CONALE COSECUTIVO',
					'NIDING ID',
					'TIDING TIPOID',
					'NIGING INGRESO',
					'VALORA VALORACION',
					'VALFEA FECHA',
					'VALHOA HORA',
					'VALFES FECHAPROX',
					'VALHOS HORAPROX',
					'ESTADO',
					'VAR00N FR',
					'VAR01N SO2',
					'VAR02N T',
					'VAR03N TAS',
					'VAR04N FC',
					'VAR05N NC',
					'VAR06N O2SP',
					'VAR07N TAD',
					'VAR26N QSOFA',
					'VAR27N PUNTAJEQSOFA',
					'VAR28N EDAD',
					'VAR29N TIPO',
					'VAR30N MANUAL',
					'CLASIF CLASIFICACION',
					'PNEWS2 PUNTAJE'
				];
				$laResultado = $goDb->select($laCampos)->tabla($lcTabla)
					->where('NIGING', '=', $tnIngreso)
					->where('TIDING', '=', $tcTipoId)
					->where('NIDING', '=', $tnId)
					->orderBy('CONALE', 'DESC')
					->get('array');
			}
		}

		return $laResultado;
	}

	public function insertar($tnIngreso = 0, $tcTipoId = '', $tnId = 0, $tcUsuario = '')
	{
		$llResultado = false;

		// Ingreso
		$this->oIngreso->cargarIngreso($tnIngreso);
		$this->nEdad = $this->oIngreso->oPaciente->getEdad(date('Y-m-d'), '%y');
		$this->nEdad += 0;
		$this->lAdulto = $this->oIngreso->oPaciente->esAdulto();


		global $goDb;
		if (isset($goDb)) {
			if ($tnIngreso > 0) {
				$laRespuesta = $this->aRespuestas[$this->nResultado];
				$this->nProxima = $laRespuesta["decision"]["proximo"];
				settype($this->nProxima, 'integer');
				$this->tProxima = Time() + (60 * 60 * $this->nProxima);

				$ltAhora = new \DateTime($goDb->fechaHoraSistema());
				$lcFecha = $ltAhora->format("Ymd");
				$lcHora = $ltAhora->format("His");

				$lcTabla = 'ALETEMP';
				$lnConsecutivo = 0;
				$llFlagExiste = true;
				while ($llFlagExiste == true) {
					$lnConsecutivo = $goDb->secuencia('SEQ_ALETEMP');
					if ($lnConsecutivo > 0) {
						$laRegistro = $goDb
							->select('CONALE CONSECUTIVO')
							->from($lcTabla)
							->where('CONALE', '=', $lnConsecutivo)
							->get('array');
						$llFlagExiste = (is_array($laRegistro) == true ? count($laRegistro) > 0 : false);
					}
				}
				$this->nConsecutivo = $lnConsecutivo;

				$loRegistros = $goDb->count('*', 'REGISTROS')->tabla($lcTabla)->where('NIGING', '=', $tnIngreso)->get('array');
				$lnValoracion = $loRegistros['REGISTROS'] + 1;

				// Obtener habitación
				$loHab = new Habitacion();
				$loHab->cargarHabitacion($tnIngreso);

				// Insertando la nueva lectura
				$laDatos = [
					'CONALE' => $this->nConsecutivo,
					'NIGING' => $tnIngreso,
					'TIDING' => $tcTipoId,
					'NIDING' => $tnId,
					'VALORA' => $lnValoracion,
					'VALFEA' => $lcFecha,
					'VALHOA' => $lcHora,
					'SECCIN' => $loHab->cSeccion,
					'HABITA' => $loHab->cHabitacion,
					'VALFES' => ($this->nProxima > 0 ? date('Ymd', $this->tProxima) + 0 : 0),
					'VALHOS' => ($this->nProxima > 0 ? date('His', $this->tProxima) + 0 : 0),
					'ESTADO' => ($this->nResultado >= 2 ? ($this->lAdulto == true ? 0 : 9) : ($this->nResultado == 1 ? 1 : 9)),
					'VAR00N' => $this->aSignos['fr']['valor'],
					'VAR01N' => $this->aSignos['so2']['valor'],
					'VAR02N' => floatval(sprintf("%01.2f", $this->aSignos['t']['valor'])),
					'VAR03N' => $this->aSignos['tas']['valor'],
					'VAR04N' => $this->aSignos['fc']['valor'],
					'VAR05N' => $this->aSignos['nc']['valor'],
					'VAR06N' => $this->aSignos['o2sp']['valor'],
					'VAR07N' => $this->aSignos['tad']['valor'],
					'VAR26N' => $this->nResultadoQSOFA,
					'VAR27N' => $this->nPuntajeQSOFA,
					'VAR28N' => $this->nEdad,
					'VAR29N' => 0,
					'VAR30N' => 0,
					'CLASIF' => $this->nResultado,
					'PNEWS2' => $this->nPuntaje,
					'USCALE' => $tcUsuario,
					'PGCALE' => substr(pathinfo(__FILE__, PATHINFO_FILENAME), 0, 10),
					'FECALE' => $lcFecha,
					'HOCALE' => $lcHora
				];

				$llResultado = $goDb->tabla($lcTabla)->insertar($laDatos);

				if ($llResultado == true) {
					// Marcar lecturas previas activas como cerradas
					$laDatos = [
						'ESTADO' => '9',
						'USMALE' => $tcUsuario,
						'PGMALE' => substr(pathinfo(__FILE__, PATHINFO_FILENAME), 0, 10),
						'FEMALE' => $lcFecha,
						'HOMALE' => $lcHora
					];
					$goDb->tabla($lcTabla)->where('NIDING', '=', $tnId)->where('ESTADO', '<', '9')->where('CONALE', '<', $this->nConsecutivo)->where('VAR29N', '=', '0')->actualizar($laDatos);

					// Guardando en notas de enfermería
					$loNotaEnfermeria = new NotaEnfermeria();
					$laRespuesta = $this->getRespuesta();
					$lcObservacion = (is_array($laRespuesta) ? $laRespuesta["decision"]["respuesta"] . (!empty($laRespuesta["decision"]["tiempo"]) ? ", tiempo: " . $laRespuesta["decision"]["tiempo"] : '') . (!empty($laRespuesta["decision"]["nivel"]) ? " minutos, Nivel: " . $laRespuesta["decision"]["nivel"] : '') . (!empty($laRespuesta["decision"]["area"]) ? ", area: " . $laRespuesta["decision"]["area"] : '') : '');

					$loDataEnsign = (object) [
						"tcObservacion" => $lcObservacion,
						"tnFr" => $this->aSignos['fr']['valor'],
						"tnSO2" => $this->aSignos['so2']['valor'],
						"tnT" => $this->aSignos['t']['valor'],
						"tnTAS" => $this->aSignos['tas']['valor'],
						"tnTAD" => $this->aSignos['tad']['valor'],
						"tnTAM" => "",
						"tcEstado" => "",
						"tnFC" => $this->aSignos['fc']['valor'],
					];
					$loNotaEnfermeria->RegistrarSignos($tnIngreso, $loDataEnsign);
				}
			}
		}

		return $llResultado;
	}

	public function llamarERR($tnIngreso = 0, $tcUsuario = '')
	{
		$llResultado = false;

		global $goDb;
		if (isset($goDb)) {
			if ($tnIngreso > 0) {
				$lnConsecutivo = $this->UltimaToma($tnIngreso, 0);
				if ($lnConsecutivo > 0) {
					$ltAhora = new \DateTime($goDb->fechaHoraSistema());
					$lcFecha = $ltAhora->format("Ymd");
					$lcHora = $ltAhora->format("His");

					$lcTabla = 'ALETEMP';
					$laDatos = [
						'ESTADO' => '0',
						'VAR30N' => 1,
						'USMALE' => $tcUsuario,
						'PGMALE' => substr(pathinfo(__FILE__, PATHINFO_FILENAME), 0, 10),
						'FEMALE' => $lcFecha,
						'HOMALE' => $lcHora
					];
					$llResultado = $goDb->tabla($lcTabla)->where('NIGING', '=', $tnIngreso)->where('CONALE', '=', $lnConsecutivo)->actualizar($laDatos);
				}
			}
		}
		return $llResultado;
	}

	public function llegadaERR($tnIngreso = 0, $tcUsuario = '')
	{
		$llResultado = false;

		global $goDb;
		if (isset($goDb)) {
			if ($tnIngreso > 0) {
				$ltAhora = new \DateTime($goDb->fechaHoraSistema());
				$lcFecha = $ltAhora->format("Ymd");
				$lcHora = $ltAhora->format("His");

				$lcTabla = 'ALETEMP';
				$laDatos = [
					'RESFEA' => $lcFecha,
					'RESHOA' => $lcHora,
					'USMALE' => $tcUsuario,
					'PGMALE' => substr(pathinfo(__FILE__, PATHINFO_FILENAME), 0, 10),
					'FEMALE' => $lcFecha,
					'HOMALE' => $lcHora
				];
				$llResultado = $goDb->tabla($lcTabla)->where('NIGING', '=', $tnIngreso)->where('ESTADO', '=', 0)->where('RESFEA', '=', 0)->actualizar($laDatos);
			}
		}
		return $llResultado;
	}

	// NEWS
	public function getSignos()
	{
		return $this->aSignos;
	}
	public function getPuntaje()
	{
		return $this->nPuntaje;
	}
	public function getResultado()
	{
		return $this->nResultado;
	}
	public function getRespuesta()
	{
		return $this->aRespuestas[$this->nResultado];
	}
	public function getRespuestaClasificacion($tnClasificacion = 0)
	{
		return $this->aRespuestas[$tnClasificacion];
	}
	public function getRespuestas()
	{
		return $this->aRespuestas;
	}
	public function getValores()
	{
		return $this->aValores;
	}
	public function getConductas()
	{
		return $this->aConductas;
	}

	//QSOFA
	public function getPuntajeQSOFA()
	{
		return $this->nPuntajeQSOFA;
	}
	public function getRespuestaQSOFA()
	{
		return $this->aRespuestasQSOFA[$this->nResultadoQSOFA];
	}

}
