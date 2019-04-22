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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StopCmd extends Command
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
        $this->setName('stop')->setDescription("Stop(Kill) server");
        $this->addOption('kill', "k", InputOption::VALUE_NONE, 'Who do you want kill server?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $serverConfig = $this->context->getServer()->getServerConfig();
        $server_name = $serverConfig->getName();
        $master_pid = exec("ps -ef | grep $server_name-master | grep -v 'grep ' | awk '{print $2}'");
        if (empty($master_pid)) {
            $io->warning("server $server_name not run");
            return ConsolePlug::SUCCESS_EXIT;
        }
        if ($input->getOption('kill')) {
            $result = $io->confirm("Kill the $server_name server?", false);
        } else {
            $result = $io->confirm("Stop the $server_name server?", false);
        }
        if (!$result) {
            $io->warning("Cancel by user");
            return ConsolePlug::SUCCESS_EXIT;
        }
        if ($input->getOption('kill')) {//kill -9
            exec("ps -ef|grep $server_name|grep -v grep|cut -c 9-15|xargs kill -9");
            return ConsolePlug::SUCCESS_EXIT;
        }
        // Send stop signal to master process.
        $master_pid && posix_kill($master_pid, SIGTERM);
        // Timeout.
        $timeout = 40;
        $start_time = time();
        // Check master process is still alive?
        while (1) {
            $master_is_alive = $master_pid && posix_kill($master_pid, 0);
            if ($master_is_alive) {
                // Timeout?
                if (time() - $start_time >= $timeout) {
                    $io->warning("server $server_name stop fail");
                    return ConsolePlug::FAIL_EXIT;
                }
                // Waiting amoment.
                usleep(10000);
                continue;
            }
            // Stop success.
            $io->success("server $server_name stop success");
            break;
        }
        return ConsolePlug::SUCCESS_EXIT;
    }
}