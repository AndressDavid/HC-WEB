<?php
namespace NUCLEO;
require_once ('class.Db.php') ;

use NUCLEO\Db;


class Texto_Informativo
{
    public function __construct() {
		global $goDb;
		$this->oDb = $goDb;
    }
	
	public function retornarTexto()
	{
		$lcTexto = $this->cargarTxtPandemia();
		return $lcTexto ;
	}
	
	public function cargarTxtPandemia(){
		
		$lcTexto = '';
		$loTabmae = $this->oDb->ObtenerTabMae('OP1TMA', 'HCPARAM', ['CL1TMA'=>'TXTFINAL', 'CL2TMA'=>'COVID', 'CL3TMA'=>'00', 'ESTTMA'=>'']);
		$lnActivar= AplicacionFunciones::getValue($loTabmae, 'OP1TMA', 0);
		
		if($lnActivar==1){
			$laTemp = $this->oDb
				->select('DE2TMA||OP5TMA TEXTO')
				->tabla('TABMAE')
				->where(['TIPTMA'=>'HCPARAM',
						 'CL1TMA'=>'TXTFINAL',
						 'CL2TMA'=>'COVID',
						 'ESTTMA'=>' ',
						])
				->where('CL3TMA', '<>', '00')		
				->orderBy ('CL3TMA')
				->getAll("array");
				
			if(is_array($laTemp)){
				if(count($laTemp)>0){
					foreach($laTemp as $laTexto) {
						$lcTexto .=$laTexto['TEXTO'];
					}
				}
			}
		}
		
		return trim($lcTexto);		
	}
	
}