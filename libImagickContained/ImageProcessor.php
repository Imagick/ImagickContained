<?php

declare(strict_types=1);

namespace ImagickContained;

use function ImagickContained\continuallyExecuteCallable;

class ImageProcessor
{
    /**
     * @var RedisImagickTaskQueue
     */
    private $redisImagickTaskQueue;

    /**
     * ImageProcessor constructor.
     * @param $redisImagickTaskQueue
     */
    public function __construct(RedisImagickTaskQueue $redisImagickTaskQueue)
    {
        $this->redisImagickTaskQueue = $redisImagickTaskQueue;
    }


    public function run()
    {
        $callable = [$this, 'processImages'];

        continuallyExecuteCallable(
            $callable,
            0,
            1,
            $maxRunTime = 600
        );
    }

    public function processImages()
    {
        $nextImagickTask = $this->redisImagickTaskQueue->waitForNextImagickTask();

        if ($nextImagickTask === null) {
            return;
        }

//        $injector = new Injector();
//
//        foreach ($nextImagickTask->getParams() as $key => $value) {
//            echo "Found image to generate\n";
//            echo "Param : $key => $value\n";
//            $injector->defineParam($key, $value);
//        }

        echo "callable is: " . var_export($nextImagickTask->getCallable(), true) . "\n";
        echo "params are: " . var_export($nextImagickTask->getParams(), true) . "\n";

        try {
//            $injector->execute($nextImagickTask->getCallable());
            call_user_func_array(
                $nextImagickTask->getCallable(),
                $nextImagickTask->getParams()
            );

            $this->redisImagickTaskQueue->setStatusSuccess($nextImagickTask);
            echo "Image processed successfully\n";
        }
        catch (\Exception $e) {
            echo "Exception processing image\n";
            echo $e->getMessage();
            echo $e->getTraceAsString();
        }
    }
}
