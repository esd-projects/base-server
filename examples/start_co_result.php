<?php

use Core\Coroutine\Co;
use Core\Coroutine\CoPoolFactory;
use Core\Coroutine\Pool\Runnable;

require __DIR__ . '/../vendor/autoload.php';
\Core\Utils\Utils::enableRuntimeCoroutine();

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

go(function () {
    $pool = CoPoolFactory::createCoPool("Executor-1", 5, 10, 1);
    $tasks = [];
    for ($i = 0; $i < 10; $i++) {
        $task = new Task(2);
        $tasks[] = $task;
        $pool->execute($task);
    }
});
