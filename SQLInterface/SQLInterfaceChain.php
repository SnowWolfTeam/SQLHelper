<?php
namespace SQLHelper\SQLInterface;
interface SQLInterfaceChain
{
    //查询
    function select();

    //统计
    function count();

    //插入
    function insert($params);

    //更新
    function update($params);

    //删除
    function delete();
}