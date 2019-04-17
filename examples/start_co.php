<?php

use Core\Coroutine\Co;
use Core\Coroutine\CoPoolFactory;
use Core\Coroutine\Pool\Runnable;

require __DIR__ . '/../vendor/autoload.php';
\Core\Utils\Utils::enableRuntimeCoroutine();

class Task implements Runnable
{
    private $max;

    public function __construct($max)
    {
        $this->max = $max;
    }

    function run()
    {
        sleep($this->max);
        print_r("[" . Co::getCid() . "]\tRunnable执行完毕\n");
    }
}

go(function () {
    $pool = CoPoolFactory::createCoPool("Executor-1", 5, 10, 1);
    for ($i = 0; $i < 10; $i++) {
        $pool->execute(new Task(2));
        $pool->execute(function () {
            print_r("[" . Co::getCid() . "]\t执行完毕\n");
        });
    }

});
