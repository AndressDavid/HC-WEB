<?php
namespace NUCLEO;

class Doc_Enf_BalanceLiq
{
	protected $oDb;
	protected $aLiquidos = [];
	protected $aBalance = [];
	protected $aEliminados = [];
	protected $aTurno = [];
	protected $aTotalTurno = [];
	protected $aTotalBal = [];
	
	protected $aReporte = [
					'cTitulo' => 'BALANCE DE LIQUIDOS - HOJA',
					'lMostrarEncabezado' => true,
					'lMostrarFechaRealizado' => true,
					'lMostrarViaCama' => true,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => '',
					'cTxtLuegoDeCup' => '',
					'aCuerpo' => [],
					'aFirmas' => [],
					'aNotas' => ['notas'=>false,],
				];

	protected $lTituloVacios=false;
	protected $cTituloVacios='';

	public function __construct()
    {
		global $goDb;
		$this->oDb = $goDb;
	}

	// Retornar array con los datos del documento
	public function retornarDocumento($taData)
	{
		$this->consultarDatos($taData);
		$this->prepararInforme($taData);

		return $this->aReporte;
	}

	//	Consulta los datos
	private function consultarDatos($taData){

		$lcSL = PHP_EOL;
		$lcSeccion = $taData['cSecHab'] ;
		$lcSeccion = substr($taData['cSecHab'],0,2) ;
		$oTabmae = $this->oDb->ObtenerTabMae('DE1TMA', 'NOTASENF', ['CL1TMA'=>$lcSeccion, 'ESTTMA'=>' ']);
		$lcTemp = trim(AplicacionFunciones::getValue($oTabmae, 'DE1TMA', ''));

		// Consulta de Liquidos
		if (empty($lcTemp)){
			$oTabmae = $this->oDb->ObtenerTabMae('OP3TMA', 'NOTASENF', ['CL1TMA'=>'HORAS', 'ESTTMA'=>'']);
			$lnHoraInicial = trim(AplicacionFunciones::getValue($oTabmae, 'OP3TMA', ''));
		}else{
			$oTabmae = $this->oDb->ObtenerTabMae('OP4TMA', 'NOTASENF', ['CL1TMA'=>'HORAS', 'ESTTMA'=>'']);
			$lnHoraInicial = intval(trim(AplicacionFunciones::getValue($oTabmae, 'OP4TMA', '')));
		}
	
		$lnFechaInicio = intval(str_replace('-','',substr($taData['tFechaHora'],0,10)));
		$lnHoraReporte = intval(str_replace(':','',substr($taData['tFechaHora'],10,16)));
		$lnFechaInicio = ($lnHoraReporte<$lnHoraInicial ? intval(date('Ymd',strtotime('-1 day' , strtotime($lnFechaInicio)))) : $lnFechaInicio) ;
		$lnFecHoraInicio = $lnFechaInicio * 1000000 + $lnHoraInicial;
		$lnFechaFinal = intval(date('Ymd',strtotime('+1 day' , strtotime($lnFechaInicio))));
		$lnFecHoraFinal = intval(date('Ymd',strtotime('+1 day' , strtotime($lnFechaInicio)))) * 1000000 + $lnHoraInicial-1;
		$lnFechaIng = intval(date('Ymd',strtotime($taData['oIngrPaciente']->nIngresoFecha)));
		$loDias = date_diff(date_create($lnFechaFinal), date_create($lnFechaIng));
		$lnDias = $loDias->days;

	   $this->aReporte['cTitulo'] .= ' No. ' . $lnDias . $lcSL . 'Balance desde '
								   . AplicacionFunciones::formatFechaHora('fechahora12', $lnFechaInicio.' '.$lnHoraInicial) . ' hasta '
								   . AplicacionFunciones::formatFechaHora('fechahora12', $lnFechaFinal.' '.$lnHoraInicial);

		$this->aLiquidos = $this->oDb
			->select('TRIM(A.LSEBAQ) AS TIPO, TRIM(A.LIQBAQ) AS DESCRIP, TRIM(A.VIABAQ) AS VIA, A.CANBAQ AS CANT, A.FDIBAQ AS FECHA, A.HDIBAQ AS HORA,  
					  IFNULL(TRIM(B.OP5TMA),\'\') AS VIAT, IFNULL(TRIM(C.NOMMED)||\' \'||TRIM(C.NNOMED), \'\') AS ENFADMIN, SUBSTR(A.OBSBAQ,0,42) AS OBSERVA, 
					  A.USRBAQ AS USUARIO')
			->from('ENBALQ AS A')
			->leftJoin('TABMAE AS B', 'TRIM(A.VIABAQ)=TRIM(B.DE1TMA) AND TRIM(A.LIQBAQ)=TRIM(B.DE2TMA) AND TRIM(A.LSEBAQ)=TRIM(B.OP1TMA)', null)
			->leftJoin('RIARGMN AS C', 'A.USRBAQ=C.USUARI', null)
			->where(['A.INGBAQ'=>$taData['nIngreso'],])
			->between('A.FDIBAQ*1000000+A.HDIBAQ',$lnFecHoraInicio,$lnFecHoraFinal)
			->orderBy ('A.FDIBAQ, SUBSTR(DIGITS(A.HDIBAQ),1,2), A.LSEBAQ, B.OP2TMA, B.OP4TMA, A.VIABAQ, A.CONBAQ, A.CNLBAQ')
			->getAll('array');

		$this->aEliminados = $this->oDb
			->select('TRIM(OP5TMA) VIA, TRIM(DE2TMA) AS LIQUIDO')
			->from('TABMAE')
			->where(['TIPTMA'=>'LIQUIDO',
					 'OP1TMA'=>'E',
					 'ESTTMA'=>'',
					])
			->getAll('array');		
			
			
		$laTurnos = $this->oDb
			->select('TRIM(DE1TMA) DIA, DE2TMA, OP2TMA, OP3TMA, OP7TMA')
			->from('TABMAE')
			->where(['TIPTMA'=>'TURNOSEN',
					 'ESTTMA'=>'',
					])
			->getAll('array');

		foreach($laTurnos as $laTurno){

			$lcCharReg = '.';
			$laWordsReg = explode($lcCharReg, $laTurno['DIA']);
			if(count($laWordsReg)>0){

				foreach($laWordsReg as $laReg){

					$lnReg = count($this->aTurno);
					$this->aTurno[$lnReg]['Dia'] = $laReg;
					$this->aTurno[$lnReg]['Nombre'] = trim($laTurno['DE2TMA']);
					$this->aTurno[$lnReg]['DiaSig'] = $laTurno['OP2TMA'];
					$this->aTurno[$lnReg]['HoraIni'] = $laTurno['OP3TMA'];
					$this->aTurno[$lnReg]['HoraFin'] = $laTurno['OP7TMA'];

				}

			}

		}

		$this->aTotalTurno = [
			'TURNO 1' =>['Admin'=>0,'Elimi'=>0,'TotBa'=>0,'IrriA'=>0,'IrriE'=>0,'TotIr'=>0,'CapdA'=>0,'CapdE'=>0,'TotCa'=>0,'Usuario'=>''],
			'TURNO 2' =>['Admin'=>0,'Elimi'=>0,'TotBa'=>0,'IrriA'=>0,'IrriE'=>0,'TotIr'=>0,'CapdA'=>0,'CapdE'=>0,'TotCa'=>0,'Usuario'=>''],
			'TURNO 3' =>['Admin'=>0,'Elimi'=>0,'TotBa'=>0,'IrriA'=>0,'IrriE'=>0,'TotIr'=>0,'CapdA'=>0,'CapdE'=>0,'TotCa'=>0,'Usuario'=>''],
			'TURNO 4' =>['Admin'=>0,'Elimi'=>0,'TotBa'=>0,'IrriA'=>0,'IrriE'=>0,'TotIr'=>0,'CapdA'=>0,'CapdE'=>0,'TotCa'=>0,'Usuario'=>''],
		];

	}

	//	Prepara array $aReporte con los datos para imprimir
	private function prepararInforme($taData)
	{
		$cVacios = $this->cTituloVacios;
		$laTr['aCuerpo'] = [];
		$lcSL = PHP_EOL;
		$laAnchos = [30, 23, 27, 78, 16, 16];
		$laAnchos1 = [190];
		$laAnchos2 = [40, 17, 6, 41, 17, 6, 40, 17, 6];
		$laTr['aCuerpo'][]= ['saltol', 3];
		
		// Cuerpo*/
		if (is_array($this->aLiquidos)){

			if (count($this->aLiquidos)>0){
				
				$this->fnOrganizarDatos();
			
				$lcTurno = 'X';

				foreach($this->aBalance as $laBalance){

					if($lcTurno!==$laBalance['Turno']){

						if($lcTurno!=='X'){

							$lnNumTabla = count($laTr['aCuerpo']);
							$laTr['aCuerpo'][$lnNumTabla] = ['tablaSL',
								[ [ 'w'=>$laAnchos1, 'd'=>['Total de líquidos TURNO'], 'a'=>'C', ] ],
								[],
							];
							
							$laTr['aCuerpo'][$lnNumTabla][2][] = [
							'w'=>$laAnchos2,
							'd'=>['Administrados: ', $this->aTotalTurno[$lcTurno]['Admin'], '',
								  (trim($this->aTotalTurno[$lcTurno]['IrriA'])=='0,00' ? '' : 'Irrigación Entrada: '), 
								  (trim($this->aTotalTurno[$lcTurno]['IrriA'])=='0,00' ? '' : $this->aTotalTurno[$lcTurno]['IrriA']),'',
								  (trim($this->aTotalTurno[$lcTurno]['CapdA'])=='0,00' ? '' : 'C.A.P.D. Entrada: '),
								  (trim($this->aTotalTurno[$lcTurno]['CapdA'])=='0,00' ? '' : $this->aTotalTurno[$lcTurno]['CapdA']),''								  
								],
							'a'=>['L','R','L','L','R','L','L','R']
							];
							
							$laTr['aCuerpo'][$lnNumTabla][2][] = [
							'w'=>$laAnchos2,
							'd'=>['L. Eliminados: ', $this->aTotalTurno[$lcTurno]['Elimi'], '',
								  (trim($this->aTotalTurno[$lcTurno]['IrriE'])=='0,00' ? '' : 'Irrigación Salida :  '), 
								  (trim($this->aTotalTurno[$lcTurno]['IrriE'])=='0,00' ? '' : $this->aTotalTurno[$lcTurno]['IrriE']),'',
								  (trim($this->aTotalTurno[$lcTurno]['CapdE'])=='0,00' ? '' : 'C.A.P.D. Salida : '),
								  (trim($this->aTotalTurno[$lcTurno]['CapdE'])=='0,00' ? '' : $this->aTotalTurno[$lcTurno]['CapdE']),''
								],
							'a'=>['L','R','L','L','R','L','L','R']
							];
							
							$laTr['aCuerpo'][$lnNumTabla][2][] = [
							'w'=>$laAnchos2,
							'd'=>['', '---------', '',
								  '', (trim($this->aTotalTurno[$lcTurno]['TotIr'])== '0,00'? '' : '---------'), '',
								  '', (trim($this->aTotalTurno[$lcTurno]['TotCa'])== '0,00'? '' : '---------'), ''
								],
							'a'=>'L'
							];
							
							$laTr['aCuerpo'][$lnNumTabla][2][] = [
							'w'=>$laAnchos2,
							'd'=>['Total Balance: ', $this->aTotalTurno[$lcTurno]['TotBa'], '',
								  (trim($this->aTotalTurno[$lcTurno]['TotIr'])=='0,00' ? '' : 'Irrigación Balance:  '),
								  (trim($this->aTotalTurno[$lcTurno]['TotIr'])=='0,00' ? '' : $this->aTotalTurno[$lcTurno]['TotIr']),'',
								  (trim($this->aTotalTurno[$lcTurno]['TotCa'])=='0,00' ? '' : 'C.A.P.D. Balance : '),
								  (trim($this->aTotalTurno[$lcTurno]['TotCa'])=='0,00' ? '' : $this->aTotalTurno[$lcTurno]['TotCa']),''
							
								],
							'a'=>['L','R','L','L','R','L','L','R']
							];
							
							/* if(!empty($this->aTotalTurno[$lcTurno]['Usuario'])){
								
								$laUsuario = $this->oDb
									->select('TRIM(NNOMED)||\' \'||TRIM(NOMMED) NOMBRE')
									->tabla('RIARGMN')
									->where(['USUARI'=>$this->aTotalTurno[$lcTurno]['Usuario']])
									->get("array");
								
								if(is_array($laUsuario)==true){
								
									$laTr['aCuerpo'][$lnNumTabla][2][] = [
									'w'=>$laAnchos1,
									'd'=>['Enfermero/a : ' . $laUsuario['NOMBRE']],
									'a'=>['L']
									];
								
									
								}
							} */
											
						}

						$lcTurno=$laBalance['Turno'] ;

						$lnNumTabla = count($laTr['aCuerpo']);
						$laTr['aCuerpo'][$lnNumTabla] = ['tabla',
							[ [ 'w'=>$laAnchos, 'd'=>['FECHA - HORA','TIPO','VÍA','DESCRIPCION','ENTRADA','SALIDA'], 'a'=>'C', ] ],
							[],
						];

					}

					$laTr['aCuerpo'][$lnNumTabla][2][] = [
					'w'=>$laAnchos,
					'd'=>[
						$laBalance['FechaHora'],
						$laBalance['Tipo'],
						$laBalance['Via'],
						$laBalance['Descrip'],
						$laBalance['Entrada'],
						$laBalance['Salida']
						],
					'a'=>['L','L','L','L','R','R',]
					];

				}

				// Totales ultimo turno
				$lnNumTabla = count($laTr['aCuerpo']);
						$laTr['aCuerpo'][$lnNumTabla] = ['tablaSL',
							[ [ 'w'=>$laAnchos1, 'd'=>['Total de líquidos TURNO'], 'a'=>'L', ] ],
							[],
						];
				$laTr['aCuerpo'][$lnNumTabla][2][] = [
				'w'=>$laAnchos2,
				'd'=>['Administrados: ', $this->aTotalTurno[$lcTurno]['Admin'], '',
					(trim($this->aTotalTurno[$lcTurno]['IrriA'])=='0,00' ? '' : 'Irrigación Entrada: '),
					(trim($this->aTotalTurno[$lcTurno]['IrriA'])=='0,00' ? '' : $this->aTotalTurno[$lcTurno]['IrriA']),'',
					(trim($this->aTotalTurno[$lcTurno]['CapdA'])=='0,00' ? '' : 'C.A.P.D. Entrada: '),
					(trim($this->aTotalTurno[$lcTurno]['CapdA'])=='0,00' ? '' : $this->aTotalTurno[$lcTurno]['CapdA']),''
					],
				'a'=>['L','R','L','L','R','L','L','R']
				];
				
				$laTr['aCuerpo'][$lnNumTabla][2][] = [
				'w'=>$laAnchos2,
				'd'=>['L. Eliminados: ', $this->aTotalTurno[$lcTurno]['Elimi'], '',
					  (trim($this->aTotalTurno[$lcTurno]['IrriE'])=='0,00' ? '' : 'Irrigación Salida :  '), 
					  (trim($this->aTotalTurno[$lcTurno]['IrriE'])=='0,00' ? '' : $this->aTotalTurno[$lcTurno]['IrriE']),'',
					  (trim($this->aTotalTurno[$lcTurno]['CapdE'])=='0,00' ? '' : 'C.A.P.D. Salida : '),
					  (trim($this->aTotalTurno[$lcTurno]['CapdE'])=='0,00' ? '' : $this->aTotalTurno[$lcTurno]['CapdE']),''
					],
				'a'=>['L','R','L','L','R','L','L','R']
				];

				$laTr['aCuerpo'][$lnNumTabla][2][] = [
				'w'=>$laAnchos2,
				'd'=>['', '---------', '',
					  '', (trim($this->aTotalTurno[$lcTurno]['TotIr'])== '0,00'? '' : '---------'), '',
					  '', (trim($this->aTotalTurno[$lcTurno]['TotCa'])== '0,00'? '' : '---------'), ''
					],
				'a'=>'L'
				];
				
				$laTr['aCuerpo'][$lnNumTabla][2][] = [
				'w'=>$laAnchos2,
				'd'=>['Total Balance: ', $this->aTotalTurno[$lcTurno]['TotBa'], '',
					  (trim($this->aTotalTurno[$lcTurno]['TotIr'])=='0,00' ? '' : 'Irrigación Balance:  '),
					  (trim($this->aTotalTurno[$lcTurno]['TotIr'])=='0,00' ? '' : $this->aTotalTurno[$lcTurno]['TotIr']),'',
					  (trim($this->aTotalTurno[$lcTurno]['TotCa'])=='0,00' ? '' : 'C.A.P.D. Balance : '),
					  (trim($this->aTotalTurno[$lcTurno]['TotCa'])=='0,00' ? '' : $this->aTotalTurno[$lcTurno]['TotCa']),''
				
					],
				'a'=>['L','R','L','L','R','L','L','R']
				];
				
				/* if(!empty($this->aTotalTurno[$lcTurno]['Usuario'])){
								
					$laUsuario = $this->oDb
						->select('TRIM(NNOMED)||\' \'||TRIM(NOMMED) NOMBRE')
						->tabla('RIARGMN')
						->where(['USUARI'=>$this->aTotalTurno[$lcTurno]['Usuario']])
						->get("array");
					
					if(is_array($laUsuario)==true){
					
						$laTr['aCuerpo'][$lnNumTabla][2][] = [
						'w'=>$laAnchos1,
						'd'=>['Enfermero/a : ' . trim($laUsuario['NOMBRE'])],
						'a'=>['L']
						];
					
						
					}
				} */
				
				// Totales
				$lnNumTabla = count($laTr['aCuerpo']);
						$laTr['aCuerpo'][$lnNumTabla] = ['tablaSL',
							[ [ 'w'=>$laAnchos1, 'd'=>['Total de líquidos 24 Horas'], 'a'=>'L', ] ],
							[],
						];

				$laTr['aCuerpo'][$lnNumTabla][2][] = [
				'w'=>$laAnchos2,
				'd'=>['Administrados: ', $this->aTotalBal['Admin'], '',
					  (trim($this->aTotalBal['IrriA'])=='0,00' ? '' : 'Irrigación Entrada: '),
					  (trim($this->aTotalBal['IrriA'])=='0,00' ? '' : $this->aTotalBal['IrriA']),'',
					  (trim($this->aTotalBal['CapdA'])=='0,00' ? '' : 'C.A.P.D. Entrada: '),
					  (trim($this->aTotalBal['CapdA'])=='0,00' ? '' : $this->aTotalBal['CapdA']),''
					],
				'a'=>['L','R','L','L','R','L','L','R']
				];
				
				$laTr['aCuerpo'][$lnNumTabla][2][] = [
				'w'=>$laAnchos2,
				'd'=>['L. Eliminados: ', $this->aTotalBal['Elimi'], '',
					  (trim($this->aTotalBal['IrriE'])=='0,00' ? '' : 'Irrigación Salida :  '),
					  (trim($this->aTotalBal['IrriE'])=='0,00' ? '' : $this->aTotalBal['IrriE']),'',
					  (trim($this->aTotalBal['CapdE'])=='0,00' ? '' : 'C.A.P.D. Salida : '),
					  (trim($this->aTotalBal['CapdE'])=='0,00' ? '' : $this->aTotalBal['CapdE']),''
					],
				'a'=>['L','R','L','L','R','L','L','R']
				];
				
				$laTr['aCuerpo'][$lnNumTabla][2][] = [
				'w'=>$laAnchos2,
				'd'=>['', '---------', '',
					  '', (trim($this->aTotalBal['TotIr'])== '0,00'? '' : '---------'), '',
					  '', (trim($this->aTotalBal['TotCa'])== '0,00'? '' : '---------'), ''
					],
				'a'=>'L'
				];

				$laTr['aCuerpo'][$lnNumTabla][2][] = [
				'w'=>$laAnchos2,
				'd'=>['Total Balance: ', $this->aTotalBal['TotBa'], '',
					  (trim($this->aTotalBal['TotIr'])=='0,00' ? '' : 'Irrigación Balance:  '),
					  (trim($this->aTotalBal['TotIr'])=='0,00' ? '' : $this->aTotalBal['TotIr']),'',
					  (trim($this->aTotalBal['TotCa'])=='0,00' ? '' : 'C.A.P.D. Balance : '),
					  (trim($this->aTotalBal['TotCa'])=='0,00' ? '' : $this->aTotalBal['TotCa']),''
					],
				'a'=>['L','R','L','L','R','L','L','R']
				];

			}

		}

		$this->aReporte = array_merge($this->aReporte, $laTr);
	}

	function fnOrganizarDatos() {

		$lcSL = '<br>'; // PHP_EOL
		$lcSLa = $lcTurno = $lcObserva = '';
		$lcFechaHora = $lcFechorReg = $lcTipoReg = $lcViaReg = $lcDescripReg = $lcCantAdmReg = $lcCanEliReg = $lcUsuario = '';
		$lnCantAdmRegTot = $lnCanEliRegTot = $lnCantAdmIrriTot = $lnCanEliIrriTot = $lnCantAdmCAPDTot = $lnCanEliCAPDTot = 0;
		$lnCantAdmRegTur = $lnCanEliRegTur = $lnCantAdmIrriTur = $lnCanEliIrriTur = $lnCantAdmCAPDTur = $lnCanEliCAPDTur = 0;

		$this->fnActualizaTurno();
		$lcFechorReg = AplicacionFunciones::formatFechaHora('fechahora', $this->aLiquidos[0]['FECHA'].' '.$this->aLiquidos[0]['HORA']);
		$lcFechorReg = trim(substr($lcFechorReg,0,13));

		$lcTurno = $this->aLiquidos[0]['TURNO'];
		
		foreach($this->aLiquidos as $laLiquido) {

			$lcObserva = '' ;
			
			if (($laLiquido['TIPO']=='E') && empty($laLiquido['VIA']) && empty($laLiquido['VIAT'])) {
				
				$laDato = '' ;
				$laDato = $laLiquido['DESCRIP'] ;
				$key = array_search($laDato, array_column($this->aEliminados, 'LIQUIDO'));
				
				if (!empty($key)){
					$laLiquido['VIA'] = $this->aEliminados[$key]['VIA']  ;
				}
				
			}
			
			$lcFechaHora = AplicacionFunciones::formatFechaHora('fechahora', $laLiquido['FECHA'].' '.$laLiquido['HORA']);
			$lcFechaHora = trim(substr($lcFechaHora,0,13));
			$lcUsuario = $laLiquido['USUARIO'];

			if($lcFechorReg!==$lcFechaHora){

				$lnReg = count($this->aBalance) + 1;
				$this->aBalance[$lnReg]['FechaHora'] = $lcFechorReg . ':00';
				$this->aBalance[$lnReg]['Tipo'] = $lcTipoReg;
				$this->aBalance[$lnReg]['Via'] = $lcViaReg;
				$this->aBalance[$lnReg]['Descrip'] = $lcDescripReg;
				$this->aBalance[$lnReg]['Entrada'] = $lcCantAdmReg;
				$this->aBalance[$lnReg]['Salida'] = $lcCanEliReg;
				$this->aBalance[$lnReg]['Turno'] = $lcTurno;
				$this->aBalance[$lnReg]['Usuario'] = $lcUsuario;
				
				$lcFechaHora = $lcFechorReg = $lcTipoReg = $lcViaReg = $lcDescripReg = '';
				$lcCantAdmReg = $lcCanEliReg = $lcSLa = '';
				$lcFechorReg=$lcFechaHora;

			}

			if($lcTurno!==$laLiquido['TURNO']){

				$this->aTotalTurno[$lcTurno]['Admin'] = number_format($lnCantAdmRegTur,2,',','.');
				$this->aTotalTurno[$lcTurno]['Elimi'] = number_format($lnCanEliRegTur,2,',','.');
				$this->aTotalTurno[$lcTurno]['TotBa'] = number_format($lnCantAdmRegTur - $lnCanEliRegTur,2,',','.') ;

				$this->aTotalTurno[$lcTurno]['IrriA'] = number_format($lnCantAdmIrriTur,2,',','.');
				$this->aTotalTurno[$lcTurno]['IrriE'] = number_format($lnCanEliIrriTur,2,',','.');
				$this->aTotalTurno[$lcTurno]['TotIr'] = number_format($lnCantAdmIrriTur - $lnCanEliIrriTur,2,',','.');

				$this->aTotalTurno[$lcTurno]['CapdA'] = number_format($lnCantAdmCAPDTur,2,',','.');
				$this->aTotalTurno[$lcTurno]['CapdE'] = number_format($lnCanEliCAPDTur,2,',','.');
				$this->aTotalTurno[$lcTurno]['TotCa'] = number_format($lnCantAdmCAPDTur - $lnCanEliCAPDTur,2,',','.');
				
				$this->aTotalTurno[$lcTurno]['Usuario'] = $lcUsuario;

				$lcTurno=$laLiquido['TURNO'];
				$lnCantAdmRegTur = $lnCanEliRegTur = $lnCantAdmIrriTur = $lnCanEliIrriTur = $lnCantAdmCAPDTur = $lnCanEliCAPDTur = 0;

			}

			$lcFechorReg = AplicacionFunciones::formatFechaHora('fechahora', $laLiquido['FECHA'].' '.$laLiquido['HORA']);
			$lcFechorReg = trim(substr($lcFechorReg,0,13));

			if(empty(trim($laLiquido['VIAT'])) && trim($laLiquido['DESCRIP'])=='Orina'){

				$laLiquido['VIAT']='ORINA';

			}

			if(empty(trim($laLiquido['VIAT'])) && trim(substr($laLiquido['DESCRIP'],0,7)=='C.A.P.D')){

				$laLiquido['VIAT']='C A P D';

			}

			switch ($laLiquido['TIPO']) {

				case 'A' :
					$lcTipoReg .= $lcSLa . 'ADMINISTRADO';
					$lcCantAdmReg .= $lcSLa . number_format($laLiquido['CANT'],2,',','.');
					$lcCanEliReg .= $lcSL;

					if(trim($laLiquido['VIAT'])=='C A P D'){
						$lnCantAdmCAPDTot += floatval($laLiquido['CANT']);
						$lnCantAdmCAPDTur += floatval($laLiquido['CANT']);
					}
					else{
						$lnCantAdmRegTot += floatval($laLiquido['CANT']);
						$lnCantAdmRegTur += floatval($laLiquido['CANT']);
					}
					break;
				case 'E' :
					$lcTipoReg .= $lcSLa . 'ELIMINADO';
					$lcCanEliReg .= $lcSLa . number_format($laLiquido['CANT'],2,',','.');
					if(trim($laLiquido['VIAT']=='C A P D')){
						$lnCanEliCAPDTot += floatval($laLiquido['CANT']);
						$lnCanEliCAPDTur += floatval($laLiquido['CANT']);
					}
					else{
						$lnCanEliRegTot += floatval($laLiquido['CANT']);
						$lnCanEliRegTur += floatval($laLiquido['CANT']);
					}
					break;
				case 'I' :
					$lcTipoReg .= $lcSLa . 'IRRIGACION';
					$lcCantAdmReg .= $lcSLa . number_format($laLiquido['CANT'],2,',','.');
					$lnCantAdmIrriTot += floatval($laLiquido['CANT']);
					$lnCantAdmIrriTur += floatval($laLiquido['CANT']);
					break;
				case 'O' :
					$lcTipoReg .= $lcSLa . 'IRRIGACION';
					$lcCanEliReg .= $lcSLa . number_format($laLiquido['CANT'],2,',','.');
					$lnCanEliIrriTot += floatval($laLiquido['CANT']);
					$lnCanEliIrriTur += floatval($laLiquido['CANT']);
					break;

			}

			$lcViaReg .= $lcSLa . (empty(trim($laLiquido['VIAT'])) ? $laLiquido['VIA'] : $laLiquido['VIAT']);
			if(mb_strtoupper(trim($laLiquido['DESCRIP'])) == trim($laLiquido['VIA']) || trim($laLiquido['VIA'])=='INTRAVENOSA' || trim($laLiquido['VIA'])=='ILIOSTOMIA') {
				$lcObserva = trim($laLiquido['OBSERVA']);
			}

			$lcDescripReg  .= $lcSLa . (!empty(trim($lcObserva)) ? trim($lcObserva): trim($laLiquido['DESCRIP'])) ;
			$lcSLa = $lcSL;
			
		}

		$lnReg = count($this->aBalance) + 1;
		$this->aBalance[$lnReg]['FechaHora'] = $lcFechorReg . ':00';
		$this->aBalance[$lnReg]['Tipo'] = $lcTipoReg;
		$this->aBalance[$lnReg]['Via'] = $lcViaReg;
		$this->aBalance[$lnReg]['Descrip'] = $lcDescripReg;
		$this->aBalance[$lnReg]['Entrada'] = $lcCantAdmReg;
		$this->aBalance[$lnReg]['Salida'] = $lcCanEliReg;
		$this->aBalance[$lnReg]['Turno'] = $lcTurno;

		// Totales Turno
		$this->aTotalTurno[$lcTurno]['Admin'] = number_format($lnCantAdmRegTur,2,',','.');
		$this->aTotalTurno[$lcTurno]['Elimi'] = number_format($lnCanEliRegTur,2,',','.');
		$this->aTotalTurno[$lcTurno]['TotBa'] = number_format($lnCantAdmRegTur - $lnCanEliRegTur,2,',','.');
		$this->aTotalTurno[$lcTurno]['IrriA'] = number_format($lnCantAdmIrriTur,2,',','.');
		$this->aTotalTurno[$lcTurno]['IrriE'] = number_format($lnCanEliIrriTur,2,',','.');
		$this->aTotalTurno[$lcTurno]['TotIr'] = number_format($lnCantAdmIrriTur - $lnCanEliIrriTur,2,',','.');
		$this->aTotalTurno[$lcTurno]['CapdA'] = number_format($lnCantAdmCAPDTur,2,',','.');
		$this->aTotalTurno[$lcTurno]['CapdE'] = number_format($lnCanEliCAPDTur,2,',','.');
		$this->aTotalTurno[$lcTurno]['TotCa'] = number_format($lnCantAdmCAPDTur - $lnCanEliCAPDTur,2,',','.');
		$this->aTotalTurno[$lcTurno]['Usuario'] = $lcUsuario;
		
		// Totales Balance
		$this->aTotalBal['Admin'] = number_format($lnCantAdmRegTot,2,',','.');
		$this->aTotalBal['Elimi'] = number_format($lnCanEliRegTot,2,',','.');
		$this->aTotalBal['TotBa'] = number_format($lnCantAdmRegTot - $lnCanEliRegTot,2,',','.');
		$this->aTotalBal['IrriA'] = number_format($lnCantAdmIrriTot,2,',','.');
		$this->aTotalBal['IrriE'] = number_format($lnCanEliIrriTot,2,',','.');
		$this->aTotalBal['TotIr'] = number_format($lnCantAdmIrriTot - $lnCanEliIrriTot,2,',','.');
		$this->aTotalBal['CapdA'] = number_format($lnCantAdmCAPDTot,2,',','.');
		$this->aTotalBal['CapdE'] = number_format($lnCanEliCAPDTot,2,',','.');
		$this->aTotalBal['TotCa'] = number_format($lnCantAdmCAPDTot - $lnCanEliCAPDTot,2,',','.');

	}

	function fnActualizaTurno(){
			
		foreach($this->aLiquidos as $lnKey=>$laLiquidoAct) {

			$lnDia = intval(date('N',strtotime($laLiquidoAct['FECHA']))) + 1;
			$lnHora = intval($laLiquidoAct['HORA']);
			$this->aLiquidos[$lnKey]['TURNO'] = 'REVISAR';
			
			foreach($this->aTurno as $laTurno) {

				if($lnHora<80000 || $lnHora>=190000 ){
					
					$this->aLiquidos[$lnKey]['TURNO'] = 'TURNO 3';
					break ;
					
				}
				
				if ($laTurno['Dia']==$lnDia && $lnHora>=$laTurno['HoraIni'] && $lnHora<$laTurno['HoraFin']){

					$this->aLiquidos[$lnKey]['TURNO'] = $laTurno['Nombre'];
					break;

				}

			}

		}

	}
}