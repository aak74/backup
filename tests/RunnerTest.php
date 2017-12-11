<?php

namespace AppTests;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class RunnerTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $this->defaultParams = [
            'backup_path' => '/mnt/b/backup',
            'host' => 'example.com',
            'user' => 'user',
            'port' => '22',
            'project_path' => '/var/www/html',
            'project_name' => 'some-project',
            'dump_name' => 'db.sql',
            'php' => 'php',
            'public_key' => '~/.ssh/id_rsa.pub',
            'private_key' => '~/.ssh/id_rsa'
        ];
        $this->testingClass = new \Backup\Runner($this->defaultParams);
    }

    /**
     * Должен возвращаться правильный путь источник
     */
    public function testCalcSourcePath()
    {
        $method = new \ReflectionMethod('\Backup\Runner', 'calcSourcePath');
        $method->setAccessible(true);
        $this->assertEquals(
            '/var/www/html',
            $method->invoke($this->testingClass)
        );
    }

    /**
     * Должен возвращаться правильный путь последней копии
     */
    public function testCalcLastPath()
    {
        // $stub = $this->createMock(\Backup\Runner::class, $this->defaultParams);
        // Configure the stub.
        $root = vfsStream::setup('exampleDir');
        $structure = [
                'Core' => [
                    'AbstractFactory' => [
                        'test.php' => 'some text content',
                        'other.php' => 'Some more text content',
                        'Invalid.csv' => 'Something else',
                    ],
                'AnEmptyFolder' => [],
                'badlocation.php' => 'some bad content',
            ]
        ];
        vfsStream::create($structure, $root);

        $reflection = new \ReflectionObject(\Backup\Runner::class);
        $method = $reflection->getMethod('getAllFolders');
        $method->setAccessible(true);
        // $method = $reflection->getMethod('calcLastPath');
        // $method->setAccessible(true);

        // $stub->method('getAllFolders')
        //     ->will(['2017-12-01', '2017-12-02','2017-12-07']);

        // $class = $this->getMockBuilder(\Backup\Runner::class, $this->defaultParams)
        //     ->setMethods(['mockMethod'])
        //     ->getMock();
        //
        // $class->expects($this->any())
        //     ->method('mockMethod')
        //     ->will($this->returnValue('Hey!'));

        $this->assertEquals(
            '/mnt/b/backup/some-project/2017-12-07',
            $method->invoke($this->testingClass)
        );
    }

    /**
     * Должен возвращаться правильная команда для rsync
     */
    public function _testGetRsyncCommand()
    {
        $method = new \ReflectionMethod('\Backup\Runner', 'getRsyncCommand');
        $method->setAccessible(true);
        $this->assertEquals(
            'rsync -aLz --delete --exclude-from exclude.txt -e "ssh -p 22" user@example.com:/var/www/html/ /mnt/b/backup/some-project',
            $method->invoke($this->testingClass)
        );
    }
    /**
     * Должен возвращаться правильная команда для backup DB
     */
    public function _testGetDumpCommand()
    {
        $method = new \ReflectionMethod('\Backup\Runner', 'getDumpCommand');
        $method->setAccessible(true);
        $params = ['database' => 'dbname', 'login' => 'login', 'password' => 'password'];
        $this->assertEquals(
            'mysqldump -u login -p"password" dbname > /var/www/html/db.sql',
            $method->invoke($this->testingClass, $params)
        );
    }
}
