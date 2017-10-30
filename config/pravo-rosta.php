<?php
return array_merge(
    require_once('default.php'),
    [
        'host' => 'pravo-rosta.ru',
        'user' => 'bitrix',
        'port' => '22',
        'project_path' => '~/www',
        'project_name' => 'pravo-rosta',
        'dump_name' => 'db.sql',
    ]
);
