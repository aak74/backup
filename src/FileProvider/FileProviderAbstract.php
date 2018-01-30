<?php

namespace Backup\FileProvider;

abstract class FileProviderAbstract implements FileProviderInterface
{
    protected $params = null;
    
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    abstract public function getConfigFile(String $path);
    
    public function moveDumpToDestination(String $source, String $destination)
    {
        $this->putDumpToDestination($source, $destination);
        $this->removeDump($source);
    }
    
    // abstract public function getFile(String $path);

    // public function getDump()
    // public function getDump()
    // {
    //     $this->fileProvider = $fileProvider;
    //     $this->backup();
    //     $this->fileProvider->getDump();
    //     $this->fileProvider->removeDump();
    // }
    
}
