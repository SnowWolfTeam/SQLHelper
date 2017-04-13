<?php
namespace SqlHelper;

use LuckyDraw\Exception\LuckyDrawException;
use PDO;
use SQLHelper\SQLAbstract\SQLBase;
use SQLHelper\SQLInterface\SQLInterfaceChain;
use SQLHelper\Exception\SQLHelperException;

class ChainSQL extends SQLBase implements SQLInterfaceChain
{
    private $sqlPrepareParams = [];
    private $whereValues = [];

    public function __construct($conParam)
    {
        parent::__construct($conParam);
    }

    public function changePdoParams($pdoParams)
    {
        $this->pdoConParams = $pdoParams;
        $this->pdoInstance = NULL;
    }

    /**
     * 表
     */
    public function tables($params)
    {
        if (!empty($params)) {
            $this->whereValues = [];
            $this->sqlPrepareParams = [];
            $this->sqlPrepareParams[':tables'] = is_array($params) ? implode(',', $params) : $params;
        } else
            trigger_error('表名不能为空', E_USER_WARNING);
        return $this;
    }

    /**
     * AND条件
     */
    public function where($key, $operator = '', $values = '')
    {
        if ($key instanceof \Closure) {
            $this->sqlPrepareParams[':wheres'] .= empty($this->sqlPrepareParams[':wheres']) ? '(' : ' and (';
            $key($this);
            $this->sqlPrepareParams[':wheres'] .= ')';
        } else if ((is_string($key) || is_array($key)) && ($key != "")) {
            if (!empty($this->sqlPrepareParams[':wheres'])) {
                $whereStr = rtrim($this->sqlPrepareParams[':wheres']);
                $lastChar = $whereStr{strlen($whereStr) - 1};
                if ($lastChar !== ')' && $lastChar !== '(')
                    $this->sqlPrepareParams[':wheres'] .= ' and ';
            }
            $this->whereSubFunc($key, $operator, $values, ' and ');
        }
        return $this;
    }

    /**
     * Or条件
     */
    public function orwhere($key, $operator = NULL, $values = NULL)
    {
        if ($key instanceof \Closure) {
            $this->sqlPrepareParams[':wheres'] .= empty($this->sqlPrepareParams[':wheres']) ? '(' : ' or (';
            $key($this);
            $this->sqlPrepareParams[':wheres'] .= ')';
        } else if ((is_string($key) || is_array($key)) && ($key != "")) {
            if (!empty($this->sqlPrepareParams[':wheres'])) {
                $whereStr = rtrim($this->sqlPrepareParams[':wheres']);
                $lastChar = $whereStr{strlen($whereStr) - 1};
                if ($lastChar !== ')' && $lastChar !== '(')
                    $this->sqlPrepareParams[':wheres'] .= ' or ';
            }
            $this->whereSubFunc($key, $operator, $values, ' or ');
        }
        return $this;
    }

    private function whereSubFunc($key, $operator, $values, $str)
    {
        if (!empty($operator)) {
            if (!empty($values)) {
                if (is_string($key) && is_string($operator) && is_string($values)) {
                    $this->sqlPrepareParams[':wheres'] .= $key . $operator . '?';
                    $this->whereValues[] = $values;
                } else if (is_array($key) && is_array($values)) {
                    foreach ($values as $value) {
                        $this->whereValues[] = $value;
                    }
                    $size = sizeof($key);
                    $operatorSize = sizeof($operator);
                    if (is_string($operator))
                        $operator = array_fill(0, $size, $operator);
                    elseif (is_array($operator)) {
                        $operator = array_merge($operator, array_fill($operatorSize - 1, $size - $operatorSize, '='));
                    }
                    for ($i = 0; $i < $size; $i++)
                        $key[$i] = $key[$i] . $operator[$i] . '?';
                    $this->sqlPrepareParams[':wheres'] .= implode($str, $key);
                }
            } else {
                if (is_string($key) && is_string($operator)) {
                    $this->sqlPrepareParams[':wheres'] .= $key . '=' . '?';
                    $this->whereValues[] = $operator;
                } else if (is_array($key) && is_array($operator)) {
                    foreach ($operator as $value) {
                        $this->whereValues[] = $value;
                    }
                    $size = sizeof($key);
                    for ($i = 0; $i < $size; $i++)
                        $key[$i] = $key[$i] . '=?';
                    $this->sqlPrepareParams[':wheres'] .= implode($str, $key);
                }
            }
        } else
            $this->sqlPrepareParams[':wheres'] .= is_array($key) ? implode($str, $key) : $key;
    }

    /**
     * 域
     */
    public function fields($params)
    {
        if (!empty($params))
            $this->sqlPrepareParams[':fields'] = $params;
        else
            trigger_error('字段名不能为空', E_USER_WARNING);
        return $this;
    }

    /**
     * 排序
     */
    public function order($params)
    {
        if (!empty($params)) {
            $this->sqlPrepareParams[':orderby'] = 'order by ' . (is_array($params) ? implode(',', $params) : $params);
        } else
            trigger_error('order排序参数不能为空', E_USER_WARNING);
        return $this;
    }

    /**
     * 分组
     */
    public function group($params)
    {
        if (!empty($params)) {
            $this->sqlPrepareParams[':groupby'] = 'group by ' . (is_array($params) ? implode(',', $params) : $params);
        } else
            trigger_error('group分组参数不能为空', E_USER_WARNING);
        return $this;
    }

    /**
     * 条数
     */
    public function limit($params)
    {
        if (!empty($params))
            $this->sqlPrepareParams[':limit'] = 'limit ' . (is_array($params) ? implode(',', $params) : $params);
        else
            trigger_error('limit分组参数不能为空', E_USER_WARNING);
        return $this;
    }

    /**
     * 查询数据
     */
    public function select()
    {
        !empty($this->sqlPrepareParams[':fields']) or $this->sqlPrepareParams[':fields'] = '*';
        is_string($this->sqlPrepareParams[':fields']) or $this->sqlPrepareParams[':fields'] = implode(',', $this->sqlPrepareParams[':fields']);
        $sqlPrepareString = 'select :fields from :tables '
            . (empty($this->sqlPrepareParams[':wheres']) ? '' : ' where ')
            . ' :wheres ' . $this->selectSqlStringEnd();
        $sqlPrepareString = str_replace(
            [':fields', ':tables', ':wheres'],
            [
                $this->sqlPrepareParams[':fields'],
                $this->sqlPrepareParams[':tables'],
                $this->sqlPrepareParams[':wheres']
            ],
            $sqlPrepareString
        );
        echo $sqlPrepareString;
        $result = $this->sqlExecute($sqlPrepareString, $this->whereValues);
        if ($result === false)
            return false;
        else
            return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 统计
     */
    public function count()
    {
        !empty($this->sqlPrepareParams[':fields']) or $this->sqlPrepareParams[':fields'] = '*';
        $sqlPrepareString = 'select count(:fields) from :tables '
            . (empty($this->sqlPrepareParams[':wheres']) ? '' : ' where ')
            . ' :wheres' . $this->countSqlStringEnd();
        $sqlPrepareString = str_replace(
            [':fields', ':tables', ':wheres'],
            [
                explode(',', $this->sqlPrepareParams[':fields'])[0],
                $this->sqlPrepareParams[':tables'],
                $this->sqlPrepareParams[':wheres']
            ],
            $sqlPrepareString
        );
        echo $sqlPrepareString;
        $execResult = $this->sqlExecute($sqlPrepareString, $this->whereValues);
        $result = false;
        if ($execResult === false) {
            trigger_error('执行统计SQL失败', E_USER_WARNING);
            return false;
        } else {
            $rows = $execResult->fetchAll();
            if ($rows !== false) {
                if (sizeof($rows) > 1)
                    foreach ($rows as $value)
                        $result[] = (int)$value[0];
                else
                    $result = (int)$rows[0][0];
            }
        }
        return $result;
    }

    /**
     * 插入数据
     */
    public function insert($params)
    {
        if (!empty($params) && is_array($params)) {
            $paramsKeys = array_keys($params);
            $paramsValues = array_values($params);
            $this->checkParamsType($paramsValues);
            $paramsSize = sizeof($paramsValues);
            $sqlPrepareString = 'insert into :tables(' . implode(',', $paramsKeys) . ')values(' . implode(',', array_fill(0, $paramsSize, '?')) . ')';
            $sqlPrepareString = str_replace(':tables', $this->sqlPrepareParams[':tables'], $sqlPrepareString);
            $execResult = $this->sqlExecute($sqlPrepareString, $paramsValues);
            return is_object($execResult) ? (int)$this->pdoInstance->lastInsertId() : false;
        } else {
            trigger_error('插入数据库输入参数不能为空', E_USER_WARNING);
            return false;
        }
    }

    /**
     * 更新数据
     */
    public function update($params)
    {
        if (empty($params) && !is_array($params)) {
            trigger_error('需要更新的数据为空', E_USER_WARNING);
            return false;
        } else {
            $sqlPrepareString = 'update :tables set :datas '
                . (empty($this->sqlPrepareParams[':wheres']) ? '' : ' where ')
                . ' :wheres '
                . (empty($this->sqlPrepareParams[':limit']) ? '' : $this->sqlPrepareParams[':limit']);
            $paramsKey = [];
            $paramsValues = [];
            foreach ($params as $key => $values) {
                $paramsKey[] = $key . '=?';
                $paramsValues[] = $values;
            }
            foreach ($this->whereValues as $values)
                $paramsValues[] = $values;
            $sqlPrepareString = str_replace(
                [':tables', ':wheres', ':datas'],
                [
                    $this->sqlPrepareParams[':tables'],
                    $this->sqlPrepareParams[':wheres'],
                    implode(',', $paramsKey)
                ],
                $sqlPrepareString
            );
            $execResult = $this->sqlExecute($sqlPrepareString, $paramsValues);
            return is_object($execResult) ? $execResult->rowCount() : false;
        }
    }

    /**
     * 删除数据
     */
    public function delete()
    {
        $sqlPrepareString = 'delete from :tables '
            . (empty($this->sqlPrepareParams[':wheres']) ? '' : ' where ')
            . ' :wheres '
            . (empty($this->sqlPrepareParams[':limit']) ? '' : $this->sqlPrepareParams[':limit']);
        $sqlPrepareString = str_replace(
            [':tables', ':wheres'],
            [
                $this->sqlPrepareParams[':tables'],
                $this->sqlPrepareParams[':wheres']
            ],
            $sqlPrepareString
        );
        $execResult = $this->sqlExecute(
            $sqlPrepareString,
            $this->whereValues);
        return is_object($execResult) ? $execResult->rowCount() : false;
    }

    /**
     * 原生查询
     */
    public function query($sqlString, $type)
    {
        if ($this->pdoInstanceFunc()) {
            $result = false;
            switch ($type) {
                case "select":
                    $pdoSearchResult = $this->pdoInstance->query($sqlString);
                    var_dump($pdoSearchResult);
                    $pdoSearchResult === false or $result = $pdoSearchResult->fetchAll(PDO::FETCH_ASSOC);
                    break;
                case "count":
                    $pdoSearchResult = $this->pdoInstance->query($sqlString);
                    $rows = $pdoSearchResult->fetchAll();
                    if ($rows !== false) {
                        if (sizeof($rows) > 1)
                            foreach ($rows as $value)
                                $result[] = (int)$value[0];
                        else{
                            $result = (int)$rows[0][0];
                        }
                    }
                    break;
                case "insert":
                case "update":
                case "delete":
                case "query":
                    $result = $this->pdoInstance->exec($sqlString);
                    break;
                default:
                    trigger_error('原生执行type不存在', E_USER_WARNING);
                    $result = false;
                    break;
            }
            return $result;
        } else {
            trigger_error('pdo实例为空', E_USER_WARNING);
            return false;
        }
    }

    private function selectSqlStringEnd()
    {
        $str = [];
        empty($this->sqlPrepareParams[':groupby']) or $str[] = $this->sqlPrepareParams[':groupby'];
        empty($this->sqlPrepareParams[':orderby']) or $str[] = $this->sqlPrepareParams[':orderby'];
        empty($this->sqlPrepareParams[':limit']) or $str[] = $this->sqlPrepareParams[':limit'];
        return ' ' . implode(' ', $str);
    }

    private function countSqlStringEnd()
    {
        $str = [];
        empty($this->sqlPrepareParams[':groupby']) or $str[] = $this->sqlPrepareParams[':groupby'];
        empty($this->sqlPrepareParams[':orderby']) or $str[] = $this->sqlPrepareParams[':orderby'];
        return ' ' . implode(' ', $str);
    }

    public function transactionStart()
    {
        if ($this->pdoInstance instanceof \PDO) {
            $this->pdoInstance->setAttribute(PDO::ATTR_AUTOCOMMIT, false);
            $this->pdoInstance->beginTransaction();
        } else {
            $result = $this->pdoInstanceFunc();
            if (!$result) trigger_error('PDO未连接', E_USER_WARNING);
            $this->pdoInstance->setAttribute(PDO::ATTR_AUTOCOMMIT, false);
            $this->pdoInstance->beginTransaction();
        }
    }

    public function commit()
    {
        if ($this->pdoInstance instanceof \PDO) {
            $this->pdoInstance->commit();
            $this->pdoInstance->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
        } else
            trigger_error('PDO未连接', E_USER_WARNING);
    }

    public function rollback()
    {
        if ($this->pdoInstance instanceof \PDO) {
            $this->pdoInstance->rollBack();
            $this->pdoInstance->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
        } else
            trigger_error('PDO未连接', E_USER_WARNING);
    }
}