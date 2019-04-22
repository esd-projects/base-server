<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 18-1-22
 * Time: 上午10:59
 */

namespace GoSwoole\BaseServer\Plugins\Console\Command;

use GoSwoole\BaseServer\Plugins\Console\ConsolePlug;
use GoSwoole\BaseServer\Server\Context;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RestartCmd extends Command
{
    /**
     * @var Context
     */
    private $context;

    /**
     * StartCmd constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct();
        $this->context = $context;
    }

    protected function configure()
    {
        $this->setName('restart')->setDescription("Restart server");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $serverConfig = $this->context->getServer()->getServerConfig();
        $server_name = $serverConfig->getName();
        $master_pid = exec("ps -ef | grep $server_name-master | grep -v 'grep ' | awk '{print $2}'");
        if (empty($master_pid)) {
            $io->warning("$server_name server not running");
            return;
        }
        $command = $this->getApplication()->find('stop');
        $arguments = array(
            'command' => 'stop'
        );
        $greetInput = new ArrayInput($arguments);
        $code = $command->run($greetInput, $output);
        if ($code == ConsolePlug::FAIL_EXIT) {
            return ConsolePlug::FAIL_EXIT;
        }
        $serverConfig->setDaemonize(true);
        return ConsolePlug::NOEXIT;
    }
}