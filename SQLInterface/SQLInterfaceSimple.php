<?php
namespace SQLHelper\SQLInterface;

interface SQLInterfaceSimple
{
    //查询
    function select($sqlString, $pdoParams = []);

    //统计
    function count($sqlString, $pdoParams = []);

    //插入
    function insert($sqlString, $pdoParams = []);

    //更新
    function update($sqlString, $pdoParams = []);

    //删除
    function delete($sqlString, $pdoParams = []);
}