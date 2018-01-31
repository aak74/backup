<?php

namespace Backup\DbProvider;

// use Backup\FileProvider\FileProviderInterface;

/**
 * Драйвер для backup mysql databases
 */
class Bitrix extends Mysql
{

    protected function getPathToConfig()
    {
        return $this->params['project_path'] . '/bitrix/.settings.php';
    }

    protected function parseConfigFile(String $file)
    {
        // print_r(['parseConfigFile', $file]);
        $config = include("data://," . $file);
        $connections = $config['connections'];
        if (empty($connections)) {
            // в некоторых версиях раздел connections вложен в exception_handling
            $connections = $config['exception_handling']['connections'];
        }
        if (empty($connections)) {
            throw new \Exception('Connection section not found', 1);
        }
        $this->dbCredentials = $connections['value']['default'];
        unset($this->dbCredentials['className']);
        unset($this->dbCredentials['options']);
    }
}
