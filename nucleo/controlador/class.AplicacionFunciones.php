<?php
namespace NUCLEO;

class AplicacionFunciones
{

	/*
	 * Retorna string de al menos 40 caracteres con cifrado dependiendo del tipo de entrada
	 *
	 * @param string $tcType: Tipo de token, INSTANTE: Aleatorio, SEMANA: Cifrado utilizando la semana del año y mes
	 *
	 * Ejemplo,
	 *	- Ruta absoluta desde un directorio especifico
	 *		AplicacionFunciones::getAbsolutePath('hcw-xxxx//nucleo//vista//programacion-salas//monitor-test.php','nucleo');
	 *		// el resultado es: 'nucleo\vista\programacion-salas\monitor-test.php'
	 *
	 *	- Ruta absoluta
	 *		AplicacionFunciones::getAbsolutePath('hcw-xxxx\nucleo\vista\programacion-salas\monitor-test.php','nucleo');
	 *		// el resultado es: 'hcw-xxxx\nucleo\vista\programacion-salas\monitor-test.php'
	 *
	 */
	public static function getToken($tcBase='', $tcType='INSTANTE'){
		$tcType = trim(strtoupper(strval($tcType)));
		$tcBase = trim(strval($tcBase)); $tcBase= (empty($tcBase)?date('WYm'):$tcBase);
		$lcToken='';

		switch($tcType){
			case 'INSTANTE':
				$lcToken = substr(bin2hex(openssl_random_pseudo_bytes(20)),0,40);
				break;

			case 'SEMANA':
				$lcToken = sha1(openssl_encrypt($tcBase, 'aes-256-ofb', sha1($tcBase), false, substr('a4g11374cxfyfh2k'.sha1($tcBase),0,16)));
				break;
		}

		return $lcToken;
	}


	/*
	 * Retorna el path absoluto de una ruta, se puede especificar un segmento de inicio
	 *
	 * @param string $tcPath: Ruta
	 * @param string $tcPathTree: directorio desde donde se empieza almacenar
	 * @return string Texto absoluta
	 *
	 * Ejemplo,
	 *	- Ruta absoluta desde un directorio especifico
	 *		AplicacionFunciones::getAbsolutePath('hcw-xxxx//nucleo//vista//programacion-salas//monitor-test.php','nucleo');
	 *		// el resultado es: 'nucleo\vista\programacion-salas\monitor-test.php'
	 *
	 *	- Ruta absoluta
	 *		AplicacionFunciones::getAbsolutePath('hcw-xxxx\nucleo\vista\programacion-salas\monitor-test.php','nucleo');
	 *		// el resultado es: 'hcw-xxxx\nucleo\vista\programacion-salas\monitor-test.php'
	 *
	 */
    public static function getAbsolutePath($tcPath='', $tcPathTree='') {
		$llPathTree = (empty($tcPathTree));
        $tcPath = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $tcPath);
        $laParts = array_filter(explode(DIRECTORY_SEPARATOR, $tcPath), 'strlen');
        $laAbsolutes = array();
        foreach ($laParts as $lnPart => $lcPart) {
            if ('.' == $lcPart) continue;
            if ('..' == $lcPart) {
                array_pop($laAbsolutes);
            } else {
				$llPathTree = (trim(strtolower(strval($tcPathTree)))==trim(strtolower(strval($lcPart)))?true:$llPathTree);

				if($llPathTree==true){
					$laAbsolutes [] = $lcPart;
				}
            }
        }
        return implode(DIRECTORY_SEPARATOR, $laAbsolutes );
    }


	// Retorna el nombre del navegador
	public static function getBrowserName($tcUserAgent=''){
		$tcUserAgent = (empty($tcUserAgent)?(isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'*'):strval($tcUserAgent));
		$tcUserAgent = strtolower($tcUserAgent);
		$tcUserAgent = ' ' . $tcUserAgent;

		// Humans / Regular Users
		if     (strpos($tcUserAgent, 'opera'     ) || strpos($tcUserAgent, 'opr/')     ) return 'Opera'            ;
		elseif (strpos($tcUserAgent, 'edge'      )                           ) return 'Edge'             ;
		elseif (strpos($tcUserAgent, 'chrome'    )                           ) return 'Chrome'           ;
		elseif (strpos($tcUserAgent, 'safari'    )                           ) return 'Safari'           ;
		elseif (strpos($tcUserAgent, 'firefox'   )                           ) return 'Firefox'          ;
		elseif (strpos($tcUserAgent, 'msie'      ) || strpos($tcUserAgent, 'trident/7')) return 'Internet Explorer';

		// Search Engines
		elseif (strpos($tcUserAgent, 'google'    )                           ) return '[Bot] Googlebot'   ;
		elseif (strpos($tcUserAgent, 'bing'      )                           ) return '[Bot] Bingbot'     ;
		elseif (strpos($tcUserAgent, 'slurp'     )                           ) return '[Bot] Yahoo! Slurp';
		elseif (strpos($tcUserAgent, 'duckduckgo')                           ) return '[Bot] DuckDuckBot' ;
		elseif (strpos($tcUserAgent, 'baidu'     )                           ) return '[Bot] Baidu'       ;
		elseif (strpos($tcUserAgent, 'yandex'    )                           ) return '[Bot] Yandex'      ;
		elseif (strpos($tcUserAgent, 'sogou'     )                           ) return '[Bot] Sogou'       ;
		elseif (strpos($tcUserAgent, 'exabot'    )                           ) return '[Bot] Exabot'      ;
		elseif (strpos($tcUserAgent, 'msn'       )                           ) return '[Bot] MSN'         ;

		// Common Tools and Bots
		elseif (strpos($tcUserAgent, 'mj12bot'   )                           ) return '[Bot] Majestic'     ;
		elseif (strpos($tcUserAgent, 'ahrefs'    )                           ) return '[Bot] Ahrefs'       ;
		elseif (strpos($tcUserAgent, 'semrush'   )                           ) return '[Bot] SEMRush'      ;
		elseif (strpos($tcUserAgent, 'rogerbot'  ) || strpos($tcUserAgent, 'dotbot')   ) return '[Bot] Moz or OpenSiteExplorer';
		elseif (strpos($tcUserAgent, 'frog'      ) || strpos($tcUserAgent, 'screaming')) return '[Bot] Screaming Frog';

		// Miscellaneous
		elseif (strpos($tcUserAgent, 'facebook'  )                           ) return '[Bot] Facebook'     ;
		elseif (strpos($tcUserAgent, 'pinterest' )                           ) return '[Bot] Pinterest'    ;

		// Check for strings commonly used in bot user agents
		elseif (strpos($tcUserAgent, 'crawler' ) || strpos($tcUserAgent, 'api'    ) ||
				strpos($tcUserAgent, 'spider'  ) || strpos($tcUserAgent, 'http'   ) ||
				strpos($tcUserAgent, 'bot'     ) || strpos($tcUserAgent, 'archive') ||
				strpos($tcUserAgent, 'info'    ) || strpos($tcUserAgent, 'data'   )    ) return '[Bot] Other'   ;

		return 'Other (Unknown)';
	}

	public static function serverProperty($tcProperty=''){
		return (isset($_SERVER[$tcProperty])?$_SERVER[$tcProperty]:'UNKNOWN');
	}

	public static function serverIp(){
		$lcIp = 'UNKNOWN';
		if(isset($_SERVER['SERVER_ADDR'])){
			$lcIp = $_SERVER['SERVER_ADDR'];
			$lcIp = ($lcIp=='::1'?gethostbyname($_SERVER['SERVER_NAME']):$lcIp);
		}
		return $lcIp;
	}

	public static function localIp(){
		$lcIp = 'UNKNOWN';
		$laKeys=array('HTTP_CLIENT_IP','HTTP_X_FORWARDED_FOR','HTTP_X_FORWARDED','HTTP_FORWARDED_FOR','HTTP_FORWARDED','REMOTE_ADDR');

		foreach($laKeys as $lcKey){
			if(isset($_SERVER[$lcKey])){
				if (!empty($_SERVER[$lcKey]) && filter_var($_SERVER[$lcKey], FILTER_VALIDATE_IP)){
					$lcIp = $_SERVER[$lcKey];
					break;
				}
			}
		}
		$lcIp = ($lcIp=='::1'?gethostbyname($_SERVER['SERVER_NAME']):$lcIp);

		return $lcIp;
	}

	function isSearchStrInStr($tcSearchIn, $tcSearch, $tlATC=true, $tlNoAcute=true, $tlInOrder=true){
		$llResult	= true;

		try{
			$tcSearchIn	= ($tlATC==true)?strtolower($tcSearchIn):$tcSearchIn;
			$tcSearch	= ($tlATC==true)?strtolower($tcSearch):$tcSearch;
			$tcSearchIn	= ($tlNoAcute==true)?$this->getNoAcute($tcSearchIn):$tcSearchIn;
			$tcSearch	= ($tlNoAcute==true)?$this->getNoAcute($tcSearch):$tcSearch;
			$tcSearch	= str_replace('%','% ',$tcSearch);
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
		$laNormalizeChars = array(
			'A'=>'A', 'A'=>'A', 'À'=>'A', 'Ã'=>'A', 'Á'=>'A', 'Æ'=>'A', 'Â'=>'A', 'Å'=>'A', 'Ä'=>'Ae',
			'Þ'=>'B',
			'C'=>'C', '?'=>'C', 'Ç'=>'C',
			'È'=>'E', 'E'=>'E', 'É'=>'E', 'Ë'=>'E', 'Ê'=>'E',
			'G'=>'G',
			'I'=>'I', 'Ï'=>'I', 'Î'=>'I', 'Í'=>'I', 'Ì'=>'I',
			'L'=>'L',
			'Ñ'=>'N', 'N'=>'N',
			'Ø'=>'O', 'Ó'=>'O', 'Ò'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'Oe',
			'S'=>'S', 'S'=>'S', '?'=>'S', 'Š'=>'S',
			'?'=>'T',
			'Ù'=>'U', 'Û'=>'U', 'Ú'=>'U', 'Ü'=>'Ue',
			'Ý'=>'Y',
			'Z'=>'Z', 'Ž'=>'Z', 'Z'=>'Z',
			'â'=>'a', 'a'=>'a', 'a'=>'a', 'á'=>'a', 'a'=>'a', 'ã'=>'a', 'A'=>'a', '?'=>'a', '?'=>'a', 'å'=>'a', 'à'=>'a', '?'=>'a', '?'=>'a', 'A'=>'a', '?'=>'a', 'a'=>'a', 'ä'=>'ae', 'æ'=>'ae', '?'=>'ae', '?'=>'ae',
			'c'=>'c', 'C'=>'c', 'C'=>'c', 'c'=>'c', 'ç'=>'c', '?'=>'c', '?'=>'c', 'c'=>'c', '?'=>'c', 'C'=>'c', 'c'=>'c', '?'=>'ch', '?'=>'ch',
			'g'=>'g', 'G'=>'g', 'G'=>'g', 'G'=>'g', '?'=>'g', '?'=>'g', 'g'=>'g', 'g'=>'g', '?'=>'g', '?'=>'g', '?'=>'g', 'g'=>'g',
			'î'=>'i', 'ï'=>'i', 'í'=>'i', 'ì'=>'i', 'i'=>'i', 'i'=>'i', 'i'=>'i', 'I'=>'i', '?'=>'i', 'i'=>'i', 'i'=>'i', 'I'=>'i', 'I'=>'i', '?'=>'i', 'I'=>'i', '?'=>'i', '?'=>'i', 'I'=>'i', '?'=>'i', '?'=>'i', '?'=>'i', 'i'=>'i', '?'=>'ij', '?'=>'ij',
			'ñ'=>'n', '?'=>'n', 'N'=>'n', '?'=>'n', '?'=>'n', '?'=>'n', '?'=>'n', 'n'=>'n', '?'=>'n', 'n'=>'n', '?'=>'n', 'N'=>'n', 'n'=>'n',
			'r'=>'r', 'r'=>'r', 'R'=>'r', 'r'=>'r', 'R'=>'r', '?'=>'r', 'R'=>'r', '?'=>'r', '?'=>'r',
			'u'=>'u', '?'=>'u', 'U'=>'u', 'u'=>'u', 'U'=>'u', 'u'=>'u', 'U'=>'u', 'U'=>'u', 'u'=>'u', 'U'=>'u', 'u'=>'u', 'U'=>'u', 'U'=>'u', 'u'=>'u', 'u'=>'u', 'U'=>'u', 'U'=>'u', 'u'=>'u', 'U'=>'u', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', '?'=>'u', 'u'=>'u', 'u'=>'u', 'U'=>'u', 'U'=>'u', 'u'=>'u', 'u'=>'u', 'ü'=>'ue'
		);
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$tcString = strtr($tcString, $laNormalizeChars);
		}else{
			if (strpos($tcString = htmlentities($tcString, ENT_QUOTES, 'UTF-8'), '&') !== false){
				$tcString = html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|tilde|uml);~i', '$1', $tcString), ENT_QUOTES, 'UTF-8');
			}
		}
		return $tcString;
	}


	/**
	 * Crea un mapa (pares clave-valor) a partir de un arreglo multidimensional o un arreglo de objetos.
	 * Los parámetros '$tcClave' y '$tcValor' especifican las claves o propiedades para configurar el mapa.
	 * Opcionalmente se puede indicar un campo '$tcGrupo' para agrupar el mapa.
	 *
	 * @param array $taArray
	 * @param string|\Closure $tcClave
	 * @param string|\Closure $tcValor
	 * @param string|\Closure $tcGrupo
	 * @return array
	 *
	 * Ejemplo,
	 *		$array = [
	 *		    ['id' => '123', 'name' => 'aaa', 'class' => 'x'],
	 *		    ['id' => '124', 'name' => 'bbb', 'class' => 'x'],
	 *		    ['id' => '345', 'name' => 'ccc', 'class' => 'y'],
	 *		];
	 *
	 *		$result = AplicacionFunciones::mapear($array, 'id', 'name');
	 *		// el resultado es:
	 *		// [
	 *		//     '123' => 'aaa',
	 *		//     '124' => 'bbb',
	 *		//     '345' => 'ccc',
	 *		// ]
	 *
	 *		$result = AplicacionFunciones::mapear($array, 'id', 'name', 'class');
	 *		// el resultado es:
	 *		// [
	 *		//     'x' => [
	 *		//         '123' => 'aaa',
	 *		//         '124' => 'bbb',
	 *		//     ],
	 *		//     'y' => [
	 *		//         '345' => 'ccc',
	 *		//     ],
	 *		// ]
	 */
	public static function mapear($taArray, $tcClave, $tcValor, $tcGrupo = null)
	{
		$aRes = [];
		foreach ($taArray as $elemento) {
			$cLlave = static::getValue($elemento, $tcClave);
			$uValor = static::getValue($elemento, $tcValor);
			if ($tcGrupo !== null) {
				$aRes[static::getValue($elemento, $tcGrupo)][$cLlave] = $uValor;
			} else {
				$aRes[$cLlave] = $uValor;
			}
		}

		return $aRes;
	}


	/**
	 * Retorna los valores de una columna específica en un arreglo.
	 * El arreglo de entrada debe ser multidimensional o un arreglo de objetos.
	 *
	 * @param array $taArray
	 * @param string|\Closure $nombre
	 * @param bool $tbMantenerKeys matiene las claves del arreglo, si es falso se reindexa con enteros.
	 * @return array con la lista de valores de la columna
	 *
	 * Ejemplo,
	 *		$array = [
	 *		    ['id' => '12', 'data' => 'ab'],
	 *		    ['id' => '34', 'data' => 'cd'],
	 *		];
	 *		$result = AplicacionFunciones::getColumn($array, 'id');
	 *		// el resultado es: ['12', '34']
	 */
	public static function getColumn($taArray, $nombre, $tbMantenerKeys = true)
	{
		$result = [];
		if ($tbMantenerKeys) {
			foreach ($taArray as $cClave => $elemento) {
				$result[$cClave] = static::getValue($elemento, $nombre);
			}
		} else {
			foreach ($taArray as $elemento) {
				$result[] = static::getValue($elemento, $nombre);
			}
		}

		return $result;
	}


	/**
	 * Recupera el valor de un elemento de un arreglo o una propiedad de un objeto.
	 * Si la clave no existe en el arreglo o objeto, se retorna el valor predeterminado.
	 *
	 * Para recuperar un elemento de un subarreglo o un objeto embebido se puede usar notación de
	 * punto para la clave. Si la llave es 'x.y.z', entonces se retorna el valor de
	 * $array['x']['y']['z'] o $array->x->y->z. If $array['x'] o $array->x no es un arrelo ni un
	 * objecto, se retorna el valor por defecto.
	 * Si el elemento 'x.y.z' existe, entonces su valor se retorna en lugar del subarreglo. Por esto
	 * es mejor hacer especificación con un arreglo de claves como ['x','y','z'].
	 *
	 * @param array|object $taArray arreglo o objeto del que se extrae el valor
	 * @param string|\Closure|array $tcClave nombre de clave o propiedad, un arreglo de claves o propiedades de un objeto
	 *		o una función anónima que retorne el valor.
	 * @param mixed $tDefault el valor por defecto que será retornado si la clave del array no existe.
	 *		No se usa cuando se obtiene valor de una propiedad de un objeto.
	 * @return valor del elemento si se encuentra, sino el valor por defecto.
	 *
	 * Ejemplo,
	 *		// trabajando con array
	 *		$cUsuario = AplicacionFunciones::getValue($_POST, 'usuario');
	 *		// trabajando con objetos
	 *		$cUsuario = AplicacionFunciones::getValue($oUsuario, 'usuario');
	 */
	public static function getValue($taArray, $tcClave, $tDefault = null)
	{
		if ($tcClave instanceof \Closure) {
			return $tcClave($taArray, $tDefault);
		}

		if (is_array($tcClave)) {
			$lastKey = array_pop($tcClave);
			foreach ($tcClave as $keyPart) {
				$taArray = static::getValue($taArray, $keyPart);
			}
			$tcClave = $lastKey;
		}

		if (is_array($taArray) && (isset($taArray[$tcClave]) || array_key_exists($tcClave, $taArray))) {
			return $taArray[$tcClave];
		}

		if (($pos = strrpos($tcClave, '.')) !== false) {
			$taArray = static::getValue($taArray, substr($tcClave, 0, $pos), $tDefault);
			$tcClave = substr($tcClave, $pos + 1);
		}

		if (is_object($taArray)) {
			// Se espera que esto falle si la propiedad no existe, o si no se implementa __get()
			// no se puede verificar anticipadamente si se puede acceder a una propiedad
			return $taArray->$tcClave;
		} elseif (is_array($taArray)) {
			return (isset($taArray[$tcClave]) || array_key_exists($tcClave, $taArray)) ? $taArray[$tcClave] : $tDefault;
		}

		return $tDefault;
	}


	/**
	 * Ordena un arreglo de objeto o de arreglos (con la misma estructura) por una o mas claves.
	 * @param array $taArray el arreglo a ordenar. El arreglo se modificará luego de llamar esta función.
	 * @param string|\Closure|array $tcClave Clave(s) a ordenar. O sea clave de los subarreglos o
	 *		propiedad de los objetos, o una función anónima que los retorne function($item).
	 *		Para ordenar por múltiples claves, se tienen que indicar en un arreglo.
	 * @param int|array $tnDireccion La dirección de ordenamiento. Puede ser `SORT_ASC` o `SORT_DESC`.
	 *		Usar un array para indicar diferentes direcciones de ordenamiento.
	 * @param int|array $tnOrden Bandera de orden de PHP. Valores válidos son `SORT_REGULAR`,
	 *		`SORT_NUMERIC`, `SORT_STRING`, `SORT_LOCALE_STRING`, `SORT_NATURAL` y `SORT_FLAG_CASE`.
	 *		Usar un array para indicar diferentes banderas de orden.
	 */
	public static function ordenarArrayMulti(&$taArray, $tcClave, $tnDireccion = SORT_ASC, $tnOrden = SORT_REGULAR)
	{
		$Claves = is_array($tcClave) ? $tcClave : [$tcClave];
		if (empty($Claves) || empty($taArray)) {
			return;
		}
		$n = count($Claves);
		if (is_scalar($tnDireccion)) {
			$tnDireccion = array_fill(0, $n, $tnDireccion);
		} elseif (count($tnDireccion) !== $n) {
			//throw new Exception('El número de elementos en $tnDireccion debe ser el mismo que en $tcClave.');
			echo 'El número de elementos en $tnDireccion debe ser el mismo que en $tcClave.<br>';
		}
		if (is_scalar($tnOrden)) {
			$tnOrden = array_fill(0, $n, $tnOrden);
		} elseif (count($tnOrden) !== $n) {
			//throw new Exception('El número de elementos en $tnOrden debe ser el mismo que en $tcClave.');
			echo 'El número de elementos en $tnOrden debe ser el mismo que en $tcClave.<br>';
		}
		$args = [];
		foreach ($Claves as $i => $cClave) {
			$flag = $tnOrden[$i];
			$args[] = static::getColumn($taArray, $cClave);
			$args[] = $tnDireccion[$i];
			$args[] = $flag;
		}

		// Cuando la clasificación principal tiene valores iguales
		// Sin esto se produce un Error Fatal: Nesting level too deep - recursive dependency?
		$args[] = range(1, count($taArray));
		$args[] = SORT_ASC;
		$args[] = SORT_NUMERIC;

		$args[] = &$taArray;
		call_user_func_array('array_multisort', $args);
	}

	/*
	 * Convierte número a texto en formato fecha, hora o fechahora
	 *
	 * @param string $tcFormato: formato a aplicar (fecha, hora, hora12, fechahora, fechahora12)
	 * @param int $tnNumero: fecha hora a convertir, en formato número
	 * @param string $tcSepF: separador entre año mes día
	 * @param string $tcSepH: separador entre horas minutos segundos
	 * @param string $tcSep: separador entre fecha y hora
	 * @return string Fecha en el formato indicado
	 */
	public static function formatFechaHora($tcFormato='fecha', $tnNumero=0, $tcSepF='-', $tcSepH=':', $tcSep=' ')
	{
		switch (strtolower($tcFormato)) {
			case 'fecha':
				$tnNumero = trim($tnNumero);
				if ($tnNumero=='0') { return '0'; }
				return substr($tnNumero, 0, 4).$tcSepF.substr($tnNumero, 4, 2).$tcSepF.substr($tnNumero, 6, 2);
				break;
			case 'fechadia':
				$tnNumero = trim($tnNumero);
				if ($tnNumero=='0') { return '0'; }
				return substr($tnNumero, 6, 2).$tcSepF.substr($tnNumero, 4, 2).$tcSepF.substr($tnNumero, 0, 4);
				break;
			case 'hora':
				$tnNumero = str_pad(trim($tnNumero), 6, '0', STR_PAD_LEFT);
				return substr($tnNumero, 0, 2).$tcSepH.substr($tnNumero, 2, 2).$tcSepH.substr($tnNumero, 4, 2);
				break;
			case 'hora12':
				$tnNumero = str_pad(trim($tnNumero), 6, '0', STR_PAD_LEFT);
				$lcHora = substr($tnNumero, 0, 2);
				$lnHora = intval($lcHora);
				$lcAmPm = $lnHora>=12 ? 'PM' : 'AM';
				$lcHora = $lnHora>12 ? str_pad($lnHora-12, 2, '0', STR_PAD_LEFT) : $lcHora;
				return $lcHora.$tcSepH.substr($tnNumero, 2, 2).$tcSepH.substr($tnNumero, 4, 2).' '.$lcAmPm;
				break;
			case 'fechahora':
				if ($tnNumero=='0') { return '0'; }
				return self::formatFechaHora('fecha', substr($tnNumero,0,8), $tcSepF) . $tcSep . self::formatFechaHora('hora', substr($tnNumero,8), $tcSepH);
				break;
			case 'fechadiahora':
				if ($tnNumero=='0') { return '0'; }
				return self::formatFechaHora('fechadia', substr($tnNumero,0,8), $tcSepF) . $tcSep . self::formatFechaHora('hora', substr($tnNumero,8), $tcSepH);
				break;
			case 'fechahora12':
				if ($tnNumero=='0') { return '0'; }
				return self::formatFechaHora('fecha', substr($tnNumero,0,8), $tcSepF) . $tcSep . self::formatFechaHora('hora12', substr($tnNumero,8), $tcSepH);
				break;
		}
		return '';
	}

	/*
	 *	Valida una fecha o fecha hora en formato texto
	 *
	 *	@param string $tcFechaHora: Fecha hora a validar
	 *	@param string $tcFormato: Formato con que debe estar la fecha hora
	 *	@return boolean: Retorna true si es una fecha hora válida
	 */
	public static function validarFechaHora($tcFechaHora, $tcFormato='Y-m-d H:i:s')
	{
		$dFecha = \DateTime::createFromFormat($tcFormato, $tcFechaHora);
		return $dFecha && $dFecha->format($tcFormato)==$tcFechaHora;
	}

	/*
	 * Retorna str_pad para cadenas con caracteres como ñ y tildes
	 *
	 * @param string $tcTexto: texto de entrada
	 * @param int $tnLongitud: longitud del relleno
	 * @param string $tcRelleno: texto con el que se rellena
	 * @param int $tnTipoPad: tipo de relleno, si no se especifica se asume como STR_PAD_RIGHT
	 * @param string $tcCodificacion: codificación usada para obtener el largo del texto de entrada
	 * @return string Texto de entrada rellenado hasta la longitud especificada
	 */
	public static function mb_str_pad($tcTexto, $tnLongitud, $tcRelleno=' ', $tnTipoPad=STR_PAD_RIGHT, $tcCodificacion='UTF8')
	{
		$lcCodificacion = is_null($tcCodificacion) ? mb_internal_encoding(): strval($tcCodificacion);
		$lnDiff = strlen($tcTexto) - mb_strlen($tcTexto, $lcCodificacion);
		return str_pad($tcTexto, ($tnLongitud + $lnDiff), $tcRelleno, $tnTipoPad);
	}

	/*
	 * Convierte un string en un array, dividiendo en cadenas de longitud indicada
	 *
	 * @param string $tcTexto: texto de entrada
	 * @param int $tnLargo: largo al que se desea cortar
	 * @param string $tcCodificacion: codificación usada para obtener el largo del texto de entrada
	 * @return string Texto de entrada rellenado hasta la longitud especificada
	 */
	public static function mb_str_split($tcTexto, $tnLargo=1, $tcCodificacion='UTF8')
	{
		$lcCodificacion = is_null($tcCodificacion) ? mb_internal_encoding(): strval($tcCodificacion);
		$laFinal = [];
		$lnLargoTxt = mb_strlen($tcTexto, $lcCodificacion);
		for ($lnNum = 0; $lnNum < $lnLargoTxt; $lnNum += $tnLargo) {
			$laFinal[] = mb_substr($tcTexto, $lnNum, $tnLargo, $lcCodificacion);
		}
		return $laFinal;
	}

	/*
	 * Reemplaza nombre del mes en Inglés a Español
	 *
	 * @param string $tcFecha: texto de fecha
	 * @return string fecha con el nombre del mes en español, si este se encuentra
	 */
	public static function FechaNombreMes($tcFecha='')
	{
		$laMesNombre = [
				'January'=>'Enero',
				'February'=>'Febrero',
				'March'=>'Marzo',
				'April'=>'Abril',
				'May'=>'Mayo',
				'June'=>'Junio',
				'July'=>'Julio',
				'August'=>'Agosto',
				'September'=>'Septiembre',
				'October'=>'Octubre',
				'November'=>'Noviembre',
				'December'=>'Diciembre',
			];
		return str_replace(array_keys($laMesNombre), array_values($laMesNombre), $tcFecha);
	}

	/*
	 * Reemplaza nombre del día en Inglés a Español
	 *
	 * @param string $tcFecha: texto de fecha
	 * @return string fecha con el nombre del día en español, si este se encuentra
	 */
	public static function FechaNombreDia($tcFecha='')
	{
		$laMesNombre = [
				'Monday'=>'Lunes',
				'Tuesday'=>'Martes',
				'Wednesday'=>'Miércoles',
				'Thursday'=>'Jueves',
				'Friday'=>'Viernes',
				'Saturday'=>'Sábado',
				'Sunday'=>'Domingo',
			];
		return str_replace(array_keys($laMesNombre), array_values($laMesNombre), $tcFecha);
	}

	/**
	 * Retorna un valor dentro un array de dos dimensiones con llaves enumeradas desde 0
	 *
	 * @param array $taArray: array dentro del cual se hace la búsqueda
	 * @param string $tcColRetorna: nombre de la columna cuyo valor debe retornar
	 * @param string|number $tuValorBuscar: valor que se va a buscar en $tcColBuscar
	 * @param string $tcColBuscar: nombre de la columna dentro de la que se va a buscar
	 * @return valor encontrado en $tcColRetorna
	 *
	 * Ejemplo,
	 *		$array = [
	 *		    0=>['id' => '123', 'name' => 'aaa', 'class' => 'x'],
	 *		    1=>['id' => '124', 'name' => 'bbb', 'class' => 'x'],
	 *		    2=>['id' => '345', 'name' => 'ccc', 'class' => 'y'],
	 *		];
	 *
	 *		$result = AplicacionFunciones::lookup($array, 'name', '124', 'id');
	 *		// el resultado es: 'bbb'
	 */
	public static function lookup($taArray, $tcColRetorna, $tuValorBuscar, $tcColBuscar)
	{
		$lnKey = array_search($tuValorBuscar, array_column($taArray, $tcColBuscar));
		return $lnKey===false ? '' : $taArray[$lnKey][$tcColRetorna];
		//return $taArray[$lnKey][$tcColRetorna] ?? '';
	}


	/*
	 *	PHP Download Image Or File From URL
	 *	I’ll show you 3 php functions that download a particular file (ex: image,video,zip,pdf,doc,xls,etc) from a remote resource (via a valid URL) then save to your server.
	 *	Depending on your current php.ini settings, some functions may not work; therefore, let try which function is best for you.
	 *	Note: please ensure the folder you want to store the downloaded file is existed and has write permission for everyone or the web process.
	 *	@param string $tcArchivoUrl: url remota del archivo
	 *	@param string $tcGuardarComo: ruta y nombre con que se guarda el archivo
	 *	@param string $tcMetodo: Método que se va a usar, puede ser:
	 *		getcontent: PHP Download Remote File From URL With file_get_contents and file_put_contents
	 *		curl: PHP Download Remote File From URL With CURL
	 *		fopen: PHP Download Remote File From URL With fopen
	 */
	public static function descargarArchivoRemoto($tcArchivoUrl, $tcGuardarComo, $tcMetodo='getcontent')
	{
		$lcReturn = '';
		switch ($tcMetodo) {
			case 'getcontent':
				$lcContent = file_get_contents($tcArchivoUrl);
				if (empty($tcGuardarComo)) {
					return $lcContent;
				} else {
					file_put_contents($tcGuardarComo, $lcContent);
				}
				break;

			case 'curl':
				$loCurl = curl_init();
				curl_setopt($loCurl, CURLOPT_POST, 0);
				curl_setopt($loCurl, CURLOPT_URL, $tcArchivoUrl);
				curl_setopt($loCurl, CURLOPT_RETURNTRANSFER, 1);
				$lcContent = curl_exec($loCurl);
				curl_close($loCurl);
				if (empty($tcGuardarComo)) {
					return $lcContent;
				} else {
					$loDownloadedFile = fopen($tcGuardarComo, 'w');
					fwrite($loDownloadedFile, $lcContent);
					fclose($loDownloadedFile);
				}
				break;

			case 'fopen':
				if (empty($tcGuardarComo)) {
					$loIn  = fopen($tcArchivoUrl, "rb");
					$lcContent = '';
					while ($loChunk = fread($loIn, 8192)) {
						$lcContent .= $loChunk;
					}
					fclose($loIn);
					return $lcContent;
				} else {
					$loIn  = fopen($tcArchivoUrl, "rb");
					$loOut = fopen($tcGuardarComo, "wb");
					while ($loChunk = fread($loIn, 8192)) {
						fwrite($loOut, $loChunk, 8192);
					}
					fclose($loIn);
					fclose($loOut);
				}
				break;
		}
	}


	/**
	 * Retorna un valor tipo String el cual puede varia dependiendo del formato de salida.
	 *
	 * @param String $tcFile: Ruta y Nombre del archivo (Obligatorio)
	 * @param Integer $tnFormat: Formato de Salida, valores posibles 0,1,2
	 * 				   0: Contenido de un archivo (binario).
	 * 				   1: Etiqueta <IMG> con la data del contenido del un archivo (Aplica solo para imágenes).
	 * 				   2: Ruta temporal de acceso al archivo de forma local. Es una copia en la carpeta temporal del archivo, debe ser eliminado después de su uso.
	 * @param $tnStatus: Variable por referencia tipo Integer, se signan los siguientes valores:
	 * 				   1: Si es accesible el archivo.
	 * 				   0: Si no realiza acción.
	 * 				  -1: Error de acceso al archivo (No existe/No se tienen permisos).
	 * 				  -2: No es accesible con las credenciales mediante SMB (Aplica unicamente para Linux).
	 * @param $tcTmpMime: Variable por referencia tipo String, se signan el tipo de archivo.
	 * @param String $tcWorkGroup: Grupo de trabajo (Aplica unicamente para Linux, en Windows se enviá NULL).
	 * @param String $tcUserName: Usuario de Dominio/Local con acceso al HOST (Aplica unicamente para Linux, en Windows se enviá NULL).
	 * @param String $tcPassword: Clave del usuario (Aplica unicamente para Linux, en Windows se enviá NULL).
	 * @return String con la información solicitada en el formato
	 *
	 * Ejemplo,
	 *	- CONTENIDO DEL ARCHIVO (BINARIO)
	 *		$lnStatus = 0;
	 *		$lcMIME = '';
	 *		$lcResult = AplicacionFunciones::obtenerRemoto('\\\\SERVIDOR\\IMG_5HS10$\\0001233902598.JPG',0,$lnStatus,$lcMIME,null,'SHAIO\usuario','LaClave');
	 *		// el resultado es: 'GIF89a...#K?' , a lnStatus se asigna o y a lcMIME 'png'
	 *
	 *	- ETIQUETA <IMG>
	 *		$lnStatus = 0;
	 *		$lcMIME = '';
	 *		$lcResult = AplicacionFunciones::obtenerRemoto('\\\\SERVIDOR\\IMG_5HS10$\\0001233902598.JPG',1,$lnStatus,$lcMIME,null,'SHAIO\usuario','LaClave');
	 *		// el resultado es: '<img src="data:image/png;base64,iVBORw0K...Jggg==">' , a lnStatus se asigna o y a lcMIME 'png'
	 *
	 *	- ARCHIVO TEMPORAL
	 * 	  Windows - No local
	 *		$lnStatus = 0;
	 *		$lcMIME = '';
	 *		$lcResult = AplicacionFunciones::obtenerRemoto('\\\\SERVIDOR\\IMG_5HS10$\\0001233902598.JPG',2,$lnStatus,$lcMIME,null,'SHAIO\usuario','LaClave');
	 *		// el resultado es: 'C:\Windows\Temp\0001233902598.JPG' , a lnStatus se asigna o y a lcMIME 'png'
	 * 	  Linux - No local
	 *		$lnStatus = 0;
	 *		$lcMIME = '';
	 *		$lcResult = AplicacionFunciones::obtenerRemoto('\\\\SERVIDOR\\IMG_5HS10$\\0001233902598.JPG',2,$lnStatus,$lcMIME,null,'SHAIO\usuario','LaClave');
	 *		// el resultado es: '/tmp/0001233902598.JPG' , a lnStatus se asigna o y a lcMIME 'png'
	 */
	public static function obtenerRemoto($tcFile='', $tnFormat=0, &$tnStatus=0, &$tcTmpMime='', $tcWorkGroup=null, $tcUserName=null, $tcPassword=null)
	{
		$tcFile = trim(strval($tcFile));
		$tnFormat = intval($tnFormat);
		$tnStatus = 0;
		$tcTmpMime = '';
		$lcBinary = '';
		$lcOutput = '';
		$lcChrPath='/';

		// Crea un archivo temporal
		$lcTmpFile = tmpfile();
		$lcTmpPath = stream_get_meta_data($lcTmpFile)['uri'];

		//Archivo
		if(empty($tcFile)==false){
			if (stripos(strtolower(PHP_OS), 'win') === 0) {
				// Obtener contenido en Windows
				$lcChrPath='\\';
				if(file_exists($tcFile)==true){
					$tnStatus = 1;
					$lcBinary = file_get_contents($tcFile, true);
					fwrite($lcTmpFile, $lcBinary);
				}else{
					$tnStatus = -1;
				}
			}else{
				$tcFile = str_replace('\\', '/', $tcFile);
				$tcFileRaw = ('smb:'.$tcFile);

				// Obtener contenido en linux con conexión SMB
				$lcSmbClientState = smbclient_state_new();
				if (smbclient_state_init($lcSmbClientState, $tcWorkGroup, $tcUserName, $tcPassword)) {
					@$loSmbFile = smbclient_open($lcSmbClientState, $tcFileRaw, 'r');
					if ($loSmbFile) {
						while (true) {
							$lcSmbData = smbclient_read($lcSmbClientState, $loSmbFile, 100000);
							if (($lcSmbData === false) || (strlen($lcSmbData) === 0)) {
								break;
							}
							$lcBinary .= $lcSmbData;
							fwrite($lcTmpFile, $lcBinary);
						}
						$tnStatus = 1;
						smbclient_close($lcSmbClientState, $loSmbFile);
					} else {
						$tnStatus = -1;
					}
				} else {
					$tnStatus=-2;
				}
				smbclient_state_free($lcSmbClientState);
			}
		}

		// Obteniendo tipo de archivo
		if(!empty($lcTmpPath)){
			if(file_exists($lcTmpPath)==true){
				if(!empty($lcBinary)){
					$tcTmpMime = mime_content_type($lcTmpPath); // Tipo de archivo
				}
			}
		}


		// Salida
		if(!empty($lcBinary) && $tnStatus==1){
			switch ($tnFormat) {
				case 0: // Binario del archivo
					$lcOutput = $lcBinary;
					break;

				case 1: // Etiqueta <IMG> en html HTML
					$lcOutput = sprintf('<img src="data:%s;base64,%s">',$tcTmpMime,base64_encode($lcBinary));
					break;

				default: // Ruta temporal de acceso al archivo de forma local. Es una copia en la carpeta temporal del archivo, debe ser eliminado después de su uso
					$lcFileNew = sprintf('%s%s%s',pathinfo($lcTmpPath,PATHINFO_DIRNAME),$lcChrPath,pathinfo($tcFile,PATHINFO_BASENAME));
					if (!copy($lcTmpPath, $lcFileNew)) {
						$lcOutput = '';
					}else{
						$lcOutput =$lcFileNew;
					}
					break;
			}
		}

		// Cierra y elimina el archivo temporal, no el temporal local.
		fclose($lcTmpFile);

		return $lcOutput;
	}

	/*
	 *	EVITAR INYECCIÓN DE CÓDIGO
	 */
	public static function fnSanitizar($tData)
	{
		// return filter_var($tData, FILTER_SANITIZE_STRING | FILTER_SANITIZE_ADD_SLASHES);
		return addslashes(filter_var($tData, FILTER_SANITIZE_STRING));
	}

	/*
	 *	Convierte color numérico obtenido con la función RGB de foxpro a un color css
	 *	@param numero $tnColor: número que representa el color
	 *	@return string color hexadeximal css
	 */
	public static function colorFoxToRGB($tnColor=0)
	{
		$tnMB = 256*256;
		$tnMG = 256;
		$tnB = intval($tnColor/$tnMB);
		$tnG = intval(($tnColor-$tnB*$tnMB)/$tnMG);
		$tnR = intval($tnColor-$tnB*$tnMB-$tnG*$tnMG);
		return str_pad(dechex($tnR),2,'0',STR_PAD_LEFT).str_pad(dechex($tnG),2,'0',STR_PAD_LEFT).str_pad(dechex($tnB),2,'0',STR_PAD_LEFT);
	}

	public static function encriptar($tcValor = '', $tcEncriptadoMetodo = 'aes-256-cbc', $tcEncriptadoClave = 'b43f06ae4a409702102d01b0a39d2c06', $tcEncriptadoIV = '6451117dcff3fe2b')
	{
		return base64_encode(openssl_encrypt(strval($tcValor), $tcEncriptadoMetodo, $tcEncriptadoClave, false, $tcEncriptadoIV));
	}

	public static function desencriptar($tcValor = '', $tcEncriptadoMetodo = 'aes-256-cbc', $tcEncriptadoClave = 'b43f06ae4a409702102d01b0a39d2c06', $tcEncriptadoIV = '6451117dcff3fe2b')
	{
		$tcValor = base64_decode(strval($tcValor));
		return openssl_decrypt($tcValor, $tcEncriptadoMetodo, $tcEncriptadoClave, false, $tcEncriptadoIV);
	}

	public static function fnSanitizarAlfanumerico($tcString = '', $tcPrefix = '', $tcPostfix = '')
	{
		$tcString = filter_var(strval($tcString), FILTER_SANITIZE_STRING | FILTER_SANITIZE_MAGIC_QUOTES);

		$lnStringLenght = strlen($tcString);
		for($laChar=0; $laChar<$lnStringLenght; $laChar++) {
			$lcChar = $tcString[$laChar];
			$lnChar = ord($lcChar);
			if(
				($lnChar == 32) || ($lnChar == 37) ||
				($lnChar == 110) || ($lnChar == 209) ||
				($lnChar > 47 && $lnChar < 58) || //0123456789
				($lnChar > 64 && $lnChar < 91) || //ABCDEFGH..Z
				($lnChar > 96 && $lnChar < 123)  //abcdefgh..z
			) {
				$tcPrefix .= $tcString[$laChar];
			}
		}
		return trim($tcPrefix.$tcPostfix);
	}

	public static function fcLimpiarEspacios($tcValue = '')
	{
		$tcValue = (gettype($tcValue)=='string' ? trim($tcValue) : '');
		return implode(' ',array_filter(explode(' ',$tcValue)));
	}

	public static function getInbetweenStrings($str,$from,$to)
	{
		$sub = substr($str, strpos($str,$from)+strlen($from),strlen($str));
		return substr($sub,0,strpos($sub,$to));
	}

}