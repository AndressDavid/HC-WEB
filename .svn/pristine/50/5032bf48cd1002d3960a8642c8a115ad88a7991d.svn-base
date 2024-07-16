<?php
namespace NUCLEO;
require_once __DIR__ . '/class.AplicacionFunciones.php';
require_once __DIR__ . '/class.PdfHC.config.php';
require_once __DIR__ . '/../publico/complementos/tcpdf/6.4.4/tcpdf.php';
require_once __DIR__ . '/../publico/complementos/fpdi/2.3.6/autoload.php';
require_once __DIR__ . '/../publico/complementos/snappy/1.2.1.0/vendor/autoload.php';

class Doc_GrabarCups 
{
	protected $oDb;
	protected $aDocumento = [];
	
	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}

	public function obtenerDocumento($taDatosImprimir=[])
	{
		$lcProcedimientos='';
		$lcTextoFinal='--------------------------------------';
		$lnCantidadProcedimientos=0;
		$lcSaltoL=PHP_EOL;
		$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
		$lcFechaHoraActual='FECHA: ' .$ltAhora->format('Y-m-d') . '       HORA: ' .$ltAhora->format('H:i:s');
		$laDatosEnviados=json_decode($taDatosImprimir, true);
		$lcNumeroIngreso=$laDatosEnviados['numeroingreso'];
		$lcIdentificacion=$laDatosEnviados['identificacion'];
		$lcNombrePaciente=$laDatosEnviados['nombrepaciente'];
		$lcHabitacionPaciente=$laDatosEnviados['habitacionpaciente'];
		$lcUsuarioIngresa=$laDatosEnviados['usuarioingresa'];
		$laProcedimientos=$laDatosEnviados['procedimientos'];

		foreach ($laProcedimientos as $laCups){
			$lcProcedimientos.='<br>'.$laCups['CUPS'] .'   '.trim(substr($laCups['DESCRIPCIONCUPS'], 0, 30));
			$lnCantidadProcedimientos++;
		}

		$loPdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$loPdf->SetCreator(PDF_CREATOR);
		$loPdf->SetAuthor('');
		$loPdf->SetTitle('');
		$loPdf->SetSubject('');
		$loPdf->setPrintHeader(false);
		$loPdf->setPrintFooter(false);
		$loPdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$loPdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$loPdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$loPdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$loPdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$loPdf->SetFont('courier', '', 8);
		$loPdf->AddPage();
		$loPdf->setCellPaddings(1, 1, 1, 1);
		$loPdf->setCellMargins(1, 1, 1, 1);
		$loPdf->SetFillColor(255, 255, 255);
		$lcProcedimientos = '<b>'.$lcProcedimientos.'<br>'."&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;TOTAL: &nbsp;".$lnCantidadProcedimientos.'</b>'.'<br>'.'<br>'.$lcTextoFinal;

		$lcTextoOrden = "FUNDACION ABBOD SHAIO".$lcSaltoL."ORDEN PROCEDIMIENTO"
						.$lcSaltoL.$lcFechaHoraActual
						.$lcSaltoL."No. IDENTIFICACION:     " .$lcIdentificacion
						.$lcSaltoL.$lcNombrePaciente
						.$lcSaltoL."INGRESO: " .$lcNumeroIngreso ."        CAMA: ".$lcHabitacionPaciente		
						.$lcSaltoL."USUARIO: " .$lcUsuarioIngresa.$lcSaltoL;	

		$loPdf->MultiCell(120, 5, $lcTextoOrden, 0, 'L', 1, 0, '5', '', true);
		$loPdf->MultiCell(120, 5, $lcProcedimientos, 0, 'L', 0, 0, '5', '30', true, 0, true, true, 0);
		$loPdf->Output('procedimientos.pdf', 'I');
	}	

}