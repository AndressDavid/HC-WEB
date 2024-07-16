<?php
namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';
require_once __DIR__ . '/class.FormulacionParametros.php';
require_once __DIR__ . '/class.AplicacionFunciones.php';
use NUCLEO\FormulacionParametros;
use NUCLEO\AplicacionFunciones;


class ConsultasEnfermeria
{
	protected $oDb;
	protected $aParAdminMed = [];

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}

	/*
	 *	Obtener parámetros para la administración de medicamentos
	 */
	public function obtenerParamAdministraMedicamentos()
	{
		$loOM = new FormulacionParametros();
		$loOM->obtenerParametrosTodos();
		$this->aParAdminMed = [
			'UniDosis'	=> $loOM->unidadesDosis(),
			'Frecuencia'=> $loOM->frecuencias(),
			'ViaAdm'	=> $loOM->viasAdmin(),
		];
		unset($loOM);
	}

	/*
	 *	Fecha hora de ingreso a la sección donde estaba en la fecha hora indicada
	 *	@param entero $tnIngreso: número de ingreso
	 *	@param entero $tnFecha: fecha AAAAMMDD, si fecha es false toma la fecha del sistema
	 *	@param entero $tnHora: hora HHMMSS, si hora es false toma las 080000
	 *	@return string con la descripción del soporte nutricional administrado
	 */
	public function ingresoSeccion($tnIngreso, $tnFecha=false, $tnHora=false)
	{
		$laSeccion = [];
		$tnFecha = $this->setFecha($tnFecha);
		if ($tnHora===false) $tnHora=80000;
		$lnFecHor = $tnFecha*1000000+$tnHora;
		$laDatos = $this->oDb
			->select('STREPC SECC, NTREPC HAB, FEIEPC FECINI, HOIEPC HORINI, FEFEPC FECFIN, HOFEPC HORFIN')
			->from('RIAEPC')
			->where(['INGEPC'=>$tnIngreso])
			->where("FEIEPC*1000000+HOIEPC <= $lnFecHor")
			->notWhere('STREPC=\'TU\'')
			->orderBy('FEIEPC DESC, HOIEPC DESC')
			->getAll('array');
		$lnNumRows = $this->oDb->numRows();
		if ($lnNumRows > 0){
			$laSeccion['seccion'] = $laDatos[0]['SECC']; // Sección Actual
			foreach ($laDatos as $laDato) {
				$lnFecha = $laDato['FECINI'];
				$lnHora  = $laDato['HORINI'];
				if ($laSeccion['seccion']!==$laDato['SECC']) {
					$laSeccion['fechaHora'] = AplicacionFunciones::formatFechaHora('fechahora', $lnFecha*1000000+$lnHora);
					break;
				}
			}
			if (!isset($laSeccion['fechaHora'])) {
				$lnNumRows = $lnNumRows - 1;
				$laSeccion['fechaHora'] = AplicacionFunciones::formatFechaHora('fechahora', $laDatos[$lnNumRows]['FECFIN']*1000000+$laDatos[$laDatos[$lnNumRows]]['HORFIN']);
			}
		}
		return $laSeccion;
	}

	/*
	 *	Administración de medicamentos de soporte nutricional para un ingreso - fecha
	 *	@param entero $tnIngreso: número de ingreso
	 *	@param entero $tnFecha: fecha AAAAMMDD
	 *	@return string con la descripción del soporte nutricional administrado
	 */
	public function soporteNutricional($tnIngreso, $tnFecha=false)
	{
		if (count($this->aParAdminMed)==0) $this->obtenerParamAdministraMedicamentos();
		$tnFecha = $this->setFecha($tnFecha);
		$lcSopNut = '';
		$laSopNut = $this->oDb
			->select('I.REFDES, I.DESDES, A.*')
			->from('ENADMMD A')
			->innerJoin('INVDES I', 'A.MEDADM=I.REFDES')
			->where(['A.INGADM'=>$tnIngreso, 'A.FEAADM'=>$tnFecha]) // A.HDAADM
			->where("A.MEDADM IN (SELECT REFDES FROM INVATTR WHERE SUBSTR(GRUDES,1,6)='210401')")
			->getAll('array');
		if ($this->oDb->numRows()>0){
			$lcSopNut = '<ul>';
			foreach ($laSopNut as &$laNut) {
				$laNut = array_map('trim',$laNut);
				$lcUnDosis = $this->aParAdminMed['UniDosis'][$laNut['DDOADM']]['abrv']??'';
				if(empty($lcUnDosis)) $lcUnDosis = $this->aParAdminMed['UniDosis'][$laNut['DDOADM']]['desc']??'';
				$lcFrec = $this->aParAdminMed['Frecuencia'][$laNut['DFRADM']]['desc'] ?? '';
				$lcVia = $this->aParAdminMed['ViaAdm']['0'.$laNut['VIAADM']];
				$lcSopNut .= "<li><b>{$laNut['REFDES']} - {$laNut['DESDES']}</b>: {$laNut['DOSADM']}{$lcUnDosis} cada {$laNut['FREADM']} {$lcFrec} vía {$lcVia}</li>";
			}
			unset($laNut);
			$lcSopNut .= '</ul>';
		}
		return $lcSopNut;
	}

	/*
	 *	Valoración de la escala de riesgo de sangrado HASBLED
	 *	@param entero $tnIngreso: número de ingreso
	 *	@return string con la descripción de HASBLED si el paciente la tiene
	 */
	public function escalaHasbled($tnIngreso, $tnFecha=false)
	{
		$tnFecha = $this->setFecha($tnFecha);
		$lcHasbled = '';
		$laHasbled = $this->oDb
			->select('OP5EHC')
			->from('ESCHCL')
			->where(['INGEHC'=>$tnIngreso])
			->where('FECEHC', '<=', $tnFecha)
			->where('INDEHC=1 AND IN2EHC=1')
			->orderBy('FECEHC DESC, HOREHC DESC')
			->get('array');
		if ($this->oDb->numRows()>0) {
			$lcHasbled = trim($laHasbled['OP5EHC']);
		}
		return $lcHasbled;
	}

	/*
	 *	Valoración de la escala de riesgo de enfermedad tromboembólica CAPRINI valorada en PreAnestesia
	 *	@param entero $tnIngreso: número de ingreso
	 *	@param boolean $tbSoloPuntajeRiesgo: si es true retorna solo Puntaje y Riesgo
	 *	@return string con la descripción de CAPRINI si el paciente la tiene
	 */
	public function escalaCaprini($tnIngreso, $tbSoloPuntajeRiesgo=false, $tnFecha=false)
	{
		$tnFecha = $this->setFecha($tnFecha);
		$lcCaprini = $lcRiesgosAnest = '';
		$laRiesgosAnest = $this->oDb
			->select('A.DESVAN')
			->from('ANEVAL A')
			->where(['A.INGVAN'=>$tnIngreso])
			->where('IN1VAN=90 AND IN2VAN=2 AND A.FECVAN*1000000+A.HORVAN=(SELECT MAX(M.FECVAN*1000000+M.HORVAN) FROM ANEVAL M WHERE M.INGVAN=A.INGVAN AND M.FECVAN<='.$tnFecha.')')
			->orderBy('CNLVAN')
			->getAll('array');
		if ($this->oDb->numRows()>0) {
			foreach ($laRiesgosAnest as $laRiesgo) {
				$lcRiesgosAnest .= $laRiesgo['DESVAN'];
			}
		}
		$lcRiesgosAnest = trim($lcRiesgosAnest);
		if (mb_strlen($lcRiesgosAnest, 'UTF-8')>0) {
			$laRiesgosAnest = explode('~',$lcRiesgosAnest);
			foreach ($laRiesgosAnest as $lcRiesgo) {
				if ($tbSoloPuntajeRiesgo) {
					if (mb_substr($lcRiesgo, 0, 9, 'UTF-8')=='CAPRINIH:') {
						$lcJSON = str_replace('¤',':',mb_substr($lcRiesgo, 9, null, 'UTF-8'));
						$laRiesgoEnfTrEm = json_decode($lcJSON, true);
						$lcCaprini = "Puntaje: {$laRiesgoEnfTrEm['Puntaje']} - Riesgo: {$laRiesgoEnfTrEm['Riesgo']}";
						break;
					}
				} else {
					if (mb_substr($lcRiesgo, 0, 8, 'UTF-8')=='CAPRINI:') {
						$lcCaprini = mb_substr($lcRiesgo, 8, null, 'UTF-8');
						break;
					}
				}
			}
		}
		return $lcCaprini;
	}

	/*
	 *	Obtener última valoración de estado reportada en notas de enfermería
	 *	@param entero $tnIngreso: número de ingreso
	 *	@return array Última valoración de estado
	 */
	public function Estado($tnIngreso, $tnFecha=false)
	{
		$tnFecha = $this->setFecha($tnFecha);
		$laEstado = [];
		$laDatos = $this->oDb
			->select('CONNEU, CTUNEU, ECONEU, GCVNEU, CAMNEU, RICNEU, PESNEU, TIPPES, TALNEU, OBSNEU, USRNEU, FECNEU, HORNEU')
			->from('ENNEUR')
			->where(['INGNEU'=>$tnIngreso])
			->where('FECNEU','<=',$tnFecha)
			->orderBy('CTUNEU DESC')
			->get('array');
		if ($this->oDb->numRows()>0) {
			$laDatos = array_map('trim', $laDatos);
			$laEstado = [
				'ConsNota'				=> $laDatos['CONNEU'],
				'ConsReg'				=> $laDatos['CTUNEU'],
				'Estado Conciencia'		=> $laDatos['ECONEU'],
				'Sueño'					=> $laDatos['GCVNEU'],
				'Talla'					=> $laDatos['TALNEU'].' cm',
				'Peso real'				=> $laDatos['PESNEU'].' '.$laDatos['TIPPES'],
				'Escala RICHMOND'		=> $laDatos['RICNEU'],
				'Escala CAM-ICU'		=> $laDatos['CAMNEU'],
				'Observaciones'			=> $laDatos['OBSNEU'],
				'Usuario'				=> $laDatos['USRNEU'],
				'Fecha'					=> $laDatos['FECNEU'],
				'Hora'					=> $laDatos['HORNEU'],
			];
		}
		return $laEstado;
	}

	/*
	 *	Obtener última valoración de control respiratorio reportada en notas de enfermería
	 *	@param entero $tnIngreso: número de ingreso
	 *	@return array Última valoración de control respiratorio
	 */
	public function Respiratorio($tnIngreso, $tnFecha=false)
	{
		$tnFecha = $this->setFecha($tnFecha);
		$laRespira = [];
		$laDatos = $this->oDb
			->select('CONRES, CTURES, CPLRES, OXIRES, RSARES, TRPRES, CUIRES, PORRES, OBSRES, USRRES, FECRES, HORRES')
			->from('ENRESP')
			->where(['INGRES'=>$tnIngreso])
			->where('FECRES','<=',$tnFecha)
			->orderBy('CTURES DESC')
			->get('array');
		if ($this->oDb->numRows()>0) {
			$laDatos = array_map('trim', $laDatos);
			$laRespira = [
				'ConsNota'				=> $laDatos['CONRES'],
				'ConsReg'				=> $laDatos['CTURES'],
				'Coloración Piel'		=> $laDatos['CPLRES'],
				'Ruidos Sobreagregados'	=> $laDatos['RSARES'],
				'Cuidado Traqueostomia'	=> $laDatos['CUIRES'],
				'Oxigenoterapia'		=> $laDatos['OXIRES'],
				'% Oxigeno'				=> $laDatos['PORRES'],
				'Tipo de Respiración'	=> $laDatos['TRPRES'],
				'Observaciones'			=> $laDatos['OBSRES'],
				'Usuario'				=> $laDatos['USRRES'],
				'Fecha'					=> $laDatos['FECRES'],
				'Hora'					=> $laDatos['HORRES'],
			];
		}
		return $laRespira;
	}

	/*
	 *	Obtener última valoración de signos reportada en notas de enfermería
	 *	@param entero $tnIngreso: número de ingreso
	 *	@return array Última valoración de signos vitales
	 */
	public function SignosMonitor($tnIngreso, $tcUsuario, $tnFecha=false) : array
	{
		$tnFecha = $this->setFecha($tnFecha);
		$laDatos = $this->oDb
			->select('CONSIG, CTUSIG, TMPSIG, FRCSIG, FRRSIG, SATSIG, TASSIG, OT2SIG, TADSIG, PRASIG, PCFSIG, OBSSIG, USRSIG, FDISIG, HDISIG, FECSIG, HORSIG, ESTSIG')
			->from('ENSIGN')
			->where(['INGSIG'=> $tnIngreso])
			->where(['USRSIG'=> $tcUsuario])
			->orderBy('FECSIG DESC, HORSIG DESC')
			->get('array');				
		$laDatos = is_array($laDatos) ? $laDatos : [];
		return $laDatos;
	}

	/*
	 *	Obtener última valoración de heridas reportada en notas de enfermería
	 *	@param entero $tnIngreso: número de ingreso
	 *	@return array Última valoración de heridas
	 */
	public function Heridas($tnIngreso, $tnFecha=false)
	{
		$tnFecha = $this->setFecha($tnFecha);
		$laHeridas = [];
		$laDatos = $this->oDb
			->select('CONHER, CNTHER, CLAHER, COLHER, ASPHER, CURHER, OBSHER, USRHER, FECHER, HORHER')
			->from('ENHERI')
			->where(['INGHER'=>$tnIngreso])
			->where('FECHER','<=',$tnFecha)
			->orderBy('CNTHER DESC')
			->get('array');
		if ($this->oDb->numRows()>0) {
			$laDatos = array_map('trim', $laDatos);
			$laHeridas = [
				'ConsNota'			=> $laDatos['CONHER'],
				'ConsReg'			=> $laDatos['CNTHER'],
				'Clase de Herida'	=> $laDatos['CLAHER'],
				'Localización'		=> $laDatos['COLHER'],
				'Aspecto'			=> $laDatos['ASPHER'],
				'Curación'			=> $laDatos['CURHER'],
				'Observaciones'		=> $laDatos['OBSHER'],
				'Usuario'			=> $laDatos['USRHER'],
				'Fecha'				=> $laDatos['FECHER'],
				'Hora'				=> $laDatos['HORHER'],
			];
		}
		return $laHeridas;
	}

	/*
	 *	Obtener última valoración de piel reportada en notas de enfermería
	 *	@param entero $tnIngreso: número de ingreso
	 *	@return array Última valoración de piel
	 */
	public function Piel($tnIngreso, $tnFecha=false)
	{
		$tnFecha = $this->setFecha($tnFecha);
		$laPiel = [];
		$laDatos = $this->oDb
			->select('CONPIE, CNTPIE, ESTPIE, OBSPIE, OB2PIE, USRPIE, FECPIE, HORPIE')
			->from('ENPIEL')
			->where(['INGPIE'=>$tnIngreso])
			->where('FECPIE','<=',$tnFecha)
			->orderBy('CNTPIE DESC')
			->get('array');
		if ($this->oDb->numRows()>0) {
			$laDatos = array_map('trim', $laDatos);
			$laPiel = [
				'ConsNota'		=> $laDatos['CONPIE'],
				'ConsReg'		=> $laDatos['CNTPIE'],
				'Estado'		=> $laDatos['ESTPIE'],
				'Descripción'	=> $laDatos['OBSPIE'],
				'Observaciones'	=> $laDatos['OB2PIE'],
				'Usuario'		=> $laDatos['USRPIE'],
				'Fecha'			=> $laDatos['FECPIE'],
				'Hora'			=> $laDatos['HORPIE'],
			];
		}
		return $laPiel;
	}

	/*
	 *	Obtener últimos registros de cateter reportados en notas de enfermería
	 *	@param entero $tnIngreso: número de ingreso
	 *	@return array Últimos registros de cateter
	 */
	public function Cateter($tnIngreso, $tnFecha=false)
	{
		$tnFecha = $this->setFecha($tnFecha);
		$laCateter = [];
		$laDatos = $this->oDb
			->select('E.CONCAT, E.CTUCAT, E.TIPCAT, E.CATCAT, E.LOCCAT, E.ASPCAT, E.CAMCAT, E.CURCAT, E.FCMCAT, E.FCRCAT, E.OBSCAT, E.OB1CAT, E.EVACAT, E.TO1CAT, E.TO2CAT, E.CALCAT, E.USRCAT, E.FECCAT, E.HORCAT')
			->from('ENCATE E')
			->where(['E.INGCAT'=>$tnIngreso])
			//->where('E.CTUCAT=(SELECT MAX(M.CTUCAT) FROM ENCATE M WHERE M.INGCAT=E.INGCAT)')
			->where('E.FECCAT*1000000+E.HORCAT=(SELECT MAX(M.FECCAT*1000000+M.HORCAT) FROM ENCATE M WHERE M.INGCAT=E.INGCAT AND M.FECCAT<='.$tnFecha.')')
			->getAll('array');

		if ($this->oDb->numRows()>0) {
			$laDato = array_map('trim', $laDatos[0]);
			$laCateter = [
				'ConsNota'		=> $laDato['CONCAT'],
				'ConsReg'		=> $laDato['CTUCAT'],
				'Cateter'		=> [],
				'Usuario'		=> $laDato['USRCAT'],
				'Fecha'			=> $laDato['FECCAT'],
				'Hora'			=> $laDato['HORCAT'],
			];
			foreach ($laDatos as $laDato) {
				$laDato = array_map('trim', $laDato);
				$laValoracion = [];
				if(!empty($laDato['EVACAT'])) {
					$laKey = ['Inserción', 'Fijación'];
					$laValoracion = [
						'Inserción'=>[
							'Total' => $laDato['TO1CAT'],
							'Preguntas' => [],
						],
						'Fijación'=>[
							'Total' => $laDato['TO2CAT'],
							'Preguntas' => [],
						],
					];
					$laValoras = explode('~', $laDato['EVACAT']);
					$laObservs = explode('~', $laDato['OB1CAT']);
					foreach ($laValoras as $lnKey => $laValora) {
						$laPregs = explode('¤', $laValora);
						foreach ($laPregs as $laPreg) {
							$lcValor = substr($laPreg,5,1);
							$laValoracion[$laKey[$lnKey]]['Preguntas'][substr($laPreg,0,4)] = [
								'Valor' => $lcValor=='1' ? 'SI' : ($lcValor=='2' ? 'NO' : ''),
							];
						}
					}
					foreach ($laObservs as $lnKey => $laObserv) {
						$laPregs = explode('¤', $laObserv);
						foreach ($laPregs as $laPreg) {
							$laValoracion[$laKey[$lnKey]]['Preguntas'][mb_substr($laPreg,0,4,'UTF-8')]['Observacion'] = mb_substr($laPreg,5,null,'UTF-8');
						}
					}
				}

				$laCateter['Cateter'][] = [
					'Tipo de Cateter'		=> $laDato['CATCAT'],
					'Calibre'				=> $laDato['CALCAT'],
					'Localización'			=> $laDato['LOCCAT'],
					'Aspecto Sitio Punción'	=> $laDato['ASPCAT'],
					'Cambio Cateter'		=> $laDato['CAMCAT'],
					'Fecha Cambio Cateter'	=> $laDato['FCMCAT'],
					'Curación'				=> $laDato['CURCAT'],
					'Fecha Curación'		=> $laDato['FCRCAT'],
					'Observaciones'			=> $laDato['OBSCAT'],
					'Valoraciones'			=> $laValoracion,
				];
			}
		}
		return $laCateter;
	}

	/*
	 *	Obtener último registro de higiene reportado en notas de enfermería
	 *	@param entero $tnIngreso: número de ingreso
	 *	@return array Último registro de higiene
	 */
	public function Higiene($tnIngreso, $tnFecha=false)
	{
		$tnFecha = $this->setFecha($tnFecha);
		$laHigiene = [];
		$laDatos = $this->oDb
			->select('CONHIG, CNTHIG, BANHIG, ORAHIG, MANHIG, OBSHIG, USRHIG, FECHIG, HORHIG')
			->from('ENHIGI')
			->where(['INGHIG'=>$tnIngreso])
			->where('FECHIG','<=',$tnFecha)
			->orderBy('CNTHIG DESC')
			->get('array');
		if ($this->oDb->numRows()>0) {
			$laDatos = array_map('trim', $laDatos);
			$laHigiene = [
				'ConsNota'				=> $laDatos['CONHIG'],
				'ConsReg'				=> $laDatos['CNTHIG'],
				'Tipo de Baño'			=> $laDatos['BANHIG'],
				'Higiene Oral'			=> $laDatos['ORAHIG'],
				'Medias Antiembólicas'	=> $laDatos['MANHIG'],
				'Observaciones'			=> $laDatos['OBSHIG'],
				'Usuario'				=> $laDatos['USRHIG'],
				'Fecha'					=> $laDatos['FECHIG'],
				'Hora'					=> $laDatos['HORHIG'],
			];
		}
		return $laHigiene;
	}

	/*
	 *	Obtener último registro de seguridad guardado por enfermería
	 *	@param entero $tnIngreso: número de ingreso
	 *	@return array Último registro de seguridad
	 */
	public function Seguridad($tnIngreso, $tnFecha=false)
	{
		$tnFecha = $this->setFecha($tnFecha);
		$laSeguridad = [];
		$laDatos = $this->oDb
			->select('CONSEG, CTUSEG, BARSEG, TIMSEG, COPSEG, MIDSEG, MPASEG, CAMSEG, RSASEG, RFUSEG, ERGSEG, CLNSEG, EBRSEG, VBRSEG, MSGSEG, AISSEG, OBSSEG, USRSEG, FECSEG, HORSEG')
			->from('ENSEGU')
			->where(['INGSEG'=>$tnIngreso])
			->where('FECSEG','<=',$tnFecha)
			->orderBy('CTUSEG DESC')
			->get('array');
		if ($this->oDb->numRows()>0) {
			$laDatos = array_map('trim', $laDatos);
			$laSeguridad = [
				'ConsNota'					=> $laDatos['CONSEG'],
				'ConsReg'					=> $laDatos['CTUSEG'],
				'Tipo de Aislamiento'		=> $laDatos['AISSEG'],
				'Seguridad del Paciente'	=> [
					'Barandas Lateral Cama'		=> $laDatos['BARSEG'],
					'Timbre a la Mano'			=> $laDatos['TIMSEG'],
					'Compañia Permanente'		=> $laDatos['COPSEG'],
					'Cama Altura Minima'		=> $laDatos['CAMSEG'],
					'Medidas de Sujeción'		=> $laDatos['MSGSEG'],
					'Manilla de Identificación'	=> $laDatos['MIDSEG'],
					'Manilla Alérgico'			=> $laDatos['MPASEG'],
					'Riesgo de Sangrado'		=> $laDatos['RSASEG'],
					'Riesgo de Fuga'			=> $laDatos['RFUSEG'],
					//'Riesgo de Caida'			=> $laDatos['ERGSEG'],
					//'Riesgo de Caida valor'	=> $laDatos['CLNSEG'],
					//'Riesgo de Ulcera'		=> $laDatos['EBRSEG'],
					//'Riesgo de Ulcera valor'	=> $laDatos['VBRSEG'],
					'Riesgo de Caida'			=> $laDatos['ERGSEG']=='SI' ? $laDatos['CLNSEG'] : $laDatos['ERGSEG'],
					'Riesgo de Ulcera'			=> $laDatos['EBRSEG']=='SI' ? $laDatos['VBRSEG'] : $laDatos['EBRSEG'],
				],
				'Observaciones'				=> $laDatos['OBSSEG'],
				'Usuario'					=> $laDatos['USRSEG'],
				'Fecha'						=> $laDatos['FECSEG'],
				'Hora'						=> $laDatos['HORSEG'],
			];
		}
		return $laSeguridad;
	}

	/*
	 *	Obtener última valoración dolor reportada en notas de enfermería
	 *	@param entero $tnIngreso: número de ingreso
	 *	@return array Última valoración de dolor
	 */
	public function Dolor($tnIngreso, $tnFecha=false)
	{
		$tnFecha = $this->setFecha($tnFecha);
		$laDolor = [];
		$laDatos = $this->oDb
			->select('CONDOL, CNTDOL, ESCDOL, LOCDOL, MDODOL, RTADOL, OBSDOL, USRDOL, FECDOL, HORDOL')
			->from('ENDOLO')
			->where(['INGDOL'=>$tnIngreso])
			->where('FECDOL','<=',$tnFecha)
			->orderBy('CNTDOL DESC')
			->get('array');
		if ($this->oDb->numRows()>0) {
			$laDatos = array_map('trim', $laDatos);
			$laDolor = [
				'ConsNota'		=> $laDatos['CONDOL'],
				'ConsReg'		=> $laDatos['CNTDOL'],
				'Escala Dolor'	=> $laDatos['ESCDOL'],
				'Localización'	=> $laDatos['LOCDOL'],
				'Manejo Dolor'	=> $laDatos['MDODOL'],
				'Respuesta'		=> $laDatos['RTADOL'],
				'Observaciones'	=> $laDatos['OBSDOL'],
				'Usuario'		=> $laDatos['USRDOL'],
				'Fecha'			=> $laDatos['FECDOL'],
				'Hora'			=> $laDatos['HORDOL'],
			];
		}
		return $laDolor;
	}

	/*
	 *	Obtener último registro de cateterismo reportado en notas de enfermería
	 *	@param entero $tnIngreso: número de ingreso
	 *	@return array Último registro de cateterismo (Hemodinamia)
	 */
	public function Hemodinamia($tnIngreso, $tnFecha=false)
	{
		$tnFecha = $this->setFecha($tnFecha);
		$laHemodinamia = [];
		$laDatos = $this->oDb
			->select('CONHEM, CNTHEM, PMDHEM, PMIHEM, ASPHEM, IMVHEM, INTHEM, USRHEM, FECHEM, HORHEM')
			->from('ENHEMO')
			->where(['INGHEM'=>$tnIngreso])
			->where('FECHEM','<=',$tnFecha)
			->orderBy('CNTHEM DESC')
			->get('array');
		if ($this->oDb->numRows()>0) {
			$laDatos = array_map('trim', $laDatos);
			$laHemodinamia = [
				'ConsNota'				=> $laDatos['CONHEM'],
				'ConsReg'				=> $laDatos['CNTHEM'],
				'Pulso MID'				=> $laDatos['PMDHEM'],
				'Pulso MII'				=> $laDatos['PMIHEM'],
				'Aspecto Sitio Punción'	=> $laDatos['ASPHEM'],
				'Inmovilización'		=> $laDatos['IMVHEM'],
				'Introductores'			=> $laDatos['INTHEM'],
				'Usuario'				=> $laDatos['USRHEM'],
				'Fecha'					=> $laDatos['FECHEM'],
				'Hora'					=> $laDatos['HORHEM'],
			];
		}
		return $laHemodinamia;
	}

	/*
	 *	Obtener último registro de drenes reportado en notas de enfermería
	 *	@param entero $tnIngreso: número de ingreso
	 *	@return array Último registro de drenes
	 */
	public function Drenes($tnIngreso, $tnFecha=false)
	{
		$tnFecha = $this->setFecha($tnFecha);
		$laDrenes = [];
		$laDatos = $this->oDb
			->select('E.CONDRE, E.CNTDRE, E.DREDRE, E.CARDRE, E.USRDRE, E.FECDRE, E.HORDRE')
			->from('ENDREN E')
			->where(['E.INGDRE'=>$tnIngreso])
			->where('E.FECDRE*1000000+E.HORDRE = (SELECT MAX(M.FECDRE*1000000+M.HORDRE) FROM ENDREN M WHERE M.INGDRE=E.INGDRE AND M.FECDRE<='.$tnFecha.')')
			->orderBy('E.CNTDRE DESC')
			->getAll('array');
		if ($this->oDb->numRows()>0) {
			$laDato = array_map('trim', $laDatos[0]);
			$laDrenes = [
				'ConsNota'	=> $laDato['CONDRE'],
				'ConsReg'	=> $laDato['CNTDRE'],
				'Drenes'	=> [],
				'Usuario'	=> $laDato['USRDRE'],
				'Fecha'		=> $laDato['FECDRE'],
				'Hora'		=> $laDato['HORDRE'],
			];
			foreach ($laDatos as $laDato) {
				$laDato = array_map('trim', $laDato);
				$laDrenes['Drenes'][] = [
					'Tipo Dren'		=> $laDato['DREDRE'],
					'Localización'	=> $laDato['CARDRE'],
				];
			}
		}
		return $laDrenes;
	}

	/*
	 *	Obtener último registro de drenes reportado en notas de enfermería
	 *	@param entero $tnIngreso: número de ingreso
	 *	@return array Último registro de drenes
	 */
	public function Renal($tnIngreso, $tnFecha=false)
	{
		$tnFecha = $this->setFecha($tnFecha);
		$laRenal = [];
		$laDatos = $this->oDb
			->select('E.CONREN, E.CNTREN, E.DIUREN, E.ASPREN, E.OBSREN, E.USRREN, E.FECREN, E.HORREN')
			->from('ENRENA E')
			->where(['E.INGREN'=>$tnIngreso])
			->where('E.FECREN*1000000+E.HORREN = (SELECT MAX(M.FECREN*1000000+M.HORREN) FROM ENRENA M WHERE M.INGREN=E.INGREN AND M.FECREN<='.$tnFecha.')')
			->orderBy('E.CNTREN DESC')
			->getAll('array');
		if ($this->oDb->numRows()>0) {
			$laDato = array_map('trim', $laDatos[0]);
			$laRenal = [
				'ConsNota'	=> $laDato['CONREN'],
				'ConsReg'	=> $laDato['CNTREN'],
				'Renal'		=> [],
				'Usuario'	=> $laDato['USRREN'],
				'Fecha'		=> $laDato['FECREN'],
				'Hora'		=> $laDato['HORREN'],
			];
			foreach ($laDatos as $laDato) {
				$laDato = array_map('trim', $laDato);
				$laRenal['Renal'][] = [
					'Diuresis'		=> $laDato['DIUREN'],
					'Aspecto'		=> $laDato['ASPREN'],
					'Observaciones'	=> $laDato['OBSREN'],
				];
			}
		}
		return $laRenal;
	}

	/*
	 *	Obtener último registro de gastro y eliminación guardado por enfermería
	 *	@param entero $tnIngreso: número de ingreso
	 *	@return array Último registro de gastro
	 */
	public function Gastro($tnIngreso, $tnFecha=false)
	{
		$tnFecha = $this->setFecha($tnFecha);
		$laGastro = [];
		$laDatos = $this->oDb
			->select('CONELI, CNTELI, EVCELI, AHEELI, EDEELI, COLELI, RESELI, OBSELI, USRELI, FECELI, HORELI')
			->from('ENELIM')
			->where(['INGELI'=>$tnIngreso])
			->where('FECELI','<=',$tnFecha)
			->orderBy('CNTELI DESC')
			->get('array');
		if ($this->oDb->numRows()>0) {
			$laDatos = array_map('trim', $laDatos);
			$laGastro = [
				'ConsNota'				=> $laDatos['CONELI'],
				'Gastro'	=> [],
				'Elimina'	=> [
					'ConsReg'				=> $laDatos['CNTELI'],
					'Evacuación Intestinal'	=> $laDatos['EVCELI'],
					'Aspecto Heces'			=> $laDatos['AHEELI'],
					'Colostomia'			=> $laDatos['COLELI'],
					'Enema/Tipo'			=> $laDatos['EDEELI'],
					'Resultado'				=> $laDatos['RESELI'],
				],
				'Observaciones'			=> $laDatos['OBSELI'],
				'Usuario'				=> $laDatos['USRELI'],
				'Fecha'					=> $laDatos['FECELI'],
				'Hora'					=> $laDatos['HORELI'],
			];

			$laDatos = $this->oDb
				->select('CONGAS, CNTGAS, DIEGAS, TVIGAS, RPRGAS, TOLGAS, RGSGAS, FI3GAS, OBSGAS, USRGAS, FECGAS, HORGAS')
				->from('ENGAST')
				->where(['INGGAS'=>$tnIngreso, 'FI3GAS'=>$laDatos['CNTELI']])
				->orderBy('CNTGAS DESC')
				->get('array');
			if ($this->oDb->numRows()>0) {
				$laDatos = array_map('trim', $laDatos);
				$laGastro['Gastro'] = [
					// 'ConsNota'				=> $laDatos['CONGAS'],
					'ConsReg'				=> $laDatos['CNTGAS'],
					'Dieta?'				=> $laDatos['DIEGAS'],
					'Vía'					=> $laDatos['TVIGAS'],
					'Tolerancia'			=> $laDatos['TOLGAS'],
					'Residuo Gástrico'		=> $laDatos['RGSGAS'],
					'Ruidos Peristalticos'	=> $laDatos['RPRGAS'],
					// 'Observaciones'			=> $laDatos['OBSGAS'],
					// 'Usuario'				=> $laDatos['USRGAS'],
					// 'Fecha'					=> $laDatos['FECGAS'],
					// 'Hora'					=> $laDatos['HORGAS'],
				];
			}
		}
		return $laGastro;
	}

	/*
	 *	Obtener último registro de Cardio reportado en notas de enfermería
	 *	@param entero $tnIngreso: número de ingreso
	 *	@return array Último registro de Cardio
	 */
	public function Cardio($tnIngreso, $tnFecha=false)
	{
		$tnFecha = $this->setFecha($tnFecha);
		$laCardio = [];
		$laDatos = $this->oDb
			->select('CONCAR, CNOCAR, RTCCAR, DTXCAR, PRHCAR, MARCAR, MODCAR, FRECAR, SALCAR, SENCAR, IAVCAR, MEDCAR, ASPCAR, OBSCAR, USRCAR, FECCAR, HORCAR')
			->from('ENCARD')
			->where(['INGCAR'=>$tnIngreso])
			->where('FECCAR','<=',$tnFecha)
			->orderBy('CNOCAR DESC')
			->get('array');
		if ($this->oDb->numRows()>0) {
			$laDatos = array_map('trim', $laDatos);
			$laCardio = [
				'ConsNota'				=> $laDatos['CONCAR'],
				'ConsReg'				=> $laDatos['CNOCAR'],
				'Marcapasos'			=> $laDatos['MARCAR'],
				'Modo '					=> $laDatos['MODCAR'],
				'Frec.por Minuto'		=> $laDatos['FRECAR'],
				'Salida'				=> $laDatos['SALCAR'],
				'Sensibilidad'			=> $laDatos['SENCAR'],
				'Intervalo AV'			=> $laDatos['IAVCAR'],
				'Ritmo Cardiáco'		=> $laDatos['RTCCAR'],
				'Dolor Toráxico'		=> $laDatos['DTXCAR'],
				'Proc. Hemodinamia'		=> $laDatos['PRHCAR'],
				'Aspec. Sitio Punción'	=> $laDatos['ASPCAR'],
				'Medias Antiembólicas'	=> $laDatos['MEDCAR'],
				'Observaciones'			=> $laDatos['OBSCAR'],
				'Usuario'				=> $laDatos['USRCAR'],
				'Fecha'					=> $laDatos['FECCAR'],
				'Hora'					=> $laDatos['HORCAR'],
			];
		}
		return $laCardio;
	}

	/*
	 *	Obtener último registro de Balón de Contrapulsación reportado en notas de enfermería
	 *	@param entero $tnIngreso: número de ingreso
	 *	@return array Último registro de Balón de Contrapulsación
	 */
	public function BalonContrapulsacion($tnIngreso, $tnFecha=false)
	{
		$tnFecha = $this->setFecha($tnFecha);
		$laBalon = [];
		$laDatos = $this->oDb
			->select('CONBAL, CNTBAL, BALBAL, AIDBAL, AIIBAL, ASDBAL, ASIBAL, PIDBAL, PIIBAL, PSDBAL, PSIBAL, CIDBAL, CIIBAL, TIDBAL, TIIBAL, RMUBAL, PSABAL, MVPBAL, USRBAL, FECBAL, HORBAL')
			->from('ENBALON')
			->where(['INGBAL'=>$tnIngreso])
			->where('FECBAL','<=',$tnFecha)
			->orderBy('CNTBAL DESC')
			->get('array');
		if ($this->oDb->numRows()>0) {
			$laDatos = array_map('trim', $laDatos);
			$laBalon = [
				'ConsNota'			=> $laDatos['CONBAL'],
				'ConsReg'			=> $laDatos['CNTBAL'],
				'Tiene Balón de Contrapulsación?' => $laDatos['BALBAL'],
				'Pulsos Previos' => [
					'MID'	=> $laDatos['AIDBAL'],
					'MII'	=> $laDatos['AIIBAL'],
					'MSD'	=> $laDatos['ASDBAL'],
					'MSI'	=> $laDatos['ASIBAL'],
				],
				'Pulsos Posteriores' => [
					'MID'	=> $laDatos['PIDBAL'],
					'MII'	=> $laDatos['PIIBAL'],
					'MSD'	=> $laDatos['PSDBAL'],
					'MSI'	=> $laDatos['PSIBAL'],
				],
				'Llenado Capilar (Seg)' => [
					'MID'	=> $laDatos['CIDBAL'],
					'MII'	=> $laDatos['CIIBAL'],
				],
				'Temperatura' => [
					'MID'	=> $laDatos['TIDBAL'],
					'MII'	=> $laDatos['TIIBAL'],
				],
				'Rigidez Muscular'	=> $laDatos['RMUBAL'],
				'Presencia Sangre'	=> $laDatos['PSABAL'],
				'Movimiento Pie'	=> $laDatos['MVPBAL'],
				'Usuario'			=> $laDatos['USRBAL'],
				'Fecha'				=> $laDatos['FECBAL'],
				'Hora'				=> $laDatos['HORBAL'],
			];
		}
		return $laBalon;
	}

	/*
	 *	Obtener último registro de Cambio Posicion reportado en notas de enfermería
	 *	@param entero $tnIngreso: número de ingreso
	 *	@return array Último registro de Cambio Posicion
	 */
	public function CambioPosicion($tnIngreso, $tnFecha=false)
	{
		$tnFecha = $this->setFecha($tnFecha);
		$laCambioPos = [];
		$laDatos = $this->oDb
			->select('CONACT, CRGACT, TACACT, TOLACT, CPSACT, HCPACT, PPCACT, ACEACT, CUIACT, USRACT, FECACT, HORACT')
			->from('ENACTI')
			->where(['INGACT'=>$tnIngreso])
			->where('FECACT','<=',$tnFecha)
			->orderBy('CRGACT DESC')
			->get('array');
		if ($this->oDb->numRows()>0) {
			$laDatos = array_map('trim', $laDatos);
			$laCambioPos = [
				'ConsNota'				=> $laDatos['CONACT'],
				'ConsReg'				=> $laDatos['CRGACT'],
				'Actividad'				=> $laDatos['TACACT'],
				'Tolerancia'			=> $laDatos['TOLACT'],
				'Cambio de Posición?'	=> $laDatos['CPSACT'],
				'Hora Cambio'			=> $laDatos['HCPACT'],
				'Posición Paciente'		=> $laDatos['PPCACT'],
				'Cuidado de Piel'		=> [
					'Protección Protuberancias'	=> substr($laDatos['CUIACT'], 8,1)==1 ? 'SI' : 'NO',
					'Masajes'					=> substr($laDatos['CUIACT'],18,1)==1 ? 'SI' : 'NO',
					'Lubricación'				=> substr($laDatos['CUIACT'],29,1)==1 ? 'SI' : 'NO',
				],
				'Actividad Enf.'		=> $laDatos['ACEACT'],
				'Usuario'				=> $laDatos['USRACT'],
				'Fecha'					=> $laDatos['FECACT'],
				'Hora'					=> $laDatos['HORACT'],
			];
		}
		return $laCambioPos;
	}

	/*
	 *	Obtener última valoración neurológica reportada en notas de enfermería
	 *	@param entero $tnIngreso: número de ingreso
	 *	@return array Última valoración neurológica
	 */
	public function Neurologico($tnIngreso, $tnFecha=false)
	{
		$tnFecha = $this->setFecha($tnFecha);
		$laNeuro = [];
		$laDatos = $this->oDb
			->select('COSNEC, CTUNEC, OBSNEC, PUPNEC, EGLNEC, USRNEC, FECNEC, HORNEC')
			->from('ENNEURC')
			->where(['INGNEC'=>$tnIngreso])
			->where('FECNEC','<=',$tnFecha)
			->orderBy('CTUNEC DESC')
			->get('array');
		if ($this->oDb->numRows()>0) {
			$laNeuro = $this->organizaNeurologico($laDatos);
		}
		return $laNeuro;
	}

	/*
	 *	Organiza los datos obtenidos de consultar registro neurológico
	 *	@param array $taDatos: una fila de la consulta
	 *	@return array Datos de registro Neurológico
	 */
	private function organizaNeurologico($taDatos)
	{
		$taDatos = array_map('trim', $taDatos);
		$laTemp = [];

		// OBSNEC="Apertura Ocular: 2 Rta Verbal: 1 Rta Motora: 3 Conciencia: 2 Reflejos: 1 1 1 Respitario: 2 Crisis Convulsiva: 2"
		// PUPNEC="Iso: S Ani: N Mod:0 Moi:0 Mde:0 Mdi:0 Reactiva:N No Reac.:S NRe Der.:1 NRe Izq.:1"
		$laPosObs = [
			'OBSNEC' => [19, 14, 14, 14, 16, 14, 21],
			'PUPNEC' => [7, 7, 6, 6, 6, 6, 11, 11, 11, 11],
		];
		foreach ($laPosObs as $lcCampo => $laPos) {
			$lnPosAnt = 0;
			foreach ($laPos as $lnLargo) {
				$laClave = explode(':', substr($taDatos[$lcCampo], $lnPosAnt, $lnLargo));
				if (is_array($laClave) && count($laClave)==2) {
					$lnPosAnt += $lnLargo;
					$laTemp[$laClave[0]] = trim($laClave[1]);
				}
			}
		}
		$laReflejos = explode(' ', $laTemp['Reflejos']);

		return [
			'ConsNota'	=> $taDatos['COSNEC'],
			'ConsReg'	=> $taDatos['CTUNEC'],
			'Glasgow'	=> [
				'Apertura Ocular'	=> $laTemp['Apertura Ocular'],
				'Respuesta Verbal'	=> $laTemp['Rta Verbal'],
				'Respuesta Motora'	=> $laTemp['Rta Motora'],
				'Glasgow'			=> intval($taDatos['EGLNEC']),
			],
			'Nivel Conciencia'		=> $laTemp['Conciencia'],
			'Patrón Respiratorio'	=> $laTemp['Respitario'],
			'Crísis Convulsiva'		=> $laTemp['Crisis Convulsiva'],
			'Reflejos' => [
				'Corneano'		=> $laReflejos[0],
				'Palpebral'		=> $laReflejos[1],
				'Oculocefálico'	=> $laReflejos[2],
			],
			'Pupilas Isocóricas'	=> $laTemp['Iso'],
			'Pupilas Anisocóricas'	=> [
				'Pupilas Anisocóricas'	=> $laTemp['Ani'],
				'Miótica Derecha'		=> $laTemp['Mod'],
				'Miótica Izquierda'		=> $laTemp['Moi'],
				'Midiátrica Derecha'	=> $laTemp['Mde'],
				'Midiátrica Izquierda'	=> $laTemp['Mdi'],
			],
			'Reactivas' => $laTemp['Reactiva']??'',
			'No Reactivas' => [
				'No Reactivas'	=> $laTemp['No Reac.']??'',
				'Derecha'		=> $laTemp['NRe Der.']??'',
				'Izquierda'		=> $laTemp['NRe Izq.']??'',
			],
			'Usuario'	=> $taDatos['USRNEC'],
			'Fecha'		=> $taDatos['FECNEC'],
			'Hora'		=> $taDatos['HORNEC'],
		];
	}

	/*
	 *	Obtener último registro de drenes reportado en notas de enfermería
	 *	@param entero $tnIngreso: número de ingreso
	 *	@return array Último registro de drenes
	 */
	public function Cuidados($tnIngreso, $tnFecha=false)
	{
		$tnFecha = $this->setFecha($tnFecha);
		$laCuidados = [];
		$laDatos = $this->oDb
			->select('E.CONCUI, E.CNTCUI, E.OBSCUI, E.USRCUI, E.FRGCUI, E.HRGCUI')
			->from('ENCUIDA E')
			->where(['E.INGCUI'=>$tnIngreso])
			->where('E.CNTCUI = (SELECT MAX(M.CNTCUI) FROM ENCUIDA M WHERE M.INGCUI=E.INGCUI AND M.FRGCUI<='.$tnFecha.')')
			->orderBy('E.CLNCUI ASC')
			->getAll('array');
		if ($this->oDb->numRows()>0) {
			$laDato = array_map('trim', $laDatos[0]);
			$laCuidados = [
				'ConsNota'	=> $laDato['CONCUI'],
				'ConsReg'	=> $laDato['CNTCUI'],
				'Cuidados'	=> '',
				'Usuario'	=> $laDato['USRCUI'],
				'Fecha'		=> $laDato['FRGCUI'],
				'Hora'		=> $laDato['HRGCUI'],
			];
			foreach ($laDatos as $laDato) {
				$laCuidados['Cuidados'] .= $laDato['OBSCUI'];
			}
			$laCuidados['Cuidados'] = trim($laCuidados['Cuidados']);
		}
		return $laCuidados;
	}

	/*
	 *	Obtener última escala de GLASGOW reportada en notas de enfermería
	 *	@param entero $tnIngreso: número de ingreso
	 *	@return string Resultado de la escala GLASGOW
	 */
	public function Glasgow($tnIngreso, $tnFecha=false)
	{
		$tnFecha = $this->setFecha($tnFecha);
		$lcGlasgow = '';
		$laGlasgow = $this->oDb
			->select('EGLNEC')
			->from('ENNEURC')
			->where(['INGNEC'=>$tnIngreso])
			->where('FECNEC','<=',$tnFecha)
			->orderBy('CTUNEC DESC')
			->get('array');
		if ($this->oDb->numRows()>0) {
			$lcGlasgow = trim($laGlasgow['EGLNEC']);
		}
		return intval($lcGlasgow);
	}

	/*
	 *	Obtener último Control de Líquidos registrado por enfermería
	 *	@param entero $tnIngreso: número de ingreso
	 *	@return array Último control de líquidos
	 */
	public function controlLiquidos($tnIngreso, $tnFecha=false)
	{
		$tnFecha = $this->setFecha($tnFecha);
		$laCtrLiq = [];
		$laDatos = $this->oDb
			->select('CONBAC,CNSBAC,TOSBAC,TOEBAC,BALBAC,TISBAC,TIEBAC,BILBAC,SCABAC,NCABAC,USRBAC,FECBAC,HORBAC')
			->from('ENBALC')
			->where(['INGBAC'=>$tnIngreso])
			->where('FECBAC','<=',$tnFecha)
			->orderBy('CONBAC DESC')
			->get('array');
		if ($this->oDb->numRows()>0) {
			$laDatos = array_map('trim', $laDatos);
			$laCtrLiq = [
				'ConsNota'			=> $laDatos['CNSBAC'],
				'ConsReg'			=> $laDatos['CONBAC'],
				'Total'				=> [
					'Suministrado'	=> $laDatos['TOSBAC'],
					'Eliminado'		=> $laDatos['TOEBAC'],
					'Balance'		=> $laDatos['BALBAC'],
				],
				'Irrigación'		=> [
					'Suministrado'	=> $laDatos['TISBAC'],
					'Eliminado'		=> $laDatos['TIEBAC'],
					'Balance'		=> $laDatos['BILBAC'],
				],
				'Usuario'			=> $laDatos['USRBAC'],
				'Fecha'				=> $laDatos['FECBAC'],
				'Hora'				=> $laDatos['HORBAC'],
			];
		}
		return $laCtrLiq;
	}

	/*
	 *	Parámetros de enfermería para un tipo determinado
	 *	@param entero $tnTipo: tipo de parámetro
	 *	@return array parámetros organizados
	 */
	public function paramEnfermeria($tnTipo=25)
	{
		$laRta = [];
		$laDatos = $this->oDb
			->select('VARENF, REFENF, DESENF')
			->from('TABENF')
			->where("TIPENF={$tnTipo}")
			->orderBy('VARENF, REFENF')
			->getAll('array');
		if ($this->oDb->numRows()>0) {
			foreach ($laDatos as $laDato) {
				$laDato = array_map('trim', $laDato);
				if (!isset($laRta[$laDato['VARENF']])) $laRta[$laDato['VARENF']]=[];
				$laRta[$laDato['VARENF']][$laDato['REFENF']] = $laDato['DESENF'];
			}
		}
		return $laRta;
	}


	/*
	 *	Retorna fecha enviada o fecha actual si se envía falso
	 *	@param entero $tnFecha: fecha (AAAAMMDD o AAAA-MM-DD) o false
	 *	@return string fecha en formato AAAAMMDD
	 */
	public function setFecha($tnFecha=false)
	{
		if ($tnFecha===false) $tnFecha = $this->oDb->fechaHoraSistema();
		return substr(str_replace('-', '', $tnFecha), 0, 8);
	}


	/*
	 *	Lista de procedimientos realizados para un ingreso - fecha
	 *	@param entero $tnIngreso: número de ingreso
	 *	@param entero $tnFecha: fecha AAAAMMDD
	 *	@return array con la lista de procedimientos, fecha y hora de realizado
	 */
	public function procedimientos($tnIngreso, $tnFecha=false)
	{
		$laRta = ['LABORATORIOS'=>[],'INTERCONSULTAS'=>[],'PROCEDIMIENTOS'=>[],'IMÁGENES'=>[],'TRANSFUSIONES'=>[],'TERAPIAS'=>[],];
		$tnFecha = $this->setFecha($tnFecha);

		$lcTmp = $this->oDb->obtenerTabMae1('de2tma||op5tma', 'FORMEDIC', ['cl1tma'=>'GLUCOME','esttma'=>''], null, 'M19275,903883');
		$laCupsGlucom = explode(',', str_replace('\'', '', trim($lcTmp)));
		$laCupsGasesAV = '903839';
		$lcTmp = $this->oDb->obtenerTabMae1('de2tma||op5tma', 'FORMEDIC', ['cl1tma'=>'CUPSURG','cl2tma'=>'1','esttma'=>''], null, '890701,890702');
		$laCupsUrg = explode(',', str_replace('\'', '', trim($lcTmp)));
		$laPrgEvita = ['RIA100ORD','RIA050'];

		$laDatos = $this->oDb
			->select('O.CCOORD, O.CCIORD, O.CODORD, O.RMRORD')
			->select('TRIM(O.COAORD) CODCUP, C.RF2CUP, TRIM(C.DESCUP) DESCUP')
			->select("TO_DATE(CHAR(O.FERORD*1000000+O.HRLORD),'YYYYMMDDHHMISS') REALIZA")
			->from('RIAORD O')
			->leftJoin('RIACUP  AS C', 'O.COAORD = C.CODCUP')
			->where([
				'O.NINORD' => $tnIngreso,
				'O.FERORD' => $tnFecha,
			])
			->in('O.ESTORD', [3,59])
			->notIn('O.COAORD', $laCupsUrg)		// Evitar consulta urgencias
			->notIn('C.PGRCUP', $laPrgEvita)	// Evitar historias clínicas, juntas médicas
			->orderBy('O.FERORD, O.HRLORD')
			->getAll('array');
		if ($this->oDb->numRows()>0) {

			foreach ($laDatos as $laDato) {

				$lcDsc = $laDato['DESCUP'];
				$lcRes = '';

				// Interconsultas
				if (substr($laDato['CODCUP'],0,4)==='8904') {
					$lcTipo = 'INTERCONSULTAS';
				}

				// Transfusiones
				elseif (substr($laDato['CODCUP'],0,4)==='9120') {
					$lcTipo = 'TRANSFUSIONES';
				}

				// Terapias
				elseif (in_array($laDato['CODORD'], ['290','291','730','731','735'])) {
					$lcTipo = 'TERAPIAS';
				}

				// Imágenes
				// elseif (in_array($laDato['CODORD'], ['602','603'])) {
				elseif ($laDato['RF2CUP']=='IMAGEN') {
					$lcTipo = 'IMÁGENES DIAG.';
				}

				// Laboratorios
				// && !in_array($laDato['CODCUP'], $laCupsGlucom)) {
				elseif (in_array($laDato['CODORD'], ['312','352','353'])) {
					$lcTipo = 'LABORATORIOS';
					$lcSep = ' - ';
					if ($laDato['CODCUP']=='903883') {
						$laResul = $this->oDb
							->select('MAYGLU, MEDGLU, UMEGLU, FDIGLU, HDIGLU')
							->from('ENGLUCO ')
							->where([ 'INGGLU'=>$tnIngreso, 'CNTGLU'=>$laDato['CCIORD'] ])
							->get('array');
						if ($this->oDb->numRows()>0) {
							$laResul = array_map('trim',$laResul);
							$lcRes = "Glu {$laResul['MAYGLU']} {$laResul['MEDGLU']} {$laResul['UMEGLU']}";
						}
					} else {
						$laResul = $this->oDb
							->select('R.ESTADO,R.CRESUL,R.MAXIMO,R.MINIMO,R.UNIDAD,R.MICROO,R.ANTIOB,R.ANTIBI,R.SENSIB,R.DATCRT,R.FECLAB,R.HOCLAB')
							->select('V.DSCVAR,V.ABRVAR,V.CRIVAR,V.RNGVAR')
							->from('LABRES R')
							->leftjoin('LABRSVR V','R.CODVAR=V.CODVAR')
							->where([ 'R.NIGING'=>$tnIngreso, 'R.CONORD'=>$laDato['CCIORD'] ])
							->where('R.FECLAB*1000000+R.HOCLAB = (SELECT MAX(R.FECLAB*1000000+R.HOCLAB) FROM LABRES WHERE NIGING=R.NIGING AND CONORD=R.CONORD)')
							->getAll('array');
						if ($this->oDb->numRows()>0) {
							foreach ($laResul as $laRes) {
								$laRes = array_map('trim',$laRes);
								$lcAbr = empty($laRes['ABRVAR']) ? (empty($laRes['DSCVAR']) ? '' : $laRes['DSCVAR'].': ') : $laRes['ABRVAR'].': ';
								$lcCritico = $laRes['DATCRT']=='0' ? '' : ' * ';
								$lcRes .= "{$lcSep}{$lcAbr}{$laRes['CRESUL']} {$laRes['UNIDAD']}{$lcCritico}";
							}
						}
					}
				}

				// Procedimientos
				else {
					$lcTipo = 'PROCEDIMIENTOS';
					// Buscar procedimientos realizados en la cirugía
					if ($laDato['CODCUP']=='22') {
						$laQxs = $this->oDb
							->select('P.CUPCRP, P.USRCRP, P.FECCRP, P.HORCRP, C.DESCUP')
							->from('FACCIRP P')
							->leftjoin('RIACUP C','P.CUPCRP=C.CODCUP')
							->where([ 'P.INGCRP'=>$tnIngreso, 'P.CNSCRP'=>$laDato['CCOORD'] ])
							->getAll('array');
						if ($this->oDb->numRows()>0) {
							$lcSep = ' - ';
							$lcDsc = '';
							foreach ($laQxs as $laRes) {
								$lcDsc .= $lcSep . trim($laRes['DESCUP']);
							}
						}
					}
				}

				$laRta[$lcTipo][] = [
					'cita'		=> $laDato['CCIORD'],
					'realiza'	=> substr($laDato['REALIZA'],0,19),
					'cup'		=> $laDato['CODCUP'],
					'descrip'	=> $lcDsc,
					'resultado'	=> $lcRes,
				];
			}
		}

		return $laRta ?? [];
	}


	/*
	 *	Lista de medicamentos administrados para un ingreso - fecha
	 *	@param entero $tnIngreso: número de ingreso
	 *	@param entero $tnFecha: fecha AAAAMMDD
	 *	@return array con la lista de medicamentos administrados
	 */
	public function adminMedicamentos($tnIngreso, $tnFecha=false)
	{
		require_once __DIR__ . '/class.Doc_Enf_AdminMed.php';
		require_once __DIR__ . '/class.Ingreso.php';

		$tnFecha = $this->setFecha($tnFecha);
		$loMed = new Doc_Enf_AdminMed();
		$laData = [
			'nIngreso' => $tnIngreso,
			'tFechaHora' => AplicacionFunciones::formatFechaHora('fecha', $tnFecha).' 08:00:00',
		];
		$laData['oIngrPaciente'] = new Ingreso();
		$laData['oIngrPaciente']->cargarIngreso($tnIngreso);

		return $loMed->retornarDatos($laData);
	}

}