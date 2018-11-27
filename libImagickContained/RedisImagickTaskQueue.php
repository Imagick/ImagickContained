<?php

declare(strict_types=1);

namespace ImagickContained;

use ImagickContained\ImagickTask;

class RedisImagickTaskQueue implements ImagickTaskQueue
{
    /** @var \Redis */
    private $redis;

    /**
     * The name of the queue
     *
     * @var string
     */
    private $taskListKey = 'imagick_containers:taskList';

    private $taskStatusKey = 'imagick_containers:taskStatus';

    /**
     * RedisImagickTaskQueue constructor.
     * @param \Redis $redis
     */
    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    public function waitForNextImagickTask(): ?ImagickTask
    {
        // A nil multi-bulk when no element could be popped and the timeout expired.
        // A two-element multi-bulk with the first element being the name of the key
        // where an element was popped and the second element being the value of
        // the popped element.
        $redisData = $this->redis->blpop([$this->taskListKey], 5);

        // Pop timed out rather than got a task
        if ($redisData === null) {
            return null;
        }

        // Pop timed out rather than got a task
        if (is_array($redisData) && count($redisData) === 0) {
            return null;
        }

        list($key, $imagickTaskString) = $redisData;


        $imagickTask = ImagickTask::deserialize($imagickTaskString);

        $statusKey = $this->getStatusKey($imagickTask);
        $this->redis->setex($statusKey, 3600, self::WORKNG);

        return $imagickTask;
    }

    private function getStatusKey(ImagickTask $imagickTask)
    {
        return $this->taskStatusKey . ':' . $imagickTask->getId();
    }

    public function pushImagickTask(ImagickTask $imagickTask)
    {
        $this->redis->rpush($this->taskListKey, $imagickTask->serialize());
        $this->setStatusKey($imagickTask, self::QUEUED);
    }

    public function getTaskStatus(ImagickTask $imagickTask): ?string
    {
        $statusKey = $this->getStatusKey($imagickTask);

        $status = $this->redis->get($statusKey);
        if ($status === false) {
            return null;
        }

        return $status;
    }

    public function isTaskStatusSuccess(ImagickTask $imagickTask): bool
    {
        $status = $this->getTaskStatus($imagickTask);

        return $status === self::SUCCESS;
    }

    private function setStatusKey(ImagickTask $imagickTask, $status)
    {
        $statusKey = $this->getStatusKey($imagickTask);
        $this->redis->setex($statusKey, 3600, $status);
    }

    public function setStatusFailed(ImagickTask $imagickTask)
    {
        $this->setStatusKey($imagickTask, self::FAILED);
    }

    public function setStatusSuccess(ImagickTask $imagickTask)
    {
        $this->setStatusKey($imagickTask, self::SUCCESS);
    }
}
