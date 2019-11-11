# Lightdb
Lightemp is a simple db component.

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
