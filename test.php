#!/usr/bin/php
<?php
$config = include('.settings.php');
$connection = $config['connections']['value']['default'];
var_dump($connection);
$login = $connection['login'];
$password = $connection['password'];
$db = $connection['database'];
// echo "mysqldump -u $login -p$password $db";
shell_exec("mysqldump -u $login -p$password $db");
// echo shell_exec("mysqldump -u $login -p$password $db");
