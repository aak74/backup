<?php

namespace App;

/**
 *
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
            echo 'DB backup started', PHP_EOL;
            $this->backupDB();
            echo 'DB backup finished', PHP_EOL;
            $this->removeScript();
            echo 'DB backup script removed', PHP_EOL;
            $this->rsync();
            echo 'Files synced', PHP_EOL;
            $this->removeDump();
            echo 'DB dump removed', PHP_EOL;
        }
    }

    private function backupDB()
    {
        ssh2_scp_send($this->connection, './src/mysqldump.php', './mysqldump.php', 0644);
        $stream = ssh2_exec(
            $this->connection,
            'php mysqldump.php project_path='
            . $this->params['project_path']
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
        ssh2_sftp_unlink($this->getSFTP(), './mysqldump.php');
    }

    private function removeDump()
    {
        return ssh2_sftp_unlink($this->getSFTP(), $this->getPathToDump());
    }
    
    private function getSFTP()
    {
        return ($this->sftp)
            ? $this->sftp
            : $this->sftp = ssh2_sftp($this->connection);
    }

    private function getPathToDump()
    {
        if ($this->params['project_path'][0] == '~') {
            return '.' . substr($this->params['project_path'], 1) . '/' . $this->params['dump_name'];
        }
        return $this->params['project_path'] . '/' . $this->params['dump_name'];
    }

    private function connect()
    {
        $this->connection = ssh2_connect($this->params['host'], $this->params['port']);
        return ssh2_auth_pubkey_file(
            $this->connection,
            $this->params['user'],
            '~/.ssh/id_rsa.pub',
            '~/.ssh/id_rsa'
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
        return 'rsync -aLz --delete --exclude-from exclude.txt -e "ssh -p ' . $this->params['port'] . '" '
            . $this->params['user'] . '@' 
            . $this->params['host'] . ':' 
            . $this->params['project_path'] . '/ '
            . $this->params['backup_path'] . '/' 
            . $this->params['project_name'];
    }

    private function readConfig($configName)
    {
        return require('./config/' . $configName . '.php');
    }
}
