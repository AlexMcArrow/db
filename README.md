DB - MySQL(i) class
===================
Init
----
```php
$DSN = array (
    'host'=>'localhost',
	'base'=>'dbname',
	'user'=>'user',
	'pass'=>'password',
	'char'=>'utf-8'
);
new \DBI\MySQL ($DSN);
```

Use
---
```php
\DBI\MySQL::query ("SELECT 1;");
```
