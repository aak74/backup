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
    public function testGetPathToDump()
    {
        $method = new \ReflectionMethod('\App\RemoteBackup', 'getPathToDump');
        $method->setAccessible(true);
        $this->assertEquals(
            '/var/www/html/db.sql',
            $method->invoke($this->testingClass)
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
}
