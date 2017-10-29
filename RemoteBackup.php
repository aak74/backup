<?php

namespace App;

/**
 *
 */
class RemoteBackup
{
    private $connection = null;

    public function __construct($configName)
    {
        $this->params = $this->readConfig($configName);
    }

    public function backup()
    {
        if ($this->connect()) {
            $this->backupDB();
            $this->removeScript();
            $this->rsync();
            $this->removeDump();
        }
    }

    private function backupDB()
    {
        ssh2_scp_send($this->connection, './mysqldump.php', './mysqldump.php', 0644);
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
        $dumpName = './' . $this->params['project_path'] . '/' . $this->params['dump_name'];
        $stat = ssh2_sftp_stat($sftp, $dumpName);
        return ($stat && $stat['size'] > 0)
            ? $stat['size']
            : false;
    }

    private function removeScript()
    {
        $sftp = ssh2_sftp($this->connection);
        ssh2_sftp_unlink($sftp, './mysqldump.php');
    }

    private function removeDump()
    {
        $sftp = ssh2_sftp($this->connection);
        $dumpName = './' . $this->params['project_path'] . '/' . $this->params['dump_name'];
        // echo $dumpName, PHP_EOL;
        return ssh2_sftp_unlink($sftp, $dumpName);
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
    }

    private function readConfig($configName)
    {
        return require('./config/' . $configName . '.php');
    }
}
