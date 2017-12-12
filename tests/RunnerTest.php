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
     * @test
     * Должен возвращаться правильный путь последней копии
     */
    public function calcLastPath()
    {
        // $list = \glob($dir . '*.*');
        // $dir = vfsStream::url('exampleDir');
        // print_r($dir);
        // if (is_dir($dir)) {
        //     if ($dh = opendir($dir)) {
        //         while (($file = readdir($dh)) !== false) {
        //             echo "файл: $file : тип: " . filetype($dir . $file) . "\n";
        //         }
        //         closedir($dh);
        //     }
        // }

        $reflection = new \ReflectionObject($this->testingClass);
        $method = $reflection->getMethod('getLastPath');
        $method->setAccessible(true);

        $structure = [
            '2017-12-04' => [
                'ok.txt' => 'fine'
            ],
            '2017-12-05' => [],
            '2017-12-06' => [],
            '2099-12-31' => [],
            '2100-12-31' => 'Something else',
        ];

        $root = vfsStream::setup('/', null, $structure);
        $folder = $root->url();

        $this->assertEquals(
            '2017-12-06',
            $method->invoke($this->testingClass, $folder)
        );

        $root = vfsStream::setup('/', null, $structure);
        $folder = $root->url();
        $structure['2017-1211'] = [];

        $this->assertEquals(
            '2017-12-06',
            $method->invoke($this->testingClass, $folder)
        );

        $structure['2017-12-11 00:00:00'] = [];
        $root = vfsStream::setup('/', null, $structure);
        $folder = $root->url();

        $this->assertEquals(
            '2017-12-11 00:00:00',
            $method->invoke($this->testingClass, $folder)
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
