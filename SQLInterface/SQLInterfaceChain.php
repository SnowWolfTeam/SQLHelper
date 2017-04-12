<?php
namespace SQLHelper\SQLInterface;
interface SQLInterfaceChain
{
    //查询
    public function select();

    //统计
    public function count();

    //插入
    public function insert($params);

    //更新
    public function update($params);

    //删除
    public function delete();

    //开启事务
    public function transactionStart();

    //提交事务
    public function commit();

    //回滚
    public function rollback();
}