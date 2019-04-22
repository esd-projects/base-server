<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/4/20
 * Time: 10:38
 */

namespace GoSwoole\BaseServer\Plugins\Console;

use GoSwoole\BaseServer\Plugins\Console\Command\ReloadCmd;
use GoSwoole\BaseServer\Plugins\Console\Command\RestartCmd;
use GoSwoole\BaseServer\Plugins\Console\Command\StartCmd;
use GoSwoole\BaseServer\Plugins\Console\Command\StopCmd;
use GoSwoole\BaseServer\Server\Context;
use GoSwoole\BaseServer\Server\PlugIn\AbstractPlug;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class ConsolePlug extends AbstractPlug
{
    const SUCCESS_EXIT = 0;
    const FAIL_EXIT = 1;
    const NOEXIT = -255;
    /**
     * @var Application
     */
    private $application;

    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "Console";
    }

    /**
     * 在服务启动前
     * @param Context $context
     * @return mixed
     * @throws \Exception
     */
    public function beforeServerStart(Context $context)
    {
        $this->application = new Application("GO-SWOOLE");
        $this->application->setAutoExit(false);
        $input = new ArgvInput();
        $output = new ConsoleOutput();
        $this->application->addCommands([
            new StartCmd($context),
            new StopCmd($context),
            new ReloadCmd($context),
            new RestartCmd($context)
        ]);
        $exitCode = $this->application->run($input, $output);
        if ($exitCode >= 0) {
            exit();
        }
    }

    /**
     * 在进程启动前
     * @param Context $context
     * @return mixed
     */
    public function beforeProcessStart(Context $context)
    {
        $this->ready();
    }
}