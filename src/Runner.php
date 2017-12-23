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

    public function __construct(array $params)
    {
        $this->params = $params;
        $this->params['backup_folder'] = $this->params['backup_path'] . DIRECTORY_SEPARATOR . $this->params['project_name'];
    }

    public function backup()
    {
        print_r($this->params);
        $this->getSourcePath();
        $this->calcDestinationPath();
        if ($this->sourcePath) {
            $this->copyWithHardLinks();
        }
        return;
        $this->rsync();
        $this->removeWastedCopy();
    }

    /**
     * Вычисляется путь к папке, в которую будет копироваться backup
     */
    private function calcDestinationPath()
    {
        return Carbon::now();
    }

    /**
     * Возвращается путь внешнего источника,
     * из которого будут синхронизироваться файлы
     */
    private function getSourcePath()
    {
        $this->sourcePath = $this->params['project_path'];
        return $this->sourcePath;
    }

    /**
     * Запускается внешний скрипт копирования с хардлинками
     */
    private function copyWithHardLinks()
    {
        shell_exec('copyWithHardLinks.sh '
            . $this->getLastPath($this->params['backup_folder'])
            . ' ' . $this->destinationPath);
    }

    /**
     * Возвращает последний путь с последней копией6
     * из которого будет скопирована предыдущая копия с хардлинками
     */
    private function getLastPath($folder)
    {
        // echo Carbon::now();

        $folders = $this->getAllFoldersLtTommorow($folder);
        // print_r($folders);
        if (count($folders)) {
            return current($folders);
        }
        return false;
    }

    /**
     * Возвращает все папки в каталоге назначения,
     * имя которых меньше завтрашнего дня
     */
    private function getAllFoldersLtTommorow($folder)
    {
        $files = array_diff(scandir($folder, 1), ['..', '.']);
        $tomorrow = Carbon::tomorrow();
        return array_filter($files, function ($file) use ($folder, $tomorrow) {
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
            . $this->params['backup_path'] . DIRECTORY_SEPARATOR
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
