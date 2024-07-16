<?php
	require_once (__DIR__ .'/../../publico/constantes.php') ;
	require_once (__DIR__ .'/../../controlador/class.AplicacionFunciones.php');	

	$ltAhora = new DateTime($goDb->fechaHoraSistema());
	$lnTimeMaxSeg=60;
	$lcHtml="";
	
	// ----------------------------
	// DOMINIO
	// ----------------------------
	$lcLdapServer="172.20.10.22";
	$lnLdapServerPort=389;
	$lcDomainFqdn="shaio.com";
	$lcLdapDc="dc=SHAIO,dc=COM";

	$laAutorizados = require __DIR__ . '/../../privada/webResoucesLDAP.php';
	$laDominio = array();

	$lnFallas = 0;
	$lcFallas = "";
	$lcUserName = trim(strtolower("urgencias"));
	$lcPassword   = "shaio";
	
	$lnUsuariosInactivos=0;
	$lnUsuariosActivos=0;
	$lcUsuariosBloqueados="";
	$lcUsuariosScriptDistinto = "";
	
	if (!empty($lcUserName) && !empty($lcPassword )){
		$lnLdapHandle = ldap_connect($lcLdapServer, $lnLdapServerPort);

		if ($lnLdapHandle){
			if(version_compare(PHP_VERSION, '8.1') === -1 ? $lnLdapHandle>0 : is_object($lnLdapHandle)){
				ldap_set_option($lnLdapHandle, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option($lnLdapHandle, LDAP_OPT_REFERRALS, 0);
				$llLdapBind = @ldap_bind($lnLdapHandle, $lcUserName."@".$lcDomainFqdn, $lcPassword);

				// USUARIOS INACTIVOS
				$LDAP_Search_String = "(&(objectCategory=person)(objectClass=user)(userAccountControl:1.2.840.113556.1.4.803:=2))";
				$LDAP_Search = ldap_search($lnLdapHandle, $lcLdapDc, $LDAP_Search_String);
				$laEntries = ldap_get_entries($lnLdapHandle, $LDAP_Search);
				$lnUsuariosInactivos = $laEntries["count"];				


				// USUARIOS
				$LDAP_Search_String = "(&(objectCategory=person)(objectClass=user))";
				$LDAP_Search = ldap_search($lnLdapHandle, $lcLdapDc, $LDAP_Search_String);
				$laEntries = ldap_get_entries($lnLdapHandle, $LDAP_Search);
				
				$lnUsuariosActivos = $laEntries["count"]-$lnUsuariosInactivos;

				$lcHtml .= showCardInfo("success","Usuarios Activos<br/><small><b>Dominio</b></small>",$lnUsuariosActivos,sprintf('<span class="badge badge-secondary">Inactivos %s</span> <span class="badge badge-success">Total %s</span>',$lnUsuariosInactivos,$laEntries["count"]));
				
				$laFault['1']="";
				$laFault['2']="";
				$laFault['3']="";
				$laFault['4']="";
				
				for ($i=0; $i<$laEntries["count"]; $i++) {
										
					if(isset($laEntries[$i]['scriptpath'])==true){
						if(isset($laEntries[$i]['samaccountname'])==true){
							$lcSamAccountName = trim(strtolower($laEntries[$i]['samaccountname'][0]));
							$lcScriptPath = trim(strtolower($laEntries[$i]['scriptpath'][0]));
							
							if($lcSamAccountName.".bat"!=$lcScriptPath){
								$lcUsuariosScriptDistinto .= (empty($lcUsuariosScriptDistinto)?'':', ').$laEntries[$i]["cn"][0].sprintf('<br><small><i class="far fa-check-circle"></i> %s <i class="fas fa-angle-right"></i> <i class="far fa-times-circle"></i> %s</small>',$lcSamAccountName.".bat",$lcScriptPath);
							}
						}
					}
					
					if(isSearchStrInStr($laEntries[$i]["dn"],"OU=Configuracion")==true && isSearchStrInStr($laEntries[$i]["dn"],"OU=Departamento de TI")){
						if(in_array(trim(strtolower($laEntries[$i]["samaccountname"][0])),$laAutorizados['configuracion'])==false){
							$laFault['1'] .= (empty($laFault['1'])?'':', ').$laEntries[$i]["cn"][0];
						}
					}
					
					if(isSearchStrInStr($laEntries[$i]["dn"],"OU=Temporales")==true && isSearchStrInStr($laEntries[$i]["dn"],"OU=Departamento de TI")){
						$laFault['2'] .= (empty($laFault['2'])?'':', ').$laEntries[$i]["cn"][0];
					}
					

					if(isSearchStrInStr($laEntries[$i]["dn"],"OU=SHAIO Equipos")==true){
						$laFault['3'] .= (empty($laFault['3'])?'':', ').$laEntries[$i]["cn"][0];
					}
					

					if(isSearchStrInStr($laEntries[$i]["dn"],"OU=SHAIO Usuarios")==false && 
					   isSearchStrInStr($laEntries[$i]["dn"],"OU=Departamento de TI")==false && 
					   isSearchStrInStr($laEntries[$i]["dn"],"OU=Usuarios Inactivos")==false){
						$laFault['4'] .= (empty($laFault['4'])?'':', ').$laEntries[$i]["cn"][0];
					}
											
				}
				if(!empty($laFault['1'])){ $lcHtml .= showCardInfo("warning","En configuraci&oacute;n",substr_count($laFault['1'],",")+1,$laFault['1']); $lcFallas = $lcFallas.(empty($lcFallas)?'':', ')."OU=Configuraci&oacute;n";}
				if(!empty($laFault['2'])){ $lcHtml .= showCardInfo("warning","En temporales",substr_count($laFault['2'],",")+1,$laFault['2']); $lcFallas = $lcFallas.(empty($lcFallas)?'':', ')."OU=Temporales"; $lnFallas+=(substr_count($laFault['2'],",")+1); }
				if(!empty($laFault['3'])){ $lcHtml .= showCardInfo("warning","Usuarios en equipos",substr_count($laFault['3'],",")+1,$laFault['3']); $lcFallas = $lcFallas.(empty($lcFallas)?'':', ')."OU=Shaio Equipos"; $lnFallas+=(substr_count($laFault['3'],",")+1); }	
				if(!empty($laFault['4'])){ $lcHtml .= showCardInfo("warning","Usuarios sin OU",substr_count($laFault['4'],",")+1,$laFault['4']); $lcFallas = $lcFallas.(empty($lcFallas)?'':', ')."! OU=Shaio Equipos"; $lnFallas+=(substr_count($laFault['4'],",")+1); }	
			
				
				
				// COMPUTADORES
				$LDAP_Search_String = "(&(objectCategory=computer)(objectClass=computer))";
				$LDAP_Search = ldap_search($lnLdapHandle, $lcLdapDc, $LDAP_Search_String);
				$laEntries = ldap_get_entries($lnLdapHandle, $LDAP_Search);
				
				$lcHtml .= showCardInfo("success","Equipos<br/><small><b>Dominio</b></small>",$laEntries["count"],"Controlados");
				
				$laFault['1']="";
				$laFault['2']="";
				$laFault['3']="";
				$laFault['4']="";					
				
				for ($i=0; $i<$laEntries["count"]; $i++) {

					
					if(isSearchStrInStr($laEntries[$i]["dn"],"OU=Computers")==true){
						if(in_array(trim(strtolower($laEntries[$i]["cn"][0])),$laAutorizados['computers'])==false){
							$laFault['1'] .= (empty($laFault['1'])?'':', ').$laEntries[$i]["cn"][0];
						}
					}

					if(isSearchStrInStr($laEntries[$i]["dn"],"OU=SHAIO Equipos")==false){
						if(in_array(trim(strtolower($laEntries[$i]["cn"][0])),$laAutorizados['computers'])==false){
							$laFault['2'] .= (empty($laFault['2'])?'':', ').$laEntries[$i]["cn"][0];
						}
					}							

					if(isSearchStrInStr($laEntries[$i]["dn"],"OU=SHAIO Usuarios")==true){
						$laFault['3'] .= (empty($laFault['3'])?'':', ').$laEntries[$i]["cn"][0];
					}						
				}					

				if(!empty($laFault['1'])){ $lcHtml .= showCardInfo("warning","En Computers",substr_count($laFault['1'],",")+1,$laFault['1']); $lcFallas = $lcFallas.(empty($lcFallas)?'':', ')."OU: Computers"; $lnFallas+=(substr_count($laFault['1'],",")+1); }
				if(!empty($laFault['2'])){ $lcHtml .= showCardInfo("warning","Equipos sin OU",substr_count($laFault['2'],",")+1,$laFault['2']);$lcFallas = $lcFallas.(empty($lcFallas)?'':', ')."!OU: Shaio Equipos"; $lnFallas+=(substr_count($laFault['2'],",")+1); }
				if(!empty($laFault['3'])){ $lcHtml .= showCardInfo("warning","Equipos en usuarios",substr_count($laFault['3'],",")+1,$laFault['3']); $lcFallas = $lcFallas.(empty($lcFallas)?'':', ')."OU=Shaio Equipos"; $lnFallas+=(substr_count($laFault['3'],",")+1); }						
				
				
				// USUARIOS BLOQUEADOS
				$LDAP_Search_String = "(&(objectCategory=Person)(objectClass=User)(lockoutTime>=1)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))";
				$LDAP_Search = ldap_search($lnLdapHandle, $lcLdapDc, $LDAP_Search_String);
				$laEntries = ldap_get_entries($lnLdapHandle, $LDAP_Search);
				
				$lcUsuariosBloqueados="";
				for ($i=0; $i<$laEntries["count"]; $i++) {
					$lcUsuariosBloqueados .= (empty($lcUsuariosBloqueados)?'':', ').$laEntries[$i]["cn"][0];
				}
				
				ldap_close($lnLdapHandle);
			}
		}
	}
	
	if($lnFallas>0){ $lcHtml .= showCardInfo("danger","Errores",$lnFallas,"Errores de asignación en las OU"); }	
	
	
	// ----------------------------
	// ZIMBRA
	// ----------------------------		
	
	$lcUID="emltYnJh";
	$lcUIDKey = "amZoSEcxRFpfeA==";
	$lcLdapServer = "172.16.3.3";
	$lnLdapServerPort="389";
	$lcDomainDC="dc=shaio,dc=org";
	$lcUsuariosBloqueadosZimbra="";
	
	if (!empty($lcUID) && !empty($lcUIDKey)){
		$lnLdapHandle = ldap_connect($lcLdapServer, $lnLdapServerPort);

		if ($lnLdapHandle){
			if($lnLdapHandle>0){
				ldap_set_option($lnLdapHandle, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option($lnLdapHandle, LDAP_OPT_REFERRALS, 0);
				$llLdapBind = @ldap_bind($lnLdapHandle, sprintf('uid=%s,cn=admins,cn=%s',base64_decode($lcUID),base64_decode($lcUID)), base64_decode($lcUIDKey));
				
				if ($llLdapBind){
					if ($llLdapBind==true){
						$lcAttributes = array("homephone","pager","title","telephonenumber","description","mobile","company","st","zimbraaccountstatus","displayname","zimbraprefmailsignaturehtml","zimbraMailStatus"); 

						// CUENTAS
						$lcFilter = "(&(objectClass=zimbraAccount))";
						$loResult = ldap_search($lnLdapHandle, $lcDomainDC, $lcFilter, $lcAttributes);
						$laEntries = ldap_get_entries($lnLdapHandle, $loResult);
												
						if(isset($laEntries['count'])){
							if($laEntries['count']>0){
								
								$laZimbraStatus=array();
								foreach($laEntries as $laEntry){
									if(!empty($laEntry['zimbraaccountstatus'][0]) && trim(strtolower($laEntry['zimbraaccountstatus'][0]))<>'lockout'){
										if(isset($laZimbraStatus[$laEntry['zimbraaccountstatus'][0]])==false){
											$laZimbraStatus[$laEntry['zimbraaccountstatus'][0]]=0;
										}
										$laZimbraStatus[$laEntry['zimbraaccountstatus'][0]]+=1;
									}
								}
								$lcZimbraStatus="";
								foreach($laZimbraStatus as $lcKey => $lcValue){
									$lcZimbraStatus.= sprintf('<span class="badge badge-%s">%s: %s</span> ',($lcKey=='active'?'success':($lcKey=='locked'?'danger':'secondary')),$lcKey,$lcValue);
								}
								
								$lcHtml .= showCardInfo("success","Cuentas e-mail<br/><small><b>Zimbra</b></small>",$laEntries['count'],$lcZimbraStatus); 
							}
						}
						
						// BLOQUEADOS
						$lcFilter = "(&(zimbraAccountStatus=lockout)(zimbraMailStatus=enabled))";
						$loResult = ldap_search($lnLdapHandle, $lcDomainDC, $lcFilter, $lcAttributes);
						$laEntries = ldap_get_entries($lnLdapHandle, $loResult);
												
						if(isset($laEntries['count'])){
							if($laEntries['count']>0){
								foreach($laEntries as $laEntry){
									if(in_array(trim(strtolower($laEntry['displayname'][0])),$laAutorizados['mail'])==false){
										$lcUsuariosBloqueadosZimbra .= (empty($lcUsuariosBloqueadosZimbra)?'':', ').$laEntry['displayname'][0];
									}
								}
								if(!empty($lcUsuariosBloqueadosZimbra)){ $lcHtml .= showCardInfo("warning","Bloqueados<br/><small><b>Zimbra</b></small>",substr_count($lcUsuariosBloqueadosZimbra,",")+1,"Cuentas e-mail"); }
							}
						}
						
					}
				}
				ldap_close($lnLdapHandle);
			}
		}
	}					
	
	
?><!doctype html>
<html lang="en" class="h-100">
	<head>
		<!-- Cabecera -->
		<?php
			$lcInclude = file_get_contents("../../publico/head.php");
			$lcInclude = str_replace("publico-complementos","../publico-complementos",$lcInclude);
			$lcInclude = str_replace("publico-css","../publico-css",$lcInclude);
			$lcInclude = str_replace("publico-ico","../publico-ico",$lcInclude);
			$lcInclude = str_replace("vista-comun","../vista-comun",$lcInclude);
			$lcInclude = str_replace("hcw-manifiest.json.php","../hcw-manifiest.json.php",$lcInclude);
			print($lcInclude);
		?>
	
		<style>
			body{
				background-color: #1f1d1d !important;
			}
			.card{
			    background-color: transparent !important;
			}		
			.badge-big {
				font-size: 20px !important;
			}
			.badge-warning-light {
				color: #c7c7c7;
				background-color: #584304;
			}
			.text-warning-light {
				color: #584304!important;
			}			
		</style>

	</head>
	<body class="h-100">
		<div class="container-fluid h-100">
			<div class="row h-100">
				<div class="container-fluid">
					<div class="card mt-3">
						<div id="banner" class="card-header text-white <?php print($lnFallas>0?"bg-danger":"bg-success"); ?>">
							<h2><i class="fas fa-network-wired"></i> Monitor LDAP <div class="float-right"><?php print(NUCLEO\AplicacionFunciones::getBrowserName($_SERVER['HTTP_USER_AGENT'])); ?></div></h2>
						</div>
						<div class="card-body">	
							<div class="card-deck mb-2 text-center">
								<div class="row w-100 h-100"><?php print($lcHtml); ?>							
								</div>
							</div>
						</div>
					</div>
					
					<?php if($lnFallas>0){ ?>
					<div class="row mb-5">
						<div class="col">					
							<div class="media">
								<span class="badge badge-danger mr-2" style="font-size: 73px; min-width: 145px;"><?php print($lnFallas); ?></span>
								<div class="media-body">
									<h2 class="text-danger"><i class="fas fa-bomb"></i> Existe elementos con error </h2>
									<?php 
										foreach(explode(",",$lcFallas) as $lcFalla){ 
											printf('<span class="badge badge-big badge-danger mb-1">%s</span> ',$lcFalla);
										}
									?>
									<iframe src="bell-ringing-01.mp3" allow="autoplay" id="audio" style="display: none"></iframe>
									<audio id="player" autoplay loop>
										<source src="bell-ringing-01.mp3" type="audio/mp3">
									</audio>									
								</div>
							</div>	
						</div>						
					</div>					
					<?php } ?>
								
					<?php if(!empty($lcUsuariosBloqueados)){ ?>
					<div class="row mb-2">
						<div class="col">
							<div class="media">
								<span class="badge badge-warning mr-2" style="font-size: 73px; min-width: 145px;"><?php print(substr_count($lcUsuariosBloqueados,",")+1); ?></span>
								<div class="media-body">
									<h2 class="text-warning"><i class="fas fa-users-slash"></i> Usuarios activos bloqueados en el dominio</h2>
									<?php 
										foreach(explode(",",$lcUsuariosBloqueados) as $lcUsuarioBloeado){ 
											printf('<span class="badge badge-big badge-warning mb-1">%s</span> ',$lcUsuarioBloeado);
										}
									?>
								</div>
							</div>
						</div>						
					</div>
					<?php } ?>
					
					<?php if(!empty($lcUsuariosScriptDistinto)){ ?>
					<div class="row mb-2">
						<div class="col">
							<div class="media">
								<span class="badge badge-warning mr-2" style="font-size: 73px; min-width: 145px;"><?php print(substr_count($lcUsuariosScriptDistinto,",")+1); ?></span>
								<div class="media-body">
									<h2 class="text-warning"><i class="fas fa-users-slash"></i> Usuarios con script de inicio distinto al nombre de usuario</h2>
									<?php 
										foreach(explode(",",$lcUsuariosScriptDistinto) as $lcUsuarioScriptDistinto){ 
											printf('<span class="badge badge-big badge-warning mb-1">%s</span> ',$lcUsuarioScriptDistinto);
										}
									?>
								</div>
							</div>
						</div>						
					</div>
					<?php } ?>					

					<?php if(!empty($lcUsuariosBloqueadosZimbra)){ ?>
					<div class="row mb-2">
						<div class="col">
							<div class="media">
								<span class="badge badge-warning-light mr-2" style="font-size: 73px; min-width: 145px;"><?php print(substr_count($lcUsuariosBloqueadosZimbra,",")+1); ?></span>
								<div class="media-body">
									<h4 class="text-warning-light"><i class="fas fa-envelope"></i> Usuarios activos bloqueados en Zimbra</h4>
									<?php 
										foreach(explode(",",$lcUsuariosBloqueadosZimbra) as $lcUsuarioBloeado){ 
											printf('<span class="badge badge-warning-light">%s</span> ',$lcUsuarioBloeado);
										}
									?>
								</div>
							</div>
						</div>						
					</div>
					<?php  } ?>

				</div>
			</div>
		</div>
		<div style="position: fixed; float:right; bottom: 0; right: 0; padding-right: 8px; padding-bottom: 8px;">
			<button id="cmdActualizar" class="btn btn-sm btn-outline-secondary"><i class="fas fa-sync fa-spin"></i> <span class="messageActualizar"></span></button>
		</div>

		<script type="text/javascript">
			$(document).ready(function(){
				"use strict";
				var lnTime=0;
				var lnTimeMaxSeg = <?php print($lnTimeMaxSeg); ?>;

				setTimeout(function(){
					location.reload(true);
				}, 1000 *lnTimeMaxSeg);

				setInterval(function() {
					lnTime+=1;
					$('.messageActualizar').html((lnTimeMaxSeg-lnTime));
				}, 1000);

				$( "#cmdActualizar" ).click(function() {
					location.reload(true);
				});

			});		
		</script>		
	</body>
</html>
<?php 
	function isSearchStrInStr($tcSearchIn, $tcSearch, $tlATC=true, $tlNoAcute=true, $tlInOrder=true){
		try{
			$llResult	= true;
			$tcSearchIn	= ($tlATC==true)?strtolower($tcSearchIn):$tcSearchIn;
			$tcSearch	= ($tlATC==true)?strtolower($tcSearch):$tcSearch;
			$tcSearchIn	= ($tlNoAcute==true)?getNoAcute($tcSearchIn):$tcSearchIn;
			$tcSearch	= ($tlNoAcute==true)?getNoAcute($tcSearch):$tcSearch;
			$tcSearch	= str_replace("%","% ",$tcSearch);
			$laSearch	= explode('%',$tcSearch);
			
			foreach ($laSearch as $lcValue) {
				$lcValue=trim($lcValue);
				if(!empty($lcValue)){
					$lnStrPos	= strpos($tcSearchIn,$lcValue);
					$llResult	= ($lnStrPos===false) ? false : $llResult;
					$tcSearchIn	= ($tlInOrder==true)?($llResult==true)?substr($tcSearchIn,$lnStrPos+strlen($lcValue)+1):$tcSearchIn:$tcSearchIn;
				}
			}
		} catch (Exception $e) {
			$llResult=false;
		}
		return $llResult;
	}
	function getNoAcute($tcString) {
		$laNoCharSetU 	= array('Á' => 'A','É' => 'E','Í' => 'I','Ó' => 'O','Ú' => 'U'); 
		$laNoCharSetL 	= array('á' => 'a','é' => 'e','í' => 'i','ó' => 'o','ú' => 'u'); 
		$tcString		= strtr($tcString, $laNoCharSetU);
		$tcString		= strtr($tcString, $laNoCharSetL);
		return $tcString; 
	}
	function showCardInfo($tcStyle="secondary",$tcTitulo="", $tcValor="", $tcInfo=""){
		$lcHTMLmonitor = "";
		$lcHTMLmonitor .= sprintf('<div class="col-12 col-md-6 col-lg-3 pl-0 pr-0 pb-3"><div class="card card-monitor text-%s border-%s">',$tcStyle,$tcStyle);
		$lcHTMLmonitor .= sprintf('<div class="card-body">');
		$lcHTMLmonitor .= sprintf('<div class="card-title text-%s">',$tcStyle);
		$lcHTMLmonitor .= sprintf('<ul class="list-unstyled">');
		$lcHTMLmonitor .= sprintf('<li class="media">');
		$lcHTMLmonitor .= sprintf(isSearchStrInStr(trim(strtolower($tcTitulo)),'usuarios')==true?'<i class="fas fa-users fa-5x"></i>':(isSearchStrInStr(trim(strtolower($tcTitulo)),'equipos')==true?'<i class="fas fa-desktop fa-5x"></i>':(isSearchStrInStr(trim(strtolower($tcTitulo)),'zimbra')==true?'<i class="fas fa-envelope fa-5x"></i>':'<i class="fas fa-tag fa-5x"></i>')));
		$lcHTMLmonitor .= sprintf('<div class="media-body text-left ml-5">');
		$lcHTMLmonitor .= sprintf('<span class="mt-0 mb-1"><h3>%s</h3></span>', $tcTitulo);
		$lcHTMLmonitor .= sprintf('<h1 class="font-weight-bold mb-0 evento" style="font-size:63px;">%s</h1>',$tcValor);
		$lcHTMLmonitor .= sprintf('<p class="card-text">%s</p>',$tcInfo);
		$lcHTMLmonitor .= sprintf('</div></li></ul></div>');

				
		$lcHTMLmonitor .= sprintf('</div></div></div>');
		
		return $lcHTMLmonitor;
	}	
?>