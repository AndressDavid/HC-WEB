<?php
namespace NUCLEO;

require_once ('class.FormulacionParametros.php');
use NUCLEO\FormulacionParametros;

class Doc_Enf_AdminMed
{
	protected $oDb;
	protected $oParam;
	protected $aMedicamentos = [];
	protected $aListaMed = [];
	protected $aReporte = [
		'cTitulo' => 'HOJA DE REGISTRO DE MEDICAMENTOS',
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
		$this->oParam = new FormulacionParametros();
		$this->oParam->obtenerParametrosTodos();
	}

	/*
	 *	Retornar array con los datos del documento
	 */
	public function retornarDocumento($taData)
	{
		$this->consultarDatos($taData);
		$this->prepararInforme();

		return $this->aReporte;
	}


	//	Consulta los datos
	private function consultarDatos($taData)
	{
		$lcSL = PHP_EOL;

		// Consulta de medicamentos
		$oTabmae = $this->oDb->ObtenerTabMae('OP3TMA', 'NOTASENF', ['CL1TMA'=>'HORAS', 'ESTTMA'=>'']);
		$lnHoraInicial = trim(AplicacionFunciones::getValue($oTabmae, 'OP3TMA', ''));
		$lnHoraReporte = intval(str_replace(':','',substr($taData['tFechaHora'],10,16)));
		$lnFechaInicio = intval(str_replace('-','',substr($taData['tFechaHora'],0,10)));
		if($lnHoraReporte<$lnHoraInicial){
			$lnFechaInicio = intval(date('Ymd',strtotime('-1 day' , strtotime($lnFechaInicio))));
		}

		$lnFecHoraInicio = $lnFechaInicio * 1000000 + $lnHoraInicial;
		$lnFechaFinal = intval(date('Ymd',strtotime('+1 day' , strtotime($lnFechaInicio))));
		$lnFecHoraFinal = intval(date('Ymd',strtotime('+1 day' , strtotime($lnFechaInicio)))) * 1000000 + $lnHoraInicial;
		$lnFechaIng = intval(date('Ymd',strtotime($taData['oIngrPaciente']->nIngresoFecha)));
		$loDias = date_diff(date_create($lnFechaFinal), date_create($lnFechaIng));
		$lnDias = $loDias->days==0?1:$loDias->days;

		$this->aReporte['cTitulo'] .= ' No. ' . $lnDias . $lcSL . 'Medicamentos Administrados desde '
								   . AplicacionFunciones::formatFechaHora('fechahora12', $lnFechaInicio.' 070000') . ' hasta '
								   . AplicacionFunciones::formatFechaHora('fechahora12', $lnFechaFinal.' 070000');

		 $this->aMedicamentos = $this->oDb
			->select('A.*, B.DESDES AS MDDADM, IFNULL(TRIM(C.NOMMED)||\' \'||TRIM(C.NNOMED), \'\') AS ENFADMIN')
			->select('M.REGMED, TRIM(M.NNOMED)||\' \'||TRIM(M.NOMMED) MEDICO')
			->from('ENADMMD AS A')
			->leftJoin('INVDES AS B', 'A.MEDADM=B.REFDES', null)
			->leftJoin('RIARGMN AS C', 'A.UD1ADM=C.USUARI', null)
			->leftJoin('RIARGMN AS M', 'A.USFADM=M.USUARI', null)
			->where(['A.INGADM'=>$taData['nIngreso'],])
			->between('A.FEPADM*1000000+A.HDPADM',$lnFecHoraInicio,$lnFecHoraFinal)
			->in('ESTADM',[2,5])
			->orderBy ('A.MEDADM, A.VIAADM, A.FEPADM, A.HDPADM, A.DOSADM')
			->getAll('array');
	}


	//	Prepara array $aReporte con los datos para imprimir
	private function prepararInforme()
	{
		$cVacios = $this->cTituloVacios;
		$laTr['aCuerpo'] = [];
		$lcSL = '<br>'; // PHP_EOL

		// Cuerpo*/
		if (is_array($this->aMedicamentos)){

			if (count($this->aMedicamentos)>0){

				$lcCodMed = '';
				$lcCodVia = $this->aMedicamentos[0]['VIAADM'];
				$laAnchos = [45, 60, 85];
				$laTitulo = [ [ 'w'=>$laAnchos, 'd'=>['FECHA - HORA','REALIZADO POR:','OBSERVACIONES ADMINISTRACIÓN',], 'a'=>'L', ] ];
				$laFilas = [ [ 'w'=>[190], 'd'=>[''], ] ];

				foreach($this->aMedicamentos as $lnKey=>$laMedica) {
					$laMedica = array_map('trim',$laMedica);
					if($lcCodVia!==$laMedica['VIAADM'] && $lcCodMed==$laMedica['MEDADM']){$lcCodMed='';}
					
					if($lcCodMed!==$laMedica['MEDADM']){
						$lcCodMed=$laMedica['MEDADM'];
						$lcDetalleM = $laMedica['MEDADM'] . ' - ' . $laMedica['MDDADM'];

						// Dosis
						$lcUdDosis = $this->oParam->unidadDosis($laMedica['DDOADM']);
						if (!empty($lcUdDosis)){
							$lcDetalleM .= ' Dosis ' . str_replace(',00','',number_format(str_replace('.00','',$laMedica['DOSADM']),2,',','.')) . ' ' . trim($lcUdDosis);
						}

						// Frecuencia
						$lcUdFrec = $this->oParam->Frecuencia($laMedica['DFRADM']);
						if (!empty($lcUdFrec)){
							$lcDetalleM .= ' Cada ' . str_replace(',00','',$laMedica['FREADM']) . ' ' . $lcUdFrec;
						}

						// Vía
						$lcViaAd = $this->oParam->viaAdmin($laMedica['VIAADM']);
						if (!empty($lcViaAd)){
							$lcDetalleM .= " Vía $lcViaAd.";
							$lcCodVia = $laMedica['VIAADM'];
						}

						$lcDetalleM .= ' Dr. ' . $laMedica['MEDICO'];
						$lcDetalleM .= !empty(trim($laMedica['OBMADM']))? $lcSL . 'Observaciones Médico: ' . htmlentities(trim($laMedica['OBMADM']), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'):'';

						// Medicamento
						$laFilas[] = [ 'w'=>[190], 'd'=>[$lcSL.'<b>'.$lcDetalleM.'</b>'], ];
					}

					// Detalle
					$lcEspacio = isset($this->aMedicamentos[$lnKey+1])?($laMedica['MEDADM']!==$this->aMedicamentos[$lnKey+1]['MEDADM']?$lcSL:''):'';
					$laFilas[] = [
						'w'=>$laAnchos,
						'd'=>[
							AplicacionFunciones::formatFechaHora('fechahora12', $laMedica['FEPADM'].' '.$laMedica['HDPADM']),
							trim($laMedica['ENFADMIN']).$lcEspacio,
							htmlentities($laMedica['OBSADM'], ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'),
						],
					];
				}

				$laTr['aCuerpo'][] = ['tablaSL', $laTitulo, $laFilas, ];
			}
		}

		$this->aReporte = array_merge($this->aReporte, $laTr);
	}


	/*
	 *	Consulta datos de administración de medicamentos y retorna array
	 */
	public function retornarDatos($taData)
	{
		$this->consultarDatos($taData);
		return $this->organizarDatos();
	}

	/*
	 *	Organiza la consulta y retorna array
	 */
	private function organizarDatos()
	{
		$laRta = [];
		foreach ($this->aMedicamentos as $laMed) {
			$laMed = array_map('trim',$laMed);
			$lcCod = $laMed['MEDADM'];
			if (!isset($laRta[$lcCod])) {
				$laRta[$lcCod]=[
					'medicamento' => $laMed['MDDADM'],
					'dosis' => str_replace(',00','',number_format(str_replace('.00','',$laMed['DOSADM']),2,',','.')),
					'cdosis' => $laMed['DDOADM'],
					'udosis' => $this->oParam->unidadDosis($laMed['DDOADM']),
					'frec' => str_replace('.00','',$laMed['FREADM']),
					'cfrec' => $laMed['DFRADM'],
					'ufrec' => $this->oParam->Frecuencia($laMed['DFRADM']),
					'via' =>$this->oParam->viaAdmin($laMed['VIAADM']),
					'regmed' => $laMed['REGMED'],
					'medico' => $laMed['MEDICO'],
					'obsmed' => $laMed['OBMADM'],
					'admin' => [],
				];
			}
			$laRta[$lcCod]['admin'][] = [
				'fecha' => AplicacionFunciones::formatFechaHora('fecha', $laMed['FEPADM']),
				'hora' => AplicacionFunciones::formatFechaHora('hora12', $laMed['HDPADM']),
				'admin' => $laMed['ENFADMIN'],
				'obs' => $laMed['OBSADM'],
			];
		}
		return $laRta;
	}


}