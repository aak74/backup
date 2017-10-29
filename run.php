<?php

require('RemoteBackup.php');
$backup = new \App\RemoteBackup($argv[1]);
$backup->backup();
