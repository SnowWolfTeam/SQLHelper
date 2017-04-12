#PDO数据封装类

#### ChainSQL.php：
此封装类提供链式查询sql语句
* 例子：
```
    1.$pdo = ['dsn'=>(dsn字符串，具体查pdo dsn) , 'user'=>(数据库用户名),'pw'=>(数据库用户密码)]  //pdo参数
    2.$test = new ChainSQL($pdo);
    3.$test->changePdoparams($pdo);             //调用此函数可以随时改变pdo参数
    4.$result = $test->tables(['a','b'])        //表函数，可以是数组，或者字符串
            ->fields(['name','pw'])           //搜索的字段名，可以是数组或字符串
            ->where(['name'],['='],['my'])    //第一个参数是条件字段名，第二个是操作符，第三个是条件
            ->order(['name desc'])            //排序，可以是数组或字符串
            ->group(['name','pw'])->select(); //分组，可以是数组或字符串
```
###### 接口
* 1 . tables($params) 表
```
    1.$params = sql操作要用到的表
    2.例子: tables(['name','pw'])
          tables('name,pw')
```
* 2 . fields($params) 字段
```
    1.$params = 检索的字段名
    2.例子: fields(['name','pw'])
          fields('name,pw')
```
* 3 . order($params) 排序
```
    1.$params = 检索结果排序规则
    2.例子: order(['name desc','pw asc'])
          order('name desc,pw asc')
```
* 4 . group($params) 分组
```
    1.$params = 分组字段
    2.例子: group(['name','pw'])
          group('name,pw')
```
* 5 . limit($params) 检索数据行数
```
    1.$params = 检索行数
    2.例子: limit([0,10]) 或 limit('0,10')  //表从第0行开始的10条数据
          limit([10]) 或 limit('10')      //前10条
```
* 6 . where($key,$char=[],$values=[]) 与检索条件
```
    1.当$char,$values为空时,$key=['name=1','pw=1'] 或 $key='name=1 and pw=1'
    2.当$values为空时,操作符默认为'=' , $key=['name','pw'] , $char=[1,1](值)
    3.都不为空是,$key=['name','pw'],$char=['=','>'],$values=[1,11];
    4.实现'a=1 and ( b=1 or c=1 )'的条件嵌套可以:
        ->where('a=1')
        ->where(function($query){
            $query->where('b=1')
            ->orwhere('c=1')
        })//
```
* 7 . orwhere($key,$char=[],$values=[]) 或检索条件
```
    1.与 where检索条件类似，条件不是and 而是 or
```
* 8 . select 查询语句
```
    1.返回结果集，数组如[
        0=>['name'=>1,'pw'=>1]
        1=>['name'=>2,'pw'=>2]
        ....
    ]
    2.错误返回false
```
* 9 . count 计算语句
```
    1.返回统计int型数据，根据条件也会返回int数组，错误返回false
```
* 10 . insert(params) 插入语句
```
    1.params = 要插入的数据，键值类型如['name'=>2,'pw'=>3]
    2.成功返回影响条数，否则返回false
```    
* 11 . update(params) 更新语句
```
    1.params = 要更新的数据，键值类型如['name'=>2,'pw'=>3]
    2.成功返回影响条数，否则返回false
```
* 12 . delete 删除语句
```
    1.成功返回影响条数,否则返回false
```

* 13 . query($sqlString, $type) 直接查询
```
    1.$sqlString 原生sql语句
    2.$type = 值有 select , count , insert , update , delete
    3.返回对应操作信息，否则返回false
```

#### SimpleSQL.php 
查询简化版
###### 接口
* 1 . select($sqlString, $params = [])  //选择数据
```
    1.$sqlString = 原生sql语句
    2.$params = [ 1 , 2 ] 当params不为空时，$sqlString将为预处理语句,预处理请查询PDO预处理api
    3.返回结果集数组或false
```
* 2 . update($sqlString, $params = [])  //更新数据
```
    1.$sqlString = 原生sql语句
    2.$params = [ 1 , 2 ] 当params不为空时，$sqlString将为预处理语句,预处理请查询PDO预处理api
    3.返回影响行数或false
```
* 3 . insert($sqlString, $params = [])  //插入数据
```
    1.$sqlString = 原生sql语句
    2.$params = [ 1 , 2 ] 当params不为空时，$sqlString将为预处理语句,预处理请查询PDO预处理api
    3.返回影响行数或false
```
* 4 . count($sqlString, $params = [])   //数据计数
```
    1.$sqlString = 原生sql语句
    2.$params = [ 1 , 2 ] 当params不为空时，$sqlString将为预处理语句,预处理请查询PDO预处理api
    3.返回int数组或int数值或false
```
* 5 . delete($sqlString, $params = [])   //删除数据
```
    1.$sqlString = 原生sql语句
    2.$params = [ 1 , 2 ] 当params不为空时，$sqlString将为预处理语句,预处理请查询PDO预处理api
    3.返回影响行数或false
```
* 6 . changePdoparams($pdoParams)   //修改pdo连接参数
```
    1.$pdoParams = pdo参数
```
* 7 . transactionStart() //开启事务

* 8 . commit()  //提交

* 9 . rollback() //回滚

    