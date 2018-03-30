<?php

namespace Backup\DbProvider;

use Backup\FileProvider\FileProviderInterface;

class DbProviderAbstract implements DbProviderInterface
{
    protected $params = null;
    protected $dbCredentials = null;
    
    public function __construct(FileProviderInterface $fileProvider, array $params)
    {
        $this->fileProvider = $fileProvider;
        $this->params = $params;
    }

    public function getDump()
    {
        $this->getDbCredentials();
        // print_r($this->dbCredentials);
        // return;
        $this->backup();
        $this->fileProvider->moveDumpToDestination($this->getDumpName(), $this->getDestinationName());
        // $this->fileProvider->removeDump();
    }
    
    protected function getDbCredentials()
    {
        $path = $this->getPathToConfig();
        $file = $this->fileProvider->getConfigFile($path);
        $this->parseConfigFile($file);
        return $this->dbCredentials;
    }
    
    protected function getPathToConfig()
    {
        return $this->params['path'];
    }
    
    protected function parseConfigFile(String $file)
    {
        throw new \Exception('Function parseConfigFile must be overridden', 501);
    }
    
    protected function backup()
    {
        $dumpCommand = $this->getDumpCommand();
        $this->fileProvider->dumpDB($dumpCommand, $this->getDumpName());
    }
    
    protected function getDumpName()
    {
        return '/tmp/dump-' . $this->params['name'] . '.sql';
        return '"dump-' . $this->params['name'] . '.sql"';
    }
    
    protected function getDestinationName()
    {
        return $this->params['destinationName'];
    }
}
