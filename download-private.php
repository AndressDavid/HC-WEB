<?php
	require_once (__DIR__ .'/nucleo/controlador/class.Documento.php');
	require_once (__DIR__ .'/nucleo/publico/constantes.php');
	require_once (__DIR__ .'/restapi/client/addons/RestClient/restclient.php');

	$laWebResoucesConfig = require (__DIR__ .'/nucleo/privada/webResoucesConfig.php');
	$lcAccion = (isset($_GET['accion'])?trim(strtolower($_GET['accion'])):'');
	$lcFileSource='';
	$llFileSourceExist = false;

	$lcError = "Archivo no encontrado";

	switch ($lcAccion){
		case 'paciente-historia':
			if(isset($_GET['documento-tipo'])==true && isset($_GET['documento-numero'])==true && isset($_GET['ingreso'])==true){

				$lnIngreso=intval($_GET['ingreso']);
				$lcId = strval($_GET['documento-tipo']);
				$lnId = intval($_GET['documento-numero']);
				$lcToken = 'jg'.md5('abc::'.trim(mb_strtolower(strval($lcId)))."::".trim(mb_strtolower(strval($lnId)))."::".trim(mb_strtolower(strval($lnIngreso))));

				if((isset($_GET['token'])?$_GET['token']:'')==$lcToken){
					$lcArchivo = 'librohc-'.$lnIngreso.'-'.$lcId.'-'.$lnId.'-'.uniqid().'.pdf';
					// Colocar password en el archivo si upd es igual a 1 o no llega
					$lcPassword = ($_GET['upd']??'1')=='1' ? sprintf('%s-%s', trim(mb_strtoupper(strval($lcId))), trim(mb_strtoupper(strval($lnId)))) : null;
					$loDocLibro = new NUCLEO\Documento();

					ini_set('max_execution_time', 60*60); // 60 minutos de consulta
					$lcContenido = '';
					try {
						$lcContenido = $loDocLibro->generarPDFxIngreso($lnIngreso, $lcArchivo, 'S', $lcPassword, true);
					} catch (Exception $e) {
						$lcContenido = '';
					} finally {
						$llFileSourceExist = true;
						if(gettype($lcContenido)!='string' || empty($lcContenido)){
							$lcContenido = base64_decode("JVBERi0xLjQKJcfsj6IKNSAwIG9iago8PC9MZW5ndGggNiAwIFIvRmlsdGVyIC9GbGF0ZURlY29kZT4+CnN0cmVhbQp4nKWRuw7CMAxFd3+FxzIQ7ER5eASJhQ3UDbFQykOiPCW+n7YSckFFDCTLUW6ufZ1ckQxbpGa/oKiAcQeOPYZEYkJES4Ri8VbCFsj4Wr1CMq5ZraXLRYWTHEaLiGIkYN44RMSl0MqMLpqEydb9arWCZcaDoTUxinCGXzAQrfIZTHOYt+F8JxyT13Tsgyrd89BxOGujzvPfNIE/phlr7ptiobhXPCg+FM+9j3D6dWHTW/eueOmtcOq1rRWPiuXbR8zhCbDddUJlbmRzdHJlYW0KZW5kb2JqCjYgMCBvYmoKMTk4CmVuZG9iago0IDAgb2JqCjw8L1R5cGUvUGFnZS9NZWRpYUJveCBbMCAwIDU5NSA4NDJdCi9Sb3RhdGUgMC9QYXJlbnQgMyAwIFIKL1Jlc291cmNlczw8L1Byb2NTZXRbL1BERiAvVGV4dF0KL0ZvbnQgOCAwIFIKPj4KL0NvbnRlbnRzIDUgMCBSCj4+CmVuZG9iagozIDAgb2JqCjw8IC9UeXBlIC9QYWdlcyAvS2lkcyBbCjQgMCBSCl0gL0NvdW50IDEKPj4KZW5kb2JqCjEgMCBvYmoKPDwvVHlwZSAvQ2F0YWxvZyAvUGFnZXMgMyAwIFIKL01ldGFkYXRhIDkgMCBSCj4+CmVuZG9iago4IDAgb2JqCjw8L1I3CjcgMCBSPj4KZW5kb2JqCjcgMCBvYmoKPDwvQmFzZUZvbnQvQ291cmllci9UeXBlL0ZvbnQKL1N1YnR5cGUvVHlwZTE+PgplbmRvYmoKOSAwIG9iago8PC9UeXBlL01ldGFkYXRhCi9TdWJ0eXBlL1hNTC9MZW5ndGggMTU2MD4+c3RyZWFtCjw/eHBhY2tldCBiZWdpbj0n77u/JyBpZD0nVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkJz8+Cjw/YWRvYmUteGFwLWZpbHRlcnMgZXNjPSJDUkxGIj8+Cjx4OnhtcG1ldGEgeG1sbnM6eD0nYWRvYmU6bnM6bWV0YS8nIHg6eG1wdGs9J1hNUCB0b29sa2l0IDIuOS4xLTEzLCBmcmFtZXdvcmsgMS42Jz4KPHJkZjpSREYgeG1sbnM6cmRmPSdodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjJyB4bWxuczppWD0naHR0cDovL25zLmFkb2JlLmNvbS9pWC8xLjAvJz4KPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9J3V1aWQ6NGYzNjk5YjctN2ViOC0xMWViLTAwMDAtOGRhMjA5NWY3ODdhJyB4bWxuczpwZGY9J2h0dHA6Ly9ucy5hZG9iZS5jb20vcGRmLzEuMy8nPjxwZGY6UHJvZHVjZXI+R1BMIEdob3N0c2NyaXB0IDkuMTA8L3BkZjpQcm9kdWNlcj4KPHBkZjpLZXl3b3Jkcz4oKTwvcGRmOktleXdvcmRzPgo8L3JkZjpEZXNjcmlwdGlvbj4KPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9J3V1aWQ6NGYzNjk5YjctN2ViOC0xMWViLTAwMDAtOGRhMjA5NWY3ODdhJyB4bWxuczp4bXA9J2h0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8nPjx4bXA6TW9kaWZ5RGF0ZT4yMDIxLTAzLTAzVDE1OjEyOjA3LTA1OjAwPC94bXA6TW9kaWZ5RGF0ZT4KPHhtcDpDcmVhdGVEYXRlPjIwMjEtMDMtMDNUMTU6MTI6MDctMDU6MDA8L3htcDpDcmVhdGVEYXRlPgo8eG1wOkNyZWF0b3JUb29sPlBERkNyZWF0b3IgVmVyc2lvbiAxLjcuMzwveG1wOkNyZWF0b3JUb29sPjwvcmRmOkRlc2NyaXB0aW9uPgo8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0ndXVpZDo0ZjM2OTliNy03ZWI4LTExZWItMDAwMC04ZGEyMDk1Zjc4N2EnIHhtbG5zOnhhcE1NPSdodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vJyB4YXBNTTpEb2N1bWVudElEPSd1dWlkOjRmMzY5OWI3LTdlYjgtMTFlYi0wMDAwLThkYTIwOTVmNzg3YScvPgo8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0ndXVpZDo0ZjM2OTliNy03ZWI4LTExZWItMDAwMC04ZGEyMDk1Zjc4N2EnIHhtbG5zOmRjPSdodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLycgZGM6Zm9ybWF0PSdhcHBsaWNhdGlvbi9wZGYnPjxkYzp0aXRsZT48cmRmOkFsdD48cmRmOmxpIHhtbDpsYW5nPSd4LWRlZmF1bHQnPkFyY2hpdm8gbm8gZGlzcG9uaWJsZTwvcmRmOmxpPjwvcmRmOkFsdD48L2RjOnRpdGxlPjxkYzpjcmVhdG9yPjxyZGY6U2VxPjxyZGY6bGk+KCk8L3JkZjpsaT48L3JkZjpTZXE+PC9kYzpjcmVhdG9yPjxkYzpkZXNjcmlwdGlvbj48cmRmOlNlcT48cmRmOmxpPigpPC9yZGY6bGk+PC9yZGY6U2VxPjwvZGM6ZGVzY3JpcHRpb24+PC9yZGY6RGVzY3JpcHRpb24+CjwvcmRmOlJERj4KPC94OnhtcG1ldGE+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKPD94cGFja2V0IGVuZD0ndyc/PgplbmRzdHJlYW0KZW5kb2JqCjIgMCBvYmoKPDwvUHJvZHVjZXIoR1BMIEdob3N0c2NyaXB0IDkuMTApCi9DcmVhdGlvbkRhdGUoRDoyMDIxMDMwMzE1MTIwNy0wNScwMCcpCi9Nb2REYXRlKEQ6MjAyMTAzMDMxNTEyMDctMDUnMDAnKQovQXV0aG9yKCkKL0NyZWF0b3IoXDM3NlwzNzdcMDAwUFwwMDBEXDAwMEZcMDAwQ1wwMDByXDAwMGVcMDAwYVwwMDB0XDAwMG9cMDAwclwwMDAgXDAwMFZcMDAwZVwwMDByXDAwMHNcMDAwaVwwMDBvXDAwMG5cMDAwIFwwMDAxXDAwMC5cMDAwN1wwMDAuXDAwMDMpCi9LZXl3b3JkcygpCi9TdWJqZWN0KCkKL1RpdGxlKFwzNzZcMzc3XDAwMEFcMDAwclwwMDBjXDAwMGhcMDAwaVwwMDB2XDAwMG9cMDAwIFwwMDBuXDAwMG9cMDAwIFwwMDBkXDAwMGlcMDAwc1wwMDBwXDAwMG9cMDAwblwwMDBpXDAwMGJcMDAwbFwwMDBlKT4+ZW5kb2JqCnhyZWYKMCAxMAowMDAwMDAwMDAwIDY1NTM1IGYgCjAwMDAwMDA1MDIgMDAwMDAgbiAKMDAwMDAwMjI5MyAwMDAwMCBuIAowMDAwMDAwNDQzIDAwMDAwIG4gCjAwMDAwMDAzMDIgMDAwMDAgbiAKMDAwMDAwMDAxNSAwMDAwMCBuIAowMDAwMDAwMjgzIDAwMDAwIG4gCjAwMDAwMDA1OTUgMDAwMDAgbiAKMDAwMDAwMDU2NiAwMDAwMCBuIAowMDAwMDAwNjU3IDAwMDAwIG4gCnRyYWlsZXIKPDwgL1NpemUgMTAgL1Jvb3QgMSAwIFIgL0luZm8gMiAwIFIKL0lEIFs8MDNDMjkzNDE1OEM0MjM3RjY0MTU2MzA4QzkxMUMxNUE+PDAzQzI5MzQxNThDNDIzN0Y2NDE1NjMwOEM5MTFDMTVBPl0KPj4Kc3RhcnR4cmVmCjI3MTAKJSVFT0YK");
						}
						writeDownloadFile('pdf', $lcArchivo, $lcArchivo, strlen($lcContenido), $lcContenido);
					}

				}else{
					$lcError = "Acceso denegado";
				}
			}else{
				$lcError = "Faltan parametros";
			}
			break;

	}

	if($llFileSourceExist==false){
		writeDownloadFile('txt', 'portal-hcw.txt', 'portal-hcw.txt', 0, $lcError);
	}


	function writeDownloadFile($tcContentType='txt', $tcFileSource='unknown.txt', $tcFileOut='unknown.txt', $tnFileSize=0, $tcFileContent=''){
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
		header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
		header("Allow: GET, POST, OPTIONS, PUT, DELETE");

		header(sprintf('Content-Type: %s',$tcContentType));
		header(sprintf("Content-Disposition: attachment; filename=pp-%s",$tcFileOut));
		header('Pragma: no-cache');
		header(sprintf('Content-Length: %s', $tnFileSize));
		if($tcFileContent<>'*'){
			echo $tcFileContent;
		}else{
			if(file_exists($tcFileSource)==true){
				$loFile = fopen($tcFileSource,"r");
				while (!feof($loFile)) {
				   echo fread($loFile, 1024);
				}
				fclose($loFile);
			}else{
				echo 'unknown';
			}
		}
	}
