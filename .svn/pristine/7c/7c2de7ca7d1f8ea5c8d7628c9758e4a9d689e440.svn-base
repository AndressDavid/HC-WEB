<?php
namespace NUCLEO;

class Doc_NotasAclaratorias
{
	/*
	 * Retorna datos de las notas aclaratorias
	 * Parámetros:
	 * @param $tnIngreso		(int) Ingreso del paciente
	 * @param $tnCita			(int) Consecutivo de cita
	 * @param $tcCup			Código del procedimiento
	 * @param $tcForma			Formulario origen de la nota (distinguir diferentes form de fisioterapia )
	 * @param $tcOrden			Orden en que retorna las notas, puede ser 'ASC' o 'DESC'
	 * @param $tlObligaForma	Si es true el form ($tcForma) es obligatorio en la consulta
	 * @public
	 * @since (2019-08-16)
	 */
	public function datosNotasAclaratorias($tnIngreso=0, $tcCup='', $tnCita=0, $tcForma='', $tcOrden='ASC', $tlObligaForma=false)
	{
		global $goDb;
		$laReturn = [];

		// Condición de consulta
		$lcWhere = empty($tcForma) ? "op2nim=''" : ($tlObligaForma ? "op2nim='$tcForma'" : "(op2nim='$tcForma' OR op2nim='')");

		// Consulta las notas aclaratorias
		$laNotas = $goDb
			->select('CNONIM,CNLNIM,DESNIM')
			->from('NOTIMAL02')
			->where([
				'INGNIM'=>$tnIngreso,
				'CCINIM'=>$tnCita==''?0:$tnCita,
				'PRONIM'=>$tcCup,
				'OP1NIM'=>'',
				])
			->where($lcWhere)
			->orderBy('CNONIM '.$tcOrden.', CNLNIM ASC')
			->getAll('array');

		// Genera array con las notas
		if (is_array($laNotas)) {
			foreach($laNotas as $laLinea) {
				if ($laLinea['CNLNIM']==1) {
					$laReturn[$laLinea['CNONIM']]['titulo'] = trim($laLinea['DESNIM']);
					$laReturn[$laLinea['CNONIM']]['descrp'] = '';
				} else {
					$laReturn[$laLinea['CNONIM']]['descrp'] .= $laLinea['DESNIM'];
				}
			}
		}
		return $laReturn;
	}

	public function notasAclaratoriasLibro($tnIngreso=0, $tcCup='', $tnCita=0, $tcForma='', $tcOrden='ASC', $tlObligaForma=false)
	{
		$laNotas = $this->datosNotasAclaratorias($tnIngreso, $tcCup, $tnCita, $tcForma, $tcOrden, $tlObligaForma);
		$laRet = [];
		if(count($laNotas )>0){
			$laRet[] = ['titulo1', 'NOTAS ACLARATORIAS'];
			foreach($laNotas as $laNota){
				$laRet[] = ['titulo6', $laNota['titulo']];
				$laRet[] = ['texto8',  trim($laNota['descrp'])];
			}
		}
		return $laRet;
	}
}