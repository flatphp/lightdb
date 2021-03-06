# Lightdb
Lightdb is a simple db component.
轻量DB库

# Install 安装
```
composer require flatphp/lightdb
```

# Usage 使用
```php
use \Lightdb\DB;

// config 配置
$conf = array(
    'dsn' => 'mysql:host=localhost;dbname=testdb',
    'username' => 'root',
    'password' => '123456',
    'options' => [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']
);

// init 初始化
DB::init($conf);

// raw sql 原生sql使用
$sql = 'select * from test where id>?';

// static use 静态门面模式使用
$data = DB::fetchAllIndexed($sql, 2);

$sql = 'insert into test(aa, bb) values(?, ?)';
$res = DB::execute($sql, [1, 2]);

// connection instance use
$conn = DB::conn();
$sql = 'select * from channel where id>?';
$data = $conn->fetchAll($sql, 2);

// fetch all to classes array
// 数据实例化对象
$data = $conn->fetchAllTo('MyModel', $sql, 2);

// fetch row to class
$data = $conn->fetchRowTo('MyModel', $sql, 2);
```

# Support Fetch Method
| method | intro |
| --- | --- |
| fetchAll | fetch all array with assoc, empty array returned if nothing or false |
| fetchAllIndexed | fetch all with first field as indexed key, empty array returned if nothing or false |
| fetchAllGrouped | fetch all grouped array with first field as keys, empty array returned if nothing or false |
| fetchRow | fetch one row array with assoc, false is returned if failure |
| fetchColumn | fetch first column array, empty array returned if nothing or false |
| fetchColumnGrouped | fetch grouped column array with first field as keys of grouped array, empty array returned if nothing or false |
| fetchPairs | fetch pairs of first column as Key and second column as Value, empty array returned if nothing or false |
| fetchPairsGrouped | fetch grouped pairs of K/V with first field as keys of grouped array, empty array returned if nothing of false |
| fetchOne | fetch one field value, false returned if nothing or false |
| fetchAllTo | fetch all to classes array, empty array returned if nothing or false |
| fetchAllIndexedTo | fetch all to classes array with firest field as indexed key, empty array returned if nothing or false |
| fetchAllGroupedTo | fetch all grouped array with first field as keys, empty array returned if nothing or false |
| fetchRowTo | fetch one row to class, false is returned if failure |


# Master-Slaves 主从使用
```php
use \Lightdb\DB;

$conf = array(
    'dsn' => 'mysql:host=master;dbname=testdb',
    'username' => 'root',
    'password' => '123456',
    'options' => [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'],
    'slaves' => array(
        [
            'dsn' => 'mysql:host=slave1;dbname=testdb',
            'username' => 'root',
            'password' => '123456',
        ],
        [
            'dsn' => 'mysql:host=slave2;dbname=testdb',
            'username' => 'root',
            'password' => '123456',
        ]
    )
);

DB::init($conf);

// write to master db
$sql = 'insert into test(aa) values(1)';
$res = DB::execute($sql);

// select from slave db
$sql = 'select * from test';
$data = DB::fetchAll($sql);

// force read from master
// 强制从主库读取
$sql = 'select * from test';
$data = DB::master()->fetchAll($sql);
```

# Multiple DB Instance 多DB实例
```php
use \Lightdb\DB;

$conf = array(
    'db1' => array(...),
    'db2' => array(...),
);

$sql = 'select * from test';
DB::conn('db1')->fetchRow($sql);
DB::conn('db2')->fetchPairs($sql);
```

# Transaction 事务
```php
use \Lightdb\DB;

$transaction = DB::transaction();
$transaction->run(function(){
    // do something
}, function(){
    // do something after commit
}, function(){
    // do something after rollback
});
```

another way:
```php
use \Lightdb\DB;
$transaction = DB::transaction();
$transaction->beginTransaction();
try {
    // do something
    $transaction->commit();
} catch (\Exception $e) {
    $transaction->rollback();
    throw $e;
}
```

# Query Builder sql构建查询

## Select
```php
use Lightdb\DB;

$conn = DB::conn();

$conn->query()->table('users')->select('name, age')->where('age>?', 10)->fetchRow();

// read from master
$query = DB::query(['master' => true])->table('users as u')->select('u.id, u.name')
    ->leftJoin('score as s', 'u.user_id=s.user_id')
    ->where('class=?', 1)->orWhere('(age>12 AND sex=1)')
    ->orderBy('u.id DESC');
$result = $query->page(1, 10)->fetchAll();
$count = $query->count();
```

## Insert
```php
use Lightdb\DB;

DB::query()->table('users')->insert(array(
    'name' => 'peter',
    'age' => 12,
    'sex' => 1
));
```

## Update
```php
use Lightdb\DB;
use Lightdb\Raw;

DB::conn('other')->query()->table('users')->whereIn('name', ['peter', 'tom'])->update(array(
    'class' => 1,
    'point' => new Raw('point+1')
));
```

## Delete
```php
use Lightdb\DB;

// get delete sql
DB::query()->where('id=?', 10)->delete();

// execute
$query = DB::conn('another')->query();
$query->table('users')->whereNotIn('id', [1,2,3])->delete();
print_r($query->getLog());
```

#### Query Join Method
* leftJoin($table, $on, $bind = null)
* rightJoin($table, $on, $bind = null)
* innerJoin($table, $on, $bind = null)
* fullJoin($table, $on, $bind = null)

#### Query Where Method
* where($where, $bind = null)
* whereIn($field, array $values)
* whereNotIn($field, array $values)
* orWhere($where, $bind = null)
* orWhereIn($field, array $values)
* orWhereNotIn($field, array $values)

#### Query Limit and Offset
* limit($limit, $offset = 0)
* offset($offset)
* page($page, $page_size = 10)

#### Query Count
* count() (ignore limit and offset)

#### Execute
* insert(array $data)
* update(array $data)
* delete()