# Lightdb
Lightdb is a simple db component.

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
$conn = DB::conn();
$sql = 'select * from channel where id>?';
$data = $conn->fetchAll($sql, 2);

// fetch all to classes array
$data = $conn->fetchAllTo('MyModel', $sql, 2);

// fetch row to class
$data = $conn->fetchTo('MyModel', $sql, 2);
```


# Master-Slaves
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
$sql = 'select * from test';
$data = DB::master()->fetchAll($sql);
```

# Multiple DB Instance
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


# Query Builder

## Select
```php
DB::query()->select('users', 'name, age')->where('age>?', 10)->fetchAll();

// to class
DB::query()->select('users', 'name, age')->where('age>?', 10)->fetchAll('MyModel');

// nested
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

// only print sql and bind
DB::query()->delete('users')->where('id not in ??', [1,2,3])->log();

// execute
DB::query()->delete('users')->where('id not in ??', [1,2,3])->execute();
```

