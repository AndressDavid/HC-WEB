<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');
	require_once (__DIR__ .'/../../../publico/headJSCRIPT.php');
		
	$lnBloqueInicio = intval(isset($_GET['nBloqueInicio'])?$_GET['nBloqueInicio']:0);
	$lnTimeMaxSeg = intval(isset($_GET['nTimeMaxSeg'])?$_GET['nTimeMaxSeg']:0);
	
?>$(document).ready(function(){
	"use strict";
	var lnTime=0;
	var lnTimeMaxSeg = <?php print($lnTimeMaxSeg); ?>;

	setTimeout(function(){
		location.href='?nBloqueInicio=<?php print($lnBloqueInicio); ?>';
	}, 1000 *lnTimeMaxSeg);

	setInterval(function() {
		lnTime+=1;
		$('.messageActualizar').html((lnTimeMaxSeg-lnTime));
	}, 1000);

	$( "#cmdActualizar" ).click(function() {
		location.href='?nBloqueInicio=<?php print($lnBloqueInicio); ?>';
	});
});