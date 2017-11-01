<?php
$params = array();
if ($argv) {
    unset($argv[0]);
    parse_str(join('&', $argv), $params);
}
$filename = __DIR__ . '/bitrix/.settings.php';
// $filename = $params['project_path'] . '/bitrix/.settings.php';
if (!is_file($filename)) {
    exit('File ' . $filename . ' is not found ');
}
$config = include($filename);
// print_r($config);
if (empty($config)) {
    return 400;
}
$connections = $config['connections'];
if (empty($connections)) {
    // в некоторых версиях раздел connections вложен в exception_handling
    $connections = $config['exception_handling']['connections'];
}
if (empty($connections)) {
    echo 'connection section not found';
    exit(1);
}
$connection = $connections['value']['default'];
$login = $connection['login'];
$password = $connection['password'];
$db = $connection['database'];
shell_exec("mysqldump -u $login -p$password $db > " . __DIR__ . "/" . $params['dump_name']);
exit(0);
