<?php
namespace SQLHelper\SQLAbstract;

use PDO;

abstract class SQLBase
{
    protected $pdoInstance = NULL;
    protected $pdoConParams = NULL;

    public function __construct($pdoParams)
    {
        $this->pdoConParams = $pdoParams;
    }

    final protected function pdoInstanceFunc()
    {
        try {
            if (empty($this->pdoInstance))
                $this->pdoInstance = new \PDO($this->pdoConParams['dsn'], $this->pdoConParams['user'], $this->pdoConParams['pw']);
            return empty($this) ? false : true;
        } catch (\PDOException $ex) {
            return false;
        }
    }

    final protected function sqlExecute($sqlString, $customParams)
    {
        try {
            $result = false;
            if (empty($this->pdoInstance))
                $this->pdoInstance = new \PDO($this->pdoConParams['dsn'], $this->pdoConParams['user'], $this->pdoConParams['pw']);
            $sth = $this->pdoInstance->prepare($sqlString, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
            if ($sth !== false)
                $result = $sth->execute($customParams);
            return $result ? $sth : $result;
        } catch (\PDOException $ex) {
            return false;
        }
    }

    final protected function checkParamsType(&$params)
    {
        foreach ($params as $key => $values) {
            if (is_string($params))
                $params[$key] = "'" . $values . "'";
        }
    }
}