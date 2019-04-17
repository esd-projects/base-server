<?php

use GoSwoole\BaseServer\Coroutine\Co;
use GoSwoole\BaseServer\Coroutine\CoPoolFactory;
use GoSwoole\BaseServer\Coroutine\Pool\Runnable;

require __DIR__ . '/../vendor/autoload.php';
enableRuntimeCoroutine();

/**
 * 用连接池执行任务并获取结果
 * Class Task
 */
class Task extends Runnable
{
    private $max;

    public function __construct($max)
    {
        parent::__construct(false);
        $this->max = $max;
    }

    function run()
    {
        sleep($this->max);
        print_r("[" . Co::getCid() . "]\tRunnable执行完毕\n");
        return $this->max;
    }
}

goWithContext(function () {
    $pool = CoPoolFactory::createCoPool("Executor-1", 5, 10, 1);
    $tasks = [];
    for ($i = 0; $i < 10; $i++) {
        $task = new Task(2);
        $tasks[] = $task;
        $pool->execute($task);
        print_r("结果->" . $task->getResult() . "\n");
    }
});
