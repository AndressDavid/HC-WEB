<?php
	require_once (__DIR__ .'/../../publico/constantes.php');

	$llPagina=false;
	if(isset($_GET)){
		$lcPagina = trim(strtolower(isset($_GET['p'])?$_GET['p']:'listaCitasTelemedicina'));
		switch ($lcPagina) {									
			case "listacitastelemedicina":
				$lcPagina="listaCitasTelemedicina";
				break;
				
			case "registrocitastelemedicina":
				$lcPagina="registroCitasTelemedicina";
				break;					
		}
		
		if(!empty($lcPagina)){
			$lcPagina = __DIR__ .'/'.$lcPagina.".php";
			if(is_file($lcPagina)){
				include($lcPagina);
				$llPagina=true;
			}
		}
	}
?>