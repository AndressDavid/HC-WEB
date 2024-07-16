<?php
namespace NUCLEO;

require_once 'class.Persona.php';
require_once 'class.PlanPaciente.php';

use NUCLEO\Persona;

class Paciente
    extends Persona {

	public $nNumHistoria = 0;
	public $cCodOcupacion = '';
	public $cOcupacion = '';
	public $cDireccion = '';
	public $cTelefono = '';
	public $aPlanes = array();


    public function __construct (){
    }

	/*
	 *	Parametro $tnIngreso correspode al número de ingreso del paciente
	 *
	*/
    public function cargarPaciente($tcId="", $tnId=0, $tnIngreso=0, $tcCargarPlanes=true)
	{
		$this->setTipoId($tcId);
		$this->nId = $tnId;

		global $goDb;
		if(isset($goDb)){
			if(empty($tcId)==false && $tnId>0){

				// Información de la tabla paciente
				$laPaciente = $goDb
					->select([	'P.TIDPAC','P.NIDPAC','P.NM1PAC','P.NM2PAC','P.AP1PAC','P.AP2PAC',
								'P.MAIPAC','P.FNAPAC','P.SEXPAC','P.NHCPAC','P.OCUPAC','P.DR1PAC',
								'P.PARPAC','P.DERPAC','P.MURPAC',
								'A.TP1PAL TELPAC','A.TP2PAL','A.CP1PAL','A.CP2PAL',
								'IFNULL(A.OP5PAL,\'\') PASSPT','IFNULL(A.OP6PAL,\'\') PERESP',
								'IFNULL(T.DE2TMA,\'\') TIPPER','IFNULL(Z.DE2TMA,\'\') DESCSEXO',])
					->from('RIAPAC P')
					->leftJoin('PACALT A', 'P.TIDPAC=A.TIDPAL AND P.NIDPAC=A.NIDPAL', null)
					->leftJoin('TABMAE T', 'T.TIPTMA=\'DATING\' AND T.CL1TMA=\'TIPPERM\' AND T.CL2TMA=P.TIDPAC', null)
					->leftJoin('TABMAE Z', 'Z.TIPTMA=\'SEXPAC\' AND Z.CL1TMA=P.SEXPAC', null)
					->where(['P.TIDPAC'=>$tcId, 'P.NIDPAC'=>$tnId])
					->get('array');
 				if(is_array($laPaciente)==true){
					if(count($laPaciente)>0){
						$this->setTipoId(trim($laPaciente['TIDPAC']));
						$this->nId = intval($laPaciente['NIDPAC']);
						$this->cPasaporte = trim($laPaciente['PASSPT'] ?? '');
						$this->cTipoPermiso = trim($laPaciente['TIPPER'] ?? '');
						$this->cIdPermiso = trim($laPaciente['PERESP'] ?? '');
						$this->cNombre1 = trim($laPaciente['NM1PAC']);
						$this->cNombre2 = trim($laPaciente['NM2PAC']);
						$this->cApellido1 = trim($laPaciente['AP1PAC']);
						$this->cApellido2 = trim($laPaciente['AP2PAC']);
						$this->cEmail = trim($laPaciente['MAIPAC']);
						$this->nNacio = intval(trim($laPaciente['FNAPAC']));
						$this->cSexo = $laPaciente['SEXPAC'];
						$this->cDescSexo = $laPaciente['DESCSEXO'];
						$this->nNumHistoria = intval($laPaciente['NHCPAC']);
						$this->cCodOcupacion = trim($laPaciente['OCUPAC']);
						$this->cDireccion = trim($laPaciente['DR1PAC']);
						$this->cTelefono = $laPaciente['TELPAC']>0 ? trim($laPaciente['TELPAC']) : '';
						$this->cTelefono2 = $laPaciente['TP2PAL']>0 ? trim($laPaciente['TP2PAL']) : '';
						$this->cCelular = $laPaciente['CP1PAL']>0 ? trim($laPaciente['CP1PAL']) : '';
						$this->cCelular2 = $laPaciente['CP2PAL']>0 ? trim($laPaciente['CP2PAL']) : '';
						$this->nPais = intval($laPaciente['PARPAC']);
						$this->cDepartamento = $laPaciente['DERPAC'];
						$this->cMunicipio = $laPaciente['MURPAC'];
					}
				}

				//Planes del paciente
				if ($tcCargarPlanes) {
					$laPlanes = $goDb
						->select('A.PLAEPP')
						->from('RIAEPP A')
						->where(['A.TIDEPP'=>$tcId, 'A.NIDEPP'=>$tnId])
						->orderBy('A.ORDEPP')
						->getAll('array');

					if(is_array($laPlanes)){
						if(count($laPlanes)>0){
							foreach($laPlanes as $laPlan){
								$this->aPlanes[] = new PlanPaciente($this->aTipoId['TIPO'], $this->nId, trim($laPlan['PLAEPP']));
							}
						}
					}
				}
			}
		}
    }

	/*
	 *	Obtiene descripción de la ocupación
	 */
	public function obtenerOcupacion()
	{
		if (!empty($this->cCodOcupacion)) {
			global $goDb;
			$loTabMae = $goDb->ObtenerTabMae('DE2TMA', 'CODOCU', ['CL1TMA'=>$this->cCodOcupacion]);
			$this->cOcupacion = trim(AplicacionFunciones::getValue($loTabMae,'DE2TMA',''));
		} else {
			$this->cOcupacion = '';
		}
		return $this->cOcupacion;
	}

	/*
	 *	Obtiene datos del paciente para una fecha determinada
	 *	Parametros:	$tcId		 Tipo de documento del paciente
	 *				$tnId		 Número de documento del paciente
	 *				$tnFechaHora Número que indica la fecha y hora a la que se debe recuperar la información
	 */
	public function cargarPacientePorFecha($tcId='', $tnId=0, $tnFechaHora=0)
	{
		$this->setTipoId('');
		$this->nId = 0;

		global $goDb;
		if(isset($goDb)){
			if( empty($tcId)==false && $tnId>0 ){

				$lcFechaHora = str_replace( '/', '', str_replace( '-', '', str_replace( ' ', '', str_replace( 'T', '', str_replace( ':', '', $tnFechaHora.'' ) ) ) ) );

				// Información de la tabla paciente
				$lcSql = "{$this->sqlPacientePorFecha()}
					WHERE	p.fecpad*1000000+p.horpad <= :fecpad
						AND p.tidpad = :tidpad
						AND p.nidpad = :nidpad
					ORDER BY p.fecpad*1000000+p.horpad DESC
					FETCH FIRST 1 ROWS ONLY";
				$laData = [
					':tidpad' => $tcId,
					':nidpad' => $tnId,
					':fecpad' => $lcFechaHora,
				];
				$goDb->clearBindValue();
				$laPacienteSql = $goDb->query($lcSql, $laData, true);
				$goDb->clearBindValue();

 				if (is_array($laPacienteSql)==true) {
					if (count($laPacienteSql)>0) {
						$laPaciente = array_map('trim', $laPacienteSql[0]);
						$this->setTipoId($laPaciente['TIPDOC']);
						$this->nId = intval($laPaciente['NUMDOC']);
						$this->cPasaporte = $laPaciente['NUMPASAPORTE'];
						$this->cTipoPermiso = trim($laPaciente['TIPPERMISO'] ?? '');
						$this->cIdPermiso = trim($laPaciente['NUMPERMISO'] ?? '');
						$this->cNombre1 = $laPaciente['NM1PAD'];
						$this->cNombre2 = $laPaciente['NM2PAD'];
						$this->cApellido1 = $laPaciente['AP1PAD'];
						$this->cApellido2 = $laPaciente['AP2PAD'];
						$this->nNacio = intval($laPaciente['FECHANAC']);
						$this->cSexo = $laPaciente['ABRSEXO'];
						$this->nNumHistoria = intval($laPaciente['NUMHC']);
						$this->cCodOcupacion = $laPaciente['CODOCUPA'];
						$this->cOcupacion = $laPaciente['OCUPACION'];
						$this->cEmail = $laPaciente['EMAIL'];
						$this->cDireccion = $laPaciente['DIRECCION'];
						$this->cTelefono = trim($laPaciente['TELEFONO']);
						$this->cTelefono = $laPaciente['TELEFONO']>0 ? trim($laPaciente['TELEFONO']) : '';
						$this->cTelefono2 = $laPaciente['TELEFONO2']>0 ? trim($laPaciente['TELEFONO2']) : '';
						$this->cCelular = $laPaciente['CELULAR']>0 ? trim($laPaciente['CELULAR']) : '';
						$this->cCelular2 = $laPaciente['CELULAR2']>0 ? trim($laPaciente['CELULAR2']) : '';
						$this->cCodigoPaisResidencia = $laPaciente['CODIGO_PAIS_RESIDENCIA']>0 ? $laPaciente['CODIGO_PAIS_RESIDENCIA'] : 0;
						$this->cDecrPaisResidencia = !empty($laPaciente['DESC_PAIS_RESIDENCIA']) ? trim(explode("-",trim($laPaciente['DESC_PAIS_RESIDENCIA']))[0]) : '';
						$this->cCodigoDptoResidencia = $laPaciente['CODIGO_DPTO_RESIDENCIA']>0 ? $laPaciente['CODIGO_DPTO_RESIDENCIA'] : 0;
						$this->cDecrDptoResidencia = !empty($laPaciente['DESC_DPTO_RESIDENCIA']) ? trim($laPaciente['DESC_DPTO_RESIDENCIA']) : '';
						$this->cCodigoCiudadResidencia = $laPaciente['CODIGO_CIUDAD_RESIDENCIA']>0 ? $laPaciente['CODIGO_CIUDAD_RESIDENCIA'] : 0;
						$this->cDecrCiudadResidencia = !empty($laPaciente['DESC_CIUDAD_RESIDENCIA']) ? trim($laPaciente['DESC_CIUDAD_RESIDENCIA']) : '';
						$this->cCodigoPertenenciaEtnica = $laPaciente['CODIGO_PERTENECIA_ETNICA'];
						$this->cDecrPertenenciaEtnica = $laPaciente['DESC_PERTENECIA_ETNICA'];
						$this->cCodigoNivelEducativo = $laPaciente['CODIGO_NIVEL_EDUCATIVO'];
						$this->cDecrNivelEducativo = $laPaciente['DESC_NIVEL_EDUCATIVO'];
					}
				}
			}
		}
	}

	/*
	 *	Retorna comando SQL para consulta de paciente por fecha
	 */
	public function sqlPacientePorFecha()
	{
		return 'SELECT	p.tidpad AS TipDoc,
						p.nidpad AS NumDoc,
						p.fnapad AS FechaNac,
						p.nm1pad, p.nm2pad, p.ap1pad, p.ap2pad,
						p.sexpad AS AbrSexo,
						p.ocupad AS CodOcupa,
						IFNULL(p.MAIPAD, \'\') AS email,
						p.dr1pad AS Direccion,
						p.tp1pad AS Telefono,
						p.TP2PAD AS Telefono2,
						p.CP1PAD AS Celular,
						p.CP2PAD AS Celular2,
						IFNULL(t.de2tma, \'\') AS Ocupacion,
						hc.nhcpad AS NumHC,
						p.fecpad*1000000+p.horpad AS FechaHora,
						SUBSTR(IFNULL(p.op6pad, \'\'), 1,99) AS NumPasaporte,
						SUBSTR(IFNULL(p.op6pad, \'\'),100, 40) AS NumPermiso,
						SUBSTR(IFNULL(u.de2tma, \'\'), 1, 2) AS TipPermiso,
						IFNULL(u.op5tma, \'\') AS NomPermiso,
						p.PARPAD AS CODIGO_PAIS_RESIDENCIA,
						IFNULL(f.DESPAI, \'\') AS DESC_PAIS_RESIDENCIA,
						p.DERPAD AS CODIGO_DPTO_RESIDENCIA,
						IFNULL(g.DESDEP, \'\') AS DESC_DPTO_RESIDENCIA,
						p.MURPAD AS CODIGO_CIUDAD_RESIDENCIA,
						IFNULL(h.DESCIU, \'\') AS DESC_CIUDAD_RESIDENCIA,
						SUBSTR(IFNULL(p.OP9PAD, \'\'), 1, 2) AS CODIGO_PERTENECIA_ETNICA,
						UPPER(SUBSTR(IFNULL(i.de2tma, \'\'), 1, 70)) AS DESC_PERTENECIA_ETNICA,
						SUBSTR(IFNULL(p.OP9PAD, \'\'), 3, 2) AS CODIGO_NIVEL_EDUCATIVO,
						UPPER(SUBSTR(IFNULL(j.de2tma, \'\'), 1, 40)) AS DESC_NIVEL_EDUCATIVO
				FROM pacdet AS p
					LEFT JOIN pacdet AS hc ON p.tidpad=hc.tidpad AND p.nidpad=hc.nidpad AND (
							SELECT fecpad*1000000+horpad FROM pacdet
							WHERE	tidpad=p.tidpad AND nidpad=p.nidpad
								AND nhcpad>0
								AND fecpad*1000000+horpad<=p.fecpad*1000000+p.horpad
							ORDER BY fecpad DESC,horpad DESC FETCH FIRST 1 ROWS ONLY
						) = hc.fecpad*1000000+hc.horpad
					LEFT JOIN tabmae AS t ON t.tiptma=\'CODOCU\' AND p.ocupad=t.cl1tma
					LEFT JOIN COMPAI AS f ON p.PARPAD=f.CODPAI
					LEFT JOIN COMDEP AS g ON p.PARPAD=g.PAIDEP AND p.DERPAD=g.CODDEP
					LEFT JOIN COMCIU AS h ON p.PARPAD=h.PAICIU AND p.DERPAD=h.DEPCIU AND p.MURPAD=h.CODCIU
					LEFT JOIN TABMAE AS i ON i.tiptma=\'DATING\' AND i.CL1TMA=\'PERETNI\' AND SUBSTR(IFNULL(p.OP9PAD, \'\'), 1, 2)=i.CL2TMA
					LEFT JOIN TABMAE AS j ON j.tiptma=\'DATING\' AND j.CL1TMA=\'NIVEDU\' AND SUBSTR(IFNULL(p.OP9PAD, \'\'), 3, 2)=j.CL2TMA
					LEFT JOIN tabmae AS u ON u.tiptma=\'DATING\' AND u.CL1TMA=\'TIPPERM\' AND p.tidpad=u.cl2tma ';
	}


	public function validarDocumento($tcDocumentoTipo='', $tnDocumentoNumero=0, $tlControlarIngresoAbierto=false, $tlCargarPaciente=false, $tlNoCargarDcumento=false, $tnIngresoAbierto=0)
	{
		$tcDocumentoTipo = trim(strtoupper(strval($tcDocumentoTipo)));
		$tnDocumentoNumero = intval($tnDocumentoNumero);
		$tlControlarIngresoAbierto = boolval($tlControlarIngresoAbierto);
		$tlCargarPaciente = boolval($tlCargarPaciente);
		$tlNoCargarDcumento = boolval($tlNoCargarDcumento);
		$tnIngresoAbierto = intval($tnIngresoAbierto);

		$lcInformacion = '';
		$llValido = true;
		$laResultado = ['VALIDO' => $llValido, 'INFORMACION' => $lcInformacion, 'PACIENTE' => array()];

		if(!empty($tcDocumentoTipo) && !empty($tnDocumentoNumero)){
			if($tlControlarIngresoAbierto==true){
				global $goDb;
				if(isset($goDb)){

					// Busca si paciente tiene ingreso abierto
					$laCampos = ['I.NIGING', 'I.FEIING'];
					$laIngresos = $goDb
						->select($laCampos)
						->from('RIAINGL10 I')
						->where('I.TIDING', '=', $tcDocumentoTipo)
						->where('I.NIDING', '=', $tnDocumentoNumero)
						->where('I.ESTING', '=','2')
						->getAll('array');

					if(is_array($laIngresos)==true){
						if(count($laIngresos)>0){
							foreach($laIngresos as $laIngreso){
								if($laIngreso['NIGING']!=$tnIngresoAbierto){
									$lcInformacion .= (empty($lcInformacion)?'':', ').'Ingreso No.'.strval($laIngreso['NIGING']).' del '.strval($laIngreso['FEIING']);
								}
							}
							$llValido = empty($lcInformacion);
						}
					}

					if($llValido == true){
						if($tlCargarPaciente == true){
							// Paciente
							$laCampos = [
										 'P.TIDPAC DOCUMENTO_TIPO',
										 'P.NIDPAC DOCUMENTO_NUMERO',
										 'P.NHCPAC HISTORIA',
										 'IFNULL(A.NM1PAL, P.NM1PAC) NOMBRE1',
										 'IFNULL(A.NM2PAL, P.NM2PAC) NOMBRE2',
										 'IFNULL(A.AP1PAL, P.AP1PAC) APELLIDO1',
										 'IFNULL(A.AP2PAL, P.AP2PAC) APELLIDO2',
										 'A.SEXPAL GENERO',
										 'A.OP9PAL GSRH',
										 'A.OP13AL DOCUMENTO_LUGAR_EXPEDICION',
										 'P.PAIPAC NACIO_PAIS',
										 'P.DEPPAC NACIO_DEPARTAMENTO',
										 'P.MUNPAC NACIO_MUNICIPIO',
										 'P.FNAPAC NACIO',
										 'P.SEXPAC SEXO',
										 'P.RAZPAC RAZA',
										 'P.GRUPAC GRUPO_ATENCION',
										 'P.CCOPAC CONSECUTIVO_CONSULTA',
										 'P.CCIPAC CONSECUTIVO_CITA',
										 'A.OP10AL ENVIAR_EPICRISIS_EMAIL',
										 'P.MAIPAC MAIL_PACIENTE',
										 'P.TELPAC TELEFONO1',
										 'A.TP2PAL TELEFONO2',
										 'A.CP1PAL TELEFONO3',
										 'A.CP2PAL TELEFONO4',
										 'P.PARPAC RESIDE_PAIS',
										 'P.DERPAC RESIDE_DEPARTAMENTO',
										 'P.MURPAC RESIDE_MUNICIPIO',
										 'P.DR1PAC RESIDE_DIRECCION',
										 'A.LOCPAL RESIDE_LOCALIDAD',
										 'P.DR2PAC RESIDE_BARRIO',
										 'P.ZORPAC RESIDE_ZONA',
										 'P.ZOOPAC RESIDE_ZONA_COMAPLENTO',
										 'P.OCUPAC LABORAL_OCUPACION',
										 'SPACE(2) LABORAL_TRABAJO',
										 'P.PAOPAC LABORAL_PAIS',
										 'P.DEOPAC LABORAL_DEPARTAMENTO',
										 'P.MUOPAC LABORAL_MUNICIPIO',
										 'P.DO1PAC LABORAL_DIRECCION',
										 'P.DO2PAC LABORAL_DIRECION_COMPLEMENTO',
										 'SPACE(2) LABORAL_EMPRESA',
										 'SPACE(2) LABORAL_CARGO',
										 'SPACE(2) LABORAL_ANTIGUEDAD',
										 'SPACE(2) REFERENCIA_MOMBRE',
										 'SPACE(2) REFERENCIA_DIRECCION',
										 'SPACE(2) REFERENCIA_TELEFONO'	,
										 'P.BARPAC RESIDE_BARRIO_ADICIONAL',
										 'P.TETPAC LABORAL_TELEFONO',
										 'P.FA1PAC ESTADO_CIVIL',
										 'P.ESTPAC ESTADO',
										 'P.FN1PAC FILLER_1',
										 'P.FN2PAC FILLER_2',
										 'P.USRPAC CREO_USUARIO',
										 'P.PGMPAC CREO_PROGRAMA',
										 'P.FECPAC CREO_FECHA',
										 'P.HORPAC CREO_HORA',
										 'P.UMOPAC MODIFICO_USUARIO',
										 'P.PMOPAC MODIFICO_PROGRAMA',
										 'P.FMOPAC MODIFICO_FECHA',
										 'P.HMOPAC MODIFICO_HORA',
										 'SPACE(2) PERTENECIA_ETNICA',
										 'SPACE(2) NIVEL_EDUCATIVO',
										 'A.OP2PAL ETNICOEDUCATIVO',
										];
							$laPaciente = $goDb
								->select($laCampos)
								->from('RIAPACL02 P')
								->leftJoin('PACALT A', 'P.TIDPAC=A.TIDPAL AND P.NIDPAC=A.NIDPAL', null)
								->where('P.TIDPAC', '=', $tcDocumentoTipo)
								->where('P.NIDPAC', '=', $tnDocumentoNumero)
								->get('array');

								$llValido = false;
								if(is_array($laPaciente)==true){
									if(count($laPaciente)>0){
										$llValido = true;
										$laResultado['PACIENTE'] = $laPaciente;
										$laResultado['PACIENTE']['PERTENECIA_ETNICA'] = (strlen($laPaciente['ETNICOEDUCATIVO'])>=4?substr($laPaciente['ETNICOEDUCATIVO'],0,2):'');
										$laResultado['PACIENTE']['NIVEL_EDUCATIVO'] = (strlen($laPaciente['ETNICOEDUCATIVO'])>=4?substr($laPaciente['ETNICOEDUCATIVO'],2,2):'');
									}
								}

							// Paciente Alterno
							if($llValido==true){
								$laCampos = [
												'OCPPAG LABORAL_TRABAJO',
												'ETPPAG LABORAL_EMPRESA',
												'CAPPAG LABORAL_CARGO',
												'ANPPAG LABORAL_ANTIGUEDAD',
												'RFPPAG REFERENCIA_MOMBRE',
												'DFPPAG REFERENCIA_DIRECCION',
												'TFPPAG REFERENCIA_TELEFONO'
											];
								$laPaciente = $goDb
									->select($laCampos)
									->from('PAGARE P')
									->where('P.TIPPAG', '=', $tcDocumentoTipo)
									->where('P.IDPPAG', '=', $tnDocumentoNumero)
									->get('array');

								if(is_array($laPaciente)==true){
									if(count($laPaciente)>0){
										$laResultado['PACIENTE']['LABORAL_TRABAJO'] = $laPaciente['LABORAL_TRABAJO'];
										$laResultado['PACIENTE']['LABORAL_EMPRESA'] = $laPaciente['LABORAL_EMPRESA'];
										$laResultado['PACIENTE']['LABORAL_CARGO'] = $laPaciente['LABORAL_CARGO'];
										$laResultado['PACIENTE']['LABORAL_ANTIGUEDAD'] = $laPaciente['LABORAL_ANTIGUEDAD'];
										$laResultado['PACIENTE']['REFERENCIA_MOMBRE'] = $laPaciente['REFERENCIA_MOMBRE'];
										$laResultado['PACIENTE']['REFERENCIA_DIRECCION'] = $laPaciente['REFERENCIA_DIRECCION'];
										$laResultado['PACIENTE']['REFERENCIA_TELEFONO'] = $laPaciente['REFERENCIA_TELEFONO'];
									}
								}
							}

							foreach($laResultado['PACIENTE'] as $lcKey => $lvValue){
								if(gettype($lvValue)=='string'){
									$laResultado['PACIENTE'][$lcKey] = trim($lvValue);
								}
							}
						}
					}
				}
			}
		}

		$laResultado['VALIDO'] = $llValido;
		$laResultado['INFORMACION'] = $lcInformacion;

		return $laResultado;
	}


	public function consultarListaGeneros()
	{
		$laParametros=[];
		global $goDb;
		if(isset($goDb)){
			$laTabla = $goDb
				->select('trim(CL1TMA) CODIGO, trim(DE2TMA) DESCRIPCION, trim(OP1TMA) HOMOLOGO, TRIM(OP5TMA) IMAGEN')
				->from('TABMAE')
				->where('TIPTMA', '=', 'SEXPAC')
				->where('ESTTMA', '=', '')
				->getAll('array');
			if (is_array($laTabla)){
				foreach($laTabla as $laFila){
					$laFila = array_map('trim', $laFila);
					$laParametros[$laFila['CODIGO']] = $laFila;
				}
			}
		}
		return $laParametros;
	}


	public function actualizarCorreo($tcTipoDoc, $tnNumeroDoc, $tcCorreo)
	{
		$laRespuesta = ['valida'=>true, 'mensaje'=>'', 'error'=>[]];

		require_once __DIR__ . '/class.TiposDocumento.php';
		$loTiposDocumento = new TiposDocumento('1');
		$laTiposDoc = array_keys($loTiposDocumento->aTipos);

		// Validar datos
		if (!in_array($tcTipoDoc, $laTiposDoc)) {
			$laRespuesta['valida'] = false;
			$laRespuesta['error'][] = 'Tipo de documento no válido';
		}

		if ($laRespuesta['valida']) {
			global $goDb;

			// Consultar paciente
			$laTabla = $goDb
				->from('RIAPAC P')
				->innerJoin('PACALT A', 'P.TIDPAC=A.TIDPAL AND P.NIDPAC=A.NIDPAL')
				->where(['P.TIDPAC'=>$tcTipoDoc, 'P.NIDPAC'=>$tnNumeroDoc])
				->getAll('array');
			if ($goDb->numRows()>0){

				// Validar correo
				if ((boolean) filter_var($tcCorreo, FILTER_VALIDATE_EMAIL)) {

					$ltAhora = new \DateTime($goDb->fechaHoraSistema());
					$lcUsuAct = isset($_SESSION[HCW_NAME]) ? $_SESSION[HCW_NAME]->oUsuario->getUsuario() : '';
					$lcPrgAct = 'ACTMAILWEB';
					$lcFecAct = $ltAhora->format('Ymd');
					$lcHorAct = $ltAhora->format('His');

					// Actualizar correo

					// Tabla de pacientes
					$goDb
						->from('RIAPAC')
						->where(['TIDPAC'=>$tcTipoDoc, 'NIDPAC'=>$tnNumeroDoc])
						->actualizar([
							'MAIPAC'=>\substr($tcCorreo,0,60),
							'UMOPAC'=>$lcUsuAct,
							'PMOPAC'=>$lcPrgAct,
							'FMOPAC'=>$lcFecAct,
							'HMOPAC'=>$lcHorAct,
						]);
					// Tabla de pacientes alterna
					$goDb
						->from('PACALT')
						->where(['TIDPAL'=>$tcTipoDoc, 'NIDPAL'=>$tnNumeroDoc])
						->actualizar([
							'MAIPAL'=>\substr($tcCorreo,0,220),
							'UMOPAL'=>$lcUsuAct,
							'PMOPAL'=>$lcPrgAct,
							'FMOPAL'=>$lcFecAct,
							'HMOPAL'=>$lcHorAct,
						]);
					// Tabla de usuarios de portal pacientes
					$goDb
						->from('PACMENSEG')
						->where(['IDTIPO'=>$tcTipoDoc, 'IDNUME'=>$tnNumeroDoc])
						->actualizar([
							'CORREO'=>\substr($tcCorreo,0,50),
							'UMOMEN'=>$lcUsuAct,
							'PMOMEN'=>$lcPrgAct,
							'FMOMEN'=>$lcFecAct,
							'HMOMEN'=>$lcHorAct,
						]);
					// Tabla de detalle de pacientes
					$goDb
						->from('PACDET')
						->insertar([
							'TIDPAD'=>$laTabla[0]['TIDPAL'],
							'NIDPAD'=>$laTabla[0]['NIDPAL'],
							'NM1PAD'=>trim($laTabla[0]['NM1PAL']),
							'NM2PAD'=>trim($laTabla[0]['NM2PAL']),
							'AP1PAD'=>trim($laTabla[0]['AP1PAL']),
							'AP2PAD'=>trim($laTabla[0]['AP2PAL']),
							'PAIPAD'=>$laTabla[0]['PAIPAC'],
							'DEPPAD'=>$laTabla[0]['DEPPAC'],
							'MUNPAD'=>$laTabla[0]['MUNPAC'],
							'FNAPAD'=>$laTabla[0]['FNAPAL'],
							'SEXPAD'=>$laTabla[0]['SEXPAL'],
							'RAZPAD'=>$laTabla[0]['RAZPAC'],
							'GRUPAD'=>$laTabla[0]['GRUPAC'],
							'OCUPAD'=>$laTabla[0]['OCUPAC'],
							'MAIPAD'=>\substr($tcCorreo,0,220),
							'CCOPAD'=>$laTabla[0]['CCOPAC'],
							'CCIPAD'=>$laTabla[0]['CCIPAC'],
							'NHCPAD'=>$laTabla[0]['NHCPAC'],
							'PARPAD'=>$laTabla[0]['PARPAL'],
							'DERPAD'=>$laTabla[0]['DERPAL'],
							'MURPAD'=>$laTabla[0]['MURPAL'],
							'LOCPAD'=>$laTabla[0]['LOCPAL'],
							'DR1PAD'=>trim($laTabla[0]['DIRPAL']),
							'BARPAD'=>trim($laTabla[0]['DR2PAC']),
							'TP1PAD'=>$laTabla[0]['TP1PAL'],
							'TP2PAD'=>$laTabla[0]['TP2PAL'],
							'CP1PAD'=>$laTabla[0]['CP1PAL'],
							'CP2PAD'=>$laTabla[0]['CP2PAL'],
							'ZORPAD'=>$laTabla[0]['ZORPAC'],
							'OP1PAD'=>$laTabla[0]['OP1PAL'],
							'OP2PAD'=>$laTabla[0]['OP2PAL'],
							'OP3PAD'=>$laTabla[0]['OP3PAL'],
							'OP4PAD'=>$laTabla[0]['OP4PAL'],
							'OP5PAD'=>$laTabla[0]['OP5PAL'],
							'OP6PAD'=>$laTabla[0]['OP6PAL'],
							'OP7PAD'=>$laTabla[0]['OP7PAL'],
							'OP8PAD'=>$laTabla[0]['OP8PAL'],
							'OP9PAD'=>$laTabla[0]['OP9PAL'],
							'USRPAD'=>$lcUsuAct,
							'PGMPAD'=>$lcPrgAct,
							'FECPAD'=>$lcFecAct,
							'HORPAD'=>$lcHorAct,
						]);

				} else {
					$laRespuesta['valida'] = false;
					$laRespuesta['error'][] = 'Correo inválido.';
				}

			} else {
				$laRespuesta['valida'] = false;
				$laRespuesta['error'][] = 'Paciente no se encuentra en la base de datos.';
			}
		}

		return $laRespuesta;
	}

}
?>