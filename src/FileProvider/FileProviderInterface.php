<?php

namespace Backup\FileProvider;

interface FileProviderInterface
{
    public function __construct(array $params);
    public function getConfigFile(String $path);
    // public function getFile(String $path);
}
