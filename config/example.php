<?php
return array_merge(
    require_once('default.php'),
    [
        'host' => 'example.com',
        'user' => 'bitrix',
        'port' => '2222',
        'project_path' => '/var/www/html',
        'project_name' => 'example',
        'dump_name' => 'db.sql',
    ]
);
