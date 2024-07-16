<?php

namespace NUCLEO;

use PDO;
use PDOException;
use Closure;

class Db
{
    public $pdo = null;

	public $cSistemaOperativo = '';

	protected $config  = [];
	protected $entorno = [
						'desarrollo'=> ['system'=>'System=172.20.10.195;' ,'odbcini'=>'ALPHILDES'],
						'produccion'=> ['system'=>'System=172.20.10.20;','odbcini'=>'ALPHILDAT'],
						];
	protected $dsn			= 'odbc: ; '
							. 'Driver=iSeries Access ODBC Driver; '
							. 'DESC=Client Access Express ODBC data source; '
							. 'AllowDataCompression=1; '
							. 'BlockFetch=1; '
							. 'BlockSizeKB=32; '
							. 'CatalogOptions=3; '
							. 'CommitMode=2; '
							. 'QAQQINILibrary=; '
							. 'SortTable=; '
							. 'LanguageID=ENU; '
							. 'ExtendedDynamic=1; '
							. 'DefaultPkgLibrary=QGPL; '
							. 'DefaultPackage=QGPL/DEFAULT(IBM),2,0,1,0,0; '
							. 'SQLConnectPromptMode=1; '
							. 'Signon=2; ';
	protected $select		= '*';
	protected $distinct		= '';
	protected $statement	= null;
	protected $from			= null;
	protected $where		= null;
	protected $bindValues	= [];
	protected $limit		= null;
	protected $offset		= null;
	protected $join			= null;
	protected $orderBy		= null;
	protected $groupBy		= null;
	protected $having		= null;
	protected $grupo		= false;
	protected $grupoOpen	= '';
	protected $numRows		= 0;
	protected $sql			= null;
	protected $error		= null;
	protected $result		= [];
	protected $prefix		= null;
	protected $op			= ['=', '<', '>', '<=', '>=', '<>'];
	protected $queryCount	= 0;
	protected $debug		= false;
	public $onErrorDie		= true;
	public $usarBindValues	= true;
	public $soWindows		= false;

	// caracteres a eliminar para crear bind values
	protected $aEliBindVal = ['.',' ',',','ñ','Ñ','(',')','{','}','[',']','"',"'",'*','/','+','-','%','\\'];


	public function __construct()
	{
		$this->cSistemaOperativo = strtoupper(substr(PHP_OS, 0, 3));
		$this->soWindows = $this->cSistemaOperativo==='WIN';

		$this->config	= require __DIR__ . '/../privada/db.php';
		$this->prefix	= (isset($this->config['prefix']) ? $this->config['prefix'] : '');
		$this->debug	= (isset($this->config['debug']) ? $this->config['debug'] : false);

		$cEntorno = $this->config['system'];
		$this->dsn = $this->soWindows ?
						$this->dsn . 'DefaultLibraries=' . $this->config['library'] . '; ' . $this->entorno[$cEntorno]['system'] :
						'odbc:'.$this->entorno[$cEntorno]['odbcini'];

		$aOptPdo = [
			\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC, // \PDO::FETCH_OBJ,
			\PDO::ATTR_CASE => \PDO::CASE_UPPER,
			\PDO::ATTR_ORACLE_NULLS => \PDO::NULL_TO_STRING
		];

		try {
			$this->pdo = new \PDO($this->dsn, $this->config['username'], $this->config['password'], $aOptPdo);
		} catch (\PDOException $loError) {
			$lcError = 'No puede conectarse a la base de datos PDO.<br /><br />' . $loError->getMessage();
			die($lcError);
		}

		return $this->pdo;
	}

	public function dsn()
	{
		return $this->dsn;
	}


	/*****   FUNCIONES PARA MANEJO DE TRANSACCIONES   *****/


	public function inicioTransaccion()
	{
		return $this->pdo->beginTransaction();
	}

	public function commit()
	{
		return $this->pdo->commit();
	}

	public function rollback()
	{
		return $this->pdo->rollBack();
	}

	public function enTransaccion()
	{
		return $this->pdo->inTransaction();
	}

	public function inicioTransaccionA($tcNivel = 'UR')
	{
		$laNivelesPermitidos = ['UR', 'CHG', 'READ UNCOMMITTED', 'CS', 'READ COMMITTED', 'RS', 'ALL', 'REPEATABLE READ', 'RR', 'SERIALIZABLE'];
		$tcNivel = strtoupper(trim($tcNivel));
		if (!in_array($tcNivel, $laNivelesPermitidos)) $tcNivel = 'UR';
		return $this->pdo->exec("SET TRANSACTION ISOLATION LEVEL $tcNivel");
	}

	public function commitA()
	{
		return $this->pdo->exec('COMMIT');
	}

	public function rollbackA()
	{
		return $this->pdo->exec('ROLLBACK');
	}

	/*****   FIN FUNCIONES PARA MANEJO DE TRANSACCIONES   *****/



	public function select($tcFields)
	{
		$lcSelect = (is_array($tcFields) ? implode(', ', $tcFields) : $tcFields);
		$this->select = ($this->select == '*' ? $lcSelect : $this->select . ', ' . $lcSelect);

		return $this;
	}

	public function distinct()
	{
		$this->distinct = ' DISTINCT ';

		return $this;
	}

	public function max($tcField, $tcName = null)
	{
		$func = 'IFNULL(MAX(' . $tcField . '),0)' . (!is_null($tcName) ? ' AS ' . $tcName : '');
		$this->select = ($this->select == '*' ? $func : $this->select . ', ' . $func);

		return $this;
	}

	public function min($tcField, $tcName = null)
	{
		$func = 'IFNULL(MIN(' . $tcField . '),0)' . (!is_null($tcName) ? ' AS ' . $tcName : '');
		$this->select = ($this->select == '*' ? $func : $this->select . ', ' . $func);

		return $this;
	}

	public function sum($tcField, $tcName = null)
	{
		$func = 'IFNULL(SUM(' . $tcField . '),0)' . (!is_null($tcName) ? ' AS ' . $tcName : '');
		$this->select = ($this->select == '*' ? $func : $this->select . ', ' . $func);

		return $this;
	}

	public function count($tcField, $tcName = null)
	{
		$func = 'IFNULL(COUNT(' . $tcField . '),0)' . (!is_null($tcName) ? ' AS ' . $tcName : '');
		$this->select = ($this->select == '*' ? $func : $this->select . ', ' . $func);

		return $this;
	}

	public function avg($tcField, $tcName = null)
	{
		$func = 'IFNULL(AVG(' . $tcField . '),0)' . (!is_null($tcName) ? ' AS ' . $tcName : '');
		$this->select = ($this->select == '*' ? $func : $this->select . ', ' . $func);

		return $this;
	}

	public function tabla($tcTabla)
	{
		return $this->from($tcTabla);
	}

	public function from($tcTabla)
	{
		if(is_array($tcTabla)) {
			$f = '';
			foreach($tcTabla as $key) {
				$f .= $this->prefix . $key . ', ';
			}

			$this->from = rtrim($f, ', ');
		} else {
			$this->from = $this->prefix . $tcTabla;
		}

		return $this;
	}

	public function join($tcTable, $tcField1 = null, $tcOp = null, $tcField2 = null, $tcType = '')
	{
		$tcTable = $this->prefix . $tcTable;

		if(!is_null($tcOp)) {
			$on = (!in_array($tcOp, $this->op) ?
				$this->prefix . $tcField1 . ' = ' . $this->prefix . $tcOp :
				$this->prefix . $tcField1 . ' ' . $tcOp . ' ' . $this->prefix . $tcField2);
		} else {
			$on = $tcField1;
		}

		$this->join = ( (is_null($this->join)) ? '' : $this->join ).
				' ' . $tcType . ' JOIN' . ' ' . $tcTable . ' ON ' . $on;

		return $this;
	}

	public function innerJoin($tcTable, $tcField1, $tcOp = null, $tcField2 = '')
	{
		$this->join($tcTable, $tcField1, $tcOp, $tcField2, 'INNER');

		return $this;
	}

	public function leftJoin($tcTable, $tcField1, $tcOp = null, $tcField2 = '')
	{
		$this->join($tcTable, $tcField1, $tcOp, $tcField2, 'LEFT');

		return $this;
	}

	public function rightJoin($tcTable, $tcField1, $tcOp = null, $tcField2 = '')
	{
		$this->join($tcTable, $tcField1, $tcOp, $tcField2, 'RIGHT');

		return $this;
	}

	public function fullOuterJoin($tcTable, $tcField1, $tcOp = null, $tcField2 = '')
	{
		$this->join($tcTable, $tcField1, $tcOp, $tcField2, 'FULL OUTER');

		return $this;
	}

	public function leftOuterJoin($tcTable, $tcField1, $tcOp = null, $tcField2 = '')
	{
		$this->join($tcTable, $tcField1, $tcOp, $tcField2, 'LEFT OUTER');

		return $this;
	}

	public function rightOuterJoin($tcTable, $tcField1, $tcOp = null, $tcField2 = '')
	{
		$this->join($tcTable, $tcField1, $tcOp, $tcField2, 'RIGHT OUTER');

		return $this;
	}

	public function where($tcWhere, $tcOp = null, $tcVal = null, $tcType = '', $tcAndOr = 'AND')
	{
		if (is_array($tcWhere)) {
			$laWhere = [];

			foreach ($tcWhere as $lcColumn => $lValor){
				if ($this->usarBindValues) {
					$lcBindValue = $this->addBindValueNombre('bvWh', $lcColumn, $lValor);
					$laWhere[] = $tcType . $lcColumn . '=' . $lcBindValue;
				} else {
					$laWhere[] = $tcType . $lcColumn . '=\'' . $lValor . '\'';
				}
			}

			$tcWhere = implode(' ' . $tcAndOr . ' ', $laWhere);

		} else {
			if(is_array($tcOp)) {
				$lcConds = explode('?', $tcWhere);
				$lcWhere = '';

				foreach($lcConds as $lcClave => $luValor) {
					if(!empty($luValor)) {
						$lcWhere .= $tcType . $luValor . (isset($tcOp[$lcClave]) ? $this->escape($tcOp[$lcClave]) : '');
					}
				}

				$tcWhere = $lcWhere;

			} elseif ($tcOp == false) {
				$tcWhere = $tcType . $tcWhere;

			} elseif (!in_array($tcOp, $this->op)) {
				if ($this->usarBindValues) {
					$lcBindValue = $this->addBindValueNombre('bvWh', $tcWhere, $tcOp);
					$tcWhere = $tcType . $tcWhere . ' = ' . $lcBindValue;
				} else {
					$tcWhere = $tcType . $tcWhere . ' = \'' . $tcOp . '\'';
				}

			} else {
				if ($this->usarBindValues) {
					$lcBindValue = $this->addBindValueNombre('bvWh', $tcWhere, $tcVal);
					$tcWhere = $tcType . $tcWhere . ' ' . $tcOp . ' ' . $lcBindValue;
				} else {
					$tcWhere = $tcType . $tcWhere . ' ' . $tcOp . ' \'' .  $tcVal . '\'';
				}
			}
		}

		if($this->grupo) {
			$tcWhere = $this->grupoOpen . $tcWhere;
			$this->grupoOpen = '';
			$this->grupo = false;
		}

		if (is_null($this->where)){
			$this->where = $tcWhere;
		} else {
			$this->where = $this->where . ' ' . $tcAndOr . ' ' . $tcWhere;
		}

		return $this;
	}

	public function orWhere($tcWhere, $tcOp = null, $tcVal = null)
	{
		$this->where($tcWhere, $tcOp, $tcVal, '', 'OR');

		return $this;
	}

	public function notWhere($tcWhere, $tcOp = null, $tcVal = null)
	{
		$this->where($tcWhere, $tcOp, $tcVal, 'NOT ', 'AND');

		return $this;
	}

	public function orNotWhere($tcWhere, $tcOp = null, $tcVal = null)
	{
		$this->where($tcWhere, $tcOp, $tcVal, 'NOT ', 'OR');

		return $this;
	}

	public function grupo(Closure $toObj, $taArgumentos=[])
	{
		$this->grupo = true;
		$this->grupoOpen .= '(';
		call_user_func_array($toObj, $taArgumentos);
		$this->where .= ')';

		return $this;
	}

	public function in($tcField, Array $taValores, $tcType = '', $tcAndOr = 'AND')
	{
		if (is_array($taValores)) {

			$laVals = [];
			foreach ($taValores as $lValor){
				if ($this->usarBindValues) {
					$lcBindValue = $this->addBindValueNombre('bvIn', $tcField, $lValor);
					$laVals[] = $lcBindValue;
				} else {
					$laVals[] = '\'' . $lValor . '\'';
				}
			}
			$lcWhere = $tcField . ' ' . $tcType . ' IN (' . implode(', ', $laVals) . ')';

			if($this->grupo) {
				$lcWhere = $this->grupoOpen . $lcWhere;
				$this->grupoOpen = '';
				$this->grupo = false;
			}

			if (is_null($this->where)) {
				$this->where = $lcWhere;
			} else {
				$this->where = $this->where . ' ' . $tcAndOr . ' ' . $lcWhere;
			}
		}
		return $this;
	}

	public function notIn($tcField, Array $taValores)
	{
		$this->in($tcField, $taValores, 'NOT ', 'AND');

		return $this;
	}

	public function orIn($tcField, Array $taValores)
	{
		$this->in($tcField, $taValores, '', 'OR');

		return $this;
	}

	public function orNotIn($tcField, Array $taValores)
	{
		$this->in($tcField, $taValores, 'NOT ', 'OR');

		return $this;
	}

	public function between($tcField, $tcValue1, $tcValue2, $tcType = '', $tcAndOr = 'AND')
	{
		if ($this->usarBindValues) {
			$lcBindValue1 = $this->addBindValueNombre('bvBt', $tcField, $tcValue1);
			$lcBindValue2 = $this->addBindValueNombre('bvBt', $tcField, $tcValue2);
			$lcWhere = $tcField . ' ' . $tcType . ' BETWEEN ' . $lcBindValue1 . ' AND ' . $lcBindValue2;
		} else {
			$lcWhere = $tcField . ' ' . $tcType . ' BETWEEN \'' . $tcValue1 . '\' AND \'' . $tcValue2 . '\'';
		}

		if($this->grupo) {
			$lcWhere = $this->grupoOpen . $lcWhere;
			$this->grupoOpen = '';
			$this->grupo = false;
		}

		if (is_null($this->where)) {
			$this->where = $lcWhere;
		} else {
			$this->where = $this->where . ' ' . $tcAndOr . ' ' . $lcWhere;
		}

		return $this;
	}

	public function notBetween($tcField, $tcValue1, $tcValue2)
	{
		$this->between($tcField, $tcValue1, $tcValue2, 'NOT ', 'AND');

		return $this;
	}

	public function orBetween($tcField, $tcValue1, $tcValue2)
	{
		$this->between($tcField, $tcValue1, $tcValue2, '', 'OR');

		return $this;
	}

	public function orNotBetween($tcField, $tcValue1, $tcValue2)
	{
		$this->between($tcField, $tcValue1, $tcValue2, 'NOT ', 'OR');

		return $this;
	}

	public function like($tcField, $tcData, $tcType = '', $tcAndOr = 'AND')
	{
		$lcLike = $this->escape($tcData);

		$lcWhere = $tcField . ' ' . $tcType . 'LIKE ' . $lcLike;

		if($this->grupo) {
			$lcWhere = $this->grupoOpen . $lcWhere;
			$this->grupoOpen = '';
			$this->grupo = false;
		}

		if (is_null($this->where)) {
			$this->where = $lcWhere;
		} else {
			$this->where = $this->where . ' ' . $tcAndOr . ' ' . $lcWhere;
		}

		return $this;
	}

	public function orLike($tcField, $tcData)
	{
		$this->like($tcField, $tcData, '', 'OR');

		return $this;
	}

	public function notLike($tcField, $tcData)
	{
		$this->like($tcField, $tcData, 'NOT ', 'AND');

		return $this;
	}

	public function orNotLike($tcField, $tcData)
	{
		$this->like($tcField, $tcData, 'NOT ', 'OR');

		return $this;
	}

	public function limit($tnLimit)
	{
		$this->limit = $tnLimit;

		return $this;
	}

	public function offset($tnOffset)
	{
		$this->offset = $tnOffset;

		return $this;
	}

	public function pagination($tnPerPage, $tnPage)
	{
		$this->limit = $tnPerPage;
		$this->offset = ($tnPage - 1) * $tnPerPage;

		return $this;
	}

	public function orderBy($tcOrderBy, $tcOrderDir = null)
	{
		$lcOrderBy = '';
		if (!is_null($tcOrderDir)) {
			$lcOrderBy = $tcOrderBy . ' ' . strtoupper($tcOrderDir);
		} else {
			if(stristr($tcOrderBy, ' ') || $tcOrderBy == "rand()") {
				$lcOrderBy = $tcOrderBy;
			} else {
				$lcOrderBy = $tcOrderBy . ' ASC';
			}
		}

		$this->orderBy .= (empty($this->orderBy)?' ':', ') . $lcOrderBy;

		return $this;
	}

	public function groupBy($tcGroupBy)
	{
		$this->groupBy = (is_array($tcGroupBy)) ? implode(', ', $tcGroupBy) : $tcGroupBy;
		if(is_array($tcGroupBy)) {
			$this->groupBy = implode(', ', $tcGroupBy);
		} else {
			$this->groupBy = $tcGroupBy;
		}

		return $this;
	}

	public function having($tcField, $taOp = null, $tcVal = null)
	{
		if(is_array($tcField)) {
			$laHaving = [];

			foreach ($tcField as $lcColumn => $lValor){
				if ($this->usarBindValues) {
					$lcBindValue = $this->addBindValueNombre('bvHv', $lcColumn, $lValor);
					$laHaving[] = $lcColumn . '=' . $lcBindValue;
				} else {
					$laHaving[] = $lcColumn . '=\'' . $lValor . '\'';
				}
			}

			$this->having = implode(' AND ', $laHaving);

		} elseif (is_null($taOp)) {
			$this->having = $tcField;

		} elseif (!in_array($taOp, $this->op)) {
			if ($this->usarBindValues) {
				$lcBindValue = $this->addBindValueNombre('bvHv', 'Having', $taOp);
				$this->having = $tcField . ' = ' . $lcBindValue;
			} else {
				$this->having = $tcField . ' = \'' . $taOp . '\'';
			}

		} else {
			if ($this->usarBindValues) {
				$lcBindValue = $this->addBindValueNombre('bvHv', 'Having', $tcVal);
				$this->having = $tcField . ' ' . $taOp . ' ' . $lcBindValue;
			} else {
				$this->having = $tcField . ' ' . $taOp . ' \'' . $tcVal . '\'';
			}
		}

		return $this;
	}

	/*
	 *	Genera nombre para el BindValue
	 *	@param string $tcPrefijo - prefijo del nombre
	 *	@param string $tcNombre - nombre a colocar
	 *	@return string - nombre del bindValue generado
	 */
	private function nombreBindValue($tcPrefijo = 'bvWh', $tcNombre = 'sn')
	{
		return ':' . $tcPrefijo . '_' . (count($this->bindValues)+1) . '_' . str_replace($this->aEliBindVal, '', $tcNombre);
	}

	/*
	 *	Adiciona un valor al array bindValues
	 *	@param string $tcBindValue - nombre del bindValue
	 *	@param string $tcValor - valor
	 *	@param boolean $tlLimpiarBindValue - si es true elimina los elementos anteriores del array bindValues
	 */
	public function addBindValue($tcBindValue, $tcValor = null, $tlLimpiarBindValue = false)
	{
		if ($tlLimpiarBindValue) {
			$this->bindValues = [];
		}
		if (is_array($tcBindValue)) {
			foreach ($tcBindValue as $lcNombre => $lValor) {
				$this->bindValues[$lcNombre] = $lValor;
			}
		} else {
			$this->bindValues[$tcBindValue] = $tcValor;
		}

		return $this;
	}

	/*
	 *	Genera nombre para el BindValue y lo adiciona al array bindValues
	 *	@param string $tcPrefijo - prefijo del nombre
	 *	@param string $tcNombre - nombre a colocar
	 *	@param string $tcValor - valor
	 *	@param boolean $tlLimpiarBindValue - si es true elimina los elementos anteriores del array bindValues
	 *	@return string - nombre del bindValue generado
	 */
	private function addBindValueNombre($tcPrefijo = 'bvWh', $tcNombre = 'sn', $tcValor = null, $tlLimpiarBindValue = false)
	{
		$lcNombre = $this->nombreBindValue($tcPrefijo, $tcNombre);
		$this->addBindValue($lcNombre, $tcValor, $tlLimpiarBindValue);
		return $lcNombre;
	}

	public function getBindValue()
	{
		return $this->bindValues;
	}

	public function clearBindValue()
	{
		$this->bindValues = [];

		return $this;
	}

	public function numRows()
	{
		return $this->numRows;
	}

	public function generaTraza()
	{
		$loExcp=new \Exception;
		$lcTraza=$loExcp->getTraceAsString();
		return mb_substr($lcTraza, mb_strpos($lcTraza, chr(10), 0, 'UTF-8')+1, null, 'UTF-8');
	}

	public function get($tcType = '')
	{
		//$this->limit = 1; $this->offset = 0;
		$this->pagination(1,1);
		$lcQuery = $this->getStatement();

		return $this->query( $lcQuery, false, (($tcType == 'array') ? true : false) );
	}

	public function getAll($tcType = '')
	{
		$lcQuery = $this->getStatement();

		return $this->query( $lcQuery, true, (($tcType == 'array') ? true : false) );
	}

	public function getStatement($tbReset = false)
	{
		$lcQuery = 'SELECT ' . $this->distinct . $this->select . ' FROM ' . $this->from;
		$lcOrderPag = '';

		if (!is_null($this->join)) {
			$lcQuery .= $this->join;
		}

		if (!is_null($this->where)) {
			$lcQuery .= ' WHERE ' . $this->where;
		}

		if (!is_null($this->groupBy)) {
			$lcQuery .= ' GROUP BY ' . $this->groupBy;
		}

		if (!is_null($this->having)) {
			$lcQuery .= ' HAVING ' . $this->having;
		}

		if (!is_null($this->orderBy)) {
			$lcQuery .= ' ORDER BY ' . $this->orderBy;
			$lcOrderPag = 'OVER (ORDER BY ' . $this->orderBy . ')';
		}


		$usaLimit  = is_null($this->limit)  ? false : ( ($this->limit  > 0) ? true : false );
		$usaOffset = is_null($this->offset) ? false : ( ($this->offset > 0) ? true : false );
		if ($usaLimit && $usaOffset) {
			$lcQuery = ' SELECT * FROM ( ' .
					'SELECT	ROW_NUMBER() ' . $lcOrderPag . ' AS NUM_FILA_SQL, ' .
					substr($lcQuery, 6) .
					' FETCH FIRST ' . (string) ($this->offset + $this->limit) . ' ROWS ONLY ' .
					' ) AS BASEDATA ' .
					' WHERE NUM_FILA_SQL BETWEEN ' . (string) ($this->offset + 1) . ' AND ' . (string) ($this->offset + $this->limit);

		} elseif (!$usaLimit && $usaOffset) {
			$lcQuery = ' SELECT * FROM ( ' .
					'SELECT	ROW_NUMBER() ' . $lcOrderPag . ' AS NUM_FILA_SQL, ' .
					substr($lcQuery, 6) .
					' ) AS BASEDATA ' .
					' WHERE NUM_FILA_SQL > ' . (string) ($this->offset) ;

		} elseif ($usaLimit && !$usaOffset) {
			$lcQuery .= ' FETCH FIRST ' . $this->limit . ' ROWS ONLY ';
		}

		if ($tbReset) { $this->reset(); }

		return $lcQuery;
	}

	public function insertar($taData, $tbType = false)
	{
		$laColumn = [];
		$laValues = [];
		foreach ($taData as $lcColumn => $lValor) {
			if ($this->usarBindValues) {
				$lcBindValue = $this->addBindValueNombre('bvIn', $lcColumn, $lValor);
				$laColumn[] = $lcColumn;
				$laValues[] = $lcBindValue;
			} else {
				$laColumn[] = $lcColumn;
				$laValues[] = '\''.$lValor.'\'';
			}
		}
		$lcQuery = 'INSERT INTO ' . $this->from . ' (' . implode(',', $laColumn) .
				') VALUES (' . implode(',', $laValues) . ')';

		if($tbType === true) {
			return $lcQuery;
		}

		$lcQuery = $this->query($lcQuery);

		if ($lcQuery) {
			return true;
		}

		return false;
	}

	public function actualizar($taData, $tbType = false)
	{
		$laValores = [];
		foreach ($taData as $lcColumn => $lValor) {
			if ($this->usarBindValues) {
				$lcBindValue = $this->addBindValueNombre('bvIn', $lcColumn, $lValor);
				$laValores[] = $lcColumn . '=' . $lcBindValue;
			} else {
				$laValores[] = $lcColumn . '=\'' . $lValor . '\'';
			}
		}

		$lcQuery = 'UPDATE ' . $this->from . ' SET ' . implode(',', $laValores);

		if (!is_null($this->where)) {
			$lcQuery .= ' WHERE ' . $this->where;
		}

		if($tbType === true) {
			return $lcQuery;
		}

		return $this->query( $lcQuery );
	}

	public function eliminar($tbType = false)
	{
		$lcQuery = 'DELETE FROM ' . $this->from;

		if (!is_null($this->where)) {
			$lcQuery .= ' WHERE ' . $this->where;
		}
/*
// AS400 no admite TRUNCATE TABLE
		if($lcQuery == 'DELETE FROM ' . $this->from) {
			$lcQuery = 'TRUNCATE TABLE ' . $this->from;
		}
*/
		if($tbType === true) {
			return $lcQuery;
		}

		return $this->query($lcQuery);
	}

	/*
	 * preparar y ejecutar una consulta
	 */
	public function query($tcQuery, $tAll = true, $tbArray = false)
	{
		return $this
				->preparar($tcQuery)
				->ejecutar($tAll, $tbArray);
	}

	/*
	 * prepara la consulta enviada
	 */
	public function preparar($tcQuery)
	{
		$this->reset();
		$this->sql = preg_replace("/\s\s+|\t\t+/", ' ', trim($tcQuery));

		$this->utf8_decode_deep($this->sql);
		$loSql = $this->pdo->prepare($this->sql);
		if (!$loSql) {
			return $this->errorPDO();
		}
		$this->statement = $loSql;

		return $this;
	}

	/*
	 * ejecuta la consulta en $this->sql usando los valores vinculados indicados en $this->bindValues
	 */
	public function ejecutar($tAll = true, $tbArray = false)
	{
		if (!$this->statement) {
			return false;
		}

		if (is_array($tAll)) {
			$this->bindValues += $tAll;
		}
		$this->utf8_decode_deep($this->bindValues);
		$lResultado = $this->statement->execute($this->bindValues);

		if ($lResultado === false) {
			return $this->errorPDO();
		}

		$tbStr = false;
		if(stripos($this->sql, 'select') === 0) {
			$tbStr = true;
		}

		if ($tbStr) {
			$lnModo = ($tbArray == false) ? \PDO::FETCH_OBJ : \PDO::FETCH_ASSOC;
			if ($tAll) {
				$laResult = [];

				while ($lResult = $this->statement->fetch($lnModo)) {
					$laResult[] = $lResult;
				}
				$this->result = $laResult;
/*
				if ($lResult = $this->statement->fetchAll($lnModo)) {
					$laResult[] = $lResult;
				}
				$this->result = isset($laResult[0]) ? $laResult[0] : [];
*/
				$this->numRows = count($this->result);

			} else {
				$lResult = $this->statement->fetch($lnModo);
				$this->numRows = ($this->result = $lResult) ? 1 : 0;
			}

		} else {
			$this->result = $lResultado;

			if ($this->result === false) {
				return $this->errorPDO();
			}
		}
		$this->utf8_encode_deep($this->result);

		$this->queryCount++;

		if ($this->debug === true) {
			echo 'Db::bindValues';
			var_dump($this->bindValues);
		}
		$this->bindValues = [];

		return $this->result;
	}

	private function errorPDO()
	{
		$this->error = $this->pdo->errorInfo();
		if (is_null($this->error[2])) {
			$this->error = $this->statement->errorInfo();
		}
		$this->error = $this->error[2];
		return $this->error();
	}

	public function error()
	{
		if($this->debug === true) {
			throw new PDOException($this->error . '. ('  . $this->sql . ')');

		} else {
			$this->utf8_encode_deep($this->error);
			$lcUsr=defined('HCW_NAME')?(isset($_SESSION[HCW_NAME])?(!empty($_SESSION[HCW_NAME]->oUsuario->getUsuario())?" - Usuario: ".$_SESSION[HCW_NAME]->oUsuario->getUsuario():''):''):'';
			$lcVal=str_replace("\r", '', str_replace("\n", '', var_export($this->bindValues, true)));
			$lcTrace=$this->generaTraza();
			$lcMsg='<h1>Database Error</h1><h4>Error: <em style="font-weight:normal;">'.$this->error.'</em></h4>';
			$lcMsgErr =
				"DATABASE ERROR $lcUsr" . PHP_EOL .
				"\t- Query: {$this->sql}". PHP_EOL .
				"\t- bindValues: $lcVal" . PHP_EOL .
				"\t- Error: {$this->error}" . PHP_EOL . $lcTrace;
			error_log($lcMsgErr);
			if ($this->onErrorDie){
				// die($lcMsg);
				die($lcMsgErr);
			} else {
				// throw new PDOException($this->error . '. ('  . $this->sql . ')');
				// exit();
			}
		}
	}

	public function escape($tData)
	{
		if($tData === NULL) {
			return 'NULL';
		}

		if(is_null($tData)) {
			return null;
		}

		return "'" . trim($tData) . "'";
	}

	public function queryCount()
	{
		return $this->queryCount;
	}

	public function getQuery()
	{
		return $this->sql;
	}

	public function reset()
	{
		$this->select		= '*';
		$this->distinct		= '';
		$this->statement	= null;
		$this->from			= null;
		$this->where		= null;
		$this->limit		= null;
		$this->offset		= null;
		$this->orderBy		= null;
		$this->groupBy		= null;
		$this->having		= null;
		$this->join			= null;
		$this->grupo		= false;
		$this->grupoOpen	= '';
		$this->numRows		= 0;
		$this->sql			= null;
		$this->error		= null;
		$this->result		= [];

		return;
	}

	/*
	 * storedProcedure
	 *	Llamado a un procedimiento almacenado de AS400
	 *		- $tcProcedimiento:	string	nombre del procedimiento almacenado
	 *		- $taParamIn:		array	lista de parametros de envío, indicando valor y tipo
	 *							ejemplo [ 'Accion'=>['ACC',PDO::PARAM_STR], 'Cantidad'=>['5',PDO::PARAM_STR] ]
	 *		- $taParamOut:		array	lista parámetros de salida, indicando tipo y longitud
	 *							ejemplo [ 'Retorno'=>[PDO::PARAM_STR|PDO::PARAM_INPUT_OUTPUT,10] ]
	 *		- $tlType:			true retorna la sentencia sql, false o omitido ejecuta
	 */
	public function storedProcedure($tcProcedimiento, $taParamIn = null, $taParamOut = null, $tlType = false)
	{
		$lcComa = '';
		$laRetorno = [];
		$lbHayParamIn = false;
		$lbHayRetorno = false;
		$this->clearBindValue();

		// Crear el llamado al procedimiento almacenado
		$lcQuery = 'CALL ' . trim($tcProcedimiento) . ' (';
		if(!is_null($taParamIn) && is_array($taParamIn)) {
			$lbHayParamIn = true;
			$lcClaves = array_keys($taParamIn);
			$lcQuery .= ':' . implode(',:', $lcClaves);
			$lcComa = ',';
		}

		if(!is_null($taParamOut) && is_array($taParamOut)) {
			$lbHayRetorno = true;
			$lcClaves = array_keys($taParamOut);
			$lcQuery .= $lcComa . ':' . implode(',:', $lcClaves);
		}
		$this->sql = $lcQuery . ')';
		$loSql = $this->pdo->prepare($this->sql);

		// vincula parámetros y ejecuta
		if($lbHayParamIn) {
			foreach($taParamIn as $lcClave => $laValor) {
				$loSql->bindValue(':' . $lcClave, $laValor[0], $laValor[1]);
			}
		}
		if($lbHayRetorno) {
			foreach($taParamOut as $lcClave => $laValor) {
				$loSql->bindParam(':'.$lcClave, $laRetorno[$lcClave], $laValor[0], $laValor[1]);
			}
		}

		// Retorna la sentencia
		if ($tlType) {
			return $this->sql;
		}

		// Ejecuta el procedimiento
		if ($loSql->execute()) {
			if($lbHayRetorno) {
				return $laRetorno;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	/*
	 * storedProcedureCursor
	 *	Llamado a un procedimiento almacenado que retorna un cursor
	 *		- $tcProcedimiento:	string	nombre del procedimiento almacenado
	 *		- $taParamIn:		array	lista de parametros de envío, indicando valor y tipo
	 *							ejemplo [ 'Accion'=>['ACC',PDO::PARAM_STR], 'Cantidad'=>['5',PDO::PARAM_STR] ]
	 *		- $tlType:			true retorna la sentencia sql, false o omitido ejecuta
	 */
	public function storedProcedureCursor($tcProcedimiento, $taParamIn = null, $tbArray = false)
	{
		$lcComa = '';
		$lbHayParamIn = false;
		$this->clearBindValue();

		// Crear el llamado al procedimiento almacenado
		$lcQuery = 'CALL ' . trim($tcProcedimiento) . ' (';
		if(!is_null($taParamIn) && is_array($taParamIn)) {
			$lbHayParamIn = true;
			$lcClaves = array_keys($taParamIn);
			$lcQuery .= ':' . implode(',:', $lcClaves);
			$lcComa = ',';
		}
		$this->sql = preg_replace("/\s\s+|\t\t+/", ' ', $lcQuery . ')');
		$this->utf8_decode_deep($this->sql);
		$loSql = $this->pdo->prepare($this->sql);
		if (!$loSql) {
			return $this->errorPDO();
		}

		// vincula parámetros y ejecuta
		if($lbHayParamIn) {
			foreach($taParamIn as $lcClave => $laValor) {
				$loSql->bindValue(':' . $lcClave, $laValor[0], $laValor[1]);
			}
		}

		$lResultado = $loSql->execute();
		if ($lResultado === false) {
			return $this->errorPDO();
		}

		// obtiene valores de resultado
		$lnModo = $tbArray ? \PDO::FETCH_ASSOC : \PDO::FETCH_OBJ;
		$laResult = [];
		while ($lResultado = $loSql->fetch($lnModo)) {
			$laResult[] = $lResultado;
		}
		$this->result = $laResult;
		$this->numRows = count($this->result);
		$this->utf8_encode_deep($this->result);
		$this->bindValues = [];

		return $this->result;
	}


	/*
	 * idLlaveUnica: Identificador de llave unica en las tablas que se requiere
	 */
	public function obtenerLlaveUnicaTabla()
	{
		$laDatos = $this->select('ALPHILDAT.GetUUID() AS LLAVE')->from('SYSIBM.SYSDUMMY1')->getAll();
		return $laDatos[0]->LLAVE;
	}

	/*
	 * FechaHoraSistema: Retorna fecha y hora actural de AS400
	 */
	public function fechaHoraSistema()
	{
		//$laDatos = $this->select('NOW() AS AHORA')->from('SYSIBM.SYSDUMMY1')->getAll();
		$laDatos = $this->select('CURRENT TIMESTAMP AS AHORA')->from('SYSIBM.SYSDUMMY1')->getAll();
		return $laDatos[0]->AHORA;
	}

	/*
	 * secuencia: Retorna siguiente valor de la secuencia y lo actualiza en la tabla RIACON
	 *
	 *	@param string $tcSecuencia: Nombre de la secuencia
	 *	@param int $tnCodigo: Código a actualizar en RIACON
	 *	@return int Número consecutivo de la secuencia
	 */
	public function secuencia($tcSecuencia='', $tnCodigo=null, $tnIntentos=10)
	{
		$lnConsec = 0;
		if($tcSecuencia !== ''){
			$lnIntentos=0;
			while($lnIntentos < $tnIntentos){
				$lnIntentos++;
				$laDatos = $this
					->select('NEXT VALUE FOR Alphildat.'.$tcSecuencia.' AS CONSEC')
					->from('SYSIBM.SYSDUMMY1')
					->getAll();
				if($this->numRows > 0){
					$lnConsec=$laDatos[0]->CONSEC;
				}
				if($lnConsec>0) break;
			}

			// Actualizar RIACON
			if($lnConsec > 0 && is_numeric($tnCodigo)){
				$ltAhora = new \DateTime( $this->fechaHoraSistema() );
				$lcFecha = $ltAhora->format('Ymd');
				$lcHora  = $ltAhora->format('His');
				$laData = [
					'CONCON' => $lnConsec,
					'UMOCON' => 'HCWEB',
					'PMOCON' => 'HCWEB',
					'FMOCON' => $lcFecha,
					'HMOCON' => $lcHora
				];
				$lnRta = $this
					->from('RIACON')
					->where('CODCON','=',$tnCodigo)
					->where('CONCON', '<', $lnConsec)
					->actualizar($laData);
			}
		}
		return $lnConsec;
	}

	/*
	 * obtenerConsecRiacon: Retorna nuevo consecutivo en la tabla RIACON
	 *
	 *	@param int tnCodigo: Código en la tabla riacon
	 *	@param string tcPrograma: Nombre del programa desde el que se llama
	 *	@param int tnMaxUpdate: Máximo número intentos para actualizar, predeterminado 20
	 *	@param string tcUsuario: Nombre del usuario que realiza la actualizacion
	 */
	public function obtenerConsecRiacon($tnCodigo=0, $tcPrograma='UPD_RIACON', $tnMaxUpdate=20, $tcUsuario = 'HCWEB')
	{
		$tnCodigo = intval($tnCodigo);
		$tcPrograma = trim(strval($tcPrograma));
		$tcPrograma = (!empty($tcPrograma) ? strtoupper(trim($tcPrograma)) : "UPD_RIACON");
		$tnMaxUpdate = intval($tnMaxUpdate);
		$tcUsuario = trim(!empty($tcUsuario) ? strval($tcUsuario): 'HCWEB');

		$lnConsecutivo = 0;

		if(is_numeric($tnCodigo)==true && !empty($tnCodigo)){

			$ltAhora = new \DateTime( $this->fechaHoraSistema() );
			$lcFecha = $ltAhora->format('Ymd');
			$lcHora  = $ltAhora->format('His');

			$laRegistro = $this->select('R.CONCON CONSECUTIVO')
			   ->from('RIACON R')
			   ->where('R.CODCON', '=', $tnCodigo)
			   ->get('array');

			$lnConsecutivo = intval(is_array($laRegistro)==true ? (count($laRegistro)>0 ? (is_null($laRegistro['CONSECUTIVO']) ? 0 : $laRegistro['CONSECUTIVO']) : 0) : 0)+1;

			$laCampos = [
				'CONCON' => $lnConsecutivo,
				'UMOCON' => $tcUsuario,
				'PMOCON' => $tcPrograma,
				'FMOCON' => $lcFecha,
				'HMOCON' => $lcHora
			];

			$lnRta = $this
				->from('RIACON')
				->where('CODCON','=',$tnCodigo)
				->where('CONCON', '<', $lnConsecutivo)
				->actualizar($laCampos);
		}

		return $lnConsecutivo;
	}

	/*
	 * obtenerTabMae: Retorna valor de varios campo en la tabla TABMAE
	 */
	public function obtenerTabMae($tcCampo, $tcTiptma, $tcWhere='', $tcOrder=null, $tbVariasFilas=false)
	{
		$this->select($tcCampo)
			 ->from('tabmae')
			 ->where('tiptma','=',$tcTiptma);
		if ($tcWhere !== null) { $this->where($tcWhere); }
		if ($tcOrder !== null) { $this->orderBy($tcOrder,' '); }

		if ($laDatos = ($tbVariasFilas ? $this->getAll() : $this->get())) {
			return $laDatos;
		} else {
			return [];
		}
	}

	/*
	 * obtenerTabMae1: Retorna un valor de la tabla TABMAE
	 */
	public function obtenerTabMae1($tcCampo, $tcTiptma, $tcWhere='', $tcOrder=null, $tuPredeterminado='')
	{
		$loTabMae = $this->ObtenerTabMae($tcCampo.' RETORNO', $tcTiptma, $tcWhere, $tcOrder);
		if (is_string($tcCampo)) {
			require_once __DIR__ . '/class.AplicacionFunciones.php';
			$luReturn = AplicacionFunciones::getValue($loTabMae, 'RETORNO', $tuPredeterminado);
			$luReturn = is_string($luReturn) ? trim($luReturn) : $luReturn;
			return $luReturn;
		} else {
			return $loTabMae;
		}

	}


	/*
	 *	configServer: Retorna configuración para linux de un server
	 *	@param string $tcServer: IP o nombre del servidor
	 *	@return array ['workgroup'=>'', 'user'=>'', 'pass'=>'']
	 */
	public function configServer($tcServer='')
	{
		$laResp = [];
		if (!empty($tcServer)) {
			$loTabMae = $this->obtenerTabMae('DE2TMA||OP5TMA AS CONFIG', 'CNFSRVS', ['DE1TMA'=>$tcServer]);
			$laResp = explode('¤', trim(AplicacionFunciones::getValue($loTabMae, 'CONFIG', '').''));
		}
		return [
			'workgroup'=>$laResp[0] ?? '',
			'user'=>$laResp[1] ?? '',
			'pass'=>$laResp[2] ?? '',
		];
	}


	//obtenerPrmtab: Retorna valor de un campo en la tabla PRMTAB
	public function obtenerPrmtab($tcCampo, $tcTiptma, $tcWhere='', $tcOrder=null)
	{
		$this->select($tcCampo)
			 ->from('PRMTAB')
			 ->where('TABTIP','=',$tcTiptma);
		if ($tcWhere !== null) { $this->where($tcWhere); }
		if ($tcOrder !== null) { $this->orderBy($tcOrder,' '); }

		if ($laDatos = $this->get()) {
			return $laDatos;
		} else {
			return [];
		}
	}

	/*
	 * utf8_encode_deep: codificar cadenas a utf8
	 */
	public static function utf8_encode_deep(&$input)
	{
		$laVersionPHP = explode('.', phpversion());
		if (intval($laVersionPHP[0])<8 || strtoupper(substr(PHP_OS, 0, 3))==='WIN') {
			if (is_string($input)) {
				$input = utf8_encode($input);

			} else if (is_array($input)) {
				foreach ($input as &$value) {
					self::utf8_encode_deep($value);
				}
				unset($value);

			} else if (is_object($input)) {
				$vars = array_keys(get_object_vars($input));
				foreach ($vars as $var) {
					self::utf8_encode_deep($input->$var);
				}
			}
		}
	}

	/*
	 * utf8_decode_deep: codificar cadenas a utf8
	 */
	public static function utf8_decode_deep(&$input)
	{
		$laVersionPHP = explode('.', phpversion());
		if (intval($laVersionPHP[0])<8 || strtoupper(substr(PHP_OS, 0, 3))==='WIN') {
			if (is_string($input)) {
				$input = utf8_decode($input);

			} else if (is_array($input)) {
				foreach ($input as &$value) {
					self::utf8_decode_deep($value);
				}
				unset($value);

			} else if (is_object($input)) {
				$vars = array_keys(get_object_vars($input));
				foreach ($vars as $var) {
					self::utf8_decode_deep($input->$var);
				}
			}
		}
	}

	public function obtenerEntorno()
	{
		return $this->config['system'];
	}

	public function obtenerError()
	{
		return $this->error;
	}

	function __destruct()
	{
		$this->pdo = null;
	}
}

$goDb = new Db();
?>