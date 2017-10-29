<?php
$params = array();
if ($argv) {
    unset($argv[0]);
    parse_str(join('&', $argv), $params);
}
$filename = $params['project_path'] . '/bitrix/.settings.php';
if (!is_file($filename)) {
    exit('File ' . $filename . ' is not found ');
}
$config = include($params['project_path'] . '/bitrix/.settings.php');
if (empty($config)) {
    return 400;
}
$connection = $config['connections']['value']['default'];
$login = $connection['login'];
$password = $connection['password'];
$db = $connection['database'];
shell_exec("mysqldump -u $login -p$password $db > " . $params['project_path'] . "/" . $params['dump_name']);
exit(0);
