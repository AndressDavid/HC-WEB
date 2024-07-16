<?php
namespace NUCLEO;

class Doc_NoInvasiva
{
	protected $oDb;
	protected $aReporte = [
				'cTitulo' => 'CARDIOLOGÍA NO INVASIVA',
				'lMostrarEncabezado' => true,
				'lMostrarFechaRealizado' => true,
				'lMostrarViaCama' => true,
				'cTxtAntesDeCup' => '',
				'cTituloCup' => 'Estudio',
				'cTxtLuegoDeCup' => '',
				'aCuerpo' => [],
				'aNotas' => ['notas'=>true,],
			];
	protected $cReporte = '';
	protected $cReporteOriginal = '';
	protected $cTipoProc = '';
	protected $cCodEsp = '';
	protected $aVar = [];
	protected $aData = [];
	protected $aRiaOrd = [];
	protected $aDiag = [];
	protected $aCalidad = [];
	protected $aEcoTransEsofag = [];
	protected $aPruebaEsfuerzo = [];
	protected $aProtocolo = [];
	protected $aApreciacion = [];
	protected $aEcoStress = [];
	protected $aConclusiones = [];
	protected $aValNormales = [];
	protected $aEquipos = [];
	protected $aValEquipos = [];
	protected $aDatFarm = [];
	protected $nEdadA = 0;
	protected $cImagen = __DIR__ . '/../publico/imagenes/librohc/cni/cni021i.jpg';


	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}


	/*
	 *	Retornar array con los datos del documento
	 */
	public function retornarDocumento($taData)
	{
		$this->cReporte=$this->cReporteOriginal=$taData['cTipoProgr'];
		$this->consultarDatos($taData);
		$this->organizarDatos($taData);
		$this->prepararInforme($taData);

		return $this->aReporte;
	}


	/*
	 *	Consulta los datos del documento desde la BD
	 */
	private function consultarDatos($taData)
	{
		// Procedimiento
		$this->oDb
			->from('ECOS')
			->where([
				'INGECO'=>$taData['nIngreso'],
				'CONECO'=>$taData['nConsecCita'],
				'PROECO'=>$taData['cCUP'],
			]);
		switch($this->cReporte) {
			case 'CNI014': case 'CNI020':
				$this->oDb->where('INDECO','<','10');
				break;
			case 'CNI021':
				$this->oDb->between('INDECO',11,20);
				break;
			case 'CNI022':
				$this->oDb->in('PGMECO',['CNI020','CNI022']);
				break;
		}
/*
		$this->oDb->orderBy('INDECO, SUBECO, LINECO');
		$this->aSql = [ 'cSQL' => $this->oDb->getStatement(), 'aBvl' => $this->oDb->getBindValue(), ];
		$this->aData = $this->oDb->getAll('array');
*/
		$this->aData = $this->oDb
			->orderBy('INDECO, SUBECO, LINECO')
			->getAll('array');
		// Tipo de Procedimiento
		$this->aRiaOrd = $this->oDb
			->select('RMEORD, ESCORD, CIPORD, ESTORD, RATORD, CD2ORD, CODORD, IFNULL(NOMMED || \' \' || NNOMED, \'\') AS MEDORD')
			->from('RIAORD15 O')
			->leftJoin('RIARGMN R','O.RMEORD=R.REGMED')
			->where([
				'NINORD'=>$taData['nIngreso'],
				'CCIORD'=>$taData['nConsecCita'],
				'COAORD'=>$taData['cCUP'],
			])
			->get('array');
		$this->cTipoProc = trim($this->aRiaOrd['CD2ORD']);
		$this->cCodEsp = trim($this->aRiaOrd['CODORD']);

		if (in_array($taData['cTipoProgr'], ['CNI001', 'CNI013', 'CNI014', 'CNI020'])) {
			$this->fnDiagnosticos($taData);
		}

		$this->fnValEquipos();

		//  ARREGLOS ESPECIFICOS
		switch($this->cReporte) {

			case 'CNI013':
				$this->fnEcoTransEsofag();
				break;

			case 'CNI014': case 'CNI022':
				$this->fnPruebaEsfuerzo();
				$this->fnEcoStress();
				$this->fnApreciacion();
				$this->fnProtocolo();
				break;

			case 'CNI020':
				$this->fnEcoStress();
				break;

			case 'CNI021':
				$this->fnCalidad();
				break;

			case 'CNI001':

		}

		$this->fnConclusiones();
	}


	/*
	 *	Organizar los datos recibidos
	 */
	private function organizarDatos($taData)
	{
		$this->aVar['lcCaption']='';
		$this->nEdadA = intval($taData['oIngrPaciente']->aEdad['y']);

		switch($this->cReporte) {

			case 'CNI001': case 'CNI013': case 'CNI020':
				$this->crearVar(['etgenu','etgpes','etgtal','etgsco'],0);
				$this->crearVar(['etgpnu','etgmso','etgdms','etgdcl'],'');

				if ($this->cReporte=='CNI013') {
					$this->aVar['lcCaption'] = $this->aEcoTransEsofag[$this->cTipoProc]??'';
					if (!empty($this->aVar['lcCaption']))
						$this->aVar['lcCaption'] = 'Eco Transesofagico '.$this->aVar['lcCaption'];
				}
				if ($this->cReporte=='CNI020') {
					$this->aVar['lcCaption'] = $this->aEcoStress[$this->cTipoProc]??'';
					if (!empty($this->aVar['lcCaption']))
						$this->aVar['lcCaption'] = 'Eco de Stress Con '.$this->aVar['lcCaption'];
				}

				foreach($this->aData as $ecos) {
					if ($ecos['INDECO']==1) {
						switch(true) {
							case $ecos['LINECO']==1:
								$this->aVar['etgenu'] = floatval($this->cReporte=='CNI020'? mb_substr($ecos['DSCECO'],30,10): mb_substr($ecos['DSCECO'],30,9));
								$this->aVar['etgpnu'] = trim(mb_substr($ecos['DSCECO'],50,9));
								$this->aVar['etgpes'] = floatval(substr($ecos['DSCECO'],70,9));
								$this->aVar['etgtal'] = floatval(substr($ecos['DSCECO'],90,9));
								break;
							case $ecos['LINECO']==5:
								$this->aVar['etgmso'] = trim(mb_substr($ecos['DSCECO'],30,90));
								break;
							case $ecos['LINECO']==6:
								$this->aVar['etgdms'] = trim(mb_substr($ecos['DSCECO'],30,90));
								break;
							case $ecos['LINECO']>10:
								$this->aVar['etgdcl'] .= mb_substr($ecos['DSCECO'],0,120);
								break;
						}
					}
				}
				$this->aVar['etgdcl'] = trim($this->aVar['etgdcl']);

				// CALCULA SUPERFICIE CORPORAL
				$this->aVar['etgsco'] = $this->fnCalcSuperficieCorporal($this->aVar['etgpes'], $this->aVar['etgtal']);

				// BUSCA CONSECUTIVO EN RIACON
				/* if ( empty($this->aVar['etgenu']) && ( $this->cReporte!=="CNI001" || ( $this->cReporte=="CNI001" && $this->cCodEsp=='124' ) ) ) {   }  */

				// BUSCA EL MEDICO QUE ORDENO EL PROCEDIMIENTO Y RESULTADO
				if ( empty($this->aVar['etgmso']) && count($this->aRiaOrd)>0 ) {
					$this->aVar['etgmso']=trim($this->aRiaOrd['MEDORD']);
					$this->aVar['etgdms']=trim($this->aRiaOrd['ESCORD']);
				}

				break;

			case 'CNI014':
				$this->crearVar(['pegenu','pegpnu','pegpes','pegtal','pegsco'],0);
				$this->crearVar(['pegmso','pegdms','pegdcl'],'');

				$this->aVar['lcCaption'] = $this->aPruebaEsfuerzo[$this->cTipoProc]??'';
				if (!empty($this->aVar['lcCaption']))
					$this->aVar['lcCaption'] = 'Prueba de Esfuerzo '.$this->aVar['lcCaption'];

				foreach($this->aData as $ecos) {
					if ($ecos['INDECO']==1) {
						switch(true) {
							case $ecos['LINECO']==1:
								$this->aVar['pegenu'] = floatval(substr($ecos['DSCECO'],30,9));
								$this->aVar['pegpnu'] = floatval(substr($ecos['DSCECO'],50,9));
								$this->aVar['pegpes'] = floatval(substr($ecos['DSCECO'],70,9));
								$this->aVar['pegtal'] = floatval(substr($ecos['DSCECO'],90,9));
								break;
							case $ecos['LINECO']==5:
								$this->aVar['pegmso'] = trim(mb_substr($ecos['DSCECO'],30,90));
								break;
							case $ecos['LINECO']==6:
								$this->aVar['pegdms'] = trim(mb_substr($ecos['DSCECO'],30,90));
								break;
							case $ecos['LINECO']>10:
								$this->aVar['pegdcl'] .= mb_substr($ecos['DSCECO'],0,120);
								break;
						}
					}
				}
				$this->aVar['pegdcl'] = trim($this->aVar['pegdcl']);

				// CALCULA SUPERFICIE CORPORAL
				$this->aVar['pegsco'] = $this->fnCalcSuperficieCorporal($this->aVar['pegpes'], $this->aVar['pegtal']);

				// BUSCA CONSECUTIVO EN RIACON
				/* if ( empty($this->aVar['pegenu']) ) {   }  */

				// BUSCA EL MEDICO QUE ORDENO EL PROCEDIMIENTO Y RESULTADO
				if ( empty($this->aVar['pegmso']) && count($this->aRiaOrd)>0 ) {
					$this->aVar['pegmso']=trim($this->aRiaOrd['MEDORD']);
					$this->aVar['pegdms']=trim($this->aRiaOrd['ESCORD']);
				}

				break;
		}

		/*****  MEDIDAS, EQUIPOS, CONCLUSIONES  *****/
		$this->crearVar(['etctex','etccon','etcmre','etcmr2','etcmr3'], '');

		switch($this->cReporte) {

			case 'CNI001': case 'CNI020':
				$this->crearVar(['etmsdn','etmequ','etmtr1','etmtr2','etmtr3'], '');
				$this->crearVar([
						'etmai1','etmai2','etmra1','etmra2','etmva1','etmva2','etvis1','etvis2','etvid1','etvid2',
						'etmsi1','etmsi2','etmpp1','etmpp2','etvds1','etvds2','etmfe1','etmfe2','etmca1','etmca2',
						'etmaiz','etmrao','etmvao','etmvis','etmvid','etmsin','etmppo','etmvds','etmfey','etmcas',
					], 0);

				foreach($this->aData as $ecos) {
					if ($ecos['INDECO']==2) {
						switch(true) {
							case $ecos['LINECO']==1:
								$this->aVar['etmequ'] = trim($this->cReporte=='CNI020' ? mb_substr($ecos['DSCECO'],30,10) : mb_substr($ecos['DSCECO'],30,9));
								$this->aVar['etmtr1'] = trim(mb_substr($ecos['DSCECO'],50,9));
								$this->aVar['etmtr2'] = trim(mb_substr($ecos['DSCECO'],70,9));
								$this->aVar['etmtr3'] = trim(mb_substr($ecos['DSCECO'],90,9));
								break;
							case $ecos['LINECO']==2:
								$this->aVar['etmsdn'] = trim(mb_substr($ecos['DSCECO'],25,9));
								$this->aVar['etmaiz'] = floatval(trim(substr($ecos['DSCECO'],  40, 7) . '.' . substr($ecos['DSCECO'],  47, 2)));
								$this->aVar['etmrao'] = floatval(trim(substr($ecos['DSCECO'],  55, 7) . '.' . substr($ecos['DSCECO'],  62, 2)));
								$this->aVar['etmvao'] = floatval(trim(substr($ecos['DSCECO'],  70, 7) . '.' . substr($ecos['DSCECO'],  77, 2)));
								$this->aVar['etmvis'] = floatval(trim(substr($ecos['DSCECO'],  85, 7) . '.' . substr($ecos['DSCECO'],  92, 2)));
								$this->aVar['etmvid'] = floatval(trim(substr($ecos['DSCECO'], 100, 7) . '.' . substr($ecos['DSCECO'], 107, 2)));
								break;
							case $ecos['LINECO']==3:
								$this->aVar['etmsdn'] = trim(mb_substr($ecos['DSCECO'],25,9));
								$this->aVar['etmsin'] = floatval(trim(substr($ecos['DSCECO'],  40, 7) . '.' . substr($ecos['DSCECO'],  47, 2)));
								$this->aVar['etmppo'] = floatval(trim(substr($ecos['DSCECO'],  55, 7) . '.' . substr($ecos['DSCECO'],  62, 2)));
								$this->aVar['etmvds'] = floatval(trim(substr($ecos['DSCECO'],  70, 7) . '.' . substr($ecos['DSCECO'],  77, 2)));
								$this->aVar['etmfey'] = floatval(trim(substr($ecos['DSCECO'],  85, 7) . '.' . substr($ecos['DSCECO'],  92, 2)));
								$this->aVar['etmcas'] = floatval(trim(substr($ecos['DSCECO'], 100, 7) . '.' . substr($ecos['DSCECO'], 107, 2)));
								break;
						}
					}

					// Conclusiones
					if ($ecos['INDECO']==3) {
						switch(true) {
							case $ecos['LINECO']==5:
								$this->aVar['etcmre'] = trim(mb_substr($ecos['DSCECO'], 26, 13, 'UTF-8'));
								$this->aVar['etcmr2'] = trim(mb_substr($ecos['DSCECO'], 46, 13, 'UTF-8'));
								$this->aVar['etcmr3'] = trim(mb_substr($ecos['DSCECO'], 66, 13, 'UTF-8'));
								break;
							case $ecos['LINECO']==10:
								$this->aVar['etctex'] = trim(mb_substr($ecos['DSCECO'], 27, 2, 'UTF-8'));
								break;
							case $ecos['LINECO']>10:
								$this->aVar['etccon'] .= mb_substr($ecos['DSCECO'], 0, 120, 'UTF-8');
								break;
						}
					}
				}
				$this->aVar['etccon']=trim($this->aVar['etccon']);
				$this->fnValNormales();

				if ($this->cReporte=='CNI001' && empty($this->aVar['etmsdn'])) {
					foreach($this->aValNormales as $laNormal) {
						if ($this->nEdadA >= $laNormal['EDNSDN'] && $this->nEdadA >= $laNormal['EDXSDN']) {
							$this->aVar['etmsdn']=$laNormal['CODSDN'];
							break;
						}
					}
				}

				foreach($this->aValNormales as $laNormal) {
					if ($this->aVar['etmsdn']==$laNormal['CODSDN']) {
						$this->aVar['etmai1'] = $laNormal['AI1SDN'];
						$this->aVar['etmai2'] = $laNormal['AI2SDN'];
						$this->aVar['etmra1'] = $laNormal['RA1SDN'];
						$this->aVar['etmra2'] = $laNormal['RA2SDN'];
						$this->aVar['etmva1'] = $laNormal['VA1SDN'];
						$this->aVar['etmva2'] = $laNormal['VA2SDN'];
						$this->aVar['etvis1'] = $laNormal['IS1SDN'];
						$this->aVar['etvis2'] = $laNormal['IS2SDN'];
						$this->aVar['etvid1'] = $laNormal['ID1SDN'];
						$this->aVar['etvid2'] = $laNormal['ID2SDN'];
						$this->aVar['etmsi1'] = $laNormal['SI1SDN'];
						$this->aVar['etmsi2'] = $laNormal['SI2SDN'];
						$this->aVar['etmpp1'] = $laNormal['PP1SDN'];
						$this->aVar['etmpp2'] = $laNormal['PP2SDN'];
						$this->aVar['etvds1'] = $laNormal['DD1SDN'];
						$this->aVar['etvds2'] = $laNormal['DD2SDN'];
						$this->aVar['etmfe1'] = $laNormal['FE1SDN'];
						$this->aVar['etmfe2'] = $laNormal['FE2SDN'];
						$this->aVar['etmca1'] = $laNormal['CA1SDN'];
						$this->aVar['etmca2'] = $laNormal['CA2SDN'];
						break;
					}
				}
				break;


			case 'CNI013':
				$this->crearVar(['eteequ','etetr1','etetr2','etetr3','etetex','etecon','etemre'], '');

				foreach($this->aData as $ecos) {
					if ($ecos['INDECO']==2) {
						switch(true) {
							case $ecos['LINECO']==1:
								$this->aVar['eteequ'] = trim(mb_substr($ecos['DSCECO'], 30, 9, 'UTF-8'));
								$this->aVar['etetr1'] = trim(mb_substr($ecos['DSCECO'], 50, 9, 'UTF-8'));
								$this->aVar['etetr2'] = trim(mb_substr($ecos['DSCECO'], 70, 9, 'UTF-8'));
								$this->aVar['etetr3'] = trim(mb_substr($ecos['DSCECO'], 90, 9, 'UTF-8'));
								break;
							case $ecos['LINECO']==5:
								$this->aVar['etemre'] = trim(mb_substr($ecos['DSCECO'], 26, 13, 'UTF-8'));
								break;
							case $ecos['LINECO']==10:
								$this->aVar['etetex'] = trim(mb_substr($ecos['DSCECO'], 27, 2, 'UTF-8'));
								break;
							case $ecos['LINECO']>10:
								$this->aVar['etecon'] .= mb_substr($ecos['DSCECO'], 0, 120, 'UTF-8');
								break;
						}
					}
				}
				$this->aVar['etecon']=trim($this->aVar['etecon']);

				break;


			case 'CNI014':
				$this->crearVar([
					'peaac1','peaac2','peaac3','peaac4','peaaca','peatra','pearep','peaeje','peapej','peepro',
					'pefrps','pef05s','pef10s','pef20s','pef30s','pef40s','pef50s','pefrcs','pepcfu','peppsu',
					'peccon','pecobs','pecmre','pecmrz', ], '');
				$this->crearVar([
					'peemer','peeme1','peeme2','peeme3','peeme4','peeme5','peeme6','peeme7','peeme8','peever',
					'peeve1','peeve2','peeve3','peeve4','peeve5','peeve6','peeve7','peeve8','peeanr','peean1',
					'peean2','peean3','peean4','peean5','peean6','peean7','peean8','peefcr','peefc1','peefc2',
					'peefc3','peefc4','peefc5','peefc6','peefc7','peefc8','peetsr','peets1','peets2','peets3',
					'peets4','peets5','peets6','peets7','peets8','peetdr','peetd1','peetd2','peetd3','peetd4',
					'peetd5','peetd6','peetd7','peetd8','pefrp1','pefrp2','pefrp3','pef051','pef052','pef053',
					'pef101','pef102','pef103','pef201','pef202','pef203','pef301','pef302','pef303','pef401',
					'pef402','pef403','pef501','pef502','pef503','pefrc1','pefrc2','pefrc3','pepfci','pepfc3',
					'pepfc5','pepfc8','peptsi','pepts3','pepts5','pepts8','peptdi','peptd3','peptd5','peptd8',
					'pepfe1','pepfe2','pepfcr','peppor','pepmet','pepdpr', ], 0);

				foreach($this->aData as $ecos) {

					// DATOS
					if ($ecos['INDECO']==2 && $ecos['LINECO']>0) {
						switch($ecos['SUBECO']) {
							case 0:
								if ($ecos['LINECO']==2) {
									$this->aVar['peaac1'] = trim(mb_substr($ecos['DSCECO'], 26, 3, 'UTF-8'));
									$this->aVar['peaac2'] = trim(mb_substr($ecos['DSCECO'], 36, 3, 'UTF-8'));
									$this->aVar['peaac3'] = trim(mb_substr($ecos['DSCECO'], 46, 3, 'UTF-8'));
									$this->aVar['peaac4'] = trim(mb_substr($ecos['DSCECO'], 56, 3, 'UTF-8'));
								}
								break;
							case 1:
								$this->aVar['peaaca'] .= mb_substr($ecos['DSCECO'], 0, 120, 'UTF-8');
								break;
							case 2:
								$this->aVar['peatra'] .= mb_substr($ecos['DSCECO'], 0, 120, 'UTF-8');
								break;
							case 3:
								$this->aVar['pearep'] .= mb_substr($ecos['DSCECO'], 0, 120, 'UTF-8');
								break;
							case 4:
								$this->aVar['peaeje'] .= mb_substr($ecos['DSCECO'], 0, 120, 'UTF-8');
								break;
							case 5:
								$this->aVar['peapej'] .= mb_substr($ecos['DSCECO'], 0, 120, 'UTF-8');
								break;
						}
					}

					// EJERCICIO
					if ($ecos['INDECO']==3 && $ecos['SUBECO']==0) {
						if(in_array($ecos['LINECO'],[1,2,3])){
							$laVar=['','peefc','peets','peetd'];
								$this->aVar['peepro'] = trim(mb_substr($ecos['DSCECO'],  25, 4, 'UTF-8'));
								$lnPos=35;
								for($lnNum=0;$lnNum<9;$lnNum++){
									$lcSuf=$lnNum==0 ? 'r' : $lnNum;
									$this->aVar[$laVar[$ecos['LINECO']].$lcSuf] = floatval(substr($ecos['DSCECO'], $lnPos, 4));
									$lnPos+=10;
								}
						}
					}

					// FARMACO
					if ($ecos['INDECO']==3 && $ecos['SUBECO']==1) {
						switch($ecos['LINECO']) {
							case 1:
								$this->aVar['pefrp1'] = floatval(substr($ecos['DSCECO'], 25,  4));
								$this->aVar['pefrp2'] = floatval(substr($ecos['DSCECO'], 35,  4));
								$this->aVar['pefrp3'] = floatval(substr($ecos['DSCECO'], 45,  4));
								$this->aVar['pefrps'] = trim(mb_substr($ecos['DSCECO'], 55, 50, 'UTF-8'));
								break;
							case 2:
								$this->aVar['pef051'] = floatval(substr($ecos['DSCECO'], 25,  4));
								$this->aVar['pef052'] = floatval(substr($ecos['DSCECO'], 35,  4));
								$this->aVar['pef053'] = floatval(substr($ecos['DSCECO'], 45,  4));
								$this->aVar['pef05s'] = trim(mb_substr($ecos['DSCECO'], 55, 50, 'UTF-8'));
								break;
							case 3:
								$this->aVar['pef101'] = floatval(substr($ecos['DSCECO'], 25,  4));
								$this->aVar['pef102'] = floatval(substr($ecos['DSCECO'], 35,  4));
								$this->aVar['pef103'] = floatval(substr($ecos['DSCECO'], 45,  4));
								$this->aVar['pef10s'] = trim(mb_substr($ecos['DSCECO'], 55, 50, 'UTF-8'));
								break;
							case 4:
								$this->aVar['pef201'] = floatval(substr($ecos['DSCECO'], 25,  4));
								$this->aVar['pef202'] = floatval(substr($ecos['DSCECO'], 35,  4));
								$this->aVar['pef203'] = floatval(substr($ecos['DSCECO'], 45,  4));
								$this->aVar['pef20s'] = trim(mb_substr($ecos['DSCECO'], 55, 50, 'UTF-8'));
								break;
							case 5:
								$this->aVar['pef301'] = floatval(substr($ecos['DSCECO'], 25,  4));
								$this->aVar['pef302'] = floatval(substr($ecos['DSCECO'], 35,  4));
								$this->aVar['pef303'] = floatval(substr($ecos['DSCECO'], 45,  4));
								$this->aVar['pef30s'] = trim(mb_substr($ecos['DSCECO'], 55, 50, 'UTF-8'));
								break;
							case 6:
								$this->aVar['pef401'] = floatval(substr($ecos['DSCECO'], 25,  4));
								$this->aVar['pef402'] = floatval(substr($ecos['DSCECO'], 35,  4));
								$this->aVar['pef403'] = floatval(substr($ecos['DSCECO'], 45,  4));
								$this->aVar['pef40s'] = trim(mb_substr($ecos['DSCECO'], 55, 50, 'UTF-8'));
								break;
							case 7:
								$this->aVar['pef501'] = floatval(substr($ecos['DSCECO'], 25,  4));
								$this->aVar['pef502'] = floatval(substr($ecos['DSCECO'], 35,  4));
								$this->aVar['pef503'] = floatval(substr($ecos['DSCECO'], 45,  4));
								$this->aVar['pef50s'] = trim(mb_substr($ecos['DSCECO'], 55, 50, 'UTF-8'));
								break;
							case 8:
								$this->aVar['pefrc1'] = floatval(substr($ecos['DSCECO'], 25,  4));
								$this->aVar['pefrc2'] = floatval(substr($ecos['DSCECO'], 35,  4));
								$this->aVar['pefrc3'] = floatval(substr($ecos['DSCECO'], 45,  4));
								$this->aVar['pefrcs'] = trim(mb_substr($ecos['DSCECO'], 55, 50, 'UTF-8'));
								break;
						}
					}

					// POST-EJERCICIO
					if ($ecos['INDECO']==4) {
						switch(true) {
							case $ecos['LINECO']==1:
								$this->aVar['pepfci'] = floatval(substr($ecos['DSCECO'], 30, 7).'.'.substr($ecos['DSCECO'], 37, 2));
								$this->aVar['pepfc3'] = floatval(substr($ecos['DSCECO'], 50, 7).'.'.substr($ecos['DSCECO'], 57, 2));
								$this->aVar['pepfc5'] = floatval(substr($ecos['DSCECO'], 70, 7).'.'.substr($ecos['DSCECO'], 77, 2));
								$this->aVar['pepfc8'] = floatval(substr($ecos['DSCECO'], 90, 7).'.'.substr($ecos['DSCECO'], 97, 2));
								break;
							case $ecos['LINECO']==2:
								$this->aVar['peptsi'] = floatval(substr($ecos['DSCECO'], 30, 7).'.'.substr($ecos['DSCECO'], 37, 2));
								$this->aVar['pepts3'] = floatval(substr($ecos['DSCECO'], 50, 7).'.'.substr($ecos['DSCECO'], 57, 2));
								$this->aVar['pepts5'] = floatval(substr($ecos['DSCECO'], 70, 7).'.'.substr($ecos['DSCECO'], 77, 2));
								$this->aVar['pepts8'] = floatval(substr($ecos['DSCECO'], 90, 7).'.'.substr($ecos['DSCECO'], 97, 2));
								break;
							case $ecos['LINECO']==3:
								$this->aVar['peptdi'] = floatval(substr($ecos['DSCECO'], 30, 7).'.'.substr($ecos['DSCECO'], 37, 2));
								$this->aVar['peptd3'] = floatval(substr($ecos['DSCECO'], 50, 7).'.'.substr($ecos['DSCECO'], 57, 2));
								$this->aVar['peptd5'] = floatval(substr($ecos['DSCECO'], 70, 7).'.'.substr($ecos['DSCECO'], 77, 2));
								$this->aVar['peptd8'] = floatval(substr($ecos['DSCECO'], 90, 7).'.'.substr($ecos['DSCECO'], 97, 2));
								break;
							case $ecos['LINECO']==4:
								$this->aVar['pepfe1'] = floatval(substr($ecos['DSCECO'], 30, 7).'.'.substr($ecos['DSCECO'], 37, 2));
								$this->aVar['pepfe2'] = floatval(substr($ecos['DSCECO'], 50, 7).'.'.substr($ecos['DSCECO'], 57, 2));
								$this->aVar['pepfcr'] = floatval(substr($ecos['DSCECO'], 70, 7).'.'.substr($ecos['DSCECO'], 77, 2));
								break;
							case $ecos['LINECO']==5:
								$this->aVar['peppor'] = floatval(substr($ecos['DSCECO'], 30, 7).'.'.substr($ecos['DSCECO'], 37, 2));
								$this->aVar['pepmet'] = floatval(substr($ecos['DSCECO'], 50, 7).'.'.substr($ecos['DSCECO'], 57, 2));
								$this->aVar['pepdpr'] = floatval(substr($ecos['DSCECO'], 70, 7).'.'.substr($ecos['DSCECO'], 77, 2));
								$this->aVar['pepcfu'] = trim(mb_substr($ecos['DSCECO'], 90,30, 'UTF-8'));
								break;
							case $ecos['LINECO']>10:
								$this->aVar['peppsu'] .= mb_substr($ecos['DSCECO'], 0, 120, 'UTF-8');
								break;
						}
					}

					// CONCLUSIONES
					if ($ecos['INDECO']==5) {
						switch(true) {
							case $ecos['LINECO']==1:
								$this->aVar['peccon'] = trim(mb_substr($ecos['DSCECO'], 20, 2, 'UTF-8'));
								break;
							case $ecos['LINECO']==5:
								$this->aVar['pecmre'] = trim(mb_substr($ecos['DSCECO'], 26, 13, 'UTF-8'));
								break;
							case $ecos['LINECO']>10:
								$this->aVar['pecobs'] .= mb_substr($ecos['DSCECO'], 0, 120, 'UTF-8');
								break;
						}
					}
				}
				$this->aVar['peaaca']=trim($this->aVar['peaaca']);
				$this->aVar['peatra']=trim($this->aVar['peatra']);
				$this->aVar['pearep']=trim($this->aVar['pearep']);
				$this->aVar['peaeje']=trim($this->aVar['peaeje']);
				$this->aVar['peapej']=trim($this->aVar['peapej']);
				$this->aVar['peppsu'] = trim($this->aVar['peppsu']);
				$this->aVar['pecobs'] = trim($this->aVar['pecobs']);

				if (empty($this->aVar['peaaca']) && empty($this->aVar['peatra']) && empty($this->aVar['pearep']) && empty($this->aVar['peaeje']) && empty($this->aVar['peapej'])) {
					$this->aVar['peaaca'] = 'No Refiere';
					$this->aVar['peatra'] = 'Ninguno';
					$this->aVar['pearep'] = 'Ritmo sinusal, eje normal';
					$this->aVar['peaeje'] = 'Cambios no significativos del segmento ST, sin arritmias';
					$this->aVar['peapej'] = 'Cambios no significativos del segmento ST, sin arritmias';
				}

				if (!empty($this->aVar['peepro'])) {
					foreach($this->aProtocolo as $laProt) {
						if ($this->aVar['peepro']==$laProt['CODPRO']) {
							$this->aVar['peemer'] = $laProt['MREPRO'];
							$this->aVar['peeme1'] = $laProt['M01PRO'];
							$this->aVar['peeme2'] = $laProt['M02PRO'];
							$this->aVar['peeme3'] = $laProt['M03PRO'];
							$this->aVar['peeme4'] = $laProt['M04PRO'];
							$this->aVar['peeme5'] = $laProt['M05PRO'];
							$this->aVar['peeme6'] = $laProt['M06PRO'];
							$this->aVar['peeme7'] = $laProt['M07PRO'];
							$this->aVar['peeme8'] = $laProt['M08PRO'];
							$this->aVar['peever'] = $laProt['VREPRO'];
							$this->aVar['peeve1'] = $laProt['V01PRO'];
							$this->aVar['peeve2'] = $laProt['V02PRO'];
							$this->aVar['peeve3'] = $laProt['V03PRO'];
							$this->aVar['peeve4'] = $laProt['V04PRO'];
							$this->aVar['peeve5'] = $laProt['V05PRO'];
							$this->aVar['peeve6'] = $laProt['V06PRO'];
							$this->aVar['peeve7'] = $laProt['V07PRO'];
							$this->aVar['peeve8'] = $laProt['V08PRO'];
							$this->aVar['peeanr'] = $laProt['AREPRO'];
							$this->aVar['peean1'] = $laProt['A01PRO'];
							$this->aVar['peean2'] = $laProt['A02PRO'];
							$this->aVar['peean3'] = $laProt['A03PRO'];
							$this->aVar['peean4'] = $laProt['A04PRO'];
							$this->aVar['peean5'] = $laProt['A05PRO'];
							$this->aVar['peean6'] = $laProt['A06PRO'];
							$this->aVar['peean7'] = $laProt['A07PRO'];
							$this->aVar['peean8'] = $laProt['A08PRO'];
							break;
						}
					}
				}

				if (empty($this->aVar['pepfe1']) || empty($this->aVar['pepfe2'])) {
					$this->aVar['pepfe1'] = 208 - ($this->nEdadA * 0.7);
					$this->aVar['pepfe2'] = intval($this->aVar['pepfe1'] * 0.85);
					$this->aVar['pepfe1'] = intval($this->aVar['pepfe1']);
				}

				break;
		}


		/** INIT DIAGNOSTICOS **/
		$this->aVar['rippro'] = $taData['cCUP'];
		$this->aVar['ripfin'] = floatval($this->aDiag['FPRAPS']??0);
		$this->aVar['ripdpr'] = trim($this->aDiag['DG1APS']??'0')=='0' ? '' : trim($this->aDiag['DG1APS']);
		$this->aVar['ripdre'] = trim($this->aDiag['DG2APS']??'0')=='0' ? '' : trim($this->aDiag['DG2APS']);
		$this->aVar['ripdco'] = trim($this->aDiag['DGCAPS']??'0')=='0' ? '' : trim($this->aDiag['DGCAPS']);
		$this->aVar['ripfaq'] = trim($this->aDiag['FAQAPS']??'');
		$this->aVar['ripnau'] = trim($this->aDiag['AUTAPS']??'');

		if ($this->cReporte=='CNI014' && empty($this->aVar['ripfin'])) { $this->aVar['ripfin']=2; }

	}


	/*
	 *	Prepara array $aReporte con los datos para imprimir
	 */
	private function prepararInforme($taData)
	{
		$this->crearVar([
			'inftit','inftex','infmre','infac1','infac2','infac3','infac4','infpro','infcon',
			'infmso','infequ','inftra','infmr1','infmr2','infmr3','infrep','infsea','infseb',
			'inflaa','inflam','inflab','infsaa','infsam','infsab','infpoa','infpom','infpob',
			'infina','infinm','infinb','infana','infanm','infanb','infobs','infti1','infsem',
			'infind','infpun'], '');

		$this->aVar['inftit'] = $this->cReporte=='CNI001' ? 'ECOCARDIOGRAMA TRANSTORACICO' :
				( in_array($this->cReporte, ['CNI013','CNI014','CNI020']) ? mb_strtoupper($this->aVar['lcCaption']) :
				( $this->cReporte=='CNI021A' ? 'DOCUMENTOS STRESS FARMACOLOGICO' : 'CARDIOLOGIA NO INVASIVA' ) );


		if (in_array($this->cReporte,['CNI014','CNI022'])) {
			$this->aVar['infac1'] = $this->aApreciacion[$this->aVar['peaac1']] ?? '';
			$this->aVar['infac2'] = $this->aApreciacion[$this->aVar['peaac2']] ?? '';
			$this->aVar['infac3'] = $this->aApreciacion[$this->aVar['peaac3']] ?? '';
			$this->aVar['infac4'] = $this->aApreciacion[$this->aVar['peaac4']] ?? '';
			$this->aVar['infpro'] = $this->aProtocolo[$this->aVar['peepro']]['DESPRO'] ?? '';

		} else {
			if (in_array($this->cReporte, ['CNI001','CNI020','CNI021'])) {
				$laCodEqu = [
					$this->aVar['etmequ'],
					$this->aVar['etmtr1'],
					$this->aVar['etmtr2'],
					$this->aVar['etmtr3'],
				];
			} elseif ($this->cReporte=='CNI013') {
				$laCodEqu = [
					$this->aVar['eteequ'],
					$this->aVar['etetr1'],
					$this->aVar['etetr2'],
					$this->aVar['etetr3'],
				];
			} else {
				$laCodEqu = ['X','X','X','X',];
			}
			$this->aVar['infequ'] = $this->fnObtenerEquipo($laCodEqu[0]);
			$this->aVar['inftra'] = ( isset($this->aValEquipos[$laCodEqu[1]]) ? '1. '.$this->aValEquipos[$laCodEqu[1]].'   ' : '' ).
									( isset($this->aValEquipos[$laCodEqu[2]]) ? '2. '.$this->aValEquipos[$laCodEqu[2]].'   ' : '' ).
									( isset($this->aValEquipos[$laCodEqu[3]]) ? '3. '.$this->aValEquipos[$laCodEqu[3]].'   ' : '' );
		}

		$lcCod = in_array($this->cReporte,['CNI001','CNI020']) ? $this->aVar['etctex'] :
					( $this->cReporte=='CNI013' ? $this->aVar['etetex'] :
					( in_array($this->cReporte,['CNI014','CNI022']) ? $this->aVar['peccon'] : 'x' ) );
		$this->aVar['inftex'] = $this->aVar['infcon'] = $this->aConclusiones[$lcCod]['DESCON'] ?? '';

		$this->aVar['infmr1'] = in_array($this->cReporte,['CNI001','CNI020']) ? $this->aVar['etcmre'] :
								( $this->cReporte=='CNI013' ? $this->aVar['etemre'] :
								( in_array($this->cReporte,['CNI014','CNI022']) ? $this->aVar['pecmre'] :
								( $this->cReporte=='CNI021' ? $this->aVar['etcmrv'] : '' ) ) );

		// Consulta por reporte
		switch ($this->cReporte) {
			case 'CNI001': case 'CNI020':
				$this->informeCNI001();
				break;
			case 'CNI013':
				$this->informeCNI013();
				break;
			case 'CNI014': case 'CNI022':
				$this->informeCNI014();
				break;
			case 'CNI021':
				$this->informeCNI021();
				break;
/*
			case 'CNI021A':
				$this->informeCNI021A(); //tcPrepararDatos
				break;
*/
		}

		$lnNumF = count($this->aReporte['aCuerpo']);
		$this->aReporte['aCuerpo'][$lnNumF] = ['firmas', [ [ 'registro'=>$this->aVar['infmr1'] ], ] ];
		if (!empty($this->aVar['infmr2'])) { $laTr['aCuerpo'][$lnNumF][1][] = [ 'registro'=>$this->aVar['infmr2'] ]; }
		if (!empty($this->aVar['infmr3'])) { $laTr['aCuerpo'][$lnNumF][1][] = [ 'registro'=>$this->aVar['infmr3'] ]; }


		// Adicionar info de los otros reportes
		if ($this->cReporte=='CNI020') {
			if($this->cTipoProc==0){
				$this->cReporte='CNI022';
				$this->consulta_Prueba_Esfuerzo($taData);
				$this->prepararInforme($taData);
			} elseif($this->cTipoProc==1){
				$this->cReporte='CNI021';
				$this->consulta_Prueba_Farmacologica($taData);
				$this->prepararInforme($taData);
			}
		} elseif ($this->cReporte=='CNI021') {
			//$this->consulta_Prueba_Farmacologica();
			$this->aVar['infmso'] = $this->aVar['etgmso'];
			$this->consulta_Datos_Farmacologia($taData['nIngreso'], $taData['nConsecCita'], $taData['cCUP']);

			//lcTituloCup = "Estudio"
			//lcTextoAdicional = ""
			//.mInsertarEncabezado("DOCUMENTOS STRESS FARMACOLOGICO~Departamento de " + PROPER(ALLTRIM(oVar.cDesEsp)), oVar.tNumDti(oVar.nFecRea, oVar.nHorRea, .T.), llMostrarViaCama, lcTituloCup, lcTextoAdicional)

			if ($taData['format']=='PDF') {
				//	Envía los 4 reportes con imagen
				for($lnNum=1; $lnNum<5; $lnNum++){
					$this->informeCNI021Aimg($lnNum);
				}
			} else {
				//	Envía los 4 reportes con tabla de datos
				for($lnNum=1; $lnNum<5; $lnNum++){
					$this->informeCNI021A($lnNum);
				}
			}
		}

		$this->aReporte['cTitulo'] = $this->aVar['inftit'] . PHP_EOL . 'Departamento de Cardiología No Invasiva';

	}



	/******************************  OTRAS FUNCIONES  ******************************/

	/*
	 *	Organiza reporte para CNI001 Y CNI020
	 */
	private function informeCNI001()
	{
		$this->infGeneral();
		$this->infEquipos($this->infMedidas());
		$this->infDescripcionG($this->aVar['etccon']);
	}


	/*
	 *	Organiza reporte para CNI013
	 */
	private function informeCNI013()
	{
		$this->infGeneral();
		$this->infEquipos();
		$this->infDescripcionG($this->aVar['etecon']);
	}


	/*
	 *	Organiza información general para CNI001, CNI020, CNI021 y CNI013
	 */
	private function infGeneral()
	{
		$this->aReporte['aCuerpo'][]=['titulo2', 'GENERAL'];
		if(!empty($this->aVar['etgpes']) && !empty($this->aVar['etgenu'])){
			$lcTxt= (empty($this->aVar['etgpes']) ? str_repeat(' ',30) : 'Peso:     '.str_pad($this->aVar['etgpes'].' Kg',20)).
					(empty($this->aVar['etgenu']) ? str_repeat(' ',30) : 'Eco:      '.$this->aVar['etgenu']);
			$this->aReporte['aCuerpo'][]=['texto9', $lcTxt];
		}
		if(!empty($this->aVar['etgtal']) && !empty($this->aVar['etgpnu'])){
			$lcTxt= (empty($this->aVar['etgtal']) ? str_repeat(' ',30) : 'Talla:    '.str_pad($this->aVar['etgtal'].' cm',20)).
					(empty($this->aVar['etgpnu']) ? str_repeat(' ',30) : 'Película: '.$this->aVar['etgpnu']);
			$this->aReporte['aCuerpo'][]=['texto9', $lcTxt];
		}
		if(!empty($this->aVar['etgsco'])){ $this->aReporte['aCuerpo'][]=['texto9', 'S.C.:     '.number_format($this->aVar['etgsco'],2).' m2']; }

		if(!empty($this->aVar['etgdcl'])){
			$this->aReporte['aCuerpo'][]=['titulo3', 'Diagnóstico Clínico'];
			$this->aReporte['aCuerpo'][]=['texto9', $this->aVar['etgdcl']];
		}

		if(!empty($this->aVar['etgmso'])){
			$this->aReporte['aCuerpo'][]=['titulo3', 'Médico Que Solicita'];
			$this->aReporte['aCuerpo'][]=['texto9', $this->aVar['etgmso']];
		}

		if(!empty($this->aVar['etgdms'])){
			$this->aReporte['aCuerpo'][]=['titulo3', 'Observaciones'];
			$this->aReporte['aCuerpo'][]=['texto9', $this->aVar['etgdms']];
		}
	}


	/*
	 *	Organiza Tabla de Medidas para CNI001 y CNI020
	 */
	private function infMedidas()
	{
		$llTtlMedEqu = true;
		if( !empty($this->aVar['etmaiz']) || !empty($this->aVar['etmrao']) || !empty($this->aVar['etmvao']) || !empty($this->aVar['etmvis']) || !empty($this->aVar['etmvid']) ||
			!empty($this->aVar['etmsin']) || !empty($this->aVar['etmppo']) || !empty($this->aVar['etmvds']) || !empty($this->aVar['etmfey']) || !empty($this->aVar['etmcas']) ) {

			if( $this->cReporte=='CNI001' || ( $this->cReporte=='CNI020' && !empty($this->aVar['etmsdn']) ) ){
				$laW = [50, 18, 25, 50, 18, 25];
				$laA = ['L','C','C','L','C','C'];

				$this->aReporte['aCuerpo'][]=['titulo2', 'MEDIDAS Y EQUIPOS'];
				$llTtlMedEqu = false;
				$lnNumTbl = count($this->aReporte['aCuerpo']);
				$this->aReporte['aCuerpo'][$lnNumTbl]=['tabla',
					[
						['w'=>$laW, 'a'=>$laA, 'd'=>['Modo M, Medidas y Calculos','Paciente','Normal','Modo M, Medidas y Calculos','Paciente','Normal'] ],
					],
					[
						[ 'w'=>$laW, 'a'=>$laA, 'd'=>[
							'Aurícula Izquierda',number_format($this->aVar['etmaiz'],2),$this->aVar['etmai1'].' - '.$this->aVar['etmai2'],
							'Septum Interventricular',number_format($this->aVar['etmsin'],2),$this->aVar['etmsi1'].' - '.$this->aVar['etmsi2'], ]
						],
						[ 'w'=>$laW, 'a'=>$laA, 'd'=>[
							'Raíz Aórtica',number_format($this->aVar['etmrao'],2),$this->aVar['etmra1'].' - '.$this->aVar['etmra2'],
							'Pared Posterior V.I.',number_format($this->aVar['etmppo'],2),$this->aVar['etmpp1'].' - '.$this->aVar['etmpp2'], ]
						],
						[ 'w'=>$laW, 'a'=>$laA, 'd'=>[
							'Válvula Aórtica',number_format($this->aVar['etmvao'],2),$this->aVar['etmva1'].' - '.$this->aVar['etmva2'],
							'Ventrículo Der. Diástole',number_format($this->aVar['etmvds'],2),$this->aVar['etvds1'].' - '.$this->aVar['etvds2'], ]
						],
						[ 'w'=>$laW, 'a'=>$laA, 'd'=>[
							'Ventrículo Izq. Sístole',number_format($this->aVar['etmvis'],2),'',
							'Fracción de Eyección',number_format($this->aVar['etmfey'],2),'> 60', ]
						],
					],
				];
				if( $this->cReporte=='CNI020' || empty($this->aVar['etmcas']) ){
					$this->aReporte['aCuerpo'][$lnNumTbl][2][] = [ 'w'=>$laW, 'a'=>$laA, 'd'=>[
							'Ventrículo Izq. Diástole',number_format($this->aVar['etmvid'],2),$this->aVar['etvid1'].' - '.$this->aVar['etvid2'],'','','', ]
						];
				} else {
					$this->aReporte['aCuerpo'][$lnNumTbl][2][] = [ 'w'=>$laW, 'a'=>$laA, 'd'=>[
							'Ventrículo Izq. Diástole',number_format($this->aVar['etmvid'],2),$this->aVar['etvid1'].' - '.$this->aVar['etvid2'],
							'Coef. Acort. Sistólico',number_format($this->aVar['etmcas'],2),$this->aVar['etmca1'].' - '.$this->aVar['etmca2'], ]
						];
				}
			}
		}
		return $llTtlMedEqu;
	}


	/*
	 *	Organiza Equipos para CNI001, CNI020, CNI021 y CNI013
	 */
	private function infEquipos($tlTitulo=true)
	{
		if(!empty($this->aVar['infequ']) || !empty($this->aVar['inftra'])){
			if($tlTitulo){
				$lcTtl = ( $this->cReporte=='CNI001' || ( $this->cReporte=='CNI020' && !empty($this->aVar['etmsdn']) ) ? 'MEDIDAS Y ' : '' ) . 'EQUIPOS';
				$this->aReporte['aCuerpo'][]=['titulo2', $lcTtl];
			}
			$lcTxt = ( empty($this->aVar['infequ']) ? '' : 'Equipo         : '.$this->aVar['infequ'].PHP_EOL )
					.( empty($this->aVar['inftra']) ? '' : 'Transductor(es): '.$this->aVar['inftra'] );
			$this->aReporte['aCuerpo'][]=['texto9', $lcTxt];
		}
	}


	/*
	 *	Organiza Descripción general para CNI001, CNI020 y CNI013
	 */
	private function infDescripcionG($tcDescripcion)
	{
		if(!empty($tcDescripcion)){
			$this->aReporte['aCuerpo'][]=['titulo2', 'DESCRIPCIÓN GENERAL'];
			$this->aReporte['aCuerpo'][]=['texto9', $tcDescripcion];
		}
	}


	/*
	 *	Organiza reporte para CNI014
	 */
	private function informeCNI014()
	{
		if($this->cReporte=='CNI022'){
			$this->aVar['pegpes'] = $this->aVar['etgpes'];
			$this->aVar['pegtal'] = $this->aVar['etgtal'];
			$this->aVar['pegsco'] = $this->aVar['etgsco'];
			$this->aVar['pegdcl'] = $this->aVar['etgdcl'];
			$this->aVar['pegmso'] = $this->aVar['etgmso'];
			$this->aVar['pegdms'] = $this->aVar['etgdms'];
		}

		// .T. si es con Isonitrilos + Dobutamina
		$llDatosB = intval($this->cTipoProc)==2;

		// GENERAL
		$lcTxt = ( empty($this->aVar['pegpes']) ? '' : 'Peso:          '. $this->aVar['pegpes'].' Kg'.PHP_EOL )
				.( empty($this->aVar['pegtal']) ? '' : 'Talla:         '. $this->aVar['pegtal'].' cm'.PHP_EOL )
				.( empty($this->aVar['pegsco']) ? '' : 'S.C.:          '. number_format($this->aVar['pegsco'],2).' m2'.PHP_EOL )
				.( empty($this->aVar['pegenu']) ? '' : 'Prueba Número: '. $this->aVar['pegenu'] );
		$this->aReporte['aCuerpo'][]=['titulo2', 'GENERAL'];
		$this->aReporte['aCuerpo'][]=['texto9', $lcTxt];

		if(!empty($this->aVar['pegdcl'])){
			$this->aReporte['aCuerpo'][]=['titulo3', 'Diagnóstico Clínico:'];
			$this->aReporte['aCuerpo'][]=['texto9', $this->aVar['pegdcl']];
		}
		if(!empty($this->aVar['pegmso'])){
			$this->aReporte['aCuerpo'][]=['titulo3', 'Médico Que Solicita:'];
			$this->aReporte['aCuerpo'][]=['texto9', $this->aVar['pegmso']];
		}
		if(!empty($this->aVar['pegdms'])){
			$this->aReporte['aCuerpo'][]=['titulo3', 'Observaciones:'];
			$this->aReporte['aCuerpo'][]=['texto9', $this->aVar['pegdms']];
		}

		//	APRECIACIÓN CLÍNICA SUBJETIVA
		$lcSL = '<br>'; //PHP_EOL;
		$lcTxt = ( empty($this->aVar['infac1']) ? '' : $this->aVar['infac1'].$lcSL )
				.( empty($this->aVar['infac2']) ? '' : $this->aVar['infac2'].$lcSL )
				.( empty($this->aVar['infac3']) ? '' : $this->aVar['infac3'].$lcSL )
				.( empty($this->aVar['infac4']) ? '' : $this->aVar['infac4'] );
		$this->aReporte['aCuerpo'][]=['titulo2', 'APRECIACIÓN CLÍNICA SUBJETIVA'];
		$this->aReporte['aCuerpo'][]=['txthtml9', '<b>'.$lcTxt.'</b>'];
		if(!empty($this->aVar['peaaca'])){
			$this->aReporte['aCuerpo'][]=['titulo3', 'Antecedentes Cardiovasculares:'];
			$this->aReporte['aCuerpo'][]=['texto9', $this->aVar['peaaca']];
		}
		if(!empty($this->aVar['peatra'])){
			$this->aReporte['aCuerpo'][]=['titulo3', 'Tratamiento:'];
			$this->aReporte['aCuerpo'][]=['texto9', $this->aVar['peatra']];
		}
		if(!empty($this->aVar['pearep'])){
			$this->aReporte['aCuerpo'][]=['titulo3', 'ECG en Reposo:'];
			$this->aReporte['aCuerpo'][]=['texto9', $this->aVar['pearep']];
		}
		if(!empty($this->aVar['peaeje'])){
			$this->aReporte['aCuerpo'][]=['titulo3', ($llDatosB ? 'ECG Durante la Aplicación de la Dobutamina:': 'ECG en Ejercicio:') ];
			$this->aReporte['aCuerpo'][]=['texto9', $this->aVar['peaeje']];
		}
		if(!empty($this->aVar['peapej'])){
			$this->aReporte['aCuerpo'][]=['titulo3', ($llDatosB ? 'ECG en periodo de Recuperación:': 'ECG Post Ejercicio:') ];
			$this->aReporte['aCuerpo'][]=['texto9', $this->aVar['peapej']];
		}

		if($llDatosB){
			// TABLA DE DATOS DURANTE LA INFUSION DEL FARMACO
			$laW = [25,15,15,15,120];
			$laA = ['L','C','C','C','L'];
			$laD = ['rp'=>'Reposo', '05'=>'5 Mcg', '10'=>'10 Mcg', '20'=>'20 Mcg', '30'=>'30 Mcg', '40'=>'40 Mcg', '50'=>'50 Mcg', 'rc'=>'Recuperación',];
			$laFilas = [];
			foreach($laD as $lcK=>$lcD){
				$laFilas[] = ['w'=>$laW,'a'=>$laA,'d'=>[$lcD,$this->aVar["pef{$lcK}1"],$this->aVar["pef{$lcK}2"],$this->aVar["pef{$lcK}3"],$this->aVar["pef{$lcK}s"] ] ];
			}
			$this->aReporte['aCuerpo'][]=['titulo2', 'TABLA DE DATOS DURANTE LA INFUSIÓN DEL FARMACO'];
			$this->aReporte['aCuerpo'][]=['tabla', [ ['w'=>$laW,'a'=>'C','d'=>['Dosis','TAS','TAD','FC','Síntomas',] ], ], $laFilas];

		} else {
			// TABLA DE DATOS DURANTE EL EJERCICIO Y POST EJERCICIO
			$laTitulos = ['Medida'=>'','Reposo'=>'r','I'=>'1','II'=>'2','III'=>'3','IV'=>'4','V'=>'5','VI'=>'6','VII'=>'7','VIII'=>'8'];
			$laMedidas = ['peeme'=>'Mets','peeve'=>'Velocidad MPH','peean'=>'Angulo %','peefc'=>'Frec.Cardiaca','peets'=>'Tens.Arterial S.','peetd'=>'Tens.Arterial D.'];
			$laA = $laW = $laTit = $laDatas = $laFilas = [];
			$lnCol = 0;
			foreach($laTitulos as $lcTtl=>$lcPos){
				if($lnCol>3)
					if($this->aVar['peetd'.$lcPos] + $this->aVar['peets'.$lcPos] + $this->aVar['peefc'.$lcPos] == 0)
						break;

				$laTit[] = $lcTtl;
				if(empty($lcPos)){
					$laW[] = 35; $laA[] = 'L';
				} else {
					$laW[] = 17; $laA[] = 'C';
				}

				$lnFila = 0;
				foreach($laMedidas as $lcK=>$lcD){
					$laDatas[$lnFila][] = empty($lcPos) ? $lcD : $this->aVar[$lcK.$lcPos];
					$lnFila++;
				}
				$lnCol++;
			}
			foreach($laDatas as $laData)
				$laFilas[] = ['w'=>$laW, 'a'=>$laA, 'd'=>$laData];
			$this->aReporte['aCuerpo'][]=['titulo2', 'TABLA DE DATOS DURANTE EL EJERCICIO'];
			$this->aReporte['aCuerpo'][]=['texto9', 'Protocolo: '.ucwords($this->aVar['infpro']??'')];
			$this->aReporte['aCuerpo'][]=['tabla', [ ['w'=>$laW,'a'=>'C','d'=>$laTit ], ], $laFilas];

			$laW = [35,20,20,20,20];
			$laA = ['L','C','C','C','C'];
			$this->aReporte['aCuerpo'][]=['titulo2', 'TABLA DE DATOS POST EJERCICIO'];
			$this->aReporte['aCuerpo'][]=['tabla',
				[	['w'=>$laW,'a'=>'C','d'=>['Tiempo','Inmed.','3 Min','5 Min','8 Min',] ], ],
				[
					['w'=>$laW,'a'=>$laA,'d'=>['Frec.Cardiaca',   $this->aVar['pepfci'],$this->aVar['pepfc3'],$this->aVar['pepfc5'],$this->aVar['pepfc8']]],
					['w'=>$laW,'a'=>$laA,'d'=>['Tens.Arterial S.',$this->aVar['peptsi'],$this->aVar['pepts3'],$this->aVar['pepts5'],$this->aVar['pepts8']]],
					['w'=>$laW,'a'=>$laA,'d'=>['Tens.Arterial D.',$this->aVar['peptdi'],$this->aVar['peptd3'],$this->aVar['peptd5'],$this->aVar['peptd8']]],
				]
			];
		}

		// EVALUACIÓN DE LA PRUEBA
		$lcTxt = 'Frecuencia Cardíaca Máxima Esperada:  '.intval($this->aVar['pepfe1']).' / '.intval($this->aVar['pepfe2']).PHP_EOL
				.'Frecuencia Cardíaca Máxima Realizada: '.intval($this->aVar['pepfcr']).PHP_EOL
				.'Porcentaje:      '.$this->aVar['peppor'].' %'.PHP_EOL
				.($llDatosB ? '' : 'Mets:            '.$this->aVar['pepmet'].PHP_EOL)
				.'Doble Producto:  '.$this->aVar['pepdpr'].PHP_EOL
				.($llDatosB ? '' : 'Clase Funcional: '.$this->aVar['pepcfu'].PHP_EOL)
				.'Prueba Suspendida Por: '.$this->aVar['peppsu'].PHP_EOL;
		$this->aReporte['aCuerpo'][]=['titulo2', 'EVALUACIÓN DE LA PRUEBA'];
		$this->aReporte['aCuerpo'][]=['texto9', $lcTxt];


		if(!empty($this->aVar['infcon'])){
			$this->aReporte['aCuerpo'][]=['titulo2', 'CONCLUSIONES: '.mb_strtoupper($this->aVar['infcon'])];
			if(!empty($this->aVar['pecobs'])){
				$this->aReporte['aCuerpo'][]=['titulo3', 'Observaciones'];
				$this->aReporte['aCuerpo'][]=['texto9', $this->aVar['pecobs']];
			}
		}
	}


	/*
	 *	Organiza reporte para CNI021
	 *	ECO TRANSESOFAGICO SIN CONTRASTE
	 */
	private function informeCNI021()
	{
		$this->infGeneral();
		$this->infEquipos();

		// EVALUACION DE LA PRUEBA
		$lcTxt = ( empty($this->aVar['ete100']) ? '' :
				 '100% de la FC Máxima Esperada: '.intval($this->aVar['ete100']).PHP_EOL
				.' 85% de la FC Máxima Esperada: '.intval($this->aVar['ete085']).PHP_EOL
				.' 85% de la FC Máxima Esperada: '.intval($this->aVar['ete075']).PHP_EOL
				.' 85% de la FC Máxima Esperada: '.intval($this->aVar['ete065']).PHP_EOL
				.'Frecuencia Máxima Realizada  : '.intval($this->aVar['etefmr']).PHP_EOL )
				.( empty($this->aVar['etepro']) ? '' : 'Protocolo:             '.$this->aVar['etepro'].PHP_EOL )
				.( empty($this->aVar['etepor']) ? '' : 'Porcentaje:            '.$this->aVar['etepor'].'%'.PHP_EOL )
				.( empty($this->aVar['etedpr']) ? '' : 'Doble Producto:        '.$this->aVar['etedpr'].PHP_EOL )
				.( empty($this->aVar['etepsu']) ? '' : 'Prueba Suspendida Por: '.$this->aVar['etepsu'].PHP_EOL )
				.( empty($this->aVar['infces']) ? '' : 'Calidad del Estudio:   '.$this->aVar['infces'].PHP_EOL );
		$this->aReporte['aCuerpo'][]=['titulo2', 'EVALUACIÓN DE LA PRUEBA'];
		$this->aReporte['aCuerpo'][]=['texto9', $lcTxt];

		//TABLA DE DATOS DURANTE EL ESTUDIO
		$laW=[25,15,15,15,120];
		$laA=['L','C','C','C','L'];
		$laDosis=['rp'=>'Reposo','05'=>'5 Mcg','10'=>'10 Mcg','20'=>'20 Mcg','30'=>'30 Mcg','40'=>'40 Mcg','50'=>'50 Mcg','rc'=>'Recuperación',];
		$laFilas=[];
		foreach($laDosis as $lcK=>$lcD)
			$laFilas[]=['w'=>$laW,'a'=>$laA,'d'=>[$lcD,$this->aVar["ete{$lcK}1"],$this->aVar["ete{$lcK}2"],$this->aVar["ete{$lcK}3"],$this->aVar["ete{$lcK}s"]]];
		$this->aReporte['aCuerpo'][]=['titulo2', 'TABLA DE DATOS DURANTE EL ESTUDIO'];
		$this->aReporte['aCuerpo'][]=['tabla', [ ['w'=>$laW,'a'=>'C','d'=>['Dosis','TAS','TAD','FC','Síntomas',]] ], $laFilas, ];

		// CONCLUSIONES
		if(!empty($this->aVar['etcccl'])){
			$this->aReporte['aCuerpo'][]=['titulo2', 'CONCLUSIONES'];
			$this->aReporte['aCuerpo'][]=['texto9', $this->aVar['etcccl']];
		}
	}


	/*
	 *	Organiza reporte para CNI021A
	 */
	private function informeCNI021A($tnTipoInforme)
	{
		$this->datos_Farm($tnTipoInforme);

		$taTipos = [1=>'SEPTAL',2=>'LATERAL',3=>'SEPTAL ANTERIOR',4=>'POSTERIOR',5=>'INTERIOR',6=>'ANTERIOR'];

		/***   TABLA   ***/
		$laW = [40,20,20,20,20];
		$laA = ['L','C','C','C','C',];
		$laFilas = [];
		foreach($taTipos as $lnNum=>$lcTitulo){
			$laFilas[] = ['w'=>$laW, 'a'=>$laA, 'd'=>[
				$lcTitulo,
				$this->aDatFarm['dat'][$lnNum][1],
				$this->aDatFarm['dat'][$lnNum][2],
				$this->aDatFarm['dat'][$lnNum][3],
				$this->aDatFarm['sum'][$lnNum],
			]];
		}
		$this->aReporte['aCuerpo'][]=['titulo2', str_pad($this->aDatFarm['ttl'],70).'PUNTAJE: '.$this->aDatFarm['sum']['total']];
		$this->aReporte['aCuerpo'][]=['tabla', [ ['w'=>$laW, 'a'=>'C', 'd'=>['','Apical','Medio','Basal','TOTAL',]] ], $laFilas];
		$this->aReporte['aCuerpo'][]=['texto9', $this->aDatFarm['obs']];
	}


	/*
	 *	Organiza reporte para CNI021A
	 */
	private function informeCNI021Aimg($tnTipoInforme)
	{
		if(isset($this->aDatFarm['dat'])){
			$this->datos_Farm($tnTipoInforme);
			$taTipos = [1=>'SEPTAL',2=>'LATERAL',3=>'SEPTAL ANTERIOR',4=>'POSTERIOR',5=>'INTERIOR',6=>'ANTERIOR'];

			/***  IMAGEN  ***/
			$this->aReporte['aCuerpo'][]=['saltop', null];
			$this->aReporte['aCuerpo'][]=['titulo1', str_pad($this->aDatFarm['ttl'],70).'PUNTAJE: '.$this->aDatFarm['sum']['total']];
			$this->aReporte['aCuerpo'][]=['imagen', [ 'archivo'=>$this->cImagen, 'w'=>160, 'h'=>150, ] ];


			$aDat = $this->aDatFarm['dat'];

			// Imagen Superior
				// Septal Anterior
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[3][1],'x'=>20,'y'=>-143,'w'=>10,'h'=>5,'aling'=>'C','border'=>1,]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[3][2],'x'=>32,'y'=>-132,'w'=>10,'h'=>5,'aling'=>'C',]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[3][3],'x'=>43,'y'=>-132,'w'=>10,'h'=>5,'aling'=>'C',]];
				// Posterior
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[4][1],'x'=>20,'y'=>-115,'w'=>10,'h'=>5,'aling'=>'C',]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[4][2],'x'=>32,'y'=>-126,'w'=>10,'h'=>5,'aling'=>'C',]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[4][3],'x'=>43,'y'=>-126,'w'=>10,'h'=>5,'aling'=>'C',]];
			// Imagen Medio
				// Septal
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[1][1],'x'=>20,'y'=>-102,'w'=>10,'h'=>5,'aling'=>'C','border'=>1,]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[1][2],'x'=>22,'y'=>-87 ,'w'=>10,'h'=>5,'aling'=>'C',]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[1][3],'x'=>22,'y'=>-78 ,'w'=>10,'h'=>5,'aling'=>'C',]];
				// Lateral
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[2][1],'x'=>50,'y'=>-102,'w'=>10,'h'=>5,'aling'=>'C','border'=>1,]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[2][2],'x'=>41,'y'=>-87 ,'w'=>10,'h'=>5,'aling'=>'C',]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[2][3],'x'=>41,'y'=>-78 ,'w'=>10,'h'=>5,'aling'=>'C',]];
				// Septal Anterior
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[3][1],'x'=>64,'y'=>-102,'w'=>10,'h'=>5,'aling'=>'C','border'=>1,]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[3][2],'x'=>82,'y'=>-87 ,'w'=>10,'h'=>5,'aling'=>'C',]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[3][3],'x'=>82,'y'=>-78 ,'w'=>10,'h'=>5,'aling'=>'C',]];
				// Posterior
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[4][1],'x'=>103,'y'=>-102,'w'=>10,'h'=>5,'aling'=>'C','border'=>1,]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[4][2],'x'=>106,'y'=>-87 ,'w'=>10,'h'=>5,'aling'=>'C',]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[4][3],'x'=>106,'y'=>-78 ,'w'=>10,'h'=>5,'aling'=>'C',]];
				// Interior
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[5][1],'x'=>113,'y'=>-102,'w'=>10,'h'=>5,'aling'=>'C','border'=>1,]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[5][2],'x'=>124,'y'=>-87 ,'w'=>10,'h'=>5,'aling'=>'C',]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[5][3],'x'=>124,'y'=>-78 ,'w'=>10,'h'=>5,'aling'=>'C',]];
				// Anterior
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[6][1],'x'=>145,'y'=>-102,'w'=>10,'h'=>5,'aling'=>'C','border'=>1,]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[6][2],'x'=>134,'y'=>-87 ,'w'=>10,'h'=>5,'aling'=>'C',]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[6][3],'x'=>134,'y'=>-78 ,'w'=>10,'h'=>5,'aling'=>'C',]];
			// Imagen Inferior
				// Septal
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[1][1],'x'=>29,'y'=>-25,'w'=>10,'h'=>5,'aling'=>'C','border'=>1,]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[1][2],'x'=>22,'y'=>-25,'w'=>10,'h'=>5,'aling'=>'C',]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[1][3],'x'=>15,'y'=>-25,'w'=>10,'h'=>5,'aling'=>'C',]];
				// Lateral
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[2][1],'x'=>36,'y'=>-25,'w'=>10,'h'=>5,'aling'=>'C','border'=>1,]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[2][2],'x'=>43,'y'=>-25,'w'=>10,'h'=>5,'aling'=>'C',]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[2][3],'x'=>50,'y'=>-25,'w'=>10,'h'=>5,'aling'=>'C',]];
				// Septal Anterior
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[3][1],'x'=>31,'y'=>-28.5,'w'=>10,'h'=>5,'aling'=>'C','border'=>1,]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[3][2],'x'=>28,'y'=>-33  ,'w'=>10,'h'=>5,'aling'=>'C',]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[3][3],'x'=>24,'y'=>-40  ,'w'=>10,'h'=>5,'aling'=>'C',]];
				// Posterior
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[4][1],'x'=>34,'y'=>-21.5,'w'=>10,'h'=>5,'aling'=>'C','border'=>1,]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[4][2],'x'=>37,'y'=>-17  ,'w'=>10,'h'=>5,'aling'=>'C',]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[4][3],'x'=>41,'y'=>-10  ,'w'=>10,'h'=>5,'aling'=>'C',]];
				// Interior
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[5][1],'x'=>31,'y'=>-21.5,'w'=>10,'h'=>5,'aling'=>'C','border'=>1,]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[5][2],'x'=>28,'y'=>-17  ,'w'=>10,'h'=>5,'aling'=>'C',]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[5][3],'x'=>24,'y'=>-10  ,'w'=>10,'h'=>5,'aling'=>'C',]];
				// Anterior
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[6][1],'x'=>34,'y'=>-28.5,'w'=>10,'h'=>5,'aling'=>'C','border'=>1,]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[6][2],'x'=>37,'y'=>-33  ,'w'=>10,'h'=>5,'aling'=>'C',]];
				$this->aReporte['aCuerpo'][]=['cuadrotxt', [ 'text'=>$aDat[6][3],'x'=>41,'y'=>-40  ,'w'=>10,'h'=>5,'aling'=>'C',]];

			$this->aReporte['aCuerpo'][]=['texto9', $this->aDatFarm['obs']];
		}
	}


	/******************************  DATOS INFORMES ESPECIALES  ******************************/


	/*
	 *	Consulta Prueba Esfuerzo
	 */
	private function consulta_Prueba_Esfuerzo($taData)
	{
		$this->consultarDatos($taData);

		//	INICIALIZA VARIABLES PUBLICAS SI VIENE DEL PROCESO DE AGILITY
		$this->crearVar([
			'infmso','infequ','inftra','inftex','infmre','infmr2','infmr3','infces','infac1','infac2',
			'infac3','infac4','infpro','infcon','inftit','infsea','infsem','infseb','inflaa','inflam',
			'inflab','infsaa','infsam','infsab','infpoa','infpom','infpob','infina','infinm','infinb',
			'infana','infanm','infanb','infobs','infti1','infind','infpun','pegtpr','pegtal','etgdcl',
			'etgmso','pegmso','pegdms','etgdms','pegdcl','etgdcl','radfec','peaac1','peaac2','peaac3',
			'peaac4','peaaca','peatra','pearep','peaeje','peapej','peepro','pepcfu','peppsu','peccon',
			'pecobs','pecmre','pecmrz',], '');
		$this->crearVar([
			'etgpes','etgtal','pegsco','pegpes','etgsco','pegenu','pegcon',
			'peemer','peeme1','peeme2','peeme3','peeme4','peeme5','peeme6','peeme7','peeme8',
			'peever','peeve1','peeve2','peeve3','peeve4','peeve5','peeve6','peeve7','peeve8',
			'peeanr','peean1','peean2','peean3','peean4','peean5','peean6','peean7','peean8',
			'peefcr','peefc1','peefc2','peefc3','peefc4','peefc5','peefc6','peefc7','peefc8',
			'peetsr','peets1','peets2','peets3','peets4','peets5','peets6','peets7','peets8',
			'peetdr','peetd1','peetd2','peetd3','peetd4','peetd5','peetd6','peetd7','peetd8',
			'pepfci','pepfc3','pepfc5','pepfc8','peptsi','pepts3','pepts5','pepts8','peptdi',
			'peptd3','peptd5','peptd8','pepfe1','pepfe2','pepfcr','peppor','pepmet','pepdpr',], 0);

		$this->aVar['pegtpr'] = $this->cTipoProc;
		$this->aVar['lcCaption'] = $this->aEcoStress[$this->cTipoProc]??'';
		if (!empty($this->aVar['lcCaption']))
			$this->aVar['lcCaption'] = 'Eco de Stress Con '.$this->aVar['lcCaption'];
		$this->aVar['inftit'] = strtoupper($this->aVar['lcCaption']);

		$this->aVar['radfec'] = $taData['tFechaHora'];

		foreach($this->aData as $ecos){
			switch($ecos['INDECO']){

				// GENERAL
				case 1:
					switch(true){
						case $ecos['LINECO']==1:
							$this->aVar['etgpes'] = floatval(substr($ecos['DSCECO'],70,9));
							$this->aVar['etgtal'] = floatval(substr($ecos['DSCECO'],90,9));
							break;
						case $ecos['LINECO']==5:
							$this->aVar['etgmso'] = trim(substr($ecos['DSCECO'],30,90));
							break;
						case $ecos['LINECO']==6:
							$this->aVar['etgdms'] = trim(substr($ecos['DSCECO'],30,90));
							break;
						case $ecos['LINECO']>10:
							$this->aVar['etgdcl'] .= trim(substr($ecos['DSCECO'],0,120));
							break;
					}

				// APRECIACION
				case 22:
					switch($ecos['SUBECO']){
						case 0:
							if($ecos['LINECO']==2){
								$this->aVar['peaac1'] = trim(substr($ecos['DSCECO'], 26, 3));
								$this->aVar['peaac2'] = trim(substr($ecos['DSCECO'], 36, 3));
								$this->aVar['peaac3'] = trim(substr($ecos['DSCECO'], 46, 3));
								$this->aVar['peaac4'] = trim(substr($ecos['DSCECO'], 56, 3));
							}
							break;
						case 1:
							if($ecos['LINECO']>0)
								$this->aVar['peaaca'] .= substr($ecos['DSCECO'], 0, 120);
							break;
						case 2:
							if($ecos['LINECO']>0)
								$this->aVar['peatra'] .= substr($ecos['DSCECO'], 0, 120);
							break;
						case 3:
							if($ecos['LINECO']>0)
								$this->aVar['pearep'] .= substr($ecos['DSCECO'], 0, 120);
							break;
						case 4:
							if($ecos['LINECO']>0)
								$this->aVar['peaeje'] .= substr($ecos['DSCECO'], 0, 120);
							break;
						case 5:
							if($ecos['LINECO']>0)
								$this->aVar['peapej'] .= substr($ecos['DSCECO'], 0, 120);
							break;
					}
					break;

				// EJERCICIO
				case 23:
					switch($ecos['LINECO']){
						case 1:
							$this->aVar['peepro'] = trim(substr($ecos['DSCECO'], 25, 4));
							$this->aVar['peefcr'] = floatval(substr($ecos['DSCECO'],  35, 4));
							$this->aVar['peefc1'] = floatval(substr($ecos['DSCECO'],  45, 4));
							$this->aVar['peefc2'] = floatval(substr($ecos['DSCECO'],  55, 4));
							$this->aVar['peefc3'] = floatval(substr($ecos['DSCECO'],  65, 4));
							$this->aVar['peefc4'] = floatval(substr($ecos['DSCECO'],  75, 4));
							$this->aVar['peefc5'] = floatval(substr($ecos['DSCECO'],  85, 4));
							$this->aVar['peefc6'] = floatval(substr($ecos['DSCECO'],  95, 4));
							$this->aVar['peefc7'] = floatval(substr($ecos['DSCECO'], 105, 4));
							$this->aVar['peefc8'] = floatval(substr($ecos['DSCECO'], 115, 4));
							break;
						case 2:
							$this->aVar['peepro'] = trim(substr($ecos['DSCECO'], 25, 4));
							$this->aVar['peetsr'] = floatval(substr($ecos['DSCECO'],  35, 4));
							$this->aVar['peets1'] = floatval(substr($ecos['DSCECO'],  45, 4));
							$this->aVar['peets2'] = floatval(substr($ecos['DSCECO'],  55, 4));
							$this->aVar['peets3'] = floatval(substr($ecos['DSCECO'],  65, 4));
							$this->aVar['peets4'] = floatval(substr($ecos['DSCECO'],  75, 4));
							$this->aVar['peets5'] = floatval(substr($ecos['DSCECO'],  85, 4));
							$this->aVar['peets6'] = floatval(substr($ecos['DSCECO'],  95, 4));
							$this->aVar['peets7'] = floatval(substr($ecos['DSCECO'], 105, 4));
							$this->aVar['peets8'] = floatval(substr($ecos['DSCECO'], 115, 4));
							break;
						case 3:
							$this->aVar['peepro'] = trim(substr($ecos['DSCECO'], 25, 4));
							$this->aVar['peetdr'] = floatval(substr($ecos['DSCECO'],  35, 4));
							$this->aVar['peetd1'] = floatval(substr($ecos['DSCECO'],  45, 4));
							$this->aVar['peetd2'] = floatval(substr($ecos['DSCECO'],  55, 4));
							$this->aVar['peetd3'] = floatval(substr($ecos['DSCECO'],  65, 4));
							$this->aVar['peetd4'] = floatval(substr($ecos['DSCECO'],  75, 4));
							$this->aVar['peetd5'] = floatval(substr($ecos['DSCECO'],  85, 4));
							$this->aVar['peetd6'] = floatval(substr($ecos['DSCECO'],  95, 4));
							$this->aVar['peetd7'] = floatval(substr($ecos['DSCECO'], 105, 4));
							$this->aVar['peetd8'] = floatval(substr($ecos['DSCECO'], 115, 4));
							break;
					}
					break;

				// POST-EJERCICIO
				case 24:
					switch(true){
						case $ecos['LINECO']==1:
							$this->aVar['pepfci'] = round(substr($ecos['DSCECO'], 30, 7).'.'.intval(substr($ecos['DSCECO'], 37, 2)));
							$this->aVar['pepfc3'] = round(substr($ecos['DSCECO'], 50, 7).'.'.intval(substr($ecos['DSCECO'], 57, 2)));
							$this->aVar['pepfc5'] = round(substr($ecos['DSCECO'], 70, 7).'.'.intval(substr($ecos['DSCECO'], 77, 2)));
							$this->aVar['pepfc8'] = round(substr($ecos['DSCECO'], 90, 7).'.'.intval(substr($ecos['DSCECO'], 97, 2)));
							break;
						case $ecos['LINECO']==2:
							$this->aVar['peptsi'] = round(substr($ecos['DSCECO'], 30, 7).'.'.intval(substr($ecos['DSCECO'], 37, 2)));
							$this->aVar['pepts3'] = round(substr($ecos['DSCECO'], 50, 7).'.'.intval(substr($ecos['DSCECO'], 57, 2)));
							$this->aVar['pepts5'] = round(substr($ecos['DSCECO'], 70, 7).'.'.intval(substr($ecos['DSCECO'], 77, 2)));
							$this->aVar['pepts8'] = round(substr($ecos['DSCECO'], 90, 7).'.'.intval(substr($ecos['DSCECO'], 97, 2)));
							break;
						case $ecos['LINECO']==3:
							$this->aVar['peptdi'] = round(substr($ecos['DSCECO'], 30, 7).'.'.intval(substr($ecos['DSCECO'], 37, 2)));
							$this->aVar['peptd3'] = round(substr($ecos['DSCECO'], 50, 7).'.'.intval(substr($ecos['DSCECO'], 57, 2)));
							$this->aVar['peptd5'] = round(substr($ecos['DSCECO'], 70, 7).'.'.intval(substr($ecos['DSCECO'], 77, 2)));
							$this->aVar['peptd8'] = round(substr($ecos['DSCECO'], 90, 7).'.'.intval(substr($ecos['DSCECO'], 97, 2)));
							break;
						case $ecos['LINECO']==4:
							$this->aVar['pepfe1'] = round(substr($ecos['DSCECO'], 30, 7).'.'.intval(substr($ecos['DSCECO'], 37, 2)));
							$this->aVar['pepfe2'] = round(substr($ecos['DSCECO'], 50, 7).'.'.intval(substr($ecos['DSCECO'], 57, 2)));
							$this->aVar['pepfcr'] = round(substr($ecos['DSCECO'], 70, 7).'.'.intval(substr($ecos['DSCECO'], 77, 2)));
							break;
						case $ecos['LINECO']==5:
							$this->aVar['peppor'] = round(substr($ecos['DSCECO'], 30, 7).'.'.intval(substr($ecos['DSCECO'], 37, 2)));
							$this->aVar['pepmet'] = round(substr($ecos['DSCECO'], 50, 7).'.'.intval(substr($ecos['DSCECO'], 57, 2)));
							$this->aVar['pepdpr'] = round(substr($ecos['DSCECO'], 70, 7).'.'.intval(substr($ecos['DSCECO'], 77, 2)));
							$this->aVar['pepcfu'] = trim(substr($ecos['DSCECO'], 90, 30));
							break;
						case $ecos['LINECO']>10:
							$this->aVar['peppsu'] .= substr($ecos['DSCECO'], 0, 120);
							break;
					}
					break;

				// CONCLUSIONES
				case 25:
					switch(true){
						case $ecos['LINECO']==1:
							$this->aVar['peccon'] = trim(substr($ecos['DSCECO'], 20, 2));
							break;
						case $ecos['LINECO']==5:
							$this->aVar['pecmre'] = trim(substr($ecos['DSCECO'], 26, 13));
							$this->aVar['pecmrz'] = trim(substr($ecos['DSCECO'], 50, 70));
							break;
						case $ecos['LINECO']>10:
							$this->aVar['pecobs'] .= substr($ecos['DSCECO'], 0, 120);
							break;
					}
					break;

			}

			if($ecos['INDECO']==21 && $ecos['LINECO']==1)
				$this->aVar['pegenu'] = trim(substr($ecos['DSCECO'],30,9));

		}

		// CALCULA SUPERFICIE CORPORAL
		$this->aVar['etgsco'] = $this->fnCalcSuperficieCorporal($this->aVar['etgpes'], $this->aVar['etgtal']);

		$laTrims=['etgdcl','peaaca','peatra','pearep','peaeje','peapej','peppsu','pecobs',];
		foreach($laTrims as $laTrim)
			$this->aVar[$laTrim] = trim($this->aVar[$laTrim]);

		if(empty($this->aVar['peaaca']) && empty($this->aVar['peatra']) && empty($this->aVar['pearep']) && empty($this->aVar['peaeje']) && empty($this->aVar['peapej'])){
			$this->aVar['peaaca'] = 'No Refiere';
			$this->aVar['peatra'] = 'Ninguno';
			$this->aVar['pearep'] = 'Ritmo sinusal, eje normal';
			$this->aVar['peaeje'] = 'Cambios no significativos del segmento ST, sin arritmias';
			$this->aVar['peapej'] = 'Cambios no significativos del segmento ST, sin arritmias';
		}

		if(!empty($this->aVar['peepro'])){
			foreach($this->aProtocolo as $laProt){
				if($laProt['CODPRO']==$this->aVar['peepro']){
					$this->aVar['peemer'] = $laProt['MREPRO'];
					$this->aVar['peeme1'] = $laProt['M01PRO'];
					$this->aVar['peeme2'] = $laProt['M02PRO'];
					$this->aVar['peeme3'] = $laProt['M03PRO'];
					$this->aVar['peeme4'] = $laProt['M04PRO'];
					$this->aVar['peeme5'] = $laProt['M05PRO'];
					$this->aVar['peeme6'] = $laProt['M06PRO'];
					$this->aVar['peeme7'] = $laProt['M07PRO'];
					$this->aVar['peeme8'] = $laProt['M08PRO'];
					$this->aVar['peever'] = $laProt['VREPRO'];
					$this->aVar['peeve1'] = $laProt['V01PRO'];
					$this->aVar['peeve2'] = $laProt['V02PRO'];
					$this->aVar['peeve3'] = $laProt['V03PRO'];
					$this->aVar['peeve4'] = $laProt['V04PRO'];
					$this->aVar['peeve5'] = $laProt['V05PRO'];
					$this->aVar['peeve6'] = $laProt['V06PRO'];
					$this->aVar['peeve7'] = $laProt['V07PRO'];
					$this->aVar['peeve8'] = $laProt['V08PRO'];
					$this->aVar['peeanr'] = $laProt['AREPRO'];
					$this->aVar['peean1'] = $laProt['A01PRO'];
					$this->aVar['peean2'] = $laProt['A02PRO'];
					$this->aVar['peean3'] = $laProt['A03PRO'];
					$this->aVar['peean4'] = $laProt['A04PRO'];
					$this->aVar['peean5'] = $laProt['A05PRO'];
					$this->aVar['peean6'] = $laProt['A06PRO'];
					$this->aVar['peean7'] = $laProt['A07PRO'];
					$this->aVar['peean8'] = $laProt['A08PRO'];
					break;
				}
			}
		}

		if(empty($this->aVar['pepfe1']) || empty($this->aVar['pepfe2'])){
			$this->aVar['pepfe1'] = 208 - ($this->nEdadA * 0.7);
			$this->aVar['pepfe1'] = intval($this->aVar['pepfe1'] * 0.85);
		}
	}


	/*
	 *	Consulta Prueba Farmacológica
	 */
	private function consulta_Prueba_Farmacologica($taData)
	{
		$this->consultarDatos($taData);

		$this->aVar['inftit'] = 'ECO DE STRESS CON PRUEBA FARMACOLOGICA - DOBUTAMINA';
		$this->aVar['radfec'] = $taData['tFechaHora'];
		$this->crearVar([
			'radmso','etepro','etepsu','eterps','ete05s','ete10s','ete20s','ete30s','ete40s','ete50s',
			'etercs','etcces','etcccl','etcmrv','etcmrz','infces', ], '');
		$this->crearVar([
			'etgale','etgasm','ete100','ete085','ete075','ete065','etefmr','etepor','etedpr','eterp1',
			'eterp2','eterp3','ete051','ete052','ete053','ete101','ete102','ete103','ete201','ete202',
			'ete203','ete301','ete302','ete303','ete401','ete402','ete403','ete501','ete502','ete503',
			'eterc1','eterc2','eterc3','etcprp','etcpbd','etcpdp','etcprc','eotpun','eotsub', ], 0);

		foreach($this->aData as $ecos){
			switch($ecos['INDECO']){

				// EVALUACION
				case 13:
					switch($ecos['LINECO']){
						case 1:
							$this->aVar['ete100'] = round(trim(substr($ecos['DSCECO'], 30,9))/100, 0); //floatval(substr($ecos['DSCECO'], 30,10));
							$this->aVar['ete085'] = round(trim(substr($ecos['DSCECO'], 50,9))/100, 0); //floatval(substr($ecos['DSCECO'], 50,10));
							$this->aVar['ete075'] = round(trim(substr($ecos['DSCECO'], 70,9))/100, 0); //floatval(substr($ecos['DSCECO'], 70,10));
							$this->aVar['ete065'] = round(trim(substr($ecos['DSCECO'], 90,9))/100, 0); //floatval(substr($ecos['DSCECO'], 90,10));
							$this->aVar['etefmr'] = round(trim(substr($ecos['DSCECO'],110,9))/100, 0); //floatval(substr($ecos['DSCECO'],110,10));
							break;
						case 2:
							$this->aVar['etepor'] = trim(substr($ecos['DSCECO'], 30,9))/100; //floatval(substr($ecos['DSCECO'], 30,10));
							$this->aVar['etedpr'] = trim(substr($ecos['DSCECO'], 50,9))/100; //floatval(substr($ecos['DSCECO'], 50,10));
							break;
						case 3:
							$this->aVar['etepsu'] = trim(substr($ecos['DSCECO'], 30,90));
							break;
						case 4:
							$this->aVar['etepro'] = trim(substr($ecos['DSCECO'], 30,90));
							break;
					}
					break;

				// EJERCICIO
				case 14:
					$laEje = ['','eterp','ete05','ete10','ete20','ete30','ete40','ete50','eterc'];
					$lnLin = $ecos['LINECO'];
					if($lnLin>0 && $lnLin<9){
						$this->aVar[$laEje[$lnLin].'1'] = trim(substr($ecos['DSCECO'], 30,9))/100;
						$this->aVar[$laEje[$lnLin].'2'] = trim(substr($ecos['DSCECO'], 45,9))/100;
						$this->aVar[$laEje[$lnLin].'3'] = trim(substr($ecos['DSCECO'], 60,9))/100;
						$this->aVar[$laEje[$lnLin].'s'] = trim(substr($ecos['DSCECO'], 70,50));
					}
					break;

				// CONCLUSIONES
				case 15:
					switch(true){
						case $ecos['LINECO']==1:
							$this->aVar['etcces'] = trim(substr($ecos['DSCECO'], 30,9));
							$this->aVar['etcprp'] = trim(substr($ecos['DSCECO'], 50,9))/100;
							$this->aVar['etcpbd'] = trim(substr($ecos['DSCECO'], 70,9))/100;
							$this->aVar['etcpdp'] = trim(substr($ecos['DSCECO'], 90,9))/100;
							$this->aVar['etcprc'] = trim(substr($ecos['DSCECO'],110,9))/100/100;
							break;
						case $ecos['LINECO']==5:
							$this->aVar['etcmrv'] = trim(substr($ecos['DSCECO'], 26,13));
							$this->aVar['etcmrz'] = trim(substr($ecos['DSCECO'], 50,70));
							break;
						case $ecos['LINECO']>10:
							$this->aVar['etcccl'] .= substr($ecos['DSCECO'],0,120);
							break;
					}
					break;


			}
		}

		if(empty($this->aVar['ete100'])){
			$this->aVar['ete100'] = 208 - ($this->nEdadA * 0.7);
			$this->aVar['ete085'] = ($this->aVar['ete100'] * 85) / 100;
			$this->aVar['ete075'] = ($this->aVar['ete100'] * 75) / 100;
			$this->aVar['ete065'] = ($this->aVar['ete100'] * 65) / 100;
			$this->aVar['etefmr'] = 0;
		}
		$this->aVar['etcccl'] = trim($this->aVar['etcccl']);
		if(!empty($this->aVar['etcces'])){
			$this->aVar['infces'] = $this->aCalidad[$this->aVar['etcces']];
		}
	}


	/*
	 *	Consulta Datos Farmacología
	 */
	private function consulta_Datos_Farmacologia($tnIngreso, $tnConsecCita, $tcCUP, $tnSubindice=0)
	{
		$laWhere = [
				'ingeco'=>$tnIngreso,
				'coneco'=>$tnConsecCita,
				'proeco'=>$tcCUP,
				'indeco'=>10,
			];
		if($tnSubindice>0)
			$laWhere['subeco']=$tnSubindice;

		$this->aFarm = $this->oDb
			->from('ECOS')
			->where($laWhere)
			->orderBy('SUBECO, LINECO')
			->getAll('array');

		if($tnSubindice>0)
			$this->datos_farm($tnSubindice);
	}


	/*
	 *	Consulta Datos Farmacología
	 */
	private function datos_Farm($tnSubindice)
	{
		$taTitFr = ['','REPOSO','BAJA DOSIS','DOSIS PICO','RECUPERACIÓN',];

		$this->aDatFarm = [];
		$this->aDatFarm['ttl'] = $taTitFr[$tnSubindice] ?? '';
		$this->aDatFarm['obs'] = '';
		//$this->aVar['dessfe']='';

		foreach($this->aFarm as $farm) {
			if($farm['SUBECO']==$tnSubindice){
				if(in_array($farm['LINECO'],[1,2,3])){
					for($lnJ=1; $lnJ<4; $lnJ++){
						$this->aDatFarm['dat'][$farm['LINECO']*2-1][$lnJ] = trim(substr($farm['DSCECO'], ($lnJ+1)*10+5, 4));
						$this->aDatFarm['dat'][$farm['LINECO']*2][$lnJ] = trim(substr($farm['DSCECO'], ($lnJ+1)*10+35, 4));
					}

				} elseif($farm['LINECO']>10){
					//$this->aVar['dessfe'] .= substr($farm['DSCECO'], 0, 120);
					$this->aDatFarm['obs'] .= substr($farm['DSCECO'], 0, 120);
				}
			}
		}
		//$this->aVar['dessfe'] = trim($this->aVar['dessfe']);
		$this->aDatFarm['obs'] = trim($this->aDatFarm['obs']);

		// Calcular Totales
		//$this->calcular_Puntaje()
		$this->aDatFarm['sum']['total'] = 0;
		if(isset($this->aDatFarm['dat'])){
			for($lnI=1; $lnI<7; $lnI++){
				$this->aDatFarm['sum'][$lnI] = 0;
				for($lnJ=1; $lnJ<4; $lnJ++){
					$this->aDatFarm['sum'][$lnI] += $this->aDatFarm['dat'][$lnI][$lnJ];
				}
				$this->aDatFarm['sum']['total'] += $this->aDatFarm['sum'][$lnI];
			}
		}
	}



	/******************************  ARREGLOS ESPECÍFICOS  ******************************/

	private function fnDiagnosticos($taData)
	{
		// Diagnósticos
		$this->aDiag = $this->oDb
			->from('RIPAPS')
			->where([
				'INGAPS'=>$taData['nIngreso'],
				'CSCAPS'=>$taData['nConsecCita'],
				])
			//->getAll('array');
			->get('array');
	}

	private function fnEcoTransEsofag()
	{
		if(count($this->aEcoTransEsofag)>0) return;
		$laTemps = $this->oDb
			->select('TABDSC AS DESCRP, SUBSTR(TABCOD,2,2) AS CODIGO')
			->from('PRMTAB04')
			->where('TABTIP=\'TDX\' AND TABCOD LIKE \'G%\'')
			->getAll('array');
		foreach($laTemps as $laTemp) {
			$laTemp=array_map('trim',$laTemp);
			$this->aEcoTransEsofag[$laTemp['CODIGO']]=$laTemp['DESCRP'];
		}
	}

	private function fnPruebaEsfuerzo()
	{
		if(count($this->aPruebaEsfuerzo)>0) return;
		$laTemps = $this->oDb
			->select('TABDSC AS DESCRP, SUBSTR(TABCOD,2,2) AS CODIGO')
			->from('PRMTAB02')
			->where('TABTIP=\'TDX\' AND tabcod LIKE \'J%\'')
			->orderBy('TABDSC')
			->getAll('array');
		foreach($laTemps as $laTemp) {
			$laTemp=array_map('trim',$laTemp);
			$this->aPruebaEsfuerzo[$laTemp['CODIGO']]=$laTemp['DESCRP'];
		}
	}

	private function fnEcoStress()
	{
		if(count($this->aEcoStress)>0) return;
		$laTemps = $this->oDb
			->select('TABDSC AS DESCRP, SUBSTR(TABCOD,2,2) AS CODIGO')
			->from('PRMTAB04')
			->where('TABTIP=\'TDX\' AND tabcod LIKE \'I%\'')
			->orderBy('TABDSC')
			->getAll('array');
		foreach($laTemps as $laTemp) {
			$laTemp=array_map('trim',$laTemp);
			$this->aEcoStress[$laTemp['CODIGO']]=$laTemp['DESCRP'];
		}
	}

	private function fnApreciacion()
	{
		if(count($this->aApreciacion)>0) return;
		$laTemps = $this->oDb
			->select('SUBSTR(DE1TMA,1,30) AS DESCRP, SUBSTR(CL1TMA,1,2) AS CODIGO')
			->from('TABMAE')
			->where('TIPTMA=\'CNIAPS\' AND esttma<>\'1\'')
			->getAll('array');
		foreach($laTemps as $laTemp) {
			$laTemp=array_map('trim',$laTemp);
			$this->aApreciacion[$laTemp['CODIGO']]=$laTemp['DESCRP'];
		}
	}

	private function fnConclusiones()
	{
		//if(count($this->aConclusiones)>0) return;
		$lcFiltro='CL4TMA'.( in_array($this->cReporte,['CNI014','CNI022'])?'=':'<>' ).'\'CNI014\'';
		$laConclusiones = $this->oDb->distinct()
			->select('INT(CL2TMA) CL2TMA,INT(CL3TMA) CL3TMA,CL4TMA,DE1TMA,DE2TMA,OP5TMA')
			->from('TABMAE')
			->where('TIPTMA=\'CNIPRMT\' AND CL1TMA=\'CNICON\' AND CL2TMA<>\'\' AND ESTTMA<>\'1\' AND '.$lcFiltro)
			->orderBy('INT(CL2TMA), INT(CL3TMA)')
			->getAll('array');
		foreach($laConclusiones as $laCncl) {
			$laCncl = array_map('trim',$laCncl);
			if (isset($this->aConclusiones[$laCncl['CL2TMA']])) {
				$this->aConclusiones[$laCncl['CL2TMA']]['TEXCON'].=$laCncl['DE2TMA'].$laCncl['OP5TMA'];
			} else {
				$this->aConclusiones[$laCncl['CL2TMA']]=[
					'DESCON'=>$laCncl['DE1TMA'],
					'CODCON'=>$laCncl['CL2TMA'],
					'PRGCON'=>$laCncl['CL4TMA'],
					'TEXCON'=>$laCncl['DE2TMA'].$laCncl['OP5TMA'],
				];
			}
		}
	}

	private function fnProtocolo()
	{
		if(count($this->aProtocolo)>0) return;
		$laProtocolo = $this->oDb
			->select([
				'SUBSTR(DE1TMA, 1, 20) DESPRO',
				'SUBSTR(CL1TMA, 1, 1) CODPRO',
				'DEC(CL2TMA, 1, 0) NUMPRO',
				'DEC(CL3TMA, 4, 1, \'.\') MREPRO',
				'DEC(SUBSTR(DE2TMA,   1, 8), 4, 1, \'.\') M01PRO',
				'DEC(SUBSTR(DE2TMA,   9, 8), 4, 1, \'.\') M02PRO',
				'DEC(SUBSTR(DE2TMA,  17, 8), 4, 1, \'.\') M03PRO',
				'DEC(SUBSTR(DE2TMA,  25, 8), 4, 1, \'.\') M04PRO',
				'DEC(SUBSTR(DE2TMA,  33, 8), 4, 1, \'.\') M05PRO',
				'DEC(SUBSTR(DE2TMA,  41, 8), 4, 1, \'.\') M06PRO',
				'DEC(SUBSTR(DE2TMA,  49, 8), 4, 1, \'.\') M07PRO',
				'DEC(SUBSTR(DE2TMA,  57, 8), 4, 1, \'.\') M08PRO',
				'DEC(SUBSTR(DE2TMA,  65, 8), 4, 1, \'.\') VREPRO',
				'DEC(SUBSTR(DE2TMA,  73, 8), 4, 1, \'.\') V01PRO',
				'DEC(SUBSTR(DE2TMA,  81, 8), 4, 1, \'.\') V02PRO',
				'DEC(SUBSTR(DE2TMA,  89, 8), 4, 1, \'.\') V03PRO',
				'DEC(SUBSTR(DE2TMA,  97, 8), 4, 1, \'.\') V04PRO',
				'DEC(SUBSTR(DE2TMA, 105, 8), 4, 1, \'.\') V05PRO',
				'DEC(SUBSTR(DE2TMA, 113, 8), 4, 1, \'.\') V06PRO',
				'DEC(SUBSTR(DE2TMA, 121, 8), 4, 1, \'.\') V07PRO',
				'DEC(SUBSTR(DE2TMA, 129, 8), 4, 1, \'.\') V08PRO',
				'DEC(SUBSTR(DE2TMA, 137, 8), 4, 1, \'.\') AREPRO',
				'DEC(SUBSTR(DE2TMA, 145, 8), 4, 1, \'.\') A01PRO',
				'DEC(SUBSTR(DE2TMA, 153, 8), 4, 1, \'.\') A02PRO',
				'DEC(SUBSTR(DE2TMA, 161, 8), 4, 1, \'.\') A03PRO',
				'DEC(SUBSTR(DE2TMA, 169, 8), 4, 1, \'.\') A04PRO',
				'DEC(SUBSTR(DE2TMA, 177, 8), 4, 1, \'.\') A05PRO',
				'DEC(SUBSTR(DE2TMA, 185, 8), 4, 1, \'.\') A06PRO',
				'DEC(SUBSTR(DE2TMA, 193, 8), 4, 1, \'.\') A07PRO',
				'DEC(SUBSTR(DE2TMA, 201, 8), 4, 1, \'.\') A08PRO',
			])
			->from('TABMAE')
			->where('TIPTMA=\'CNIPRO\'')
			->orderBy('DE1TMA')
			->getAll('array');
		foreach($laProtocolo as $laProt)
			$this->aProtocolo[$laProt['CODPRO']] = $laProt;
	}

	private function fnCalidad()
	{
		$this->aCalidad = [
			1 => 'Mala',
			2 => 'Regular',
			3 => 'Aceptable',
			4 => 'Excelente',
		];
	}

	private function fnEquipos()
	{
		if(count($this->aEquipos)>0) return;
		$laTemps = $this->oDb
			->select('SUBSTR(DE1TMA,1,50) AS DESCRP, SUBSTR(CL1TMA,1,2) AS CODIGO')
			->from('TABMAE')
			->where('TIPTMA=\'CNIEQU\' AND esttma<>\'1\'')
			->getAll('array');
		foreach($laTemps as $laTemp) {
			$laTemp=array_map('trim',$laTemp);
			$this->aEquipos[$laTemp['CODIGO']]=$laTemp['DESCRP'];
		}
	}

	private function fnValEquipos()
	{
		if(count($this->aValEquipos)>0) return;
		$laTemps = $this->oDb
			->select('SUBSTR(DE1TMA,1,20) AS DESCRP, SUBSTR(CL1TMA,1,2) AS CODIGO')
			->from('TABMAE')
			->where('TIPTMA=\'CNITRA\' AND esttma<>\'1\'')
			->getAll('array');
		foreach($laTemps as $laTemp) {
			$laTemp=array_map('trim',$laTemp);
			$this->aValEquipos[$laTemp['CODIGO']]=$laTemp['DESCRP'];
		}
	}

	private function fnValNormales()
	{
		if(count($this->aValNormales)>0) return;
		$this->aValNormales = $this->oDb
			->select([
					'SUBSTR(CL1TMA, 1, 2) AS CODSDN',
					'DEC(TRIM(CL2TMA), 4, 1, \'.\') AS EDNSDN',
					'DEC(TRIM(CL3TMA), 4, 1, \'.\') AS EDXSDN',
					'DEC(TRIM(CL4TMA), 4, 1, \'.\') AS AI1SDN',
					'DEC(SUBSTR(DE2TMA,  1,8), 4, 1, \'.\') AS AI2SDN',
					'DEC(SUBSTR(DE2TMA,  9,8), 4, 1, \'.\') AS RA1SDN',
					'DEC(SUBSTR(DE2TMA, 17,8), 4, 1, \'.\') AS RA2SDN',
					'DEC(SUBSTR(DE2TMA, 25,8), 4, 1, \'.\') AS VA1SDN',
					'DEC(SUBSTR(DE2TMA, 33,8), 4, 1, \'.\') AS VA2SDN',
					'DEC(SUBSTR(DE2TMA, 41,8), 4, 1, \'.\') AS IS1SDN',
					'DEC(SUBSTR(DE2TMA, 49,8), 4, 1, \'.\') AS IS2SDN',
					'DEC(SUBSTR(DE2TMA, 57,8), 4, 1, \'.\') AS ID1SDN',
					'DEC(SUBSTR(DE2TMA, 65,8), 4, 1, \'.\') AS ID2SDN',
					'DEC(SUBSTR(DE2TMA, 73,8), 4, 1, \'.\') AS SI1SDN',
					'DEC(SUBSTR(DE2TMA, 81,8), 4, 1, \'.\') AS SI2SDN',
					'DEC(SUBSTR(DE2TMA, 89,8), 4, 1, \'.\') AS PP1SDN',
					'DEC(SUBSTR(DE2TMA, 97,8), 4, 1, \'.\') AS PP2SDN',
					'DEC(SUBSTR(DE2TMA,105,8), 4, 1, \'.\') AS DD1SDN',
					'DEC(SUBSTR(DE2TMA,113,8), 4, 1, \'.\') AS DD2SDN',
					'DEC(SUBSTR(DE2TMA,121,8), 4, 1, \'.\') AS FE1SDN',
					'DEC(SUBSTR(DE2TMA,129,8), 4, 1, \'.\') AS FE2SDN',
					'DEC(SUBSTR(DE2TMA,137,8), 4, 1, \'.\') AS CA1SDN',
					'DEC(SUBSTR(DE2TMA,145,8), 4, 1, \'.\') AS CA2SDN',
				])
			->from('TABMAE')
			->where('TIPTMA=\'CNISDN\'')
			->orderBy('DE1TMA')
			->getAll('array');
	}

	/******************************  OTRAS FUNCIONES  ******************************/

	/*
	 *	Consulta y retorna la descripción del equipo utilizado
	 */
	private function fnObtenerEquipo($tnCodigoEqu=0)
	{
		$oTabmae = $this->oDb->ObtenerTabMae('DE1TMA', 'CNIEQU', ['CL1TMA'=>$tnCodigoEqu,'ESTTMA'=>'']);
		return trim(AplicacionFunciones::getValue($oTabmae, 'DE1TMA', ''));
	}

	/*
	 *	Calcular la superficie corporal
	 *	@param $tnPeso: número, peso en kg
	 *	@param $tnTalla: número, talla en cm
	 *	@return número, superficie corporal o cero si no se puede calcular
	 */
	private function fnCalcSuperficieCorporal($tnPeso=0, $tnTalla=0)
	{
		return ($tnPeso>0 && $tnTalla>0) ? sqrt($tnPeso * $tnTalla / 3600) : 0;
	}

	/*
	 *	Crea o establece valores a variables en $this->aVar
	 *	@param $taVars: array, lista de variables
	 *	@param $tuValor: valor que deben tomar las variables
	 */
	private function crearVar($taVars=[], $tuValor=null)
	{
		foreach($taVars as $tcVar)
			$this->aVar[$tcVar] = $tuValor;
	}

}
