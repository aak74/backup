<?php

require_once('src/RemoteBackup.php');
$backup = new \App\RemoteBackup($argv[1]);
$backup->backup();
