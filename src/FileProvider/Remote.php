<?php

namespace Backup\FileProvider;

use phpseclib\Net\SSH2;
use phpseclib\Net\SFTP;
use phpseclib\Crypt\RSA;

class Remote extends FileProviderAbstract
{
    private $connection = null;
    private $sftp = null;
    
    public function dumpDB(String $dumpCommand, String $dumpName)
    {
        $ssh = $this->getSSH();
        $result = !$ssh->exec($dumpCommand);

        if ($result && ($filesize = $this->getFileSize($dumpName))) {
            echo "Size of MySQL dump = " . $filesize, PHP_EOL;
        }
        return $result && $filesize;
    }    

    private function getFileSize(String $path)
    {
        $this->connectSFTP();
        return $this->sftp->size($path);
    }
    
    /**
     * Возвращает конфиг с данными о подключении к БД
     */
    public function getConfigFile(String $path)
    {
        $this->connectSFTP();
        return $this->sftp->get($path);
    }

    private function getSSH()
    {
        $connection = new SSH2($this->params['host'], $this->params['port']);
        $this->login($connection);
        return $connection;
    }

    private function connectSFTP()
    {
        if ($this->sftp) {
            return;
        }
        $this->sftp = new SFTP($this->params['host'], $this->params['port']);
        $this->login($this->sftp);
    }
    
    private function login($connection)
    {
        if (empty($this->params['password'])) {
            $key = new RSA();
            $key->loadKey(file_get_contents($this->params['private_key']));
            return $connection->login($this->params['user'], $key);
        }
        return $connection->login($this->params['user'], $this->params['password']);
    }
    
    protected function putDumpToDestination(String $source, String $destination)
    {
        $this->connectSFTP();
        return $this->sftp->get($source, $destination);
    }
    
    protected function removeDump(String $filepath)
    {
        $this->connectSFTP();
        return $this->sftp->delete($filepath);
    }
}
