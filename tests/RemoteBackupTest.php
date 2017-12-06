<?php

namespace AppTests;

class RemoteBackupTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $this->testingClass = new \App\RemoteBackup;
    }

    /**
     * Должен возвращаться правильный путь для удаления
     */
    public function testGetPath()
    {
        $method = new \ReflectionMethod('\App\RemoteBackup', 'getPath');
        $method->setAccessible(true);
        $this->assertEquals(
            '/var/www/html/db.sql',
            $method->invoke($this->testingClass, 'db.sql')
        );
    }

    /**
     * Должен возвращаться правильная команда для rsync
     */
    public function testGetRsyncCommand()
    {
        $method = new \ReflectionMethod('\App\RemoteBackup', 'getRsyncCommand');
        $method->setAccessible(true);
        $this->assertEquals(
            'rsync -aLz --delete --exclude-from exclude.txt -e "ssh -p 22" user@example.com:/var/www/html/ /mnt/b/backup/some-project',
            $method->invoke($this->testingClass)
        );
    }
    /**
     * Должен возвращаться правильная команда для backup DB
     */
    public function testGetDumpCommand()
    {
        $method = new \ReflectionMethod('\App\RemoteBackup', 'getDumpCommand');
        $method->setAccessible(true);
        $params = ['database' => 'dbname', 'login' => 'login', 'password' => 'password'];
        $this->assertEquals(
            'mysqldump -u login -p"password" dbname > /var/www/html/db.sql',
            $method->invoke($this->testingClass, $params)
        );
    }
}
