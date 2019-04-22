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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ReloadCmd extends Command
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
        $this->setName('reload')->setDescription("Reload server");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $server_name = $this->config['name'] ?? 'SWD';
        $master_pid = exec("ps -ef | grep $server_name-master | grep -v 'grep ' | awk '{print $2}'");
        $manager_pid = exec("ps -ef | grep $server_name-manager | grep -v 'grep ' | awk '{print $2}'");
        if (empty($master_pid)) {
            $io->warning("server $server_name not run");
            return ConsolePlug::SUCCESS_EXIT;
        }
        posix_kill($manager_pid, SIGUSR1);
        $io->success("server $server_name reload");
        return ConsolePlug::SUCCESS_EXIT;
    }
}