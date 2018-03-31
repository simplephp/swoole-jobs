<?php

/*
 * This file is part of PHP CS Fixer.
 * (c) kcloze <pei.greet@qq.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Kcloze\Jobs;

class Console
{
    public $logger    = null;
    private $config   = [];

    public function __construct($config)
    {
        Config::setConfig($config);
        $this->config  = Config::getConfig();
        $this->logger  = new Logs($this->config['logPath'] ? $this->config['logPath'] : '', $this->config['logSaveFileApp'] ? $this->config['logSaveFileApp'] : '');
    }

    public function run()
    {
        $this->runOpt();
    }

    public function start()
    {
        //启动
        $process = new Process();
        $process->start();
    }

    /**
     *  给主进程发送信号：
     *  SIGUSR1 自定义信号，让子进程平滑退出
     *  SIGUSR2 自定义信号2，显示进程状态
     *  SIGTERM 程序终止，让子进程强制退出.
     *
     * @param [type] $signal
     */
    public function sendSignal($signal=SIGUSR1)
    {
        $this->logger->log($signal . (SIGUSR1 == $signal) ? ' smooth to exit...' : ' force to exit...');

        if (isset($this->config['pidPath']) && !empty($this->config['pidPath'])) {
            $masterPidFile=$this->config['pidPath'] . '/master.pid';
            $pidStatusFile=$this->config['pidPath'] . '/status.info';
        } else {
            die('config pidPath must be set!' . PHP_EOL);
        }

        if (file_exists($masterPidFile)) {
            $pid   =file_get_contents($masterPidFile);
            if ($pid && !@\Swoole\Process::kill($pid, 0)) {
                exit('service is not running' . PHP_EOL);
            }
            if (@\Swoole\Process::kill($pid, $signal)) {
                $this->logger->log('[master pid: ' . $pid . '] has been received  signal' . $signal);
                sleep(1);
                //如果是SIGUSR2信号，显示swoole-jobs状态信息
                if (SIGUSR2 == $signal) {
                    $statusStr=file_get_contents($pidStatusFile);

                    echo $statusStr ? $statusStr : 'sorry,show status fail.';
                    exit;
                }
            }
            $this->logger->log('[master pid: ' . $pid . '] has been received signal fail');
        } else {
            exit('service is not running' . PHP_EOL);
        }
    }

    public function restart()
    {
        $this->logger->log('restarting...');
        $this->kill();
        sleep(3);
        $this->start();
    }

    public function kill()
    {
        $this->sendSignal(SIGTERM);
    }

    public function runOpt()
    {
        global $argv;
        if (empty($argv[1])) {
            $this->printHelpMessage();
            exit(1);
        }
        $opt=$argv[1];
        switch ($opt) {
            case 'start':
                $this->start();
                break;
            case 'stop':
                $this->sendSignal();
                break;
            case 'status':
                $this->sendSignal(SIGUSR2);
                break;
            case 'exit':
                $this->kill();
                break;
            case 'restart':
                $this->restart();
                break;
            case 'help':
                $this->printHelpMessage();
                break;

            default:
                $this->printHelpMessage();
                break;
        }
    }

    public function printHelpMessage()
    {
        $msg=<<<'EOF'
NAME
      php swoole-jobs - manage swoole-jobs

SYNOPSIS
      php swoole-jobs command [options]
          Manage swoole-jobs daemons.

WORKFLOWS

      help [command]
      Show this help, or workflow help for command.

      restart
      Stop, then start swoole-jobs master and workers.

      start
      Start swoole-jobs master and workers.

      stop
      Wait all running workers smooth exit, please check swoole-jobs status for a while.

      exit
      Kill all running workers and master PIDs.


EOF;
        echo $msg;
    }
}
