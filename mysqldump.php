<?php
$params = [];
if ($argv) {
    unset($argv[0]);
    parse_str(join('&', $argv), $params);
}
$filename = $params['pp'] . '/bitrix/.settings.php';
if (!is_file($filename)) {
    exit('File ' . $filename . ' is not found ');
}
$config = include($params['pp'] . '/bitrix/.settings.php');
if (empty($config)) {
    return 400;
}
$connection = $config['connections']['value']['default'];
$login = $connection['login'];
$password = $connection['password'];
$db = $connection['database'];
shell_exec("mysqldump -u $login -p$password $db > /tmp/mysqldump.sql");
exit(0);
