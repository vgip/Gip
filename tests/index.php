<?php

declare(strict_types = 1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

ini_set('default_charset', 'UTF-8');

mb_internal_encoding('UTF-8');
setlocale (LC_ALL, 'ru_RU.UTF-8');
header('Content-type: text/html; charset=utf-8');


$pathStoragePublic     = __DIR__;
define('GIP_STORAGE_PUBLIC', $pathStoragePublic);

$pathMain       = dirname(__DIR__);
$pathStoragePrivate    = $pathMain;
define('GIP_STORAGE_PRIVATE', $pathStoragePrivate);

$pathComposer = join(DIRECTORY_SEPARATOR, [$pathStoragePrivate, 'vendor', 'autoload.php']);
require $pathComposer;

$pathTest = join(DIRECTORY_SEPARATOR, [$pathStoragePublic, 'Db', 'Mysql', 'placeholder.php']);
require $pathTest;

require 'template.php';
