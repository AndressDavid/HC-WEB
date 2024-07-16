<?php

namespace NUCLEO;

use Exception;

session_start();
/***********************************************************************************/
/******  GENERACIÓN DE INFORMACION PARA DASHBOARD DE FACTURACIÓN ELECTRÓNICA  ******/
/***********************************************************************************/
error_reporting(0);
require_once __DIR__ . '/class.Db.php';

class DashBoardFE
{

	private $cTablaFemov = 'FEMOV';
	private $cTablaTabmae = 'TABMAE';
	public $cFecha = '';
	public $nConError = 0;
	public $nConEnviar = 0;
	public $nCuentaFE = 0;
	public $nCuentaNC = 0;
	public $nCuentaND = 0;
	public $nCuentaDS = 0;
	public $nCuentaNA = 0;
	public $nConExitoso = 0;
	public $nConPendiente = 0;
	public $nTotalDocumentos = 0;

	public function __construct()
	{
		setlocale(LC_ALL, "es_ES");
		date_default_timezone_set("America/Bogota");
		$this->cFecha = date("Y-m-d");
	}


	/*
	 *	Retorna arreglo con la cantidad de documentos generados dependiendo el tipo de documento y el total general
	 */
	public function obtenerCantidadDocumentosGenerados(string $lcConsultaFecha): array
	{
		global $goDb;
		$lcErrorConsulta = '';
		try {
			$laDatosCuentaGenerados = $goDb->select("SUBSTR(F.TIPR,3,2) TIPO_DOC , COUNT(*) CUENTA")
				->from($this->cTablaFemov . ' F')
				->leftJoin($this->cTablaTabmae . ' E', "E.TIPTMA = 'FACTELE' AND E.CL1TMA ='ESTADO' AND E.CL2TMA=F.ESTA")
				->where("FECC $lcConsultaFecha")
				->groupBy("SUBSTR(F.TIPR,3,2)")
				->orderBy("SUBSTR(F.TIPR,3,2)")
				->getAll("array");

			if (isset($laDatosCuentaGenerados)) {
				if (is_array($laDatosCuentaGenerados)) {
					foreach ($laDatosCuentaGenerados as $laDatosCuentaGenerado) {
						if ($laDatosCuentaGenerado['TIPO_DOC'] === 'FA') {
							$this->nCuentaFE = $laDatosCuentaGenerado['CUENTA'];
						} else if ($laDatosCuentaGenerado['TIPO_DOC'] === 'NC') {
							$this->nCuentaNC = $laDatosCuentaGenerado['CUENTA'];
						} else if ($laDatosCuentaGenerado['TIPO_DOC'] === 'ND') {
							$this->nCuentaND = $laDatosCuentaGenerado['CUENTA'];
						}
						$this->nTotalDocumentos += intval($laDatosCuentaGenerado['CUENTA']);
					}
				}
			}
		} catch (Exception $e) {
			$lcErrorConsulta .= $e->getMessage() . ' ';
		}

		try {
			$laDatosCuentaGeneradosDs = $goDb->select("TIPR TIPO_DOC, COUNT(*) CUENTA")
				->from($this->cTablaFemov)
				->leftJoin($this->cTablaTabmae, "TIPTMA = 'FACTELE' AND CL1TMA ='ESTADO' AND CL2TMA=ESTA")
				->where("FECC $lcConsultaFecha")
				->where("SUBSTR(TIPR,0,2) = 'DS'")
				->groupBy("TIPR")
				->orderBy("TIPR")
				->getAll("array");

			if (isset($laDatosCuentaGeneradosDs)) {
				if (is_array($laDatosCuentaGeneradosDs)) {
					foreach ($laDatosCuentaGeneradosDs as $laDatosCuentaGeneradoDs) {
						if ($laDatosCuentaGeneradoDs['TIPO_DOC'] === 'DSFA') {
							$this->nCuentaDS = $laDatosCuentaGeneradoDs['CUENTA'];
						} else if ($laDatosCuentaGeneradoDs['TIPO_DOC'] === 'DSNC') {
							$this->nCuentaNA = $laDatosCuentaGeneradoDs['CUENTA'];
						}
						$this->nTotalDocumentos += intval($laDatosCuentaGeneradoDs['CUENTA']);
					}
				}
			}
		} catch (Exception $e) {
			$lcErrorConsulta .= $e->getMessage();
		}

		return [
			"error" => $lcErrorConsulta,
			"cuentaFE" => $this->nCuentaFE,
			"cuentaNC" => $this->nCuentaNC,
			"cuentaND" => $this->nCuentaND,
			"cuentaDS" => $this->nCuentaDS,
			"cuentaNA" => $this->nCuentaNA,
			"totalDocumentos" => $this->nTotalDocumentos
		];
	}

	/*
	 *	Retorna arreglo con la cantidad de documentos dependiendo su estado  (exitoso, pendiente, por enviar o con error)
	 */
	public function obtenerDistribucionEstados(string $lcConsultaFecha): array
	{
		global $goDb;
		$lcErrorConsulta = '';
		try {
			$laDatosCuentaEstados = $goDb->select("FECC FECHA, ESTA ESTADO, TRIM(DE1TMA) ESTADO_DESCR, COUNT(*) CUENTA")
				->from($this->cTablaFemov)
				->leftJoin($this->cTablaTabmae, "TIPTMA = 'FACTELE' AND CL1TMA ='ESTADO' AND CL2TMA=ESTA")
				->where("FECC $lcConsultaFecha")
				->groupBy("FECC,ESTA,DE1TMA")
				->orderBy("FECC,ESTA")
				->getAll("array");

			if (isset($laDatosCuentaEstados)) {
				if (is_array($laDatosCuentaEstados)) {
					foreach ($laDatosCuentaEstados as $laDatosCuentaEstado) {
						if ($laDatosCuentaEstado['ESTADO'] === '00') {
							$this->nConEnviar += intval($laDatosCuentaEstado['CUENTA']);
						} else if ($laDatosCuentaEstado['ESTADO'] === '02') {
							$this->nConExitoso += intval($laDatosCuentaEstado['CUENTA']);
						} else if ($laDatosCuentaEstado['ESTADO'] === '03') {
							$this->nConPendiente += intval($laDatosCuentaEstado['CUENTA']);
						} else if ($laDatosCuentaEstado['ESTADO'] === '04') {
							$this->nConError += intval($laDatosCuentaEstado['CUENTA']);
						}
					}
				}
			}
		} catch (Exception $e) {
			$lcErrorConsulta .= $e->getMessage();
		}

		return [
			"error" => $lcErrorConsulta,
			"conEnviar" => $this->nConEnviar,
			"conExitoso" => $this->nConExitoso,
			"conPendiente" => $this->nConPendiente,
			"conError" => $this->nConError
		];
	}

	/*
	 *	Retorna arreglo con la lista de documentos con errores
	 */
	public function obtenerDocumentosError(string $lnfecha): array
	{
		global $goDb;
		$lcErrorConsulta = '';
		$laDatosDocumentosError = [];
		
		try {
			$laDatosDocumentosError = $goDb
				->select('DISTINCT E.FACTE FACTURA, E.NOTAE NOTA,SUBSTR(F.TIPR,3,2) TIPO_DOC, TRIM(E.DESCE) INFO, F.FECC FECHAG, F.HORC HORAG, MIN(E.FECC) FECHAE, MIN(E.HORC) HORAE')
				->from('FEMOVE E')
				->innerJoin('FEMOV F', 'E.FACTE=F.FACT AND E.NOTAE=F.NOTA')
				->leftJoin('TABMAE T', "T.TIPTMA='FACTELE' AND T.CL1TMA='ESTADO' AND T.CL2TMA=F.ESTA")
				->where("F.FECC $lnfecha AND F.ESTA = '04'")
				->groupBy("E.FACTE,E.NOTAE,F.TIPR,E.DESCE,F.FECC,F.HORC")
				->orderBy('E.FACTE DESC')
				->getAll('array');
		} catch (Exception $e) {
			$lcErrorConsulta = $e->getMessage();
		}

		return [
			"error" => $lcErrorConsulta,
			"informacionDoc" => $laDatosDocumentosError
		];
	}

	/*
	 *	Retorna arreglo con la lista de documentos pendientes por proveedor
	 */
	public function obtenerDocumentosPendientes(string $lnfecha): array
	{
		global $goDb;
		$lcErrorConsulta = '';
		$laDatosDocumentosPendientes = [];
		try {
			$laDatosDocumentosPendientes = $goDb
				->select('DISTINCT FACT FACTURA, NOTA NOTA, SUBSTR(TIPR,3,2) TIPO_DOC, FECC FECHAG, HORC HORAG')
				->from('FEMOV')
				->where("FECC $lnfecha AND ESTA = '03'")
				->orderBy('FACT DESC')
				->getAll('array');
		} catch (Exception $e) {
			$lcErrorConsulta = $e->getMessage();
		}

		return [
			"error" => $lcErrorConsulta,
			"informacionDoc" => $laDatosDocumentosPendientes
		];
	}

	/*
	 *	Retorna arreglo con la lista de documentos pendientes por enviar a proveedor
	 */
	public function obtenerDocumentosPorEnviar(string $lnfecha): array
	{
		global $goDb;
		$lcErrorConsulta = '';
		$laDatosDocumentosPorEnviar = [];
		try {
			$laDatosDocumentosPorEnviar = $goDb
				->select('DISTINCT FACT FACTURA, NOTA NOTA, SUBSTR(TIPR,3,2) TIPO_DOC, FECC FECHAG, HORC HORAG')
				->from('FEMOV')
				->where("FECC $lnfecha AND ESTA = '00'")
				->orderBy('FACT DESC')
				->getAll('array');
		} catch (Exception $e) {
			$lcErrorConsulta = $e->getMessage();
		}
		return [
			"error" => $lcErrorConsulta,
			"informacionDoc" => $laDatosDocumentosPorEnviar
		];
	}


	/*
	 *	Retorna arreglo con la lista de documentos procesados con exito
	 */
	public function obtenerDocumentosExitosos(string $lnfecha): array
	{
		global $goDb;
		$lcErrorConsulta = '';
		$laDatosDocumentosExitosos = [];
		try {
			$laDatosDocumentosExitosos = $goDb
				->select('DISTINCT FACT FACTURA, NOTA NOTA, SUBSTR(TIPR,3,2) TIPO_DOC, FECC FECHAG, HORC HORAG, CUFE INFO')
				->from('FEMOV')
				->where("FECC $lnfecha AND ESTA = '02'")
				->orderBy('FACT DESC')
				->getAll('array');
		} catch (Exception $e) {
			$lcErrorConsulta = $e->getMessage();
		}

		return [
			"error" => $lcErrorConsulta,
			"informacionDoc" => $laDatosDocumentosExitosos
		];
	}

	/*
	 *	Retorna arreglo con los valores para mostrar alertas
	 */
	public function obtenerDatosPrametrica(): array
	{
		global $goDb;
		$lcErrorConsulta = '';
		$laDatosParametricas = [];
		try {
			$laDatosParametricas = $goDb
				->select('TRIM(CL2TMA) TIPO_DATO, TRIM(CL3TMA) VALOR')
				->from('TABMAE')
				->where("TIPTMA = 'DASHBOAR' AND OP1TMA = '1'")
				->getAll('array');
		} catch (Exception $e) {
			$lcErrorConsulta = $e->getMessage();
		}

		return [
			"error" => $lcErrorConsulta,
			"informacionParametrosAlertas" => $laDatosParametricas
		];
	}
}
