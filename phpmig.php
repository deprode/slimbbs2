<?php

use \Phpmig\Adapter;
use Pimple\Container;

$env_file = __DIR__. '/.env';
if (is_readable($env_file)) {
    $dot_env = new Dotenv\Dotenv(__DIR__ . '/');
    $dot_env->load();
}

$container = new Container();

$container['db'] = function () {
    $dbh = new PDO('mysql:dbname='.getenv('MYSQL_DATABASE').';host='.getenv('MYSQL_LOCAL_HOST'),getenv('MYSQL_USER'),getenv('MYSQL_PASSWORD'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
};

$container['phpmig.adapter'] = new Adapter\File\Flat(__DIR__ . DIRECTORY_SEPARATOR . 'migrations/.migrations.log');

$container['phpmig.migrations_path'] = __DIR__ . DIRECTORY_SEPARATOR . 'migrations';

return $container;