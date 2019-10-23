<?php

declare(strict_types = 1);

use Vgip\Gip\Html\PageStorage;
use Vgip\Gip\Db\Mysql\Config AS MysqlConfig;
use Vgip\Gip\Db\Mysql\MysqlPlaceholder;

$pageStorage = PageStorage::getInstance();


$pathConfig = join(DIRECTORY_SEPARATOR, [dirname(dirname(dirname(__DIR__))), 'config', 'db_mysql.php']);
$config = require $pathConfig;

$mysqlConfig = new MysqlConfig();
$mysqlConfig->setAll($config, 'LowerCamelCasef');

print_r($config);

$pageStorage->setHeadTitle('Mysql Placeholder test');
$pageStorage->setBodyContent('Mysql Placeholder test!');

