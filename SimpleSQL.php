<?php
namespace SqlHelper;


use PDO;
use SQLHelper\SQLAbstract\SQLBase;
use SQLHelper\SQLInterface\SQLInterfaceSimple;
use SQLHelper\Exception\SQLHelperException;

class SimpleSQL extends SQLBase implements SQLInterfaceSimple
{
    public function __construct($pdoParams)
    {
        parent::__construct($pdoParams);
    }

    public function changePdoparams($pdoParams)
    {
        $this->pdoConParams = $pdoParams;
        $this->pdoInstance = NULL;
    }

    public function select($sqlString, $params = [])
    {
        // TODO: Implement select() method.
        $result = false;
        if (empty($params)) {
            $pdoSearchResult = $this->pdoInstanceFunc();
            $queryResult = $pdoSearchResult ? $this->pdoInstance->query($sqlString) : false;
            $result = is_object($queryResult) ? $queryResult->fetchAll(PDO::FETCH_ASSOC) : false;
        } else {
            $executeResult = $this->sqlExecute($sqlString, $params);
            $result = ($executeResult === false) ? false : $executeResult->fetchAll(PDO::FETCH_ASSOC);
        }
        return $result;
    }

    public function update($sqlString, $params = [])
    {
        // TODO: Implement update() method.
        $result = false;
        if (empty($params)) {
            $pdoSearchResult = $this->pdoInstanceFunc();
            $result = $pdoSearchResult ? $this->pdoInstance->exec($sqlString) : false;
        } else {
            $execute = $this->sqlExecute($sqlString, $params);
            $result = is_object($execute) ? (int)$execute->rowCount() : false;
        }
        return $result;
    }

    public function insert($sqlString, $params = [])
    {
        // TODO: Implement insert() method.
        $result = false;
        if (empty($params)) {
            $pdoSearchResult = $this->pdoInstanceFunc();
            $result = $pdoSearchResult ? $this->pdoInstance->exec($sqlString) : false;
        } else {
            $execute = $this->sqlExecute($sqlString, $params);
            $result = is_object($execute) ? (int)$execute->rowCount() : false;
        }
        return $result;
    }

    public function count($sqlString, $params = [])
    {
        // TODO: Implement count() method.
        $result = false;
        if (empty($params)) {
            $pdoSearchResult = $this->pdoInstanceFunc();
            $queryResult = $pdoSearchResult ? $this->pdoInstance->query($sqlString) : false;
        } else
            $queryResult = $this->sqlExecute($sqlString, $params);

        $fetchResult = $queryResult ? $queryResult->fetchAll(PDO::FETCH_NUM) : false;
        if ($fetchResult !== false) {
            $size = sizeof($fetchResult);
            if ($size > 1) {
                $result = [];
                for ($i = 0; $i < $size; $i++)
                    $result[] = (int)$fetchResult[$i][0];
            } else
                $result = (int)$fetchResult[0][0];
        }
        return $result;
    }

    public function delete($sqlString, $params = [])
    {
        // TODO: Implement delete() method.
        $result = false;
        if (empty($params)) {
            $pdoSearchResult = $this->pdoInstanceFunc();
            $result = $pdoSearchResult ? $this->pdoInstance->exec($sqlString) : false;
        } else {
            $executeResult = $this->sqlExecute($sqlString, $params);
            $result = is_object($executeResult) ? $executeResult->rowCount() : false;
        }
        return $result;
    }

    public function transactionStart()
    {
        if ($this->pdoInstance instanceof \PDO) {
            $this->pdoInstance->setAttribute(PDO::ATTR_AUTOCOMMIT, false);
            $this->pdoInstance->beginTransaction();
            return true;
        } else
            trigger_error('PDO未连接', E_USER_WARNING);
    }

    public function commit()
    {
        if ($this->pdoInstance instanceof \PDO) {
            $this->pdoInstance->commit();
            $this->pdoInstance->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
            return true;
        } else
            trigger_error('PDO未连接', E_USER_WARNING);
    }

    public function rollback()
    {
        if ($this->pdoInstance instanceof \PDO) {
            $this->pdoInstance->rollBack();
            $this->pdoInstance->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
            return true;
        } else
            trigger_error('PDO未连接', E_USER_WARNING);
    }
}