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
$res = DB::exec($sql);

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

DB::getConn('db1')->fetchRow($sql);
DB::getConn('db2')->fetchPairs($sql);
```
