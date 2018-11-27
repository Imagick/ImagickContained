<?php

declare(strict_types=1);


namespace ImagickContained;


interface ImagickTaskQueue
{
    const UNKNOWN   = 'unknown';
    const QUEUED    = 'queued';
    const WORKNG    = 'working';
    const FAILED    = 'failed';
    const SUCCESS   = 'success';

    public function getTaskStatus(ImagickTask $imagickTask) : ?string;

    public function isTaskStatusSuccess(ImagickTask $imagickTask): bool;

    public function pushImagickTask(ImagickTask $imagickTask);

    public function waitForNextImagickTask() : ?ImagickTask;
}