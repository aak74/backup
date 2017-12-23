<?php

require_once('./src/bootstrap.php');
$reader = new \Backup\ConfigReader('dvagruza');
$backup = new \Backup\Runner($reader->config);
$backup->backup();
