<?php
namespace NUCLEO;

use NUCLEO\Cup;
use NUCLEO\Diagnostico;
use NUCLEO\ParametrosConsulta;

require_once __DIR__ .'/class.Cup.php';
require_once __DIR__ .'/class.Diagnostico.php';
require_once __DIR__ . '/class.ParametrosConsulta.php';


class Doc_Preanestesia
{
	protected $oDb;
	protected $aDataDoc = [];		// Datos del documento sin organizar
	protected $aDocumento = [];		// Datos del documento
	protected $aListas = [];		// Listas para pre-anestesia
	protected $aTitulos = [];		// Títulos
	protected $aTtlPrincipal = [];	// Títulos principales
	protected $aDental = [];		// Config Dental
	protected $aLog = [];			// Log de guardado
	protected $aSep = ['~',':'];
	protected $oCup;
	protected $oDx;
	protected $aReporte = [
					'cTitulo' => 'VALORACION ANESTESICA',
					'lMostrarFechaRealizado' => false,
					'lMostrarViaCama' => false,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => '',
					'cTxtLuegoDeCup' => '',
					'aCuerpo' => [],
					'aFirmas' => [],
					'aNotas' => ['notas'=>true,],
				];


	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;

		// Objetos para obtener descripciones de CUPS y Dx
		$this->oCup = new Cup();
		$this->oDx  = new Diagnostico();

		// Listas de configuración
		$this->obtenerListas();
		$this->obtenerTitulos();
		$this->obtenerDental();

		$this->cTxtVacio = $goDb->obtenerTabMae('de2tma', 'ANSIA', 'cl1tma=\'TXTVACIO\' AND cl2tma<>\'\'', 'cl2tma DESC');
	}

	public function retornarDocumento($taData)
	{
		$this->consultarDatos($taData);
		$this->organizarDatos($taData);
		$this->prepararInforme($taData);
		return $this->aReporte;
	}

	private function consultarDatos($taData)
	{
		// Consulta de datos del documento
		$laDoc = $this->oDb
			->select('IN1VAN,IN2VAN,CNLVAN,DESVAN,OP1VAN,USRVAN,PGMVAN,FECVAN,HORVAN,UMOVAN,PMOVAN,FMOVAN,HMOVAN')
			->from('ANEVALL01')
			->where([
				'INGVAN'=>$taData['nIngreso'],
				'TIPVAN'=>'ANES',
				'CCIVAN'=>$taData['nConsecCita'],
			])
			->orderBy('IN1VAN,IN2VAN,CNLVAN')
			->getAll(true);
		if (is_array($laDoc)) {
			if (count($laDoc)>0) {
				$this->aLog = [
					'USRVAN'=>$laDoc[0]['USRVAN'],
					'PGMVAN'=>$laDoc[0]['PGMVAN'],
					'FECVAN'=>$laDoc[0]['FECVAN'],
					'HORVAN'=>$laDoc[0]['HORVAN'],
					'UMOVAN'=>$laDoc[0]['UMOVAN'],
					'PMOVAN'=>$laDoc[0]['PMOVAN'],
					'FMOVAN'=>$laDoc[0]['FMOVAN'],
					'HMOVAN'=>$laDoc[0]['HMOVAN'],
				];
				$lnInd1=$lnInd2='';
				$lnI=-1;
				foreach($laDoc as $laFila){
					if ($lnInd1==$laFila['IN1VAN'] && $lnInd2==$laFila['IN2VAN']) {
						$this->aDataDoc[$lnI]['DESVAN'].=$laFila['DESVAN'];
					} else {
						$lnI++;
						$this->aDataDoc[$lnI]=[
							'IN1VAN'=>$laFila['IN1VAN'],
							'IN2VAN'=>$laFila['IN2VAN'],
							'DESVAN'=>$laFila['DESVAN'],
							'OP1VAN'=>trim($laFila['OP1VAN']),
						];
						$lnInd1=$laFila['IN1VAN'];
						$lnInd2=$laFila['IN2VAN'];
					}
				}
				for($lnIndx=0; $lnIndx<$lnI; $lnIndx++){
					$this->aDataDoc[$lnIndx]['DESVAN']=trim($this->aDataDoc[$lnIndx]['DESVAN']);
				}
			}
		}
	}


	private function organizarDatos($taData)
	{
		$this->aDocumento['datos']=[];
		$laIndexTit = AplicacionFunciones::getColumn($this->aTitulos, 'INDICE', true);
		$lnIndGineco = 0;

		foreach($this->aDataDoc as $laData) {
			switch(true) {

				// Lugar y fecha de valoración
				case $laData['IN1VAN']=='10':
					$laVals=$this->separaDatos($laData['DESVAN']);
					$this->aDocumento['FechaDem'] = $laVals['FECHA']??'';
					$this->aDocumento['LugarDem'] = empty($laVals['LUGRE']) ? '' : ( $this->aListas['LUGRE'][$laVals['LUGRE']]??'' );
					if(isset($laVals['CAUSAEXT'])){
						$loTipoCausa = new ParametrosConsulta();
						$loTipoCausa->ObtenerTipoCausa();
						$this->aDocumento['CausaExt'] = $loTipoCausa->tipoCausa($laVals['CAUSAEXT']);
						unset($loTipoCausa);
					}
					break;

				// Checkbox
				case $laData['OP1VAN']=='C':
					$laVals=$this->separaDatos($laData['DESVAN']);
					foreach($laVals as $lcIndx=>$laVal){
						if (intval($laVal)>0) {
							$lnIndxTtl = $this->buscarTitulo($laData['IN1VAN'], $lcIndx);
							if (!$lnIndxTtl===false){
								$laTtl=$this->aTitulos[$lnIndxTtl];
								$lnCod=intval($laTtl['CODIGO']/10);
								if ($laTtl['CODIGO']=='360' && $laTtl['INDICE']=='GIOBS') {
									$lnIndGineco = count($this->aDocumento['datos']);
								}
								$this->aDocumento['datos'][] = [
									'indice'	=>$laTtl['ORDEN'],
									'codigo'	=>$lnCod,
									'tipo'		=>$laData['OP1VAN'],
									'coditem'	=>$lcIndx,
									'desitem'	=>'',
									'valor'		=>'',
								];
							}
						}
					}
					break;

				// Combobox, Spinner, Textbox, Fecha, Dx, Qx
				case $laData['OP1VAN']=='O':

					// Ginecoobstetricos
					if ($laData['IN1VAN']==36 && $laData['IN2VAN']==2){
						$this->aDocumento['datos'][$lnIndGineco]['tipo']=$laData['OP1VAN'];
						$this->aDocumento['datos'][$lnIndGineco]['valor']=str_replace($this->aSep[0], ', ', $laData['DESVAN']);

					} else {
						$laVals=$this->separaDatos($laData['DESVAN']);
						foreach($laVals as $lcIndx=>$laVal){
							$lnIndxTtl = $this->buscarTitulo($laData['IN1VAN'], $lcIndx);
							if (!$lnIndxTtl===false){
								$laTtl=$this->aTitulos[$lnIndxTtl];
								$lnCod=intval($laTtl['CODIGO']/10);
								if (!empty($laTtl['CURSOR'])){
									switch ($laTtl['CURSOR']){
										case 'CODCUP':
											$this->oCup->cargarDatos($laVal);
											$lcTexto=$laVal.' - '.($this->oCup->cDscrCup ?? '');
											break;

										case 'CODCIE':
											$this->oDx->cargar($laVal);
											$lcTexto=$laVal.' - '.($this->oDx->getTexto() ?? '');
											break;

										default:
											$lcTexto=( $this->aListas[$laTtl['CURSOR']][$laVal] ?? $laVal ).' '.$laTtl['POSTER'];
									}
								} else {
									$lcTexto=$laVal.' '.$laTtl['POSTER'];
								}

								$lbNuevo=true;
								foreach($this->aDocumento['datos'] as $lnKey=>$laDato){
									if($laDato['indice']==$laTtl['ORDEN'] && $laDato['codigo']==$lnCod){ // && $laDato['coditem']==$lcIndx
										$this->aDocumento['datos'][$lnKey]['valor'].=' '.$lcTexto;
										$lbNuevo=false;
										break;
									}
								}
								if ($lbNuevo) {
									$this->aDocumento['datos'][] = [
										'indice'	=>$laTtl['ORDEN'],
										'codigo'	=>$lnCod,
										'tipo'		=>$laData['OP1VAN'],
										'coditem'	=>$lcIndx,
										'desitem'	=>'',
										'valor'		=>$lcTexto,
									];
								}
							}
						}
					}
					break;

				// Editbox
				case $laData['OP1VAN']=='T':
					$laVal=explode(':',$laData['DESVAN']);
					$lnIndxTtl = $this->buscarTitulo($laData['IN1VAN'], $laVal[0]);
					$laText = substr($laData['DESVAN'], strpos($laData['DESVAN'],':')+1);
					if (!$lnIndxTtl===false){
						$laTtl=$this->aTitulos[$lnIndxTtl];
						$lnCod=intval($laTtl['CODIGO']/10);
						$this->aDocumento['datos'][] = [
							'indice'	=>$laTtl['ORDEN'],
							'codigo'	=>$lnCod,
							'tipo'		=>$laData['OP1VAN'],
							'coditem'	=>$laVal[0],
							'desitem'	=>'',
							'valor'		=>$laText,
						];
					}
					break;


				// Casos especiales
				case $laData['OP1VAN']=='E':

					switch (true){

						// Conciliación de medicamentos
						case $laData['IN1VAN']==38 && $laData['IN2VAN']==100:
							$lnCodigo=380000;
							$lcCodItm='CONCMED';
							$lcDscItm='CONCILIACION MEDICAMENTOS';
							$lcTexto=$laData['DESVAN'];
							break;

						// Cirugias Previas
						case $laData['IN1VAN']==40 && $laData['IN2VAN']>100:
							$lnCodigo=401000 + $laData['IN2VAN'] - 100;
							$lcCodItm='QXPREV';
							$lcDscItm='';	//'CIRUGÍAS PREVIAS';
							$loRta = json_decode($laData['DESVAN']);
							$lcTexto = '- ' . $loRta->Cirugia . PHP_EOL
										. '   FECHA: ' . $loRta->Fecha . ' - '
										. '   ANESTESIA: ' . $loRta->DscAnestesia . PHP_EOL
										. '   COMPLICACIONES: ' . str_replace('~', '. ', $loRta->DscComplica)
										. (empty($loRta->Observ) ? '' : PHP_EOL . '   OBSERVACIONES: ' . $loRta->Observ);
							break;

						// Dentición
						case $laData['IN1VAN']==72 && $laData['IN2VAN']==100:
							$laDental=AplicacionFunciones::mapear($this->aDental, 'CODIGO', 'DESCRIPCION');
							$lnCodigo=720000;
							$lcCodItm='DENTIC';
							$lcDscItm='DENTICIÓN';
							$lcTexto='Diente - Observación' . PHP_EOL;
							$laVals=$this->separaDatos($laData['DESVAN']);
							foreach($laVals as $lcIndx=>$lcVal){
								$lcTexto.= '  ' . $lcIndx . '   - ' . $laDental[$lcVal] . PHP_EOL;
							}

							break;

					}
					$this->aDocumento['datos'][] = [
						'indice'	=>$lnCodigo,
						'codigo'	=>intval($lnCodigo/10000),
						'tipo'		=>$laData['OP1VAN'],
						'coditem'	=>$lcCodItm,
						'desitem'	=>$lcDscItm,
						'valor'		=>$lcTexto,
					];
					break;

			}
		}
		$this->insertarTitulos();
	}


	private function prepararInforme($taData)
	{
		$lnAnchoPagina = 90;
		$lcSL = PHP_EOL;

		//$laTr['aCuerpo'][] = ['texto9',	'FECHA DE VALORACION: '.$this->aDocumento['FechaDem'].'   |   LUGAR DE REALIZACION: '.$this->aDocumento['LugarDem']];
		$laTr['cTxtAntesDeCup'] = 'FECHA DE VALORACION: '.$this->aDocumento['FechaDem'].'  |  LUGAR DE REALIZACION: '.$this->aDocumento['LugarDem'];

		$laTr['aCuerpo'][] = ['columnas', 2, ];
		if(!empty($this->aDocumento['CausaExt'])){
			$laTr['aCuerpo'][] = ['texto9', 'CAUSA EXTERNA: '.$this->aDocumento['CausaExt']];
		}

		$laTtls=['','','','',];
		foreach($this->aDocumento['datos'] as $lnIndx=>$laDato){
			for ($lnI=1; $lnI<4; $lnI++) {
				$lcTit='titulo'.$lnI;
				$lcDatTit=trim($laDato[$lcTit]??'');
				if ($laTtls[$lnI]!==$lcDatTit) {
					if (!empty($lcDatTit)){
						$laTr['aCuerpo'][]=[$lcTit, $lcDatTit];
					}
					$laTtls[$lnI]=$lcDatTit;
				}
			}
			$lcTexto = $laDato['desitem'].((empty($laDato['desitem']) || empty($laDato['valor'])) ? '' : ': ').$laDato['valor'];
			if (!empty($lcTexto)){
				$laTr['aCuerpo'][] = ['texto9',	$lcTexto];
			}
		}

		// Firma
		$laTr['aCuerpo'][] = ['firmas', [ [ 'usuario' => $this->aLog['USRVAN'], ], ], ];

		$laTr['aCuerpo'][] = ['columnas', 1, ];

		$this->aReporte = array_merge($this->aReporte, $laTr);
	}


	// Retorna array luego de separar datos de una cadena
	private function separaDatos($tcDatos)
	{
		$laReturn = [];
		$laDatos = explode($this->aSep[0],trim($tcDatos));
		foreach ($laDatos as $laDato) {
			$laPar = explode($this->aSep[1],$laDato);
			$laReturn[$laPar[0]] = $laPar[1];
		}
		return $laReturn;
	}


	private function obtenerListas()
	{
		// Consulta de listas
		$laTab = $this->oDb
			->select('TRIM(CL2TMA) CL2TMA, TRIM(CL3TMA) CL3TMA, TRIM(DE2TMA) DE2TMA')
			->from('TABMAE')
			->where('TIPTMA=\'ANSIA\' AND CL1TMA=\'LISTA\'')
			->orderBy('CL2TMA, OP3TMA')
			->getAll(true);
		if ($this->oDb->numRows()>0) {
			$this->aListas = AplicacionFunciones::mapear($laTab, 'CL3TMA', 'DE2TMA', 'CL2TMA');
			$this->aListas['ACPRNT'] = $this->obtenerParentescos();
		}
	}


	private function obtenerTitulos()
	{
		// Consulta de títulos
		$laTit = $this->oDb
			->select('TRIM(CL2TMA) CODIGO, TRIM(CL3TMA) INDICE, TRIM(DE1TMA) TITULO, TRIM(DE2TMA) TITULOF')
			->select('TRIM(SUBSTR(OP5TMA,1,25)) POSTER, INT(OP4TMA) ORDEN, TRIM(OP2TMA) CURSOR, OP1TMA NIVEL')
			->from('TABMAE')
			->where('TIPTMA=\'ANSIA\' AND CL1TMA=\'TITULO\' AND ESTTMA<>\'1\'')
			->orderBy('OP4TMA')
			->getAll(true);
		if (is_array($laTit)) {
			$this->aTitulos = $laTit;
			foreach($this->aTitulos as $laTitulo){
				if ($laTitulo['NIVEL']>0 && !($laTitulo['INDICE']=='GIOBS' && $laTitulo['ORDEN']=='360019')) {
					$lcTitulo = empty($laTitulo['TITULO'])? $laTitulo['TITULOF']: $laTitulo['TITULO'];
					//$lcTitulo = $laTitulo['TITULO']?:$laTitulo['TITULOF'];
					$lcTitulo1 = $laTitulo['NIVEL']=='1'? $lcTitulo: $lcTitulo1;
					$lcTitulo2 = $laTitulo['NIVEL']=='2'? $lcTitulo: ($laTitulo['NIVEL']<'2'? '': $lcTitulo2);
					$lcTitulo3 = $laTitulo['NIVEL']=='3'? $lcTitulo: ($laTitulo['NIVEL']<'3'? '': $lcTitulo3);
					$this->aTtlPrincipal[$laTitulo['CODIGO']]=[
						'CODIGOP'=>intval($laTitulo['CODIGO']/10),
						'TITULO1'=>$lcTitulo1,
						'TITULO2'=>$lcTitulo2,
						'TITULO3'=>$lcTitulo3,
					];
				}
			}
		}
	}


	private function buscarTitulo($tcCodigo, $tcIndice)
	{
		$lnIndice=false;
		foreach ($this->aTitulos as $lcKey=>$laTitulo){
			if ($tcIndice==$laTitulo['INDICE'] && $tcCodigo==intval($laTitulo['CODIGO']/10)){
				$lnIndice=$lcKey;
				break;
			}
		}
		return $lnIndice;
	}


	private function insertarTitulos()
	{
		// Inserta los títulos
		$laIndxTit=AplicacionFunciones::getColumn($this->aTitulos, 'ORDEN', true);
		foreach($this->aDocumento['datos'] as $lnIndx=>$laData){
			$this->aDocumento['datos'][$lnIndx]['valor']=trim($laData['valor']);
			$lnIndxTtl=array_search($laData['indice'], $laIndxTit);
			$this->aDocumento['datos'][$lnIndx]['desitem']=$lnIndxTtl===false ? '' : $this->aTitulos[$lnIndxTtl]['TITULO'];
			$laTtlPrin=$this->aTtlPrincipal[intval($laData['indice']/1000)]??false;
			if ($laTtlPrin){
				$this->aDocumento['datos'][$lnIndx]['titulo1']=$laTtlPrin['TITULO1'];
				$this->aDocumento['datos'][$lnIndx]['titulo2']=$laTtlPrin['TITULO2'];
				if (in_array($laData['indice'], ['331001','331002','331003','331004'])) {
					$this->aDocumento['datos'][$lnIndx]['desitem']=$laTtlPrin['TITULO3'].': '.$this->aDocumento['datos'][$lnIndx]['desitem'];
					$this->aDocumento['datos'][$lnIndx]['titulo3']='';
				} else {
					$this->aDocumento['datos'][$lnIndx]['titulo3']=$laTtlPrin['TITULO3'];
				}
			}
		}

		// Inserta Títulos vacíos
		$oTabmae=$this->oDb->obtenerTabMae('DE2TMA', 'ANSIA', "CL1TMA='TXTVACIO' AND CL2TMA<>''", 'CL2TMA DESC');
		$lnTextoVacio=trim(AplicacionFunciones::getValue($oTabmae,'DE2TMA',''));
		if (!empty($lnTextoVacio)){
			$laDataTtl=[0=>[],1=>[]];
			foreach($this->aDocumento['datos'] as $lnIndx=>$laTtlPrin){
				$laDataTtl[0][]=intval($laTtlPrin['codigo'] / 10);
				$laDataTtl[1][]=intval($laTtlPrin['codigo']);
			}
			foreach($this->aTtlPrincipal as $lnIndx=>$laTtlPrin){
				if (!empty($laTtlPrin['TITULO1']) && $lnIndx>=300 && $lnIndx<700) {
					if ( (!in_array(intval($laTtlPrin['CODIGOP']/10), $laDataTtl[0]) && empty($laTtlPrin['TITULO2']) && empty($laTtlPrin['TITULO3'])) ||
						(!in_array(intval($laTtlPrin['CODIGOP']), $laDataTtl[1]) && !empty($laTtlPrin['TITULO2']) && empty($laTtlPrin['TITULO3'])) ) {
							$this->aDocumento['datos'][] = [
								'indice'	=>$lnIndx*1000,
								'codigo'	=>$laTtlPrin['CODIGOP'],
								'tipo'		=>'T',
								'coditem'	=>($lnIndx*1000).'',
								'desitem'	=>'',
								'titulo1'	=>$laTtlPrin['TITULO1'],
								'titulo2'	=>$laTtlPrin['TITULO2'],
								'titulo3'	=>$laTtlPrin['TITULO3'],
								'valor'		=>$lnTextoVacio,
							];
					}
				}
			}
		}

		// Ordena resultado
		$laIndices=AplicacionFunciones::getColumn($this->aDocumento['datos'], 'indice', true);
		array_multisort($laIndices, SORT_ASC, $this->aDocumento['datos']);
	}


	private function obtenerDental()
	{
		// Consulta de títulos
		$laDen = $this->oDb
			->select('TRIM(SUBSTR(DE2TMA,1,30)) AS DESCRIPCION, TRIM(SUBSTR(CL3TMA,1,5)) AS CODIGO, OP1TMA AS LETRA')
			->from('TABMAE')
			->where('TIPTMA=\'ANSIA\' AND CL2TMA=\'DENTI\' AND ESTTMA<>\'1\'')
			->orderBy('DE2TMA')
			->getAll(true);

		if (is_array($laDen)) {
			$this->aDental = $laDen;
		}
	}


	// Retorna la descripción de un código en una lista
	private function obtenerDatoLista($tcLista, $tcCodigo)
	{
		$lcReturn = '';
		if (count($this->aListas)>0) {
			$lcReturn = $this->aListas[$tcLista][$tcCodigo] ?? '';

		} else {
			$laTab = $this->oDb
				->select('DE2TMA')
				->from('TABMAE')
				->where("TIPTMA='ANSIA' AND CL1TMA='LISTA' AND CL2TMA='$tcLista' AND CL2TMA='$tcCodigo'")
				->get(true);
			if (is_array($laTab)) {
				$lcReturn = trim($laPar['DE2TMA']);
			}
		}
		return $lcReturn;
	}


	// Retorna parentesco desde la base de datos
	private function obtenerParentesco($tcCod)
	{
		$lcReturn = '';
		$laPar = $this->oDb
			->select('SUBSTR(DE1TMA,1,40) AS PARENT')
			->from('TABMAE')
			->where("TIPTMA='CODPAR' AND CL1TMA='$tcCod' AND ESTTMA<>'1'")
			->get(true);
		if (is_array($laPar)) {
			if (count($laPar)>0) {
				$lcReturn = trim($laPar['PARENT']);
			}
		}
		return $lcReturn;
	}


	// Retorna array con lista de parentescos
	private function obtenerParentescos()
	{
		$laReturn = [];
		$laPar = $this->oDb
			->select('TRIM(SUBSTR(DE1TMA,1,40)) AS PARENT, TRIM(SUBSTR(CL1TMA,1,2)) AS CODIGO')
			->from('TABMAE')
			->where('TIPTMA=\'CODPAR\' AND ESTTMA<>\'1\'')
			->getAll(true);
		if (is_array($laPar)) {
			if (count($laPar)>0) {
				$laReturn = AplicacionFunciones::mapear($laPar, 'CODIGO', 'PARENT');
			}
		}
		return $laReturn;
	}


	// Retorna array con datos vacíos
	private function dataVacio()
	{
		return [
			'indice'	=>0,	// Numero de orden
			'codigo'	=>0,	//Código de página
			'tipo'		=>'',	// tipo de controles
			'coditem'	=>'',	// codigo de ítem
			'desitem'	=>'',	// descripción del ítem
			'titulo1'	=>'',	// titulo principal de grupo
			'titulo2'	=>'',
			'titulo3'	=>'',
			'valor'		=>'',	// valor (si no es check)
		];
	}

}