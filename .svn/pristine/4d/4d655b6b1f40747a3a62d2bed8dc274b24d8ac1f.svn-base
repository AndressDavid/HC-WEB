<?php
NAMESPACE NUCLEO;

require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	require_once __DIR__ . '/../../../controlador/class.DatosPlanosRips.php';
	$loDatRips=new DatosPlanosRips();

	$lcAccion=$_POST['accion'] ?? '';

	switch($lcAccion){

		case 'listaConsultas':
			$laRetorna['lista']=$loDatRips->aTipos();
			break;

		case 'obtenerPlanos':
			$lcTipoFuncion = $_POST['ent']??'';
			$lcTipo = $_POST['res']??'';
			$laDatos = [
				'facturas'=>explode(',',$_POST['lst']??''),
				'categoria'=>'DATOS_'.date('Y-m-d-h-i-s'),
			];
			if(is_array($laDatos['facturas'])){
				if(count($laDatos['facturas'])>0){
					$laRetorna=$loDatRips->consultarDatos($lcTipoFuncion, $lcTipo, $laDatos);
				}else{
					$laRetorna['error']='No hay facturas para consultar.';
				}
			}else{
				$laRetorna['error']='No se puede obtener la lista de facturas a consultar.';
			}
			break;

		case 'consultaFacturas':
			$laLista=explode(',',$_POST['lista']??'');
			if(is_array($laLista)){
				if(count($laLista)>0){
					$laRetorna['lista']=$laFactErr=$laFactAdd=[];
					foreach($laLista as $lcFactura){
						if(!in_array($lcFactura, $laFactAdd)){
							$laFactAdd[]=$lcFactura;
							$laFactura=$loDatRips->consultarFactura($lcFactura);
							if(empty($laFactura['error']['Dsc']??'')){
								$laRetorna['lista'][]=[
									'FACTURA'	=>$laFactura['cabecera']['FRACAB'],
									'FECHA'		=>$laFactura['cabecera']['FEFCAB'],
									'INGRESO'	=>$laFactura['cabecera']['NIGING'],
									'DOCPAC'	=>$laFactura['cabecera']['TIDING'].' '.$laFactura['cabecera']['NIDING'],
									'PACIENTE'	=>$laFactura['paciente']['nombre'].' '.$laFactura['paciente']['apellido'],
									'CODPLAN'	=>$laFactura['cabecera']['PLNCAB'],
									'PLAN'		=>$laFactura['cabecera']['DSCCON'],
									'ERROR'		=>'Z',
								];
							}else{
								$laFactErr[]=[
									'FACTURA'	=>$lcFactura,
									'PACIENTE'	=>$laFactura['error']['Dsc'],
									'ERROR'		=>'S',
								];
							}
						}
					}
					$laRetorna['lista']=array_merge($laFactErr,$laRetorna['lista']);
				}else{
					$laRetorna['error']='No hay facturas para consultar.';
				}
			}else{
				$laRetorna['error']='No se puede obtener la lista de facturas a consultar.';
			}
			break;

	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
