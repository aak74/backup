<?php

namespace Backup;

class ConfigReader
{
    public function __construct($configName = 'default')
    {
        $filename = './config/' . $configName . '.php';
        if (\is_file($filename)) {
            return require($filename);
        }
        throw new \Exception("Config file $filename doesn`t exists", 1);
    }
}
