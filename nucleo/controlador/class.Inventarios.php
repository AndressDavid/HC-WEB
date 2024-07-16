<?php
namespace NUCLEO;

require_once ('class.Db.php') ;
use NUCLEO\Db;

class Inventarios
{
	protected $oDb;

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
    }

	/*
	 *	Llama al procedimiento almacenado, para traer el número de documento
	 *	tcStoreProcedure	String - Store procedure
	 *	tcTipoDoc			String - Tipo documento
	 *	tcUsuario			String - Usuriario realiza
	 *	@return el número de documento
	*/
	public function numeroDocumentoTransaccion($tcStoreProcedure='',$tcTipoDoc='',$tcUsuario='')
	{
		$lcNroTransaccion="";
		$laParamIn = [  
			'tipoDoc'=>[$tcTipoDoc, \PDO::PARAM_STR],
			'Usuario'=>[$tcUsuario, \PDO::PARAM_STR],
		];
		 
		$laParamOut = [ 'Retorno'=>[\PDO::PARAM_STR, 9]];
		$lcNroTransaccion = $this->oDb->storedProcedure($tcStoreProcedure, $laParamIn, $laParamOut);
		return $lcNroTransaccion;
    }
	
	/*
	 *	Llama al procedimiento almacenado, crear cabecera transacción 
	 *	tcStoreProcedure		String - Store procedure
	 *	taDatos					Array  - datos a enviar
	 *	@return, no retorna datos
	*/
	public function cabeceraTransaccion($taDatos)
	{
		$lcRetornar="";
		$tcStoreProcedure=$taDatos['procedure'];
		
		$laParamIn =[
			'TDOCTR' =>[$taDatos['tipodocumento'], \PDO::PARAM_STR],
			'NDOCTR' =>[$taDatos['nrodocumento'], \PDO::PARAM_STR],
			'ND1CTR' =>[$taDatos['nroingreso'], \PDO::PARAM_STR],
			'NI1CTR' =>[' ', \PDO::PARAM_STR],
			'FE1CTR' =>[$taDatos['fechacrea'], \PDO::PARAM_STR],
			'ND2CTR' =>['', \PDO::PARAM_STR],
			'NI2CTR' =>['', \PDO::PARAM_STR],
			'FE2CTR' =>['0', \PDO::PARAM_STR],
			'ND3CTR' =>['', \PDO::PARAM_STR],
			'NI3CTR' =>['', \PDO::PARAM_STR],
			'FE3CTR' =>['0', \PDO::PARAM_STR],
			'BO1CTR' =>['', \PDO::PARAM_STR],
			'BO2CTR' =>['', \PDO::PARAM_STR],
			'CC1CTR' =>['', \PDO::PARAM_STR],
			'CC2CTR' =>['', \PDO::PARAM_STR],
			'TO1CTR' =>['0', \PDO::PARAM_STR],
			'TO2CTR' =>['0', \PDO::PARAM_STR],
			'TO3CTR' =>['0', \PDO::PARAM_STR],
			'IM1CTR' =>['0', \PDO::PARAM_STR],
			'IM2CTR' =>['0', \PDO::PARAM_STR],
			'IM3CTR' =>['0', \PDO::PARAM_STR],
			'IM4CTR' =>['0', \PDO::PARAM_STR],
			'IM5CTR' =>['0', \PDO::PARAM_STR],
			'IM6CTR' =>['0', \PDO::PARAM_STR],
			'MNDCTR' =>['', \PDO::PARAM_STR],
			'CPBCTR' =>['', \PDO::PARAM_STR],
			'LEGCTR' =>['', \PDO::PARAM_STR],
			'PAQCTR' =>['W', \PDO::PARAM_STR],
			'STSCTR' =>['0', \PDO::PARAM_STR],
			'USCCTR' =>[$taDatos['usuariocrea'], \PDO::PARAM_STR],
			'PGCCTR' =>[$taDatos['programacrea'], \PDO::PARAM_STR],
			'DTCCTR' =>[$taDatos['fechacrea'], \PDO::PARAM_STR],
			'HOCCTR' =>[$taDatos['horacrea'], \PDO::PARAM_STR],
			'USRCTR' =>[$taDatos['usuariocrea'], \PDO::PARAM_STR],
			'PGMCTR' =>[$taDatos['programacrea'], \PDO::PARAM_STR],
			'DTECTR' =>[$taDatos['fechacrea'], \PDO::PARAM_STR],
			'HORCTR' =>[$taDatos['horacrea'], \PDO::PARAM_STR],
			'ACCION' =>[$taDatos['accion'], \PDO::PARAM_STR],
		];
		$laParamOut = [ 'Retorno'=>[\PDO::PARAM_STR, 9]];
		$lcRetornar=$this->oDb->storedProcedure($tcStoreProcedure, $laParamIn, $laParamOut);
		return $lcRetornar;
    }
	
	
	/*
	 *	Llama al procedimiento almacenado, crear detalle transacción 
	 *	tcStoreProcedure		String - Store procedure
	 *	taDatos					Array  - datos a enviar
	 *	@return, no retorna datos
	*/
 	public function detalleTransaccion($taDatos)
	{
		$lcRetornar="";
		$tcStoreProcedure=$taDatos['procedure'];
	
		$laParamIn =[
			'TIDDET' =>[$taDatos['tipodocumento'], \PDO::PARAM_STR],
			'NDODET' =>[$taDatos['nrodocumento'], \PDO::PARAM_STR],
			'SEQDET' =>[$taDatos['consecutivo'], \PDO::PARAM_STR],
			'PR1DET' =>[$taDatos['codigoinsumo'], \PDO::PARAM_STR],
			'PR2DET' =>['', \PDO::PARAM_STR],
			'CC1DET' =>['', \PDO::PARAM_STR],
			'CC2DET' =>['', \PDO::PARAM_STR],
			'DO1DET' =>['', \PDO::PARAM_STR],
			'DO2DET' =>['', \PDO::PARAM_STR],
			'DO3DET' =>['', \PDO::PARAM_STR],
			'BO1DET' =>['', \PDO::PARAM_STR],
			'BO2DET' =>['', \PDO::PARAM_STR],
			'FE1DET' =>['0', \PDO::PARAM_STR],
			'FE2DET' =>['0', \PDO::PARAM_STR],
			'CA1DET' =>[$taDatos['cantidad'], \PDO::PARAM_STR],
			'CA2DET' =>['0', \PDO::PARAM_STR],
			'UN1DET' =>[$taDatos['unidad'], \PDO::PARAM_STR],
			'UN2DET' =>['', \PDO::PARAM_STR],
			'VLRDET' =>['0', \PDO::PARAM_STR],
			'VLRDE2' =>['0', \PDO::PARAM_STR],
			'IM1DET' =>['0', \PDO::PARAM_STR],
			'IM2DET' =>['0', \PDO::PARAM_STR],
			'IM3DET' =>['0', \PDO::PARAM_STR],
			'IM4DET' =>['0', \PDO::PARAM_STR],
			'IM5DET' =>['0', \PDO::PARAM_STR],
			'IM6DET' =>['0', \PDO::PARAM_STR],
			'TASDET' =>['0', \PDO::PARAM_STR],
			'COADET' =>['0', \PDO::PARAM_STR],
			'CODDET' =>['0', \PDO::PARAM_STR],
			'SDADET' =>['0', \PDO::PARAM_STR],
			'SDDDET' =>['0', \PDO::PARAM_STR],
			'NIDDET' =>['', \PDO::PARAM_STR],
			'COPDET' =>['', \PDO::PARAM_STR],
			'STSDET' =>['0', \PDO::PARAM_STR],
			'USRDET' =>[$taDatos['usuariocrea'], \PDO::PARAM_STR],
			'PGMDET' =>[$taDatos['programacrea'], \PDO::PARAM_STR],
			'DTEDET' =>[$taDatos['fechacrea'], \PDO::PARAM_STR],
			'HORDET' =>[$taDatos['horacrea'], \PDO::PARAM_STR],
			'ACCION' =>[$taDatos['accion'], \PDO::PARAM_STR],
			'ACAN2' =>['', \PDO::PARAM_STR],
		];

		$laParamOut = [ 'Retorno'=>[\PDO::PARAM_STR, 9]];
		$lcRetornar=$this->oDb->storedProcedure($tcStoreProcedure, $laParamIn, $laParamOut);
		return $lcRetornar;
    }
 	
	/*
	 *	Llama al procedimiento almacenado, para .........
	 *	tcStoreProcedure	String - Store procedure
	 *	tipo documento		String - Tipo documento
	 *	número documento	String - Número documento
	 *	Usuario				String - Usuario realiza
	 *	Acción				String - Acción
	 *	@return 
	*/
	public function kardexTransaccion($taDatos)
	{
		$lcRetornar="";
		$tcStoreProcedure=$taDatos['procedure'];

		 $laParamIn = [  
			'tipodocumento' =>[$taDatos['tipodocumento'], \PDO::PARAM_STR],
			'nrodocumento' 	=>[$taDatos['nrodocumento'], \PDO::PARAM_STR],
			'usuario'		=>[$taDatos['usuariocrea'], \PDO::PARAM_STR],
			'accion'		=>[$taDatos['accion'], \PDO::PARAM_STR],
		 ];

		$laParamOut = [ 'Retorno'=>[\PDO::PARAM_STR, 9]];
		$lcRetornar=$this->oDb->storedProcedure($tcStoreProcedure, $laParamIn, $laParamOut);
		return $lcRetornar;
    }
	
	/*
	 *	Llama al procedimiento almacenado, para .........
	 *	tcStoreProcedure	String - Store procedure
	 *	tipo documento		String - Tipo documento
	 *	número documento	String - Número documento
	 *	Acción				String - Acción
	 *	@return 
	*/
	public function kardexCumTransaccion($taDatos)
	{
		$lcRetornar="";
		$tcStoreProcedure=$taDatos['procedure'];

		$laParamIn = [  
			'tipodocumento' =>[$taDatos['tipodocumento'], \PDO::PARAM_STR],
			'nrodocumento' 	=>[$taDatos['nrodocumento'], \PDO::PARAM_STR],
			'accion'		=>[$taDatos['accion'], \PDO::PARAM_STR],
		];
		$laParamOut = [ 'Retorno'=>[\PDO::PARAM_STR, 9]];
		$lcRetornar=$this->oDb->storedProcedure($tcStoreProcedure, $laParamIn, $laParamOut);
		return $lcRetornar;
    }

}