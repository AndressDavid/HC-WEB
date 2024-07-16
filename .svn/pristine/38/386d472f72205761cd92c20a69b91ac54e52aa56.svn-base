<?php
namespace NUCLEO;

class Observaciones
{
	protected $oDb;
    protected $cReporte ='';
    protected $aError = [
        'Mensaje' => "",
        'Objeto' => "",
        'Valido' => true,
    ];

	public function __construct()
    {
		global $goDb;
		$this->oDb = $goDb;
    }

	//	Retornar array con los datos del documento
	public function retornarDocumento($tcTipo='', $tnIngreso=0)
	{
       	$this->consultarDatos($tcTipo, $tnIngreso);
		return $this->cReporte;
	}

	//	Consulta los datos del documento desde la BD en el array $aDocumento
	private function consultarDatos($tcTipo='', $tnIngreso=0 )
	{
		// Realiza consulta de obsrevaciones de urgencias
        if($tcTipo=='Urgencias'){
           $laTemp = $this->oDb
                ->from('URGOBSL01')
                ->where(['INGUOB'=>$tnIngreso,
                        ])
                ->orderBy('CONUOB DESC, CLIUOB')
                ->getAll('array');

            if (is_array($laTemp)){
                if (count($laTemp)>0){
                    $this->organizarDatos($laTemp);
                }
            }
        }
	}

	// Prepara array $aReporte con los datos para imprimir
	private function organizarDatos($taDatos=[])
	{
        $lcSL = "\n"; //PHP_EOL;
        $lnRegistro = $taDatos[0]['CONUOB'];
        $lcDescrip = '';
		foreach($taDatos as $laReg) {
            $lcDescrip .= ($lnRegistro!==$laReg['CONUOB']?$lcSL:'').($lnRegistro!==$laReg['CONUOB']?$lcSL:'') . $laReg['OBSUOB'];
            $lnRegistro=$laReg['CONUOB'];
		}
   		$this->cReporte = $lcDescrip;
	}

    public function verificarObs($taDatos=[])
	{
        if(empty($taDatos['Ingreso'])){
            $this->aError = [
				'Mensaje'=>'Error en el número de ingreso. No puede estar vacio, Revise por favor!',
				'Objeto'=>'edtNuevaObserva',
				'Valido'=>false,
			];
            return  $this->aError;
        }else{
            $laIngreso = $this->oDb
				->select('TIDING, NIDING, FEIING')
				->from('RIAING')
				->where('NIGING', '=', $taDatos['Ingreso'])
				->get('array'); 
            if(!is_array($laIngreso)){
                $this->aError = [
                    'Mensaje'=>'Ingreso NO existe, Revise por favor!',
                    'Objeto'=>'edtNuevaObserva',
                    'Valido'=>false,
                ];
            }
        }

        if(empty($taDatos['Observaciones'])){
            $this->aError = [
				'Mensaje'=>'El dato observaciones no puede estar vacio, Revise por favor!',
				'Objeto'=>'edtNuevaObserva',
				'Valido'=>false,
			];
            return  $this->aError;
        }

        if(strlen(($taDatos['Observaciones']))< 10 ){
            $this->aError = [
				'Mensaje'=>'la longitud de las observaciones no puede ser menor a 10 digitos. Revise por favor!',
				'Objeto'=>'edtNuevaObserva',
				'Valido'=>false,
			];
        }
       return  $this->aError;
    }

    public function GuardarObs($taDatos=[])
	{
        $laChar = AplicacionFunciones::mb_str_split(trim($taDatos['Observaciones']),220);
		if(is_array($laChar)==true){
			if(count($laChar)>0){
                
                //CONSULTA DE CONSECUTIVO DE URGENCIAS
                $laConsecutivo = $this->oDb->max('CONUOB', 'MAXIMO')->from('URGOBSL01')->where('INGUOB', '=', $taDatos['Ingreso'])->get('array');
                if(is_array($laConsecutivo)){
                    if(count($laConsecutivo)>0){
                        $lnConsec = $laConsecutivo['MAXIMO']; settype($lnConsec,'integer');
                    }
                }
                unset($laConsecutivo);
                $lnConsec = $lnConsec+1;

                // DATOS AUDITORIA
                $ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
		        $lcFecCre = $ltAhora->format("Ymd");
		        $lcHorCre = $ltAhora->format("His");
		        $lcUsuCre = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getUsuario():'');
		        $lcPrgCre = 'OBSURGWEB';

                // GUARDA CABECERA
                $lnLinea = 1;
                $lcTabla = 'URGOBSL01';
                $lcDescrip = $lnConsec . ' - ' . AplicacionFunciones::formatFechaHora('fechahora12', $lcFecCre.' '. $lcHorCre);
                $laData=[
                    'INGUOB' => $taDatos['Ingreso'],
                    'CONUOB' => $lnConsec,
                    'CLIUOB' => $lnLinea,
                    'OBSUOB' => $lcDescrip,
                    'USRUOB' => $lcUsuCre,
                    'PGMUOB' => $lcPrgCre,
                    'FECUOB' => $lcFecCre,
                    'HORUOB' => $lcHorCre,
                ];
                $llResultado = $this->oDb->tabla($lcTabla)->insertar($laData);

                $lnLinea = 99;
				foreach($laChar as $laDato){
                    $lnLinea++;
                    $laData=[
                        'INGUOB' => $taDatos['Ingreso'],
                        'CONUOB' => $lnConsec,
                        'CLIUOB' => $lnLinea,
                        'OBSUOB' => $laDato,
                        'USRUOB' => $lcUsuCre,
                        'PGMUOB' => $lcPrgCre,
                        'FECUOB' => $lcFecCre,
                        'HORUOB' => $lcHorCre,
                    ];

                    $llResultado = $this->oDb->tabla($lcTabla)->insertar($laData);

				}
			}
		}
        $this->aError = [
            'Mensaje'=>"Se ha guardado las observaciones",
            'Objeto'=>'edtNuevaObserva',
            'Valido'=>true,
        ];

        return  $this->aError;

    }
}
