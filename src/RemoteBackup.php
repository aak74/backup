<?php

namespace App;

/**
 * Удаленный backuper
 */
class RemoteBackup
{
    private $connection = null;
    private $sftp = null;

    public function __construct($configName = 'default')
    {
        $this->params = $this->readConfig($configName);
    }

    public function backup()
    {
        if ($this->connect()) {
            echo 'connected', PHP_EOL;
            // echo memory_get_usage();
            // memory_get_usage();
            echo 'DB backup started' . PHP_EOL;
            $this->backupDB();
            echo 'DB backup finished', PHP_EOL;
            $this->removeScript();
            echo 'DB backup script removed', PHP_EOL;
            $this->rsync();
            echo 'Files synced', PHP_EOL;
            $this->removeDump();
            echo 'DB dump removed', PHP_EOL;
            return;
        }
        // echo 'Can`t connect to host', PHP_EOL;
    }

    private function backupDB()
    {
        $scriptPath = $this->params['project_path'] . '/mysqldump.php';
        ssh2_scp_send($this->connection, './src/mysqldump.php', $scriptPath, 0644);
        $stream = ssh2_exec(
            $this->connection,
            $this->params['php'] . ' ' . $scriptPath 
            // . ' project_path=' . $this->params['project_path']
            . ' dump_name=' . $this->params['dump_name']
        );
        $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
        stream_set_blocking($errorStream, true);
        stream_set_blocking($stream, true);

        if ($output = stream_get_contents($stream)) {
            echo "Output: " . $output, PHP_EOL;
        }
        if ($error = stream_get_contents($errorStream)) {
            echo "Error: " . $error, PHP_EOL;
        }
        fclose($errorStream);
        fclose($stream);
        $result = (empty($output) && empty($output));

        if ($result && ($filesize = $this->getFileSize())) {
            echo "Size of msysql dump = " . $filesize, PHP_EOL;
        }
        return $result && $filesize;
    }

    private function getFileSize()
    {
        $sftp = ssh2_sftp($this->connection);
        $dumpName = $this->params['project_path'] . '/' . $this->params['dump_name'];
        $stat = ssh2_sftp_stat($sftp, $dumpName);
        return ($stat && $stat['size'] > 0)
            ? $stat['size']
            : false;
    }

    private function removeScript()
    {
        return ssh2_sftp_unlink($this->getSFTP(), $this->getPath('mysqldump.php'));
    }

    private function removeDump()
    {
        return ssh2_sftp_unlink($this->getSFTP(), $this->getPath($this->params['dump_name']));
    }
    
    private function getSFTP()
    {
        return ($this->sftp)
            ? $this->sftp
            : $this->sftp = ssh2_sftp($this->connection);
    }

    private function getPath($filename)
    {
        if ($this->params['project_path'][0] == '~') {
            return '.' . substr($this->params['project_path'], 1) . '/' . $filename;
        }
        return $this->params['project_path'] . '/' . $filename;
    }

    private function connect()
    {
        $this->connection = ssh2_connect($this->params['host'], $this->params['port']);
        if (empty($this->params['password'])) {
            return ssh2_auth_pubkey_file(
                $this->connection,
                $this->params['user'],
                $this->params['public_key'],
                $this->params['private_key']
            );
        }
        return ssh2_auth_password(
            $this->connection,
            $this->params['user'],
            $this->params['password']
        );
    }

    private function rsync()
    {
        $rsyncCommand = $this->getRsyncCommand();
        echo 'Rsync started', PHP_EOL;
        echo $rsyncCommand, PHP_EOL;
        shell_exec($rsyncCommand);
    }

    private function getRsyncCommand()
    {
        $prefix = (empty($this->params['password']))
            ? ''
            : 'sshpass -p ' . $this->params['password'] . ' ';
            
        return $prefix . 'rsync -aLz --delete --exclude-from exclude.txt -e "ssh -p '
            . $this->params['port'] . '" '
            . $this->params['user'] . '@' 
            . $this->params['host'] . ':' 
            . $this->params['project_path'] . '/ '
            . $this->params['backup_path'] . '/' 
            . $this->params['project_name'];
    }

    private function readConfig($configName)
    {
        $filename = './config/' . $configName . '.php';
        if (\is_file($filename)) {
            return require($filename);
        }
        throw new \Exception("Config file $filename doesn`t exists", 1);
    }
}
