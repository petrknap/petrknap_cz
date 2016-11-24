<?php

namespace PetrKnap\Web\Service;

use Netpromotion\Profiler\Profiler;
use Nette\DI\Container;

class CronService
{
    const TASK_CLASS_NAME = "class";
    const TASK_METHOD_NAME = "method";
    const TASK_EXECUTE_AT = "hours";

    /**
     * @var array
     */
    private $tasks;

    /**
     * @var Container
     */
    private $container;

    public function __construct(array $tasks, Container $container)
    {
        $this->tasks = $tasks;
        $this->container = $container;
    }

    public function run(\DateTime $now)
    {
        Profiler::start("CronService::run(...)");
        foreach ($this->tasks as $task) {
            if (!in_array($now->format("%h"), $task[self::TASK_EXECUTE_AT])) {
                continue;
            }
            Profiler::start("%s::%s", $task[self::TASK_CLASS_NAME], $task[self::TASK_METHOD_NAME]);
            $taskInstance = $this->container->getByType($task[self::TASK_CLASS_NAME]);
            call_user_func([$taskInstance, $task[self::TASK_METHOD_NAME]]);
            Profiler::finish("%s::%s", $task[self::TASK_CLASS_NAME], $task[self::TASK_METHOD_NAME]);
        }
        Profiler::finish("CronService::run(...)");
    }
}
