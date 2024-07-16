<?php
namespace NUCLEO;

class Doc_Adjuntos
{
	protected $oDb;
	protected $aDocumento = [];
	protected $aReporte = [
					'cTitulo' => '',
					'lMostrarEncabezado' => false,
					'lMostrarFechaRealizado' => false,
					'lMostrarViaCama' => false,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => '',
					'cTxtLuegoDeCup' => '',
					'aCuerpo' => [],
					'aFirmas' => [],
					'aNotas' => ['notas'=>false,],
				];
	protected $lTituloVacios=false;
	protected $cTituloVacios='';
	protected $servidorPrincipal='';
	protected $servidorBackup='';
	protected $permittedChars='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';


	public function __construct()
    {
		global $goDb;
		$this->oDb = $goDb;

		$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'HCADJUN', ['CL1TMA'=>'GENERAL', 'CL2TMA'=>'01010101', 'ESTTMA'=>'']);
		$this->servidorPrincipal = trim(AplicacionFunciones::getValue($oTabmae, 'DE2TMA', ''));

		$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'HCADJUN', ['CL1TMA'=>'GENERAL', 'CL2TMA'=>'01010102', 'ESTTMA'=>'']);
		$this->servidorBackup = trim(AplicacionFunciones::getValue($oTabmae, 'DE2TMA', ''));
    }


	/*
	 *	Retornar array con los datos del documento
	 */
	public function retornarDocumento($taData)
	{
		$lcArchivo = $this->fcRutaHcAdjunto($this->servidorPrincipal, $this->servidorBackup, $taData['nConsecCons'], $taData['nConsecEvol'], false);
		if(!empty($lcArchivo)){
			$laTr['aCuerpo'][] = ['pdf', [$lcArchivo] ];
			$this->aReporte = array_merge($this->aReporte, $laTr);
		}
		return $this->aReporte;
	}


	/*
	 *	Consulta los datos del documento desde la BD en el array $aDocumento
	 */
	private function fcRutaHcAdjunto($tcSrvPrincipal='', $tcSrvBackup='', $tcCarpeta='', $tcArchivo='', $tlCopiaLocal=false)
	{
		$lcFile=$tcSrvPrincipal.'/'.$tcCarpeta.$tcArchivo;
		$lcFile=file_exists($lcFile) ? $lcFile : $tcSrvBackup.$tcCarpeta.$tcArchivo;
		$lcFile=str_replace('\\', '/', $lcFile);

		if(file_exists($lcFile)){
			if($tlCopiaLocal){
				$lcCopyFile=substr(str_shuffle($this->permittedChars), 0, 32).'.pdf';
				$lnFile=0;
				try {
					while(file_exists($lcCopyFile)) {
						$lcCopyFile=$lcCopyFile=substr(str_shuffle($this->permittedChars), 0, 32).'.pdf';
					}
					while(!copy($lcFile, $lcCopyFile)){
						$lnFile++;
						if($lnFile==10) break;
					}
					$lcFile=$lnFile==10?'':$lcCopyFile;
				} catch (Exception $e) {
					//echo 'Excepción capturada: ',  $e->getMessage(), "\n";
					$lcFile='';
				}
			}
		} else {
			$lcFile='';
		}

		return $lcFile;
	}

}
