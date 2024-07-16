<?php
	require_once (__DIR__ .'/../../publico/constantes.php') ;
	require_once (__DIR__ .'/../../../nucleo/controlador/class.Db.php');

	$lcBody = "";
	$lcBodyAyer = "";
	$llValores = false;
	
	$lnRegistrosAyer = 0;
	$lnConsumosAyer = 0;
	$lnIngresosAyer = 0;
	
	
	$lnRegistros = 0;
	$lnConsumos = 0;
	$laIngresos = array();
	
	$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
	$ltAyer = new \DateTime( $goDb->fechaHoraSistema() );
	$ltAyer->modify('-1 day');
	
	$lcFecha = $ltAhora->format("Ymd");
	$lcFechaAyer = $ltAyer->format("Ymd");
	
	$laCampos = ['FRELAB,DATO01,DATO02,DATO03'];
	$laConsumosAyer = $goDb->select($laCampos)
					  ->tabla('CSINFACR')
					  ->where('FRELAB','<',$lcFecha)
					  ->orderBy('FRELAB','DESC')
					  ->limit(11)
					  ->getAll("array");
	if(is_array($laConsumosAyer)==true){
		for($lnItem=0;$lnItem<count($laConsumosAyer)-1;$lnItem++){
			$laConsumoAyer=$laConsumosAyer[$lnItem];

			$lnConsumos=($lnItem+1<count($laConsumosAyer)?$lnConsumos=$laConsumosAyer[$lnItem+1]['DATO03']:0);			
			$lcPicture=($lnConsumos==0?'':($lnConsumos==$laConsumoAyer['DATO03']?'equals':($lnConsumos>$laConsumoAyer['DATO03']?'angle-double-down':'angle-double-up')));
			$lcBg=($lnConsumos==0?'':($lnConsumos==$laConsumoAyer['DATO03']?'success':($lnConsumos>$laConsumoAyer['DATO03']?'success':'danger')));
			$lnConsumosAyer=(empty($lnConsumosAyer)?$laConsumoAyer['DATO03']:$lnConsumosAyer);
			
			$laFecha=date_parse_from_format("Y-m-d", sprintf("%s",$laConsumoAyer['FRELAB']));
			
			$lcBodyAyer.='<tr>';
			$lcBodyAyer.=sprintf('<td class="anteriores">%s-%s-%s</td>',$laFecha['year'],str_pad($laFecha['month'],2, "0", STR_PAD_LEFT),str_pad($laFecha['day'], 2, "0", STR_PAD_LEFT));
			$lcBodyAyer.=sprintf('<td class="anteriores">%s</td>',number_format($laConsumoAyer['DATO01'],0,",","."));
			$lcBodyAyer.=sprintf('<td class="anteriores">%s</td>',number_format($laConsumoAyer['DATO02'],0,",","."));
			$lcBodyAyer.=sprintf('<td class="anteriores">%s <i class="text-%s fas fa-%s"></i></td>',($llValores==true?"$ ".number_format($laConsumoAyer['DATO03'],2,",","."):''),$lcBg,$lcPicture);
			$lcBodyAyer.='</tr>';
		}
	}	
	
	

	$laCampos = [
				'A.INGCSF AS INGRESO', 
				'A.FACCSF AS FACTURA',
				'A.CCOCSF AS CONSEC_CONSUMO',
				'A.CODCSF AS CODIGO_CONSUMO',
				"IFNULL((SELECT SUBSTR(TRIM(DESCUP), 1, 120) FROM RIACUP WHERE CODCUP=A.CODCSF), '') AS DESCRIPCION_CUPS",
				"IFNULL((SELECT SUBSTR(TRIM(DESDES), 1, 120) FROM INVDES WHERE REFDES=A.CODCSF), '') AS DESCRIPCION_INVENTARIO",
				'A.TCOCSF AS TIPO_CONSUMO',
				"IFNULL((SELECT SUBSTR(TRIM(DE2TMA), 1, 25) FROM TABMAE WHERE TIPTMA='WSNOPOS' AND CL1TMA='TIPCONS' AND CL2TMA=A.TCOCSF), '') AS DESCRIPCION_TIPO_CONSUMO",
				'A.VALCSF AS VALOR_CONSUMO',
				'A.PLACSF AS PLAN',
				'A.FCNCSF AS FECHA_CONSUMO',
				'TRIM(B.DSCCON) AS DESCRIPCION_PLAN',
				'C.FEIING AS FECHA_INGRESO',
				'C.FEEING AS FECHA_EGRESO'
				];
	$laConsumos = $goDb->select($laCampos)
					  ->tabla('CSINFAC A')
					  ->leftJoin('FACPLNC B', 'A.PLACSF=B.PLNCON')
					  ->leftJoin('RIAING C', 'A.INGCSF=C.NIGING')
					  ->where('A.FRECSF','=',$lcFecha)
					  ->orderBy('A.INGCSF, A.TCOCSF, A.CODCSF')
					  ->getAll("array");
	$lnConsumos = 0;					  
	if(is_array($laConsumos)==true){
		foreach($laConsumos as $laConsumo){
			$lnTipoConsumo = $laConsumo['TIPO_CONSUMO']; settype($lnTipoConsumo,'Integer'); $lnTipoConsumo+=0;
			$lcEstilo = ($lnTipoConsumo==400?'253,223,224':($lnTipoConsumo==500?'133,200,250':'192,192,192'));
			$lnRegistros+=1;
			$lnConsumos+=$laConsumo['VALOR_CONSUMO'];
			if(isset($laIngresos[$laConsumo['INGRESO']])==true){
				$laIngresos[$laConsumo['INGRESO']]+=1;
			}else{
				$laIngresos[$laConsumo['INGRESO']]=1;
			}
			
			$lcBody.='<tr>';
			$lcBody.=sprintf('<td>%s</td>',$laConsumo['INGRESO']);
			$lcBody.=sprintf('<td>%s</td>',$laConsumo['FECHA_INGRESO']);
			$lcBody.=sprintf('<td>%s</td>',$laConsumo['FACTURA']);
			$lcBody.=sprintf('<td>%s</td>',sprintf('<span class="badge badge-light" style="background: rgb(%s);">%s</span>',$lcEstilo,$laConsumo['DESCRIPCION_TIPO_CONSUMO']));
			$lcBody.=sprintf('<td><small>%s</small></td>',$lnTipoConsumo==400?$laConsumo['DESCRIPCION_CUPS']:$laConsumo['DESCRIPCION_INVENTARIO']);
			$lcBody.=sprintf('<td>%s</td>',$laConsumo['DESCRIPCION_PLAN']);
			$lcBody.=sprintf('<td class="text-right">%s</td>',sprintf('<span class="badge badge-light" style="background: rgb(%s);">%s</span>',$lcEstilo,number_format($laConsumo['VALOR_CONSUMO'],2,",",".")));
			$lcBody.='</tr>';
		}
	}
	$lcBg = ($lnConsumos>$lnConsumosAyer?"danger":"success");
	$lcPicture=($lnConsumos==0?'':($lnConsumos==$lnConsumosAyer?'equals':($lnConsumos>$lnConsumosAyer?'angle-double-up':'angle-double-down')));	
		
?><!doctype html>
<html lang="en" class="h-100">
	<head>
		<!-- Cabecera -->
		<?php
			$lcInclude = file_get_contents("../../publico/head.php");
			$lcInclude = str_replace("publico-complementos","../publico-complementos",$lcInclude);
			$lcInclude = str_replace("publico-css","../publico-css",$lcInclude);
			$lcInclude = str_replace("publico-ico","../publico-ico",$lcInclude);
			$lcInclude = str_replace("hcw-manifiest.json.php","../hcw-manifiest.json.php",$lcInclude);
			print($lcInclude);
		?>

		<style>
			.anteriores{
				font-size: 2.8em !important;
			}
			.hoy{
				font-size: 2em !important;
			}
			.filaPrincipal th{
				width:25%;
			}
		</style>

	</head>
	<body class="h-100">
		<div class="container-fluid h-100">
			<div class="row h-100">
				<div class="container-fluid">
					<div class="card mt-3">
						<div class="card-header text-white bg-<?php print($lcBg); ?>">
							<h2>Consumos sin facturar</h2>
						</div>
						<div class="card-body">			
							<div class="card text-center">

										<table class="card-body table table-sm">
											<thead>
												<tr class="filaPrincipal">
													<th>Fecha</th>
													<th>Registros</th>
													<th>Ingresos</th>
													<th>Valor consumo</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td>
														<h1><span class="badge badge-<?php print($lcBg); ?> hoy"><?php print($ltAhora->format("Y-m-d")); ?></span></h1>
													</td>
													<td>
														<h1><span class="badge badge-<?php print($lcBg); ?> hoy"><?php print(number_format($lnRegistros,0,",",".")); ?></span></h1>
													</td>
													<td>
														<h1><span class="badge badge-<?php print($lcBg); ?> hoy"><?php print(number_format(count($laIngresos),0,",",".")); ?></span></h1>
													</td>
													<td>
														<h1><span class="badge badge-<?php print($lcBg); ?> hoy"><?php print(($llValores==true?"$ ".number_format($lnConsumos,2,",","."):"")." ".sprintf('<i class="fas fa-%s"></i>',$lcPicture)); ?></span></h1>
													</td>
												</tr>
												<?php print($lcBodyAyer); ?>
											</tbody>
										</table>

							</div>
							

							<div class="row">
								<div class="col-md-12">
									<p class="m-t-5 m-b-5">
										<a class="btn btn-outline-primary btn-sm btn-block" data-toggle="collapse" href="#consumosNoFacturados" role="button" aria-expanded="false" aria-controls="consumosNoFacturados">Ver detalle  <b><?php print($ltAhora->format("Y-m-d")); ?></b></a>
									</p>
								</div>
							</div>
							<div class="collapse" id="consumosNoFacturados">
								<div class="card text-left">

										<table class="card-body table table-sm table-striped table-hover">
											<thead>
												<tr>
													<th>Ingreso</th>
													<th>Fecha ingreso</th>
													<th>Factura</th>
													<th>Tipo consumo</th>
													<th>Consumo</th>
													<th>Plan</th>
													<th>Valor consumo</th>
												</tr>
											</thead>
											<tbody><?php print($lcBody); ?></tbody>
											<tfoot>
												<tr>
													<th>Registros: <?php print($lnRegistros); ?></th>
													<th>Ingresos: <?php print(count($laIngresos)); ?></th>
													<th colspan="4"></th>
													<th>$ <?php print(number_format($lnConsumos,2,",",".")); ?></th>
												</tr>								
											</tfoot>
										</table>

								</div>
							</div>				
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>