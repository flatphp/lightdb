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
$data = $conn->fetchRowTo('MyModel', $sql, 2);
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
use Lightdb\DB;

$conn = DB::conn();

DB::query()->table('users')->select('name, age')->where('age>?', 10)->fetchRow();

// read from master
$query = DB::query($conn, ['master' => true])->table('users as u')->select('u.id, u.name')
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

DB::query('other')->table('users')->whereIn('name', ['peter', 'tom'])->update(array(
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
$query = DB::query('another');
$query->table('users')->whereNotIn('id', [1,2,3])->delete();
print_r($query->getLog());
```

