<?php

namespace Backup;

use Carbon\Carbon;

/**
 * Запускатель backup
 */
class Runner
{
    const STATUS_START = 0;
    const STATUS_FINISH = 1;
    const DIR_PERMISSION = 0755;
    private $lastPath = null;
    private $sourcePath = null;

    public function __construct(array $params)
    {
        $this->params = $params;
        $this->params['backup_folder'] = $this->params['backup_path']
            . DIRECTORY_SEPARATOR . $this->params['project_name'] . DIRECTORY_SEPARATOR;
    }

    public function backup()
    {
        // print_r($this->params);
        $this->createFolders($this->params['backup_folder']);
        $this->calcLastPath($this->params['backup_folder']);
        var_dump($this->lastPath);
        $this->calcDestinationPath();
        if ($this->lastPath) {
            $this->copyWithHardLinks();
        }
        // return;
        $this->getSourcePath();
        $this->rsync();
        // $this->removeWastedCopy();
    }

    private function createFolders($folder)
    {
        if (!is_dir($folder)) {
            mkdir($folder, self::DIR_PERMISSION, true);
        }
    }

    /**
     * Вычисляется путь к папке, в которую будет копироваться backup
     */
    private function calcDestinationPath()
    {
        $this->destinationPath = Carbon::now();
    }

    /**
     * Возвращается путь внешнего источника,
     * из которого будут синхронизироваться файлы
     */
    private function getSourcePath()
    {
        // $this->sourcePath = $this->calcLastPath($this->params['backup_folder']);
        $this->sourcePath = $this->params['project_path'];
        return $this->sourcePath;
    }

    /**
     * Запускается внешний скрипт копирования с хардлинками
     */
    private function copyWithHardLinks()
    {
        $output = shell_exec(__DIR__ . '/sh/copyWithHardLinks.sh '
            . '"' . $this->params['backup_folder'] . $this->lastPath . '"'
            // . $this->params['backup_folder'] . $this->lastPath . '"'
            . ' "' . $this->params['backup_folder'] . $this->destinationPath . '"');
            // . ' "' . $this->params['backup_folder'] . $this->destinationPath) . '"';
        echo $output, PHP_EOL;
    }

    /**
     * Возвращает последний путь с последней копией6
     * из которого будет скопирована предыдущая копия с хардлинками
     */
    private function calcLastPath($folder)
    {
        // echo Carbon::now();

        $folders = $this->getAllFoldersLtTommorow($folder);
        // print_r($folders);
        if (count($folders)) {
            $this->lastPath = current($folders);
        }
        return $this->lastPath;
    }

    /**
     * Возвращает все папки в каталоге назначения,
     * имя которых меньше завтрашнего дня
     */
    private function getAllFoldersLtTommorow($folder)
    {
        // print_r($folder);
        $files = array_diff(scandir($folder, 1), ['..', '.']);
        // print_r($files);
        $tomorrow = Carbon::tomorrow();
        return array_filter($files, function ($file) use ($folder, $tomorrow) {
            if (strlen($file) < 8) {
                return false;
            }
            if (!is_dir($folder . $file)) {
                return false;
            }
            try {
                $dt = Carbon::parse($file);
            } catch (\Exception $e) {
                return false;
            }
            if ($dt->gte($tomorrow)) {
                return false;
            }
            return true;
        });
    }

    private function getPath($filename)
    {
        if ($this->params['project_path'][0] == '~') {
            return '.' . substr($this->params['project_path'], 1) . DIRECTORY_SEPARATOR . $filename;
        }
        return $this->params['project_path'] . DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * Запускает синхронизацию в нужную папку из внешнего источника
     */
    private function rsync()
    {
        $rsyncCommand = $this->getRsyncCommand();
        $this->addAction('rsync', self::STATUS_START);
        shell_exec($rsyncCommand);
        $this->addAction('rsync', self::STATUS_FINISH);
    }

    private function getRsyncCommand()
    {
        $prefix = (empty($this->params['password']))
            ? ''
            : 'sshpass -p ' . $this->params['password'] . ' ';

        return $prefix . 'rsync -aLz --delete-after --exclude-from exclude.txt -e "ssh -p '
            . $this->params['port'] . '" '
            . $this->params['user'] . '@'
            . $this->params['host'] . ':'
            . $this->params['project_path'] . DIRECTORY_SEPARATOR . ' '
            . '"' . $this->params['backup_folder'] . $this->destinationPath . DIRECTORY_SEPARATOR . '"'
            . $this->params['project_name'];
    }

    private function addAction($action, $status)
    {
        $this->actions = [
            'action' => $action,
            'status' => $status
        ];
    }
}
