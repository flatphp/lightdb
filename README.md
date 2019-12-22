# Lightdb
Lightemp is a simple db component.

# Install
```
composer require flatphp/lightdb
```

# Usage
```php
use \Lightdb\DB;

$conf = array(
    'dsn' => 'mysql:host=localhost;dbname=testdb',
    'username' => 'root',
    'password' => '123456',
    'options' => [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']
);

DB::init($conf);

$sql = 'select * from test where id>?';

// static use
$data = DB::fetchAllIndexed($sql, 2);

$sql = 'insert into test(aa, bb) values(?, ?)';
$res = DB::execute($sql, [1, 2]);

// connection instance use
$conn = DB::getConn();
$sql = 'select * from channel where id>?';
$data = $conn->fetchAll($sql, 2);
```


# Master-Slaves
```php
use \Lightdb\DB;

$conf = array(
    'dsn' => 'mysql:host=localhost;dbname=testdb1',
    'username' => 'root',
    'password' => '123456',
    'options' => [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'],
    'slaves' => array(
        [
            'dsn' => 'mysql:host=localhost;dbname=testdb2',
            'username' => 'root',
            'password' => '123456',
        ],
        [
            'dsn' => 'mysql:host=localhost;dbname=testdb3',
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
```

# Multiple DB Instance
```php
use \Lightdb\DB;

$conf = array(
    'db1' => array(...),
    'db2' => array(...),
);

$sql = 'select * from test';
DB::getConn('db1')->fetchRow($sql);
DB::getConn('db2')->fetchPairs($sql);
```


# Query Builder

## Select
```php
DB::query()->select('users', 'name, age')->where('age>?', 10)->fetchAll();

DB::query('db2')->select('users')->where(function($w){
    return $w->where('sex=?', 1)->orWhere('class in ??', [1,2]);
})->where('age>?', 10)->fetchRow();

DB::query()->select('users as u', 'u.id, u.name')
    ->leftJoin('score as s', 'u.user_id=s.user_id')
    ->where('class=?', 1)
    ->orderBy('u.id DESC')
    ->page(1, 10)
    ->log();
```

## Insert
```php
DB::query()->insert('users', array(
    'name' => 'peter',
    'age' => 12,
    'sex' => 1
))->execute();
```

## Update
```php
use Lightdb\Builder\Raw;

DB::query('other')->update('users', array(
    'class' => 1,
    'point' => Raw('point+1')
))->where('name in ??', ['peter', 'tom'])->execute();
```

## Delete
```php
// get delete sql
$query = DB::query()->delete('users')->where('id=?', 10);
$sql = $query->getSql();
$bind = $query->getBind();

// print sql and bind
DB::query()->delete('users')->where('id not in ?', [1,2,3])->log();
```

