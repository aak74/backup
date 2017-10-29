<?php

require('src/RemoteBackup.php');
$backup = new \App\RemoteBackup($argv[1]);
$backup->backup();
